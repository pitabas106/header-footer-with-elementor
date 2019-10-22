<?php
/**
 * HFWE_Ascent_Compat setup
 * 
 * @since 1.0.0
 * 
 * @package header-footer-with-elementor
 */

class HFWE_Ascent_Compat {

	/**
	 * Instance of HFWE_Ascent_Compat.
	 *
	 * @var HFWE_Ascent_Compat
	 */
	private static $instance;

	/**
	 * Instance of Elementor Frontend class.
	 *
	 * @var \Elementor\Frontend()
	 */
	private static $elementor_instance;

	/**
	 * Instance
	 *  
	 * @since 1.0.0
	 * 
	 * @return $instance 
	 */
	public static function instance() {

		if ( ! isset( self::$instance ) ) {

			self::$instance = new HFWE_Ascent_Compat();

			add_action( 'wp', [ self::$instance, 'theme_hooks' ] );

		}

		if ( defined( 'ELEMENTOR_VERSION' ) && is_callable( 'Elementor\Plugin::instance' ) ) {

			self::$elementor_instance = Elementor\Plugin::instance();

		}

		return self::$instance;
	}
	

	/**
	 * Theme hooks for apply Header / Footer markup
	 *  
	 * @since 1.0.0
	 * 
	 * @return void 
	 */
	public function theme_hooks() {

		$header_id = Header_Footer_With_Elementor::get_settings( 'type_header', '' );
		$footer_id = Header_Footer_With_Elementor::get_settings( 'type_footer', '' );


		if ( $header_id !== '' ) {
			add_action( 'template_redirect', [ $this, 'ascent_setup_header' ], 10 );
			add_action( 'ascent_header', [ $this, 'add_header_markup' ] );
		}

		if ( $footer_id !== ''  ) {
			add_action( 'template_redirect', [ $this, 'ascent_setup_footer' ], 10 );
			add_action( 'ascent_footer', [ $this, 'add_footer_markup' ] );
		}

	}

	
	/**
	 * Disable header from the theme
	 *  
	 * @since 1.0.0
	 * 
	 * @return void
	 */
	public function ascent_setup_header() {
		remove_action( 'ascent_header', 'ascent_header_html' );
	}


	/**
	 * Disable footer from the theme
	 *  
	 * @since 1.0.0
	 * 
	 * @return void
	 */
	public function ascent_setup_footer() {
		remove_action( 'ascent_footer', 'ascent_footer_html' );
	}


	/**
	 * Add header markup
	 *  
	 * @since 1.0.0
	 * 
	 * @return void
	 */
	public function add_header_markup() {
		?>
			<header id="masthead" class="site-header" role="banner" itemscope="itemscope" itemtype="http://schema.org/WPHeader">
				
				<?php Header_Footer_With_Elementor::get_header_content(); ?>

			</header>

		<?php
	}

	
	/**
	 * Add footer markup
	 *  
	 * @since 1.0.0
	 * 
	 * @return void
	 */
	public function add_footer_markup() {

		?>
			<footer id="colophon" class="site-footer" role="contentinfo" itemscope="itemscope" itemtype="http://schema.org/WPFooter">
				<?php Header_Footer_With_Elementor::get_footer_content(); ?>
			</footer>
		<?php
	}

}

HFWE_Ascent_Compat::instance();
