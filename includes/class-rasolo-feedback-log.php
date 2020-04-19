<?php

/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       http://ra-solo.com.ua
 * @since      1.0.0
 *
 * @package    Rasolo_Feedback_Log
 * @subpackage Rasolo_Feedback_Log/includes
 */

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      1.0.0
 * @package    Rasolo_Feedback_Log
 * @subpackage Rasolo_Feedback_Log/includes
 * @author     Andrew V. Galagan <andrew.galagan@gmail.com>
 */

class Rasolo_Feedback_Log {

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      Rasolo_Feedback_Log_Loader    $loader    Maintains and registers all hooks for the plugin.
	 */
	protected $loader;

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $plugin_name    The string used to uniquely identify this plugin.
	 */
	protected $plugin_name;

	/**
	 * The current version of the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $version    The current version of the plugin.
	 */
	protected $version;

    static public $LOG_OPTION_KEY = '_rasolo_feed_log_key';
    static public $LOG_SETTINGS_KEY = '_rasolo_feed_sett_key';
    static public $TEXTDOMAIN = 'rasolo-feedback-log';

    private $plugin_admin;

	/**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and set the hooks for the admin area and
	 * the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {
		if ( defined( 'RASOLO_FEEDBACK_LOG_VERSION' ) ) {
			$this->version = RASOLO_FEEDBACK_LOG_VERSION;
		} else {
			$this->version = '1.0.0';
		}
		$this->plugin_name = 'rasolo-feedback-log';

		$this->load_dependencies();
		$this->set_locale();
		$this->define_admin_hooks();
		$this->define_public_hooks();

	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * Include the following files that make up the plugin:
	 *
	 * - Rasolo_Feedback_Log_Loader. Orchestrates the hooks of the plugin.
	 * - Rasolo_Feedback_Log_i18n. Defines internationalization functionality.
	 * - Rasolo_Feedback_Log_Admin. Defines all hooks for the admin area.
	 * - Rasolo_Feedback_Log_Public. Defines all hooks for the public side of the site.
	 *
	 * Create an instance of the loader which will be used to register the hooks
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function load_dependencies() {

		/**
		 * The class responsible for orchestrating the actions and filters of the
		 * core plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-rasolo-feedback-log-loader.php';

		/**
		 * The class responsible for defining internationalization functionality
		 * of the plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-rasolo-feedback-log-i18n.php';

		/**
		 * The class responsible for defining all actions that occur in the admin area.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-rasolo-feedback-log-admin.php';

		/**
		 * The class responsible for defining all actions that occur in the public-facing
		 * side of the site.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-rasolo-feedback-log-public.php';

		$this->loader = new Rasolo_Feedback_Log_Loader();

	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the Rasolo_Feedback_Log_i18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function set_locale() {

		$plugin_i18n = new Rasolo_Feedback_Log_i18n();
		$this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );

	}

	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_admin_hooks() {

		$this->plugin_admin = new Rasolo_Feedback_Log_Admin( $this->get_plugin_name(), $this->get_version() );

        $this->loader->add_action( 'wp_ajax_rasolofb', $this->plugin_admin, 'get_fb' );
        $this->loader->add_action( 'wp_ajax_nopriv_rasolofb', $this->plugin_admin, 'get_fb' );

		$this->loader->add_action( 'admin_enqueue_scripts', $this->plugin_admin, 'enqueue_styles' );
		$this->loader->add_action( 'admin_enqueue_scripts', $this->plugin_admin, 'enqueue_scripts' );
		$this->loader->add_action( 'admin_menu', $this->plugin_admin, 'create_adm_menu' );
//        add_action('admin_menu',array($this,'add_opts_page'));




	}

    public function add_opts_page(){
//        die('add_opts_page_123422322');
//        call_user_func_array('add_menu_page',
//            $plugin_admin->get_page_arguments());
//        call_user_func_array('add_menu_page',
//            $plugin_admin->get_page_arguments());


    }
    public function my_orders_options(){
//        die('asdadas');
//        $this->delete_success=FALSE;
        $this->show_msg_html();
    }


	/**
	 * Register all of the hooks related to the public-facing functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_public_hooks() {

		$plugin_public = new Rasolo_Feedback_Log_Public( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_styles' );
		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_scripts' );

	}

	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since    1.0.0
	 */
	public function run() {
		$this->loader->run();

	}

	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 *
	 * @since     1.0.0
	 * @return    string    The name of the plugin.
	 */
	public function get_plugin_name() {
		return $this->plugin_name;
	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @since     1.0.0
	 * @return    Rasolo_Feedback_Log_Loader    Orchestrates the hooks of the plugin.
	 */
	public function get_loader() {
		return $this->loader;
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @since     1.0.0
	 * @return    string    The version number of the plugin.
	 */
	public function get_version() {
		return $this->version;
	}

    private function write_log(){
        update_option(Rasolo_Feedback_Log::$LOG_OPTION_KEY,serialize($this->log));
        return true;
    }

    public function add_msg($u_name,$u_phone,$u_mail,$u_sub,$u_mes,$u_ip){

        $this->plugin_admin->add_msg($u_name,$u_phone,$u_mail,$u_sub,$u_mes,$u_ip);
//        rasolo_debug_to_file($u_phone,'$u_phone');
//        rasolo_debug_to_file($u_phone,null);
    } // The end of add_msg

}
