<?php

/*
 * This file is part of Twig I18n extension.
 *
 * (c) 2010-2019 Fabien Potencier
 * (c) 2019-2021 phpMyAdmin contributors
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PhpMyAdmin\Twig\Extensions\Node;

use Twig\Compiler;
use Twig\Node\CheckToStringNode;
use Twig\Node\Expression\AbstractExpression;
use Twig\Node\Expression\ConstantExpression;
use Twig\Node\Expression\FilterExpression;
use Twig\Node\Expression\NameExpression;
use Twig\Node\Expression\TempNameExpression;
use Twig\Node\Node;
use Twig\Node\PrintNode;
use function array_merge;
use function count;
use function sprintf;
use function str_replace;
use function trim;

/**
 * Represents a trans node.
 *
 * Author Fabien Potencier <fabien.potencier@symfony-project.com>
 */
class TransNode extends Node
{
    /** @var string */
    protected static $notesLabel = '// notes: ';

    public function __construct(Node $body, ?Node $plural, ?AbstractExpression $count, ?Node $notes, ?Node $domain = null, int $lineno = 0, ?string $tag = null)
    {
        $nodes = ['body' => $body];
        if ($count !== null) {
            $nodes['count'] = $count;
        }
        if ($plural !== null) {
            $nodes['plural'] = $plural;
        }
        if ($notes !== null) {
            $nodes['notes'] = $notes;
        }
        if ($domain !== null) {
            $nodes['domain'] = $domain;
        }

        parent::__construct($nodes, [], $lineno, $tag);
    }

    /**
     * {@inheritdoc}
     */
    public function compile(Compiler $compiler)
    {
        $compiler->addDebugInfo($this);

        [$msg, $vars] = $this->compileString($this->getNode('body'));

        $hasPlural = $this->hasNode('plural');

        if ($hasPlural) {
            [$msg1, $vars1] = $this->compileString($this->getNode('plural'));

            $vars = array_merge($vars, $vars1);
        }

        $hasDomain = $this->hasNode('domain');

        $function = $this->getTransFunction($hasPlural, $hasDomain);

        if ($this->hasNode('notes')) {
            $message = trim($this->getNode('notes')->getAttribute('data'));

            // line breaks are not allowed cause we want a single line comment
            $message = str_replace(["\n", "\r"], ' ', $message);
            $compiler->write(static::$notesLabel . $message . "\n");
        }

        if ($vars) {
            $compiler
                ->write('echo strtr(' . $function . '(');

            if ($hasDomain) {
                [$domain] = $this->compileString($this->getNode('domain'));
                $compiler
                    ->subcompile($domain)
                    ->raw(', ');
            }

            $compiler
                ->subcompile($msg);

            if ($hasPlural) {
                $compiler
                    ->raw(', ')
                    ->subcompile($msg1)
                    ->raw(', abs(')
                    ->subcompile($this->getNode('count'))
                    ->raw(')');
            }

            $compiler->raw('), array(');

            foreach ($vars as $var) {
                if ($var->getAttribute('name') === 'count') {
                    $compiler
                        ->string('%count%')
                        ->raw(' => abs(')
                        ->subcompile($this->getNode('count'))
                        ->raw('), ');
                } else {
                    $compiler
                        ->string('%' . $var->getAttribute('name') . '%')
                        ->raw(' => ')
                        ->subcompile($var)
                        ->raw(', ');
                }
            }

            $compiler->raw("));\n");
        } else {
            $compiler
                ->write('echo ' . $function . '(');

            if ($hasDomain) {
                [$domain] = $this->compileString($this->getNode('domain'));
                $compiler
                    ->subcompile($domain)
                    ->raw(', ');
            }

            $compiler
                ->subcompile($msg);

            if ($hasPlural) {
                $compiler
                    ->raw(', ')
                    ->subcompile($msg1)
                    ->raw(', abs(')
                    ->subcompile($this->getNode('count'))
                    ->raw(')');
            }

            $compiler->raw(");\n");
        }
    }

    /**
     * Keep this method protected instead of private
     * Twig/I18n/NodeTrans from phpmyadmin/phpmyadmin uses it
     */
    protected function compileString(Node $body): array
    {
        if ($body instanceof NameExpression || $body instanceof ConstantExpression || $body instanceof TempNameExpression) {
            return [$body, []];
        }

        $vars = [];
        if (count($body)) {
            $msg = '';

            foreach ($body as $node) {
                if ($node instanceof PrintNode) {
                    $n = $node->getNode('expr');
                    while ($n instanceof FilterExpression) {
                        $n = $n->getNode('node');
                    }
                    while ($n instanceof CheckToStringNode) {
                        $n = $n->getNode('expr');
                    }
                    $msg .= sprintf('%%%s%%', $n->getAttribute('name'));
                    $vars[] = new NameExpression($n->getAttribute('name'), $n->getTemplateLine());
                } else {
                    $msg .= $node->getAttribute('data');
                }
            }
        } else {
            $msg = $body->getAttribute('data');
        }

        return [new Node([new ConstantExpression(trim($msg), $body->getTemplateLine())]), $vars];
    }

    private function getTransFunction(bool $plural, bool $hasDomain): string
    {
        if ($plural) {
            return $hasDomain ? 'dngettext' : 'ngettext';
        }

        return $hasDomain ? 'dgettext' : 'gettext';
    }
}
