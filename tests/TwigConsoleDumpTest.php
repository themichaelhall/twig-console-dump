<?php

declare(strict_types=1);

namespace MichaelHall\TwigConsoleDump\Tests;

use MichaelHall\TwigConsoleDump\TwigConsoleDump;
use PHPUnit\Framework\TestCase;
use Twig\Environment;
use Twig\Loader\ArrayLoader;

/**
 * Test TwigConsoleDump extension.
 */
class TwigConsoleDumpTest extends TestCase
{
    /**
     * Test dump extension.
     */
    public function testDump()
    {
        $result = $this->twigEnvironment->render('test.twig', ['var' => 'Foo']);

        self::assertSame('<script>console.log(\'Foo\');</script>', $result);
    }

    /**
     * Set up.
     */
    public function setUp()
    {
        $arrayLoader = new ArrayLoader([
            'test.twig' => '{{ var | dump }}',
        ]);
        $this->twigEnvironment = new Environment($arrayLoader);
        $this->twigEnvironment->addExtension(new TwigConsoleDump());
    }

    /**
     * Tear down.
     */
    public function tearDown()
    {
        $this->twigEnvironment = null;
    }

    /**
     * @var Environment My Twig environment.
     */
    private $twigEnvironment;
}
