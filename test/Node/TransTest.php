<?php

declare(strict_types=1);

/*
 * This file is part of Twig.
 *
 * (c) 2010-2019 Fabien Potencier
 * (c) 2019-2021 phpMyAdmin contributors
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PhpMyAdmin\Tests\Twig\Extensions\Node;

use PhpMyAdmin\Tests\Twig\Extensions\NodeTestCase;
use PhpMyAdmin\Twig\Extensions\Node\TransNode;
use Twig\Environment;
use Twig\Node\CheckToStringNode;
use Twig\Node\Expression\ConstantExpression;
use Twig\Node\Expression\FilterExpression;
use Twig\Node\PrintNode;
use Twig\Node\TextNode;
use Twig\TwigFilter;

use function sprintf;
use function version_compare;

final class TransTest extends NodeTestCase
{
    /** @return ConstantExpression */
    public static function getFilter(string $filterName, int $lineno)
    {
        /** @phpstan-ignore-next-line */
        if (version_compare(Environment::VERSION, '3.12.0', '>=')) {
            /** @phpstan-ignore-next-line */
            return new TwigFilter($filterName);
        }

        return new ConstantExpression($filterName, $lineno);
    }

    public function testConstructor(): void
    {
        $count = new ConstantExpression(12, 0);
        $body = new Nodes([
            new TextNode('Hello', 0),
        ]);
        $plural = new Nodes([
            new TextNode('Hey ', 0),
            new PrintNode(self::createContextVariable('name', 0), 0),
            new TextNode(', I have ', 0),
            new PrintNode(self::createContextVariable('count', 0), 0),
            new TextNode(' apples', 0),
        ]);
        $node = new TransNode($body, $plural, $count, null, null, null, 0);

        self::assertEquals($body, $node->getNode('body'));
        self::assertEquals($count, $node->getNode('count'));
        self::assertEquals($plural, $node->getNode('plural'));
    }

    public function testConstructorWithDomain(): void
    {
        $count = new ConstantExpression(12, 0);
        $body = new Nodes([
            new TextNode('Hello', 0),
        ]);
        $domain = new Nodes([
            new TextNode('coredomain', 0),
        ]);
        $plural = new Nodes([
            new TextNode('Hey ', 0),
            new PrintNode(self::createContextVariable('name', 0), 0),
            new TextNode(', I have ', 0),
            new PrintNode(self::createContextVariable('count', 0), 0),
            new TextNode(' apples', 0),
        ]);
        $node = new TransNode($body, $plural, $count, null, null, $domain, 0);

        self::assertEquals($body, $node->getNode('body'));
        self::assertEquals($count, $node->getNode('count'));
        self::assertEquals($plural, $node->getNode('plural'));
        self::assertEquals($domain, $node->getNode('domain'));
    }

    public function testEnableDebugNotEnabled(): void
    {
        $count = new ConstantExpression(5, 0);
        $body = new TextNode('There is 1 pending task', 0);
        $plural = new Nodes([
            new TextNode('There are ', 0),
            new PrintNode(self::createContextVariable('count', 0), 0),
            new TextNode(' pending tasks', 0),
        ]);
        $notes = new TextNode('Notes for translators', 0);
        TransNode::$enableAddDebugInfo = false;
        TransNode::$notesLabel = '// custom: ';
        $node = new TransNode($body, $plural, $count, null, $notes, null, 80);

        $compiler = $this->getCompiler();
        self::assertEmpty($compiler->getDebugInfo());
        $sourceCode = $compiler->compile($node)->getSource();
        self::assertSame(
            '// custom: Notes for translators' . "\n"
            . self::echoOrYield() . ' strtr(ngettext("There is 1 pending task",'
            . ' "There are %count% pending tasks", abs(5)), array("%count%" => abs(5), ));' . "\n",
            $sourceCode
        );
        self::assertSame([], $compiler->getDebugInfo());
        TransNode::$enableAddDebugInfo = false;
        TransNode::$notesLabel = '// notes: ';
    }

    public function testEnableDebugEnabled(): void
    {
        $count = new ConstantExpression(5, 0);
        $body = new TextNode('There is 1 pending task', 0);
        $plural = new Nodes([
            new TextNode('There are ', 0),
            new PrintNode(self::createContextVariable('count', 0), 0),
            new TextNode(' pending tasks', 0),
        ]);
        $notes = new TextNode('Notes for translators', 0);

        TransNode::$enableAddDebugInfo = true;
        TransNode::$notesLabel = '// custom: ';
        $node = new TransNode($body, $plural, $count, null, $notes, null, 80);

        $compiler = $this->getCompiler();
        self::assertEmpty($compiler->getDebugInfo());
        $sourceCode = $compiler->compile($node)->getSource();
        self::assertSame(
            '// line 80' . "\n" . '// custom: Notes for translators' . "\n"
            . self::echoOrYield() . ' strtr(ngettext("There'
            . ' is 1 pending task", "There are %count% pending tasks", abs(5)), array("%count%" => abs(5), ));' . "\n",
            $sourceCode
        );
        self::assertSame([2 => 80], $compiler->getDebugInfo());
        TransNode::$enableAddDebugInfo = false;
        TransNode::$notesLabel = '// notes: ';
    }

    /** {@inheritDoc} */
    public static function provideTests(): iterable
    {
        $tests = [];

        $body = self::createContextVariable('foo', 0);
        $domain = new Nodes([
            new TextNode('coredomain', 0),
        ]);
        $node = new TransNode($body, null, null, null, null, $domain, 0);
        $tests[] = [
            $node,
            sprintf(self::echoOrYield() . ' dgettext("coredomain", %s);', self::createVariableGetter('foo')),
        ];

        $body = self::createContextVariable('foo', 0);
        $node = new TransNode($body, null, null, null, null, null, 0);
        $tests[] = [$node, sprintf(self::echoOrYield() . ' gettext(%s);', self::createVariableGetter('foo'))];

        $body = new ConstantExpression('Hello', 0);
        $node = new TransNode($body, null, null, null, null, null, 0);
        $tests[] = [$node, self::echoOrYield() . ' gettext("Hello");'];

        $body = new Nodes([
            new TextNode('Hello', 0),
        ]);
        $node = new TransNode($body, null, null, null, null, null, 0);
        $tests[] = [$node, self::echoOrYield() . ' gettext("Hello");'];

        $body = new Nodes([
            new TextNode('J\'ai ', 0),
            new PrintNode(self::createContextVariable('foo', 0), 0),
            new TextNode(' pommes', 0),
        ]);
        $node = new TransNode($body, null, null, null, null, null, 0);
        $tests[] = [
            $node,
            sprintf(
                self::echoOrYield() . ' strtr(gettext("J' . self::apostrophe()
                    . 'ai %%foo%% pommes"), array("%%foo%%" => %s, ));',
                self::createVariableGetter('foo')
            ),
        ];

        $count = new ConstantExpression(12, 0);
        $body = new Nodes([
            new TextNode('Hey ', 0),
            new PrintNode(self::createContextVariable('name', 0), 0),
            new TextNode(', I have one apple', 0),
        ]);
        $plural = new Nodes([
            new TextNode('Hey ', 0),
            new PrintNode(self::createContextVariable('name', 0), 0),
            new TextNode(', I have ', 0),
            new PrintNode(self::createContextVariable('count', 0), 0),
            new TextNode(' apples', 0),
        ]);
        $node = new TransNode($body, $plural, $count, null, null, null, 0);
        $tests[] = [
            $node,
            sprintf(
                self::echoOrYield() . ' strtr(ngettext("Hey %%name%%, I have one apple", "Hey %%name%%, I have'
                . ' %%count%% apples", abs(12)), array("%%name%%" => %s,'
                . ' "%%name%%" => %s, "%%count%%" => abs(12), ));',
                self::createVariableGetter('name'),
                self::createVariableGetter('name')
            ),
        ];

        // with escaper extension set to on
        $body = new Nodes([
            new TextNode('J\'ai ', 0),
            new PrintNode(
                new FilterExpression(
                    self::createContextVariable('foo', 0),
                    self::getFilter('escape', 0),
                    new Nodes(),
                    0
                ),
                0
            ),
            new TextNode(' pommes', 0),
        ]);

        $node = new TransNode($body, null, null, null, null, null, 0);
        $tests[] = [
            $node,
            sprintf(
                self::echoOrYield() . ' strtr(gettext("J' . self::apostrophe()
                    . 'ai %%foo%% pommes"), array("%%foo%%" => %s, ));',
                self::createVariableGetter('foo')
            ),
        ];

        // sandbox + auto-escape (Twig 3.26+): SandboxNodeVisitor wraps the escape FilterExpression
        // in CheckToStringNode, so the print expr becomes CTS(FE(escape, foo)).
        $body = new Nodes([
            new TextNode('J\'ai ', 0),
            new PrintNode(
                new CheckToStringNode(
                    new FilterExpression(
                        self::createContextVariable('foo', 0),
                        self::getFilter('escape', 0),
                        new Nodes(),
                        0
                    )
                ),
                0
            ),
            new TextNode(' pommes', 0),
        ]);

        $node = new TransNode($body, null, null, null, null, null, 0);
        $tests[] = [
            $node,
            sprintf(
                self::echoOrYield() . ' strtr(gettext("J' . self::apostrophe()
                    . 'ai %%foo%% pommes"), array("%%foo%%" => %s, ));',
                self::createVariableGetter('foo')
            ),
        ];

        // sandbox + auto-escape on a filtered variable (e.g. {{ foo|upper }}) produces
        // CTS(FE(escape, FE(upper, foo))) — the unwrap must strip every FE/CTS layer.
        $body = new Nodes([
            new TextNode('J\'ai ', 0),
            new PrintNode(
                new CheckToStringNode(
                    new FilterExpression(
                        new FilterExpression(
                            self::createContextVariable('foo', 0),
                            self::getFilter('upper', 0),
                            new Nodes(),
                            0
                        ),
                        self::getFilter('escape', 0),
                        new Nodes(),
                        0
                    )
                ),
                0
            ),
            new TextNode(' pommes', 0),
        ]);

        $node = new TransNode($body, null, null, null, null, null, 0);
        $tests[] = [
            $node,
            sprintf(
                self::echoOrYield() . ' strtr(gettext("J' . self::apostrophe()
                    . 'ai %%foo%% pommes"), array("%%foo%%" => %s, ));',
                self::createVariableGetter('foo')
            ),
        ];

        // with notes
        $body = new ConstantExpression('Hello', 0);
        $notes = new TextNode('Notes for translators', 0);
        $node = new TransNode($body, null, null, null, $notes, null, 0);
        $tests[] = [$node, "// notes: Notes for translators\n" . self::echoOrYield() . ' gettext("Hello");'];

        $body = new ConstantExpression('Hello', 0);
        $notes = new TextNode("Notes for translators\nand line breaks", 0);
        $node = new TransNode($body, null, null, null, $notes, null, 0);
        $tests[] = [
            $node,
            "// notes: Notes for translators and line breaks\n"
            . self::echoOrYield() . ' gettext("Hello");',
        ];

        $count = new ConstantExpression(5, 0);
        $body = new TextNode('There is 1 pending task', 0);
        $plural = new Nodes([
            new TextNode('There are ', 0),
            new PrintNode(self::createContextVariable('count', 0), 0),
            new TextNode(' pending tasks', 0),
        ]);
        $notes = new TextNode('Notes for translators', 0);
        $node = new TransNode($body, $plural, $count, null, $notes, null, 0);
        $tests[] = [
            $node,
            '// notes: Notes for translators' . "\n"
            . self::echoOrYield() . ' strtr(ngettext("There is 1 pending task",'
            . ' "There are %count% pending tasks", abs(5)), array("%count%" => abs(5), ));',
        ];

        return $tests;
    }
}
