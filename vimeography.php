<?php
/*
	Plugin Name: Vimeography
	Plugin URI: http://vimeography.com
	Description: Vimeography is the easiest way to set up a custom Vimeo gallery on your site.
	Version: 0.5.1
	Author: Dave Kiss
	Author URI: http://davekiss.com
	License: MIT
*/
	
if (!function_exists('json_decode'))
	wp_die('Vimeography needs the JSON PHP extension.');
	
global $wpdb;
$wp_upload_dir = wp_upload_dir();

// Define constants
define( 'VIMEOGRAPHY_URL', plugin_dir_url(__FILE__) );
define( 'VIMEOGRAPHY_PATH', plugin_dir_path(__FILE__) );
define( 'VIMEOGRAPHY_THEME_URL', $wp_upload_dir['baseurl'].'/vimeography-themes/' );
define( 'VIMEOGRAPHY_THEME_PATH', $wp_upload_dir['basedir'].'/vimeography-themes/' );
define( 'VIMEOGRAPHY_BASENAME', plugin_basename( __FILE__ ) );
define( 'VIMEOGRAPHY_VERSION', '0.5');
define( 'VIMEOGRAPHY_GALLERY_TABLE', $wpdb->prefix . "vimeography_gallery");
define( 'VIMEOGRAPHY_GALLERY_META_TABLE', $wpdb->prefix . "vimeography_gallery_meta");
define( 'VIMEOGRAPHY_CURRENT_PAGE', basename($_SERVER['PHP_SELF']));

require_once(VIMEOGRAPHY_PATH . '/vendor/mustache/Mustache.php');
		
class Vimeography
{								
	public function __construct()
	{
		add_action( 'init', array(&$this, 'vimeography_init') );
		add_action( 'admin_init', array(&$this, 'vimeography_requires_wordpress_version') );
		add_action( 'plugins_loaded', array(&$this, 'vimeography_update_db_check') );
		add_action( 'admin_menu', array(&$this, 'vimeography_add_menu') );
		
		register_activation_hook( VIMEOGRAPHY_BASENAME, array(&$this, 'vimeography_create_tables') );
		
		add_filter( 'plugin_action_links', array(&$this, 'vimeography_filter_plugin_actions'), 10, 2 );
		add_shortcode( 'vimeography', array(&$this, 'vimeography_shortcode') );
				
		// Add shortcode support for widgets  
		add_filter( 'widget_text', 'do_shortcode' );
	}
		
	public function vimeography_init()
	{
		if(in_array(VIMEOGRAPHY_CURRENT_PAGE, array('post.php', 'page.php', 'page-new.php', 'post-new.php'))){
	        add_action('admin_footer',  array(&$this, 'vimeography_add_mce_popup'));
	    }

		if ( get_user_option('rich_editing') == 'true' ) {
			add_filter( 'mce_external_plugins', array(&$this, 'vimeography_add_editor_plugin' ));
			add_filter( 'mce_buttons', array(&$this, 'vimeography_register_editor_button') );
		}

	}
	
	public function vimeography_register_editor_button($buttons)
	{
	 	array_push( $buttons, "|", "vimeography" );
	 	return $buttons;
 	}
 	
	public function vimeography_add_editor_plugin( $plugin_array ) {
		$plugin_array['vimeography'] = VIMEOGRAPHY_URL . 'media/js/mce.js';
		return $plugin_array;
	}
	
	/**
	 * Check the wordpress version is compatible, and disable plugin if not.
	 * 
	 * @access public
	 * @return void
	 */
	public function vimeography_requires_wordpress_version() {
		global $wp_version;
		$plugin = plugin_basename( __FILE__ );
		$plugin_data = get_plugin_data( __FILE__, false );
	
		if ( version_compare($wp_version, "3.3", "<" ) ) {
			if( is_plugin_active($plugin) ) {
				deactivate_plugins( $plugin );
				wp_die( "'".$plugin_data['Name']."' requires WordPress 3.3 or higher, and has been deactivated! Please upgrade WordPress and try again.<br /><br />Back to <a href='".admin_url()."'>WordPress admin</a>." );
			}
		}
	}
	
