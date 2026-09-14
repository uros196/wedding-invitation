<?php

declare(strict_types=1);

namespace App\Exceptions;

use RuntimeException;

/**
 * Stops archive generation after a user requested cancellation.
 */
final class MemoryWallDownloadCancelled extends RuntimeException {}
