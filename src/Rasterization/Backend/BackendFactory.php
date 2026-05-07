<?php

namespace SVG\Rasterization\Backend;

/**
 * Factory for creating the optimal rendering backend.
 *
 * This factory automatically detects available PHP extensions and creates
 * an appropriate backend instance. The preference order is:
 * 1. Imagick (highest quality)
 * 2. Gmagick (good quality)
 * 3. GD (always available fallback)
 *
 * @since 3.0.0
 */
class BackendFactory
{
    /**
     * Create the optimal available backend.
     *
     * @return ImageBackend The best available backend.
     * @throws \RuntimeException If no suitable backend is available (GD should always be available).
     */
    public static function createOptimalBackend(): ImageBackend
    {
        if (self::isImagickAvailable()) {
            return new ImagickBackend();
        }

        if (!self::isGDAvailable()) {
            throw new \RuntimeException('No suitable image backend available. Please install ext-gd or ext-imagick.');
        }

        return new GDBackend();
    }

    /**
     * Check if Imagick extension is available.
     *
     * @return bool
     */
    public static function isImagickAvailable(): bool
    {
        return extension_loaded('imagick') && class_exists('Imagick');
    }

    /**
     * Check if Gmagick extension is available.
     *
     * @return bool
     */
    public static function isGmagickAvailable(): bool
    {
        return extension_loaded('gmagick') && class_exists('Gmagick');
    }

    /**
     * Check if GD extension is available.
     *
     * @return bool
     */
    public static function isGDAvailable(): bool
    {
        return extension_loaded('gd');
    }

    /**
     * Check if any suitable backend is available.
     *
     * @return bool
     */
    public static function hasAvailableBackend(): bool
    {
        return self::isImagickAvailable() || self::isGmagickAvailable() || self::isGDAvailable();
    }

    /**
     * Get a list of available backends.
     *
     * @return string[] Array of available backend names ('gd', 'imagick', 'gmagick')
     */
    public static function getAvailableBackends(): array
    {
        $backends = [];

        if (self::isImagickAvailable()) {
            $backends[] = 'imagick';
        }

        if (self::isGmagickAvailable()) {
            $backends[] = 'gmagick';
        }

        if (self::isGDAvailable()) {
            $backends[] = 'gd';
        }

        return $backends;
    }
}
