<?php
/**
 * Plugin Name: Limit Image Upload
 * Plugin URI:  http://bueltge.de
 * Text Domain: limit-image-upload
 * Domain Path: /languages
 * Description: Limit the number of uploads from images on posts
 * Version:     1.0.4
 * Author:      Frank Bültge, Ralf Albert
 * Author URI:  http://bueltge.de
 * License:     GPLv3
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

class Init_Limit_Upload {
	
	/**
	 * Instance holder
	 *
	 * @since   0.1
	 * @var     NULL
	 */
	private static $instance = NULL;
	
	/**
	 * Constructor
	 * 
	 * @return  void
	 */
	public function __construct() {
		
		// load this plugin only on backend
		// change this if you need to load it either in frontend
		if ( ! is_admin() )
			return;
		
		$this->load_classes();
		$this->start_limit_image_upload();
	}
	
	/**
	 * Method for ensuring that only one instance of this object is used
	 *
	 * @since   0.1
	 * @return  Custom_Loop_Widget
	 */
	public static function get_instance() {
		
		if ( ! self::$instance )
			self::$instance = new self;
		
		return self::$instance;
	}
	
	/**
	 * Returns array of features, also
	 * Scans the plugins subfolder "/classes"
	 *
	 * @since   0.1
	 * @return  void
	 */
	protected function load_classes() {
		
		// load all files with the pattern class-*.php from the directory classes
		foreach( glob( dirname( __FILE__ ) . '/classes/class-*.php' ) as $class )
			require_once $class;
	}
	
	/**
	 * Init the plugin
	 * 
	 * @see     Mimetypes Image: http://www.fileformat.info/info/mimetype/image/index.htm
	 * @return  void
	 */
	public function start_limit_image_upload() {
		
		$plugin_header = new Fetch_Plugin_Header( __FILE__ );
		
		$args = array(
			'max_uploads' => 4,
			'post_types'  => array(),
			'mime_types'  => array(
				'pdf'      => 'application/pdf',
				'doc|docx' => 'application/msword',
				'png'      => 'image/png',
				'jpe'      => 'image/jpeg',
				'jpeg'     => 'image/jpeg',
				'jpg'      => 'image/jpeg',
				'bmp'      => 'image/bmp',
				'gif'      => 'image/gif',
				'tif'      => 'image/tiff',
				'tiff'     => 'image/tiff',
			),
			'textdomain' => $plugin_header->get_textdomain(),
		);
		
		unset( $plugin_header );
		new Limit_Image_Upload( $args );
	}
	
} // end class

if ( function_exists( 'add_filter' ) && class_exists( 'Init_Limit_Upload' ) )
	add_action( 'plugins_loaded', array( 'Init_Limit_Upload', 'get_instance' ) );