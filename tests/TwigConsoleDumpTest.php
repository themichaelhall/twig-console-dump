<?php

declare(strict_types=1);

namespace MichaelHall\TwigConsoleDump\Tests;

use MichaelHall\TwigConsoleDump\Tests\Helpers\BasicTestClass;
use MichaelHall\TwigConsoleDump\Tests\Helpers\DerivedTestClass;
use MichaelHall\TwigConsoleDump\Tests\Helpers\EmptyTestClass;
use MichaelHall\TwigConsoleDump\Tests\Helpers\Recursive1TestClass;
use MichaelHall\TwigConsoleDump\Tests\Helpers\Recursive2TestClass;
use MichaelHall\TwigConsoleDump\Tests\Helpers\StringableTestClass;
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
     * Test for scalars (and null).
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
            [null, '<script>console.log(\'%cnull\',\'color:#555;font-weight:400\');</script>'],
            [false, '<script>console.log(\'%cfalse %cbool\',\'color:#608;font-weight:600\',\'color:#555;font-weight:400\');</script>'],
            [true, '<script>console.log(\'%ctrue %cbool\',\'color:#608;font-weight:600\',\'color:#555;font-weight:400\');</script>'],
            [100, '<script>console.log(\'%c100 %cint\',\'color:#608;font-weight:600\',\'color:#555;font-weight:400\');</script>'],
            [-0.5, '<script>console.log(\'%c-0.5 %cfloat\',\'color:#608;font-weight:600\',\'color:#555;font-weight:400\');</script>'],
            ['Foo Bar Baz', '<script>console.log(\'%c\\\'Foo Bar Baz\\\' %cstring[11]\',\'color:#900;font-weight:600\',\'color:#555;font-weight:400\');</script>'],
        ];
    }

    /**
     * Test output is empty in non-debug mode.
     */
    public function testOutputIsEmptyInNonDebugMode()
    {
        $this->twigEnvironment->disableDebug();
        $result = $this->twigEnvironment->render('test.twig', ['var' => 'Foo']);

        self::assertSame('', $result);
    }

    /**
     * Test logging with escaped characters.
     */
    public function testEscapedCharacters()
    {
        $result = $this->twigEnvironment->render('test.twig', ['var' => '<p> \'Foo\' "Bar" \\ New' . "\r\n" . 'Line %c']);

        self::assertSame('<script>console.log(\'%c\\\'\\<p\\> \\\'Foo\\\' "Bar" \\\\ New\\r\\nLine %%c\\\' %cstring[30]\',\'color:#900;font-weight:600\',\'color:#555;font-weight:400\');</script>', $result);
    }

    /**
     * Test for an array.
     */
    public function testArray()
    {
        $result = $this->twigEnvironment->render('test.twig', ['var' => ['Foo' => 'Bar', 1 => [2, 'Baz' => false]]]);

        self::assertSame(
            '<script>' .
            'console.groupCollapsed(\'%carray[2]\',\'color:#555;font-weight:400\');' .
            'console.log(\'%c\\\'Foo\\\' %c=> %c\\\'Bar\\\' %cstring[3]\',\'color:#900;font-weight:600\',\'color:#555;font-weight:400\',\'color:#900;font-weight:600\',\'color:#555;font-weight:400\');' .
            'console.groupCollapsed(\'%c1 %c=> %carray[2]\',\'color:#608;font-weight:600\',\'color:#555;font-weight:400\',\'color:#555;font-weight:400\');' .
            'console.log(\'%c0 %c=> %c2 %cint\',\'color:#608;font-weight:600\',\'color:#555;font-weight:400\',\'color:#608;font-weight:600\',\'color:#555;font-weight:400\');' .
            'console.log(\'%c\\\'Baz\\\' %c=> %cfalse %cbool\',\'color:#900;font-weight:600\',\'color:#555;font-weight:400\',\'color:#608;font-weight:600\',\'color:#555;font-weight:400\');' .
            'console.groupEnd();' .
            'console.groupEnd();' .
            '</script>', $result
        );
    }

    /**
     * Test for an object.
     */
    public function testObject()
    {
        $result = $this->twigEnvironment->render('test.twig', ['var' => new BasicTestClass()]);

        self::assertSame(
            '<script>' .
            'console.groupCollapsed(\'%cMichaelHall\\\\TwigConsoleDump\\\\Tests\\\\Helpers\\\\BasicTestClass\',\'color:#555;font-weight:400\');' .
            'console.log(\'%cpublic %cpublicVar %c\\\'Foo\\\' %cstring[3]\',\'color:#555;font-weight:400;font-style:italic\',\'color:#00b;font-weight:400\',\'color:#900;font-weight:600\',\'color:#555;font-weight:400\');' .
            'console.log(\'%cprotected %cprotectedVar %cfalse %cbool\',\'color:#555;font-weight:400;font-style:italic\',\'color:#00b;font-weight:400\',\'color:#608;font-weight:600\',\'color:#555;font-weight:400\');' .
            'console.log(\'%cprivate %cprivateVar %c100.25 %cfloat\',\'color:#555;font-weight:400;font-style:italic\',\'color:#00b;font-weight:400\',\'color:#608;font-weight:600\',\'color:#555;font-weight:400\');' .
            'console.groupCollapsed(\'%cstatic\',\'color:#555;font-weight:400;font-style:italic\');' .
            'console.log(\'%cpublic %cpublicStaticVar %c42 %cint\',\'color:#555;font-weight:400;font-style:italic\',\'color:#00b;font-weight:400\',\'color:#608;font-weight:600\',\'color:#555;font-weight:400\');' .
            'console.groupCollapsed(\'%cprotected %cprotectedStaticVar %carray[2]\',\'color:#555;font-weight:400;font-style:italic\',\'color:#00b;font-weight:400\',\'color:#555;font-weight:400\');' .
            'console.log(\'%c0 %c=> %c\\\'Bar\\\' %cstring[3]\',\'color:#608;font-weight:600\',\'color:#555;font-weight:400\',\'color:#900;font-weight:600\',\'color:#555;font-weight:400\');' .
            'console.log(\'%c1 %c=> %c\\\'Baz\\\' %cstring[3]\',\'color:#608;font-weight:600\',\'color:#555;font-weight:400\',\'color:#900;font-weight:600\',\'color:#555;font-weight:400\');' .
            'console.groupEnd();' .
            'console.log(\'%cprivate %cprivateStaticVar %cnull\',\'color:#555;font-weight:400;font-style:italic\',\'color:#00b;font-weight:400\',\'color:#555;font-weight:400\');' .
            'console.groupEnd();' .
            'console.groupEnd();' .
            '</script>', $result
        );
    }

    /**
     * Test for a object with a __toString method.
     */
    public function testStringableObject()
    {
        $result = $this->twigEnvironment->render('test.twig', ['var' => new StringableTestClass('Foo')]);

        self::assertSame(
            '<script>' .
            'console.groupCollapsed(\'%c\\\'Hello from Foo\\\' %cMichaelHall\\\\TwigConsoleDump\\\\Tests\\\\Helpers\\\\StringableTestClass\',\'color:#900;font-weight:600\',\'color:#555;font-weight:400\');' .
            'console.log(\'%cprivate %clabel %c\\\'Foo\\\' %cstring[3]\',\'color:#555;font-weight:400;font-style:italic\',\'color:#00b;font-weight:400\',\'color:#900;font-weight:600\',\'color:#555;font-weight:400\');' .
            'console.groupEnd();' .
            '</script>', $result
        );
    }

    /**
     * Test with a label.
     */
    public function testWithLabel()
    {
        $result = $this->twigEnvironment->render('test-label.twig', ['var' => ['Foo' => 'Bar']]);

        self::assertSame(
            '<script>' .
            'console.groupCollapsed(\'%cLabel %carray[1]\',\'color:#00b;font-weight:400\',\'color:#555;font-weight:400\');' .
            'console.log(\'%c\\\'Foo\\\' %c=> %c\\\'Bar\\\' %cstring[3]\',\'color:#900;font-weight:600\',\'color:#555;font-weight:400\',\'color:#900;font-weight:600\',\'color:#555;font-weight:400\');' .
            'console.groupEnd();' .
            '</script>', $result
        );
    }

    /**
     * Test for a resource.
     */
    public function testResource()
    {
        $result = $this->twigEnvironment->render('test.twig', ['var' => opendir(sys_get_temp_dir())]);

        self::assertSame('<script>console.log(\'%cresource\',\'color:#555;font-weight:400\');</script>', $result);
    }

    /**
     * Test for derived class.
     */
    public function testDerived()
    {
        $result = $this->twigEnvironment->render('test.twig', ['var' => new DerivedTestClass()]);

        self::assertSame(
            '<script>' .
            'console.groupCollapsed(\'%c\\\'Bar\\\' %cMichaelHall\\\\TwigConsoleDump\\\\Tests\\\\Helpers\\\\DerivedTestClass\',\'color:#900;font-weight:600\',\'color:#555;font-weight:400\');' .
            'console.groupCollapsed(\'%cparent %cMichaelHall\\\\TwigConsoleDump\\\\Tests\\\\Helpers\\\\BaseTestClass\',\'color:#555;font-weight:400;font-style:italic\',\'color:#555;font-weight:400\');' .
            'console.groupCollapsed(\'%cparent %cMichaelHall\\\\TwigConsoleDump\\\\Tests\\\\Helpers\\\\AbstractBaseTestClass\',\'color:#555;font-weight:400;font-style:italic\',\'color:#555;font-weight:400\');' .
            'console.log(\'%cprivate %cfoo %c\\\'Foo\\\' %cstring[3]\',\'color:#555;font-weight:400;font-style:italic\',\'color:#00b;font-weight:400\',\'color:#900;font-weight:600\',\'color:#555;font-weight:400\');' .
            'console.groupEnd();' .
            'console.groupCollapsed(\'%cstatic\',\'color:#555;font-weight:400;font-style:italic\');' .
            'console.log(\'%cprotected %cbar %c\\\'Bar\\\' %cstring[3]\',\'color:#555;font-weight:400;font-style:italic\',\'color:#00b;font-weight:400\',\'color:#900;font-weight:600\',\'color:#555;font-weight:400\');' .
            'console.groupEnd();' .
            'console.groupEnd();' .
            'console.log(\'%cpublic %cbaz %c12345 %cint\',\'color:#555;font-weight:400;font-style:italic\',\'color:#00b;font-weight:400\',\'color:#608;font-weight:600\',\'color:#555;font-weight:400\');' .
            'console.groupEnd();' .
            '</script>', $result
        );
    }

    /**
     * Test with script-nonce.
     */
    public function testWithScriptNonce()
    {
        $result = $this->twigEnvironment->render('test-script-nonce.twig', ['var' => ['Foo' => 'Bar']]);

        self::assertSame(
            '<script nonce="abc">' .
            'console.groupCollapsed(\'%cLabel %carray[1]\',\'color:#00b;font-weight:400\',\'color:#555;font-weight:400\');' .
            'console.log(\'%c\\\'Foo\\\' %c=> %c\\\'Bar\\\' %cstring[3]\',\'color:#900;font-weight:600\',\'color:#555;font-weight:400\',\'color:#900;font-weight:600\',\'color:#555;font-weight:400\');' .
            'console.groupEnd();' .
            '</script>', $result
        );
    }

    /**
     * Test for a recursive object.
     */
    public function testRecursiveObject()
    {
        $recursive2 = new Recursive2TestClass();
        $recursive1 = new Recursive1TestClass($recursive2);
        $recursive2->recursive1 = $recursive1;

        $result = $this->twigEnvironment->render('test.twig', ['var' => $recursive1]);

        self::assertSame(
            '<script>' .
            'console.groupCollapsed(\'%cMichaelHall\\\\TwigConsoleDump\\\\Tests\\\\Helpers\\\\Recursive1TestClass\',\'color:#555;font-weight:400\');' .
            'console.groupCollapsed(\'%cprivate %crecursive2 %cMichaelHall\\\\TwigConsoleDump\\\\Tests\\\\Helpers\\\\Recursive2TestClass\',\'color:#555;font-weight:400;font-style:italic\',\'color:#00b;font-weight:400\',\'color:#555;font-weight:400\');' .
            'console.log(\'%cpublic %crecursive1 %cMichaelHall\\\\TwigConsoleDump\\\\Tests\\\\Helpers\\\\Recursive1TestClass %crecursion\',\'color:#555;font-weight:400;font-style:italic\',\'color:#00b;font-weight:400\',\'color:#555;font-weight:400\',\'color:#555;font-weight:400;font-style:italic\');' .
            'console.groupEnd();' .
            'console.groupEnd();' .
            '</script>', $result
        );
    }

    /**
     * Test with date classes (DateTime/DateTimeImmutable/DateInterval).
     */
    public function testDateClasses()
    {
        $result = $this->twigEnvironment->render('test.twig',
            [
                'var' => [
                    new \DateTime('2000-01-02 03:04:05', new \DateTimeZone('+0200')),
                    new \DateTimeImmutable('2006-07-08 09:10:11', new \DateTimeZone('-0400')),
                    new \DateInterval('P1Y2M3DT4H5M6S'),
                ],
            ]
        );

        self::assertSame(
            '<script>' .
            'console.groupCollapsed(\'%carray[3]\',\'color:#555;font-weight:400\');' .
            'console.log(\'%c0 %c=> %c\\\'2000-01-02 03:04:05 +0200\\\' %cDateTime\',\'color:#608;font-weight:600\',\'color:#555;font-weight:400\',\'color:#900;font-weight:600\',\'color:#555;font-weight:400\');' .
            'console.log(\'%c1 %c=> %c\\\'2006-07-08 09:10:11 -0400\\\' %cDateTimeImmutable\',\'color:#608;font-weight:600\',\'color:#555;font-weight:400\',\'color:#900;font-weight:600\',\'color:#555;font-weight:400\');' .
            'console.log(\'%c2 %c=> %c\\\'1y 2m 3d 4h 5m 6s\\\' %cDateInterval\',\'color:#608;font-weight:600\',\'color:#555;font-weight:400\',\'color:#900;font-weight:600\',\'color:#555;font-weight:400\');' .
            'console.groupEnd();' .
            '</script>', $result
        );
    }

    /**
     * Test with empty content.
     */
    public function testEmptyContent()
    {
        $result = $this->twigEnvironment->render('test.twig',
            [
                'var' => [
                    [],
                    new EmptyTestClass(),
                ],
            ]
        );

        self::assertSame(
            '<script>' .
            'console.groupCollapsed(\'%carray[2]\',\'color:#555;font-weight:400\');' .
            'console.log(\'%c0 %c=> %carray[0]\',\'color:#608;font-weight:600\',\'color:#555;font-weight:400\',\'color:#555;font-weight:400\');' .
            'console.log(\'%c1 %c=> %cMichaelHall\\\\TwigConsoleDump\\\\Tests\\\\Helpers\\\\EmptyTestClass\',\'color:#608;font-weight:600\',\'color:#555;font-weight:400\',\'color:#555;font-weight:400\');' .
            'console.groupEnd();' .
            '</script>', $result
        );
    }

    /**
     * Set up.
     */
    public function setUp()
    {
        $arrayLoader = new ArrayLoader([
            'test.twig'              => '{{ dump(var) }}',
            'test-label.twig'        => '{{ dump(var, \'Label\') }}',
            'test-script-nonce.twig' => '{{ dump(var, \'Label\', {\'script-nonce\':\'abc\'}) }}',
        ]);
        $this->twigEnvironment = new Environment($arrayLoader, ['debug' => true]);
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
