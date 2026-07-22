<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Models\Wedding;
use App\Rules\MediaSizeRule;
use App\Services\MemoryWallService;
use Illuminate\Foundation\Http\FormRequest;

class MemoryWallUploadRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        /** @var Wedding $wedding */
        $wedding = $this->route('wedding');

        return resolve(MemoryWallService::class)->isFormOpen($wedding);
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'files' => ['required', 'array', 'min:1'],
            'files.*' => [
                'required',
                'file',
                'mimes:jpg,jpeg,png,mp4,mov,avi',
                new MediaSizeRule([
                    'image/*' => '20MB',
                    'video/*' => '1GB',
                ]),
            ],
        ];
    }
}
