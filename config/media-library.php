<?php

use App\Models\Group;
use App\Models\Wedding;
use App\Support\MediaLibrary\ModelPathGenerator;
use App\Support\MediaLibrary\WeddingPathGenerator;
use Spatie\MediaLibrary\Support\PathGenerator\DefaultPathGenerator;

return [
    /*
     * Keep the Media Library limit aligned with the Memory Wall multipart limit.
     */
    'max_file_size' => 1024 * 1024 * 1024,

    /*
     * Keep Spatie's generator as the default for models without a custom mapping.
     */
    'path_generator' => DefaultPathGenerator::class,

    /*
     * Spatie selects the generator by the media owner's model class.
     * Group media uses the model folder followed by Spatie's default path.
     * Wedding media additionally includes the wedding entity UUID.
     */
    'custom_path_generators' => [
        Group::class => ModelPathGenerator::class,
        Wedding::class => WeddingPathGenerator::class,
    ],
];
