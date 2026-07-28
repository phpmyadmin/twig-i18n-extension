<?php

declare(strict_types=1);

/*
 * This file is part of Twig.
 *
 * (c) Fabien Potencier
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PhpMyAdmin\Tests\Twig\Extensions;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Twig\Compiler;
use Twig\Environment;
use Twig\Loader\ArrayLoader;
use Twig\Node\Node;

use function sprintf;
use function trim;
use function version_compare;

abstract class NodeTestCase extends TestCase
{
    private Environment|null $currentEnv = null;

    /**
     * Twig 3.26 encodes single quotes as \x27 (not ') in compiled string literals
     * as a defense-in-depth measure, so the expected output differs by Twig version.
     */
    protected static function apostrophe(): string
    {
        /** @phpstan-ignore-next-line */
        return version_compare(Environment::VERSION, '3.26.0', '>=') ? '\\x27' : '\'';
    }

    /** @return iterable<array{0: Node, 1: string, 2?: Environment|null, 3?: bool}> */
    abstract public static function provideTests(): iterable;

    #[DataProvider('provideTests')]
    public function testCompile(
        Node $node,
        string $source,
        Environment|null $environment = null,
        bool $isPattern = false,
    ): void {
        $this->assertNodeCompilation($source, $node, $environment, $isPattern);
    }

    public function assertNodeCompilation(
        string $source,
        Node $node,
        Environment|null $environment = null,
        bool $isPattern = false,
    ): void {
        $compiler = $this->getCompiler($environment);
        $compiler->compile($node);

        if ($isPattern) {
            self::assertStringMatchesFormat($source, trim($compiler->getSource()));
        } else {
            self::assertEquals($source, trim($compiler->getSource()));
        }
    }

    protected function getCompiler(Environment|null $environment = null): Compiler
    {
        return new Compiler($environment ?? $this->getEnvironment());
    }

    final protected function getEnvironment(): Environment
    {
        return $this->currentEnv ??= static::createEnvironment();
    }

    protected static function createEnvironment(): Environment
    {
        return new Environment(new ArrayLoader());
    }

    final protected static function createVariableGetter(string $name, bool $line = false): string
    {
        $line = $line > 0 ? '// line ' . $line . "\n" : '';

        return sprintf('%s($context["%s"] ?? null)', $line, $name);
    }

    final protected static function createAttributeGetter(): string
    {
        return 'CoreExtension::getAttribute($this->env, $this->source, ';
    }
}
