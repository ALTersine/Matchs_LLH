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
        $this->container = static::getContainer()->get(ContainerBagInterface::class);
        $this->service = new GameTypeDispatcher($this->container);
    }

    private function getCsv(string $name): string
    {
        return $this->container->get('app.test_import_directory') . '/' . $name;
    }

    private function testData(array $tab, bool $isResult): void
    {
        $this->assertArrayHasKey(
            'games',
            $tab,
            'Tableau games manquant'
        );

        foreach ($tab['games'] as $game) {
            $gameToTest = $game;
            if ($isResult) {
                unset($gameToTest['Forfait'], $gameToTest['sc rec'], $gameToTest['sc vis']);
            }
            foreach ($gameToTest as $value) {
                $this->assertNotSame('', $value, 'Des données sont vides');
                $this->assertNotSame(null, $value, 'Des données sont nules');
            }
        }

        $clubs = array_merge(
            array_column($tab['games'], 'club rec'),
            array_column($tab['games'], 'club vis')
        );
        $this->assertContains(
            'LE LANDREAU HANDBALL',
            $clubs,
            'Référence LE LANDREAU HANDBALL non retrouvé dans la qualification des clubs'
        );

        foreach ($tab['games'] as $game) {
            $this->assertMatchesRegularExpression(
                '/^\d{2}\/\d{2}\/\d{4}$/',
                $game['le'],
                'La date n\'est pas au format JJ/MM/AAAA'
            );
        }

        foreach ($tab['games'] as $game) {
            $this->assertMatchesRegularExpression(
                '/^\d{2}:\d{2}:\d{2}$/',
                $game['horaire'],
                'L\'heure n\'est pas au format HH:MM:SS'
            );
        }
    }

    public function testForWeekendOff(): void
    {
        $resultats = $this->service->processCSVImport($this->getCsv('vide.csv'));
        $this->assertEquals(
            $this->container->get('app.label.game.absent'),
            $resultats['type'],
            'le type du tableau retourné est ' . $resultats['type']
        );
    }

    public function testIsImportForPreview(): void
    {
        $resultats = $this->service->processCSVImport($this->getCsv('preview.csv'));

        $this->assertEquals(
            $this->container->get('app.label.game.preview'),
            $resultats['type'],
            'le type du tableau retourné est ' . $resultats['type']
        );

        $this->testData($resultats, false);
    }

    public function testIsImportForResult(): void
    {
        $resultats = $this->service->processCSVImport($this->getCsv('result.csv'));

        $this->assertEquals(
            $this->container->get('app.label.game.result'),
            $resultats['type'],
            'le type du tableau retourné est ' . $resultats['type']
        );

        $this->testData($resultats, true);
    }

    public function testPreviewMissingHeader() : void {
        $this->expectException(CsvException::class);
        $this->service->processCSVImport($this->getCsv('previewError.csv'));
    }

    public function testResultMissingHeader() : void {
        $this->expectException(CsvException::class);
        $this->service->processCSVImport($this->getCsv('resultError.csv'));
    }
}
