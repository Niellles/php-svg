<?php

namespace SVG\Rasterization\Backend;

use SVG\Rasterization\Image\RasterImage;

/**
 * Abstraction for image rendering backends.
 *
 * Implementations of this interface handle the actual drawing operations
 * for different graphics libraries (GD, Imagick, Gmagick, etc.).
 *
 * @since 3.0.0
 */
interface ImageBackend
{
    /**
     * Create a new image instance for the backend.
     *
     * @param int $width The image width in pixels.
     * @param int $height The image height in pixels.
     * @param string|null $background The background color (hex/rgb[a]/hsl[a]/...)
     *
     * @return mixed Backend-native image (GD resource, Imagick instance, etc.)
     */
    public function createImage(int $width, int $height, ?string $background);

    /**
     * Wrap a native backend image in a RasterImage abstraction.
     *
     * @param mixed $nativeImage The backend-native image object/resource.
     *
     * @return RasterImage The wrapped image.
     */
    public function wrapImage($nativeImage): RasterImage;

    /**
     * Get the backend type identifier.
     *
     * @return string One of: 'gd', 'imagick', 'gmagick'
     */
    public function getBackendType(): string;
}
