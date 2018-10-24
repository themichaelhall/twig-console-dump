<?php

declare(strict_types=1);

namespace MichaelHall\TwigConsoleDump\Tests\Helpers;

/**
 * A test class with a __toString method.
 */
class StringableTestClass
{
    /**
     * StringableTestClass constructor.
     *
     * @param string $label The label.
     */
    public function __construct(string $label)
    {
        $this->label = $label;
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        return 'Hello from ' . $this->label;
    }

    /**
     * @var string
     */
    private $label;
}
