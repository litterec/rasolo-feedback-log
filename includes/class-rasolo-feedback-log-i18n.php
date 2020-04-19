<?php

/**
 * Define the internationalization functionality
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @link       http://ra-solo.com.ua
 * @since      1.0.0
 *
 * @package    Rasolo_Feedback_Log
 * @subpackage Rasolo_Feedback_Log/includes
 */

/**
 * Define the internationalization functionality.
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @since      1.0.0
 * @package    Rasolo_Feedback_Log
 * @subpackage Rasolo_Feedback_Log/includes
 * @author     Andrew V. Galagan <andrew.galagan@gmail.com>
 */
class Rasolo_Feedback_Log_i18n {


	/**
	 * Load the plugin text domain for translation.
	 *
	 * @since    1.0.0
	 */
	public function load_plugin_textdomain() {
        //$path=dirname( dirname( plugin_basename( __FILE__ ) ) ) . '/languages/';
		load_plugin_textdomain(
			'rasolo-feedback-log',
			false,
			dirname( dirname( plugin_basename( __FILE__ ) ) ) . '/languages/'
		);
	}



}
