<?php

/* WordPress Check */
if ( ! defined( 'ABSPATH' ) ) {

	exit;
}

require_once( plugin_dir_path( __FILE__ ) . 'class_push_monkey_light_debugger.php' );
require_once( plugin_dir_path( __FILE__ ) . 'class_push_monkey_light_cache.php' );

/**
 * API Client
 */
class Push_Monkey_Light_Client {

	public $endpointURL;
	public $registerURL;
	public $cartURL;

	/* Public */

	const PLAN_NAME_KEY = 'push_monkey_light_plan_name_output';
	
	/**
	 * Signs in a user with an Account Key or a Token-Secret combination.
	 * @param string $account_key
	 * @return boolean
	 */
	public function push_monkey_light_sign_in( $account_key ) {

		delete_option( self::PLAN_NAME_KEY );
		$url = 'https://getpushmonkey.com/v2/api/verify';
		$args = array( 'body' => array( 'account_key' => $account_key ) );
		$response = wp_remote_post( $url, $args );
		if ( ! is_wp_error( $response ) ) {
			$body = wp_remote_retrieve_body( $response );
			$output = json_decode( $body );
			if ( $output->response == "ok" ) {
				return true;
			} else {
				return false;
			}
		}
	}

	/**
	 * Get the stats for an Account Key.
	 * @param string $account_key 
	 * @return mixed; false if nothing found; array otherwise.
	 */
	public function push_monkey_light_get_stats( $account_key ) {

		$stats_api_url = $this->endpointURL . '/stats/api';
		$args = array( 'body' => array( 'account_key' => $account_key ) );
		$response = wp_remote_post( $stats_api_url, $args );
		if( is_wp_error( $response ) ) {

			$this->d->push_monkey_light_debug( $response->get_error_message() );
			return ( object ) array( 'error' => $response->get_error_message() );
		} else {

			$body = wp_remote_retrieve_body( $response );
			$output = json_decode( $body ); 
			return $output;
		}
		return false;
	}

	/**
	 * Get the Website Push ID for an Account Key.
	 * @param string $account_key 
	 * @return string; array with error info if an error occured.
	 */
	public function push_monkey_light_get_website_push_ID( $account_key ) {

		$url = $this->endpointURL . '/v2/api/website_push_id';
		$args = array( 'body' => array( 'account_key' => $account_key ) );

		$response = wp_remote_post( $url, $args );

		if( is_wp_error( $response ) ) {

			return ( object ) array( 'error' => $response->get_error_message() );
		} 
		$body = wp_remote_retrieve_body( $response );
		$output = json_decode( $body ); 
		return $output;
	}

	/**
	 * Sends a desktop push notification.
	 * @param string $account_key 
	 * @param string $title 
	 * @param string $body 
	 * @param string $url_args 
	 * @param boolean $custom 
	 */
	public function push_monkey_light_send_push_notification( $account_key, $title, $body, $url_args, $custom, $segments, $locations, $image = NULL ) {

		$url = $this->endpointURL . '/push_message';
		$args = array( 
			'account_key' => $account_key,
			'title' => $title,
			'body' => $body, 
			'url_args' => $url_args,
			'send_to_segments_string' => implode(",", $segments),
			'send_to_locations_string' => implode(",", $locations),
			'image' => $image
		);
		$this->d->push_monkey_light_debug( print_r( $args, true ) );
		if ( $custom ) {

			$args['custom'] = true;
		}
		$response = $this->post_with_file( $url, $args, $image );
		if( is_wp_error( $response ) ) {

			$this->d->push_monkey_light_debug('send_push_notification '.$response->get_error_message());
		} else {

			$this->d->push_monkey_light_debug( print_r( $response, true) );
		}
	}

