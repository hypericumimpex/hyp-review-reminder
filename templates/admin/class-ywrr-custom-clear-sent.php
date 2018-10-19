<?php
/**
 * This file belongs to the YIT Plugin Framework.
 *
 * This source file is subject to the GNU GENERAL PUBLIC LICENSE (GPL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.gnu.org/licenses/gpl-3.0.txt
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

/**
 * Outputs a custom template for clear sent reminders from schedule list
 *
 * @class   YWRR_Custom_Clear_Sent
 * @package Yithemes
 * @since   1.0.0
 * @author  Your Inspiration Themes
 *
 */
class YWRR_Custom_Clear_Sent {

	/**
	 * Single instance of the class
	 *
	 * @var \YWRR_Custom_Schedule
	 * @since 1.0.0
	 */
	protected static $instance;

	/**
	 * Returns single instance of the class
	 *
	 * @return \YWRR_Custom_Schedule
	 * @since 1.0.0
	 */
	public static function get_instance() {

		if ( is_null( self::$instance ) ) {

			self::$instance = new self( $_REQUEST );

		}

		return self::$instance;
	}

	/**
	 * Constructor
	 *
	 * @since   1.0.0
	 * @return  mixed
	 * @author  Alberto Ruggiero
	 */
	public function __construct() {

		add_action( 'woocommerce_admin_field_ywrr-clear-sent', array( $this, 'output' ) );

	}

	/**
	 * Outputs a custom template for clear sent reminders from schedule list
	 *
	 * @since   1.2.3
	 *
	 * @param   $option
	 *
	 * @return  void
	 * @author  Alberto Ruggiero
	 */
	public function output( $option ) {

		?>
		<tr valign="top">
			<th scope="row" class="titledesc">
				<label><?php _e( 'Clear sent emails from Schedule List', 'yith-woocommerce-review-reminder' ) ?></label>
			</th>
			<td class="forminp forminp-custom-send">
				<button type="button" class="button-secondary ywrr-clear-sent-email"><?php _e( 'Clear', 'yith-woocommerce-review-reminder' ); ?></button>
				<div class="ywrr-sent-result send-progress"><?php _e( 'Please wait...', 'yith-woocommerce-review-reminder' ); ?></div>
				<span class="description" style="display: block;"><?php _e( 'Use this option to clear all sent emails from Schedule List. This option is useful if you want to reduce the weight of your database.', 'yith-woocommerce-review-reminder' ); ?></span>
			</td>
		</tr>
		<?php
	}

}

/**
 * Unique access to instance of YWRR_Custom_Clear_Sent class
 *
 * @return \YWRR_Custom_Clear_Schedule
 */
function YWRR_Custom_Clear_Sent() {

	return YWRR_Custom_Clear_Sent::get_instance();

}

new YWRR_Custom_Clear_Sent();