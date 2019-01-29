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

if ( ! class_exists( 'YWRR_Review_Reminder_Premium' ) ) {

	/**
	 * Implements features of YWRR plugin
	 *
	 * @class   YWRR_Review_Reminder_Premium
	 * @package Yithemes
	 * @since   1.0.0
	 * @author  Your Inspiration Themes
	 */
	class YWRR_Review_Reminder_Premium extends YWRR_Review_Reminder {

		/**
		 * Returns single instance of the class
		 *
		 * @return \YWRR_Review_Reminder
		 * @since 1.1.5
		 */
		public static function get_instance() {

			if ( is_null( self::$instance ) ) {

				self::$instance = new self;

			}

			return self::$instance;

		}

		/**
		 * Constructor
		 *
		 * Initialize plugin and registers actions and filters to be used
		 *
		 * @since   1.0.0
		 * @return  void
		 * @author  Alberto Ruggiero
		 */
		public function __construct() {

			if ( ! function_exists( 'WC' ) ) {
				return;
			}

			parent::__construct();

			$this->_email_templates = array(
				'premium-1' => array(
					'folder' => 'emails/premium-1',
					'path'   => YWRR_TEMPLATE_PATH
				),
				'premium-2' => array(
					'folder' => 'emails/premium-2',
					'path'   => YWRR_TEMPLATE_PATH
				),
				'premium-3' => array(
					'folder' => 'emails/premium-3',
					'path'   => YWRR_TEMPLATE_PATH
				)
			);

			// register plugin to licence/update system
			add_action( 'wp_loaded', array( $this, 'register_plugin_for_activation' ), 99 );
			add_action( 'admin_init', array( $this, 'register_plugin_for_updates' ) );
			add_action( 'init', array( $this, 'ywrr_image_sizes' ) );

			// Include required files
			$this->includes();

			add_filter( 'yith_wcet_email_template_types', array( $this, 'add_yith_wcet_template' ) );
			add_action( 'yith_wcet_after_email_styles', array( $this, 'add_yith_wcet_styles' ), 10, 3 );
			add_filter( 'woocommerce_email_styles', array( $this, 'add_ywrr_styles' ) );
			add_filter( 'ywrr_product_permalink', array( $this, 'ywrr_product_permalink' ) );

			if ( get_option( 'ywrr_schedule_order_column' ) == 'yes' ) {

				add_filter( 'manage_shop_order_posts_columns', array( $this, 'add_ywrr_column' ), 11 );
				add_action( 'manage_shop_order_posts_custom_column', array( $this, 'render_ywrr_column' ), 3 );

			}

			add_action( 'admin_footer', array( $this, 'ywrr_admin_footer' ), 11 );
			add_action( 'load-edit.php', array( $this, 'ywrr_bulk_action' ) );
			add_action( 'admin_notices', array( $this, 'ywrr_bulk_admin_notices' ) );

			if ( is_admin() ) {

				add_filter( 'ywrr_admin_scripts_filter', array( $this, 'ywrr_admin_scripts_filter' ), 10, 2 );

				add_action( 'admin_enqueue_scripts', array( $this, 'ywrr_admin_scripts_premium' ) );
				add_action( 'ywrr_schedulelist', array( YWRR_Schedule_Table(), 'output' ) );

			} else {

				add_action( 'wp_enqueue_scripts', array( $this, 'ywrr_scripts' ) );


			}

		}

		/**
		 * Files inclusion
		 *
		 * @since   1.0.0
		 * @return  void
		 * @author  Alberto Ruggiero
		 */
		private function includes() {

			include_once( 'includes/class-ywrr-emails-premium.php' );
			include_once( 'includes/class-ywrr-schedule-premium.php' );
			include_once( 'includes/emails/class-ywrr-mandrill-premium.php' );

			if ( is_admin() ) {
				include_once( 'includes/admin/class-ywrr-ajax-premium.php' );
				include_once( 'includes/admin/meta-boxes/class-ywrr-meta-box.php' );
				include_once( 'templates/admin/schedule-table.php' );
				include_once( 'templates/admin/class-ywrr-custom-schedule.php' );
				include_once( 'templates/admin/class-ywrr-custom-unschedule.php' );
				include_once( 'templates/admin/class-ywrr-custom-clear-sent.php' );
				include_once( 'templates/admin/class-ywrr-custom-clear-cancelled.php' );
				include_once( 'templates/admin/class-ywrr-custom-mailskin.php' );
				include_once( 'templates/admin/class-yith-wc-custom-checklist.php' );
			}

		}

		/**
		 * Check if current user is a vendor
		 *
		 * @since   1.2.3
		 * @return  boolean
		 * @author  Alberto Ruggiero
		 */
		public function ywrr_vendor_check() {

			$is_vendor = false;

			if ( defined( 'YITH_WPV_PREMIUM' ) && YITH_WPV_PREMIUM ) {

				$vendor = yith_get_vendor( 'current', 'user' );

				$is_vendor = ( $vendor->id != 0 );

			}

			return $is_vendor;

		}

		/**
		 * Add the schedule column
		 *
		 * @since   1.2.2
		 *
		 * @param   $columns
		 *
		 * @return  array
		 * @author  Alberto Ruggiero
		 */
		public function add_ywrr_column( $columns ) {

			if ( ! $this->ywrr_vendor_check() ) {

				$columns['ywrr_status'] = __( 'Review Reminder', 'yith-woocommerce-review-reminder' );

			}

			return $columns;

		}


		/**
		 * Render the schedule column
		 *
		 * @since   1.2.2
		 *
		 * @param   $column
		 *
		 * @return  void
		 * @author  Alberto Ruggiero
		 */
		public function render_ywrr_column( $column ) {

			if ( ! $this->ywrr_vendor_check() && 'ywrr_status' == $column ) {

				global $post;

				$order = wc_get_order( $post->ID );

				$customer_id    = yit_get_prop( $order, '_customer_user' );
				$customer_email = yit_get_prop( $order, '_billing_email' );

				if ( YWRR_Blocklist()->check_blocklist( $customer_id, $customer_email ) == true ) {

					$is_funds    = yit_get_prop( $order, '_order_has_deposit' ) == 'yes';
					$is_deposits = yit_get_prop( $order, '_created_via' ) == 'yith_wcdp_balance_order';

					if ( $this->check_reviewable_items( $post->ID ) == 0 || $is_funds || $is_deposits ) {

						?>
                        <div class="toolbar">
							<?php _e( 'There are no reviewable items in this order', 'yith-woocommerce-review-reminder' ) ?>
							<?php
							if ( defined( 'YITH_WPV_PREMIUM' ) && YITH_WPV_PREMIUM ) {

								$suborders = YITH_Orders::get_suborder( $post->ID );

								if ( ! empty( $suborders ) ) {
									?><br /><?php

									foreach ( $suborders as $suborder_id ) {

										if ( $this->check_reviewable_items( $suborder_id ) == 0 ) {
											printf( __( 'Suborder #%s has no reviewable items', 'yith-woocommerce-review-reminder' ), $suborder_id );

										} else {

											$order_uri = apply_filters( 'yith_wcmv_edit_order_uri', esc_url( 'post.php?post=' . absint( $suborder_id ) . '&action=edit' ), absint( $suborder_id ) );
											$link_text = sprintf( __( 'Suborder %s has reviewable items', 'yith-woocommerce-review-reminder' ), '<strong>#' . $suborder_id . '</strong>' );

											printf( '<a href="%s">%s</a><br />',
											        $order_uri,
											        $link_text
											);

										}

									}

								}

							}
							?>

                        </div>
						<?php


					} else {

						global $wpdb;

						$schedule   = $wpdb->get_row( $wpdb->prepare( "SELECT scheduled_date, mail_status FROM {$wpdb->prefix}ywrr_email_schedule WHERE order_id = %d AND mail_status <> 'cancelled'", $post->ID ) );
						$order_date = yit_get_prop( $order, 'date_modified' );
						if ( ! $order_date ) {
							$order_date = yit_get_prop( $order, 'date_created' );

						}

						?>

                        <div class="ywrr-send-box">
                            <div class="buttons">
                                <button type="button" class="button tips do-send-email" data-tip="<?php _e( 'Send Email', 'yith-woocommerce-review-reminder' ); ?>"><?php _e( 'Send Email', 'yith-woocommerce-review-reminder' ); ?></button>
                                <button type="button" class="button tips do-reschedule-email" data-tip="<?php _e( 'Reschedule Email', 'yith-woocommerce-review-reminder' ); ?>"><?php _e( 'Reschedule Email', 'yith-woocommerce-review-reminder' ); ?></button>
                                <button type="button" class="button tips do-cancel-email" data-tip="<?php _e( 'Cancel Email', 'yith-woocommerce-review-reminder' ); ?>"><?php _e( 'Cancel Email', 'yith-woocommerce-review-reminder' ); ?></button>
                                <input class="ywrr-order-id" type="hidden" value="<?php echo yit_get_order_id( $order ) ?>">
                                <input class="ywrr-order-date" type="hidden" value="<?php echo yit_datetime_to_timestamp( $order_date ) ?>">
                            </div>
                            <div class="clear"></div>
                            <div class="ywrr-send-title" style="display: <?php echo( $schedule ? 'block' : 'none' ) ?>">

								<?php
								if ( $schedule ) {

									if ( $schedule->mail_status == 'pending' ) {
										$message = __( 'The request will be sent on %s', 'yith-woocommerce-review-reminder' );
									} else {
										$message = __( 'The request was sent on %s', 'yith-woocommerce-review-reminder' );
									}

									printf( $message, '<span class="ywrr-send-date">' . date_i18n( get_option( 'date_format' ), yit_datetime_to_timestamp( $schedule->scheduled_date ) ) . '</span>' );

								}
								?>

                            </div>
                            <div class="clear"></div>
                            <div class="ywrr-send-result send-progress"></div>
                            <div class="clear"></div>

                        </div>

						<?php

					}

				} else {

					_e( 'This customer doesn\'t want to receive any more review requests', 'yith-woocommerce-review-reminder' );

				}


			}

		}

		/**
		 * Add bulk actions to orders
		 *
		 * @since   1.2.2
		 * @return  void
		 * @author  Alberto Ruggiero
		 */
		public function ywrr_admin_footer() {
			global $post_type;

			if ( ! $this->ywrr_vendor_check() && 'shop_order' == $post_type ) {
				?>
                <script type="text/javascript">
                    jQuery(function () {
                        jQuery('<option>').val('ywrr_send').text('<?php _e( 'Review Reminder: Send Email', 'yith-woocommerce-review-reminder' )?>').appendTo('select[name="action"]');
                        jQuery('<option>').val('ywrr_send').text('<?php _e( 'Review Reminder: Send Email', 'yith-woocommerce-review-reminder' )?>').appendTo('select[name="action2"]');

                        jQuery('<option>').val('ywrr_reschedule').text('<?php _e( 'Review Reminder: Reschedule Email', 'yith-woocommerce-review-reminder' )?>').appendTo('select[name="action"]');
                        jQuery('<option>').val('ywrr_reschedule').text('<?php _e( 'Review Reminder: Reschedule Email', 'yith-woocommerce-review-reminder' )?>').appendTo('select[name="action2"]');

                        jQuery('<option>').val('ywrr_cancel').text('<?php _e( 'Review Reminder: Cancel Email', 'yith-woocommerce-review-reminder' )?>').appendTo('select[name="action"]');
                        jQuery('<option>').val('ywrr_cancel').text('<?php _e( 'Review Reminder: Cancel Email', 'yith-woocommerce-review-reminder' )?>').appendTo('select[name="action2"]');
                    });
                </script>
				<?php
			}
		}

		/**
		 * Trigger bulk actions to orders
		 *
		 * @since   1.2.2
		 * @return  void
		 * @author  Alberto Ruggiero
		 */
		public function ywrr_bulk_action() {

			if ( $this->ywrr_vendor_check() ) {
				return;
			}

			$wp_list_table = _get_list_table( 'WP_Posts_List_Table' );
			$action        = $wp_list_table->current_action();

			// Bail out if this is not a status-changing action
			if ( strpos( $action, 'ywrr_' ) === false ) {
				return;
			}

			$processed = 0;

			$post_ids = array_map( 'absint', (array) $_REQUEST['post'] );

			foreach ( $post_ids as $post_id ) {

				$order = wc_get_order( $post_id );

				$customer_id    = yit_get_prop( $order, '_customer_user' );
				$customer_email = yit_get_prop( $order, '_billing_email' );

				if ( YWRR_Blocklist()->check_blocklist( $customer_id, $customer_email ) == true ) {

					if ( $this->check_reviewable_items( $post_id ) == 0 ) {
						continue;
					}

					switch ( substr( $action, 5 ) ) {

						case 'send':

							$today      = new DateTime( current_time( 'mysql' ) );
							$order_date = yit_get_prop( $order, 'date_modified' );

							if ( ! $order_date ) {
								$order_date = yit_get_prop( $order, 'date_created' );
							}

							$pay_date     = new DateTime( date( 'Y-m-d H:i:s', yit_datetime_to_timestamp( $order_date ) ) );
							$days         = $pay_date->diff( $today );
							$email_result = YWRR_Emails()->send_email( $post_id, $days->days );

							break;

						case 'reschedule':

							$scheduled_date = date( 'Y-m-d', strtotime( current_time( 'mysql' ) . ' + ' . get_option( 'ywrr_mail_schedule_day' ) . ' days' ) );

							if ( YWRR_Schedule()->check_exists_schedule( $post_id ) != 0 ) {

								YWRR_Schedule_Premium()->reschedule( $post_id, $scheduled_date );

							} else {

								YWRR_Schedule()->schedule_mail( $post_id );

							}

							break;

						case 'cancel':

							if ( YWRR_Schedule()->check_exists_schedule( $post_id ) != 0 ) {

								YWRR_Schedule()->change_schedule_status( $post_id );

							}

							break;

					}

					$processed ++;

				}

			}

			$sendback = add_query_arg( array( 'post_type' => 'shop_order', 'ywrr_action' => substr( $action, 5 ), 'processed' => $processed, 'ids' => join( ',', $post_ids ) ), '' );

			wp_redirect( esc_url_raw( $sendback ) );
			exit();
		}

		/**
		 * Show notification after bulk actions
		 *
		 * @since   1.2.2
		 * @return  void
		 * @author  Alberto Ruggiero
		 */
		public function ywrr_bulk_admin_notices() {

			if ( $this->ywrr_vendor_check() ) {
				return;
			}

			global $post_type, $pagenow;

			// Bail out if not on shop order list page
			if ( 'edit.php' !== $pagenow || 'shop_order' !== $post_type ) {
				return;
			}

			if ( isset( $_REQUEST['ywrr_action'] ) ) {

				$number = isset( $_REQUEST['processed'] ) ? absint( $_REQUEST['processed'] ) : 0;

				switch ( $_REQUEST['ywrr_action'] ) {

					case'send':
						$message = sprintf( _n( 'Review Reminder: Email sent.', 'Review Reminder: %s emails sent', $number, 'yith-woocommerce-review-reminder' ), number_format_i18n( $number ) );
						break;

					case'reschedule':
						$message = sprintf( _n( 'Review Reminder: Email rescheduled.', 'Review Reminder: %s emails rescheduled.', $number, 'yith-woocommerce-review-reminder' ), number_format_i18n( $number ) );
						break;

					case'cancel':
						$message = sprintf( _n( 'Review Reminder: Email cancelled.', 'Review Reminder: %s emails cancelled.', $number, 'yith-woocommerce-review-reminder' ), number_format_i18n( $number ) );
						break;

					default:
						$message = '';
				}

				if ( $message ) {

					echo '<div class="updated"><p>' . $message . '</p></div>';

				}

			}

		}

		/**
		 * Set image sizes for email
		 *
		 * @since   1.0.4
		 * @return  void
		 * @author  Alberto Ruggiero
		 */
		public function ywrr_image_sizes() {

			add_image_size( 'ywrr_picture', 135, 135, true );

		}

		/**
		 * If is active YITH WooCommerce Email Templates, add YWRR to list
		 *
		 * @since   1.0.0
		 *
		 * @param   $templates
		 *
		 * @return  array
		 * @author  Alberto Ruggiero
		 */
		public function add_yith_wcet_template( $templates ) {

			$templates[] = array(
				'id'   => 'yith-review-reminder',
				'name' => 'YITH WooCommerce Review Reminder',
			);

			return $templates;

		}

		/**
		 * If is active YITH WooCommerce Email Templates, add YWRR styles
		 *
		 * @since   1.0.0
		 *
		 * @param   $premium_style
		 * @param   $meta
		 * @param   $current_email
		 *
		 * @return  void
		 * @author  Alberto Ruggiero
		 */
		public function add_yith_wcet_styles( $premium_style, $meta, $current_email ) {

			if ( $current_email->id == 'yith-review-reminder' ) {
				$this->email_styles();

				?>
                .ywrr-table td.title-column a{
                color:<?php echo $meta['base_color'] ?>;
                }
				<?php

			}

		}

		/**
		 * Add YWRR styles to WC Emails
		 *
		 * @since   1.0.0
		 *
		 * @param   $css
		 *
		 * @return  string
		 * @author  Alberto Ruggiero
		 */
		public function add_ywrr_styles( $css ) {
			ob_start();
			$this->email_styles();
			$css .= ob_get_clean();

			return $css;

		}

		/**
		 * Get email styles
		 *
		 * @since   1.0.0
		 * @return  void
		 * @author  Alberto Ruggiero
		 */
		public function email_styles() {

			?>

            .ywrr-table {
            border: none;
            }

            .ywrr-table td {
            border: none;
            border-bottom: 1px solid #e0e7f0;
            text-align: left;
            vertical-align: top;
            padding: 10px 0!important;
            }

            .ywrr-table td.picture-column {
            width: 135px;
            padding: 10px 20px 10px 0 !important;
            }

            .ywrr-table td.picture-column a {
            display: block;
            }

            .ywrr-table td.picture-column a img {
            margin: 0!important;
            max-width: 135px;
            }

            .ywrr-table td.title-column a {
            font-size: 16px;
            font-weight: bold!important;
            text-decoration: none;
            display: block:
            }

            .ywrr-table td.title-column a .stars{
            display: block;
            font-size: 11px;
            color: #6e6e6e;
            text-transform: uppercase:
            }

			<?php
		}

		/**
		 * Set the link to the product
		 *
		 * @since   1.0.4
		 *
		 * @param   $permalink
		 *
		 * @return  string
		 * @author  Alberto Ruggiero
		 */
		public function ywrr_product_permalink( $permalink ) {

			$link_type = get_option( 'ywrr_mail_item_link' );

			switch ( $link_type ) {
				case 'custom':
					$link_hash = get_option( 'ywrr_mail_item_link_hash' );

					if ( ! empty( $link_hash ) ) {

						if ( substr( $link_hash, 0, 1 ) === '#' ) {

							$permalink .= $link_hash;

						} else {

							$permalink .= '#' . $link_hash;

						}

					}

					break;

				case 'review':

					$permalink .= '#tab-reviews';

					break;

				default:

			}

			if ( get_option( 'ywrr_enable_analytics' ) == 'yes' ) {

				$campaign_source  = str_replace( ' ', '%20', get_option( 'ywrr_campaign_source' ) );
				$campaign_medium  = str_replace( ' ', '%20', get_option( 'ywrr_campaign_medium' ) );
				$campaign_term    = str_replace( ',', '+', get_option( 'ywrr_campaign_term' ) );
				$campaign_content = str_replace( ' ', '%20', get_option( 'ywrr_campaign_content' ) );
				$campaign_name    = str_replace( ' ', '%20', get_option( 'ywrr_campaign_name' ) );

				$query_args = array(
					'utm_source' => $campaign_source,
					'utm_medium' => $campaign_medium,
				);

				if ( $campaign_term != '' ) {

					$query_args['utm_term'] = $campaign_term;

				}

				if ( $campaign_content != '' ) {

					$query_args['utm_content'] = $campaign_content;

				}

				$query_args['utm_name'] = $campaign_name;

				$permalink = add_query_arg( $query_args, $permalink );

			}


			return $permalink;

		}

		/**
		 * ADMIN FUNCTIONS
		 */

		/**
		 * Advise if the plugin cannot be performed
		 *
		 * @since   1.0.3
		 * @return  void
		 * @author  Alberto Ruggiero
		 */
		public function ywrr_admin_notices() {
			if ( get_option( 'ywrr_mandrill_enable' ) == 'yes' && get_option( 'ywrr_mandrill_apikey' ) == '' ) : ?>
                <div class="error">
                    <p>
						<?php _e( 'Please enter Mandrill API Key for YITH Woocommerce Review Reminder', 'yith-woocommerce-review-reminder' ); ?>
                    </p>
                </div>
			<?php
			endif;
		}

		/**
		 * Initializes Javascript with localization
		 *
		 * @since   1.0.0
		 * @return  void
		 * @author  Alberto Ruggiero
		 */
		public function ywrr_admin_scripts_premium() {

			$suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';

			wp_enqueue_style( 'ywrr-admin-premium', YWRR_ASSETS_URL . 'css/ywrr-admin-premium' . $suffix . '.css' );
			wp_enqueue_script( 'ywrr-admin-premium', YWRR_ASSETS_URL . 'js/ywrr-admin-premium' . $suffix . '.js', array( 'jquery', 'ywrr-admin' ) );

		}

		/**
		 * Add premium strings for localization
		 *
		 * @since   1.1.5
		 *
		 * @param   $strings
		 * @param   $post
		 *
		 * @return  array
		 * @author  Alberto Ruggiero
		 */
		public function ywrr_admin_scripts_filter( $strings, $post ) {

			if ( $post ) {

				$order = wc_get_order( $post->ID );

				if ( $order ) {
					$strings['post_id'] = $post->ID;

					$order_date = yit_get_prop( $order, 'date_modified' );

					if ( ! $order_date ) {
						$order_date = yit_get_prop( $order, 'date_created' );

					}

					$strings['order_date'] = yit_datetime_to_timestamp( $order_date );
				}

			}

			$strings['is_order_page']          = isset( $_GET['post_type'] ) && $_GET['post_type'] == 'shop_order';
			$strings['do_send_email']          = __( 'Do you want to send remind email?', 'yith-woocommerce-review-reminder' );
			$strings['after_send_email']       = __( 'Reminder email has been sent successfully!', 'yith-woocommerce-review-reminder' );
			$strings['do_reschedule_email']    = __( 'Do you want to reschedule reminder email?', 'yith-woocommerce-review-reminder' );
			$strings['after_reschedule_email'] = __( 'Reminder email has been rescheduled successfully!', 'yith-woocommerce-review-reminder' );
			$strings['do_cancel_email']        = __( 'Do you want to cancel reminder email?', 'yith-woocommerce-review-reminder' );
			$strings['after_cancel_email']     = __( 'Reminder email has been cancelled!', 'yith-woocommerce-review-reminder' );
			$strings['not_found_cancel']       = __( 'There is no email to unschedule', 'yith-woocommerce-review-reminder' );
			$strings['please_wait']            = __( 'Please wait...', 'yith-woocommerce-review-reminder' );

			return $strings;

		}

		/**
		 * FRONTEND FUNCTIONS
		 */

		/**
		 * Initializes Javascript
		 *
		 * @since   1.0.4
		 * @return  void
		 * @author  Alberto Ruggiero
		 */
		public function ywrr_scripts() {

			$suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';

			wp_enqueue_script( 'ywrr-footer', YWRR_ASSETS_URL . 'js/ywrr-footer' . $suffix . '.js', array(), false, true );

		}

		/**
		 * YITH FRAMEWORK
		 */

		/**
		 * Register plugins for activation tab
		 *
		 * @return void
		 * @since    2.0.0
		 * @author   Andrea Grillo <andrea.grillo@yithemes.com>
		 */
		public function register_plugin_for_activation() {
			if ( ! class_exists( 'YIT_Plugin_Licence' ) ) {
				require_once 'plugin-fw/licence/lib/yit-licence.php';
				require_once 'plugin-fw/licence/lib/yit-plugin-licence.php';
			}
			YIT_Plugin_Licence()->register( YWRR_INIT, YWRR_SECRET_KEY, YWRR_SLUG );
		}

		/**
		 * Register plugins for update tab
		 *
		 * @return void
		 * @since    2.0.0
		 * @author   Andrea Grillo <andrea.grillo@yithemes.com>
		 */
		public function register_plugin_for_updates() {
			if ( ! class_exists( 'YIT_Upgrade' ) ) {
				require_once( 'plugin-fw/lib/yit-upgrade.php' );
			}
			YIT_Upgrade()->register( YWRR_SLUG, YWRR_INIT );
		}

		/**
		 * Plugin row meta
		 *
		 * add the action links to plugin admin page
		 *
		 * @since   1.0.0
		 *
		 * @param   $new_row_meta_args
		 * @param   $plugin_meta
		 * @param   $plugin_file
		 * @param   $plugin_data
		 * @param   $status
		 * @param   $init_file
		 *
		 * @return  array
		 * @author  Andrea Grillo <andrea.grillo@yithemes.com>
		 * @use     plugin_row_meta
		 */
		public function plugin_row_meta( $new_row_meta_args, $plugin_meta, $plugin_file, $plugin_data, $status, $init_file = 'YWRR_INIT' ) {
			$new_row_meta_args = parent::plugin_row_meta( $new_row_meta_args, $plugin_meta, $plugin_file, $plugin_data, $status, $init_file );

			if ( defined( $init_file ) && constant( $init_file ) == $plugin_file ) {
				$new_row_meta_args['is_premium'] = true;
			}

			return $new_row_meta_args;
		}

		/**
		 * Action Links
		 *
		 * add the action links to plugin admin page
		 * @since   1.0.0
		 *
		 * @param   $links | links plugin array
		 *
		 * @return  mixed
		 * @author  Andrea Grillo <andrea.grillo@yithemes.com>
		 * @use     plugin_action_links_{$plugin_file_name}
		 */
		public function action_links( $links ) {
			$links = yith_add_action_links( $links, $this->_panel_page, true );

			return $links;
		}

	}

}