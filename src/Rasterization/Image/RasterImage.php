<?php

namespace SVG\Rasterization\Image;

/**
 * Abstraction for rasterized image output, backend-agnostic.
 *
 * This interface ensures compatibility with different rendering backends
 * (GD, Imagick, Gmagick, etc.) without coupling client code to specific libraries.
 *
 * @since 3.0.0
 */
interface RasterImage
{
    /**
     * Get the image width in pixels.
     *
     * @return int
     */
    public function getWidth(): int;

    /**
     * Get the image height in pixels.
     *
     * @return int
     */
    public function getHeight(): int;

    /**
     * Export image as PNG binary string.
     *
     * @return string PNG-encoded image data
     */
    public function toPNG(): string;

    /**
     * Export image as JPEG binary string.
     *
     * @param int $quality JPEG quality (0-100)
     * @return string JPEG-encoded image data
     */
    public function toJPEG(int $quality = 85): string;

    /**
     * Export image as GIF binary string.
     *
     * @return string GIF-encoded image data
     */
    public function toGIF(): string;

    /**
     * Get underlying GD resource (for backward compatibility).
     *
     * This converts non-GD backends to GD resource format.
     * Note: This is deprecated. Use toPNG(), toJPEG(), toGIF() instead.
     *
     * @return resource GD image resource
     * @deprecated Use toPNG(), toJPEG(), or toGIF() instead
     */
    public function toGDResource();

    /**
     * Get backend type identifier.
     *
     * @return string One of: 'gd', 'imagick', 'gmagick'
     */
    public function getBackendType(): string;
}
