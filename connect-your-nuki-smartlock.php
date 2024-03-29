<?php
/**
 * Plugin Name: Connect your Nuki Smartlock!
 * Plugin URI: https://nuki-smartlock-for-wp.com/
 * Description: Connect your Nuki Smartlock to your WordPress and manage it!
 * Author: Nuki Smartlock for WP
 * Author URI: https://nuki-smartlock-for-wp.com/
 * Version: 1.3.4
 **/

namespace Nuki;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly.

use Nuki\API\api;

if ( ! class_exists( 'Connect_Nuki_Smartlock' ) ) {
	class Connect_Nuki_Smartlock {
		public function init() {
			register_activation_hook( __FILE__, array( $this, 'activate' ) );
			register_deactivation_hook( __FILE__, array( $this, 'deactivate' ) );

			add_action( 'plugins_loaded', array( $this, 'define_constantes' ), 10 );
			add_action( 'plugins_loaded', array( $this, 'load_files' ), 15 );

			add_action( 'admin_print_styles', array( $this, 'load_styles' ) );

			add_filter( 'cron_schedules', array( $this, 'create_schedule' ) );
		}

		public function define_constantes() {
			define( 'NUKIWP_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
			define( 'NUKIWP_PLUGIN_PATH', plugin_dir_path( __FILE__ ) );
			define( 'NUKIWP_PLUGIN_DIR', untrailingslashit( NUKIWP_PLUGIN_PATH ) );
		}

		public function load_files() {
			$files = scandir( NUKIWP_PLUGIN_PATH . 'inc/' );
			foreach ( $files as $file ) {
				if ( is_file( NUKIWP_PLUGIN_PATH . 'inc/' . $file ) ) {
					require NUKIWP_PLUGIN_PATH . 'inc/' . $file;
				}
			}
		}

		public function create_schedule( $schedules ) {
			$schedules['five_minutes'] = array(
				'interval' => 300,
				'display'  => esc_html__( 'Every Five Minutes', 'connect-your-nuki-smartlock' ),
			);

			return $schedules;
		}

		public function activate() {
			if ( ! wp_next_scheduled( 'nuki_cron_hook' ) ) {
				wp_schedule_event( time(), 'five_minutes', 'nuki_cron_hook' );
			}
		}

		public function deactivate() {
			$timestamp = wp_next_scheduled( 'nuki_cron_hook' );
			wp_unschedule_event( $timestamp, 'nuki_cron_hook' );
		}

		public function load_styles(){
			wp_enqueue_style( 'nuki-dashboard-styles', NUKIWP_PLUGIN_URL . 'assets/css/admin-nuki.min.css', '', 1.0 );
		}

	}
}
$nuki = new Connect_Nuki_Smartlock();
$nuki->init();

/**
 * Initialize Nuki API.
 *
 * @return api
 */
function nukiwp_api() {
	$api = new Api();
	return $api;
}