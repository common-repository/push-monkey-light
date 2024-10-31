<?php

/* WordPress Check */
if ( ! defined( 'ABSPATH' ) ) {

  exit;
}

/**
 * Class used to help debugging.
 */
class Push_Monkey_Light_Debugger { 

  /**
   * Prints a message to php.log
   * @param string $text 
   */
  public function push_monkey_light_debug( $text ) {

    // error_log( "========= " . $text);
  }

  /**
   * Prints a message in the outputed HTML with an easy to notice prefix.
   * @param string $prefix 
   * @param string $text 
   */
  public function push_monkey_light_debug2( $prefix, $text ) {

    // $output = print_r( $text, true );
    // print_r( '<br />==== ' . $prefix . ': ' . $output );
  }
}
