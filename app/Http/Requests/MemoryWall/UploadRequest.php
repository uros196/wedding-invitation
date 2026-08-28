<?php

declare(strict_types=1);

namespace App\Http\Requests\MemoryWall;

use App\DTOs\MemoryWallUploadInitializeData;
use App\Models\Wedding;
use App\Services\MemoryWallService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Validator;

/**
 * Validates the trusted metadata submitted when a memory wall upload starts.
 *
 * The file bytes arrive later through presigned S3 requests, therefore the
 * completion service repeats the important checks against the assembled object.
 */
class UploadRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Guests may upload anonymously, but only while this wedding's wall is open.
        /** @var Wedding $wedding */
        $wedding = $this->route('wedding');

        return resolve(MemoryWallService::class)->isFormOpen($wedding);
    }

    /**
     * Validate client identity, declared size, filename, and MIME metadata.
     */
    public function rules(): array
    {
        return [
            'client_upload_id' => ['required', 'uuid'],
            'upload_token' => ['required', 'string', 'size:64', 'regex:/^[a-f0-9]+$/i'],
            'file_name' => ['required', 'string', 'max:255'],
            'size' => ['required', 'integer', 'min:1', 'max:'.config('memory-wall.max_file_size')],
            'mime_type' => ['required', 'string', 'in:'.implode(',', config('memory-wall.allowed_mime_types'))],
        ];
    }

    /**
     * Reject metadata combinations that are not allowed by the configured map.
     *
     * @return array<int, callable(Validator): void>
     */
    public function after(): array
    {
        return [
            function (Validator $validator): void {
                $fileName = (string) $this->input('file_name');
                $extension = Str::lower(pathinfo($fileName, PATHINFO_EXTENSION));
                $mimeType = (string) $this->input('mime_type');
                $allowedExtensions = config('memory-wall.allowed_extensions', []);
                $allowedMimeTypes = config('memory-wall.extension_mime_types', []);

                if (! in_array($extension, $allowedExtensions, true)) {
                    $validator->errors()->add(
                        'file_name',
                        __('wedding.memory_wall.validation.file_type'),
                    );

                    return;
                }

                if (! in_array($mimeType, $allowedMimeTypes[$extension] ?? [], true)) {
                    $validator->errors()->add(
                        'mime_type',
                        __('wedding.memory_wall.validation.file_type'),
                    );
                }
            },
        ];
    }

    /**
     * Convert the validated request data to upload initialization data.
     */
    public function toDto(): MemoryWallUploadInitializeData
    {
        return MemoryWallUploadInitializeData::fromRequest($this);
    }
}
