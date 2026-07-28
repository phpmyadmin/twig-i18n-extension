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
use Twig\Attribute\YieldReady;
use Twig\Compiler;
use Twig\Environment;
use Twig\Loader\ArrayLoader;
use Twig\Node\Expression\NameExpression;
use Twig\Node\Expression\Variable\ContextVariable;
use Twig\Node\Node;

use function class_exists;
use function sprintf;
use function trim;
use function version_compare;

// phpcs:disable SlevomatCodingStandard.Namespaces.UnusedUses.MismatchingCaseSensitivity

abstract class NodeTestCase extends TestCase
{
    /** @var Environment|null */
    private $currentEnv = null;

    protected static function echoOrYield(): string
    {
        return class_exists(YieldReady::class) ? 'yield' : 'echo';
    }

    /**
     * Twig 3.26 encodes single quotes as \x27 (not ') in compiled string literals
     * as a defense-in-depth measure, so the expected output differs by Twig version.
     */
    protected static function apostrophe(): string
    {
        /** @phpstan-ignore-next-line */
        return version_compare(Environment::VERSION, '3.26.0', '>=') ? '\\x27' : '\'';
    }

    protected static function createContextVariable(string $name, int $lineno): NameExpression
    {
        if (class_exists(ContextVariable::class)) {
            /** @phpstan-ignore-next-line */
            return new ContextVariable($name, $lineno);
        }

        return new NameExpression($name, $lineno);
    }

    /** @return iterable<array{0: Node, 1: string, 2?: Environment|null, 3?: bool}> */
    abstract public static function provideTests(): iterable;

    /** @dataProvider provideTests */
    #[DataProvider('provideTests')]
    public function testCompile(
        Node $node,
        string $source,
        ?Environment $environment = null,
        bool $isPattern = false
    ): void {
        $this->assertNodeCompilation($source, $node, $environment, $isPattern);
    }

    public function assertNodeCompilation(
        string $source,
        Node $node,
        ?Environment $environment = null,
        bool $isPattern = false
    ): void {
        $compiler = $this->getCompiler($environment);
        $compiler->compile($node);

        if ($isPattern) {
            self::assertStringMatchesFormat($source, trim($compiler->getSource()));
        } else {
            self::assertEquals($source, trim($compiler->getSource()));
        }
    }

    protected function getCompiler(?Environment $environment = null): Compiler
    {
        return new Compiler($environment ?? $this->getEnvironment());
    }

    final protected function getEnvironment(): Environment
    {
        $this->currentEnv = $this->currentEnv ?? static::createEnvironment();

        return $this->currentEnv;
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
