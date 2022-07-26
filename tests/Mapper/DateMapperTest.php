<?php

declare(strict_types=1);

namespace Szemul\Framework\Test\Mapper;

use Carbon\Carbon;
use DateTimeZone;
use PHPUnit\Framework\TestCase;
use Szemul\Framework\Mapper\DateMapper;

class DateMapperTest extends TestCase
{
    public function testMapTimeToString_shouldConvertToUTCAndMapObjectToString(): void
    {
        $time   = Carbon::parse('2022-06-04 12:00:00', new DateTimeZone('Europe/Budapest'));
        $result = (new DateMapper())->mapTimeToString($time);

        $this->assertSame('2022-06-04 10:00:00', $result);
    }

    public function testMapStringToTime_shouldConvertToObjectInUTC(): void
    {
        $timeString = '2022-06-04 12:00:00';
        $result     = (new DateMapper())->mapStringToTime($timeString);

        $this->assertSame($timeString, $result->toDateTimeString());
        $this->assertSame('UTC', $result->getTimezone()->getName());
    }
}
