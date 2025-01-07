<?php

declare(strict_types=1);

/*
 * This file is part of Twig.
 *
 * (c) Fabien Potencier
 * (c) phpMyAdmin contributors
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PhpMyAdmin\Tests\Twig\Extensions\Node;

use Twig\Attribute\YieldReady;
use Twig\Node\Node;

/**
 * Represents a list of nodes.
 */
#[YieldReady]
final class Nodes extends Node
{
    /** @param array<string|int, Node> $nodes */
    public function __construct(array $nodes = [], int $lineno = 0)
    {
        parent::__construct($nodes, [], $lineno);
    }
}