	/**
	 * Check if the Vimeography database needs updated based on the db version.
	 * 
	 * @access public
	 * @return void
	 */
	public function vimeography_update_db_check()
	{
		if (get_option('vimeography_db_version') != VIMEOGRAPHY_VERSION)
			$this->vimeography_create_tables();
			
		update_option('vimeography_db_version', VIMEOGRAPHY_VERSION);
	}
				
	/**
	 * Add Settings link to "installed plugins" admin page.
	 * 
	 * @access public
	 * @param mixed $links
	 * @param mixed $file
	 * @return void
	 */
	public function vimeography_filter_plugin_actions($links, $file)
	{		
		if ( $file == VIMEOGRAPHY_BASENAME )
		{
			$settings_link = '<a href="admin.php?page=vimeography-edit-galleries">' . __('Settings') . '</a>';
			if (!in_array($settings_link, $links))
				array_unshift( $links, $settings_link ); // before other links
		}
		return $links;
	}
	
    /**
     * Action target that displays the popup to insert a form to a post/page.
     * 
     * @access public
     * @return void
     */
    public function vimeography_add_mce_popup(){
		require_once(VIMEOGRAPHY_PATH . 'lib/admin/view/vimeography/mce.php');
		$mustache = new Vimeography_MCE();
		$template = $this->_load_template('vimeography/mce');
		echo $mustache->render($template);
    }

	/**
	 * Adds a new top level menu to the admin menu.
	 * 
	 * @access public
	 * @return void
	 */
	public function vimeography_add_menu()
	{
		
		global $submenu;
		
		add_menu_page( 'Vimeography Page Title', 'Vimeography', 'manage_options', 'vimeography-edit-galleries', '', VIMEOGRAPHY_URL.'media/img/vimeography-icon.png' );
		add_submenu_page( 'vimeography-edit-galleries', 'Edit Galleries', 'Edit Galleries', 'manage_options', 'vimeography-edit-galleries', array(&$this, 'vimeography_render_template' ));
		add_submenu_page( 'vimeography-edit-galleries', 'New Gallery', 'New Gallery', 'manage_options', 'vimeography-new-gallery', array(&$this, 'vimeography_render_template' ));
		add_submenu_page( 'vimeography-edit-galleries', 'My Themes', 'My Themes', 'manage_options', 'vimeography-my-themes', array(&$this, 'vimeography_render_template' ));
		$submenu['vimeography-edit-galleries'][500] = array( 'Buy Themes', 'manage_options' , 'http://vimeography.com/themes' );
		add_submenu_page( 'vimeography-edit-galleries', 'Vimeography Pro', 'Vimeography Pro', 'manage_options', 'vimeography-pro', array(&$this, 'vimeography_render_template' ));
		add_submenu_page( 'vimeography-edit-galleries', 'Help', 'Help', 'manage_options', 'vimeography-help', array(&$this, 'vimeography_render_template' ));

	}
	
