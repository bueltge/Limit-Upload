<?php

/**
 * Handles the settings
 *
 * @since   08/03/2012
 * @author  Frank Bültge
 */

class Limit_Upload_Settings extends Limit_Image_Upload {


	/**
	 * default options
	 *
	 * @var array
	 */
	protected static $default_options = array(
		'http_auth_feed' => '0',
		'max_uploads'    => 5,
		'exl_post_types' => array(),
		'mime_types'     => array(),
	);

	/**
	 * current options
	 *
	 * @var array
	 */
	public $options = array();

	/**
	 * the settings page
	 *
	 * @var string
	 */
	public $page = 'media';

	/**
	 * section identifyer
	 *
	 * @var string
	 */
	public $section = '_section';

	/**
	 * The plugins textdomain
	 *
	 * @since   0.1
	 * @var     string
	 */
	public static $textdomain = '';

	/**
	 * Instance holder
	 *
	 * @since	0.1
	 * @access	private
	 * @static
	 * @var		NULL | Inpsyde_Plugin_Creator_General
	 */
	private static $instance = NULL;
	
	/**
	 * Method for ensuring that only one instance of this object is used
	 *
	 * @since	0.1
	 * @access	public
	 * @static
	 * @return	Inpsyde_Plugin_Creator_General
	 */
	public static function get_instance() {
			
		if ( ! self::$instance )
			self::$instance = new self;
			
		return self::$instance;
	}

	/**
	 * constructor
	 *
	 * @return 
	 */
	public function __construct() {
		
		$this->key = parent::get_textdomain();
		
		$this->load_options();
		add_action( 'admin_init', array( $this, 'register_settings' ) );
	}
	
	public function register_settings() {
		
		$this->key = parent::get_textdomain();
		
		register_setting(
			$this->page,
			$this->key,
			array( $this, 'validate' )
		);
		
		add_settings_section(
			$this->section,
			__( 'Upload Restrictions' ),
			array( $this, 'description' ),
			$this->page
		);
		
		add_settings_field(
			$this->key . 'max_uploads',
			__( 'Maximal Uploads' ),
			array( $this, 'input' ),
			$this->page,
			$this->section,
			array(
				'id'        => $this->key . '_' . 'max_uploads',
				'name'      => $this->key . '[max_uploads]',
				'type'      => 'number',
				'label_for' => 'max_uploads',
				'desc'      => __( 'The value for limit upload on each post' )
			)
		);
		
		add_settings_field(
			$this->key . 'exl_post_types',
			__( 'Excludes' ),
			array( $this, 'checkboxes_list' ),
			$this->page,
			$this->section,
			$this->get_post_type_list()
		);
		
		add_settings_field(
			$this->key . 'mime_types',
			__( 'MIME types' ),
			array( $this, 'checkbox' ),
			$this->page,
			$this->section,
			array(
				'id'        => 'mime_types',
				'name'      => $this->key . '[mime_types]',
				'label_for' => 'mime_types'
			)
		);
	}
	
	public function get_post_type_list() {
		
		$post_types = get_post_types( $args=array( 'public'   => TRUE ) );
		foreach ( $post_types as $post_type ) {
			$list[$post_type] = array(
				'id' => $this->key . '_' . $post_type,
				'name' => $this->key . '[' . $post_type . ']',
				'label_for' => 'exl_post_types'
			);
			$this->options['exl_post_types'][$post_type] = 0;
		}
		
		//$this->options['exl_post_types'] = array( join( ', ', $post_types) );
		return $list;
	}
	
	/**
	 * prints the form field
	 *
	 * @param array $attr
	 * @return void
	 */
	public function checkbox( $attr ) {

		$id      = $attr['label_for'];
		$name    = $attr['name'];
		$current = $this->options[$id];
		?>
		<input
			type="checkbox"
			name="<?php echo $name; ?>"
			id="<?php echo $id; ?>"
			value="1"
			<?php checked( $current, 1 ); ?>
		/>
		<?php
	}
	
	public function checkboxes_list( $attr ) {
		
		foreach ( $attr as $post_type => $values ) {
			?>
			<input
				type="checkbox"
				name="<?php echo $values['name']; ?>"
				id="<?php echo $values['label_for']. '_' . $post_type; ?>"
				value="1"
				<?php checked( $this->options[$values['label_for']][$post_type], 1 ); ?>
			/>
			<label for="<?php echo $values['label_for']. '_' . $post_type; ?>"><?php echo $post_type; ?></label>
			<br />
			<?php
		}
		
	}
	
	public function input( $attr ) {
		
		$id      = $attr['label_for'];
		$name    = $attr['name'];
		$type    = $attr['type'];
		$current = $this->options[$id];
		$desc    = $attr['desc'];
		?>
		<input 
			name="<?php echo $name; ?>"
			type="<?php echo $type; ?>"
			step="1"
			min="0"
			id="<?php echo $id; ?>"
			value="<?php form_option($current); ?>"
			class="small-text"
		/>
		<?php
		if ( isset( $desc ) && ! empty( $desc ) )
			echo '<p class="description">' . $desc . '</p>';
		/*
		<label for="embed_size_w"><?php _e('Width'); ?></label>
<input name="embed_size_w" type="number" step="1" min="0" id="embed_size_w" value="<?php form_option('embed_size_w'); ?>" class="small-text" />
		*/
	}
	
	/**
	 * validate the input
	 *
	 * @param array $request
	 * @return array
	 */
	public function validate( $request ) {

		if ( ! empty( $request[ 'http_auth_feed' ] ) && 1 === (int) $request[ 'http_auth_feed' ] ) {
			$request[ 'http_auth_feed' ] = 1;
			$request['max_uploads'] = intval( $request['max_uploads'] );
		} else {
			$request[ 'http_auth_feed' ] = 0;
		}
		
		return $request;
	}

	/**
	 * prints the sections description, if it were needed
	 *
	 * @return void
	 */
	public function description() {
		
		return;
	}

	/**
	 * load options and set defaults if necessary
	 *
	 * @return void
	 */
	public function load_options() {
		
		$options = get_option( $this->key, '' );
		
		if ( ! is_array( $options ) ) {
			$options = self::$default_options;
			update_option( $this->key, $options );
		}
		
		$this->options = $options;
	}

}

Limit_Upload_Settings::get_instance();