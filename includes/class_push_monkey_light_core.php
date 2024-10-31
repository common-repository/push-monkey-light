<?php
/* WordPress Check */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once( plugin_dir_path( __FILE__ ) . 'class_push_monkey_light_client.php' );
require_once( plugin_dir_path( __FILE__ ) . 'class_push_monkey_light_debugger.php' );

/**
 * Main class that connects the WordPress API
 * with the Push Monkey API
 */
class Push_Monkey_Light {

	/* Public */

	public $endpointURL;
	public $apiClient;
	public $sign_in_error;

	const ACCOUNT_KEY_KEY = 'push_monkey_light_account_key';
	const WEBSITE_PUSH_ID_KEY = 'push_monkey_light_website_push_id_key';
	const WEBSITE_NAME_KEY = 'push_monkey_light_website_name';
	const FLUSH_REWRITE_RULES_FLAG_KEY = 'push_monkey_light_user_flush_key';
	const SUBDOMAIN_FORCED = 'push_monkey_light_subdomain_settings';

	/**
	 * Hooks up with the required WordPress actions.
	 */
	public function push_monkey_light_instance() {

		$this->push_monkey_light_add_actions();
	}

	/**
	 * Checks if an Account Key is stored.
	 * @return boolean
	 */
	public function push_monkey_light_has_account_key() {

		if( $this->push_monkey_light_account_key() ) {

			return true;
		}
		return false;
	}

	/**
	 * Returns the stored Account Key.
	 * @return string - the Account Key
	 */
	public function push_monkey_light_account_key() {

		$account_key = get_option( self::ACCOUNT_KEY_KEY, '' );
		if( ! $this->push_monkey_light_account_key_is_valid( $account_key ) ) {

			return NULL;
		}
		return $account_key;
	}

	/**
	 * Checks if an Account Key is valid.
	 * @param string $account_key - the Account Key checked.
	 * @return boolean
	 */
	public function push_monkey_light_account_key_is_valid( $account_key ) {

		if( ! strlen( $account_key ) ) {

			return false;
		}
		return true;
	}

	/**
	 * Checks if a user is signed in.
	 * @return boolean
	 */
	public function push_monkey_light_signed_in() {

		return get_option( self::ACCOUNT_KEY_KEY );
	}

	/**
	 * Write the service worker file
	 */
	public function push_monkey_light_service_worker_file_create() {

		$content_file = plugin_dir_path( __DIR__ ) . 'templates/pages/service_worker.php';
		$file_name = ABSPATH . 'service-worker-' . $this->push_monkey_light_account_key() . '.php';
		if ( file_exists( $content_file ) ) {

			$file = fopen( $file_name, 'w' );
			$file_write = fwrite( $file, file_get_contents( $content_file ) );
			fclose( $file );
			chmod( $file_name, 0644 );
			return $file_write;
		}
	}

	/**
	 * service worker file error
	 */
	public function push_monkey_light_service_worker_file_error() {

	  if ( ( isset( $_GET['page'] ) ) && ( is_admin() ) && ( $_GET['page'] == "push_monkey_light_main_config" ) ) {

			echo '<div class="notice notice-error is-dismissible"><p>' . __( 'Error: Could not create service-worker-' . $this->push_monkey_light_account_key() . '.php file', 'push-monkey-light' ) . '</p></div>';
		}
	}

	/**
	 * Signs out an user.
	 */
	public function push_monkey_light_sign_out() {

		delete_option( self::ACCOUNT_KEY_KEY );
		delete_option( self::WEBSITE_PUSH_ID_KEY );
		delete_option( self::SUBDOMAIN_FORCED );
		delete_option( self::FLUSH_REWRITE_RULES_FLAG_KEY );
		delete_option( Push_Monkey_Light_Client::PLAN_NAME_KEY );
	}

	/**
	 * Constructor that initializes the Push Monkey class.
	 */
	function __construct() {

		if ( is_ssl() ) {

			$this->endpointURL = "https://www.getpushmonkey.com"; //live
		} else {

			$this->endpointURL = "http://www.getpushmonkey.com"; //live
		}
		$this->apiClient = new Push_Monkey_Light_Client( $this->endpointURL );
		$this->d = new Push_Monkey_Light_Debugger();
	}

