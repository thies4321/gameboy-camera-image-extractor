<?php

declare(strict_types=1);

namespace GameboyCameraImageExtractor\Enum;

enum PalettePreset
{
    case BlackAndWhite;
    case Gameboy;
    case GameboyPlayer;
    case GameboyColorUp;
    case GameboyColorUpA;
    case GameboyColorUpB;
    case GameboyColorDown;
    case GameboyColorDownA;
    case GameboyColorDownB;
    case GameboyColorLeft;
    case GameboyColorLeftA;
    case GameboyColorLeftB;
    case GameboyColorRight;
    case GameboyColorRightA;
    case GameboyColorRightB;

    public function getColors() : array
    {
        return match ($this) {
            self::BlackAndWhite => ['#ffffff', '#cccccc', '#666666', '#000000'],
            self::Gameboy => ['#9bbc0f', '#8bac0f', '#306230', '#0f380f'],
            self::GameboyPlayer => ['#ffffff', '#ffc000', '#a06000', '#000000'],
            self::GameboyColorUp => ['#ffffff', '#f3b070', '#7a3612', '#000000'],
            self::GameboyColorUpA => ['#ffffff', '#ef8a87', '#89403d', '#000000'],
            self::GameboyColorUpB => ['#fbe7c9', '#c69e88', '#806b34', '#543312'],
            self::GameboyColorDown => ['#fffeb0', '#f19997', '#9496f8', '#000000'],
            self::GameboyColorDownA => ['#ffffff', '#fffe54', '#ea3323', '#000000'],
            self::GameboyColorDownB => ['#ffffff', '#fffe54', '#744c17', '#000000'],
            self::GameboyColorLeft => ['#ffffff', '#72a5f8', '#001df5', '#000000'],
            self::GameboyColorLeftA => ['#ffffff', '#8d90d7', '#525389', '#000000'],
            self::GameboyColorLeftB => ['#ffffff', '#a6a5a6', '#525252', '#000000'],
            self::GameboyColorRight => ['#ffffff', '#88fa4d', '#eb5228', '#000000'],
            self::GameboyColorRightA => ['#ffffff', '#9efb5b', '#2863be', '#000000'],
            self::GameboyColorRightB => ['#000000', '#398283', '#fade4b', '#ffffff'],
        };
    }
}
