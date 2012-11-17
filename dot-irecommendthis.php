<?php
/**
 * Plugin Name: I Recommend This
 * Plugin URI: http://www.harishchouhan.com/personal-projects/i-recommend-this/
 * Description: This plugin allows your visitors to simply recommend or like your posts instead of commment it.
 * Version: 1.4.6
 * Author: Harish Chouhan
 * Author URI: http://www.harishchouhan.com
 * Author Email: me@harishchouhan.com
 *
 * @package WordPress
 * @subpackage DOT_IRecommendThis
 * @author Harish
 * @since 1.4.6
 *
 * License:

  Copyright 2012 "I Recommend This WordPress Plugin" (me@harishchouhan.coms)

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License, version 2, as 
  published by the Free Software Foundation.

  This program is distributed in the hope that it will be useful,
  but WITHOUT ANY WARRANTY; without even the implied warranty of
  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
  GNU General Public License for more details.

  You should have received a copy of the GNU General Public License
  along with this program; if not, write to the Free Software
  Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
  
 */
 
//if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
 

if ( ! class_exists( 'DOT_IRecommendThis' ) ) {
	
	
class DOT_IRecommendThis {
	
	/**
	 * @var string
	 */
	var $version = '1.4.6';

	/*--------------------------------------------*
	 * Constructor
	 *--------------------------------------------*/
	
	function __construct( $file ) {
		
		$this->file = $file;
		
		// Load text domain
		add_action( 'init', array( &$this, 'load_localisation' ), 0 );
		
		// Run this on activation / deactivation
		register_activation_hook(  __FILE__, array( &$this, 'activate' ) );
		register_deactivation_hook(  __FILE__, array( &$this, 'deactivate' ) );
		
		add_action( 'admin_menu', array( &$this, 'dot_irecommendthis_menu' ) );
		add_action( 'admin_init', array( &$this, 'dot_irecommendthis_settings' ) );
		add_action( 'wp_enqueue_scripts', array( &$this, 'dot_enqueue_scripts' ) );
		add_filter( 'the_content', array( &$this, 'dot_content' ) );
		add_action( 'publish_post', array( &$this, 'dot_setup_recommends' ) );
        add_action( 'wp_ajax_dot-irecommendthis', array( &$this, 'ajax_callback' ) );
		add_action( 'wp_ajax_nopriv_dot-irecommendthis', array( &$this, 'ajax_callback' ) );
		add_shortcode( 'dot_recommends', array( &$this, 'shortcode' ) );
		

	} // end constructor    


	/*--------------------------------------------*
	 * Localisation | Public | 1.4.6 | Return : void
	 *--------------------------------------------*/

	public function load_localisation () {
		load_plugin_textdomain( 'dot', false, dirname( plugin_basename( $this->file ) ) . '/languages/' );
	} // End load_localisation()
	
	
	/*--------------------------------------------*
	 * Activate
	 *--------------------------------------------*/
	 
	public function activate( $network_wide ) {
		
		global $wpdb;
		global $irt_dbVersion;
		
		$table_name = $wpdb->prefix . "dot_irecommendthis_votes";
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
		
		add_option('dot_irecommendthis_settings');
		//add_option('irt_jquery', '1', '', 'yes');
		//add_option('irt_onPage', '1', '', 'yes');
		//add_option('irt_textOrNotext', 'notext', '', 'yes');
		//add_option('irt_text', 'I recommend This', '', 'yes');
		//add_option('irt_textOnclick', 'recommends', '', 'yes');



	} // end activate
	
	/*--------------------------------------------*
	 * Deactivate
	 *--------------------------------------------*/
	 
	public function deactivate( $network_wide ) {
		
		// TODO define deactivation functionality here		

		global $wpdb;
		$wpdb->query("DROP TABLE IF EXISTS ".$wpdb->prefix."irecommendthis_votes");
	
		//delete_option('irt_jquery');
		//delete_option('irt_onPage');
		//delete_option('irt_textOrNotext');
		//delete_option('irt_text');
		//delete_option('irt_textOnclick');
		//delete_option('most_recommended_posts');
		//delete_option('irt_dbVersion');		

	} // end deactivate  



	private function register_plugin_version () {
		if ( $this->version != '' ) {
			update_option( 'dot-irecommendthis' . '-version', $this->version );
		}
	} 
	

	/*--------------------------------------------*
	 * Admin Menu
	 *--------------------------------------------*/

	function dot_irecommendthis_menu() {
		$page_title = __('I Recommend This', 'dot');
		$menu_title = __('I Recommend This', 'dot');
		$capability = 'manage_options';
		$menu_slug = 'dot-irecommendthis';
		$function =  array( &$this, 'dot_settings_page');
		add_options_page($page_title, $menu_title, $capability, $menu_slug, $function);
	}	//dot_irecommendthis_menu
	
	
	/*--------------------------------------------*
	 * Settings Page
	 *--------------------------------------------*/

	public function dot_irecommendthis_settings() { // whitelist options
		register_setting( 'dot-irecommendthis', 'dot_irecommendthis_settings', array(&$this, 'settings_validate') );
		add_settings_section( 'dot-irecommendthis', '', array(&$this, 'section_intro'), 'dot-irecommendthis' );		
		add_settings_field( 'show_on', __( 'Automatically display on', 'dot' ), array(&$this, 'setting_show_on'), 'dot-irecommendthis', 'dot-irecommendthis' );
		add_settings_field( 'text_zero_suffix', __( 'Text after 0 Count', 'dot' ), array(&$this, 'setting_text_zero_suffix'), 'dot-irecommendthis', 'dot-irecommendthis' );
		add_settings_field( 'text_one_suffix', __( 'Text after 1 Count', 'dot' ), array(&$this, 'setting_text_one_suffix'), 'dot-irecommendthis', 'dot-irecommendthis' );
		add_settings_field( 'text_more_suffix', __( 'Text after more than 1 Count', 'dot' ), array(&$this, 'setting_text_more_suffix'), 'dot-irecommendthis', 'dot-irecommendthis' );
		add_settings_field( 'disable_css', __( 'Disable CSS', 'dot' ), array(&$this, 'setting_disable_css'), 'dot-irecommendthis', 'dot-irecommendthis' );
		add_settings_field( 'recommend_style', __( 'Choose a style', 'dot' ), array(&$this, 'setting_recommend_style'), 'dot-irecommendthis', 'dot-irecommendthis' );
		add_settings_field( 'instructions', __( 'Shortcode and Template Tag', 'dot' ), array(&$this, 'setting_instructions'), 'dot-irecommendthis', 'dot-irecommendthis' );
	}	
	
	public function dot_settings_page() {

		?>
		<div class="wrap">
        	<?php screen_icon(); ?>

			<h2>"I Recommend This" Options</h2>
		
                <div class="metabox-holder has-right-sidebar">
                
					<!-- SIDEBAR -->
					<div class="inner-sidebar">
						
                        <!--<div class="postbox">
							<h3><span>Metabox 1</span></h3>
							<div class="inside">
								<p>Hi, I'm metabox 1!</p>
							</div>
						</div>-->
                        
					</div> <!-- //inner-sidebar -->
                        
					<!-- MAIN CONTENT -->
                    <div id="post-body">
                        <div id="post-body-content">

                            <form action="options.php" method="post">
                                <?php settings_fields( 'dot-irecommendthis' ); ?>
                                <?php do_settings_sections( 'dot-irecommendthis' ); ?>
                                <p class="submit"><input type="submit" class="button-primary" value="<?php _e( 'Save Changes', 'dot' ); ?>" /></p>
                            </form>
                        
                        </div>
                    </div> <!-- //main content -->
                
                
                </div> <!-- //metabox-holder -->

		</div>
		<?php

	} //IRecommendThisAdminContent
		
	function section_intro() {
	    ?>
		<p><?php _e('This plugin allows your visitors to simply recommend or like your posts instead of commment it.', 'dot'); ?></p>
		<p><?php _e('Check out our other themes & plugins at <a href="http://www.dreamsonline.net">Dreams Online Themes</a>.', 'dot'); ?></p>
		<?php		
	}	

	function setting_show_on() {
		$options = get_option( 'dot_irecommendthis_settings' );
		if( !isset($options['add_to_posts']) ) $options['add_to_posts'] = '1';
		if( !isset($options['add_to_other']) ) $options['add_to_other'] = '1';
		
		echo '<input type="hidden" name="dot_irecommendthis_settings[add_to_posts]" value="0" />
		<label><input type="checkbox" name="dot_irecommendthis_settings[add_to_posts]" value="1"'. (($options['add_to_posts']) ? ' checked="checked"' : '') .' />
		'. __('Posts', 'dot') .'</label><br />
		<input type="hidden" name="dot_irecommendthis_settings[add_to_other]" value="0" />
		<label><input type="checkbox" name="dot_irecommendthis_settings[add_to_other]" value="1"'. (($options['add_to_other']) ? ' checked="checked"' : '') .' />
		'. __('All other pages like Index, Archive, etc.', 'dot') .'</label><br />';
	}

	function setting_disable_css() {
		$options = get_option( 'dot_irecommendthis_settings' );
		if( !isset($options['disable_css']) ) $options['disable_css'] = '0';
		
		echo '<input type="hidden" name="dot_irecommendthis_settings[disable_css]" value="0" />
		<label><input type="checkbox" name="dot_irecommendthis_settings[disable_css]" value="1"'. (($options['disable_css']) ? ' checked="checked"' : '') .' />
		I want to use my own CSS styles</label>';
	}
	
	function setting_text_zero_suffix() {
		$options = get_option( 'dot_irecommendthis_settings' );
		if( !isset($options['text_zero_suffix']) ) $options['text_zero_suffix'] = '';
		
		echo '<input type="text" name="dot_irecommendthis_settings[text_zero_suffix]" class="regular-text" value="'. $options['text_zero_suffix'] .'" /><br />
		<span class="description">'. __('Text to display after zero count. Leave blank for no text after the count.', 'dot') .'</span>';
	}
	
	function setting_text_one_suffix() {
		$options = get_option( 'dot_irecommendthis_settings' );
		if( !isset($options['text_one_suffix']) ) $options['text_one_suffix'] = '';
		
		echo '<input type="text" name="dot_irecommendthis_settings[text_one_suffix]" class="regular-text" value="'. $options['text_one_suffix'] .'" /><br />
		<span class="description">'. __('Text to display after 1 person has recommended. Leave blank for no text after the count.', 'dot') .'</span>';
	}
	
	function setting_text_more_suffix() {
		$options = get_option( 'dot_irecommendthis_settings' );
		if( !isset($options['text_more_suffix']) ) $options['text_more_suffix'] = '';
		
		echo '<input type="text" name="dot_irecommendthis_settings[text_more_suffix]" class="regular-text" value="'. $options['text_more_suffix'] .'" /><br />
		<span class="description">'. __('Text to display after more than 1 person have recommended. Leave blank for no text after the count.', 'dot') .'</span>';
	}

	function setting_recommend_style() {
		$options = get_option( 'dot_irecommendthis_settings' );
		if( !isset($options['recommend_style']) ) $options['recommend_style'] = '0';
		
		echo '<label><input type="radio" name="dot_irecommendthis_settings[recommend_style]" value="0"'. (($options['recommend_style']) == "0" ? checked : '') .' />
		'. __('Default style - Thumb', 'dot') .'</label><br />
		
		<label><input type="radio" name="dot_irecommendthis_settings[recommend_style]" value="1"'. (($options['recommend_style']) == "1" ? checked : '') .' />
		'. __('Heart', 'dot') .'</label><br />';
	}
	
	function setting_instructions() {
		echo '<p>'. __('To use I Recomment This in your posts and pages you can use the shortcode:', 'dot') .'</p>
		<p><code>[dot_irecommendthis]</code></p>
		<p>'. __('To use I Recomment This manually in your theme template use the following PHP code:', 'dot') .'</p>
		<p><code>&lt;?php if( function_exists(\'dot_irecommendthis\') ) dot_irecommendthis(); ?&gt;</code></p>';
	}	
	
	function settings_validate($input) {
		return $input;
	}


	/*--------------------------------------------*
	 * Enqueue Scripts
	 *--------------------------------------------*/
	 
	function dot_enqueue_scripts() {
	    $options = get_option( 'dot_irecommendthis_settings' );
		if( !isset($options['disable_css']) ) $options['disable_css'] = '0';
		if( !isset($options['recommend_style']) ) $options['recommend_style'] = '0';
		
		
			if ($options['recommend_style'] == '0') {
				wp_enqueue_style( 'dot-irecommendthis', plugins_url( '/css/dot-irecommendthis.css', __FILE__ ) );
			}
			else {
				wp_enqueue_style( 'dot-irecommendthis', plugins_url( '/css/dot-irecommendthis-heart.css', __FILE__ ) );
			}
		
		
		wp_enqueue_script( 'dot-irecommendthis', plugins_url( '/js/dot_irecommendthis.js', __FILE__ ), array('jquery') );
		wp_enqueue_script( 'jquery' );
		
		wp_localize_script('dot-irecommendthis', 'dot', array(
            'ajaxurl' => admin_url('admin-ajax.php'),
        ));

		wp_localize_script( 'dot-irecommendthis', 'dot_irecommendthis', array('ajaxurl' => admin_url('admin-ajax.php')) );
	}


	/*--------------------------------------------*
	 * Content / Front-end view
	 *--------------------------------------------*/
	 
	function dot_content( $content ) {

	    // Don't show on custom page templates or pages
	    if(is_page_template() || is_page() || is_front_page()) return $content;
		
		// Don't show after excerpts				
		global $wp_current_filter;
		if ( in_array( 'get_the_excerpt', (array) $wp_current_filter ) ) {
			return $content;
		}

		$options = get_option( 'dot_irecommendthis_settings' );
		if( !isset($options['add_to_posts']) ) $options['add_to_posts'] = '0';
		if( !isset($options['add_to_other']) ) $options['add_to_other'] = '0';

		if(is_singular('post') && $options['add_to_posts']) $content .= $this->dot_recommend();
		if((is_home() || is_category() || is_tag() || is_author() || is_date() || is_search()) && $options['add_to_other'] ) $content .= $this->dot_recommend();
		
		return $content;
	}
	

	/*--------------------------------------------*
	 * Setup recommends
	 *--------------------------------------------*/

	function setup_recommends( $post_id ) 
	{
		if(!is_numeric($post_id)) return;
	
		add_post_meta($post_id, '_recommended', '0', true);
	}
	
	
	
	function ajax_callback($post_id) 
	{
		$options = get_option( 'dot_irecommendthis_settings' );
		if( !isset($options['add_to_posts']) ) $options['add_to_posts'] = '1';
		if( !isset($options['add_to_other']) ) $options['add_to_other'] = '1';
		if( !isset($options['text_zero_suffix']) ) $options['text_zero_suffix'] = '';
		if( !isset($options['text_one_suffix']) ) $options['text_one_suffix'] = '';
		if( !isset($options['text_more_suffix']) ) $options['text_more_suffix'] = '';

		if( isset($_POST['recommend_id']) ) {
		    // Click event. Get and Update Count
			$post_id = str_replace('dot-irecommendthis-', '', $_POST['recommend_id']);
			echo $this->dot_recommend_this($post_id, $options['text_zero_suffix'], $options['text_one_suffix'], $options['text_more_suffix'], 'update');
		} else {
		    // AJAXing data in. Get Count
			$post_id = str_replace('dot-irecommendthis-', '', $_POST['post_id']);
			echo $this->dot_recommend_this($post_id, $options['text_zero_suffix'], $options['text_one_suffix'], $options['text_more_suffix'], 'get');
		}
		
		exit;
	}
	
	
	function dot_recommend_this($post_id, $text_zero_suffix = false, $text_one_suffix = false, $text_more_suffix = false, $action = 'get') 
	{
		if(!is_numeric($post_id)) return;
		$text_zero_suffix = strip_tags($text_zero_suffix);
		$text_one_suffix = strip_tags($text_one_suffix);
		$text_more_suffix = strip_tags($text_more_suffix);		
		
		switch($action) {
		
			case 'get':
				$recommended = get_post_meta($post_id, '_recommended', true);
				if( !$recommended ){
					$recommended = 0;
					add_post_meta($post_id, '_recommended', $recommended, true);
				}
				
				if( $recommended == 0 ) { $suffix = $text_zero_suffix; }
				elseif( $recommended == 1 ) { $suffix = $text_one_suffix; }
				else { $suffix = $text_more_suffix; }
				
				return '<span class="dot-irecommendthis-count">'. $recommended .'</span> <span class="dot-irecommendthis-suffix">'. $suffix .'</span>';
				break;
				
			case 'update':			
		
				$recommended = get_post_meta($post_id, '_recommended', true);
				if( isset($_COOKIE['dot_irecommendthis_'. $post_id]) ) return $recommended;
				
				$recommended++;
				update_post_meta($post_id, '_recommended', $recommended);
				setcookie('dot_irecommendthis_'. $post_id, time(), time()+3600*24*365, '/');
				
				if( $recommended == 0 ) { $suffix = $text_zero_suffix; }
				elseif( $recommended == 1 ) { $suffix = $text_one_suffix; }
				else { $suffix = $text_more_suffix; }
				
				return '<span class="dot-irecommendthis-count">'. $recommended .'</span> <span class=""dot-irecommendthis-suffix">'. $suffix .'</span>';
				break;
		
		}
	}
	
	
			
	function shortcode( $atts )
	{
		extract( shortcode_atts( array(
		), $atts ) );
		
		return $this->dot_recommend();
	}


	function dot_recommend()
	{
		global $post;

        $options = get_option( 'dot_irecommendthis_settings' );
		if( !isset($options['text_zero_suffix']) ) $options['text_zero_suffix'] = '';
		if( !isset($options['text_one_suffix']) ) $options['text_one_suffix'] = '';
		if( !isset($options['text_more_suffix']) ) $options['text_more_suffix'] = '';
		
		$output = $this->dot_recommend_this($post->ID, $options['text_zero_suffix'], $options['text_one_suffix'], $options['text_more_suffix']);
  
  		$class = 'dot-irecommendthis';
  		$title = __('Recommend this', 'dot');
		if( isset($_COOKIE['dot_irecommendthis_'. $post->ID])  ){
			$class = 'dot-irecommendthis active';
			$title = __('You already recommended this', 'dot');
		}
		
		return '<a href="#" class="'. $class .'" id="dot-irecommendthis-'. $post->ID .'" title="'. $title .'">'. $output .'</a>';
	}
	

} // End Class

global $dot_irecommendthis;

// Initiation call of plugin
$dot_irecommendthis = new DOT_IRecommendThis( $file );

}

/**
 * Template Tag
 */
function dot_irecommendthis()
{
	global $dot_irecommendthis;
    echo $dot_irecommendthis->dot_recommend(); 
	
}

	/*--------------------------------------------*
	 * Settings Menus
	 *--------------------------------------------*/
	
	add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), 'dot_irecommendthis_plugin_links' );

	function dot_irecommendthis_plugin_links($links) {	
		return array_merge(
			array(
				'settings' => '<a href="' . get_bloginfo('wpurl') . '/wp-admin/options-general.php?page=dot-irecommendthis">' . __('Settings', 'dot-irecommendthis') . '</a>'
			),
			$links
		);
	}

?>
