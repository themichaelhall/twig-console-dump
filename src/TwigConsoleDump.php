<?php
/**
 * This file is a part of the twig-console-dump package.
 *
 * Read more at https://github.com/themichaelhall/twig-console-dump
 */
declare(strict_types=1);

namespace MichaelHall\TwigConsoleDump;

use Twig\Environment;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

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
     * @return TwigFunction[] The filters.
     */
    public function getFunctions(): array
    {
        return [
            new TwigFunction('dump', [$this, 'dumpFunction'], ['is_safe' => ['html'], 'needs_environment' => true]),
        ];
    }

    /**
     * The dump function.
     *
     * @since 1.0.0
     *
     * @param Environment $environment The Twig environment.
     * @param mixed       $var         The variable to dump.
     *
     * @return string The result as a script printing to console.
     */
    public function dumpFunction(Environment $environment, $var): string
    {
        if (!$environment->isDebug()) {
            return '';
        }

        return '<script>console.log(\'' . htmlentities(print_r($var, true)) . '\');</script>';
    }
}
