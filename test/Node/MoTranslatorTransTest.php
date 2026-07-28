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
use Twig\Node\Expression\ConstantExpression;
use Twig\Node\PrintNode;
use Twig\Node\TextNode;

use function sprintf;

final class MoTranslatorTransTest extends NodeTestCase
{
    public static function setUpBeforeClass(): void
    {
        TransNode::$notesLabel = '// l10n: ';
        TransNode::$enableMoTranslator = true;
    }

    public static function tearDownAfterClass(): void
    {
        TransNode::$notesLabel = '// notes: ';
        TransNode::$enableMoTranslator = false;
    }

    public function testFullConstructor(): void
    {
        $count = new ConstantExpression(12, 0);
        $body = new Nodes([
            new TextNode('Hello', 0),
        ]);
        $notes = new Nodes([
            new TextNode('notes for translators', 0),
        ]);
        $domain = new Nodes([
            new TextNode('mydomain', 0),
        ]);
        $context = new Nodes([
            new TextNode('mydomain', 0),
        ]);
        $plural = new Nodes([
            new TextNode('Hey ', 0),
            new PrintNode(self::createContextVariable('name', 0), 0),
            new TextNode(', I have ', 0),
            new PrintNode(self::createContextVariable('count', 0), 0),
            new TextNode(' apples', 0),
        ]);
        $node = new TransNode($body, $plural, $count, $context, $notes, $domain, 0);

        self::assertEquals($body, $node->getNode('body'));
        self::assertEquals($count, $node->getNode('count'));
        self::assertEquals($plural, $node->getNode('plural'));
        self::assertEquals($notes, $node->getNode('notes'));
        self::assertEquals($domain, $node->getNode('domain'));
        self::assertEquals($context, $node->getNode('context'));
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
            sprintf(self::echoOrYield() . ' _dgettext("coredomain", %s);', self::createVariableGetter('foo')),
        ];

        $body = self::createContextVariable('foo', 0);
        $domain = new Nodes([
            new TextNode('coredomain', 0),
        ]);
        $context = new Nodes([
            new TextNode('The context', 0),
        ]);
        $node = new TransNode($body, null, null, $context, null, $domain, 0);
        $tests[] = [
            $node,
            sprintf(
                self::echoOrYield() . ' _dpgettext("coredomain", "The context", %s);',
                self::createVariableGetter('foo')
            ),
        ];

        $body = new Nodes([
            new TextNode('J\'ai ', 0),
            new PrintNode(self::createContextVariable('foo', 0), 0),
            new TextNode(' pommes', 0),
        ]);
        $node = new TransNode($body, null, null, null, null, null, 0);
        $tests[] = [
            $node,
            sprintf(
                self::echoOrYield() . ' strtr(_gettext("J' . self::apostrophe()
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
                self::echoOrYield() . ' strtr(_ngettext("Hey %%name%%, I have one apple", "Hey %%name%%,'
                . ' I have %%count%% apples", abs(12)), array("%%name%%" => %s,'
                . ' "%%name%%" => %s, "%%count%%" => abs(12), ));',
                self::createVariableGetter('name'),
                self::createVariableGetter('name')
            ),
        ];

        $body = new Nodes([
            new TextNode('J\'ai ', 0),
            new PrintNode(self::createContextVariable('foo', 0), 0),
            new TextNode(' pommes', 0),
        ]);
        $context = new Nodes([
            new TextNode('The context', 0),
        ]);
        $node = new TransNode($body, null, null, $context, null, null, 0);
        $tests[] = [
            $node,
            sprintf(
                self::echoOrYield()
                . ' strtr(_pgettext("The context", "J' . self::apostrophe()
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
        $context = new Nodes([
            new TextNode('The context', 0),
        ]);
        $plural = new Nodes([
            new TextNode('Hey ', 0),
            new PrintNode(self::createContextVariable('name', 0), 0),
            new TextNode(', I have ', 0),
            new PrintNode(self::createContextVariable('count', 0), 0),
            new TextNode(' apples', 0),
        ]);
        $node = new TransNode($body, $plural, $count, $context, null, null, 0);
        $tests[] = [
            $node,
            sprintf(
                self::echoOrYield()
                . ' strtr(_npgettext("The context", "Hey %%name%%, I have one apple", "Hey %%name%%,'
                . ' I have %%count%% apples", abs(12)), array("%%name%%" => %s,'
                . ' "%%name%%" => %s, "%%count%%" => abs(12), ));',
                self::createVariableGetter('name'),
                self::createVariableGetter('name')
            ),
        ];

        $body = new Nodes([
            new TextNode('J\'ai ', 0),
            new PrintNode(self::createContextVariable('foo', 0), 0),
            new TextNode(' pommes', 0),
        ]);
        $context = new Nodes([
            new TextNode('The context', 0),
        ]);
        $domain = new Nodes([
            new TextNode('mydomain', 0),
        ]);
        $node = new TransNode($body, null, null, $context, null, $domain, 0);
        $tests[] = [
            $node,
            sprintf(
                self::echoOrYield()
                . ' strtr(_dpgettext("mydomain", "The context", "J' . self::apostrophe()
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
        $context = new Nodes([
            new TextNode('The context', 0),
        ]);
        $domain = new Nodes([
            new TextNode('mydomain', 0),
        ]);
        $plural = new Nodes([
            new TextNode('Hey ', 0),
            new PrintNode(self::createContextVariable('name', 0), 0),
            new TextNode(', I have ', 0),
            new PrintNode(self::createContextVariable('count', 0), 0),
            new TextNode(' apples', 0),
        ]);
        $node = new TransNode($body, $plural, $count, $context, null, $domain, 0);
        $tests[] = [
            $node,
            sprintf(
                self::echoOrYield()
                . ' strtr(_dnpgettext("mydomain", "The context", "Hey %%name%%, I have one apple",'
                . ' "Hey %%name%%, I have %%count%% apples", abs(12)), array("%%name%%" => %s,'
                . ' "%%name%%" => %s, "%%count%%" => abs(12), ));',
                self::createVariableGetter('name'),
                self::createVariableGetter('name')
            ),
        ];

        return $tests;
    }
}
