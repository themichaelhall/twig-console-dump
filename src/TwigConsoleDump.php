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

        return '<script>' . self::varToLogString($var) . '</script>';
    }

    /**
     * Converts a variable into a log string.
     *
     * @param mixed $var The variable.
     *
     * @return string The log string.
     */
    private static function varToLogString($var): string
    {
        if (is_null($var)) {
            return 'console.log(\'%cnull\',\'color:#555;font-weight:400\');';
        }

        if (is_bool($var)) {
            return 'console.log(\'%c' . ($var ? 'true' : 'false') . ' %cbool\',\'color:#608;font-weight:600;\',\'color:#555;font-weight:400\');';
        }

        if (is_int($var)) {
            return 'console.log(\'%c' . $var . ' %cint\',\'color:#608;font-weight:600;\',\'color:#555;font-weight:400\');';
        }

        return 'console.log(\'' . self::escapeString(print_r($var, true)) . '\');';
    }

    /**
     * Escapes a string for console logging.
     *
     * @param string $s The original string.
     *
     * @return string The escaped string.
     */
    private static function escapeString(string $s): string
    {
        return str_replace(
            ['\\', '<', '>', '\'', "\n", "\r", '%'],
            ['\\\\', '\\<', '\\>', '\\\'', '\\n', '\\r', '\\%'],
            $s
        );
    }
}
