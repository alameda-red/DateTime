<?php

namespace Alameda\Component\DateTime;

use Zebba\Component\Utility\ParameterConverter;

final class DateInterval
{
    /**
     * @param \DateInterval $base
     * @param mixed:\DateInterval|\DateInterval $vars
     * @return \DateInterval
     */
    static public function sum(\DateInterval $base, $vars)
    {
        if (is_array($vars)) {
            $intervals =  array_values($vars);
        } else {
            $intervals = func_get_args();
            unset ($intervals[0]);
        }

        try { /* @var $intervals \DateInterval[] */
            $intervals = ParameterConverter::toArray($intervals, '\DateInterval');
        } catch (\DomainException $e) {
            throw new \InvalidArgumentException('This method only accepts \DateInterval objects.');
        }

        $s = self::toSeconds($base);

        foreach ($intervals as $interval) { /* @var $interval \DateInterval */
            if (! $interval->invert) {
                $s += self::toSeconds($interval);
            } else {
                $s -= self::toSeconds($interval);
            }
        }

        $result = new \DateInterval(sprintf('PT%dS', abs($s)));
        $result = new \DateInterval(self::shortenString($result));

        if ($s < 0) { $result->invert = true; }

        return $result;
    }

    /**
     * @param \DateInterval $interval
     * @param float $divisor
     * @return \DateInterval
     * @throws \InvalidArgumentException
     */
    static public function divide(\DateInterval $interval, $divisor)
    {
        if (0 === $divisor) {
            throw new \InvalidArgumentException('Division by 0 is not allowed.');
        }

        $map = self::toMap($interval);
        $map = self::divideYears($map, $divisor);
        $map = self::divideMonths($map, $divisor);
        $map = self::divideDays($map, $divisor);
        $map = self::divideHours($map, $divisor);
        $map = self::divideMinutes($map, $divisor);
        $map = self::divideSeconds($map, $divisor);

        $map = self::toIntegerValues($map);

        return self::toDateInterval($map);
    }

    /**
     * @param \DateInterval $interval
     * @param bool $include_days
     * @return \DateInterval
     */
    static public function shorten(\DateInterval $interval, $include_days = false)
    {
        $result = self::shortenString($interval, $include_days);

        return new \DateInterval($result);
    }

    /**
     * @param \DateInterval $interval
     * @param bool $include_days
     * @return string
     */
    static public function getString(\DateInterval $interval, $include_days = false)
    {
        return self::shortenString($interval, $include_days);
    }

    /**
     * @param \DateInterval $interval
     * @param bool $include_days
     * @return string
     */
    static private function shortenString(\DateInterval $interval, $include_days = false)
    {
        $m = self::toMap($interval);

        $result = 'P';

        while($m['s'] > 59) {
            $m['s'] -= 60;
            $m['i']++;
        }

        while($m['i'] > 59) {
            $m['i'] -= 60;
            $m['h']++;
        }

        if ($include_days) {
            while($m['h'] > 23) {
                $m['h'] -= 24;
                $m['d']++;
            }

            while($m['d'] > 364) {
                $m['d'] -= 365;
                $m['y']++;
            }

            while($m['d'] > 29) {
                $m['d'] -= 30;
                $m['m']++;
            }

            while($m['m'] > 11) {
                $m['m'] -= 12;
                $m['y']++;
            }

            if (0 != $m['y']) { $result .= $m['y'] .'Y'; }
            if (0 != $m['m']) { $result .= $m['m'] .'M'; }
            if (0 != $m['d']) { $result .= $m['d'] .'D'; }
        } else {
            if (0 < $m['days']) {
                $m['h'] += $m['days'] * 24;
            } else {
                $m['h'] += $m['y'] * 365 * 24;
                $m['h'] += $m['m'] * 30 * 24;
                $m['h'] += $m['d'] * 24;
            }
        }

        if (0 < $m['h'] + $m['i'] + $m['s']) {
            $result .= 'T';
        }

        if (0 != $m['h']) { $result .= $m['h'] .'H'; }
        if (0 != $m['i']) { $result .= $m['i'] .'M'; }
        if (0 != $m['s']) { $result .= $m['s'] .'S'; }

        if (1 === strlen($result)) { $result = 'PT0M'; }

        return $result;
    }

