<?php
/*
* Plugin Name: AI Auto Content Generator For Elementor
 * Description: Best AI Auto Content Generator For Elementor. <strong>[Elementor Addon]</strong>
 * Plugin URI:  https://cooltimeline.com
 * Version:     1.0.1
 * Author:      Cool Plugins
 * Author URI:  https://coolplugins.net
 * Text Domain: aacgfe
 * Elementor tested up to:3.11.1
 * Elementor Pro tested up to: 3.11.1
*/
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
if ( defined( 'AACGFE_VERSION' ) ) {
	return;
}
define( 'AACGFE_VERSION', '1.0.1' );
define( 'AACGFE_FILE', __FILE__ );
define( 'AACGFE_PATH', plugin_dir_path( AACGFE_FILE ) );
define( 'AACGFE_URL', plugin_dir_url( AACGFE_FILE ) );
register_activation_hook( AACGFE_FILE, array( 'AACGFE_Widget_Addon', 'aacgfe_activate' ) );
register_deactivation_hook( AACGFE_FILE, array( 'AACGFE_Widget_Addon', 'aacgfe_deactivate' ) );
if ( ! class_exists( 'AACGFE_Widget_Addon' ) ) {
	final class AACGFE_Widget_Addon {
		private static $instance = null;
		public static function get_instance() {
			if ( ! isset( self::$instance ) ) {
				self::$instance = new self();
			};
			return self::$instance;
		}
		function __construct() {
			add_action( 'admin_menu', array( $this, 'aacgfe_sub_menu' ), 100 );	
			add_action( 'plugins_loaded', array($this, 'aacgfe_plugins_loaded' ) );
			add_action( 'init', array($this, 'save_prompt_data' ) );
			add_filter('plugin_action_links_' . plugin_basename(__FILE__), array($this, 'aacgfe_add_widgets_action_links'));
			add_action('admin_init',array($this,'aacgfe_plugin_redirect'));
		}
		function aacgfe_plugin_redirect() {
			if (get_option('aacgfe_plugin_redirect', false)) {
				delete_option('aacgfe_plugin_redirect');
				exit(wp_redirect(esc_url(admin_url() . 'admin.php?page=auto_content_generator') ) );
			}
		}
		public function save_prompt_data() {
			if ( get_option( 'AACGFE_prompt_data' ) == false ) {
				$prompt_data = json_decode( file_get_contents( AACGFE_PATH . '/open-ai/prompt-data.json' ), true );
				update_option( 'AACGFE_prompt_data', $prompt_data );
			}
		}
		public function aacgfe_plugins_loaded() {
			load_plugin_textdomain( 'aacg', false, basename( dirname( __FILE__ ) ) . '/languages/' );
			// Notice if the Elementor is not active
			if ( ! did_action( 'elementor/loaded' ) ) {
				add_action( 'admin_notices', array($this, 'aacgfe_fail_to_load' ) );
				return;
			}
			if ( is_admin() ) {
				require_once AACGFE_PATH . 'admin/codestar-framework/codestar-framework.php';
				require_once AACGFE_PATH . 'admin/aacgfe-admin-fields.php';
				require_once AACGFE_PATH . '/admin/feedback/admin-feedback-form.php';
				require AACGFE_PATH . '/admin/class-admin-notice.php';
				add_action( 'admin_init', array( $this, 'aacgfe_show_upgrade_notice' ) );
				
			};
			require_once AACGFE_PATH . 'controls/ai_controller.php';
		}
		public function aacgfe_show_upgrade_notice() {
			/*** Plugin review notice file */
				aacgfe_create_admin_notice(
					array(
						'id'              => 'aacgfe-review-box',  // required and must be unique
						'slug'            => 'aacgfe',      // required in case of review box
						'review'          => true,     // required and set to be true for review box
						'review_url'      => esc_url( 'https://wordpress.org/support/plugin/ai-auto-content-generator-for-elementor/reviews/?filter=5#new-post' ), // required
						'plugin_name'     => 'AI Auto Content Generator For Elementor',    // required
						'logo'            => AACGFE_URL . 'assets/images/acgfe-logo.png',    // optional: it will display logo
						'review_interval' => 3,                    // optional: this will display review notice
																// after 5 days from the installation_time
																// default is 3
					)
				);
		}
		function aacgfe_fail_to_load() {
			if ( ! is_plugin_active( 'elementor/elementor.php' ) ) : ?>
				<div class="notice notice-warning is-dismissible">
				<p><?php echo '<a href="https://wordpress.org/plugins/elementor/"  target="_blank" >' . esc_html__( 'Elementor Page Builder', 'aacg' ) . '</a>' . wp_kses_post( __( ' must be installed and activated for using "<strong>AI Auto Content Generator For Elementor</strong>" ', 'aacg' ) ); ?></p>
				</div>
				<?php
			endif;
		}
		public static function aacgfe_sub_menu() {
			add_submenu_page( 'elementor', 'Aacg Settings', 'AI Content Genrator', 'manage_options', 'admin.php?page=auto_content_generator', false, 100 );
		}
		public function aacgfe_add_widgets_action_links($links)
        {
            $aacgfe_settings = admin_url() . 'admin.php?page=auto_content_generator';
            $links[] = '<a  style="font-weight:bold" href="' . esc_url($aacgfe_settings) . '" target="_self">' . esc_html__("Settings", "aacg") . '</a>';
            return $links;
        }
		public static function aacgfe_activate(){
			update_option( 'aacgfe-installDate', gmdate( 'Y-m-d h:i:s' ) );
			update_option( 'aacgfe-version', AACGFE_VERSION);
			update_option( 'aacgfe-plugin-type', 'free');
			update_option( 'aacgfe-ratingDiv', 'no' );
			update_option('aacgfe_plugin_redirect', true);
		}
		public static function aacgfe_deactivate(){
			delete_option('AACGFE_prompt_data');
		}
	}
}
function AACGFE_Widget_Addon() {
	return AACGFE_Widget_Addon::get_instance();
}
AACGFE_Widget_Addon();