	/**
	 * Get the plan name.
	 * @param string $account_key 
	 * @return string; array with error info otherwise.
	 */
	public function push_monkey_light_get_plan_name( $account_key ) {

		$output = $this->cache->push_monkey_light_get( self::PLAN_NAME_KEY );
		if ( $output ) {
			
			$this->d->push_monkey_light_debug('served from cache');
			return (object) $output;
		}

		$url = $this->endpointURL . '/v2/api/get_plan_name';
		$args = array( 'body' => array( 'account_key' => $account_key ) );

		$response = wp_remote_post( $url, $args );

		if( is_wp_error( $response ) ) {

			return ( object ) array( 'error' => $response->get_error_message() );
		} 
		$body = wp_remote_retrieve_body( $response );
		$output = json_decode( $body ); 
		$serialized_output = json_decode( $body, true );
		if ( isset( $output->error ) ) {
			
			$this->d->push_monkey_light_debug('get_plan_name: ' . $output->error);
			return $output->error;
		} else {

			$this->d->push_monkey_light_debug("not from cache");
			$this->cache->push_monkey_light_store( self::PLAN_NAME_KEY, $serialized_output );
			return $output;
		}
		return '';
	}

	/**
	 * Get all the segments
	 * @param string $account_key
	 * @return associative array of [id=>string]
	 */
	public function push_monkey_light_get_segments( $account_key ) {

		$segments_api_url = $this->endpointURL . '/push/v1/segments/' . $account_key;
		$response = wp_remote_post( $segments_api_url, array() );
		if( is_wp_error( $response ) ) {

			$this->d->push_monkey_light_debug( $response->get_error_message() );
			return ( object ) array( 'error' => $response->get_error_message() );
		} else {

			$body = wp_remote_retrieve_body( $response );
			$output = json_decode( $body, true ); 
			if ( isset( $output["segments"] ) ) {

				if ( count( $output["segments"] ) > 0 ) {

					if ( gettype($output["segments"][0]) == "array" ) {

						return $output["segments"];
					}
				}
			}
		}
		return array();		
	}

	/**
	 * Save a segments
	 * @param string $account_key
	 * @param string $name	 
	 * @return response or error
	 */
	public function push_monkey_light_save_segment( $account_key, $name ) {

		$url = $this->endpointURL . '/push/v1/segments/create/' . $account_key;
		$args = array( 'body' => array( 
			
			'name' => $name
			) );
		$response = wp_remote_post( $url, $args );
		if ( is_wp_error( $response ) ) {
			
			return ( object ) array( 'error' => $response->get_error_message() );
		} else {

			$body = wp_remote_retrieve_body( $response );
			$output = json_decode( $body );
			$this->d->push_monkey_light_debug(print_r($output, true));
			return $output;				
		}
		return false;
	}

	/**
	 * Delete a segments
	 * @param string $account_key
	 * @param string $id of segment	 
	 * @return response or error
	 */
	public function push_monkey_light_delete_segment( $account_key, $id ) {

		$url = $this->endpointURL . '/push/v1/segments/delete/' . $account_key;
		$args = array( 'body' => array( 
			
			'id' => $id
			) );
		$this->d->push_monkey_light_debug($url);
		$response = wp_remote_post( $url, $args );
		if ( is_wp_error( $response ) ) {
			
			return ( object ) array( 'error' => $response->get_error_message() );
		} else {

			$body = wp_remote_retrieve_body( $response );
			$output = json_decode( $body );
			$this->d->push_monkey_light_debug(print_r($output, true));
			return $output;				
		}
		return false;		
	}

	/**
	 * Retrieve the status of a welcome message
	 * @param string $account_key
	 * @return associative array of JSON response
	 */
	public function push_monkey_light_get_welcome_message_status( $account_key ) {

		$url = $this->endpointURL . '/v2/api/welcome_notification_status/' . $account_key;
		$response = wp_remote_post( $url, array() );
		if( is_wp_error( $response ) ) {

			$this->d->push_monkey_light_debug( $response->get_error_message() );
			return ( object ) array( 'error' => $response->get_error_message() );			
		}
		$body = wp_remote_retrieve_body( $response );
		$output = json_decode( $body, true ); 
		if ( empty( $output ) ) {

			return ( object ) array( 'error' => 'empty' );			
		}
		return $output;
	}

