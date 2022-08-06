<?php

declare(strict_types=1);

namespace Hoa\Ruler\Test\Unit;

use Hoa\Compiler;
use PHPUnit\Framework\TestCase;
use Hoa\Ruler as LUT;
use Hoa\Ruler\Test\Unit\TestableRuler as SUT;

class PHPUnitRulerTest extends TestCase
{
    protected function tearDown(): void
    {
        (new SUT())->destroy();
    }

    public function testAssert(): void
    {
        $rule = '7 < 42';
        $ruler = new SUT();
        $result = $ruler->assert($rule);

        $this->assertTrue($result);
    }

    public function testAssertWithAContext(): void
    {
        $rule = 'x < 42';
        $ruler = new SUT();
        $context = new LUT\Context();
        $context['x'] = 7;
        $result = $ruler->assert($rule, $context);

        $this->assertTrue($result);
    }

    public function testAssertWithRuleAsAModel(): void
    {
        $rule = SUT::interpret('x < 42');
        $ruler = new SUT();
        $context = new LUT\Context();
        $context['x'] = 7;
        $result = $ruler->assert($rule, $context);

        $this->assertTrue($result);
    }

    public function testInterpret(): void
    {
        $result = (new SUT())->interpret('x < 42');

        $this->assertInstanceOf(LUT\Model::class, $result);
    }

    public function testGetInterpret(): void
    {
        $result = (new SUT())->getInterpreter();

        $this->assertInstanceOf(LUT\Visitor\Interpreter::class, $result);
    }

    public function testSetAsserter(): void
    {
        $ruler = new SUT();
        $asserter = new LUT\Visitor\Asserter();
        $result = $ruler->setAsserter($asserter);

        $this->assertNull($result);
    }

    public function testGetAsserter(): void
    {
        $asserter = new LUT\Visitor\Asserter();
        $ruler = new SUT();
        $context = new LUT\Context();
        $ruler->setAsserter($asserter);
        $asserter->setContext($context);
        $result = $ruler->getAsserter();

        $this->assertSame($asserter, $result);
        $this->assertSame($context, $asserter->getContext());
    }

    public function testGetAsserterWithASpecificContext(): void
    {
        $asserter = new LUT\Visitor\Asserter();
        $contextA = new LUT\Context();
        $contextB = new LUT\Context();
        $ruler = new SUT();
        $ruler->setAsserter($asserter);
        $asserter->setContext($contextA);
        $result = $ruler->getAsserter($contextB);

        $this->assertSame($result, $asserter);
        $this->assertSame($result->getContext(), $contextB);
    }

    public function testGetAsserterTheDefaultOne(): void
    {
        $ruler = new SUT();
        $result = $ruler->getAsserter();

        $this->assertInstanceOf(LUT\Visitor\Asserter::class, $result);
        $this->assertNull($result->getContext());
        $this->assertSame($ruler->getAsserter(), $result);
    }

    public function testGetAsserterTheDefaultOneWithASpecificContext(): void
    {
        $ruler = new SUT();
        $context = new LUT\Context();
        $result = $ruler->getAsserter($context);

        $this->assertInstanceOf(LUT\Visitor\Asserter::class, $result);
        $this->assertSame($result->getContext(), $context);
        $this->assertSame($ruler->getAsserter($context), $result);
    }

    public function testGetDefaultAsserter(): void
    {
        $result = SUT::getDefaultAsserter();

        $this->assertInstanceOf(LUT\Visitor\Asserter::class, $result);
        $this->assertNull($result->getContext());
    }

    public function testGetDefaultAsserterWithASpecificContext(): void
    {
        $context = new LUT\Context();
        $result = SUT::getDefaultAsserter($context);

        $this->assertInstanceOf(LUT\Visitor\Asserter::class, $result);
        $this->assertSame($result->getContext(), $context);
        $this->assertSame(SUT::getDefaultAsserter($context), $result);
    }

    public function testGetCompiler(): void
    {
        $result = SUT::getCompiler();

        $this->assertInstanceOf(Compiler\Llk\Parser::class, $result);
        $this->assertSame(SUT::getCompiler(), $result);
    }
}
