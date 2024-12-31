<?php

declare(strict_types=1);

namespace GameboyCameraImageExtractor\Tests;

use GameboyCameraImageExtractor\ImageExtractor;
use PHPUnit\Framework\TestCase;
use function base64_encode;
use function imagetruecolortopalette;
use function ob_get_contents;
use function ob_start;
use function sprintf;
use function var_dump;

final class ImageExtractorTest extends TestCase
{
    private readonly ImageExtractor $imageExtractor;

    public function setUp() : void
    {
        $this->imageExtractor = new ImageExtractor();
    }

    public function testGetImages() : void
    {
        $images = $this->imageExtractor->extract(__DIR__ . '/../Fixtures/test.sav');

        foreach ($images as $key => $image) {
            $testImagePath = sprintf('%s/../Fixtures/images/png/output_%d.png', __DIR__, ($key + 1));

            imagetruecolortopalette($image, true, 4);

            ob_start();
            imagepng($image);
            $imageData = ob_get_contents();
            ob_end_clean();

            $this->assertEquals(base64_encode($imageData), base64_encode(file_get_contents($testImagePath)));
        }
    }
}
