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
     * @param string      $label       The label (optional).
     * @param array       $options     The options (optional). Valid options are: 'script-nonce'.
     *
     * @return string The result as a script printing to console.
     */
    public function dumpFunction(Environment $environment, $var, $label = '', $options = []): string
    {
        if (!$environment->isDebug()) {
            return '';
        }

        $content = [];
        if ($label !== '') {
            $content[] = [self::escapeString(strval($label)), self::STYLE_NAME];
        }

        $scriptNonce = strval($options['script-nonce'] ?? '');
        $result =
            '<script' . ($scriptNonce !== '' ? ' nonce="' . htmlentities($scriptNonce) . '"' : '') . '>' .
            self::varToLogString($var, $content, []) .
            '</script>';

        return $result;
    }

    /**
     * Converts a variable into a log string.
     *
     * @param mixed   $var             The variable.
     * @param array   $content         Optional content to insert before result.
     * @param mixed[] $previousObjects The previous processed objects.
     *
     * @return string The log string.
     */
    private static function varToLogString($var, $content, $previousObjects): string
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
            return self::arrayToLogString($var, $content, $previousObjects);
        }

        // Object.
        if (is_object($var)) {
            $reflectionClass = new \ReflectionClass($var);

            return self::objectToLogString($reflectionClass, $var, $content, true, $previousObjects);
        }

        // Other type.
        $content[] = [gettype($var), self::STYLE_TYPE];

        return self::toConsoleLog($content);
    }

    /**
     * Converts an array into a log string.
     *
     * @param array   $arr             The array.
     * @param array   $content         Optional content to insert before result.
     * @param mixed[] $previousObjects The previous processed objects.
     *
     * @return string The log string.
     */
    private static function arrayToLogString(array $arr, array $content, array $previousObjects): string
    {
        $size = count($arr);

        $content[] = ['array[' . $size . ']', self::STYLE_TYPE];

        $result = self::toConsoleLog($content, $size > 0);
        if ($size === 0) {
            return $result;
        }

        foreach ($arr as $key => $value) {
            $keyContent = [];
            if (is_string($key)) {
                $keyContent[] = ['"' . self::escapeString($key) . '"', self::STYLE_STRING_VALUE];
            } else {
                $keyContent[] = [$key, self::STYLE_VALUE];
            }

            $keyContent[] = ['=>', self::STYLE_ARROW];
            $result .= self::varToLogString($value, $keyContent, $previousObjects);
        }

        $result .= 'console.groupEnd();';

        return $result;
    }

    /**
     * Converts an object into log string.
     *
     * @param \ReflectionClass $reflectionClass   The reflection class of the object.
     * @param mixed            $obj               The object.
     * @param array            $content           Optional content to insert before result.
     * @param bool             $showDisplayString If true, show a string representation of object.
     * @param mixed[]          $previousObjects   The previous processed objects.
     *
     * @return string The log string.
     */
    private static function objectToLogString(\ReflectionClass $reflectionClass, $obj, array $content, bool $showDisplayString, array $previousObjects): string
    {
        // String representation of object.
        if ($showDisplayString) {
            $asString = self::objectToString($obj);
            if ($asString !== null) {
                $content[] = ['"' . self::escapeString($asString) . '"', self::STYLE_STRING_VALUE];
            }
        }

        // Class name header.
        $content[] = [self::escapeString($reflectionClass->getName()), self::STYLE_TYPE];

        // Stuck in infinite recursion loop?
        foreach ($previousObjects as $previousObject) {
            if ($previousObject === $obj) {
                $content[] = ['recursion', self::STYLE_NOTE];

                return self::toConsoleLog($content);
            }
        }

        // Does this object contain anything?
        $parentClass = $reflectionClass->getParentClass();
        /** @var \ReflectionProperty[] $reflectionProperties */
        $reflectionProperties = array_filter($reflectionClass->getProperties(), function (\ReflectionProperty $rp) use ($reflectionClass) {
            return $rp->getDeclaringClass()->getName() === $reflectionClass->getName();
        });
        $isEmpty = $parentClass === false && count($reflectionProperties) === 0;

        // Class header.
        $result = self::toConsoleLog($content, !$isEmpty);

        if ($isEmpty) {
            return $result;
        }

        // Parent class.
        if ($parentClass !== false) {
            $result .= self::objectToLogString($parentClass, $obj, [['parent', self::STYLE_NOTE]], false, $previousObjects);
        }

        // This object should not be processed again.
        $previousObjects[] = $obj;

        // Properties.
        $nonStaticProperties = [];
        $staticProperties = [];
        foreach ($reflectionProperties as $reflectionProperty) {
            if ($reflectionProperty->isStatic()) {
                $staticProperties[] = $reflectionProperty;
            } else {
                $nonStaticProperties[] = $reflectionProperty;
            }
        }

        $result .= self::objectPropertiesToLogString($nonStaticProperties, $obj, $previousObjects);

        if (count($staticProperties) !== 0) {
            $result .= self::toConsoleLog([['static', self::STYLE_NOTE]], true);
            $result .= self::objectPropertiesToLogString($staticProperties, $obj, $previousObjects);
            $result .= 'console.groupEnd();';
        }

        $result .= 'console.groupEnd();';

        return $result;
    }

    /**
     * Converts object properties into log string.
     *
     * @param \ReflectionProperty[] $reflectionProperties The reflection properties.
     * @param mixed                 $obj                  The object.
     * @param mixed[]               $previousObjects      The previous processed objects.
     *
     * @return string The log string.
     */
    private static function objectPropertiesToLogString(array $reflectionProperties, $obj, array $previousObjects): string
    {
        $result = '';

        foreach ($reflectionProperties as $reflectionProperty) {
            $reflectionProperty->setAccessible(true);
            $propertyContent = [];

            $visibility = '';
            if ($reflectionProperty->isPublic()) {
                $visibility = 'public';
            } elseif ($reflectionProperty->isProtected()) {
                $visibility = 'protected';
            } elseif ($reflectionProperty->isPrivate()) {
                $visibility = 'private';
            }

            $propertyContent[] = [$visibility, self::STYLE_NOTE];
            $propertyContent[] = [self::escapeString($reflectionProperty->getName()), self::STYLE_NAME];
            $result .= self::varToLogString($reflectionProperty->getValue($obj), $propertyContent, $previousObjects);
        }

        return $result;
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

        if ($obj instanceof \DateTimeInterface) {
            return $obj->format('Y-m-d H:i:s O');
        }

        if ($obj instanceof \DateInterval) {
            return $obj->format('%yy %mm %dd %hh %im %ss');
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

    /**
     * Styles for note (e.g. Base class).
     */
    private const STYLE_NOTE = 'color:#555;font-weight:400;font-style:italic';
}
