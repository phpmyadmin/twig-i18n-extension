<?php

/*
 * (c) 2021 phpMyAdmin contributors
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PhpMyAdmin\Tests\Twig\Extensions\Node;

use PhpMyAdmin\Twig\Extensions\I18nExtension;
use Twig\Test\IntegrationTestCase;
use Twig\Extension\AbstractExtension;
use Twig\Extension\SandboxExtension;
use Twig\Sandbox\SecurityPolicy;

class I18nExtensionSandboxTest extends IntegrationTestCase
{
    /**
     * @return AbstractExtension[]
     */
    public function getExtensions()
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

    /**
     * @return string
     */
    public function getFixturesDir()
    {
        return __DIR__ . '/FixturesWithSandbox/';
    }

    public function testGetName(): void
    {
        $this->assertNotEmpty((new I18nExtension())->getName());
    }
}