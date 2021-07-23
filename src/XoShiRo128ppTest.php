<?php
namespace Quatsch;

use PHPUnit\Framework\TestCase;

final class XoShiRo128ppTest extends TestCase
{
    public function testFixture(): void
    {
        $fixture = [
            641,
            1573767,
            3222811527,
            3517856514,
            836907274,
            4247214768,
            3867114732,
            1355841295,
            495546011,
            621204420,
        ];

        $rng = new XoShiRo128pp(1, 2, 3, 4);
        foreach ($fixture as $value) {
            $this->assertSame($value, $rng->next());
        }
    }
}
