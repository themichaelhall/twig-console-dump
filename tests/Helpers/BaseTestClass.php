<?php

declare(strict_types=1);

namespace MichaelHall\TwigConsoleDump\Tests\Helpers;

/**
 * A base class.
 */
class BaseTestClass extends AbstractBaseTestClass
{
    /**
     * BaseTestClass constructor.
     */
    public function __construct()
    {
        parent::__construct();

        self::$bar = 'Bar';
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        return self::$bar;
    }

    /**
     * @var string
     */
    protected static $bar;
}