	/**
	 * Adds all the WordPress action hooks required by Push Monkey.
	 */
	function push_monkey_light_add_actions() {

		add_action( 'init', array( $this, 'push_monkey_light_process_forms' ) );

		add_action( 'init', array( $this, 'push_monkey_light_enqueue_scripts' ) );

		add_action( 'init', array( $this, 'push_monkey_light_enqueue_styles' ) );
		 
		add_action( 'wp_head', array( $this, 'push_monkey_light_sw_meta' ) );

		add_action( 'admin_menu', array( $this, 'push_monkey_light_register_settings_pages' )) ;

		// If not signed in, display an admin_notice prompting the user to sign in.
		if( $this->push_monkey_light_signed_in() ) {

			if ( $this->push_monkey_light_service_worker_file_create() == false ) {
				
					add_action( 'admin_notices' , array( $this, 'push_monkey_light_service_worker_file_error' ) );
			}
		} 
		add_action( 'admin_notices', array( $this, 'push_monkey_light_manifest_js' ) );
	}

	/**
	 * Register menu pages
	 */
	function push_monkey_light_register_settings_pages() {
		$icon_svg = 'data:image/svg+xml;base64,PD94bWwgdmVyc2lvbj0iMS4wIiBlbmNvZGluZz0iVVRGLTgiPz48c3ZnIHdpZHRoPSI2NHB4IiBoZWlnaHQ9IjY0cHgiIHZpZXdCb3g9IjAgMCA2NCA2NCIgdmVyc2lvbj0iMS4xIiB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHhtbG5zOnhsaW5rPSJodHRwOi8vd3d3LnczLm9yZy8xOTk5L3hsaW5rIj4gICAgICAgIDx0aXRsZT5sb2dvLWdyYXktMTY8L3RpdGxlPiAgICA8ZGVzYz5DcmVhdGVkIHdpdGggU2tldGNoLjwvZGVzYz4gICAgPGRlZnM+PC9kZWZzPiAgICA8ZyBpZD0iUGFnZS0xIiBzdHJva2U9Im5vbmUiIHN0cm9rZS13aWR0aD0iMSIgZmlsbD0ibm9uZSIgZmlsbC1ydWxlPSJldmVub2RkIj4gICAgICAgIDxnIGlkPSJsb2dvLWdyYXktMTYiIGZpbGw9IiNFRUVFRUUiPiAgICAgICAgICAgIDxnIGlkPSJsb2dvIiB0cmFuc2Zvcm09InRyYW5zbGF0ZSg2LjAwMDAwMCwgMC4wMDAwMDApIj4gICAgICAgICAgICAgICAgPHBhdGggZD0iTTM2LjY0OTU4NzEsNDAuNTczMDk0IEM0MS4yODQwMTgxLDQwLjAxNTgwNjcgNDQuODcwMjQwMywzNi4yMDIwOTcxIDQ0Ljg3MDI0MDMsMzEuNTgwMzU3NiBDNDQuODcwMjQwMywyNi41NzUxMTExIDQwLjY2NDE2MzMsMjIuNTE3NTU3MSAzNS40NzU3MDQzLDIyLjUxNzU1NzEgQzMyLjUyMDE3MDksMjIuNTE3NTU3MSAyOS44ODM0MDYsMjMuODM0MTczNSAyOC4xNjEyNzI1LDI1Ljg5MjU4NDIgQzI2LjQ0MjQ3ODgsMjMuNzU5MTM4OCAyMy43NTcwMTA5LDIyLjM4NjQ1MDYgMjAuNzM5MTk4OSwyMi4zODY0NTA2IEMxNS41NTA3NCwyMi4zODY0NTA2IDExLjM0NDY2MjksMjYuNDQ0MDA0NiAxMS4zNDQ2NjI5LDMxLjQ0OTI1MTEgQzExLjM0NDY2MjksMzYuMDI0NDE0NyAxNC44NTg5Njg2LDM5LjgwNzc1MjMgMTkuNDI1NTI2OCw0MC40MjQxNDk2IEMxOC45MDA5ODksNDEuNTU0MDM2NCAxOC42MDkyODA1LDQyLjgwNjQ0OTYgMTguNjA5MjgwNSw0NC4xMjQ1ODkxIEMxOC42MDkyODA1LDQ5LjEyOTgzNTcgMjIuODE1MzU3Niw1My4xODczODk3IDI4LjAwMzgxNjYsNTMuMTg3Mzg5NyBDMzMuMTkyMjc1NSw1My4xODczODk3IDM3LjM5ODM1MjYsNDkuMTI5ODM1NyAzNy4zOTgzNTI2LDQ0LjEyNDU4OTEgQzM3LjM5ODM1MjYsNDIuODY0MDk4MiAzNy4xMzE2MDE4LDQxLjY2MzcxMDIgMzYuNjQ5NTg3MSw0MC41NzMwOTQgWiBNMTEuMzYzNDU3NywwLjA4NDE5ODUxNDUgQzE0LjkyODQ2NjcsMC43MjAyNzU0MDMgMTguNjAwODc0LDMuNDQzODE1NyAyMi4zODA2Nzk4LDguMjU0ODE5NDIgQzIyLjY0NTA3Nyw1Ljk1OTI1NjUgMjIuNzYwODc3NiwzLjUyMzA0MzU0IDIyLjcyODA4MTYsMC45NDYxODA1NTYgQzI3LjEyNTM2MTcsMS44NDEwMTkzNiAzMC43MjMyMzUsNC41OTQ2MzYyNiAzMy41MjE3MDE0LDkuMjA3MDMxMjUgQzMzLjk1NjU5NzIsNy41NDk2MjM4NCAzNC4wMzU4MDczLDUuMzgyMDg5MTIgMzMuNzU5MzMxNiwyLjcwNDQyNzA4IEMzOS43OTE5ODI5LDUuNDA3MTE3IDQyLjcyMTAzNTUsNy4zODA1MzI2NiA0Ni44MDgyODQzLDE1Ljg1MzMzNzEgQzQ5LjY3NDc3ODcsMjEuNzk1NTM2NCA0OS44MzczMjk0LDI4LjU1NzI2ODMgNDkuNzAyMDk0OSwzMi4xMTk2MDI0IEM0OS42ODg2NTc0LDMyLjQ3MzU3MzUgNDkuNjE4NDA4MiwzMi45OTgyMzU0IDQ5LjYxODQwODIsMzMuODAyNjUzIEM0OS42MTg0MDgyLDM0LjcyMTI5OTkgNTAuNjY0OTk4NCwzNS4yODQ2MTM3IDUxLjUwODU5OTIsMzQuNzAzODU3NCBDNDkuNDQ0MTYxMyw1MC4yMjU4NzcyIDM4Ljc0NDcwOTYsNjAuMjUwODIwNSAyNy40NDU5MTU1LDU5LjY2Mzg1ODEgQzIyLjQ5ODk4MTIsNTkuNDA2ODY5MyAyMC4wNjU4NDU2LDU4LjQ0MTQ0ODIgMTcuOTM2MzExLDU4LjIzMzgxMDYgQzE1LjMyNjUwMTUsNTcuOTc5MzQ0NSAxMi41MjAwOTkzLDU5LjA3Njk0NDcgMTAuOTkwMTQ1LDYyLjI2ODc3NTEgQzkuMTIyOTM3OTIsNjYuMTY0MTkwOCAyLjY4NzkxNjM3LDg4LjY2MzE5MjYgMTMuMTQ5NDcxLDg3LjQyNjY0ODkgQzE4LjkwMTA0NDcsODYuNzQyNTE3NCAxNi4yMzc5ODcyLDgyLjg5NzczMDggMTcuMzQ1MDU2Nyw4MC42MTQ1ODYxIEMxOC40ODQ2NTU4LDc4LjA4NzIzMDggMjMuNTU5MzAwOSw3OC42Nzk5MTY5IDI0LjY1Mjk3NTgsODAuNjE0NTg2MSBDMjUuMzczMzA0NSw4MS45NzY2NTMxIDI0LjI1NTA2NTIsOTQuMTQ5MTUxOSAxMi44OTIyNDE2LDk0LjQyOTY4NDIgQy01LjEzMzk4NTcxLDk1LjExMjE0MjYgMS4wMjY2MjkzLDcxLjkwODY1ODUgMS4zMjU1MzA1LDcwLjUxNTU2ODQgQzMuNjUzODcxODgsNTkuNjYzODU4MSA4LjM0MzI2NDk2LDU2LjIyMDY5OTEgNy40NzkzNTg0MSw1NC4wOTA3NzY4IEM2LjYxNTQ1MTg2LDUxLjk2MDg1NDUgMi4yMzkxODM2NCw0OC42ODY3MzIzIDAuOTA5NzIyMjIyLDQyLjM3NzYzODEgQzEuMTc1OTM3MjMsNDIuNTYyNTMzOSAxLjc5OTk4MTAxLDQyLjQ3MTgyMjEgMS43OTU4NTA5Nyw0MS44MzgzNDUgQzEuNzk0NzUyMzMsNDEuNjQ0OTM1OSAxLjc3ODM3NjgzLDQxLjQ5NjU3MyAxLjc0NjcyNDQ1LDQxLjM5MzI1NjMgQy0wLjM1NDQ2NzczNSwzMy41OTE1MDAyIC0wLjM5MTExODIxNiwyNy4xODgzMDY1IDEuNjM2NzczLDIyLjE4MzY3NTEgQzEuOTg5OTM1OTgsMjIuODcxMjAyMyAyLjk3MTY3OTY5LDIyLjk5OTc1NTkgMy42NTM4NzE4OCwyMi4zMzk0NTM0IEMzLjc1ODk4MTY4LDIyLjIzNzcxNjMgNC4wMzYzOTc3OCwyMS43OTc4ODY4IDQuNDE5NzY3NTcsMjEuMDY2OTU2IEM2LjMyNTEzNjg2LDE3LjQzNDE4ODYgMTAuOTMyNjkwOCw2LjkzMDA3ODYzIDExLjM2MzQ1NzcsMC4wODQxOTg1MTQ1IFoiIGlkPSJDb21iaW5lZC1TaGFwZSI+PC9wYXRoPiAgICAgICAgICAgICAgICA8ZyBpZD0iZmFjZS1jb3B5IiB0cmFuc2Zvcm09InRyYW5zbGF0ZSgyNy45MjY0NDAsIDM4LjA5MDgwOSkgcm90YXRlKDguMDAwMDAwKSB0cmFuc2xhdGUoLTI3LjkyNjQ0MCwgLTM4LjA5MDgwOSkgdHJhbnNsYXRlKDE2LjQyNjQ0MCwgMjYuNTkwODA5KSI+ICAgICAgICAgICAgICAgICAgICA8cGF0aCBkPSJNNi41MDI0MDg2NiwzLjczMDI2MzE1IEM1Ljk0MzE3NzQsMy4yNzQ1MDcyNyA1LjIzMDEyODQ5LDMuMDAxMzY2MyA0LjQ1MzQ5MjQsMy4wMDEzNjYzIEMyLjY1NzQ4NzM3LDMuMDAxMzY2MyAxLjIwMTUzNzYyLDQuNDYyMDg1NzQgMS4yMDE1Mzc2Miw2LjI2Mzk3NDQ5IEMxLjIwMTUzNzYyLDguMDY1ODYzMjMgMi42NTc0ODczNyw5LjUyNjU4MjY3IDQuNDUzNDkyNCw5LjUyNjU4MjY3IEM1Ljk3Njc5OTU2LDkuNTI2NTgyNjcgNy4yNTU0NzY5MSw4LjQ3NTc2NjY2IDcuNjA4NjU2NDEsNy4wNTcyMzMxNyBDNy4zMjQ4OTM4MSw3LjI0MzMyNjY3IDYuOTg1NzcwNjMsNy4zNTE1MTA1NSA2LjYyMTQ2MjI1LDcuMzUxNTEwNTUgQzUuNjIzNjgxNjgsNy4zNTE1MTA1NSA0LjgxNDgyMDcxLDYuNTM5OTk5NzUgNC44MTQ4MjA3MSw1LjUzODk1MDQ0IEM0LjgxNDgyMDcxLDQuNTc4MDMzNjcgNS41NjAxMjY0OSwzLjc5MTc2MjY2IDYuNTAyNDA4NjYsMy43MzAyNjMxNSBaIiBpZD0iZXllLWxlZnQiIHRyYW5zZm9ybT0idHJhbnNsYXRlKDQuNDA1MDk3LCA2LjI2Mzk3NCkgcm90YXRlKC0yMy4wMDAwMDApIHRyYW5zbGF0ZSgtNC40MDUwOTcsIC02LjI2Mzk3NCkgIj48L3BhdGg+ICAgICAgICAgICAgICAgICAgICA8cGF0aCBkPSJNMjAuMDY3OTYzNywxLjgwMDA0MjM5IEMxOS41MDg3MzI1LDEuMzQ0Mjg2NSAxOC43OTU2ODM2LDEuMDcxMTQ1NTMgMTguMDE5MDQ3NSwxLjA3MTE0NTUzIEMxNi4yMjMwNDI0LDEuMDcxMTQ1NTMgMTQuNzY3MDkyNywyLjUzMTg2NDk3IDE0Ljc2NzA5MjcsNC4zMzM3NTM3MiBDMTQuNzY3MDkyNyw2LjEzNTY0MjQ3IDE2LjIyMzA0MjQsNy41OTYzNjE5MSAxOC4wMTkwNDc1LDcuNTk2MzYxOTEgQzE5LjU0MjM1NDYsNy41OTYzNjE5MSAyMC44MjEwMzIsNi41NDU1NDU5IDIxLjE3NDIxMTUsNS4xMjcwMTI0MSBDMjAuODkwNDQ4OSw1LjMxMzEwNTkxIDIwLjU1MTMyNTcsNS40MjEyODk3OCAyMC4xODcwMTczLDUuNDIxMjg5NzggQzE5LjE4OTIzNjgsNS40MjEyODk3OCAxOC4zODAzNzU4LDQuNjA5Nzc4OTggMTguMzgwMzc1OCwzLjYwODcyOTY4IEMxOC4zODAzNzU4LDIuNjQ3ODEyOSAxOS4xMjU2ODE2LDEuODYxNTQxODkgMjAuMDY3OTYzNywxLjgwMDA0MjM5IFoiIGlkPSJleWUtcmlnaHQiIHRyYW5zZm9ybT0idHJhbnNsYXRlKDE3Ljk3MDY1MiwgNC4zMzM3NTQpIHJvdGF0ZSgtMjMuMDAwMDAwKSB0cmFuc2xhdGUoLTE3Ljk3MDY1MiwgLTQuMzMzNzU0KSAiPjwvcGF0aD4gICAgICAgICAgICAgICAgICAgIDxlbGxpcHNlIGlkPSJPdmFsLTMiIHRyYW5zZm9ybT0idHJhbnNsYXRlKDE0LjM4MzU5NSwgMTEuODYwNjIxKSByb3RhdGUoLTIyLjAwMDAwMCkgdHJhbnNsYXRlKC0xNC4zODM1OTUsIC0xMS44NjA2MjEpICIgY3g9IjE0LjM4MzU5NDkiIGN5PSIxMS44NjA2MjA5IiByeD0iMSIgcnk9IjEuMDg3NTM2MDYiPjwvZWxsaXBzZT4gICAgICAgICAgICAgICAgICAgIDxlbGxpcHNlIGlkPSJPdmFsLTMtQ29weSIgdHJhbnNmb3JtPSJ0cmFuc2xhdGUoMTEuMzc0Nzk0LCAxMi4yNjQyMzYpIHNjYWxlKC0xLCAxKSByb3RhdGUoLTIyLjAwMDAwMCkgdHJhbnNsYXRlKC0xMS4zNzQ3OTQsIC0xMi4yNjQyMzYpICIgY3g9IjExLjM3NDc5NDUiIGN5PSIxMi4yNjQyMzYzIiByeD0iMSIgcnk9IjEuMDg3NTM2MDYiPjwvZWxsaXBzZT4gICAgICAgICAgICAgICAgICAgIDxwYXRoIGQ9Ik0xNC4yNjc0MTQ2LDIxLjEwNDg5NyBDMTQuNDM1MTc3OCwyMi43NDg0IDEwLjg4MDc3ODEsMjIuMjM0NjcwMyAxMC4xOTk4MzYsMjIuMDEzMTc4MiBDOC43MjA1MDYyOSwyMS41MzE5OTIgNy45MTY3Njk0MywyMC41NzI4NjY3IDcuNzg4NjI1NDEsMTkuMTM1ODAyMiBDOC4xNzM4MTA0NiwyMC4wNzMyNjUxIDkuMzc4MTQzMTgsMjAuNjUxMjMwOSAxMS40MDE2MjM2LDIwLjg2OTY5OTQgQzEyLjQ2NzExOTksMjAuOTg0NzM3NiAxMy4zMTE0MDUyLDIwLjYwMjIwMjUgMTMuNzkyNDY4NCwyMC42ODM2NzUzIEMxNC4xNDU0ODU1LDIwLjc0MzQ2MjIgMTQuMjQ0NTgyMSwyMC44ODEyMTY3IDE0LjI2NzQxNDYsMjEuMTA0ODk3IFoiIGlkPSJQYXRoLTIiPjwvcGF0aD4gICAgICAgICAgICAgICAgPC9nPiAgICAgICAgICAgIDwvZz4gICAgICAgIDwvZz4gICAgPC9nPjwvc3ZnPg==';
		//NOTE: call a function to load this page. Loading a file instead of a function doesn't execute the page hook suffix.
		$hook_suffix = add_menu_page( __( 'Push Monkey Light', 'push-monkey-light' ), __( 'Push Monkey Light', 'push-monkey-light' ), 'manage_options', 'push_monkey_light_main_config', array( $this, 'push_monkey_light_submenu_page_content_main' ), $icon_svg );
		add_action( 'admin_print_styles-' . $hook_suffix , array( $this, 'push_monkey_light_enqueue_styles_main_config' ) );
	}

