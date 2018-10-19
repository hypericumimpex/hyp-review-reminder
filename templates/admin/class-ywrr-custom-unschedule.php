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
 * Outputs a custom template for unschedule reminders from schedule list
 *
 * @class   YWRR_Custom_Unschedule
 * @package Yithemes
 * @since   1.0.0
 * @author  Your Inspiration Themes
 *
 */
class YWRR_Custom_Unschedule {

	/**
	 * Single instance of the class
	 *
	 * @var \YWRR_Custom_Unschedule
	 * @since 1.0.0
	 */
	protected static $instance;

	/**
	 * Returns single instance of the class
	 *
	 * @return \YWRR_Custom_Unschedule
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

		add_action( 'woocommerce_admin_field_ywrr-unschedule', array( $this, 'output' ) );

	}

	/**
	 * Outputs a custom template for unschedule sent reminders from schedule list
	 *
	 * @since   1.3.5
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
				<label><?php _e( 'Bulk Unschedule', 'yith-woocommerce-review-reminder' ) ?></label>
			</th>
			<td class="forminp forminp-custom-send">
				<button type="button" class="button-secondary ywrr-unschedule-email"><?php _e( 'Clear', 'yith-woocommerce-review-reminder' ); ?></button>
				<div class="ywrr-unschedule-result send-progress"><?php _e( 'Please wait...', 'yith-woocommerce-review-reminder' ); ?></div>
				<span class="description" style="display: block;"><?php _e( 'Use this option to unschedule all pending emails from Schedule List.', 'yith-woocommerce-review-reminder' ); ?></span>
			</td>
		</tr>
		<?php
	}

}

/**
 * Unique access to instance of YWRR_Custom_Schedule class
 *
 * @return \YWRR_Custom_Unschedule
 */
function YWRR_Custom_Unschedule() {

	return YWRR_Custom_Unschedule::get_instance();

}

new YWRR_Custom_Unschedule();