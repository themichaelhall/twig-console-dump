<?php
/**
 * This file is a part of the twig-console-dump package.
 *
 * Read more at https://github.com/themichaelhall/twig-console-dump
 */
declare(strict_types=1);

namespace MichaelHall\TwigConsoleDump;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

/**
 * Twig console dump extension.
 *
 * @since 1.0.0
 */
class TwigConsoleDump extends AbstractExtension
{
    /** @noinspection PhpMissingParentCallCommonInspection */

    /**
     * Returns the filters.
     *
     * @since 1.0.0
     *
     * @return TwigFilter[] The filters.
     */
    public function getFilters(): array
    {
        return [
            new TwigFilter('dump', [$this, 'dumpFilter'], ['is_safe' => ['html']]),
        ];
    }

    /**
     * The dump filter.
     *
     * @since 1.0.0
     *
     * @param mixed $var The variable to dump.
     *
     * @return string The result as a script printing to console.
     */
    public function dumpFilter($var): string
    {
        return '<script>console.log(\'' . htmlentities(print_r($var, true)) . '\');</script>';
    }
}
