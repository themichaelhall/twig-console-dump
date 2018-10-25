<?php

declare(strict_types=1);

namespace MichaelHall\TwigConsoleDump\Tests\Helpers;

/**
 * A derived test class.
 */
class DerivedTestClass extends BaseTestClass
{
    /**
     * DerivedTestClass constructor.
     */
    public function __construct()
    {
        parent::__construct();

        $this->baz = 12345;
    }

    /**
     * @var int
     */
    public $baz;
}
