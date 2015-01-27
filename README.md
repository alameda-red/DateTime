Alameda DateTime component
==========================

This component provides a tool-kit to work with PHPs' Date and Time related objects.

Installation
------------

You can either modify your composer.json with

```json
{
    "require" : {
        "alameda-red/datetime" : "0.*"
    }
}
```

or run:
```shell
    $ composer require "alameda-red/datetime=0.*"
```

Usage
-----

Let's start with a warning right away: working with \DateIntervals is not trivial! There is obviously a certain extend
boundary in which you can pretty much do anything. As soon as you leave the box you are on your own and there is actually
no tool to help you from that point on. This component won't be able to help you either.

When you base your code on working with the values for seconds, minutes, hours and days you are on the safe-side in most
cases when working in a narrow timeframe.
If you use DateInterval::shorten($interval, true) be sure you understand what this value will represent.
An hourly representation (2nd parameter = true) of a leap-year will yield 366 * 24 = 8784 hours while the year representation
(2nd parameter = false *default*) will yield 365 * 24 = 8760 hours.

### Divide a \DateInterval
Ever thought about splitting your \DateInterval('P1D') into two parts so you'd have \DateInterval('PT12H')? You can do that:

``` php
<?php
    $interval = new \DateInterval('P1D');

    $split = DateInterval::divide($interval, 2); // \DateInterval('PT12H')
```

### Create a shorter, more readable representation of \DateInterval
No idea what \DateTime('PT86400S) means? Make it more readable:

``` php
<?php
    $interval = new \DateInterval('PT86400S');

    $short = DateInterval::shorten($interval); // \DateInterval('P1D')
    $short = DateInterval::shorten($interval, true); // \DateInterval('P1D')
    $short = DateInterval::shorten($interval, false); // \DateInterval('PT24H')

```

### String representation of a \DateInterval
If you don't want to store your \DateInterval object in a human readable form it is probably more catchy reading 'PT24H'
or 'P1D' in your database over the approach of other developers to store the value in seconds (86400):

``` php
<?php
    $interval = new \DateInterval('PT86400S');

    $string = DateInterval::getString($interval); // 'P1D'
    $string = DateInterval::getString($interval, true); // 'P1D'
    $string = DateInterval::getString($interval, false); // 'PT24H'
```

### Shorten the string by using larger time units
So \DateInterval('PT1440M') is a day, make it look like one:

``` php
<?php
    $interval = new \DateInterval('PT1440M');

    $short = DateInterval::shortenString($interval); // 'P1D'
    $short = DateInterval::shortenString($interval, true); // 'P1D'
    $short = DateInterval::shortenString($interval, false); // 'PT24H'
```

### Sum up \DateIntervals
Doing calculations with intervals? Struggle no more!

``` php
<?php
    $base = new \DateInterval('PT0H');

    $i1 = new \DateInterval('PT1S');
    $i2 = new \DateInterval('PT1M');
    $i3 = new \DateInterval('PT1H');

    $sum = DateInterval::sum($base, $i1, $i2, $i3); // 'PT1H1M1S'

    $base = new \DateInterval('PT1H1M1S');

    $i1 = new \DateInterval('PT1S'); $i1->invert = true;
    $i2 = new \DateInterval('PT1M'); $i2->invert = true;
    $i3 = new \DateInterval('PT1H'); $i3->invert = true;

    $sum = DateInterval::sum($base, $i1, $i2, $i3); // 'PT0H', $sum->invert -> true
```