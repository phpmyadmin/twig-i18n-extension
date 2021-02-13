<?php

/*
 * (c) 2021 phpMyAdmin contributors
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace PhpMyAdmin\Tests\Twig\Extensions\MoTranslator;

use PhpMyAdmin\Twig\Extensions\Node\TransNode;

class NodeTrans extends TransNode
{
    /**
     * The label for gettext notes to be exported
     *
     * @var string
     */
    protected static $notesLabel = '// l10n: ';

    /**
     * Enable MoTranslator functions
     *
     * @var bool
     */
    protected static $enableMoTranslator = true;
}
