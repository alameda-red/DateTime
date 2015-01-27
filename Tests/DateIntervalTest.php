<?php

namespace Alameda\Component\DateTime\Tests;

use Alameda\Component\DateTime\DateInterval;

class DateIntervalTest extends \PHPUnit_Framework_TestCase
{
    public function testDateInterval_1o1_Years()
    {
        $start = new \DateTime('2000-01-01 00:00:00'); // leap year, 366 days
        $end = new \DateTime('2001-01-01 00:00:00');

        $diff = $start->diff($end);

        $this->assertEquals(366, $diff->days);

        $end = $start->add(new \DateInterval('P1Y'));

        $this->assertEquals(new \DateTime('2001-01-01 00:00:00'), $end);

        $start = new \DateTime('2001-01-01 00:00:00');
        $end = $start->add(new \DateInterval('P1Y'));

        $this->assertEquals(new \DateTime('2002-01-01 00:00:00'), $end);
    }

    public function testDateInterval_1o1_Months()
    {
        $start = new \DateTime('2000-01-01 00:00:00'); // leap year, 366 days
        $end = $start->add(new \DateInterval('P12M')); // 12 * 30 = 360 days

        $this->assertEquals(new \DateTime('2001-01-01 00:00:00'), $end);

        $start = new \DateTime('2001-01-01 00:00:00');
        $end = $start->add(new \DateInterval('P12M'));

        $this->assertEquals(new \DateTime('2002-01-01 00:00:00'), $end);
    }

    public function testDateInterval_1o1_Weeks()
    {
        $start = new \DateTime('2000-01-01 00:00:00'); // leap year, 366 days
        $end = $start->add(new \DateInterval('P52W')); // 52 * 7 = 364 days

        $this->assertNotEquals(new \DateTime('2001-01-01 00:00:00'), $end);
        $this->assertEquals(new \DateTime('2000-12-30 00:00:00'), $end);

        $start = new \DateTime('2001-01-01 00:00:00');
        $end = $start->add(new \DateInterval('P52W'));

        $this->assertNotEquals(new \DateTime('2002-01-01 00:00:00'), $end);
        $this->assertEquals(new \DateTime('2001-12-31 00:00:00'), $end);

    }

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

        $start2 = new \DateTime('2000-01-01 00:00:00');
        $end2 = new \DateTime('2001-01-01 00:00:00');

        $start3 = new \DateTime('2001-01-01 00:00:00');
        $end3 = new \DateTime('2002-01-01 00:00:00');

        return array(
            array(new \DateInterval('PT48H'), false, '00-00-00 48:00:00'),
            array(new \DateInterval('PT48H'), true, '00-00-02 00:00:00'),
            array(new \DateInterval('P2D'), false, '00-00-00 48:00:00'),
            array(new \DateInterval('P2D'), true, '00-00-02 00:00:00'),
            array(new \DateInterval('P33D'), true, '00-01-03 00:00:00'),
            array(new \DateInterval('PT61M'), true, '00-00-00 01:01:00'),
            array(new \DateInterval('PT61S'), true, '00-00-00 00:01:01'),
            array(new \DateInterval('P13M'), true, '01-01-00 00:00:00'),
            array(new \DateInterval('P366D'), true, '01-00-01 00:00:00'),
            array(new \DateInterval('P366D'), false, '00-00-00 8784:00:00'),

            array($start->diff($end), true, '00-00-02 12:00:00'),
            array($start->diff($end), false, '00-00-00 60:00:00'),

            array($start2->diff($end2), true, '01-00-00 00:00:00'),
            array($start2->diff($end2), false, '00-00-00 8784:00:00'),

            array($start3->diff($end3), true, '01-00-00 00:00:00'),
            array($start3->diff($end3), false, '00-00-00 8760:00:00'),
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

    public function testSum()
    {
        $base = new \DateInterval('PT0H');

        $i1 = new \DateInterval('PT1S');
        $i2 = new \DateInterval('PT1M');

        $this->assertEquals(new \DateInterval('PT1M1S'), DateInterval::sum($base, $i1, $i2));

        $base = new \DateInterval('PT1S');

        $i1 = new \DateInterval('PT1S'); $i1->invert = true;
        $i2 = new \DateInterval('PT1M');
        $i3 = new \DateInterval('PT1H');

        $this->assertEquals(new \DateInterval('PT1H1M0S'), DateInterval::sum($base, $i1, $i2, $i3));

        $base = new \DateInterval('PT1H1M1S');

        $i1 = new \DateInterval('PT1S'); $i1->invert = true;
        $i2 = new \DateInterval('PT1M'); $i2->invert = true;
        $i3 = new \DateInterval('PT1H'); $i3->invert = true;

        $this->assertEquals(new \DateInterval('PT0S'), DateInterval::sum($base, $i1, $i2, $i3));

        $base = new \DateInterval('PT0S');

        $i1 = new \DateInterval('P1Y');
        $i2 = new \DateInterval('P1M');
        $i3 = new \DateInterval('P1D');

        $this->assertEquals(new \DateInterval('PT9504H'), DateInterval::sum($base, $i1, $i2, $i3));
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testSumTooFewArgumentException()
    {
        DateInterval::sum(new \DateInterval('PT0S'));
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testSumWrongArgumentException()
    {
        DateInterval::sum(new \DateInterval('PT0S'), new \DateTime('now'));
    }
}