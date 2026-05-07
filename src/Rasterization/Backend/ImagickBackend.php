<?php

namespace SVG\Rasterization\Backend;

use SVG\Rasterization\Image\RasterImage;
use SVG\Rasterization\Image\ImagickRasterImage;
use SVG\Utilities\Colors\Color;

/**
 * Imagick/Gmagick-based rendering backend implementation.
 *
 * This backend uses either the Imagick or Gmagick PHP extension for rasterization.
 * These provide superior rendering quality compared to GD but are not always available.
 *
 * @since 3.0.0
 */
class ImagickBackend implements ImageBackend
{
    /**
     * @var string $type Either 'imagick' or 'gmagick'
     */
    private string $type;

    /**
     * Construct a new ImagickBackend.
     *
     * @throws \RuntimeException If neither Imagick nor Gmagick is available.
     */
    public function __construct()
    {
        if (extension_loaded('imagick')) {
            $this->type = 'imagick';
        } elseif (extension_loaded('gmagick')) {
            $this->type = 'gmagick';
        } else {
            throw new \RuntimeException('Neither Imagick nor Gmagick extension is available');
        }
    }

    /**
     * @inheritdoc
     */
    public function createImage(int $width, int $height, ?string $background)
    {
        // Determine background color
        $bgColor = 'transparent';
        if (!empty($background)) {
            $bgColor = $background;
        }

        // Create Imagick/Gmagick instance
        $class = $this->type === 'imagick' ? '\\Imagick' : '\\Gmagick';
        $imagick = new $class();

        // Create new image with specified dimensions and background
        $imagick->newImage($width, $height, $bgColor);
        $imagick->setImageFormat('png');

        return $imagick;
    }

    /**
     * @inheritdoc
     */
    public function wrapImage($nativeImage): RasterImage
    {
        return new ImagickRasterImage($nativeImage);
    }

    /**
     * @inheritdoc
     */
    public function getBackendType(): string
    {
        return $this->type;
    }
}
