<?php
/**
 * Options class
 */
class Venomaps_Options {
	/**
	 * Refers to a single instance of this class.
	 *
	 * @var $instance
	 */
	private static $instance = null;

	/**
	 * Plugin options
	 *
	 * @var $options
	 */
	private $options = null;

	/**
	 * Creates or returns an instance of this class.
	 *
	 * @return  Venomaps_Options A single instance of this class.
	 */
	public static function get_instance() {

		if ( null == self::$instance ) {
			self::$instance = new self();
			self::$instance->hooks();
		}
		return self::$instance;
	}

	/**
	 * Initializes the plugin
	 */
	private function __construct() {
		// Get registered option.
		$this->options = get_option( 'venomaps_settings' );
	}

	/**
	 * Initiate hooks
	 */
	public function hooks() {
		add_action( 'admin_menu', array( $this, 'add_page' ) );
		add_action( 'admin_init', array( $this, 'register_page_options' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'load_scripts' ) );
	}

	/**
	 * Validate all fields.
	 *
	 * @param string $hook page hook.
	 */
	public function load_scripts( $hook ) {
		if ( 'settings_page_venomaps' != $hook ) {
			return;
		}

		$min = defined( 'WP_DEBUG' ) && true === WP_DEBUG ? '' : '.min';
		wp_enqueue_script( 'venomaps-box-image', plugins_url( 'js/venomaps-admin' . $min . '.js', __FILE__ ), array( 'jquery' ), VENOMAPS_VERSION );
		wp_enqueue_style( 'venomaps-admin', plugins_url( 'css/venomaps-admin' . $min . '.css', __FILE__ ), array(), VENOMAPS_VERSION );
	}

	/**
	 * Add the options page under Setting Menu.
	 */
	public function add_page() {
		// $page_title, $menu_title, $capability, $menu_slug, $callback_function
		$page_title = __( 'VenoMaps', 'venomaps' );
		$menu_title = __( 'VenoMaps', 'venomaps' );
		add_options_page( $page_title, $menu_title, 'manage_options', 'venomaps', array( $this, 'display_page' ) );
	}

	/**
	 * Display the options page.
	 */
	public function display_page() {
		?>
		<div class="wrap">
			<h2><?php esc_attr_e( 'VenoMaps Settings', 'venomaps' ); ?></h2>
			<form method="post" action="options.php">     
				<?php
				settings_fields( __FILE__ );
				do_settings_sections( __FILE__ );
				submit_button();
				?>
			</form>
		</div> <!-- /wrap -->
		<?php
	}

	/**
	 * Register admin page options.
	 */
	public function register_page_options() {
		add_settings_section( 'venomaps_section', __( 'Options', 'venomaps' ), array( $this, 'display_section' ), __FILE__ ); // id, title, display cb, page.
		add_settings_field( 'venomaps_style_field', __( 'Style', 'venomaps' ), array( $this, 'style_settings_field' ), __FILE__, 'venomaps_section' );
		// add_settings_field( 'venomaps_geo_field', __( 'Utilities', 'venomaps' ), array( $this, 'geo_settings_field' ), __FILE__, 'venomaps_section' );
		register_setting( __FILE__, 'venomaps_settings', array( $this, 'validate_options' ) ); // option group, option name, sanitize cb.
	}

	/**
	 * Validate all fields.
	 *
	 * @param array $values posted fields.
	 */
	public function validate_options( $values ) {

		$valid_fields = array();

		foreach ( $values as $firstkey => $value ) {
			if ( ! is_array( $value ) ) {
				$valid_fields[ $firstkey ] = esc_attr( $value );
			} else {
				foreach ( $value as $secondkey => $value ) {
					if ( ! is_array( $value ) ) {
						$valid_fields[ $firstkey ][ $secondkey ] = esc_attr( $value );
					} else {
						foreach ( $value as $thirdkey => $value ) {
							$valid_fields[ $firstkey ][ $secondkey ][ $thirdkey ] = esc_attr( $value );
						}
					}
				}
			}
		}

		$cleanstyles = array();

		if ( is_array( $valid_fields ) ) {
			foreach ( $valid_fields as $key => $value ) {
				if ( isset( $value['url'] ) && strlen( $value['url'] ) ) {
					$cleanstyles[ $key ] = $value;
				}
			}
		}

		return apply_filters( 'validate_options', $valid_fields, $values );
	}

	/**
	 * Callback function for settings section
	 */
	public function display_section() {
	}

