<?php

declare(strict_types=1);

namespace GameboyCameraImageExtractor\Enum;

enum PalettePreset
{
    case BlackAndWhite;
    case Gameboy;
    case GameboyPlayer;

    public function getColors() : array
    {
        return match ($this) {
            self::BlackAndWhite => ['#ffffff', '#cccccc', '#666666', '#000000'],
            self::Gameboy => ['#9bbc0f', '#8bac0f', '#306230', '#0f380f'],
            self::GameboyPlayer => ['#ffffff', '#ffc000', '#a06000', '#000000'],
        };
    }
}
