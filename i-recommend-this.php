<?php

 
	#### INSTALL PROCESS ####
	$irt_dbVersion = "1.0";
		
			


		
class DOT_Recommend {


	/*--------------------------------------------*
	 * Constructor
	 *--------------------------------------------*/
	
	/**
	 * Initializes the plugin by setting localization, filters, and administration functions.
	 */
	function __construct() {
		
		// TODO: replace "plugin-name-locale" with a unique value for your plugin
		load_plugin_textdomain( 'dot-irt', false, dirname( plugin_basename( __FILE__ ) ) . '/lang' );
		
	
		register_activation_hook( __FILE__, array( &$this, 'activate' ) );
		register_deactivation_hook( __FILE__, array( &$this, 'deactivate' ) );

		add_action( 'admin_menu', array( $this, 'IRecommendThisAdminMenu' ) );
		add_action( 'admin_init', array( $this, 'IRecommendThisAdminRegisterSettings' ) );
	    add_action( 'dotRecommend', array( $this, 'action_method_name' ) );
		
		add_filter( 'plugin_action_links', array( $this, 'dot_irt_plugin_links', 10, 2 ) );
		add_filter( 'the_content', array( $this, putIRecommendThis ) );
		
		//add_filter( 'plugin_action_links',  array( $this, 'irt_plugin_action_links', 10, 2  ) );
		//add_action( 'plugin_action_links', array( $this, 'irt_plugin_action_links' ) );
		
		add_action( 'init', array( $this, 'add_widget_most_recommended_posts' ) );
		add_action( 'init', array( $this, enqueueScripts ) );
		add_action( 'wp_head', array( $this, addHeaderLinks ) );


	} // end constructor    



	/**
	 * Fired when the plugin is activated.
	 *
	 * @params	$network_wide	True if WPMU superadmin uses "Network Activate" action, false if WPMU is disabled or plugin is activated on an individual blog 
	 */
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

	/*--------------------------------------------*
	 * Core Functions
	 *---------------------------------------------*/
	
	/**
 	 * Note:  Actions are points in the execution of a page or process
	 *        lifecycle that WordPress fires.
	 *
	 *		  WordPress Actions: http://codex.wordpress.org/Plugin_API#Actions
	 *		  Action Reference:  http://codex.wordpress.org/Plugin_API/Action_Reference
	 *
	 */


	public function IRecommendThisAdminRegisterSettings() { // whitelist options
		register_setting( 'irt_options', 'irt_jquery' );
		register_setting( 'irt_options', 'irt_onPage' );
		register_setting( 'irt_options', 'irt_textOrNotext' );
		register_setting( 'irt_options', 'irt_text' );
		register_setting( 'irt_options', 'irt_textOnclick' );
	}


	#### ADMIN OPTIONS ####
	public function IRecommendThisAdminMenu() {
		add_options_page('I Recommend This', __('I Recommend This', 'dot_irt'), 'manage_options', 'irt-settings', array($this, 'settings_page'));
	} //IRecommendThisAdminMenu
	

