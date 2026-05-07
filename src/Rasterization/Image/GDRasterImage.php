<?php

namespace SVG\Rasterization\Image;

/**
 * GD-backed raster image implementation.
 *
 * @since 3.0.0
 */
class GDRasterImage implements RasterImage
{
    /**
     * @var resource|\GdImage $gdResource The underlying GD image resource.
     */
    private $gdResource;

    /**
     * Construct a new GDRasterImage from a GD resource.
     *
     * @param resource|\GdImage $gdResource
     * @throws \InvalidArgumentException If not a valid GD resource/object.
     */
    public function __construct($gdResource)
    {
        if (!is_resource($gdResource) && !($gdResource instanceof \GdImage)) {
            throw new \InvalidArgumentException('Expected GD resource or GdImage object');
        }
        $this->gdResource = $gdResource;
    }

    /**
     * @inheritdoc
     */
    public function getWidth(): int
    {
        return imagesx($this->gdResource);
    }

    /**
     * @inheritdoc
     */
    public function getHeight(): int
    {
        return imagesy($this->gdResource);
    }

    /**
     * @inheritdoc
     */
    public function toPNG(): string
    {
        ob_start();
        imagepng($this->gdResource);
        $png = ob_get_clean();
        return $png !== false ? $png : '';
    }

    /**
     * @inheritdoc
     */
    public function toJPEG(int $quality = 85): string
    {
        $quality = max(0, min(100, $quality));
        ob_start();
        imagejpeg($this->gdResource, null, $quality);
        $jpeg = ob_get_clean();
        return $jpeg !== false ? $jpeg : '';
    }

    /**
     * @inheritdoc
     */
    public function toGIF(): string
    {
        ob_start();
        imagegif($this->gdResource);
        $gif = ob_get_clean();
        return $gif !== false ? $gif : '';
    }

    /**
     * @inheritdoc
     */
    public function toGDResource()
    {
        return $this->gdResource;
    }

    /**
     * @inheritdoc
     */
    public function getBackendType(): string
    {
        return 'gd';
    }

    /**
     * Clean up the GD resource when object is destroyed.
     */
    public function __destruct()
    {
        // Only destroy if it's still a resource (not a GdImage object)
        if (is_resource($this->gdResource)) {
            imagedestroy($this->gdResource);
        }
    }
}
