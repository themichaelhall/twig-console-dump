<?php

declare(strict_types=1);

namespace MichaelHall\TwigConsoleDump\Tests\Helpers;

/**
 * A test class that contains a recursive reference.
 */
class Recursive1TestClass
{
    /**
     * Recursive1TestClass constructor.
     *
     * @param Recursive2TestClass $recursive2
     */
    public function __construct(Recursive2TestClass $recursive2)
    {
        $this->recursive2 = $recursive2;
    }

    /**
     * @var Recursive2TestClass
     */
    private $recursive2;
}
