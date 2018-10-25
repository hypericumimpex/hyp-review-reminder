<?php
/**
 * Plugin Name: HYP Review Reminder
 * Plugin URI: https://github.com/hypericumimpex/hyp-review-reminder/
 * Description: <code><strong>HYP Review Reminder</strong></code> vă permite să creșteți evaluarile produselor prin reamintirea automată a utilizatorilor de adăugarea acestora.
 * Author: Romeo C.
 * Text Domain: yith-woocommerce-review-reminder
 * Version: 1.4.4
 * Author URI: https://romeocovaci.com/
 * WC requires at least: 3.3.0
 * WC tested up to: 3.5.x
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

if ( ! function_exists( 'is_plugin_active' ) ) {
	require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
}

function ywrr_install_premium_woocommerce_admin_notice() {
	?>
	<div class="error">
		<p><?php _e( 'YITH WooCommerce Review Reminder is enabled but not effective. It requires WooCommerce in order to work.', 'yith-woocommerce-review-reminder' ); ?></p>
	</div>
	<?php
}

if ( ! function_exists( 'yit_deactive_free_version' ) ) {
	require_once 'plugin-fw/yit-deactive-plugin.php';
}

yit_deactive_free_version( 'YWRR_FREE_INIT', plugin_basename( __FILE__ ) );

if ( ! defined( 'YWRR_VERSION' ) ) {
	define( 'YWRR_VERSION', '1.4.4' );
}

if ( ! defined( 'YWRR_INIT' ) ) {
	define( 'YWRR_INIT', plugin_basename( __FILE__ ) );
}

if ( ! defined( 'YWRR_SLUG' ) ) {
	define( 'YWRR_SLUG', 'yith-woocommerce-review-reminder' );
}

if ( ! defined( 'YWRR_SECRET_KEY' ) ) {
	define( 'YWRR_SECRET_KEY', 'LDgCfgh9GZCnoX6UjYzI' );
}

if ( ! defined( 'YWRR_PREMIUM' ) ) {
	define( 'YWRR_PREMIUM', '1' );
}

if ( ! defined( 'YWRR_FILE' ) ) {
	define( 'YWRR_FILE', __FILE__ );
}

if ( ! defined( 'YWRR_DIR' ) ) {
	define( 'YWRR_DIR', plugin_dir_path( __FILE__ ) );
}

if ( ! defined( 'YWRR_URL' ) ) {
	define( 'YWRR_URL', plugins_url( '/', __FILE__ ) );
}

if ( ! defined( 'YWRR_ASSETS_URL' ) ) {
	define( 'YWRR_ASSETS_URL', YWRR_URL . 'assets/' );
}

if ( ! defined( 'YWRR_TEMPLATE_PATH' ) ) {
	define( 'YWRR_TEMPLATE_PATH', YWRR_DIR . 'templates/' );
}

/* Plugin Framework Version Check */
if ( ! function_exists( 'yit_maybe_plugin_fw_loader' ) && file_exists( YWRR_DIR . 'plugin-fw/init.php' ) ) {
	require_once( YWRR_DIR . 'plugin-fw/init.php' );
}
yit_maybe_plugin_fw_loader( YWRR_DIR );

function ywrr_premium_init() {

	/* Load YWRR text domain */
	load_plugin_textdomain( 'yith-woocommerce-review-reminder', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );

	YITH_WRR();

}

add_action( 'ywrr_premium_init', 'ywrr_premium_init' );

function ywrr_premium_install() {

	if ( ! function_exists( 'WC' ) ) {
		add_action( 'admin_notices', 'ywrr_install_premium_woocommerce_admin_notice' );
	} else {
		do_action( 'ywrr_premium_init' );
		ywrr_create_tables();
	}
}

add_action( 'plugins_loaded', 'ywrr_premium_install', 11 );

/**
 * Init default plugin settings
 */
if ( ! function_exists( 'yith_plugin_registration_hook' ) ) {
	require_once 'plugin-fw/yit-plugin-registration-hook.php';
}