	public function vimeography_render_template()
	{
		if ( !current_user_can( 'manage_options' ) )  {
			wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
		}
		
		wp_register_style( 'bootstrap_css', VIMEOGRAPHY_URL.'media/css/bootstrap.min.css');
		wp_register_style( 'bootstrap_responsive_css', VIMEOGRAPHY_URL.'media/css/bootstrap-responsive.min.css');
		wp_register_style( 'vimeography-admin.css', VIMEOGRAPHY_URL.'media/css/admin.css');
		wp_register_script( 'bootstrap_tab_js', VIMEOGRAPHY_URL.'media/js/bootstrap-tab.js');
		wp_register_script( 'bootstrap_alert_js', VIMEOGRAPHY_URL.'media/js/bootstrap-alert.js');
		wp_register_script( 'vimeography-admin.js', VIMEOGRAPHY_URL.'media/js/admin.js', 'jquery');
		
		wp_enqueue_style( 'bootstrap_css');
		wp_enqueue_style( 'bootstrap_responsive_css');
		wp_enqueue_style( 'vimeography-admin.css');
		wp_enqueue_script( 'jquery');
		wp_enqueue_script( 'bootstrap_tab_js');
		wp_enqueue_script( 'bootstrap_alert_js');
		wp_enqueue_script( 'vimeography-admin.js');
				
		switch(current_filter())
		{
			case 'vimeography_page_vimeography-new-gallery':
				require_once(VIMEOGRAPHY_PATH . 'lib/admin/view/gallery/new.php');
				$mustache = new Vimeography_Gallery_New();
				$template = $this->_load_template('gallery/new');
				break;
			case 'toplevel_page_vimeography-edit-galleries':
				if (isset($_GET['id']))
				{
					require_once(VIMEOGRAPHY_PATH . 'lib/admin/view/gallery/edit.php');
					$mustache = new Vimeography_Gallery_Edit();
					$template = $this->_load_template('gallery/edit');
				}
				else
				{
					require_once(VIMEOGRAPHY_PATH . 'lib/admin/view/gallery/list.php');
					$mustache = new Vimeography_Gallery_List();
					$template = $this->_load_template('gallery/list');
				}				
				break;
			case 'vimeography_page_vimeography-my-themes':
				require_once(VIMEOGRAPHY_PATH . 'lib/admin/view/theme/list.php');
				$mustache = new Vimeography_Theme_List();
				$template = $this->_load_template('theme/list');
				break;
			case 'vimeography_page_vimeography-pro':
				require_once(VIMEOGRAPHY_PATH . 'lib/admin/view/vimeography/pro.php');
				$mustache = new Vimeography_Pro();
				$template = $this->_load_template('vimeography/pro');
				break;
			case 'vimeography_page_vimeography-help':
				require_once(VIMEOGRAPHY_PATH . 'lib/admin/view/vimeography/help.php');
				$mustache = new Vimeography_Help();
				$template = $this->_load_template('vimeography/help');
				break;
			default:
				wp_die( __('The admin template for "'.current_filter().'" cannot be found.') );
			break;
		}
		echo $mustache->render($template);
	}
	
	protected function _load_template($name)
	{
		$path = VIMEOGRAPHY_PATH . 'lib/admin/templates/' . $name .'.mustache';
		if (! $result = @file_get_contents($path))
			wp_die('The admin template "'.$name.'" cannot be found.');
		return $result;
	}
		
	/**
	 * Create tables and define defaults when plugin is activated.
	 * 
	 * @access public
	 * @return void
	 */
	public function vimeography_create_tables() {
		global $wpdb;
		
		delete_option('vimeography_default_settings');
		delete_option('vimeography_advanced_settings');
		
		add_option('vimeography_advanced_settings', array(
			'active' => FALSE,
			'client_id' => '',
			'client_secret' => '',
			'access_token' => '',
			'access_token_secret' => '',
		));
		
		add_option('vimeography_default_settings', array(
			'source_type' => 'channel',
			'source_name' => 'hd',
			'video_count' => 20,
			'featured_video' => '',
			'cache_timeout' => 3600,
			'theme_name' => 'bugsauce',
		));
			      
		$sql = 'CREATE TABLE '.VIMEOGRAPHY_GALLERY_TABLE.' (
		id mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
		title varchar(150) NOT NULL,
		date_created datetime NOT NULL,
		is_active tinyint(1) NOT NULL,
		PRIMARY KEY (id)
		);
		CREATE TABLE '.VIMEOGRAPHY_GALLERY_META_TABLE.' (
		id mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
		gallery_id mediumint(8) unsigned NOT NULL,
		source_type varchar(50) NOT NULL,
		source_name varchar(50) NOT NULL,
		video_count mediumint(7) NOT NULL,
		featured_video int(9) unsigned DEFAULT NULL,
		cache_timeout mediumint(7) NOT NULL,
		theme_name varchar(50) NOT NULL,
		PRIMARY KEY (id)
		);
		';
			
		require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
		dbDelta($sql);
		add_option("vimeography_db_version", VIMEOGRAPHY_VERSION);
		