	/**
	 * Pushes a monkey light submenu page content main.
	 */
	function push_monkey_light_submenu_page_content_main() {
		$pluginPath = plugins_url( '/', plugin_dir_path( __FILE__ ) );
		$login_account_key = $this->push_monkey_light_account_key();
		$signed_in = $this->push_monkey_light_signed_in();
		$sign_in_error = $this->sign_in_error;
		require_once plugin_dir_path( __FILE__ ) . '../templates/pages/settings/main.php';
	}

	/**
   * Recursively search a directory for a file.
   * Return: array of paths to the found files.
	 */
	function push_monkey_light_rglob( $pattern, $flags = 0 ) {

    $files = glob( $pattern, $flags );
    foreach ( glob( dirname( $pattern ).'/*', GLOB_ONLYDIR|GLOB_NOSORT ) as $dir ) {

        $files = array_merge( $files, $this->push_monkey_light_rglob( $dir.'/'.basename( $pattern ), $flags ) );
    }
    return $files;
	}

	/**
	 * Get the name of the website. Can be either from get_bloginfo() or
	 * from a previously saved value.
	 * @return string
	 */
	function push_monkey_light_website_name() {

		$name = get_option( self::WEBSITE_NAME_KEY, false );
		if( ! $name ) {

			$name = get_bloginfo( 'name' );
		}
		return $name;
	}