	/**
	 * Retrieve the status of a welcome message
	 * @param string $account_key
	 * @return associative array of JSON response
	 */
	public function push_monkey_light_get_custom_prompt( $account_key ){

		$url = $this->endpointURL . '/v2/api/custom_prompt/' . $account_key;
		$response = wp_remote_post( $url, array() );
		if( is_wp_error( $response ) ) {

			$this->d->push_monkey_light_debug( $response->get_error_message() );
			return ( object ) array( 'error' => $response->get_error_message() );			
		}
		$body = wp_remote_retrieve_body( $response );
		$output = json_decode( $body, true ); 
		if ( empty( $output ) ) {

			return ( object ) array( 'error' => 'empty' );			
		}
		return $output;
	}

	/**
	 * Update the welcome message info
	 * @param string $account_key
	 * @param boolean $enabled
	 * @param string $message
 	 * @param string $title
	 * @return boolean. True if operation finished successfully.
	 */
	public function push_monkey_light_update_custom_prompt( $account_key, $enabled, $title, $message ) {

		$url = $this->endpointURL . '/v2/api/custom_prompt/' . $account_key . '/update';
		$args = array( 'body' => array( 
			
			'custom_prompt_message' => $message,
			'custom_prompt_title' => $title
		) );		
		if ( $enabled ) {

			$args['body']['enabled'] = true;
		}
		$response = wp_remote_post( $url, $args );
		if( is_wp_error( $response ) ) {

			$this->d->push_monkey_light_debug( $response->get_error_message() );
			return ( object ) array( 'error' => $response->get_error_message() );			
		}
		$body = wp_remote_retrieve_body( $response );
		$output = json_decode( $body, true ); 
		if ( isset( $output["response"] ) ) {

			if ( $output['response'] == "ok" ) {

				return true;
			}
		}
		return false;		
	}

	/**
	 * Retrieve locations stored for this account key
	 * @param string $account_key
	 * @return associative array of JSON response	 
	 */
	public function push_monkey_light_get_locations( $account_key ) { 

		$url = $this->endpointURL . '/v2/api/locations/' . $account_key;
		$response = wp_remote_post( $url, array() );
		if( is_wp_error( $response ) ) {

			$this->d->push_monkey_light_debug( $response->get_error_message() );
			return ( object ) array( 'error' => $response->get_error_message() );			
		}
		$body = wp_remote_retrieve_body( $response );
		$output = json_decode( $body, true ); 
		if ( empty( $output ) ) {

			return ( object ) array( 'error' => 'empty' );			
		}
		return $output;
	}

	/**
	 * Update the welcome message info
	 * @param string $account_key
	 * @param boolean $enabled
	 * @param string $message
	 * @return boolean. True if operation finished successfully.
	 */
	public function push_monkey_light_update_welcome_message( $account_key, $enabled, $message ) {

		$url = $this->endpointURL . '/v2/api/update_welcome_notification/' . $account_key;
		$args = array( 'body' => array( 
			
			'message' => $message
		) );		
		if ( $enabled ) {

			$args['body']['enabled'] = true;
		}
		$this->d->push_monkey_light_debug(print_r($args, true));
		$response = wp_remote_post( $url, $args );
		if( is_wp_error( $response ) ) {

			$this->d->push_monkey_light_debug( $response->get_error_message() );
			return ( object ) array( 'error' => $response->get_error_message() );			
		}
		$body = wp_remote_retrieve_body( $response );
		$output = json_decode( $body, true ); 
		$this->d->push_monkey_light_debug(print_r($output, true));				
		if ( isset( $output["status"] ) ) {

			if ( $output['status'] == "ok" ) {

				return true;
			}
		}
		return false;
	}
	
	/**
	 * Private
	 *
	 * @param      string  $endpoint_url  The endpoint url
	 */
	function __construct( $endpoint_url ) {
		$this->endpointURL = $endpoint_url;
		$this->registerURL = $endpoint_url.'/v2/register';
		$this->cartURL = $endpoint_url.'/magento/v1/cart';
		$this->d = new Push_Monkey_Light_Debugger();
		$this->cache = new Push_Monkey_Light_Cache();
	}
}
