<?php

declare(strict_types=1);

/*
 * This file is part of Twig I18n extension.
 *
 * (c) 2010-2019 Fabien Potencier
 * (c) 2019-2021 phpMyAdmin contributors
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PhpMyAdmin\Twig\Extensions;

use PhpMyAdmin\Twig\Extensions\TokenParser\TransTokenParser;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

use function dgettext;
use function gettext;

class I18nExtension extends AbstractExtension
{
    /**
     * {@inheritdoc}
     */
    public function getTokenParsers(): array
    {
        return [new TransTokenParser()];
    }

    /**
     * {@inheritdoc}
     */
    public function getFilters(): array
    {
        return [
            new TwigFilter('trans', [$this, 'translate']), /* Note, the filter does not handle plurals */
        ];
    }

    public function getName(): string
    {
        return 'i18n';
    }

    /**
     * Translate a GetText string via filter
     *
     * @param string      $message The message to translate
     * @param string|null $domain  The GetText domain
     */
    public function translate(string $message, string|null $domain = null): string
    {
        /* If we don't have a domain, assume we're just using the default */
        if ($domain === null) {
            return gettext($message);
        }

        /* Otherwise specify where the message comes from */
        return dgettext($domain, $message);
    }
}
