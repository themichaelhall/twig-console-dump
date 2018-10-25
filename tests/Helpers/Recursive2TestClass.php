<?php

declare(strict_types=1);

namespace MichaelHall\TwigConsoleDump\Tests\Helpers;

/**
 * A test class that contains a recursive reference.
 */
class Recursive2TestClass
{
    /**
     * Recursive2TestClass constructor.
     */
    public function __construct()
    {
        $this->recursive1 = null;
    }

    /**
     * @var null|Recursive1TestClass
     */
    public $recursive1;
}
