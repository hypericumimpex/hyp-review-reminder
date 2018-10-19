<?php
/**
 * This file belongs to the YIT Plugin Framework.
 *
 * This source file is subject to the GNU GENERAL PUBLIC LICENSE (GPL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.gnu.org/licenses/gpl-3.0.txt
 */

if ( !defined( 'ABSPATH' ) ) {
    exit;
} // Exit if accessed directly

$site_url   = get_option( 'siteurl' );
$assets_url = untrailingslashit( YWRR_ASSETS_URL );

if ( strpos( $assets_url, $site_url ) === false ) {
    $assets_url = $site_url . $assets_url;
}

?>
<!DOCTYPE html>
<html dir="<?php echo is_rtl() ? 'rtl' : 'ltr' ?>">

<head>
    <meta name="viewport" content="width=device-width" />
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <title><?php echo get_bloginfo( 'name', 'display' ); ?></title>
    <style type="text/css">
        @import url(https://fonts.googleapis.com/css?family=Raleway:800,700,400);

        @media only screen and (max-width: 599px) {
            #header {
                height: 197px !important;
                line-height: 26px !important;
                font-size: 18px !important;
            }

            #header img {
                margin: 20px auto 30px auto !important;
                height: 40px !important;
            }

            .items {
                height: auto !important;
                text-align: center !important;
            }

            .items > img {
                float: none !important;
                margin: 0 auto 20px auto !important;
            }
        }
    </style>
</head>

<body <?php echo is_rtl() ? 'rightmargin' : 'leftmargin'; ?>="0" marginwidth="0" topmargin="0" marginheight="0" offset="0">
<table width="100%" height="100%" border="0" cellpadding="0" cellspacing="0">
    <tr>
        <td>
            <!--[if (gte mso 9)|(IE)]>
            <table width="600" align="center" cellpadding="0" cellspacing="0" border="0">
                <tr>
                    <td><![endif]-->
            <table id="content_table" align="center" cellpadding="0" cellspacing="0" border="0">
                <tr>
                    <td id="overheader">
                    </td>
                </tr>
                <tr>
                    <td id="header" valign="top">
                        <img src="<?php echo $assets_url; ?>/images/stars-icon.png" alt="" />
                        <?php echo $email_heading; ?>
                    </td>
                </tr>
                <tr>
                    <td id="mailbody">