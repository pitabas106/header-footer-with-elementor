<?php
/**
 * Class Header_Footer_With_Elementor file.
 * 
 * @package header-footer-with-elementor
 * @since  1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}


if ( ! class_exists( 'Header_Footer_With_Elementor', false ) ) :

    /**
     * Header_Footer_With_Elementor Class
     */
    class Header_Footer_With_Elementor {

        /**
         * Member Variable
         *
         * @var object instance
         */
        private static $instance;

        /**
		 * Minimum PHP Version
		 *
		 * @since 1.0.0
		 *
		 * @var string Minimum PHP version required to run the plugin.
		 */
		const MINIMUM_PHP_VERSION = '5.6';

        /**
		 * Minimum Elementor Version
		 *
		 * @since 1.0.0
		 *
		 * @var string Minimum Elementor version required to run the plugin.
		 */
        const MINIMUM_ELEMENTOR_VERSION = '2.0.0';

        /**
		 * Current theme template
		 *
		 * @var String
		 */
		public $template;

		/**
		 * Instance of Elemenntor Frontend class.
		 *
		 * @var \Elementor\Frontend()
		 */
		private static $elementor_instance;

        /**
         *
         * @return Singleton instance.
         */
        public static function get_instance() {
            if ( null === self::$instance ) {
                self::$instance = new self();
            }
            return self::$instance;
        }

        /**
         * Class Constructor
         * 
         * @since  1.0.0
         * @return void
         */
        public function __construct() {

            //add_action for plugin shortcode
            add_action( 'init', [ $this, 'i18n' ] );

            $this->inits();

        }

        /**
         * Initialize the plugin
         * 
         * Load the plugin only after Elementor (and other plugins) are loaded.
         * 
         * @since  1.0.0
         * @access public
         * @return void
         */
        public function inits() {

			// Check for required PHP version
			if ( version_compare( PHP_VERSION, self::MINIMUM_PHP_VERSION, '<' ) ) {
				add_action( 'admin_notices', [ $this, 'admin_notice_minimum_php_version' ] );
				
				return;
			}

			// Check if the Elementor plugin is installed and activated

			if ( defined( 'ELEMENTOR_VERSION' ) && is_callable( 'Elementor\Plugin::instance' ) ) {

				$this->includes();
				$this->template = get_template();
				self::$elementor_instance = Elementor\Plugin::instance();


				//check the Theme type
				if ( $this->template == 'ascent' ) {

					require HFWE_INC_DIR . 'themes/ascent/class-ascent-compat.php';

				} else {
					//display warning message
					add_action( 'admin_notices', [ $this, 'unsupported_theme_notice' ] );

				}

				add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_scripts' ] );
				add_filter( 'body_class', [ $this, 'body_class' ] );


			} else {

				add_action( 'admin_notices', [ $this, 'admin_notice_missing_main_plugin' ] );
				add_action( 'network_admin_notices', [ $this, 'admin_notice_missing_main_plugin' ] );
				return;

			}

        }

        /**
		 * Add classes to the body tag
		 * 
		 * @param  Array $classes
		 *
		 * @since  1.0.0
         * @access public
         * @return Array | class names for body tag
		 */
		public function body_class( $classes ) {

			$header_id = $this->get_settings( 'type_header', '' );
			$footer_id = $this->get_settings( 'type_footer', '' );

			//adding CSS class for header layout
			if ( $header_id !== '' ) {
				$classes[] = 'hfwe-header';
			}

			//adding CSS class for footer layout
			if ( $footer_id !== ''  ) {
				$classes[] = 'hfwe-footer';
			}

			$classes[] = 'hfwe-template-' . $this->template;
			$classes[] = 'hfwe-stylesheet-' . get_stylesheet();

			return $classes;

		}


		/**
		 * Get options
		 *
		 * @param  $setting Option name | $default Default value
		 * @since  1.0.0
         * @access public
		 * @return mixed.
		 */
		public static function get_settings( $setting = '', $default = '' ) {
			if ( $setting == 'type_header' || $setting == 'type_footer' ) {
				
				$templates = self::get_template_id( $setting );

				return is_array( $templates ) ? $templates[0] : '';
			}
		}

		
		/**
		 * Prints the Header content
		 *
		 * @since  1.0.0
		 * 
		 * @return void
		 */
		public static function get_header_content() {
			$header_id = Header_Footer_With_Elementor::get_settings( 'type_header', '' );
			echo self::$elementor_instance->frontend->get_builder_content_for_display( $header_id );
		}

		
		/**
		 * Prints the Footer content
		 *
		 * @since  1.0.0
		 * 
		 * @return void
		 */
		public static function get_footer_content() {

			$footer_id = Header_Footer_With_Elementor::get_settings( 'type_footer', '' );
			echo "<div class='footer-width-fixer'>";
			echo self::$elementor_instance->frontend->get_builder_content_for_display( $footer_id );
			echo '</div>';
		}


		/**
		 * Enqueue CSS and JS files
		 *
		 * @since  1.0.0
		 * 
		 * @return void
		 */
        public function enqueue_scripts() {

			wp_enqueue_style( 'hfwe-style', HFWE_URL . 'assets/css/hfwe.css', array(), HFWE_VERSION );

        }

        
        /**
		 * Display Admin notice if the active theme is not supported
		 *
		 * @since  1.0.0
		 * 
		 * @return void
		 */
		public function unsupported_theme_notice() {

			$message = __( 'Hey, your current theme is not supported by Header Footer with Elementor. Only suport with <a href="https://wordpress.org/themes/ascent/">Ascent theme</a>.', 'header-footer-with-elementor' );
			
			printf( '<div class="notice notice-warning is-dismissible"><p>%1$s</p></div>', $message );
		}	


        /**
		 * Display notice if the Elementor plugin is not installed or activated
		 *
		 * @since  1.0.0
		 * 
		 * @return void
		 */
		public function admin_notice_missing_main_plugin() {
			
			if ( isset( $_GET['activate'] ) ) {
				unset( $_GET['activate'] );
			}
			
			$message = sprintf(
			/* translators: 1: Plugin name 2: Elementor */
				esc_html__( '"%1$s" requires "%2$s" to be installed and activated.', 'header-footer-with-elementor' ),
				'<strong>' . esc_html__( 'Header Footer with Elementor', 'header-footer-with-elementor' ) . '</strong>',
				'<strong>' . esc_html__( 'Elementor', 'header-footer-with-elementor' ) . '</strong>'
			);
			
			printf( '<div class="notice notice-warning is-dismissible"><p>%1$s</p></div>', $message );
			
		}
		

		/**
		 * Admin notice
		 *
		 * Warning when the site doesn't have a minimum required PHP version.
		 *
		 * @since 1.0.0
		 *
		 * @return void
		 */
		public function admin_notice_minimum_php_version() {
			
			if ( isset( $_GET['activate'] ) ) {
				unset( $_GET['activate'] );
			}
			
			$message = sprintf(
			/* translators: 1: Plugin name 2: PHP 3: Required PHP version */
				esc_html__( '"%1$s" requires "%2$s" version %3$s or greater.', 'header-footer-with-elementor' ),
				'<strong>' . esc_html__( 'Header & Footer with Elementor', 'header-footer-with-elementor' ) . '</strong>',
				'<strong>' . esc_html__( 'PHP', 'header-footer-with-elementor' ) . '</strong>',
				self::MINIMUM_PHP_VERSION
			);
			
			printf( '<div class="notice notice-warning is-dismissible"><p>%1$s</p></div>', $message );
			
		}
		

        /**
		 * Load Admin Class File
		 *
		 * @since 1.0.0
		 *
		 * @return void
		 */
		public function includes() {
			require_once HFWE_INC_DIR . 'admin/class-admin.php';
		}


        /**
         * Plugin localization
         *
         * @since  1.0.0
         * @return void
         */
        public function i18n() {

            $lang_dir = HFWE_DIR . '/languages/';
            load_plugin_textdomain( 'header-footer-with-elementor', false, $lang_dir );
        }


	    /**
		 * Get the template id.
		 *
		 * @param  String $type 
		 * @since  1.0.0
		 * @return Mixed | Returns the header or footer template id
		 */
		public static function get_template_id( $type ) {

			$cached = wp_cache_get( $type );
			
			if ( $cached !== false ) {
				return $cached;
			}

			$template = new WP_Query( array(
				'post_type'    => 'hfw-elementor',
				'meta_key'     => 'hfwe_template_type',
				'meta_value'   => $type,
				'meta_type'    => 'post',
				'meta_compare' => '>=',
				'orderby'      => 'meta_value',
				'order'        => 'ASC',
				'meta_query'   => array(
					'relation' => 'OR',
					array(
						'key'     => 'hfwe_template_type',
						'value'   => $type,
						'compare' => '==',
						'type'    => 'post',
					)
				),
			) );

			if ( $template->have_posts() ) {
				$posts = wp_list_pluck( $template->posts, 'ID' );
				wp_cache_set( $type, $posts );

				return $posts;
			}

			return '';
		}


    }

    Header_Footer_With_Elementor::get_instance();

endif;

