<?php

namespace Tests\Unit;

use App\Services\MachineStatusCalculator;
use PHPUnit\Framework\TestCase;

class MachineStatusCalculatorTest extends TestCase
{
    public function test_normal_condition_when_oee_and_availability_meet_normal_thresholds(): void
    {
        $calculator = new MachineStatusCalculator();

        $this->assertSame('normal', $calculator->determine([
            'oee' => 90,
            'availability' => 95,
        ]));
    }

    public function test_attention_condition_when_performance_is_below_normal_threshold(): void
    {
        $calculator = new MachineStatusCalculator();

        $this->assertSame('perhatian', $calculator->determine([
            'oee' => 75,
            'availability' => 90,
        ]));
    }

    public function test_repair_condition_when_oee_is_critical(): void
    {
        $calculator = new MachineStatusCalculator();

        $this->assertSame('perbaikan', $calculator->determine([
            'oee' => 60,
            'availability' => 80,
        ]));
    }

    public function test_repair_condition_when_availability_is_critical(): void
    {
        $calculator = new MachineStatusCalculator();

        $this->assertSame('perbaikan', $calculator->determine([
            'oee' => 90,
            'availability' => 65,
        ]));
    }

    public function test_missing_performance_data_does_not_mark_machine_as_normal(): void
    {
        $calculator = new MachineStatusCalculator();

        $this->assertSame('perhatian', $calculator->determine([
            'oee' => null,
            'availability' => null,
        ]));
    }
}