    /**
     * @param array $m
     * @return \DateInterval
     */
    static private function toDateInterval(array $m)
    {
        return self::shorten(new \DateInterval(sprintf('P%dY%dM%dDT%dH%dM%dS',
            $m['y'], $m['m'], $m['d'], $m['h'], $m['i'], $m['s']
        )));
    }

    /**
     * @param \DateInterval $i
     * @return array
     */
    static private function toMap(\DateInterval $i)
    {
        return array(
            'y' => ($i->y) ? $i->y : 0,
            'm' => ($i->m) ? $i->m : 0,
            'd' => ($i->d) ? $i->d : 0,
            'h' => ($i->h) ? $i->h : 0,
            'i' => ($i->i) ? $i->i : 0,
            's' => ($i->s) ? $i->s : 0,
            'days' => ($i->days) ? $i->days : 0,
        );
    }

    /**
     * @param array $m
     * @param $d
     * @return array
     */
    static private function divideYears(array $m, $d)
    {
        $rest_y = $m['y'] % $d;
        $m['y'] = floor($m['y'] / $d);

        if ($rest_y) $m['m'] += $rest_y * 365;

        return $m;
    }

    /**
     * @param array $m
     * @param float $d
     * @return array
     */
    static private function divideMonths(array $m, $d)
    {
        $rest_m = $m['m'] % $d;
        $m['m'] = floor($m['m'] / $d);

        if ($rest_m) $m['d'] += $rest_m * 30;

        return $m;
    }

    /**
     * @param array $m
     * @param $d
     * @return array
     */
    static private function divideDays(array $m, $d)
    {
        $rest_d = $m['d'] % $d;
        $m['d'] = floor($m['d'] / $d);

        if ($rest_d) $m['h'] += $rest_d * 24;

        return $m;
    }

    /**
     * @param array $m
     * @param $d
     * @return array
     */
    static private function divideHours(array $m, $d)
    {
        $rest_h = $m['h'] % $d;
        $m['h'] = floor($m['h'] / $d);

        if ($rest_h) $m['i'] += $rest_h * 60;

        return $m;
    }

    /**
     * @param array $m
     * @param $d
     * @return array
     */
    static private function divideMinutes(array $m, $d)
    {
        $rest_i = $m['i'] % $d;
        $m['i'] = floor($m['i'] / $d);

        if ($rest_i) $m['s'] += $rest_i * 60;

        if (60 < $m['i']) {
            $tmp = floor($m['i'] / 60);

            $m['h'] += $tmp;
            $m['i'] -= $tmp * 60;
        }

        return $m;
    }

    /**
     * @param array $m
     * @param $d
     * @return array
     */
    static private function divideSeconds(array $m, $d)
    {
        $rest_s = $m['s'] % $d;
        $m['s'] = floor($m['s'] / $d);

        if ($rest_s) $m['s'] = floor($m['s'] / $d);

        if (60 < $m['s']) {
            $tmp = floor($m['s'] / 60);

            $m['i'] += $tmp;
            $m['s'] -= $tmp * 60;
        }

        return $m;
    }

    /**
     * @param array $m
     * @return array
     */
    static private function toIntegerValues(array $m)
    {
        return array_map(function ($v) {
            return (int) $v;
        }, $m
        );
    }

    /**
     * @param \DateInterval $i
     * @return integet
     */
    static private function toSeconds(\DateInterval $i)
    {
        $seconds = 0;

        $seconds += $i->y * 365 * 24 * 60 * 60;
        $seconds += $i->m * 30 * 24 * 60 * 60;
        $seconds += $i->d * 24 * 60 * 60;
        $seconds += $i->h * 60 * 60;
        $seconds += $i->i * 60;
        $seconds += $i->s;

        return $seconds;
    }
} 