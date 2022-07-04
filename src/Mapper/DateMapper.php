<?php

declare(strict_types=1);

namespace Szemul\Framework\Mapper;

use Carbon\Carbon;
use Carbon\CarbonInterface;
use DateTimeZone;

class DateMapper
{
    public function mapTimeToString(CarbonInterface $dateTime): string
    {
        return $dateTime->copy()->setTimezone(new DateTimeZone('UTC'))->toDateTimeString();
    }

    public function mapStringToTime(?string $string): ?CarbonInterface
    {
        return empty($string) ? null : Carbon::parse($string, new DateTimeZone('UTC'));
    }
}