	/**
	 * Enqueue all the JS files required.
	 */
	function push_monkey_light_enqueue_scripts() {

		if ( ! is_admin() ) {

			if ( $this->push_monkey_light_signed_in() ) {

				$url = "https://www.getpushmonkey.com/sdk/config-".$this->push_monkey_light_account_key().".js";
				wp_enqueue_script( 'push_monkey_light_sdk', esc_url( $url ) , array( 'jquery' ) );
			}
		}
	}

	/**
	 * Enqueue all the CSS required.
	 */
	function push_monkey_light_enqueue_styles( $hook_suffix ) {

		if ( is_admin() ) {
			
			wp_enqueue_style( 'push_monkey_light_styles', plugins_url( '/css/styles.css', plugin_dir_path( __FILE__ ) ) );
			wp_enqueue_style( 'push_monkey_light_additional', plugins_url( '/css/additional.css', plugin_dir_path( __FILE__ ) ) );
		} else {

			wp_enqueue_style( 'push_monkey_light_animate', plugins_url( 'css/default/animate.css', plugin_dir_path( __FILE__ ) ) );
			wp_enqueue_style( 'push_monkey', plugins_url( 'css/default/push-monkey.css', plugin_dir_path( __FILE__ ) ) );
		}
	}

	/**
	 * Enqueue the CSS for the Settings page
	 */
	function push_monkey_light_enqueue_styles_main_config() {

		wp_enqueue_style( 'push_monkey_light_config_style', plugins_url( 'css/main-config.css', plugin_dir_path( __FILE__ ) ) );
	}

