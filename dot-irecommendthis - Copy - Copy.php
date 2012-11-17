<?php

 

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
		add_action( 'publish_post', array( &$this, 'setup_recommends' ) );
		add_shortcode( 'dot_recommends', array( &$this, 'dot_irecommendthis_shortcode' ) );
		

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
	
	
	public function dot_irecommendthis_settings() { // whitelist options
		register_setting( 'dot-irecommendthis', 'dot_irecommendthis_settings', array(&$this, 'settings_validate') );
		add_settings_section( 'dot-irecommendthis', '', array(&$this, 'section_intro'), 'dot-irecommendthis' );
		add_settings_field( 'on_page', __( 'Automatic display', 'dot' ), array(&$this, 'setting_on_page'), 'dot-irecommendthis', 'dot-irecommendthis' );
		add_settings_field( 'show_text', __( 'Show Text', 'dot' ), array(&$this, 'setting_show_text'), 'dot-irecommendthis', 'dot-irecommendthis' );
		add_settings_field( 'text', __( 'Text', 'dot' ), array(&$this, 'setting_text'), 'dot-irecommendthis', 'dot-irecommendthis' );
		add_settings_field( 'text_onclick', __( 'Text displayed on click', 'dot' ), array(&$this, 'setting_text_onclick'), 'dot-irecommendthis', 'dot-irecommendthis' );
	}	


	/*--------------------------------------------*
	 * Settings Page
	 *--------------------------------------------*/

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
	
	
	function section_intro()
	{
	    ?>
		<p><?php _e('This plugin allows your visitors to simply recommend or like your posts instead of commment it.', 'dot'); ?></p>
		<p><?php _e('Check out our other themes & plugins at <a href="http://www.dreamsonline.net">Dreams Online Themes</a>.', 'dot'); ?></p>
		<?php		
	}	


	function setting_on_page()
	{
		$options = get_option( 'dot_irecommendthis_settings' );
		if( !isset($options['on_page']) ) $options['on_page'] = '1';

	    echo '<input type="hidden" name="dot_irecommendthis_settings[on_page]" value="0" />
		<label><input type="checkbox" name="dot_irecommendthis_settings[on_page]" value="1"'. (($options['on_page']) ? ' checked="checked"' : '') .' />
		' . __('Automatic display', 'dot') . '</label><br />
		<span class="description">'. __('If you disable this option, you have to put manually the code.', 'dot') .'</span>';
	}

	function setting_show_text()
	{
		$options = get_option( 'dot_irecommendthis_settings' );
		if( !isset($options['show_text']) ) $options['show_text'] = '0';
		//if( !isset($options['text_notext']) ) $options['text_notext'] = 'text_notext';
		
		echo '<label><input type="radio" name="dot_irecommendthis_settings[show_text]" value="0"'. (($options['show_text']) == "0" ? checked : '') .' />
		'. __('No Text', 'dot') .'</label><br />
		
		<label><input type="radio" name="dot_irecommendthis_settings[show_text]" value="1"'. (($options['show_text']) == "1" ? checked : '') .' />
		'. __('Show Text', 'dot') .'</label><br />';
	}


	function setting_text()
	{
		$options = get_option( 'dot_irecommendthis_settings' );
		if( !isset($options['text']) ) $options['text'] = '';
		
		echo '<input type="text" name="dot_irecommendthis_settings[text]" size="57" class="text" value="'. $options['text'] .'" /><br />
		<span class="description">'. __('The text to display after the count. Set "Show Text" to yes in order to enable this', 'dot') .'</span>';
	}
	
	function setting_text_onclick()
	{
		$options = get_option( 'dot_irecommendthis_settings' );
		if( !isset($options['text_onclick']) ) $options['text_onclick'] = '';
		
		echo '<input type="text" name="dot_irecommendthis_settings[text_onclick]" size="57" class="text_onclick" value="'. $options['text_onclick'] .'" /><br />
		<span class="description">'. __('The text to display after click.', 'dot') .'</span>';
	}

	function settings_validate($input)
	{
	    //$input['exclude_from'] = str_replace(' ', '', trim(strip_tags($input['exclude_from'])));
		
		return $input;
	}


	/*--------------------------------------------*
	 * Enqueue Scripts
	 *--------------------------------------------*/
	 
	function dot_enqueue_scripts()
	{
	    //$options = get_option( 'dot_irecommendthis_settings' );
		//if( !isset($options['disable_css']) ) $options['disable_css'] = '0';
		
		//if(!$options['disable_css']) wp_enqueue_style( 'zilla-likes', plugins_url( '/styles/zilla-likes.css', __FILE__ ) );
		wp_enqueue_style( 'dot-irecommendthis', plugins_url( '/css/dot_irecommendthis.css', __FILE__ ) );
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

		global $wpdb;
		$post_ID = get_the_ID();
		$ip = $_SERVER['REMOTE_ADDR'];
		
		$recommended = get_post_meta($post_ID, '_recommended', true) != '' ? get_post_meta($post_ID, '_recommended', true) : '0';
		$voteStatusByIp = $wpdb->get_var("SELECT COUNT(*) FROM ".$wpdb->prefix."irecommendthis_votes WHERE post_id = '$post_ID' AND ip = '$ip'");
		
		if (!isset($_COOKIE['recommended-'.$post_ID]) && $voteStatusByIp == 0) {
			if (get_option('show_text') == '0') {
				$counter = '<a onclick="recommendThis('.$post_ID.');" class="recommendThis" href="#">'.$recommended.'</a>';
			}
			else {
				$counter = '<a onclick="recommendThis('.$post_ID.');" class="recommendThis" href="#">'.$recommended.' - '.get_option('irt_text').'</a>';
			}
		}
		else {
			$counter = '<a onclick="return(false);" class="recommendThis active" href="#">'.$recommended.'</a>';
		}
	
	
		if(!is_feed() && !is_page()) {
			$content.= $counter;
		}
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
	



	function recommend_this($post_id, $action = 'get') 
	{
		if(!is_numeric($post_id)) return;	
		
		switch($action) {
		
			case 'get':
				$likes = get_post_meta($post_id, '_zilla_likes', true);
				if( !$likes ){
					$likes = 0;
					add_post_meta($post_id, '_zilla_likes', $likes, true);
				}
				
				if( $likes == 0 ) { $postfix = $zero_postfix; }
				elseif( $likes == 1 ) { $postfix = $one_postfix; }
				else { $postfix = $more_postfix; }
				
				return '<span class="zilla-likes-count">'. $likes .'</span> <span class="zilla-likes-postfix">'. $postfix .'</span>';
				break;
				
			case 'update':
				$likes = get_post_meta($post_id, '_zilla_likes', true);
				if( isset($_COOKIE['zilla_likes_'. $post_id]) ) return $likes;
				
				$likes++;
				update_post_meta($post_id, '_zilla_likes', $likes);
				setcookie('zilla_likes_'. $post_id, $post_id, time()*20, '/');
				
				if( $likes == 0 ) { $postfix = $zero_postfix; }
				elseif( $likes == 1 ) { $postfix = $one_postfix; }
				else { $postfix = $more_postfix; }
				
				return '<span class="zilla-likes-count">'. $likes .'</span> <span class="zilla-likes-postfix">'. $postfix .'</span>';
				break;
		
		}
	}
	
	
			
	function dot_irecommendthis_shortcode( $atts )
	{
		extract( shortcode_atts( array(
		), $atts ) );
		
		return $this->dot_recommends();
	}

	function dot_recommends()
	{
		global $post;

		$output = $this->recommend_this($post->ID);
  
  		$class = 'irecommendthis';
  		$title = __('Recommend this', 'dot');
		if( isset($_COOKIE['recommended-'. $post->ID]) ){
			$class = 'irecommendthis active';
			$title = __('You already like this', 'dot');
		}
		
		return '<a href="#" class="'. $class .'" id="zilla-likes-'. $post->ID .'" title="'. $title .'">'. $output .'</a>';
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
    echo $dot_irecommendthis->dot_recommends(); 
	
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
