<?php
/**
 * Plugin Name: Limit Image Upload
 * Plugin URI:  http://bueltge.de
 * Text Domain: limit-image-upload
 * Domain Path: /languages
 * Description: Limit the number of uploads from images on posts
 * Version:     1.0.0
 * Author:      Frank Bültge
 * Author URI:  http://bueltge.de
 * License:     GPLv3
 *
 * 
 * 
 * License:
 * ==============================================================================
 * Copyright 2012 Frank Bültge  (email: frank@bueltge.de)
 * 
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 * 
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 * 
 * Requirements:
 * ==============================================================================
 * This plugin was tested with WordPress 3.4 and PHP Interpreter 5.3
 */
 
// This file is not called by WordPress. We don't like that.
if ( ! function_exists( 'add_filter' ) ) {
	echo "Hi there!  I'm just a part of plugin, not much I can do when called directly.";
	exit;
}

class Limit_Image_Upload {
	
	/**
	 * The value for limit upload on each post
	 * 
	 * @since   0.1
	 * @var     integer
	 */
	public static $limit_upload = 4;
	
	/**
	 * Define post types for exclude from upload restrictions
	 *
	 * @since   0.1
	 * @var     array
	 */
	 public static $post_types = array();
	
	/**
	 * The plugins textdomain
	 *
	 * @since   0.1
	 * @var     string
	 */
	public static $textdomain = '';
	
	/**
	 * The plugins textdomain path
	 *
	 * @since  0.1
	 * @var    string
	 */
	public static $textdomainpath = '';
	
	/**
	 * Instance holder
	 *
	 * @since  0.1
	 * @var    NULL | Custom_Loop_Widget
	 */
	private static $instance = NULL;
	
	/**
	 * Method for ensuring that only one instance of this object is used
	 *
	 * @since   0.1
	 * @return  __CLASS__
	 */
	public static function get_instance() {
		
		if ( ! self::$instance )
			self::$instance = new self;
		
		return self::$instance;
	}
	
	/**
	 * Setting up some data, initialize localization and load
	 * the features
	 * 
	 * @since   0.1
	 * @return  void
	 */
	public function __construct () {
		
		// limit value, ensure for integer
		self::$limit_upload = intval( self::$limit_upload );
		// Textdomain
		self::$textdomain = $this->get_textdomain();
		// Textdomain Path
		self::$textdomainpath = $this->get_plugin_header( 'DomainPath' );
		// Initialize the localization
		$this->load_plugin_textdomain();
		// handle upload
		add_filter( 'wp_handle_upload_prefilter', array( $this, 'limit_handle_upload_prefilter' ) );
		// handel tabs
		add_filter( 'media_upload_tabs', array( $this, 'control_media_upload_tabs' ) );
		// set new active tab
		add_filter( 'media_upload_default_tab', array( $this, 'set_media_upload_default_tab' ) );
	}
	
	/**
	 * Get a value of the plugin header
	 *
	 * @since   0.1
	 * @param   string $value
	 * @uses    get_plugin_data, ABSPATH
	 * @return  string The plugin header value
	 */
	protected function get_plugin_header( $value = 'TextDomain' ) {
		
		if ( ! function_exists( 'get_plugin_data' ) )
			require_once( ABSPATH . '/wp-admin/includes/plugin.php' );
	
		$plugin_data = get_plugin_data( __FILE__ );
		$plugin_value = $plugin_data[ $value ];
	
		return $plugin_value;
	}
	
	/**
	 * Get the Textdomain
	 *
	 * @since   0.1
	 * @return  string The plugins textdomain
	 */
	public function get_textdomain() {
		
		return $this->get_plugin_header( 'TextDomain' );
	}
	
	/**
	 * Load the localization
	 *
	 * @since   0.1
	 * @uses    load_plugin_textdomain, plugin_basename
	 * @return  void
	 */
	public function load_plugin_textdomain() {
		
		load_plugin_textdomain( 
			self::$textdomain,
			FALSE,
			dirname( plugin_basename( __FILE__ ) ) . self::$textdomainpath
		);
	}
	
	/**
	 * Limit the number of uploads from images on posts
	 * 
	 * @since   1.0.0
	 * @param   array $file Reference to a single element of $_FILES. Call the function once for each uploaded file.
	 * @return  array $file Reference to a single element of $_FILES. Call the function once for each uploaded file.
	 */
	public function limit_handle_upload_prefilter( $file ) {
		// check for excludes of post types
		if ( in_array( $GLOBALS['post_type'], self::$post_types ) )
			return $file;
		
		// This conditional is for the flash uploader
		if ( $file['type'] == 'application/octet-stream' && isset( $file['tmp_name'] ) ) {
			
			$file_size = getimagesize( $file['tmp_name'] );
			if ( isset( $file_size['error'] ) && 0 != $file_size['error'] ) {
				$file['error'] = __( 'Unexpected Error:', self::$textdomain ) . ' ' . $file_size['error'];
				return $file;
			} else {
				$file['type'] = $file_size['mime'];
			}
		}
		
		// count start with 0
		if ( $this->get_count_attachments() > self::$limit_upload - 1 ) {
			$file['error'] = sprintf( 
				__( 'Sorry, you cannot upload more than %d images.', self::$textdomain ),
				self::$limit_upload
			);
		}
		
		return $file;
	}
	
	/**
	 * Remove tabs on media uploader, if reached limit
	 * 
	 * @since   1.0.0
	 * @param   array  $tabs
	 * @return  array  $tabs
	 */
	public function control_media_upload_tabs( $tabs ) {
		
		if ( $this->get_count_attachments() >= self::$limit_upload ) {
			unset( $tabs['type'] );
			unset( $tabs['type_url'] );
		}
		
		
		return $tabs;
	}
	
	/**
	 * Remove tabs on media uploader, if reached limit
	 * 
	 * @since   1.0.0
	 * @param   array  $tabs
	 * @return  array  $tabs
	 */
	public function set_media_upload_default_tab( $current ) {
		
		if ( $this->get_count_attachments() >= self::$limit_upload )
			$current = 'gallery';
		
		return $current;
	}
	
	/**
	 * Count attachments to each post
	 * 
	 * @since   1.0.0
	 * @return  integer
	 */
	protected function get_count_attachments() {
		
		if ( ! isset( $_REQUEST['post_id'] ) )
			return;
			
		$post_id = intval( $_REQUEST['post_id'] );
		
		if ( $post_id ) {
			// $attachments = intval( $wpdb->get_var( $wpdb->prepare( "SELECT count(*) FROM $wpdb->posts WHERE post_type = 'attachment' AND post_status != 'trash' AND post_parent = %d", $post_id ) ) );
			$args = array(
				'post_type'   => 'attachment',
				'post_parent' => $post_id
			);
			return intval( count( get_posts( $args ) ) );
		}
	}
	
} // end class

if ( function_exists( 'add_filter' ) && class_exists( 'Limit_Image_Upload' ) )
	add_action( 'plugins_loaded', array( 'Limit_Image_Upload', 'get_instance' ) );
