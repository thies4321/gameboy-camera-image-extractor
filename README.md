# gameboy-camera-image-extractor
This is a PHP library for extracting Game Boy Camera photos from a save ram (.sav) file and writing them in various image formats.

The library requires the GD php extension to work.

## Basic usage

To just extract the images and store them on disk use the image writer:
```php
$imageWriter = new \GameboyCameraImageExtractor\ImageWriter();
$imageWriter->extractAndStoreToDisk('/path/to/file.sav', '/output/directory');
```

By default, it outputs the images in PNG format. There are multiple adapter available for different image formats.
For example, if you want to output the images in webp format you can use the Webp adapter:

```php
$webpAdapter = new \GameboyCameraImageExtractor\Writer\Webp();
$imageWriter = new \GameboyCameraImageExtractor\ImageWriter($webpAdapter);
$imageWriter->extractAndStoreToDisk('/path/to/file.sav', '/output/directory');
```

It will output the images in webp format. You can also extend `\GameboyCameraImageExtractor\Writer\Writer` and write
you own implementation for your specific needs.

If you want to get the images without writing them to disk you can use the image extractor:

```php
$imageExtractor = new \GameboyCameraImageExtractor\ImageExtractor();
$imageExtractor->extract('/path/to/file.sav');
```

This will return an array of `\GdImage` objects.

## Palettes

You can also use palletes to give the image a bit more color.

Basic usage:

```php
$palette = new \GameboyCameraImageExtractor\Entity\Palette('#ffffff', '#cccccc', '#666666', '#000000');
$imageWriter = new \GameboyCameraImageExtractor\ImageWriter();
$imageWriter->extractAndStoreToDisk('/path/to/file.sav', '/output/directory', $palette);
```

There are some presets, I might add some more at a later time.

```php
$preset = \GameboyCameraImageExtractor\Enum\PalettePreset::GameboyPlayer;
$palette = \GameboyCameraImageExtractor\Entity\Palette::createForPreset($preset);
```

## Credits

I could not have made this without the works of raphnet: https://github.com/raphnet/gbcam2png
