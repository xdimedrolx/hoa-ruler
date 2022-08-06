<?php

declare(strict_types=1);

namespace Hoa\Ruler\Test\Unit;

class TestableRuler extends \Hoa\Ruler\Ruler
{
    public function destroy(): void
    {
        $this->_asserter = null;
        self::$_defaultAsserter = null;
        self::$_compiler = null;
        self::$_interpreter = null;
    }
}