	public function settings_page() {

		?>
		<div class="wrap">
			<h2>"I Recommend This" Options</h2>
			<br class="clear" />
					
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
	

	#### WIDGET ####
	public function most_recommended_posts($numberOf, $before, $after, $show_count, $post_type="post", $raw=false) {
		global $wpdb;
	
		$request = "SELECT * FROM $wpdb->posts, $wpdb->postmeta";
		$request .= " WHERE $wpdb->posts.ID = $wpdb->postmeta.post_id";
		$request .= " AND post_status='publish' AND post_type='$post_type' AND meta_key='_recommended'";
		$request .= " ORDER BY $wpdb->postmeta.meta_value+0 DESC LIMIT $numberOf";
		$posts = $wpdb->get_results($request);
	
		if ($raw):
			return $posts;
		else:
			foreach ($posts as $item) {
				$post_title = stripslashes($item->post_title);
				$permalink = get_permalink($item->ID);
				$post_count = $item->meta_value;
				echo $before.'<a href="' . $permalink . '" title="' . $post_title.'" rel="nofollow">' . $post_title . '</a>';
				echo $show_count == '1' ? ' ('.$post_count.')' : '';
				echo $after;
			}
		endif;
	}

	/**
	 * Mini counter widget
	 */
	public function most_recommended_recommend_widget(){
		global $wpdb;
		$post_ID = get_the_ID();
		$ip = $_SERVER['REMOTE_ADDR'];
		
		$liked = get_post_meta($post_ID, '_recommended', true) != '' ? get_post_meta($post_ID, '_recommended', true) : '0';
		$voteStatusByIp = $wpdb->get_var("SELECT COUNT(*) FROM ".$wpdb->prefix."irecommendthis_votes WHERE post_id = '$post_ID' AND ip = '$ip'");
		
		
		$return='<div class="irt_counter_widget">';
		if (!isset($_COOKIE['recommended-'.$post_ID]) && $voteStatusByIp == 0) {
			$return.='<p class="irt_counter_widget_btn"><a onclick="recommendThis('.$post_ID.');">Vote!</a></p>';
		}
		else {
			$return.='<p class="irt_counter_widget_btn recommended">Voted</p>';
		}
		$return.= '<p class="irt_counter_widget_counter" id="'.$post_ID.'">'.$recommended.' lights</p>';
		$return.= '</div>';
		echo $return;	
	}

	/**
	 * SIDEBAR WIDGET
	 */
	public function add_widget_most_recommended_posts() {
		function widget_most_recommended_posts($args) {
			extract($args);
			$options = get_option("most_recommended_posts");
			if (!is_array( $options )) {
				$options = array(
				'title' => 'Most recommended posts',
				'number' => '5',
				'show_count' => '0'
				);
			}
			$title = $options['title'];
			$numberOf = $options['number'];
			$show_count = $options['show_count'];
			
			echo $before_widget;
			echo $before_title . $title . $after_title;
			echo '<ul class="mostrecommendedposts">';
	
			most_recommended_posts($numberOf, '<li>', '</li>', $show_count);
			
			echo '</ul>';
			echo $after_widget;
		}	
		wp_register_sidebar_widget('most_recommended_posts', 'Most recommended posts', 'widget_most_recommended_posts');
		
		function options_widget_most_recommended_posts() {
			$options = get_option("most_recommended_posts");
			
			if (!is_array( $options )) {
				$options = array(
				'title' => 'Most recommended posts',
				'number' => '5',
				'show_count' => '0'
				);
			}
			
			if ($_POST['mrp-submit']) {
				$options['title'] = htmlspecialchars($_POST['mrp-title']);
				$options['number'] = htmlspecialchars($_POST['mrp-number']);
				$options['show_count'] = $_POST['mrp-show-count'];
				if ( $options['number'] > 15) { $options['number'] = 15; }
				
				update_option("most_recommended_posts", $options);
			}
			?>
			<p><label for="mrp-title"><?php _e('Title:', 'i-recommend-this'); ?><br />
			<input class="widefat" type="text" id="mrp-title" name="mrp-title" value="<?php echo $options['title'];?>" /></label></p>
			
			<p><label for="mrp-number"><?php _e('Number of posts to show:', 'i-recommend-this'); ?><br />
			<input type="text" id="mrp-number" name="mrp-number" style="width: 25px;" value="<?php echo $options['number'];?>" /> <small>(max. 15)</small></label></p>
			
			<p><label for="mrp-show-count"><input type="checkbox" id="mrp-show-count" name="mrp-show-count" value="1"<?php if($options['show_count'] == '1') echo 'checked="checked"'; ?> /> <?php _e('Show post count', 'i-recommend-this'); ?></label></p>
			
			<input type="hidden" id="mrp-submit" name="mrp-submit" value="1" />
			<?php
		}
		wp_register_widget_control('most_recommended_posts', 'Most recommended posts', 'options_widget_most_recommended_posts');
	} 



	#### FRONT-END VIEW ####
	public function getIRecommendThis($arg) {
		global $wpdb;
		$post_ID = get_the_ID();
		$ip = $_SERVER['REMOTE_ADDR'];
		
		$recommended = get_post_meta($post_ID, '_recommended', true) != '' ? get_post_meta($post_ID, '_recommended', true) : '0';
		$voteStatusByIp = $wpdb->get_var("SELECT COUNT(*) FROM ".$wpdb->prefix."irecommendthis_votes WHERE post_id = '$post_ID' AND ip = '$ip'");
			
		if (!isset($_COOKIE['recommended-'.$post_ID]) && $voteStatusByIp == 0) {
			if (get_option('irt_textOrNotext') == 'notext') {
				$counter = '<a onclick="recommendThis('.$post_ID.');" class="recommendThis">'.$recommended.'</a>';
			}
			else {
				$counter = '<a onclick="recommendThis('.$post_ID.');" class="recommendThis">'.$recommended.' - '.get_option('irt_text').'</a>';
			}
		}
		else {
			$counter = '<a onclick="return(false);" class="recommendThis active">'.$recommended.'</a>';
		}

		$iRecommendThis = '<div id="iRecommendThis-'.$post_ID.'" class="iRecommendThis">';
			$iRecommendThis .= $counter;
		$iRecommendThis .= '</div>';
		
	
		if ($arg == 'put') {
			return $iRecommendThis;
		}
		else if ($arg == 'count'){
			echo $recommended;
		}
		else {
			echo $iRecommendThis;
		}
	}
   // 


	public function putIRecommendThis($content) {
		if (get_option('irt_onPage') == '1') {
			if(!is_feed() && !is_page()) {
				$content.= getIRecommendThis('put');
			}
		}
	    return $content;
	}

	


	public function enqueueScripts() {
		if (get_option('irt_jquery') == '1') {
			wp_enqueue_script('iRecommendThis', WP_PLUGIN_URL.'/i-recommend-this/js/i-recommend-this.js', array('jquery'));	
		}
		else {
			wp_enqueue_script('iRecommendThis', WP_PLUGIN_URL.'/i-recommend-this/js/i-recommend-this.js');	
		}
	}
	
	public function addHeaderLinks() {
		echo '<link rel="stylesheet" type="text/css" href="'.WP_PLUGIN_URL.'/i-recommend-this/css/i-recommend-this.css" media="screen" />'."\n";
		echo '<script type="text/javascript">var blogUrl = \''.get_bloginfo('wpurl').'\'</script>'."\n";
	}









	



} // end class



// Initiation call of plugin
new DOT_Recommend();


//global $woothemes_features;
//$woothemes_features = new Woothemes_Features( __FILE__ );

	function dot_irt_plugin_links($links, $file) {
		 static $this_plugin;
	
		 if (!$this_plugin) {
			$this_plugin = plugin_basename(__FILE__);
		 }
	
		 if ($file == $this_plugin) {
			// The "page" query string value must be equal to the slug
			// of the Settings admin page we defined earlier, which in
			// this case equals "myplugin-settings".
			$settings_link = '<a href="' . get_bloginfo('wpurl') . '/wp-admin/options-general.php?page=irt-settings">' . __('Settings', 'dot_irt') . '</a>';
			array_unshift($links, $settings_link);
		 }
	
		 return $links;
	}
		
?>