	/**
	 * Multiple manifest js admin notice
	 */
	function push_monkey_light_manifest_js() {

		if ( ( isset( $_GET['page'] ) ) && ( file_exists( get_template_directory() . '/manifest.json' ) ) && ( is_admin() ) && ( $_GET['page'] == "push_monkey_light_main_config" ) ) {

			$manifest_json = file_get_contents( get_template_directory() . '/manifest.json' );
			$json_array = json_decode( $manifest_json, true );

			if ( ( ! array_key_exists( 'gcm_sender_id', $json_array ) ) || ( ! array_key_exists( 'gcm_user_visible_only', $json_array ) ) ) {
	    // Manifest file
				$manifest_file = plugins_url( 'assets/manifest.json', plugin_dir_path( __FILE__ ) );
				echo '<div class="notice notice-warning is-dismissible"><p>' . __( 'Check manifest.json file of the', 'push-monkey-light' ) . ' <a href="' . esc_url( $manifest_file ) . '" target="_blank">' . __( 'plugin here', 'push-monkey-light' ) . '</a>' . __( '. And copy the "gcm_sender_id": "some-id","gcm_user_visible_only": true in your theme\'s manifest.json', 'push-monkey-light' ) . '</p></div>';
			}
		}
	}

	/**
	* Add a custom <link> tag for the manifest
	*/
	function push_monkey_light_sw_meta() {

			if ( ! file_exists( get_template_directory() . '/manifest.json' ) ) {

	    		echo '<link rel="manifest" href="' . esc_url( plugins_url( 'assets/manifest.json', plugin_dir_path( __FILE__ ) ) ) . '">';
			}

	}

	/**
	 * Central point to process forms.
	 */
	function push_monkey_light_process_forms() {

		if ( isset( $_POST['logout'] ) ) {

			$this->push_monkey_light_sign_out();
			wp_redirect( esc_url( admin_url( 'admin.php?page=push_monkey_light_main_config' ) ) );
			exit;
		}
		// sign in login form
		if( isset( $_POST['push_monkey_light_sign_in'] ) ) {
			$key = sanitize_text_field( wp_unslash( $_POST['account_key'] ) );
			// empty account key
			if ( ! strlen( $key) ) {
				$this->sign_in_error = __( 'The two fields can\'t be empty.', 'push-monkey-light' );
				return;
			}
			// valid account key
			$update = $this->apiClient->push_monkey_light_sign_in( $key );
			if ( $update ) {
				update_option( self::ACCOUNT_KEY_KEY, $key );
				update_option( self::FLUSH_REWRITE_RULES_FLAG_KEY, true);
			} else {
				$this->sign_in_error = __( 'The Account Key seems to be invalid.', 'push-monkey-light' );
			}
		}
	}
}
