<?php

namespace SVG\Rasterization\Image;

/**
 * Imagick/Gmagick-backed raster image implementation.
 *
 * @since 3.0.0
 */
class ImagickRasterImage implements RasterImage
{
    /**
     * @var \Imagick|\Gmagick $imagick The underlying Imagick/Gmagick instance.
     */
    private $imagick;

    /**
     * @var int $width The image width in pixels.
     */
    private int $width;

    /**
     * @var int $height The image height in pixels.
     */
    private int $height;

    /**
     * Construct a new ImagickRasterImage from an Imagick or Gmagick instance.
     *
     * @param \Imagick|\Gmagick $imagick
     * @throws \InvalidArgumentException If not a valid Imagick or Gmagick instance.
     */
    public function __construct($imagick)
    {
        if (!($imagick instanceof \Imagick || $imagick instanceof \Gmagick)) {
            throw new \InvalidArgumentException('Expected Imagick or Gmagick instance');
        }
        $this->imagick = $imagick;
        $this->width = (int)$imagick->getImageWidth();
        $this->height = (int)$imagick->getImageHeight();
    }

    /**
     * @inheritdoc
     */
    public function getWidth(): int
    {
        return $this->width;
    }

    /**
     * @inheritdoc
     */
    public function getHeight(): int
    {
        return $this->height;
    }

    /**
     * @inheritdoc
     */
    public function toPNG(): string
    {
        $imagick = clone $this->imagick;
        $imagick->setFormat('png');
        return $imagick->getImageBlob();
    }

    /**
     * @inheritdoc
     */
    public function toJPEG(int $quality = 85): string
    {
        $quality = max(0, min(100, $quality));
        $imagick = clone $this->imagick;
        $imagick->setFormat('jpeg');

        // Set compression quality if these methods exist
        if (method_exists($imagick, 'setImageCompressionQuality')) {
            $imagick->setImageCompressionQuality($quality);
        } elseif (method_exists($imagick, 'setCompressionQuality')) {
            $imagick->setCompressionQuality($quality);
        }

        return $imagick->getImageBlob();
    }

    /**
     * @inheritdoc
     */
    public function toGIF(): string
    {
        $imagick = clone $this->imagick;
        $imagick->setFormat('gif');
        return $imagick->getImageBlob();
    }

    /**
     * @inheritdoc
     */
    public function toGDResource()
    {
        // Convert Imagick/Gmagick to PNG and load as GD resource
        $pngData = $this->toPNG();
        $gdResource = imagecreatefromstring($pngData);
        if ($gdResource === false) {
            throw new \RuntimeException('Failed to convert Imagick image to GD resource');
        }
        return $gdResource;
    }

    /**
     * @inheritdoc
     */
    public function getBackendType(): string
    {
        return ($this->imagick instanceof \Imagick) ? 'imagick' : 'gmagick';
    }
}
