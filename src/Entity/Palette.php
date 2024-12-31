<?php

declare(strict_types=1);

namespace GameboyCameraImageExtractor\Entity;

use GameboyCameraImageExtractor\Enum\PalettePreset;

final readonly class Palette
{
    public function __construct(
        public string $colorOne,
        public string $colorTwo,
        public string $colorThree,
        public string $colorFour,
    ) {
    }

    public function getColorForPixelType(int $pixelType): string
    {
        return match ($pixelType) {
            0 => $this->colorOne,
            1 => $this->colorTwo,
            2 => $this->colorThree,
            3 => $this->colorFour,
        };
    }

    public static function createForPreset(?PalettePreset $preset = null): self
    {
        if (! $preset instanceof PalettePreset) {
            $preset = PalettePreset::BlackAndWhite;
        }

        $colors = $preset->getColors();

        return new self(
            $colors[0],
            $colors[1],
            $colors[2],
            $colors[3],
        );
    }
}
