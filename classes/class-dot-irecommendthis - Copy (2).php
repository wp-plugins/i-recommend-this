<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly.
 
/**
 * DOT IRecommendThis Class
 *
 * All functionality pertaining to the I Recommend This feature.
 *
 * @package WordPress
 * @subpackage DOT_IRecommendThis
 * @category Plugin
 * @author Harish
 * @since 1.4.6
 */

class DOT_IRecommendThis {

	private $dir;
	public $version;
	//public $file;
	//public $filename = 'i-recommend-this/dot-irecommendthis.php';


	/**
	 * Constructor function.
	 * 
	 * @access public
	 * @since 1.4.6
	 * @return void
	 */
	public function __construct( $file ) {

		$this->dir = dirname( $file );
		//$this->file = $file;
		
	
		$this->load_plugin_textdomain();
		add_action( 'init', array( &$this, 'load_localisation' ), 0 );

		// Run this on activation / deactivation
		register_activation_hook( $this->file, array( &$this, 'activate' ) );
		register_deactivation_hook( $this->file, array( &$this, 'deactivate' ) );
		
		//add_action( 'admin_init', array(&$this, 'admin_init'));
		add_action( 'admin_menu', array( &$this, 'dot_irecommendthis_menu' ) );
		add_filter( "plugin_action_links_{$file}", array( &$this, 'dot_irecommendthis_plugin_links', 10, 2 ) );
		add_action( "plugin_action_links_{$file}" , array( &$this, 'customActionLink' ) );

		
	} // End __construct()

	/**
	 * Load the plugin's localisation file.
	 * @access public
	 * @since 1.4.6
	 * @return void
	 */
	public function load_localisation () {
		load_plugin_textdomain( 'dot-irecommendthis', false, dirname( plugin_basename( $this->file ) ) . '/lang/' );
	} // End load_localisation()

	/**
	 * Load the plugin textdomain from the main WordPress "languages" folder.
	 * @since  1.4.6
	 * @return  void
	 */
	public function load_plugin_textdomain () {
	    $domain = 'dot-irecommendthis';
	    // The "plugin_locale" filter is also used in load_plugin_textdomain()
	    $locale = apply_filters( 'plugin_locale', get_locale(), $domain );
	 
	    load_textdomain( $domain, WP_LANG_DIR . '/' . $domain . '/' . $domain . '-' . $locale . '.mo' );
	    load_plugin_textdomain( $domain, FALSE, dirname( plugin_basename( $this->file ) ) . '/lang/' );
	} // End load_plugin_textdomain()


	/**
	 * Fired when the plugin is activated.
	 *
	 * @params	$network_wide	True if WPMU superadmin uses "Network Activate" action, false if WPMU is disabled or plugin is activated on an individual blog 
	 */
	public function activate( $network_wide ) {
		
		
		// TODO define activation functionality here
		
		global $wpdb;
		global $irt_dbVersion;
		
		
		// Version Number
		if (!defined('MYPLUGIN_VERSION_KEY'))
			define('MYPLUGIN_VERSION_KEY', 'myplugin_version');
		
		if (!defined('MYPLUGIN_VERSION_NUM'))
			define('MYPLUGIN_VERSION_NUM', '1.0.0');
		
		add_option(MYPLUGIN_VERSION_KEY, MYPLUGIN_VERSION_NUM);
		
		
		
		$table_name = $wpdb->prefix . "irecommendthis_votes";
		if($wpdb->get_var("show tables recommend '$table_name'") != $table_name) {
			$sql = "CREATE TABLE " . $table_name . " (
				id MEDIUMINT(9) NOT NULL AUTO_INCREMENT,
				time TIMESTAMP NOT NULL,
				post_id BIGINT(20) NOT NULL,
				ip VARCHAR(15) NOT NULL,
				UNIQUE KEY id (id)
			);";
	
			require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
			dbDelta($sql);
	
			add_option("irt_dbVersion", $irt_dbVersion);
		}
		
