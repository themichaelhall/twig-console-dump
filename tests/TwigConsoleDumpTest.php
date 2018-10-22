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
     * Test dump extension for scalars (and null).
     *
     * @dataProvider scalarsDataProvider
     *
     * @param mixed  $var            The variable to debug.
     * @param string $expectedResult The expected result.
     */
    public function testScalars($var, string $expectedResult)
    {
        $result = $this->twigEnvironment->render('test.twig', ['var' => $var]);

        self::assertSame($expectedResult, $result);
    }

    /**
     * Data provider for testScalars.
     *
     * @return array
     */
    public function scalarsDataProvider()
    {
        return [
            [null, '<script>console.log(\'\');</script>'],
            [false, '<script>console.log(\'\');</script>'],
            [true, '<script>console.log(\'1\');</script>'],
            [100, '<script>console.log(\'100\');</script>'],
            [-0.5, '<script>console.log(\'-0.5\');</script>'],
            ['Foo Bar Baz', '<script>console.log(\'Foo Bar Baz\');</script>'],
        ];
    }

    /**
     * Set up.
     */
    public function setUp()
    {
        $arrayLoader = new ArrayLoader([
            'test.twig' => '{{ dump(var) }}',
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
