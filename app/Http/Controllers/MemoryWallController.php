<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\MemoryWall\UploadRequest;
use App\Http\Requests\MemoryWall\UploadSessionRequest;
use App\Http\Resources\Media\MediaResource;
use App\Http\Resources\MetaDataResource;
use App\Http\Resources\WeddingResource;
use App\Jobs\CompleteMemoryWallUploadJob;
use App\Models\MemoryWallUpload;
use App\Models\Wedding;
use App\Services\MemoryWall\CancelMemoryWallUpload;
use App\Services\MemoryWall\GetMemoryWallUploadPartUrls;
use App\Services\MemoryWall\InitializeMemoryWallUpload;
use App\Services\MemoryWall\Upload\Authorizer;
use App\Services\MemoryWallService;
use App\Support\MetaFactory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

/**
 * Serves the public memory wall and coordinates its JSON upload endpoints.
 *
 * The controller deliberately contains no multipart or validation logic; it
 * translates HTTP input into calls to the dedicated upload actions.
 */
final class MemoryWallController extends Controller
{
    public function __construct(
        protected MemoryWallService $service,
        protected MetaFactory $metaFactory,
        protected InitializeMemoryWallUpload $initializeUpload,
        protected GetMemoryWallUploadPartUrls $getPartUrls,
        protected Authorizer $authorizer,
        protected CancelMemoryWallUpload $cancelUpload,
    ) {}

    /**
     * Render the memory wall view.
     */
    public function show(Wedding $wedding): InertiaResponse
    {
        abort_if(! $wedding->has_memory_wall, 404);

        $metaData = $this->metaFactory->forWedding($wedding);

        return Inertia::render('memory-wall', [
            'wedding' => WeddingResource::make($wedding),
            'metaData' => MetaDataResource::make($metaData),
            'media' => fn () => MediaResource::collection($this->service->getRandomFiles($wedding)),
            'uploadConfig' => [
                'maxFiles' => config('memory-wall.max_files'),
                'maxFileSize' => config('memory-wall.max_file_size'),
                'acceptedTypes' => config('memory-wall.allowed_mime_types'),
            ],
            'translations' => [
                'upload' => [
                    'title' => __('wedding.memory_wall.upload.title'),
                    'description' => __('wedding.memory_wall.upload.description'),
                    'dropzone' => __('wedding.memory_wall.upload.dropzone'),
                    'browse' => __('wedding.memory_wall.upload.browse'),
                    'dropzoneHint' => __('wedding.memory_wall.upload.dropzone_hint'),
                    'videoLabel' => __('wedding.memory_wall.upload.video_label'),
                    'selected' => __('wedding.memory_wall.upload.selected'),
                    'uploadAction' => __('wedding.memory_wall.upload.upload_action'),
                    'uploading' => __('wedding.memory_wall.upload.uploading'),
                    'processing' => __('wedding.memory_wall.status.processing'),
                    'queued' => __('wedding.memory_wall.upload.queued'),
                    'completed' => __('wedding.memory_wall.upload.completed'),
                    'failed' => __('wedding.memory_wall.upload.failed'),
                    'retry' => __('wedding.memory_wall.upload.retry'),
                    'cancel' => __('wedding.memory_wall.upload.cancel'),
                    'remove' => __('wedding.memory_wall.upload.remove'),
                    'maxFiles' => __('wedding.memory_wall.upload.max_files', ['count' => config('memory-wall.max_files')]),
                    'maxFileSize' => __('wedding.memory_wall.upload.max_file_size'),
                    'fileTypeError' => __('wedding.memory_wall.validation.file_type'),
                    'fileSizeError' => __('wedding.memory_wall.validation.file_size'),
                    'maxFilesError' => __('wedding.memory_wall.upload.max_files', ['count' => config('memory-wall.max_files')]),
                    'empty' => __('wedding.memory_wall.upload.empty'),
                    'networkError' => __('wedding.memory_wall.upload.network_error'),
                    'completedSummary' => __('wedding.memory_wall.upload.completed_summary'),
                ],
                'gallery' => [
                    'title' => __('wedding.memory_wall.gallery.title'),
                    'empty' => __('wedding.memory_wall.gallery.empty'),
                    'imageAlt' => __('wedding.memory_wall.gallery.image_alt'),
                    'videoLabel' => __('wedding.memory_wall.gallery.video_label'),
                ],
            ],
        ]);
    }

    /**
     * Create or resume the multipart session for one selected file.
     */
    public function initializeUpload(UploadRequest $request, Wedding $wedding): JsonResponse
    {
        $data = $request->toDto();
        $upload = $this->initializeUpload->handle($wedding, $data);

        return response()->json([
            'data' => [
                'uuid' => $upload->uuid,
                'upload_token' => $data->uploadToken,
                'part_size' => $upload->part_size,
                'total_parts' => $upload->total_parts,
            ],
        ], 201);
    }

    /**
     * Return the presigned URLs needed to upload this session's parts.
     */
    public function getUploadPartUrls(UploadSessionRequest $request, Wedding $wedding, MemoryWallUpload $upload): JsonResponse
    {
        return response()->json([
            'data' => [
                'parts' => $this->getPartUrls->handle($wedding, $upload, $request->token()),
            ],
        ]);
    }

    /**
     * Validate and publish a multipart session after all parts are uploaded.
     */
    public function completeUpload(UploadSessionRequest $request, Wedding $wedding, MemoryWallUpload $upload): JsonResponse
    {
        $this->authorizer->authorize($wedding, $upload, $request->token());

        if ($upload->status->isFailed()) {
            throw ValidationException::withMessages([
                'file' => $upload->error_message ?? __('wedding.memory_wall.validation.processing_failed'),
            ]);
        }

        if ($upload->status->isCompleted()) {
            /** @var Media $media */
            $media = $upload->media()->firstOrFail();

            return $this->mediaResponse($request, $media);
        }

        if ($upload->status->isUploading()) {
            CompleteMemoryWallUploadJob::dispatch($wedding, $upload);
        }

        return response()->json([
            'data' => ['status' => 'processing'],
        ], Response::HTTP_ACCEPTED);
    }

    /**
     * Return a completed media resource from the upload completion endpoint.
     */
    private function mediaResponse(UploadSessionRequest $request, Media $media): JsonResponse
    {
        return response()->json([
            'data' => MediaResource::make($media)->resolve($request),
        ]);
    }

    /**
     * Cancel an unfinished session and discard its remote object.
     */
    public function cancelUpload(UploadSessionRequest $request, Wedding $wedding, MemoryWallUpload $upload): Response
    {
        $this->cancelUpload->handle($wedding, $upload, $request->token());

        return response()->noContent();
    }
}