	/**
	 * Custom Maps
	 */
	public function style_settings_field() {

		$style = isset( $this->options['style'] ) ? $this->options['style'] : false;
		?>

		<h2><?php esc_html_e( 'Default Maps', 'venomaps' ); ?></h2>
		<div class="venomaps-default-maps">
			<div class="venomaps-image-placeholder">
				<img src="<?php echo esc_url( plugins_url( '/images/maps/default.jpg', __FILE__ ) ); ?>">
				<p>Default</p>
			</div>
			<div class="venomaps-image-placeholder">
				<img src="<?php echo esc_url( plugins_url( '/images/maps/terrain.jpg', __FILE__ ) ); ?>">
				<p>Terrain</p>
			</div>
			<div class="venomaps-image-placeholder">
				<img src="<?php echo esc_url( plugins_url( '/images/maps/toner.jpg', __FILE__ ) ); ?>">
				<p>Toner</p>
			</div>
			<div class="venomaps-image-placeholder">
				<img src="<?php echo esc_url( plugins_url( '/images/maps/watercolor.jpg', __FILE__ ) ); ?>">
				<p>Watercolor</p>
			</div>
			<div class="venomaps-image-placeholder">
				<img src="<?php echo esc_url( plugins_url( '/images/maps/wikimedia.jpg', __FILE__ ) ); ?>">
				<p>Wikimedia</p>
			</div>
		</div>

		<h2><?php esc_html_e( 'Custom Maps', 'venomaps' ); ?></h2>
		<p>
		<?php
		// translators: "maptiler" is the website where to get custom maps.
		printf( __( 'Select a standard map or create your custom map at %1$sMapTiler%2$s and paste here the %3$sVector Style%4$s.', 'venomaps' ), '<a target="_blank" href="' . esc_url( 'https://cloud.maptiler.com/maps/' ) . '">', '</a>', '<strong>', '</strong>' ); // XSS ok.
		?>
		</p>

		<fieldset class="wpol-repeatable-group">
			<?php
			$cleanstyles = array();

			if ( is_array( $style ) ) {
				foreach ( $style as $key => $value ) {
					if ( isset( $value['url'] ) && strlen( $value['url'] ) ) {
						$cleanstyles[ $key ] = $value;
					}
				}
			}
			if ( ! empty( $cleanstyles ) ) {
				foreach ( $cleanstyles as $key => $value ) {
					?>
					<div class="wpol-repeatable-item wpol-form-group" data-number="<?php echo esc_attr( $key ); ?>">
						<input type="text" class="all-options" name="venomaps_settings[style][<?php echo esc_attr( $key ); ?>][name]" value="<?php echo esc_attr( $value['name'] ); ?>" placeholder="<?php esc_html_e( 'Title', 'venomaps' ); ?>"> 
						<input type="url" class="regular-text" name="venomaps_settings[style][<?php echo esc_attr( $key ); ?>][url]" value="<?php echo esc_attr( $value['url'] ); ?>" placeholder="https://api.maptiler.com/maps/.../style.json?key=...">
					</div>
					<?php
				}
			} else {
				?>
				<div class="wpol-repeatable-item wpol-form-group" data-number="0">
					<input type="text" class="all-options" name="venomaps_settings[style][0][name]" value="map style" placeholder="<?php esc_html_e( 'Title', 'venomaps' ); ?>"> 
					<input type="url" class="regular-text" name="venomaps_settings[style][0][url]" value="" placeholder="https://api.maptiler.com/maps/.../style.json?key=...">
				</div>
				<?php
			}
			?>
		</fieldset>
		<div class="wpol-form-group">
			<div class="button wpol-call-repeat"><span class="dashicons dashicons-plus"></span> <?php esc_html_e( 'New style', 'venomaps' ); ?></div>
		</div>
		<?php
	}

	/**
	 * Geolocation
	 */
	public function geo_settings_field() {
		?>
		<h2><?php esc_html_e( 'Geolocation', 'venomaps' ); ?></h2>
		<p><?php esc_html_e( 'Search an address or click on the map to adjust the marker position and get the coordinates', 'venomaps' ); ?></p>
			<fieldset>
				<div class="wpol-form-group">
					<input type="text" class="regular-text venomaps-set-address" value="" placeholder="Type a place address">
					<div class="button venomaps-get-coordinates"><span class="dashicons dashicons-search"></span> <?php esc_html_e( 'Search', 'venomaps' ); ?></div>
				</div>
			</fieldset>
			<fieldset>
				<div class="wpol-form-group">
					<input type="text" class="all-options venomaps-get-lat" value="" placeholder="Latitude">
					<span class="description"><?php esc_html_e( 'Latitude', 'venomaps' ); ?></span>
					<input type="text" class="all-options venomaps-get-lon" value="" placeholder="Longitude">
					<span class="description"><?php esc_html_e( 'Longitude', 'venomaps' ); ?></span>
				</div>
			</fieldset>

			<div id="wpol-admin-map" class="venomap"></div>
			<div style="display:none;">
				<div class="wpol-infomarker" id="infomarker_admin"><img src="<?php echo esc_url( plugins_url( '/images/marker.svg', __FILE__ ) ); ?>" style="height: 40px;"></div>
			</div>
		<?php
	}

} // end class

// Call options.
Venomaps_Options::get_instance();
