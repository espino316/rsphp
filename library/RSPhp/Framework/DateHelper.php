<?php
/**
 * DateHelper.php
 *
 * PHP Version 5
 *
 * DateHelper File Doc Comment
 *
 * @category  FrameworkCore
 * @package   RSPhp\Framework
 * @author    Luis Espino <luis@espino.info>
 * @copyright 2016 Luis Espino
 * @license   MIT License
 * @link      https://rsphp.espino.info/
 */

namespace RSPhp\Framework;

use DateTime;
use DateInterval;

/**
 * Helper for date operations
 *
 * Please report bugs on https://github.com/espino316/rsphp/issues
 *
 * @category  FrameworkCore
 * @package   RSPhp\Framework
 * @author    Luis Espino <luis@espino.info>
 * @copyright 2016 Luis Espino
 * @license   MIT License
 * @link      https://rsphp.espino.info/
 */
class DateHelper
{
    /**
     * Returns a string representing the current date
     *
     * @return String
     */
    static function now()
    {
        return date('Y-m-d H:i:s');
    }

    /**
     * Adds $increment number of seconds to $date and returns a String
     *
     * @param Date $date      The date to add the increment
     * @param Int  $increment The increment to add to the date
     *
     * @return String
     */
    static function addSeconds( $date, $increment )
    {
        $interval = "PT$increment"."S";
        return self::add($date, $interval);
    } // end function addDays

    /**
     * Adds $increment number of minutes to $date and returns a String
     *
     * @param Date $date      The date to add the increment
     * @param Int  $increment The increment to add to the date
     *
     * @return String
     */
    static function addMinutes( $date, $increment )
    {
        $interval = "PT$increment"."M";
        return self::add($date, $interval);
    } // end function addDays

    /**
     * Adds $increment number of hours to $date and returns a String
     *
     * @param Date $date      The date to add the increment
     * @param Int  $increment The increment to add to the date
     *
     * @return String
     */
    static function addHours( $date, $increment )
    {
        $interval = "PT$increment"."H";
        return self::add($date, $interval);
    } // end function addDays

    /**
     * Adds $increment number of days to $date and returns a String
     *
     * @param Date $date      The date to add the increment
     * @param Int  $increment The increment to add to the date
     *
     * @return String
     */
    static function addDays( $date, $increment )
    {
        $interval = "P$increment"."D";
        return self::add($date, $interval);
    } // end function addDays

    /**
     * Adds $increment number of months to $date and returns a String
     *
     * @param Date $date      The date to add the increment
     * @param Int  $increment The increment to add to the date
     *
     * @return String
     */
    static function addMonths( $date, $increment )
    {
        $interval = "P$increment"."M";
        return self::add($date, $interval);
    } // end function addDays

    /**
     * Adds $increment to $date
     *
     * @param Date $date      The date to add the increment
     * @param Int  $increment The increment to add to the date
     *
     * @return String
     */
    static function addYears( $date, $increment )
    {
        $interval = "P$increment"."Y";
        return self::add($date, $interval);
    } // end function addDays

    /**
     * Adds a interval to a date
     *
     * @param Date   $date     The date to add the interval
     * @param String $interval The interval added to the date
     *
     * @return Date
     */
    static function add( $date, $interval )
    {
        $date = new DateTime($date);
        $date->add(new DateInterval($interval));
        return $date->format('Y-m-d H:i:s');
    } // end function add

    /**
     * Gets the difference from two dates
     *
     * @param Char $interval The interval type ( 'y', 'm', 'd', 'h', 'i', 's' )
     * @param Date $date1    The first date
     * @param Date $date2    The second date
     *
     * @return Int
     */
    static function diff( $interval, $date1, $date2 )
    {
        $time1 = null;
        $time2 =  null;

        $typeOf = getType($date1);
        if ($typeOf == 'object' ) {
            $className = get_class($date1);
            if ($className != 'DateTime' ) {
                throw new Exception("Object 1 is not date");
            } else {
                $time1 = $date1->getTimestamp();
            } // end if class is DateTime
        } else if ($typeOf == 'string' ) {
            try {
                $date1 = new DateTime($date1);
                $time1 = $date1->getTimestamp();
            } catch ( Exception $ex ) {
                throw new Exception("Object 1 is not a date", 1);
            } // end try catch create date
        } else {
            throw new Exception("Object 1 is not date");
        }// end if type is object

        $typeOf = getType($date2);
        if ($typeOf == 'object' ) {
            $className = get_class($date2);
            if ($className != 'DateTime' ) {
                throw new Exception("Object 2 is not date");
            } else {
                $time2 = $date2->getTimestamp();
            } // end if class is DateTime
        } else if ($typeOf == 'string' ) {
            try {
                $date2 = new DateTime($date2);
                $time2 = $date2->getTimestamp();
            } catch ( Exception $ex ) {
                throw new Exception("Object 2 is not a date", 1);
            } // end try catch create date
        } else {
            throw new Exception("Object 2 is not date");
        }// end if type is object

        $result = $time1 - $time2;

        switch ( $interval ) {
        case 'y':
            $result = $result / 60 / 60 / 24 / 365.25; // years
            break;

        case 'm':
            $result = $result / 60 / 60 / 24 / 30.4375; // month
            break;

        case 'd':
            $result = $result / 60 / 60 / 24; // days
            break;

        case 'h':
            $result = $result / 60 / 60; // hours
            break;

        case 'i':
            $result = $result / 60; // minutes
            break;

        case 's':
            $result = $result; // seconds
            break;

        default:
            // code...
            break;
        }

        return abs($result);
    } // end function diff
} // end class DateHelper
