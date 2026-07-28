<?php

declare(strict_types=1);

/*
 * (c) 2021 phpMyAdmin contributors
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PhpMyAdmin\Tests\Twig\Extensions;

use PhpMyAdmin\Twig\Extensions\I18nExtension;

final class I18nExtensionTest extends IntegrationTestCase
{
    /** {@inheritDoc} */
    public function getExtensions(): array
    {
        return [
            new I18nExtension(),
        ];
    }

    protected static function getFixturesDirectory(): string
    {
        return __DIR__ . '/Fixtures/';
    }

    public function testGetName(): void
    {
        self::assertNotEmpty((new I18nExtension())->getName());
    }
}
