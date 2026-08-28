<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\MemoryWallUploadStatus;
use App\Models\MemoryWallUpload;
use App\Models\Wedding;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<MemoryWallUpload>
 */
class MemoryWallUploadFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * The factory represents a completed control-plane record without creating
     * a real object in S3; tests can override the status for intermediate states.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'wedding_id' => Wedding::factory(),
            'media_id' => null,
            'uuid' => Str::uuid()->toString(),
            'client_upload_id' => Str::uuid()->toString(),
            'upload_token_hash' => hash('sha256', Str::random(64)),
            'multipart_upload_id' => null,
            'object_path' => 'weddings/memory-wall/'.$this->faker->uuid.'.bin',
            'original_name' => 'memory-wall-file.jpg',
            'extension' => 'jpg',
            'mime_type' => 'image/jpeg',
            'expected_size' => 1024,
            'part_size' => config('memory-wall.part_size', 8 * 1024 * 1024),
            'total_parts' => 1,
            'status' => MemoryWallUploadStatus::Completed,
            'error_message' => null,
            'completed_at' => now(),
        ];
    }
}
