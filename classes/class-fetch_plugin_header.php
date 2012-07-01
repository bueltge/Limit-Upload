<?php
/**
 * Class for WordPress to read the data from the plugin header
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

class Fetch_Plugin_Header {
	
	/**
	 * 
	 * Array for error messages
	 * 
	 * @since  0.1
	 * @var    array
	 */
	public $errors = array();
	
	/**
	 * Array for the plugin header
	 * 
	 * @since  0.1
	 * @var    array
	 */
	public $plugin_data = array(); 
	
	/**
	 * Absolute path to the file with plugin header
	 * 
	 * @since  0.1
	 * @var    string
	 */
	public $absfile = '';
	
	/**
	 * 
	 * Constructor initialize the plugin data array
	 * 
	 * @since  0.1
	 * @param  string  $absfile  [optional] Absolute path to the file with plugin header. If set, the plugin header will be read
	 */
	public function __construct( $absfile = '' ) {
		
		if ( ! empty( $absfile ) && is_string( $absfile ) )
			$this->absfile = $absfile;
			
		if ( empty( $this->plugin_data ) && ! empty( $this->absfile ) )
			$this->get_plugin_header( $this->absfile );
		
		$this->load_plugin_textdomain();
	}
	
	/**
	 * 
	 * Check if the internal value $absfile was set correctly
	 * 
	 * @since   0.1
	 * @return  TRUE|NULL	TRUE on success, NULL on error
	 */
	protected function check_absfile() {
		
		if ( empty( $this->absfile ) && ! is_string( $this->absfile ) ){
			
			array_push( $this->errors[], 'No filepath was set.' );
			return NULL;
			
		} else {
			
			return TRUE;
			
		}
	}
	
	/**
	 * 
	 * Checks if the internal array $plugin_data is not empty. If it is, try to read the plugin header
	 * 
	 * @since   0.1
	 * @return  TRUE|NULL	TRUE on success, NULL on error
	 */
	protected function check_plugin_data() {
		
		if ( empty( $this->plugin_data ) ) {
			
			// returns TRUE (on success) or NULL (on error)
			return $this->get_plugin_header();
			
		} else {
			
			return TRUE;
			
		}
	}
	
	
	/**
	 * Get a value from the plugin header
	 *
	 * @since   0.1
	 * @return  TRUE|NULL	TRUE on success, NULL on error
	 * @uses    get_plugin_data, ABSPATH
	 */
	public function get_plugin_header() {
		
		if ( TRUE !== $this->check_absfile() )
			return NULL;
		
		if ( ! function_exists( 'get_plugin_data' ) )
			require_once( ABSPATH . '/wp-admin/includes/plugin.php' );
	
		$this->plugin_data = get_plugin_data( $this->absfile );
		
		if ( empty( $this->plugin_data ) ){
			
			array_push( $this->errors, 'Can not read plugin header' );
			return NULL;
			
		} else {
			
			return TRUE;
			
		}	
		
	}
	
	/**
	 * 
	 * Get a sindgle value from the plugin header
	 * 
	 * @param   string  $value  The value to get from the plugin header
	 * @return  string  string  The requested value or NULL on error
	 */
	public function get_value( $value = 'TextDomain' ) {
		
		if ( TRUE !== $this->check_plugin_data() )
			return NULL;
		
		return ( isset( $this->plugin_data[$value] ) ) ? $this->plugin_data[$value] : NULL;
	}
	
	/**
	 * Get the Textdomain
	 *
	 * @since   0.1
	 * @return  string	The plugins textdomain or NULL on error
	 */
	public function get_textdomain() {
		
		return $this->get_value( 'TextDomain' );
	}
	
	/**
	 * Load the localization
	 *
	 * @since   0.1
	 * @return  TRUE|NULL  TRUE on success, NULL on error
	 * @uses    load_plugin_textdomain, plugin_basename
	 */
	public function load_plugin_textdomain() {
		
		if ( TRUE !== $this->check_plugin_data()
			||
			empty( $this->plugin_data['TextDomain'] )
		) {
			return NULL;
		}
		
		if ( ! isset( $this->plugin_data['DomainPath'] )
			||
			empty( $this->plugin_data['DomainPath'] ) ) {
			
			array_push( $this->errors, 'DomainPath was not set' );
			return NULL;
			
		} else {
			
			return load_plugin_textdomain(
				$this->plugin_data['TextDomain'],
				FALSE,
				dirname( plugin_basename( $this->absfile ) ) . $this->plugin_data['DomainPath']
			);
			
		}
	}
	
} // end class