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
		add_action( 'admin_init', array( &$this, 'IRecommendThisAdminRegisterSettings' ) );

	} // end constructor    


	/*--------------------------------------------*
	 * Localisation | Public | 1.4.6 | Return : void
	 *--------------------------------------------*/

	public function load_localisation () {
		load_plugin_textdomain( 'dot-irecommendthis', false, dirname( plugin_basename( $this->file ) ) . '/languages/' );
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
		
		add_option('irt_jquery', '1', '', 'yes');
		add_option('irt_onPage', '1', '', 'yes');
		add_option('irt_textOrNotext', 'notext', '', 'yes');
		add_option('irt_text', 'I recommend This', '', 'yes');
		add_option('irt_textOnclick', 'recommends', '', 'yes');



	} // end activate
	
	/*--------------------------------------------*
	 * Deactivate
	 *--------------------------------------------*/
	 
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
	
	


	public function IRecommendThisAdminRegisterSettings() { // whitelist options
		register_setting( 'irt_options', 'irt_jquery' );
		register_setting( 'irt_options', 'irt_onPage' );
		register_setting( 'irt_options', 'irt_textOrNotext' );
		register_setting( 'irt_options', 'irt_text' );
		register_setting( 'irt_options', 'irt_textOnclick' );
	}


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
						
                        <div class="postbox">
							<h3><span>Metabox 1</span></h3>
							<div class="inside">
								<p>Hi, I'm metabox 1!</p>
							</div>
						</div>
                        
					</div> <!-- //inner-sidebar -->
                        
					<!-- MAIN CONTENT -->
                    <div id="post-body">
                        <div id="post-body-content">
                        
                        </div>
                    </div> <!-- //main content -->
                
                
                </div> <!-- //metabox-holder -->
                
                
                
                	
                <div id="poststuff" class="ui-sortable meta-box-sortables">
                    <div id="irecommendthisoptions" class="postbox">
                    <h3><?php _e('Configuration'); ?></h3>
                        <div class="inside">
                        <form action="options.php" method="post">
                        <?php settings_fields('irt_options'); ?>
                        <table class="form-table">
                            <tr valign="top">
                                <th scope="row"><label for="irt_jquery"><?php _e('jQuery framework', 'i-recommend-this'); ?></label></th>
                                <td>
                                    <select name="irt_jquery" id="irt_jquery">
                                        <?php echo get_option('irt_jquery') == '1' ? '<option value="1" selected="selected">'.__('Enabled', 'i-recommend-this').'</option><option value="0">'.__('Disabled', 'i-recommend-this').'</option>' : '<option value="1">'.__('Enabled', 'i-recommend-this').'</option><option value="0" selected="selected">'.__('Disabled', 'i-recommend-this').'</option>'; ?>
                                    </select>
                                    <span class="description"><?php _e('Disable it if you already have the jQuery framework enabled in your theme.', 'i-recommend-this'); ?></span>
                                </td>
                            </tr>
                            <tr valign="top">
                                <th scope="row"><legend><?php _e('No Text or Text?', 'i-recommend-this'); ?></legend></th>
                                <td>
                                    <label for="irt_textOrNotext" style="padding:3px 20px 0 0; margin-right:20px;">
                                    <?php echo get_option('irt_textOrNotext') == 'notext' ? '<input type="radio" name="irt_textOrNotext" id="irt_textOrNotext" value="image" checked="checked">' : '<input type="radio" name="irt_textOrNotext" id="irt_textOrNotext" value="notext">'; ?> No Text
                                    </label>
                                    <label for="irt_text">
                                    <?php echo get_option('irt_textOrNotext') == 'text' ? '<input type="radio" name="irt_textOrNotext" id="irt_textOrNotext" value="text" checked="checked">' : '<input type="radio" name="irt_textOrNotext" id="irt_textOrNotext" value="text">'; ?>
                                    <input type="text" name="irt_text" id="irt_text" value="<?php echo get_option('irt_text'); ?>" />
                                    </label>
                                </td>
                            </tr>
                            <tr valign="top">
                                <th scope="row"><legend><?php _e('Text displayed on click', 'i-recommend-this'); ?></legend></th>
                                <td>
                                    <label for="irt_textOnclick">
                                    <input type="text" name="irt_textOnclick" id="irt_textOnclick" value="<?php echo get_option('irt_textOnclick'); ?>" />
                                    </label>
                                </td>
                            </tr>              
                            <tr valign="top">
                                <th scope="row"><legend><?php _e('Automatic display', 'i-recommend-this'); ?></legend></th>
                                <td>
                                    <label for="irt_onPage">
                                    <?php echo get_option('irt_onPage') == '1' ? '<input type="checkbox" name="irt_onPage" id="ilt_onPage" value="1" checked="checked">' : '<input type="checkbox" name="irt_onPage" id="irt_onPage" value="1">'; ?>
                                    <?php _e('<strong>On all posts</strong> (home, archives, search) at the bottom of the post', 'i-recommend-this'); ?>
                                    </label>
                                    <p class="description"><?php _e('If you disable this option, you have to put manually the code', 'i-recommend-this'); ?><code>&lt;?php if(function_exists(getIRecommendThis)) getIRecommendThis('get'); ?&gt;</code> <?php _e('wherever you want in your template.', 'i-recommend-this'); ?></p>
                                </td>
                            </tr>
                            <tr valign="top">
                                <th scope="row"><input class="button-primary" type="submit" name="Save" value="<?php _e('Save Options', 'i-recommend-this'); ?>" /></th>
                                <td></td>
                            </tr>
                        </table>
                        </form>
                        </div>
                    </div>
                </div>
		</div>
		<?php

	} //IRecommendThisAdminContent



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
