<?php declare(strict_types=1);

namespace App\Utils;

class DateTime
{
    /**
     * Returns the number of week in a month for the specified date.
     *
     * @link https://stackoverflow.com/a/5853796
     *
     * @param string $date a YYYY-MM-DD formatted date.
     * @param string $rollover The day on which the week rolls over.
     *
     * @return int
     */
    public static function weekOfMonth(
        string $date,
        string $rollover = "sunday"
    ): int {
        $cut = substr($date, 0, 8);
        $daylen = 86400;

        $timestamp = strtotime($date);
        $first = strtotime($cut . "00");
        $elapsed = ($timestamp - $first) / $daylen;

        $weeks = 1;

        for ($i = 1; $i <= $elapsed; $i++) {
            $dayfind = $cut . ($i < 2 ? "0" . $i : $i);
            $daytimestamp = strtotime($dayfind);

            $day = strtolower(date("l", $daytimestamp));

            if ($day == strtolower($rollover)) {
                $weeks++;
            }
        }

        return $weeks;
    }
}
