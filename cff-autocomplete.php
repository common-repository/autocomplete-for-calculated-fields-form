<?php
/*
Plugin Name: Autocomplete for Calculated Fields Form
Plugin URI: https://cff-bundles.dwbooster.com/product/autocomplete
Description: Suggests words and phrases to auto-complete text field values as the user types.
Version: 1.0.2
Text Domain: cff-autocomplete
Author: CodePeople
Author URI: https://cff-bundles.dwbooster.com
License: GPL
*/

if ( ! class_exists( 'AUTOCOMPLETE_CALCULATED_FIELDS_FORM' ) ) {
	class AUTOCOMPLETE_CALCULATED_FIELDS_FORM {

		private static $version = '1.0.2';
		private static $obj;
		public static function init() {
			self::$obj = new AUTOCOMPLETE_CALCULATED_FIELDS_FORM();
		}

		/********************** INSTANCE METHODS AND PROPERTIES **********************/
		public function __construct() {

			if ( is_admin() ) {
				// Language
				add_action( 'after_setup_theme', array( $this, 'load_language' ) );
				add_action( 'admin_notices', array( $this, 'dashboard_message' ) );
			}

			// Load scripts
			$version = self::$version;

			add_action( 'init', array( $this, 'autocomplete' ) );

			add_action(
				'cpcff_load_controls_admin',
				function( $form_id ) use ( $version ) {
					include dirname( __FILE__ ) . '/assets/admin.js';
				}
			);

			add_filter(
				'cpcff_the_form',
				function( $form, $form_id ) use ( $version ) {

					wp_enqueue_script( 'cff-autocomplete-public-js', plugins_url( '/assets/public.js', __FILE__ ), array(), $version );
					wp_localize_script(
						'cff-autocomplete-public-js',
						'cff_autocomplete_settings',
						array(
							'url'      => get_site_url( get_current_blog_id() ) . '/',
							'wp_nonce' => wp_create_nonce( 'cff-autocomplete-get-terms' ),
						)
					);

					return $form;
				},
				10,
				2
			);
		} // End __construct

		public function autocomplete() {
			if (
				isset( $_REQUEST['wp_nonce'] ) &&
				wp_verify_nonce( sanitize_text_field( wp_unslash( $_REQUEST['wp_nonce'] ) ), 'cff-autocomplete-get-terms' ) &&
				isset( $_REQUEST['action'] ) &&
				'cff-autocomplete' == sanitize_text_field( wp_unslash( $_REQUEST['action'] ) ) &&
				! empty( $_REQUEST['terms'] )
			) {
				$terms = sanitize_text_field( wp_unslash( $_REQUEST['terms'] ) );
				if ( ! empty( $terms ) ) {
					require_once dirname( __FILE__ ) . '/classes/autocomplete.clss.php';
					$autocomplete_obj = new CPCFFAutocomplete();
					$result           = $autocomplete_obj->autocomplete( $terms );
					print json_encode( $result );
					exit;
				}
			}
		} // End autocomplete

		public function dashboard_message() {
			// Display dashboard message including a link to the Calculated Fields Form plugin
			if ( current_user_can( 'manage_options' ) && ! defined( 'CP_CALCULATEDFIELDSF_VERSION' ) ) {
				$screen = get_current_screen();
				// Check if it is the plugins or dashboard page
				if ( 'dashboard' == $screen->id || 'plugins' == $screen->id ) {
					?>
					<style>
						#cff-installation-banner{width:calc( 100% - 20px );width:-webkit-calc( 100% - 20px );width:-moz-calc( 100% - 20px );width:-o-calc( 100% - 20px );border:10px solid #1582AB;background:#FFF;display:table;}
						#cff-installation-banner .cff-installation-banner-picture{width:120px;padding:10px 10px 10px 10px;float:left;text-align:center;}
						#cff-installation-banner .cff-installation-banner-content{float: left;padding:10px;width: calc( 100% - 160px );width: -webkit-calc( 100% - 160px );width: -moz-calc( 100% - 160px );width: -o-calc( 100% - 160px );}
						#cff-installation-banner  .cff-installation-banner-buttons{padding-top:5;}
						@media screen AND (max-width:760px)
						{
							#cff-installation-banner .cff-installation-banner-picture{display:none;}
							#cff-installation-banner .cff-installation-banner-content{width:calc( 100% - 20px );width:-webkit-calc( 100% - 20px );width:-moz-calc( 100% - 20px );width:-o-calc( 100% - 20px );}
						}
					</style>
					<div id="cff-installation-banner">
						<div class="cff-installation-banner-picture">
							<img alt="" src="<?php print esc_attr( plugins_url( '/assets/icon-256x256.jpg', __FILE__ ) ); ?>" style="width:80px;">
						</div>
						<div class="cff-installation-banner-content">
							<div class="cff-installation-banner-text">
								<p><strong><?php esc_html_e( 'Plugin Name: Autocomplete for Calculated Fields Form plugin suggests words and phrases to auto-complete text field values as the user types them. It requires the Calculated Fields Form to be installed and active. The Calculated Fields Form plugin is a powerful and easy-to-use forms builder. If you do not have installed it, please visit the following link (It is free). Thank you!', 'cff-autocomplete' ); ?></p>
							</div>
							<div class="cff-installation-banner-buttons">
								<button type="button" class="button-primary" onclick="window.open('https://wordpress.org/plugins/calculated-fields-form/', '_blank');"><?php esc_html_e( 'Calculated Fields Form Plugin', 'cff-autocomplete' ); ?>
							</div>
						</div>
						<div style="clear:both;"></div>
					</div>
					<?php
				}
			}
		} // End dashboard_message

		public function load_language() {
			// I18n
			load_plugin_textdomain( 'cff-autocomplete', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
		} // End load_language
	} // End form AUTOCOMPLETE_CALCULATED_FIELDS_FORM

	AUTOCOMPLETE_CALCULATED_FIELDS_FORM::init();
}
