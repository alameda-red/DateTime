<?php

namespace Alameda\Component\DateTime\Tests;

use Alameda\Component\DateTime\DateInterval;

class DateIntervalTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @expectedException \InvalidArgumentException
     */
    public function testDivideExceptionDivisor()
    {
        DateInterval::divide(new \DateInterval('PT0M'), 0);
    }

    /**
     * @param $input
     * @param $divisor
     * @param $output
     *
     * @dataProvider divideDataProvider
     */
    public function testDivide($input, $divisor, $output)
    {
        $this->assertEquals($output, DateInterval::divide($input, $divisor)->format('%Y-%M-%D %H:%I:%S'));
    }

    public function divideDataProvider()
    {
        return array(
            array(new \DateInterval('PT1H'), 2, '00-00-00 00:30:00'),
            array(new \DateInterval('P1D'), 2, '00-00-00 12:00:00'),
            array(new \DateInterval('P1D'), 4, '00-00-00 06:00:00'),
            array(new \DateInterval('PT75M'), 1, '00-00-00 01:15:00'),
            array(new \DateInterval('PT75S'), 1, '00-00-00 00:01:15'),
        );
    }

    /**
     * @param $input
     * @param $output
     *
     * @dataProvider shortenDataProvider
     */
    public function testShorten($input, $include_days, $output)
    {
        $this->assertEquals($output, DateInterval::shorten($input, $include_days)->format('%Y-%M-%D %H:%I:%S'));
    }

    public function shortenDataProvider()
    {
        $start = new \DateTime('2014-09-14 00:00:00');
        $end = new \DateTime('2014-09-16 12:00:00');

        return array(
            array(new \DateInterval('PT48H'), false, '00-00-00 48:00:00'),
            array(new \DateInterval('PT48H'), true, '00-00-02 00:00:00'),
            array(new \DateInterval('P2D'), false, '00-00-00 48:00:00'),
            array(new \DateInterval('P2D'), true, '00-00-02 00:00:00'),
            array(new \DateInterval('P33D'), true, '00-01-03 00:00:00'),
            array(new \DateInterval('PT61M'), true, '00-00-00 01:01:00'),
            array(new \DateInterval('PT61S'), true, '00-00-00 00:01:01'),
            array(new \DateInterval('P13M'), true, '01-01-00 00:00:00'),
            array($start->diff($end), true, '00-00-02 12:00:00'),
            array($start->diff($end), false, '00-00-00 60:00:00'),
        );
    }

    /**
     * @param $input
     * @param boolean $include_days
     * @param $output
     *
     * @dataProvider stringDataProvider
     */
    public function testString($input, $include_days, $output)
    {
        $this->assertEquals($output, DateInterval::getString($input, $include_days));
    }

    public function stringDataProvider()
    {
        return array(
            array(new \DateInterval('PT36H'), false, 'PT36H'),
            array(new \DateInterval('PT36H'), true, 'P1DT12H'),
        );
    }
} 