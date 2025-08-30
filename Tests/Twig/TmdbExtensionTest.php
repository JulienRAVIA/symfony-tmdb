<?php

namespace Tmdb\SymfonyBundle\Tests\Twig;

use PHPUnit\Framework\TestCase;
use Tmdb\Client;
use Tmdb\Model\Configuration;
use Tmdb\Model\Image;
use Tmdb\Repository\ConfigurationRepository;
use Tmdb\SymfonyBundle\Twig\TmdbExtension;
use PHPUnit\Framework\Attributes as PHPUnit;

class TmdbExtensionTest extends TestCase
{
    #[PHPUnit\Group('Twig')]
    public function testTwigExtension(): void
    {
        $client = $this->createMock(Client::class);
        $responseData = json_decode(
            file_get_contents(__DIR__ . '/../../Resources/test/configuration.json'),
            true
        );

        $configuration = new Configuration();
        $configuration->setImages($responseData['images']);

        $extension = new TmdbExtension($client, $configuration);

        $image = new Image();
        $image
            ->setAspectRatio(1)
            ->setFilePath('/foo.jpg')
            ->setHeight(null)
            ->setWidth(null)
            ->setIso6391('foobar')
            ->setMedia('dunno')
            ->setVoteAverage(4.7)
            ->setVoteCount(666);

        $this->assertSame('//image.tmdb.org/t/p/original/foo.jpg', $extension->getUrl($image));
        $this->assertSame(
            '<img src="//image.tmdb.org/t/p/original/foo.jpg" width="" height="" title="" alt=""/>',
            $extension->getHtml($image)
        );
        $this->assertSame('tmdb_extension', $extension->getName());
        $this->assertCount(2, $extension->getFilters());
    }

    #[PHPUnit\Group('Twig')]
    public function testRepository(): void
    {
        $client = $this->createMock(Client::class);
        $responseData = json_decode(
            file_get_contents(__DIR__ . '/../../Resources/test/configuration.json'),
            true
        );

        $configuration = new Configuration();
        $configuration->setImages($responseData['images']);

        $repository = $this->getMockBuilder(ConfigurationRepository::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['load', 'getApi', 'getFactory'])
            ->getMock()
        ;

        $repository->method('load')->willReturn($configuration);

        $extension = new TmdbExtension($client, $repository->load());

        $image = new Image();
        $image
            ->setAspectRatio(1)
            ->setFilePath('/foo.jpg')
            ->setHeight(null)
            ->setWidth(null)
            ->setIso6391('foobar')
            ->setMedia('dunno')
            ->setVoteAverage(4.7)
            ->setVoteCount(666);

        $this->assertSame('//image.tmdb.org/t/p/original/foo.jpg', $extension->getUrl($image));
        $this->assertSame(
            '<img src="//image.tmdb.org/t/p/original/foo.jpg" width="" height="" title="" alt=""/>',
            $extension->getHtml($image)
        );
        $this->assertSame('tmdb_extension', $extension->getName());
        $this->assertCount(2, $extension->getFilters());
    }
}
