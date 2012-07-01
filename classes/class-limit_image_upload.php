<?php
/**
 * Class for WordPress to limit the number and type of uploads
 *
 * PHP version 5.3
 *
 * @category   PHP
 * @package    WordPress
 * @subpackage Limit Image Uploads
 * @author     Frank Bültge <frank@bueltge.de>, Ralf Albert <me@neun12.de>
 * @license    GPLv3 http://www.gnu.org/licenses/gpl-3.0.txt
 * @version    0.2
 * @link       http://wordpress.com
 */

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
	  * Array with allowed mime types
	  * 
	  * @since  0.2
	  * @var    array
	  * @see    http://wpengineer.com/2369/restrict-mime-types-on-wordpress-media-upload/
	  * @see    http://wpengineer.com/1668/add-file-types-for-mediathek/
	  */
	public static $mime_types = array();
	
	/**
	 * The plugins textdomain
	 *
	 * @since   0.1
	 * @var     string
	 */
	public static $textdomain = '';
	
	/**
	 * Setting up some data, initialize localization and load
	 * the features
	 * 
	 * @since   0.1
	 * @return  void
	 */
	public function __construct( $args = array() ) {
		
		/*
		 * get the basic settings
		 * (int)     max_uploads  Number of maximum uploads. Default is 4
		 * (array)   post_types  Post types to exclude from upload restrictions
		 * (array)   mime_types  Allowed mime types
		 * (string)  textdomain  Textdomain to be use in this class  
		 */
		$def_args = array(
			'max_uploads' => self::$limit_upload,
			'post_types'  => self::$post_types,
			'mime_types'  => self::$mime_types,
			'textdomain'  => self::$textdomain,
		);
		
		$args = array_merge( $def_args, $args );
		
		// limit value, ensure for integer
		self::$limit_upload = intval( $args['max_uploads'] );
		
		// allowed post types
		self::$post_types = $args['post_types'];
		
		/*
		 * restrict mime types. need an array with key->value
		 * an empty array means, all mime types are allowed
		 * 
		 * e.g.:
		 *   $mime_types = array(
		 *       'pdf' => 'application/pdf',
		 *       'doc|docx' => 'application/msword',
		 *   );
		 *
		 */
		self::$mime_types = $args['mime_types'];
		
		// Textdomain
		self::$textdomain = $args['textdomain'];
		
		$filters = array(
			// handle upload
			'wp_handle_upload_prefilter' => 'limit_handle_upload_prefilter',
			// handel tabs
		 	'media_upload_tabs'          => 'control_media_upload_tabs',
			// set new active tab
			'media_upload_default_tab'   => 'set_media_upload_default_tab',
			// restrict mime types
			'upload_mimes'               => 'restrict_mime_types',
		);
		
		foreach( $filters as $hook => $method )
			add_filter( $hook, array( &$this, $method ) );
				
		// show a notification on restricted mime types
		if ( ! empty( self::$mime_types ) )
			add_action( 'post-upload-ui', array( &$this, 'restrict_mime_types_hint' ) );
		
		// cleanup
		unset( $def_args, $args, $filters );
	}
	
	/**
	 * Limit the number of uploads from images on posts
	 * 
	 * @since   0.1
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
	 * @since   0.1
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
	 * Set active tab, if reached limit
	 * 
	 * @since   0.1
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
	 * @since   0.1
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
	
	/**
	 * 
	 * Restricting the allowed uploads to selected mime-types
	 * 
	 * @since 0.2
	 * @param array $mime_types
	 */
	public function restrict_mime_types( $mime_types ) {
		
		// an empty array means, all mime types are allowed
		return self::$mime_types;
	}
	
	/**
	 * 
	 * Show a hint on the upload screen that only some mime types are allowed
	 * 
	 * @since 0.2
	 */
	public function restrict_mime_types_hint(){
		
		echo '<br />';
		_e( 'Accepted MIME types:', self::$textdomain );
		echo ' <strong>' . implode( ', ', array_flip( self::$mime_types ) ) . '</strong>';
	}
	
} // end class
