<?php

/*
 * (c) 2021 phpMyAdmin contributors
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PhpMyAdmin\Tests\Twig\Extensions\Node;

use PhpMyAdmin\Tests\Twig\Extensions\motranslator\I18nExtensionMoTranslator;
use Twig\Test\IntegrationTestCase;
use Twig\Extension\AbstractExtension;
use PhpMyAdmin\MoTranslator\Loader;

class I18nExtensionMoTranslatorTest extends IntegrationTestCase
{
    public static function setUpBeforeClass(): void
    {
        Loader::loadFunctions();
    }

    /**
     * @return AbstractExtension[]
     */
    public function getExtensions()
    {
        return [
            new I18nExtensionMoTranslator(),
        ];
    }

    /**
     * @return string
     */
    public function getFixturesDir()
    {
        return __DIR__ . '/Fixtures/';
    }
}
