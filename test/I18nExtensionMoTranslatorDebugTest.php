<?php

declare(strict_types=1);

/*
 * (c) 2021 phpMyAdmin contributors
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PhpMyAdmin\Tests\Twig\Extensions\Node;

use PhpMyAdmin\MoTranslator\Loader;
use PhpMyAdmin\Tests\Twig\Extensions\MoTranslator\I18nExtensionDebug;
use PhpMyAdmin\Twig\Extensions\I18nExtension;
use PHPUnit\Framework\Attributes\Group;
use Twig\Extension\ExtensionInterface;
use Twig\Test\IntegrationTestCase;

#[Group('integration')]
class I18nExtensionMoTranslatorDebugTest extends IntegrationTestCase
{
    public static function setUpBeforeClass(): void
    {
        Loader::loadFunctions();
    }

    /** @return ExtensionInterface[] */
    public function getExtensions(): array
    {
        return [
            new I18nExtensionDebug(),
        ];
    }

    protected static function getFixturesDirectory(): string
    {
        return __DIR__ . '/Fixtures/';
    }

    public function testGetName(): void
    {
        $this->assertNotEmpty((new I18nExtension())->getName());
    }
}
