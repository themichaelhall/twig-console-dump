<?php

declare(strict_types=1);

namespace MichaelHall\TwigConsoleDump\Tests\Helpers;

/**
 * An abstract base class.
 */
abstract class AbstractBaseTestClass
{
    /**
     * AbstractBaseTestClass constructor.
     */
    public function __construct()
    {
        $this->foo = 'Foo';
    }

    /**
     * @var string
     */
    private $foo;
}