		add_option('irt_jquery', '1', '', 'yes');
		add_option('irt_onPage', '1', '', 'yes');
		add_option('irt_textOrNotext', 'notext', '', 'yes');
		add_option('irt_text', 'I recommend This', '', 'yes');
		add_option('irt_textOnclick', 'recommends', '', 'yes');



	} // end activate
	
	/**
	 * Fired when the plugin is deactivated.
	 *
	 * @params	$network_wide	True if WPMU superadmin uses "Network Activate" action, false if WPMU is disabled or plugin is activated on an individual blog 
	 */
	public function deactivate( $network_wide ) {
		
		// TODO define deactivation functionality here		

		global $wpdb;
		$wpdb->query("DROP TABLE IF EXISTS ".$wpdb->prefix."irecommendthis_votes");
	
		delete_option('irt_jquery');
		delete_option('irt_onPage');
		delete_option('irt_textOrNotext');
		delete_option('irt_text');
		delete_option('irt_textOnclick');
		delete_option('most_recommended_posts');
		delete_option('irt_dbVersion');		

	} // end deactivate   

	/**
	 * Register the plugin's version.
	 * @access public
	 * @since 1.0.0
	 * @return void
	 */
	private function register_plugin_version () {
		if ( $this->version != '' ) {
			update_option( 'dot-irecommendthis' . '-version', $this->version );
		}
	} // End register_plugin_version()
	
	
	/*--------------------------------------------*
	 * Core Functions
	 *--------------------------------------------*/
	 

	#### ADMIN OPTIONS ####
	function dot_irecommendthis_menu() {
		$page_title = __('I Recommend This', 'dot-irecommendthis');
		$menu_title = __('I Recommend This', 'dot-irecommendthis');
		$capability = 'manage_options';
		$menu_slug = 'dot-irecommendthis';
		$function = 'dot_irecommendthis_settings';
		add_options_page($page_title, $menu_title, $capability, $menu_slug, $function);
	}
	
	
	public function dot_irecommendthis_plugin_links($links, $file) {
		//if ( $file == plugin_basename( dirname(__FILE__).'/dot-irecommendthis.php' ) ) {
			$links[] = '<a href="' . get_bloginfo('wpurl') . '/wp-admin/options-general.php?page=dot-irecommendthis">' . __('Settings', 'dot-irecommendthis') . '</a>';
		//}
		return $links;
	}
	/*
	function dot_irecommendthis_plugin_links($file, $links) {
		$settings_link = '<a href="options-general.php?page='.$this->filename.'">Settings</a>';
		array_unshift($links, $settings_link);
		return $links;
	}
	*/
	function customActionLink($content, $file){ 
		if ( $file == plugin_basename( dirname(__FILE__).'/dot-irecommendthis.php' ) ) {
			return array(
				$content['myLink'] = 'Your',
				$content['deactivate'] = 'Mother',
				$content['edit'] = 'is a',
				$content['blub'] = 'Plugin!'
				);
		}
	} 
	
	/*
	public function dot_irecommendthis_plugin_links($links, $file) {
		 static $this_plugin;
	
		 if (!$this_plugin) {
			$this_plugin = plugin_basename(__FILE__);
		 }
	
		 if ($file == $this_plugin) {
			// The "page" query string value must be equal to the slug
			// of the Settings admin page we defined earlier, which in
			// this case equals "myplugin-settings".
			$settings_link = '<a href="' . get_bloginfo('wpurl') . '/wp-admin/options-general.php?page=dot-irecommendthis">' . __('Settings', 'dot-irecommendthis') . '</a>';
			array_unshift($links, $settings_link);
		 }
	
		 return $links;
	}
	*/
	
	function dot_irecommendthis_settings() {
		if (!current_user_can('manage_options')) {
			wp_die('You do not have sufficient permissions to access this page.');
		}
	
		// Here is where you could start displaying the HTML needed for the settings
		// page, or you could include a file that handles the HTML output for you.
	}


} // End Class

		
?>