		// Let's also move the vimeography_themes folder to wp-content/uploads			    	    
		$this->rcopy(VIMEOGRAPHY_PATH . 'vimeography-themes/' , VIMEOGRAPHY_THEME_PATH );
	    
	}
																   	
	/**
	 * Read the shortcode and return the output.
	 * example:
	 * [vimeography from='user' named='davekiss' theme='apple']
	 * 
	 * @access public
	 * @param mixed $atts
	 * @return void
	 */
	public function vimeography_shortcode($atts)
	{

		// Get admin panel options
		$default_settings = get_option('vimeography_default_settings');

		// Get shortcode attributes
		$settings = shortcode_atts( array(
			'id' => '',
			'theme' => $default_settings['theme_name'],
			'featured' => $default_settings['featured_video'],
			'from' => $default_settings['source_type'],
			'named' => $default_settings['source_name'],
			'limit' => $default_settings['video_count'],
			'cache' => $default_settings['cache_timeout'],
			'width' => '',
			'height' => '',
		), $atts );
		
		if (intval($settings['id']))
		{
			global $wpdb;
			$gallery_info = $wpdb->get_results('SELECT * from '.VIMEOGRAPHY_GALLERY_META_TABLE.' AS meta JOIN '.VIMEOGRAPHY_GALLERY_TABLE.' AS gallery ON meta.gallery_id = gallery.id WHERE meta.gallery_id = '.$settings['id'].' LIMIT 1;');
			if ($gallery_info)
			{
				$settings['theme']    = $gallery_info[0]->theme_name;
				$settings['featured'] = $gallery_info[0]->featured_video;
				$settings['from']     = $gallery_info[0]->source_type;
				$settings['named']    = $gallery_info[0]->source_name;
				$settings['limit']    = $gallery_info[0]->video_count;
				$settings['cache']    = $gallery_info[0]->cache_timeout;
			}
		}
		
		try
		{
			require_once(VIMEOGRAPHY_PATH . 'lib/core.php');
		    $vimeography = Vimeography_Core::factory('videos', $settings);
		    
			// if cache is set, render it. otherwise, get the json, set the cache, and render it		
			if (($vimeography_data = $this->get_vimeography_cache($settings['id'])) === FALSE)
			{
		    	// cache not set, let's do a new request to the vimeo API and cache it
		        $vimeography_data = $vimeography->get('videos');
		        $transient = $this->set_vimeography_cache($settings['id'], $vimeography_data, $settings['cache']);
			}
			return $vimeography->render($vimeography_data);
		}
		catch (Vimeography_Exception $e)
		{
			return "Vimeography error: ".$e->getMessage();
		}
	}
		
	/**
	 * Get the JSON data stored in the Vimeography cache for the provided gallery id.
	 * 
	 * @access public
	 * @static
	 * @param mixed $id
	 * @return void
	 */
	public static function get_vimeography_cache($id)
	{
		return FALSE === ( $vimeography_cache_results = get_transient( 'vimeography_cache_'.$id ) ) ? FALSE : $vimeography_cache_results;
    }
    
    /**
     * Set the JSON data to the Vimeography cache for the provided gallery id.
     * 
     * @access public
     * @static
     * @param mixed $id
     * @param mixed $data
     * @param mixed $cache_limit
     * @return void
     */
    public static function set_vimeography_cache($id, $data, $cache_limit)
    {
		return set_transient( 'vimeography_cache_'.$id, $data, $cache_limit );
    }
    
    /**
     * Clear the Vimeography cache for the provided gallery id.
     * 
     * @access public
     * @static
     * @param mixed $id
     * @return void
     */
    public static function delete_vimeography_cache($id)
    {
    	return delete_transient('vimeography_cache_'.$id);
    }
    
    // Function to remove folders and files 
    private function rrmdir($dir) {
        if (is_dir($dir)) {
            $files = scandir($dir);
            foreach ($files as $file)
                if ($file != "." && $file != "..") $this->rrmdir("$dir/$file");
            rmdir($dir);
        }
        else if (file_exists($dir)) unlink($dir);
    }

    // Function to Copy folders and files       
    private function rcopy($source, $dst) {
        if (file_exists ( $dst ))
            $this->rrmdir ( $dst );
        if (is_dir ( $source )) 
        {
            mkdir ( $dst );
            $files = scandir ( $source );
            foreach ( $files as $file )
                if ($file != "." && $file != "..")
                    $this->rcopy ( $source.'/'.$file, $dst.'/'.$file );
        } else if (file_exists ( $source ))
            copy ( $source, $dst );
            $this->rrmdir($source);
    }  
	
}

new Vimeography;