<?php

declare(strict_types=1);

/*
 * (c) 2021 phpMyAdmin contributors
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PhpMyAdmin\Tests\Twig\Extensions;

use PhpMyAdmin\MoTranslator\Loader;
use PhpMyAdmin\Twig\Extensions\I18nExtension;
use Twig\Extension\SandboxExtension;
use Twig\Sandbox\SecurityPolicy;

final class I18nExtensionMoTranslatorSandboxTest extends IntegrationTestCase
{
    public static function setUpBeforeClass(): void
    {
        Loader::loadFunctions();
    }

    /** {@inheritDoc} */
    public function getExtensions(): array
    {
        $tags = ['if', 'set', 'trans'];
        $filters = ['upper', 'escape'];
        $methods = [];
        $properties = [];
        $functions = [];
        $policy = new SecurityPolicy($tags, $filters, $methods, $properties, $functions);

        return [
            new I18nExtension(),
            new SandboxExtension($policy, true),
        ];
    }

    protected static function getFixturesDirectory(): string
    {
        return __DIR__ . '/FixturesWithSandbox/';
    }

    public function testGetName(): void
    {
        self::assertNotEmpty((new I18nExtension())->getName());
    }
}
