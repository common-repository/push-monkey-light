<?php

/**
 * Extend DateTime to ensure PHP 5.2 compatibility
 */
class Push_Monkey_Light_Date_Time extends DateTime {

    public static function createFromFormat( $format, $time, $timezone = null ) {

        if( ! $timezone ) {

        	$timezone = new DateTimeZone( date_default_timezone_get() );
        }
        if ( method_exists( 'DateTime', 'createFromFormat' ) ) {
        	
        	return parent::createFromFormat( $format, $time, $timezone );
        }
        return new Push_Monkey_Light_Date_Time( date( $format, strtotime( $time ) ), $timezone );
    }

    public function getTimestamp() {

         return method_exists( 'DateTime', 'getTimestamp' ) ? 

             parent::getTimestamp() : $this->format( 'U' );
    }
}