<?php

namespace App\Tests;

use App\Exception\CsvException;
use App\Service\GameTypeDispatcher;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\DependencyInjection\ParameterBag\ContainerBagInterface;

class DispatcherTest extends KernelTestCase
{
    private GameTypeDispatcher $service;
    private ContainerBagInterface $container;

    protected function setUp(): void
    {
        self::bootKernel();
        $this->service = new GameTypeDispatcher();
        $this->container = static::getContainer()->get(ContainerBagInterface::class);
    }

    public function testIsImportForPreview(): void
    {
        $resultat = $this->service->isImportingGamesPreview(
            $this->container->get('app.test_import_directory') . '/preview.csv'
        );
        $this->assertTrue($resultat);
    }

    public function testIsImportForResult(): void
    {
        $resultat = $this->service->isImportingGamesPreview(
            $this->container->get('app.test_import_directory') . '/result.csv'
        );
        $this->assertFalse($resultat);
    }

    public function missingRequiredHeader(): void
    {
        $this->service->isImportingGamesPreview(
            $this->container->get('app.test_import_directory') . '/previewError.csv'
        );
        $this->expectException(CsvException::class);
    }
    public function missingRequiredHeaderInResultType(): void
    {
        $this->service->isImportingGamesPreview(
            $this->container->get('app.test_import_directory') . '/resultrror.csv'
        );
        $this->expectException(CsvException::class);
    }
}