register_activation_hook( __FILE__, 'yith_plugin_registration_hook' );

if ( ! function_exists( 'YITH_WRR' ) ) {

	/**
	 * Unique access to instance of YWRR_Review_Reminder
	 *
	 * @since   1.1.5
	 * @return  YWRR_Review_Reminder|YWRR_Review_Reminder_Premium
	 * @author  Alberto Ruggiero
	 */
	function YITH_WRR() {

		// Load required classes and functions
		require_once( YWRR_DIR . 'class.yith-woocommerce-review-reminder.php' );

		if ( defined( 'YWRR_PREMIUM' ) && file_exists( YWRR_DIR . 'class.yith-woocommerce-review-reminder-premium.php' ) ) {


			require_once( YWRR_DIR . 'class.yith-woocommerce-review-reminder-premium.php' );

			return YWRR_Review_Reminder_Premium::get_instance();
		}

		return YWRR_Review_Reminder::get_instance();

	}

}


register_activation_hook( __FILE__, 'ywrr_create_tables' );
register_activation_hook( __FILE__, 'ywrr_create_schedule_job' );
register_deactivation_hook( __FILE__, 'ywrr_create_unschedule_job' );

if ( ! function_exists( 'ywrr_create_tables' ) ) {

	/**
	 * Creates database table for blocklist and scheduling
	 *
	 * @since   1.0.0
	 * @return  void
	 * @author  Alberto Ruggiero
	 */
	function ywrr_create_tables() {

		$current_version = get_option( 'ywrr_db_version' );

		if ( $current_version != YWRR_VERSION ) {


			global $wpdb;

			$wpdb->hide_errors();

			$collate = $wpdb->get_charset_collate();

			$blocklist_table_name = $wpdb->prefix . 'ywrr_email_blocklist';
			$schedule_table_name  = $wpdb->prefix . 'ywrr_email_schedule';

			$blocklist_table_sql = "
            CREATE TABLE $blocklist_table_name (
              id int NOT NULL AUTO_INCREMENT,
              customer_email longtext NOT NULL,
              customer_id bigint(20) NOT NULL DEFAULT 0,
              PRIMARY KEY (id)
            ) $collate;";

			$schedule_table_sql = "
            CREATE TABLE $schedule_table_name (
              id int NOT NULL AUTO_INCREMENT,
              order_id bigint(20) NOT NULL,
              order_date date NOT NULL DEFAULT '0000-00-00',
              scheduled_date date NOT NULL DEFAULT '0000-00-00',
              request_items longtext NOT NULL DEFAULT '',
              mail_status varchar(15) NOT NULL DEFAULT 'pending',
              PRIMARY KEY (id)
            ) $collate;";

			if ( ! function_exists( 'dbDelta' ) ) {
				require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
			}

			$failed = false;
			$result = dbDelta( array( $blocklist_table_sql, $schedule_table_sql ) );

			foreach ( $result as $key => $value ) {

				if ( strpos( $value, 'Created' ) === false ) {
					$failed = true;
				}

			}

			if ( ! $failed ) {

				update_option( 'ywrr_db_version', YWRR_VERSION );

			}

		}

	}
}

if ( ! function_exists( 'ywrr_create_schedule_job' ) ) {

	/**
	 * Creates a cron job to handle daily mail send
	 *
	 * @since   1.0.0
	 * @return  void
	 * @author  Alberto Ruggiero
	 */
	function ywrr_create_schedule_job() {
		wp_schedule_event( time(), 'daily', 'ywrr_daily_send_mail_job' );
	}

}

if ( ! function_exists( 'ywrr_create_unschedule_job' ) ) {

	/**
	 * Removes cron job
	 *
	 * @since   1.0.0
	 * @return  void
	 * @author  Alberto Ruggiero
	 */
	function ywrr_create_unschedule_job() {
		wp_clear_scheduled_hook( 'ywrr_daily_send_mail_job' );
	}

}


