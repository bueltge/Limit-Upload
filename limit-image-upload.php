<?php
/**
 * Plugin Name: Limit Image Upload
 * Plugin URI:  http://bueltge.de
 * Text Domain: limit-image-upload
 * Domain Path: /languages
 * Description: Limit the number of uploads from images on posts
 * Version:     1.0.1
 * Author:      Frank Bültge, Ralf Albert
 * Author URI:  http://bueltge.de
 * License:     GPLv3
 *
 * 
 * 
 * License:
 * ==============================================================================
 * Copyright 2012 Frank BÃ¼ltge  (email: frank@bueltge.de)
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

// load this plugin only on backend. change this if you need to load it either in frontend
if (
	function_exists( 'add_filter' )
		&&
	is_admin()
){

	if( ! class_exists( 'Fetch_Plugin_Header' ) )
		require_once 'classes/class-fetch_plugin_header.php';
		
	if( ! class_exists( 'Limit_Image_Upload' ) )
		require_once 'classes/class-limit_image_upload.php';
		
	add_action( 'plugins_loaded', 'start_limit_image_upload' );
	
	function start_limit_image_upload(){
		
		$plugin_header = new Fetch_Plugin_Header( __FILE__ );
		
		$args = array(
			
			'max_uploads'		=> 4,
			
			'post_types'	=> array(),

			'mime_types'	=> array(
								'pdf' => 'application/pdf',
								'doc|docx' => 'application/msword',
			),
			
			'textdomain'	=> $plugin_header->get_textdomain(),
		);
		
		unset( $plugin_header );
		
		new Limit_Image_Upload( $args );
		
	}
	
}