<?php

/*
 * (c) 2021 phpMyAdmin contributors
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace PhpMyAdmin\Tests\Twig\Extensions\MoTranslator;

use PhpMyAdmin\Twig\Extensions\I18nExtension as TwigI18nExtension;
use Twig\TokenParser\TokenParserInterface;

class I18nExtensionMoTranslator extends TwigI18nExtension
{
    /**
     * Returns the token parser instances to add to the existing list.
     *
     * @return TokenParserInterface[]
     */
    public function getTokenParsers()
    {
        return [new TokenParserTrans()];
    }
}
