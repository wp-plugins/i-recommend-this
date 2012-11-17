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
 
 
class DOT_IRecommendThis {
	

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
		add_action( 'admin_init', array( &$this, 'admin_init' ) );

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
		
		
		// TODO define activation functionality here
		
		global $wpdb;
		global $irt_dbVersion;
		
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
	 * Admin Menu
	 *--------------------------------------------*/
	 
	
	function dot_irecommendthis_menu() {
		$page_title = __('I Recommend This', 'dot-irecommendthis');
		$menu_title = __('I Recommend This', 'dot-irecommendthis');
		$capability = 'manage_options';
		$menu_slug = 'dot-irecommendthis';
		$function =  array( &$this, 'dot_settings_page');
		add_options_page($page_title, $menu_title, $capability, $menu_slug, $function);
	}	//dot_irecommendthis_menu
	
	
	
	public function admin_init() { // whitelist options
	
		register_setting( 'dot-irecommendthis', 'dot_irecommendthis_settings', array(&$this, 'settings_validate') );
		
		add_settings_section( 'dot-irecommendthis', '', array(&$this, 'section_intro'), 'dot-irecommendthis' );
		
		
		add_settings_field( 'on_page', __( 'Automatic display', 'dot-irecommendthis' ), array(&$this, 'setting_on_page'), 'dot-irecommendthis', 'dot-irecommendthis' );
		add_settings_field( 'text_notext', __( 'Text or No Text?', 'dot-irecommendthis' ), array(&$this, 'setting_text_notext'), 'dot-irecommendthis', 'dot-irecommendthis' );
		add_settings_field( 'text', __( 'Text', 'dot-irecommendthis' ), array(&$this, 'setting_text'), 'dot-irecommendthis', 'dot-irecommendthis' );
		add_settings_field( 'text_onclick', __( 'Text displayed on click', 'dot-irecommendthis' ), array(&$this, 'setting_text_onclick'), 'dot-irecommendthis', 'dot-irecommendthis' );
		
	}



	

	/*--------------------------------------------*
	 * Admin Page
	 *--------------------------------------------*/
	 
	public function dot_settings_page() {

		?>
		<div class="wrap">
        	<?php screen_icon(); ?>

			<h2>"I Recommend This" Options</h2>
			<?php if( isset($_GET['settings-updated']) && $_GET['settings-updated'] ){ ?>
			<div id="setting-error-settings_updated" class="updated settings-error"> 
				<p><strong><?php _e( 'Settings saved.', 'dot-irecommendthis' ); ?></strong></p>
			</div>
			<?php } ?>
			<br class="clear" />
				
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
                                <?php //do_settings_sections( 'dot-irecommendthis' ); ?>
                                <p class="submit"><input type="submit" class="button-primary" value="<?php _e( 'Save Changes', 'dot-irecommendthis' ); ?>" /></p>
                            </form>
                        
                        </div>
                    </div> <!-- //main content -->
                
                
                </div> <!-- //metabox-holder -->

		</div>
		<?php

	} //IRecommendThisAdminContent
	
	
	function section_intro()
	{
	    ?>
		<p><?php _e('ZillaLikes allows you to display like icons throughout your site. Customize the output of ZillaLike with this settings page.', 'dot-irecommendthis'); ?></p>
		<p><?php _e('Check out our other free <a href="http://www.themezilla.com/plugins/?ref=zillalikes">plugins</a> and <a href="http://www.themezilla.com/themes/?ref=zillalikes">themes</a>.', 'dot-irecommendthis'); ?></p>
		<?php
		
	}	


	function setting_on_page()
	{
		$options = get_option( 'dot_irecommendthis_settings' );
		if( !isset($options['on_page']) ) $options['on_page'] = '1';

	    echo '<input type="hidden" name="dot_irecommendthis_settings[on_page]" value="0" />
		<label><input type="checkbox" name="dot_irecommendthis_settings[on_page]" value="1"'. (($options['on_page']) ? ' checked="checked"' : '') .' />
		' . __('Automatic display', 'dot-irecommendthis') . '</label><br />
		<span class="description">'. __('If you disable this option, you have to put manually the code.', 'dot-irecommendthis') .'</span>';
	

	}
	
	function settings_validate($input)
	{
	    $input['exclude_from'] = str_replace(' ', '', trim(strip_tags($input['exclude_from'])));
		
		return $input;
	}



} // End Class

global $dot_irecommendthis;

// Initiation call of plugin
$dot_irecommendthis = new DOT_IRecommendThis( $file );



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
