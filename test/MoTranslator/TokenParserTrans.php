<?php

/*
 * (c) 2021 phpMyAdmin contributors
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace PhpMyAdmin\Tests\Twig\Extensions\MoTranslator;

use PhpMyAdmin\Twig\Extensions\TokenParser\TransTokenParser;
use Twig\Token;

class TokenParserTrans extends TransTokenParser
{
    /**
     * Parses a token and returns a node.
     *
     * @param Token $token Twig token to parse
     *
     * @return NodeTrans
     */
    public function parse(Token $token)
    {
        [
            $body,
            $plural,
            $count,
            $context,
            $notes,
            $domain,
            $lineno,
            $tag,
        ] = $this->preParse($token);

        return new NodeTrans($body, $plural, $count, $context, $notes, $domain, $lineno, $tag);
    }
}
