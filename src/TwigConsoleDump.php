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
     * @param mixed $var     The variable.
     * @param array $content Optional content to insert before result.
     *
     * @return string The log string.
     */
    private static function varToLogString($var, $content = []): string
    {
        // Null.
        if (is_null($var)) {
            $content[] = ['null', self::STYLE_TYPE];

            return self::toConsoleLog($content);
        }

        // Bool.
        if (is_bool($var)) {
            $content[] = [$var ? 'true' : 'false', self::STYLE_VALUE];
            $content[] = ['bool', self::STYLE_TYPE];

            return self::toConsoleLog($content);
        }

        // Int.
        if (is_int($var)) {
            $content[] = [$var, self::STYLE_VALUE];
            $content[] = ['int', self::STYLE_TYPE];

            return self::toConsoleLog($content);
        }

        // Float.
        if (is_float($var)) {
            $content[] = [$var, self::STYLE_VALUE];
            $content[] = ['float', self::STYLE_TYPE];

            return self::toConsoleLog($content);
        }

        // String.
        if (is_string($var)) {
            $content[] = ['"' . self::escapeString($var) . '"', self::STYLE_STRING_VALUE];
            $content[] = ['string[' . strlen($var) . ']', self::STYLE_TYPE];

            return self::toConsoleLog($content);
        }

        // Array.
        if (is_array($var)) {
            $content[] = ['array[' . count($var) . ']', self::STYLE_TYPE];
            $result = self::toConsoleLog($content, true);
            foreach ($var as $key => $value) {
                $keyContent = [];
                if (is_string($key)) {
                    $keyContent[] = ['"' . self::escapeString($key) . '"', self::STYLE_STRING_VALUE];
                } else {
                    $keyContent[] = [$key, self::STYLE_VALUE];
                }

                $keyContent[] = ['=>', self::STYLE_ARROW];
                $result .= self::varToLogString($value, $keyContent);
            }
            $result .= 'console.groupEnd();';

            return $result;
        }

        // Object.
        if (is_object($var)) {
            $asString = self::objectToString($var);
            if ($asString !== null) {
                $content[] = ['"' . self::escapeString($asString) . '"', self::STYLE_STRING_VALUE];
            }

            $content[] = [self::escapeString(get_class($var)), self::STYLE_TYPE];
            $result = self::toConsoleLog($content, true);

            $reflectionClass = new \ReflectionClass($var);
            foreach ($reflectionClass->getProperties() as $reflectionProperty) {
                $reflectionProperty->setAccessible(true);
                $propertyContent = [];

                $propertyContent[] = [self::escapeString($reflectionProperty->getName()), self::STYLE_NAME];
                $result .= self::varToLogString($reflectionProperty->getValue($var), $propertyContent);
            }

            $result .= 'console.groupEnd();';

            return $result;
        }

        // Other type.
        $content[] = [gettype($var), self::STYLE_TYPE];

        return self::toConsoleLog($content);
    }

    /**
     * Creates a console log or group from log items.
     *
     * @param array $items   The log items.
     * @param bool  $isGroup If true, create a group, if false, create a log.
     *
     * @return string The console log or group.
     */
    private static function toConsoleLog(array $items, bool $isGroup = false): string
    {
        $texts = [];
        $styles = [];

        foreach ($items as $item) {
            $texts[] = '%c' . $item[0];
            $styles[] = '\'' . $item[1] . '\'';
        }

        return 'console.' . ($isGroup ? 'groupCollapsed' : 'log') . '(\'' . implode(' ', $texts) . '\',' . implode(',', $styles) . ');';
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
            ['\\\\', '\\<', '\\>', '\\\'', '\\n', '\\r', '%%'],
            $s
        );
    }

    /**
     * Returns an object as a string or null if object could not be converted to a string.
     *
     * @param mixed $obj The object.
     *
     * @return null|string The object as a string or null.
     */
    private static function objectToString($obj): ?string
    {
        if (method_exists($obj, '__toString')) {
            return $obj->__toString();
        }

        return null;
    }

    /**
     * Styles for type (e.g. null, int, \Foo\Bar\Baz).
     */
    private const STYLE_TYPE = 'color:#555;font-weight:400';

    /**
     * Styles for ordinary values (e.g false, 42, 10.5).
     */
    private const STYLE_VALUE = 'color:#608;font-weight:600';

    /**
     * Style for strings (e.g. "Foo").
     */
    private const STYLE_STRING_VALUE = 'color:#063;font-weight:600';

    /**
     * Styles for arrow (e.g. =>).
     */
    private const STYLE_ARROW = 'color:#555;font-weight:400';

    /**
     * Styles for name (e.g. Model, myVar).
     */
    private const STYLE_NAME = 'color:#00b;font-weight:400';
}
