<?php

/*
Name:        YD Admin Notice Helper
URI:         NONE
Version:     0.1
Author:      Y-Designs
Author URI:  http://www.y-designs.com
*/

/*  
 * Admin Notice Helper class, see readme for example usage.
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Access denied.' );
}

if ( ! class_exists( 'YD_Admin_Notice_Helper' ) ) {

	class YD_Admin_Notice_Helper {
		// Declare variables and constants
		protected static $instance;
		protected $notices, $notices_were_updated;

		/**
		 * Constructor
		 */
		protected function __construct() {
			add_action( 'init',          array( $this, 'init' ), 9 );         // needs to run before other plugin's init callbacks so that they can enqueue messages in their init callbacks
			add_action( 'admin_notices', array( $this, 'print_notices' ) );
			add_action( 'shutdown',      array( $this, 'shutdown' ) );
		}

		/**
		 * Provides access to a single instances of the class using the singleton pattern
		 *
		 * @mvc    Controller
		 * @author Ian Dunn <ian@iandunn.name>
		 * @return object
		 */
		public static function get_singleton() {
			if ( ! isset( self::$instance ) ) {
				self::$instance = new YD_Admin_Notice_Helper();
			}

			return self::$instance;
		}

		/**
		 * Initializes variables
		 */
		public function init() {
			$default_notices             = array( 'update' => array(), 'error' => array() );
			$this->notices               = array_merge( $default_notices, get_option( 'anh_notices', array() ) );
			$this->notices_were_updated  = false;
		}

		/**
		 * Queues up a message to be displayed to the user
		 *
		 * @param string $message The text to show the user
		 * @param string $type    'update' for a success or notification message, or 'error' for an error message
		 */
		public function enqueue( $message, $type = 'update' ) {
			if ( in_array( $message, array_values( $this->notices[ $type ] ) ) ) {
				return;
			}

			$this->notices[ $type ][]   = (string) apply_filters( 'anh_enqueue_message', $message );
			$this->notices_were_updated = true;
		}

		/**
		 * Displays updates and errors
		 */
		public function print_notices() {
			foreach ( array( 'update', 'error' ) as $type ) {
				if ( count( $this->notices[ $type ] ) ) {
					$class = 'update' == $type ? 'updated' : 'error';

					require( dirname( __FILE__ ) . '/yd-admin-notice.php' );

					$this->notices[ $type ]      = array();
					$this->notices_were_updated  = true;
				}
			}
		}

		/**
		 * Writes notices to the database
		 */
		public function shutdown() {
			if ( $this->notices_were_updated ) {
				update_option( 'anh_notices', $this->notices );
			}
		}
	} // end YD_Admin_Notice_Helper

	YD_Admin_Notice_Helper::get_singleton(); // Create the instance immediately to make sure hook callbacks are registered in time

	if ( ! function_exists( 'add_notice' ) ) {
		function add_notice( $message, $type = 'update' ) {
			YD_Admin_Notice_Helper::get_singleton()->enqueue( $message, $type );
		}
	}
}
