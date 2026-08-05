<?php

declare(strict_types=1);

namespace App\Support\QrCodeBuilder;

use App\Support\QrCodeBuilder\Data\QrCodeLogoPlacement;
use Endroid\QrCode\Matrix\MatrixInterface;

/**
 * Calculates shared QR module, finder-pattern, and logo placement geometry.
 */
final class QrCodeGeometry
{
    /**
     * Return the three finder-pattern origins in matrix coordinates.
     *
     * @return array<int, array{0: int, 1: int}>
     */
    public function finderOrigins(int $blockCount): array
    {
        return [
            [0, 0],
            [$blockCount - 7, 0],
            [0, $blockCount - 7],
        ];
    }

    /**
     * Determine whether a matrix cell belongs to a finder pattern.
     *
     * @param  array<int, array{0: int, 1: int}>  $origins
     */
    public function isFinderPatternCell(int $row, int $column, array $origins): bool
    {
        foreach ($origins as [$originRow, $originColumn]) {
            if (
                $row >= $originRow
                && $row < $originRow + 7
                && $column >= $originColumn
                && $column < $originColumn + 7
            ) {
                return true;
            }
        }

        return false;
    }

    /**
     * Calculate the center point of a matrix module in output coordinates.
     *
     * @return array{0: float, 1: float}
     */
    public function moduleCenter(MatrixInterface $matrix, int $row, int $column): array
    {
        $blockSize = $matrix->getBlockSize();
        $margin = $matrix->getMarginLeft();

        return [
            $margin + ($column + 0.5) * $blockSize,
            $margin + ($row + 0.5) * $blockSize,
        ];
    }

    /**
     * Calculate the centered circular logo zone for a QR matrix.
     */
    public function logoPlacement(MatrixInterface $matrix, QrCodeStyle $style): QrCodeLogoPlacement
    {
        $logoSize = $style->logoSize($matrix->getInnerSize());
        $logoMargin = $style->logoMargin($matrix->getInnerSize());
        $containerDiameter = $logoSize + 2 * $logoMargin;
        $center = $matrix->getOuterSize() / 2;

        return new QrCodeLogoPlacement(
            centerX: $center,
            centerY: $center,
            logoX: $center - ($logoSize / 2),
            logoY: $center - ($logoSize / 2),
            logoSize: $logoSize,
            margin: $logoMargin,
            containerDiameter: $containerDiameter,
            containerRadius: $containerDiameter / 2,
        );
    }
}
