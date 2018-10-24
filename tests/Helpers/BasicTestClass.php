<?php

declare(strict_types=1);

namespace MichaelHall\TwigConsoleDump\Tests\Helpers;

/**
 * A basic test class with variuos properties.
 */
class BasicTestClass
{
    /**
     * BasicTestClass constructor.
     */
    public function __construct()
    {
        $this->publicVar = 'Foo';
        $this->protectedVar = false;
        $this->privateVar = 100.25;

        self::$publicStaticVar = 42;
        self::$protectedStaticVar = ['Bar', 'Baz'];
        self::$privateStaticVar = null;
    }

    /**
     * @var string
     */
    public $publicVar;

    /**
     * @var bool
     */
    protected $protectedVar;

    /**
     * @var float
     */
    private $privateVar;

    /**
     * @var int
     */
    public static $publicStaticVar;

    /**
     * @var string[]
     */
    protected static $protectedStaticVar;

    /**
     * @var null
     */
    private static $privateStaticVar;
}
