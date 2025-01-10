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

use PhpMyAdmin\Twig\Extensions\Node\TransNode;
use Twig\Node\Expression\ConstantExpression;
use Twig\Node\Expression\Variable\ContextVariable;
use Twig\Node\Nodes;
use Twig\Node\PrintNode;
use Twig\Node\TextNode;
use Twig\Test\NodeTestCase;

use function sprintf;

class MoTranslatorTransTest extends NodeTestCase
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
            new PrintNode(new ContextVariable('name', 0), 0),
            new TextNode(', I have ', 0),
            new PrintNode(new ContextVariable('count', 0), 0),
            new TextNode(' apples', 0),
        ]);
        $node = new TransNode($body, $plural, $count, $context, $notes, $domain, 0);

        $this->assertEquals($body, $node->getNode('body'));
        $this->assertEquals($count, $node->getNode('count'));
        $this->assertEquals($plural, $node->getNode('plural'));
        $this->assertEquals($notes, $node->getNode('notes'));
        $this->assertEquals($domain, $node->getNode('domain'));
        $this->assertEquals($context, $node->getNode('context'));
    }

    /** @return iterable<array{0: \Twig\Node\Node, 1: string, 2?: Environment|null, 3?: bool}> */
    public static function provideTests(): iterable
    {
        $tests = [];

        $body = new ContextVariable('foo', 0);
        $domain = new Nodes([
            new TextNode('coredomain', 0),
        ]);
        $node = new TransNode($body, null, null, null, null, $domain, 0);
        $tests[] = [
            $node,
            sprintf('yield _dgettext("coredomain", %s);', self::createVariableGetter('foo')),
        ];

        $body = new ContextVariable('foo', 0);
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
                'yield _dpgettext("coredomain", "The context", %s);',
                self::createVariableGetter('foo'),
            ),
        ];

        $body = new Nodes([
            new TextNode('J\'ai ', 0),
            new PrintNode(new ContextVariable('foo', 0), 0),
            new TextNode(' pommes', 0),
        ]);
        $node = new TransNode($body, null, null, null, null, null, 0);
        $tests[] = [
            $node,
            sprintf(
                'yield strtr(_gettext("J\'ai %%foo%% pommes"), array("%%foo%%" => %s, ));',
                self::createVariableGetter('foo'),
            ),
        ];

        $count = new ConstantExpression(12, 0);
        $body = new Nodes([
            new TextNode('Hey ', 0),
            new PrintNode(new ContextVariable('name', 0), 0),
            new TextNode(', I have one apple', 0),
        ]);
        $plural = new Nodes([
            new TextNode('Hey ', 0),
            new PrintNode(new ContextVariable('name', 0), 0),
            new TextNode(', I have ', 0),
            new PrintNode(new ContextVariable('count', 0), 0),
            new TextNode(' apples', 0),
        ]);
        $node = new TransNode($body, $plural, $count, null, null, null, 0);
        $tests[] = [
            $node,
            sprintf(
                'yield strtr(_ngettext("Hey %%name%%, I have one apple", "Hey %%name%%,'
                . ' I have %%count%% apples", abs(12)), array("%%name%%" => %s,'
                . ' "%%name%%" => %s, "%%count%%" => abs(12), ));',
                self::createVariableGetter('name'),
                self::createVariableGetter('name'),
            ),
        ];

        $body = new Nodes([
            new TextNode('J\'ai ', 0),
            new PrintNode(new ContextVariable('foo', 0), 0),
            new TextNode(' pommes', 0),
        ]);
        $context = new Nodes([
            new TextNode('The context', 0),
        ]);
        $node = new TransNode($body, null, null, $context, null, null, 0);
        $tests[] = [
            $node,
            sprintf(
                'yield strtr(_pgettext("The context", "J\'ai %%foo%% pommes"), array("%%foo%%" => %s, ));',
                self::createVariableGetter('foo'),
            ),
        ];

        $count = new ConstantExpression(12, 0);
        $body = new Nodes([
            new TextNode('Hey ', 0),
            new PrintNode(new ContextVariable('name', 0), 0),
            new TextNode(', I have one apple', 0),
        ]);
        $context = new Nodes([
            new TextNode('The context', 0),
        ]);
        $plural = new Nodes([
            new TextNode('Hey ', 0),
            new PrintNode(new ContextVariable('name', 0), 0),
            new TextNode(', I have ', 0),
            new PrintNode(new ContextVariable('count', 0), 0),
            new TextNode(' apples', 0),
        ]);
        $node = new TransNode($body, $plural, $count, $context, null, null, 0);
        $tests[] = [
            $node,
            sprintf(
                'yield strtr(_npgettext("The context", "Hey %%name%%, I have one apple", "Hey %%name%%,'
                . ' I have %%count%% apples", abs(12)), array("%%name%%" => %s,'
                . ' "%%name%%" => %s, "%%count%%" => abs(12), ));',
                self::createVariableGetter('name'),
                self::createVariableGetter('name'),
            ),
        ];

        $body = new Nodes([
            new TextNode('J\'ai ', 0),
            new PrintNode(new ContextVariable('foo', 0), 0),
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
                'yield strtr(_dpgettext("mydomain", "The context", "J\'ai %%foo%% pommes"), array("%%foo%%" => %s, ));',
                self::createVariableGetter('foo'),
            ),
        ];

        $count = new ConstantExpression(12, 0);
        $body = new Nodes([
            new TextNode('Hey ', 0),
            new PrintNode(new ContextVariable('name', 0), 0),
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
            new PrintNode(new ContextVariable('name', 0), 0),
            new TextNode(', I have ', 0),
            new PrintNode(new ContextVariable('count', 0), 0),
            new TextNode(' apples', 0),
        ]);
        $node = new TransNode($body, $plural, $count, $context, null, $domain, 0);
        $tests[] = [
            $node,
            sprintf(
                'yield strtr(_dnpgettext("mydomain", "The context", "Hey %%name%%, I have one apple",'
                . ' "Hey %%name%%, I have %%count%% apples", abs(12)), array("%%name%%" => %s,'
                . ' "%%name%%" => %s, "%%count%%" => abs(12), ));',
                self::createVariableGetter('name'),
                self::createVariableGetter('name'),
            ),
        ];

        return $tests;
    }
}
