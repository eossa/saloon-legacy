<?php

namespace Saloon\Tests\Unit;

use Saloon\Helpers\Pipeline;
use PHPUnit\Framework\TestCase;

class PipelineTest extends TestCase
{
    public function testAPipelineCanBeExecuted()
    {
        $pipeline = new Pipeline();
        $number = 0;

        $pipeline
            ->pipe(function ($number) {
                return $number + 5;
            })
            ->pipe(function ($number) {
                return $number * 2;
            })
            ->pipe(function ($number) {
                return $number - 3;
            });

        $this->assertCount(3, $pipeline->getPipes());

        $number = $pipeline->process($number);

        $this->assertEquals(7, $number);
    }
}
