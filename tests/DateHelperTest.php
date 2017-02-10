<?php

namespace RSPhp\Framework;

use PhpUnit\Framework\TestCase;
use RSPhp\Framework\DateHelper;

class DateHelperTest extends TestCase {

    public function testDateDiff()
    {
        $date1 = '2013-11-23 08:34:12';
        $date2 = '2016-10-23 22:23:54';

        $this->assertEquals(
            round( 2.9172745075671, 2 ),
            round( DateHelper::diff( 'y', $date1, $date2 ), 2 )
        );
    } // end function testDateDiff

    public function additionProvider()
    {
        return [
            'years'  => [ 'y', '2016-01-01', 1, '2017-01-01 00:00:00' ],
            'months' => [ 'm', '2016-01-01', 2, '2016-03-01 00:00:00' ]
        ];
    } // end function additionProvider

    /**
     * @dataProvider additionProvider
     */
    public function testAddX($type, $date, $increment, $expected)
    {
        switch ( $type ) {
            case 'y':
                $this->assertEquals(
                    DateHelper::addYears( $date, $increment ),
                    $expected
                );
            break;
            case 'm':
                $this->assertEquals(
                    DateHelper::addMonths( $date, $increment ),
                    $expected
                );
            break;
        } // end switch
    } // end function testAddX

} // end class DateHelperTest
