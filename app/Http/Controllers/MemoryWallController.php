<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\MemoryWall\UploadRequest;
use App\Http\Requests\MemoryWall\UploadSessionRequest;
use App\Http\Resources\Media\MediaResource;
use App\Http\Resources\MetaDataResource;
use App\Http\Resources\WeddingResource;
use App\Models\MemoryWallUpload;
use App\Models\Wedding;
use App\Services\MemoryWall\CancelMemoryWallUpload;
use App\Services\MemoryWall\GetMemoryWallUploadPartUrls;
use App\Services\MemoryWall\InitializeMemoryWallUpload;
use App\Services\MemoryWall\RequestMemoryWallUploadCompletion;
use App\Services\MemoryWallService;
use App\Support\MetaFactory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
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
        protected RequestMemoryWallUploadCompletion $requestCompletion,
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
                'autoUpload' => config('memory-wall.auto_upload'),
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
     * Request background completion or return the already completed media.
     */
    public function completeUpload(UploadSessionRequest $request, Wedding $wedding, MemoryWallUpload $upload): JsonResponse
    {
        $media = $this->requestCompletion->handle($wedding, $upload, $request->token());

        if ($media !== null) {
            // Return a completed media resource from the upload completion endpoint.
            return response()->json([
                'data' => MediaResource::make($media),
            ]);
        }

        return response()->json([
            'data' => ['status' => 'processing'],
        ], Response::HTTP_ACCEPTED);
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
