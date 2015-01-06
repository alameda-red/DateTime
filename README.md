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

    $split = DateInterval::shorten($interval); // \DateInterval('P1D')
    $split = DateInterval::shorten($interval, true); // \DateInterval('P1D')
    $split = DateInterval::shorten($interval, false); // \DateInterval('PT24H')

```

### String representation of a \DateInterval
If you don't want to store your \DateInterval object in a human readable form it is probably more catchy reading 'PT24H'
or 'P1D' in your database over the approach of other developers to store the value in seconds (86400):

``` php
<?php
    $interval = new \DateInterval('PT86400S');

    $split = DateInterval::getString($interval); // 'P1D'
    $split = DateInterval::getString($interval, true); // 'P1D'
    $split = DateInterval::getString($interval, false); // 'PT24H'
```

### Shorten the string by using larger time units
So \DateInterval('PT1440M') is a day, make it look like one:

``` php
<?php
    $interval = new \DateInterval('PT1440M');

    $split = DateInterval::shortenString($interval); // 'P1D'
    $split = DateInterval::shortenString($interval, true); // 'P1D'
    $split = DateInterval::shortenString($interval, false); // 'PT24H'
```