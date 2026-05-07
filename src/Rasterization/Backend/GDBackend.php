<?php

namespace SVG\Rasterization\Backend;

use SVG\Rasterization\Image\RasterImage;
use SVG\Rasterization\Image\GDRasterImage;
use SVG\Utilities\Colors\Color;

/**
 * GD-based rendering backend implementation.
 *
 * This backend uses PHP's built-in GD (Graphics Draw) extension for rasterization.
 * GD is widely available but has limitations compared to Imagick.
 *
 * @since 3.0.0
 */
class GDBackend implements ImageBackend
{
    /**
     * @inheritdoc
     */
    public function createImage(int $width, int $height, ?string $background)
    {
        // Create a true color image with alpha channel support
        $img = imagecreatetruecolor($width, $height);
        if ($img === false) {
            throw new \RuntimeException('Failed to create GD image');
        }

        // Enable alpha blending and save alpha channel
        imagealphablending($img, true);
        imagesavealpha($img, true);

        // Determine background color (default: transparent)
        $bgRgb = 0x7F000000; // Transparent by default
        if (!empty($background)) {
            $bgColor = Color::parse($background);
            $alpha = 127 - (int)($bgColor[3] * 127 / 255);
            $bgRgb = ($alpha << 24) + ($bgColor[0] << 16) + ($bgColor[1] << 8) + ($bgColor[2]);
        }

        // Fill with background color
        imagefill($img, 0, 0, $bgRgb);

        return $img;
    }

    /**
     * @inheritdoc
     */
    public function wrapImage($nativeImage): RasterImage
    {
        return new GDRasterImage($nativeImage);
    }

    /**
     * @inheritdoc
     */
    public function getBackendType(): string
    {
        return 'gd';
    }
}
