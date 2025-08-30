<?php

namespace Tmdb\SymfonyBundle\Tests\DependencyInjection;

use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Tmdb\SymfonyBundle\DependencyInjection\TmdbSymfonyExtension;
use PHPUnit\Framework\Attributes as PHPUnit;

final class TmdbSymfonyExtensionTest extends TestCase
{
    private const DEPENDENCY_INJECTION_GROUP = 'DependencyInjection';
    
    #[PHPUnit\Group(self::DEPENDENCY_INJECTION_GROUP)]
    #[PHPUnit\Test]
    public function testDefaultConfigurationWithoutApiKeyThrowsException(): void
    {
        $this->expectException(InvalidConfigurationException::class);
        $loader = new TmdbSymfonyExtension();
        $config = $this->getEmptyConfig();
        $loader->load([$config], new ContainerBuilder());
    }

    #[PHPUnit\Group(self::DEPENDENCY_INJECTION_GROUP)]
    #[PHPUnit\Test]
    public function testDefaultConfigurationWithApiKey(): void
    {
        $this->container = new ContainerBuilder();
        $loader = new TmdbSymfonyExtension();
        $config = $this->getMinimalConfig();
        $loader->load([$config], $this->container);

        $this->assertHasDefinition('Tmdb\Client');
        $this->assertHasDefinition('Tmdb\Repository\MovieRepository');
        $this->assertHasDefinition('Tmdb\SymfonyBundle\Twig\TmdbExtension');
    }

    #[PHPUnit\Group(self::DEPENDENCY_INJECTION_GROUP)]
    #[PHPUnit\Test]
    public function testDefaultConfigurationHasLegacyAliases(): void
    {
        $this->container = new ContainerBuilder();
        $loader = new TmdbSymfonyExtension();
        $config = $this->getMinimalConfig();
        $loader->load([$config], $this->container);

        $this->assertAlias($this->container, 'Tmdb\Client', 'tmdb.client');
        $this->assertAlias($this->container, 'Tmdb\Repository\MovieRepository', 'tmdb.movie_repository');
        $this->assertAlias($this->container, 'Tmdb\SymfonyBundle\Twig\TmdbExtension', 'tmdb.twig.image_extension');
    }

    #[PHPUnit\Group(self::DEPENDENCY_INJECTION_GROUP)]
    #[PHPUnit\Test]
    public function testDisablingRepositories(): void
    {
        $this->container = new ContainerBuilder();
        $loader = new TmdbSymfonyExtension();
        $config = $this->getMinimalConfig();
        $config['repositories']['enabled'] = false;
        $loader->load([$config], $this->container);

        $this->assertAlias($this->container, 'Tmdb\Client', 'tmdb.client');
        $this->assertNotAlias('tmdb.movie_repository');
        $this->assertNotHasDefinition('Tmdb\Repository\MovieRepository');
    }

    #[PHPUnit\Group(self::DEPENDENCY_INJECTION_GROUP)]
    #[PHPUnit\Test]
    public function testDisablingTwig(): void
    {
        $this->container = new ContainerBuilder();
        $loader = new TmdbSymfonyExtension();
        $config = $this->getMinimalConfig();
        $config['twig_extension']['enabled'] = false;
        $loader->load([$config], $this->container);

        $this->assertAlias($this->container, 'Tmdb\Client', 'tmdb.client');
        $this->assertHasDefinition('Tmdb\Repository\MovieRepository');
        $this->assertNotHasDefinition('Tmdb\SymfonyBundle\Twig\TmdbExtension');
    }

    #[PHPUnit\Group(self::DEPENDENCY_INJECTION_GROUP)]
    #[PHPUnit\Test]
    public function testDisablingLegacyAliasesRemovesLegacyAliases(): void
    {
        $this->container = new ContainerBuilder();
        $loader = new TmdbSymfonyExtension();
        $config = $this->getMinimalConfig();
        $config['disable_legacy_aliases'] = true;
        $loader->load([$config], $this->container);

        $this->assertNotAlias('tmdb.client');
        $this->assertNotAlias('tmdb.movie_repository');
        $this->assertNotAlias('tmdb.twig.image_extension');
    }

    #[PHPUnit\Group(self::DEPENDENCY_INJECTION_GROUP)]
    #[PHPUnit\Test]
    public function testLegacyMappingMapsCorrectly(): void
    {
        $this->container = new ContainerBuilder();
        $loader = new TmdbSymfonyExtension();
        $config = $this->getMinimalConfig();
        $loader->load([$config], $this->container);

        foreach ($loader->getLegacyAliasMapping() as $group => $mapping) {
            foreach ($mapping as $alias => $serviceIdentifier) {
                $this->assertHasDefinition($serviceIdentifier);
                $this->assertAlias($this->container, $serviceIdentifier, $alias);
            }
        }
    }
}
