<?php
/**
 * Class HFWE_Admin file.
 * 
 * @package header-footer-with-elementor
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

if ( ! class_exists( 'HFWE_Admin', false ) ) :

    /**
     * HFWE_Admin Class
     */
    class HFWE_Admin {

        /**
         *
         * @var object instance
         */
        private static $instance;
        
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

			add_action( 'init', array( $this, 'register_post_type' ) );
			add_action( 'admin_menu', array( $this, 'register_admin_menu' ), 60 );
			add_action( 'add_meta_boxes', array( $this, 'register_metabox' ) );
			add_action( 'save_post', array( $this, 'save_meta' ) );
			add_action( 'admin_notices', array( $this, 'location_notice' ) );

			add_action( 'single_template', array( $this, 'load_canvas_template' ) );
			add_action( 'template_redirect', array( $this, 'redirect_template_frontend' ) );

        }


        /**
         * Register plugin Post type
         * 
         * 
         * @since  1.0.0
         * @access public
         * @return void
         */
        public function register_post_type() {

        	$labels = array(
				'name'               	=> __( 'Header & Footer Template', 'header-footer-with-elementor' ),
				'singular_name'      	=> __( 'Elementor Header & Footer', 'header-footer-with-elementor' ),
				'menu_name'          	=> __( 'Header & Footer Template', 'header-footer-with-elementor' ),
				'name_admin_bar'     	=> __( 'Elementor Header & Footer', 'header-footer-with-elementor' ),
				'add_new'            	=> __( 'Add New', 'header-footer-with-elementor' ),
				'add_new_item'       	=> __( 'Add New Header & Footer', 'header-footer-with-elementor' ),
				'new_item'           	=> __( 'New Header & Footer Template', 'header-footer-with-elementor' ),
				'edit_item'          	=> __( 'Edit Header & Footer Template', 'header-footer-with-elementor' ),
				'view_item'          	=> __( 'View Header & Footer Template', 'header-footer-with-elementor' ),
				'all_items'          	=> __( 'All Elementor Header & Footer', 'header-footer-with-elementor' ),
				'search_items'       	=> __( 'Search Header & Footer Templates', 'header-footer-with-elementor' ),
				'parent_item_colon'  	=> __( 'Parent Header & Footer Templates:', 'header-footer-with-elementor' ),
				'not_found'          	=> __( 'No Header & Footer Templates found.', 'header-footer-with-elementor' ),
				'not_found_in_trash' 	=> __( 'No Header & Footer Templates found in Trash.', 'header-footer-with-elementor' ),
			);

			$args = array(
				'labels'              	=> $labels,
				'public'              	=> true,
				'show_ui'             	=> true,
				'rewrite'             	=> false,
				'show_in_menu'        	=> false,
				'show_in_nav_menus'   	=> false,
				'exclude_from_search' 	=> true,
				'capability_type'     	=> 'post',
				'hierarchical'        	=> false,
				'supports'            	=> array( 'title', 'thumbnail', 'elementor' ),
			);

			//register the header & footer with elementor post type
			register_post_type( 'hfw-elementor', $args );

        }


        /**
		 * Register the plugin admin menu.
		 *
		 * @since  1.0.0
		 * @return void       
		 */
		public function register_admin_menu() {
			
			add_submenu_page(
				'themes.php',
				__( 'Header & Footer Template', 'header-footer-with-elementor' ),
				__( 'Header & Footer Template', 'header-footer-with-elementor' ),
				'edit_pages',
				'edit.php?post_type=hfw-elementor'
			);
		}


		/**
		 * Register Metabox.
		 *
		 * @since  1.0.0
		 * @return void       
		 */
		public function register_metabox() {
			add_meta_box( 'hfwe-meta-box', __( 'Header & Footer with Elementor options', 'header-footer-with-elementor' ), array(
				$this,
				'metabox_render',
			), 'hfw-elementor', 'normal', 'high' );
		}

		
		/**
		 * Render meta fields
		 * 
		 * @param  $post_id | Currennt post object.
		 * @since  1.0.0
		 * @return void
		 */
		function metabox_render( $post ) {
			$values   = get_post_custom( $post->ID );
			$selected = isset( $values['hfwe_template_type'] ) ? esc_attr( $values['hfwe_template_type'][0] ) : '';
			wp_nonce_field( 'hfwe_meta_nounce', 'hfwe_meta_nounce' );
			?>
			<p>
				<label for="hfwe_template_type"><?php esc_attr_e('Select template type', 'header-footer-with-elementor'); ?></label>
				<select name="hfwe_template_type" id="hfwe_template_type">
					<option value="" <?php selected( $selected, '' ); ?>><?php esc_attr_e('Select Template', 'header-footer-with-elementor'); ?></option>
					<option value="type_header" <?php selected( $selected, 'type_header' ); ?>>Header</option>
					<option value="type_footer" <?php selected( $selected, 'type_footer' ); ?>>Footer</option>
				</select>
			</p>
			<?php
		}


		
		/**
		 * Save meta field
		 * 
		 * @param  $post_id | Currennt post_id.
		 * @since  1.0.0
		 * @return void
		 */
		public function save_meta( $post_id ) {

			// Check DOING_AUTOSAVE .
			if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
				return;
			}

			// Check nonce
			if ( ! isset( $_POST['hfwe_meta_nounce'] ) || 
				! wp_verify_nonce( $_POST['hfwe_meta_nounce'], 'hfwe_meta_nounce' ) ) {
				return;
			}

			// Check current user permission.
			if ( ! current_user_can( 'edit_posts' ) ) {
				return;
			}

			if ( isset( $_POST['hfwe_template_type'] ) ) {
				update_post_meta( $post_id, 'hfwe_template_type', esc_attr( $_POST['hfwe_template_type'] ) );
			}

		}


		/**
		 * Template redirect as per the user permission
		 * 
		 * @since  1.0.0
		 * @return void
		 */
		public function redirect_template_frontend() {

			if ( is_singular( 'hfw-elementor' ) && ! current_user_can( 'edit_posts' ) ) {
				wp_redirect( site_url(), 301 );
				die;
			}
		}


		/**
		 * Template notice to check the template is assigned or not
		 * 
		 * @since  1.0.0
		 * @return void
		 */
		public function location_notice() {

			global $pagenow;
			global $post;

			if ( $pagenow != 'post.php' || ! is_object( $post ) || $post->post_type != 'hfw-elementor' ) {
				return;
			}

			$template_type = get_post_meta( $post->ID, 'hfwe_template_type', true );

			if ( $template_type !== '' ) {
				$templates = Header_Footer_With_Elementor::get_template_id( $template_type );

				// Check if more than one template is selected for current template type.
				if ( is_array( $templates ) && isset( $templates[1] ) && $post->ID != $templates[0] ) {

					$post_title = '<strong>' . get_the_title( $templates[0] ) . '</strong>';

					//generate template name
					$template_type_display = ucfirst( str_replace( 'type_', '', $template_type ) );

					$message = __( sprintf( 'Template %s is already assigned to the %s location', $post_title, $template_type_display ), 'header-footer-with-elementor' );

					echo '<div class="error"><p>';
					echo $message;
					echo '</p></div>';
				}
			}

		}


		/**
		 * Single template
		 * 
		 * @param String $single_template
		 * @since  1.0.0
		 * @return void
		 */
		function load_canvas_template( $single_template ) {

			global $post;

			if ( $post->post_type == 'hfw-elementor' ) {

				$elementor_2_0_canvas = ELEMENTOR_PATH . '/modules/page-templates/templates/canvas.php';

				if ( file_exists( $elementor_2_0_canvas ) ) {
					return $elementor_2_0_canvas;
				} else {
					return ELEMENTOR_PATH . '/includes/page-templates/canvas.php';
				}
			}
			
			return $single_template;
		}

    }

    HFWE_Admin::get_instance();

endif;

