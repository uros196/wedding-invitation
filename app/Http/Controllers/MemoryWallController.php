<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\MemoryWallUploadRequest;
use App\Http\Resources\MediaResource;
use App\Http\Resources\MetaDataResource;
use App\Http\Resources\WeddingResource;
use App\Models\Wedding;
use App\Services\MemoryWallService;
use App\Support\MetaFactory;
use Illuminate\Http\JsonResponse;
use Inertia\Inertia;
use Inertia\Response;

class MemoryWallController extends Controller
{
    public function __construct(
        protected MemoryWallService $service,
        protected MetaFactory $metaFactory
    ) {}

    /**
     * Render the memory wall view.
     */
    public function show(Wedding $wedding): Response
    {
        abort_if(!$wedding->has_memory_wall, 404);

        $metaData = $this->metaFactory->forWedding($wedding);

        return Inertia::render('memory-wall', [
            'wedding' => WeddingResource::make($wedding),
            'metaData' => MetaDataResource::make($metaData),
            'media' => fn () => MediaResource::collection($this->service->getRandomFiles($wedding)),
        ]);
    }

    /**
     * Upload images/video on the wedding memory wall.
     */
    public function upload(MemoryWallUploadRequest $request, Wedding $wedding): JsonResponse
    {
        $this->service->uploadFiles($wedding, $request->validated('files'));

        return response()->json([
            'message' => __('Files uploaded successfully.'),
        ]);
    }
}
