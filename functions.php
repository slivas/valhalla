<?php

require_once "inc/code-snippets.php";

require_once "inc/yydevelopment-tag-manager.php";

require_once "viprewards/functions.php";

//=======Nexus=======
//require_once "inc/nexus-registration-data.php";

//======== Meta tags =======
require_once get_stylesheet_directory() . '/inc/wphead.php';

//======== Styles and scripts for new home page =======
require_once get_stylesheet_directory() . '/inc/includes/enqueue/home-page-enqueue.php';

//======== Styles and scripts for the cart =======
require_once get_stylesheet_directory() . '/inc/includes/enqueue/cart.php';

//======== Styles and scripts for the checkout =======
require_once get_stylesheet_directory() . '/inc/includes/enqueue/checkout-enqueue.php';

//======== Styles and scripts for the GLP care page =======
require_once get_stylesheet_directory() . '/inc/includes/enqueue/glp-care-enqueue.php';

//======== Styles and scripts for the GLP care page =======
require_once get_stylesheet_directory() . '/inc/includes/enqueue/vitality-nexus.php';

//======== Styles and scripts for the Nexus Home Dashboard =======
require_once get_stylesheet_directory() . '/inc/includes/enqueue/nexus-home-enqueue.php';

//======== Styles and scripts for the Content page for registered users =======
require_once get_stylesheet_directory() . '/inc/includes/enqueue/content-for-registered-users-enqueue.php';

//======== Styles and scripts for the Enclomiphene Therapy page =======
require_once get_stylesheet_directory() . '/inc/includes/enqueue/enclomiphene-therapy-enqueue.php';

//======== Styles and scripts for the Affiliate page =======
require_once get_stylesheet_directory() . '/inc/includes/enqueue/affiliate-enqueue.php';

//======== Styles and scripts for the VIP rewards page =======
require_once get_stylesheet_directory() . '/inc/includes/enqueue/vip-rewards.php';

//======== Registration scripts =======
require_once get_stylesheet_directory() . '/inc/includes/enqueue/registration-enqueue.php';

//======== Testosterone scripts =======
require_once get_stylesheet_directory() . '/inc/includes/enqueue/testosterone-enqueue.php';

//======== Styles and scripts for Quiz Thank You =======
require_once get_stylesheet_directory() . '/inc/includes/enqueue/thank-you.php';


function enqueue_custom_checkout_script() {
    // Only enqueue on the checkout page

    if (is_checkout()) {
        wp_add_inline_script('jquery', <<<JS

document.addEventListener("DOMContentLoaded", function () {
    setTimeout(function () {
        const select = document.querySelector('select[name="additional_wooccm2"]');
        if (select) {
            const blankOption = select.querySelector('option[value=""]');
            if (blankOption) {
                blankOption.remove();
            }

            // Set default selected value explicitly
            select.value = "Yes"; // must match the actual 'value', not label
	    
	    // Trigger a change event so WooCommerce Checkout Manager can respond
            const event = new Event('change', { bubbles: true });
            select.dispatchEvent(event);
        }
    }, 300); // delay to allow plugin-generated DOM to render
});

JS
        );
    }
}

add_action( 'wp_enqueue_scripts', 'salient_child_enqueue_styles', 100);

function salient_child_enqueue_styles() {
    $nectar_theme_version = nectar_get_theme_version();
    wp_enqueue_style( 'salient-child-style', get_stylesheet_directory_uri() . '/style.css', '', $nectar_theme_version );
    if ( is_rtl() ) {
        wp_enqueue_style(  'salient-rtl',  get_template_directory_uri(). '/rtl.css', array(), '1', 'screen' );
    }
}

function my_enqueue_registration_style() {
    if (
            is_page( array( 'registration', 'registration-wellness', 'medical-intake-form') ) ||
            is_singular( 'product' )
    ) {
        wp_enqueue_style(
                'registration-style',
                get_stylesheet_directory_uri() . '/inc/assets/css/registration.css',
                array(),
                '1.0',
                'all'
        );
    }
}
add_action( 'wp_enqueue_scripts', 'my_enqueue_registration_style' );


function my_enqueue_product_style() {
    if ( is_product() ) {
        wp_enqueue_style('product-style', get_stylesheet_directory_uri() . '/inc/assets/css/product.css', array(), '1.0', 'all');
        wp_enqueue_script('product-script', get_stylesheet_directory_uri() . '/inc/assets/scripts/product.js', array(), '1.0');
    }
}
add_action( 'wp_enqueue_scripts', 'my_enqueue_product_style' );


add_filter( 'woocommerce_subscriptions_is_recurring_fee', '__return_true' );


add_filter( 'woocommerce_cart_totals_order_total_html', 'custom_cart_totals_order_total_html', 20, 1 );


function custom_cart_totals_order_total_html( $value ){

    $value = '<strong>' . WC()->cart->get_total() . '</strong> ';



    // If prices are tax inclusive, show taxes here.

    $incl_tax_display_cart = version_compare( WC_VERSION, '3.3', '<' ) ? WC()->cart->tax_display_cart == 'incl'  : WC()->cart->display_prices_including_tax();

    if ( wc_tax_enabled() && $incl_tax_display_cart ) {

        $tax_string_array = array();

        $cart_tax_totals  = WC()->cart->get_tax_totals();



        if ( get_option( 'woocommerce_tax_total_display' ) == 'itemized' ) {

            foreach ( $cart_tax_totals as $code => $tax ) {

                $tax_string_array[] = sprintf( '%s %s', $tax->formatted_amount, $tax->label );

            }

        } elseif ( ! empty( $cart_tax_totals ) ) {

            $tax_string_array[] = sprintf( '%s %s', wc_price( WC()->cart->get_taxes_total( true, true ) ), WC()->countries->tax_or_vat() );

        }



        if ( ! empty( $tax_string_array ) ) {

            $taxable_address = WC()->customer->get_taxable_address();

            $estimated_text  = '';

            $value .= '<small class="includes_tax">' . sprintf( __( '(includes %s)', 'woocommerce' ), implode( ', ', $tax_string_array ) . $estimated_text ) . '</small>';

        }

    }

    return $value;

}



/**

 * Replace the home link URL

 */

add_filter( 'woocommerce_breadcrumb_home_url', 'woo_custom_breadrumb_home_url' );



function woo_custom_breadrumb_home_url() {

    return '/valhalla-vitality-therapy-shop';

}



/**

 * Rename "home" in breadcrumb

 */

add_filter( 'woocommerce_breadcrumb_defaults', 'wcc_change_breadcrumb_home_text', 20 );



function wcc_change_breadcrumb_home_text( $defaults ) {

    // Change the breadcrumb home text from 'Home' to 'Shop'

    $defaults['home'] = 'Shop';

    return $defaults;

}



/**

 * Add accessiBe widget script */



add_action('wp_footer', function () {

    echo <<<HTML

	<!--<script> (function(){ var s = document.createElement('script'); var h = document.querySelector('head') || document.body; s.src = 'https://acsbapp.com/apps/app/dist/js/app.js'; s.async = true; s.onload = function(){ acsbJS.init({ statementLink : '', footerHtml : '', hideMobile : false, hideTrigger : false, disableBgProcess : false, language : 'en', position : 'left', leadColor : '#146FF8', triggerColor : '#146FF8', triggerRadius : '50%', triggerPositionX : 'left', triggerPositionY : 'bottom', triggerIcon : 'people', triggerSize : 'bottom', triggerOffsetX : 20, triggerOffsetY : 20, mobile : { triggerSize : 'small', triggerPositionX : 'left', triggerPositionY : 'bottom', triggerOffsetX : 10, triggerOffsetY : 10, triggerRadius : '20' } }); }; h.appendChild(s); })(); </script>-->

	<style>#wps-wps-pr-drag { display: none; }#wps-pr-mobile-open-popup{display:none}</style>

	HTML;

});



/**

 * Add menu item attributes necessary for triggering the accessiBe widget

 */

add_action(

        'nav_menu_link_attributes',

        function ($atts, $item) {

            // If a menu item has the 'acsb-custom-trigger' class, then we'll add

            // the data-acsb-custom-trigger attribute which is required to triger

            // the widget.

            // @link https://accessibe.com/support/customization/how-can-i-create-a-custom-button-that-opens-the-interface

            if (in_array('acsb-custom-trigger', $item->classes ?? [], true)) {

                $atts['data-acsb-custom-trigger'] = 'true';

            }

            return $atts;

        },

        10,

        2

);



/**

 *  Overriding language for Points & Rewards Pro plugin

 */

function custom_plugin_text_strings( $translated_text, $text, $domain ) {

    // Ensure the modification is only for the specific plugin's text domain

    if ( 'ultimate-woocommerce-points-and-rewards' === $domain ) {

        switch ( trim($translated_text) ) {

            case 'How to Earn More!':

                $translated_text = 'Valhalla VIP Rewards';

                break;

            case 'Total Point :':

                $translated_text = 'Total Points :';

                break;

            case 'Place The Order and Earn Points':

                $translated_text = 'Place an order and ';

                break;

            case 'Points on every':

                $translated_text = ' point on every ';

                break;

            case 'Refer Someone':

                $translated_text = 'Invite a Friend';

                break;

            case 'Reward is :':

                $translated_text = 'Earn ';

                break;

            case 'Convert Points into Coupons':

                $translated_text = 'Apply Points on Cart Total';

                break;

            case 'Point':

                $translated_text = ' points for each accepted invitation';

                break;

        }

    }

    return $translated_text;

}

add_filter( 'gettext', 'custom_plugin_text_strings', 20, 3 );



add_action( 'woocommerce_account_my-appointments_endpoint', 'wpsh_endpoint_content3' );

function wpsh_endpoint_content3() {



    // At the moment I will add Learndash profile with the shordcode

    echo (

    '<h3>My Appointments</h3>'

    );

    echo do_shortcode('[bookly-appointments-list columns="category,service,staff,date,time,time_zone,online_meeting,status" show_column_titles="1"]');

    echo (

    '<br><h4>Book An Appointment</h4>'

    );

    echo do_shortcode('[bookly-form hide="staff_members,date,week_days,time_range"]');

}



function save_custom_account_bday_field( $user_id ) {

    if ( isset( $_POST['account_bday'] ) ) {

        // Sanitize the input to ensure it's a valid date format.

        $account_bday = sanitize_text_field( $_POST['account_bday'] );



        // Update the user meta with the new value.

        update_user_meta( $user_id, '_my_bday', $account_bday );

    }

}

add_action( 'woocommerce_save_account_details', 'save_custom_account_bday_field' );

function custom_enqueue_scripts() {

    if (is_account_page()) {

        wp_enqueue_script('enable-account-bday-field', get_template_directory_uri() . '/js/enable-birthday-field.js', array('jquery'), '1.0', true);

    }

}

add_action('wp_enqueue_scripts', 'custom_enqueue_scripts');



function add_birthday_to_user_profile( $user ) {

    ?>

    <h3><?php esc_html_e( 'Extra Profile Information', 'your-textdomain' ); ?></h3>



    <table class="form-table">

        <tr>

            <th><label for="birthday"><?php esc_html_e( 'Birthday', 'your-textdomain' ); ?></label></th>

            <td>

                <input type="date" name="_my_bday" id="birthday" value="<?php echo esc_attr( get_user_meta( $user->ID, '_my_bday', true ) ); ?>" class="regular-text" />

                <span class="description"><?php esc_html_e( 'Please enter your birthday.', 'your-textdomain' ); ?></span>

            </td>

        </tr>

    </table>

    <?php

}

add_action( 'show_user_profile', 'add_birthday_to_user_profile' ); // Adding to your own profile

add_action( 'edit_user_profile', 'add_birthday_to_user_profile' ); // Adding to another user's profile



// Save the birthday field value when the user profile is updated

function save_birthday_user_profile( $user_id ) {

    // Check if the current user has permission to edit the user

    if ( !current_user_can( 'edit_user', $user_id ) ) {

        return false;

    }



    // Save the birthday data if it's present in the POST array

    if ( !empty( $_POST['_my_bday'] ) ) {

        update_user_meta( $user_id, '_my_bday', sanitize_text_field( $_POST['_my_bday'] ) );

    }

}

add_action( 'personal_options_update', 'save_birthday_user_profile' ); // Saving your own profile

add_action( 'edit_user_profile_update', 'save_birthday_user_profile' ); // Saving another user's profile



///collapsable my account page navigation

function app_additional_assets(){

    if (is_account_page()) {

        wp_enqueue_script('additional_js', get_stylesheet_directory_uri() . '/inc/assets/scripts/additional.js', array('jquery'), null, true);

        wp_enqueue_style('additional_styles', get_stylesheet_directory_uri() . '/inc/assets/css/additional.css', null, time());

    }

}

add_action('wp_enqueue_scripts', 'app_additional_assets');

//Valhalla VIP Rewards Page CSS

function custom_points_page_css_functionality() {

    $current_page = trim( $_SERVER["REQUEST_URI"] , '/' );

    if ( is_user_logged_in() && $current_page=='my-account/points' ) {

        ?>

        <script>

            (function ($) {

                $(document).ready(function () {

                    jQuery('.wps_wpr_heading').css('text-align','left');

                    jQuery('.wps_wpr_upgrade_level').css('display','block');

                    jQuery('.wps_wpr_upgrade_level').css('text-align','left');

                    jQuery('.wps_ways_to_gain_points_section .wps_wpr_heading').contents().filter(function() {

                        return this.nodeType === Node.TEXT_NODE && this.nodeValue.trim() === 'Ways to gain more points:';

                    }).each(function() {

                        this.nodeValue = 'Ways to Earn More Points:';

                    });

                    var oldlink = jQuery('.wps_wpr_refrral_code_copy code').html();

                    oldlink = oldlink.replace("accessibility-statement", "");

                    jQuery('.wps_wpr_refrral_code_copy code').html(oldlink);

                    var referralUrl = jQuery('code').html();

                    jQuery('code').before("<p style='width:90%; text-align:center; font-size:12px; background-color:#EAEAEA; padding:5px; line-height:1;'>"+referralUrl+"</p>");

                    jQuery('code').remove();

                    var referralContainer = jQuery('.wps_account_wrapper').detach();

                    jQuery('.wps_ways_to_gain_points_section').after(referralContainer);

                    jQuery('.wps_wpr_whatsapp_share').css('display','flex!important');

                    jQuery('.wps_wpr_whatsapp_share').css('width','32px');

                    jQuery('.wps_wpr_wrapper_button .wps_wpr_mail_button').css('width','50px');

                    jQuery('.wps_wpr_wrapper_button .wps_wpr_mail_button').css('background-color','#ffbf37');

                    jQuery('.woocommerce-MyAccount-points').after("<p><center><a href='/terms-conditions' target='_blank'>Click here</a> to view the terms and conditions for the Valhalla Vitality VIP Rewards Program</center></p>");

                })

            })(jQuery)

        </script>

        <?php

    }

}

add_action( 'wp_footer', 'custom_points_page_css_functionality', 9999 );

add_action( 'wp_footer', 'custom_add_registration_notification' );

function pointsandrewardssettings($id){

    $wps_wpr_value    = 0;

    $general_settings = get_option( 'wps_wpr_settings_gallery', true );

    if ( ! empty( $general_settings[ $id ] ) ) {

        $wps_wpr_value = (int) $general_settings[ $id ];

    }

    return $wps_wpr_value;

}

function custom_add_registration_notification() {

    $current_page = trim( $_SERVER["REQUEST_URI"] , '/' );

    $current_page = explode('?',$current_page);

    if ( $current_page[0] =='registration' ) {

        $wps_wpr_notification_color = pointsandrewardssettings( 'wps_wpr_notification_color' );

        $wps_wpr_notification_color = ( ! empty( $wps_wpr_notification_color ) ) ? $wps_wpr_notification_color : '#55b3a5';



        $wps_wpr_signup_value = pointsandrewardssettings( 'wps_wpr_general_signup_value' );

        $enable_wps_signup    = pointsandrewardssettings( 'wps_wpr_general_signup' );



        if ( $enable_wps_signup ) {

            ?>

            <script>

                var woocommerce_points_message = '<div class="woocommerce-message" style="background-color: <?php echo esc_attr( $wps_wpr_notification_color ); ?>;color: #000000;border: none;"><?php echo esc_html__( 'All members get a head start with VIP rewards. ', 'points-and-rewards-for-woocommerce' ) . esc_html( $wps_wpr_signup_value ) . esc_html__( ' points awarded upon registration!', 'points-and-rewards-for-woocommerce' ); ?></div>';

                (function ($) {

                    $(document).ready(function () {

                        jQuery('h1').after(woocommerce_points_message);

                    })

                })(jQuery)

            </script>

            <?php

        }

    }

}

add_action( 'wp_footer', 'custom_provider_registration_notification',1 );

function custom_provider_registration_notification() {

    $current_page = trim( $_SERVER["REQUEST_URI"] , '/' );

    $current_page = explode('?',$current_page);

    if ( $current_page[0] =='registration' ) {

        if(isset($_GET['ref_provider_key'])){

            ?>

            <script>

                jQuery('.contact_dr').hide();

            </script>

            <?php

            $show_provider_info = false;

            switch ($_GET['ref_provider_key']){

                case "zf05OSUW":

                    $show_provider_info = true;

                    $provider_name = "Rosanna Paknoush, FNP";

                    $provider_image = "/wp-content/uploads/2024/02/dr-w.png";

                    break;

                case "a20fOSWW":

                    $show_provider_info = true;

                    $provider_name = "Dr. Harirajan Mani, MD";

                    $provider_image = "/wp-content/uploads/2024/02/dr-m.png";

                    break;

                case "5AZmXQb1":

                    $show_provider_info = true;

                    $provider_name = "Dr. Leita Harris";

                    $provider_logo = "/wp-content/uploads/2024/08/logo-scaled.jpg";

                    $provider_image = "/wp-content/uploads/2024/08/Leita-pic-labcoat-final-scaled.jpg";

                    break;

                case "Mp2FJb3c":

                    $show_provider_info = true;

                    $provider_name = "J.Aaron Henley, DO";

                    $provider_logo = "/wp-content/uploads/2024/08/aureahealthlogo.png";

                    break;

                case "X5n9P357":

                    $show_provider_info = true;

                    $provider_logo = "/wp-content/uploads/2024/09/Original-Logo.png";

                    $provider_details = "New Hope Mental Health and Wellness<br>Dr. Kameko McGuire, NP<br>124 E. Miracle Strip Parkway<br>Suite 202<br>Mary Esther, FL 32569<br>Phone: 850-226-8096";

                    break;

                case "Z3c2h1p5":

                    $show_provider_info = true;

                    $referral_logo = "/wp-content/uploads/2025/08/SCM_LOGO_Image.png";

                    $referral_details = "You were referred by: ";

                    break;

                case "58MbqXWy":

                    $show_provider_info = true;

                    $provider_name = "Sandra Regina Appel";

                    $provider_logo = "/wp-content/uploads/2024/09/Atlas-Premier-Health-Logo.png";

                    break;

                case "fmIi4rkR":

                    $show_provider_info = true;

                    $provider_name = "";

                    $provider_logo = "/wp-content/uploads/2024/10/NewLife_Health_Wellness_hjh6sx.png";

                    $provider_details = "Nicholas Smith<br>5440 W 110th St., STE 3015<br>Overland Park, KS 66211<br>Phone: (816) 808-7404";

                    break;

                case "XQt9fiM0":

                    $show_provider_info = true;

                    $provider_name = "Vianka Cesena, NP";

                    $provider_logo = "/wp-content/uploads/2024/10/CrispAZWellness-LOGO-JPG-1.jpg";

                    $provider_details = "Total Salud<br>Vianka Cesena, NP<br>13291 W McDowell Rd, Suite E-5 #1060<br>Goodyear, AZ 85395<br>Phone: (623) 850-8264";

                    break;

                case "t3OUi3L4":

                    $show_provider_info = true;

                    $provider_name = "Laurie Dahl, NP";

                    $provider_logo = "/wp-content/uploads/2024/10/LOGO-1.jpg";

                    $provider_details = "My Direct Health<br>Laurie Dahl, NP<br>700 Twin Creeks Crossing Loop, Suite A<br>Central Point, OR 97502<br>Phone: (541) 500-0561";

                    break;

                case "ed7qHGgY":

                    $show_provider_info = true;

                    $provider_logo = "/wp-content/uploads/2024/10/Logoblack.png";

                    $provider_details = "Upstanding Care Chiropractic<br>2802 Juan St, Ste 12<br>San Diego, CA 92110<br>Phone: (619) 381-3759";

                    break;

                case "A1JqvyjD":

                    $show_provider_info = true;

                    $provider_name = "Eleanor Hethcox, DNP";

                    $provider_logo = "/wp-content/uploads/2024/10/Copy-of-Screenshot-2024-09-21-at-7.01.46 AM.png";

                    $provider_image = "/wp-content/uploads/2024/10/ellieheadshot.jpg";

                    $provider_details = "RxBodyFx<br>Eleanor Hethcox, DNP<br>121 W Parkwood Avenue<br>Friendswood, TX 77546<br>Phone: (409) 256-3996";

                    break;

                case "6t4eoari":

                    $show_provider_info = true;

                    $provider_image = "/wp-content/uploads/2024/10/image014.jpg";

                    $provider_details = "Boonton Medical Associates &amp; Advocare Prime Family Care<br>Dr. Anas Salem & Dr. Mohammed Salem<br><br>Boonton Office<br>223 W Main St<br>Boonton, New Jersey, 07005<br>Phone: (973) 873-4939<br>Fax: (866) 778-0015<br><br>Paterson Office<br>1044 Main Street,<br>Paterson, NJ 07503<br>Phone: 973-988-3000<br>Fax: 973-278-2818";

                    break;

                case "VFmMwMNs":

                    $show_provider_info = true;

                    $provider_logo = "/wp-content/uploads/2024/10/Arfooz_Web_Logo.jpg";

                    $provider_image = "/wp-content/uploads/2024/10/IMG_2456-scaled.jpg";

                    $provider_details = "Sultana J Afrooz, D.O.<br>8808 Centre Park Drive, Suite 301<br>Windsor Mill, MD 21045";

                    break;

                case "cYVvgJCn":

                    $show_provider_info = true;

                    $provider_logo = "/wp-content/uploads/2024/10/IMG-20240723-WA0009.jpg";

                    $provider_image = "/wp-content/uploads/2024/10/FB_IMG_1629691486556_Original3109.jpg";

                    $provider_details = "Fabiola B. Marcelin<br>4319 Salisbury Road, Suite 103<br>Jacksonville, FL 32216<br>Ph: 904-337-1268<br>Fax: 720-600-0873<br>Web: www.ufcremote.com";

                    break;

                case "ujbbDCO6":

                    $show_provider_info = true;

                    $provider_logo = "/wp-content/uploads/2024/10/New-Chalmers-Wellness-Logo-2024.jpg";

                    $provider_details = "Vitality Health & Wellness<br>6988 Lebanon Road, Suite 101<br>Frisco, TX 75034<br>214.446.5300<br>214.446.5304<br>www.pillarsofwellness.com<br>www.chalmerswellness.com";

                    break;

                case "SyTfJevr":

                    $show_provider_info = true;

                    $provider_logo = "/wp-content/uploads/2024/10/bellaclinicalcare.png";

                    $provider_details = "Avril Davis, APRN<br>Bella Clinical Care<br>534 Saint Andrews Rd, Suite B<br>Columbus, SC 29210<br>Phone: (803) 489-8777";

                    break;

                case "VRh1XpiU":

                    $show_provider_info = true;

                    $provider_logo = "/wp-content/uploads/2024/10/bellaclinicalcare.png";

                    $provider_details = "Gregory Sieverding, APRN<br>Bella Clinical Care<br>534 Saint Andrews Rd, Suite B<br>Columbus, SC 29210<br>Phone: (803) 489-8777";

                    break;

                case "XQt9fiM0":

                    $show_provider_info = true;

                    $provider_logo = "/wp-content/uploads/2024/10/CrispAZWellness-LOGO-JPG-1-1.jpg";

                    $provider_details = "Vianka Cesena, NP <br>Total Salud<br>13291 W McDowell Rd, Suite E-5 #1060<br>Goodyear, AZ 85395<br>Phone: (623) 850-8264";

                    break;

                case "gdVXfixn":

                    $show_provider_info = true;

                    $provider_logo = "/wp-content/uploads/2024/10/dispa.png";

                    $provider_details = "Diana Dion, NP <br>SansAge Medical Aesthetic<br>287 South Main St, Unit#3<br>Concord, NH 03301<br>Phone: (603) 556-1739";

                    break;

                case "Xqcs1pAe":

                    $show_provider_info = true;

                    $provider_logo = "/wp-content/uploads/2024/10/mynp.png";

                    $provider_details = "Rosalyn McFarland, NP<br>My NP Professional, LLC<br>5050 West Brown Deer Road<br>Brown Deer, WI 53223<br>Phone: (414) 308-9468";

                    break;

                case "BAvlEEQ0":

                    $show_provider_info = true;

                    $provider_logo = "/wp-content/uploads/2024/10/Rejuvalife_Logo.png";

                    $provider_details = "Andre Berger, MD<br>Rejuvalife Vitality Institute<br>9735 Wilshire Blvd , #417<br>Beverly Hills, CA 90212<br>Phone: (310) 276-4494";

                    break;

                case "ORj8tMKN":

                    $show_provider_info = true;

                    $provider_logo = "/wp-content/uploads/2024/10/LOGO-1.png";

                    $provider_details = "Thomas Mattio, MD<br>Neuroscience Group<br>1305 W American Drive<br>Neenah, WI 54956<br>Phone: (920) 809-3118";

                    break;

                case "g6P8KzkP":

                    $show_provider_info = true;

                    $provider_logo = "/wp-content/uploads/2024/10/SFSBI-Logo-1-1.png";

                    $provider_details = "Dr. Eric Valladares<br>South Florida Surgery Bariatric Cosmetic Institute<br>351 NW 42 Avenue, Suite 303<br>Miami, Florida, 33126<br>Phone: 305-631-5355<br>Fax: 786-368-1519";

                    break;

                case "gRL5nFCR":

                    $show_provider_info = true;

                    $provider_details = "Fonderre Musongong, NP<br>19-21 Fair Lawn Avenue,<br>Fair Lawn, NJ 07410";

                    break;

                case "XXVG56f6":

                    $show_provider_info = true;

                    $provider_logo = "/wp-content/uploads/2024/11/SpineWellnessCentersofAmerica.jpg";

                    $provider_details = "Dr. Gina Corsaletti<br>Spine and Wellness Centers of America";

                    break;

                case "lGdmwqU1":

                    $show_provider_info = true;

                    $provider_image = "/wp-content/uploads/2025/01/kelsey-house.png";

                    $provider_logo = "/wp-content/uploads/2025/01/house-of-hormones.png";

                    $provider_details = "Kelsey House<br>House of Hormones & Weight Loss";

                    break;

                case "cwhQw8Tk":

                    $show_provider_info = true;

                    $provider_image = "/wp-content/uploads/2025/01/Headshot12.jpg";

                    $provider_logo = "/wp-content/uploads/2025/01/Mother-Goose-Logo.png";

                    $provider_details = "Keri Southall, APRN<br>Mother Goose Wellness<br>4255 SW Cambridge Glen<br>Lake City, Florida 32024<br>352-210-7126";

                    break;

                case "AddCUvVi":

                    $show_provider_info = true;

                    $provider_image = '/wp-content/uploads/2025/01/image0.jpeg';

                    $provider_details = "Shereef El-Ibiary, MD<br>Shereef El-Ibiary MD LLC<br>9 Rockhampton Dr<br>Greenville, SC 29607<br>	(864) 387-9730";

                    break;

                case "Vx4LiwtG":

                    $show_provider_info = true;

                    $provider_image = '/wp-content/uploads/2025/02/IMG_1109103.jpg';

                    $provider_logo = "/wp-content/uploads/2025/02/EWB_MAIN-LOGO_ON-BLACK-1.jpg";

                    $provider_details = "Princess Lomax DNP, APRN, FNP-C, WCC<br>Exquisite Wellness Bar";

                    break;

                case "Hv80U1FE":

                    $show_provider_info = true;

                    $provider_logo = "/wp-content/uploads/2025/02/Copy-of-LogoColorTextBelow.jpeg";

                    $provider_details = "Dr. Thirumalesh Venkatesh<br>Seven Hills Med Service PC<br>113 Franklin Ave,<br> Franklin Square, New York, 11010<br>Phone: 516-354-2707";

                    break;

                case "ABEUxZ4G":

                    $show_provider_info = true;

                    $provider_logo = "/wp-content/uploads/2025/07/EI-Logo-079.png";

                    $provider_details = "Angelica McGough<br>Essential Infusions Plus<br>604 N Main St<br>Rochelle, Illinois, 61068<br>Phone: (815) 762-4307";

                    break;

                case "SIcJ0cjK":

                    $show_provider_info = true;

                    $provider_logo = "/wp-content/uploads/2025/07/RMD_Logo.jpg";

                    $provider_details = "Jennifer Shealy<br>	Rural Medicine Direct<br>700 Twin Creeks Crossing Loop, Suite A<br>Central Point, Oregon, 97502<br>Phone: (801) 725-5789";

                    break;

                case "IGQUiJpK":

                    $show_provider_info = true;

                    $provider_logo = "/wp-content/uploads/2025/07/Rasheeda_Hall_Hall_Health_logo.png";

                    $provider_details = "Rasheeda Hall<br>	Hall Health, PLLC<br>511 W Laurel Ave.<br>Hattiesburg, Mississippi, 39401<br>Phone: (601) 658-9343";

                    break;

                case "LTAp0gaM":

                    $show_provider_info = true;

                    $provider_logo = "/wp-content/uploads/2025/07/Adam_Chavez_Chavez_Complete_Wellness.png";

                    $provider_details = "Adam Chavez<br>     Chavez Complete Wellness LLC<br>1804 N Naper Blvd, Suite 470<br>Naperville, Illinois, 60653<br>Phone: (847) 909-3462";

                    break;

                case "FxBM9tH6":

                    $show_provider_info = true;

                    $provider_logo = "/wp-content/uploads/2025/07/Jennifer_Ransom_Ascent_Health.png";

                    $provider_details = "Jennifer Ranson<br>     Ascent Elite Health<br>206 Chase Park<br>Hurricane, West Virginia, 25526<br>Phone: (304) 546-2777";

                    break;

            }

            if($show_provider_info){

                ?>

                <div id="ref_provider" style="width:65%;max-width:400px;min-width:350px;margin: 0 auto;border: 1px solid #CCC;padding: 10px;border-radius: 5px;margin-bottom: 10px;display:none;">

                    <?php if(isset($referral_details)): ?>

                        <?php echo $referral_details; ?>

                    <?php endif; ?>

                    <?php if(isset($provider_logo)): ?>

                        <img src="<?php echo $provider_logo; ?>" style="width:75%; margin:0 auto; display:block; border-bottom: 1px solid #CCCCCC; margin-bottom:10px;" />

                    <?php endif; ?>

                    <? if (!isset($referral_details)): ?>

                        <h3 style="text-align:left;margin-bottom:-45px;">Your Provider</h3>

                    <?php endif; ?>

                    <?php if(isset($provider_details)): ?>

                        <div class="provider_details" style="margin-top:55px; font-size:13px; color:#333; line-height:1.3;">

                            <?php echo $provider_details; ?>

                        </div>

                    <?php endif; ?>

                    <?php if(isset($provider_name)): ?>

                        <div class="provider_name" style="float: left;margin-top: 100px;margin-left:30px;font-size: 85%;font-weight: bold;text-align: center; color:black;"><?php echo $provider_name; ?></div>

                    <?php endif; ?>

                    <?php if(isset($provider_image)): ?>

                        <div class="provider_image"><img src="<?php echo $provider_image; ?>" style="width: 35%; margin-top:20px;"></div>

                    <?php endif; ?>

                    <?php if(isset($referral_details)): ?>

                        <div class="provider_image">

                            <?php if(isset($referral_logo)): ?>

                                <img src="<?php echo $referral_logo; ?>" style="width: 55%; margin-top:20px;">

                            <?php endif; ?>

                        </div>

                    <?php endif; ?>

                    <div style="clear:both"></div>

                </div>

                <script>

                    (function ($) {

                        $(document).ready(function () {

                            var ref_provider = jQuery('#ref_provider').detach();

                            jQuery('h1').after(ref_provider);

                            jQuery('#ref_provider').show();

                        })

                    })(jQuery)

                </script>

                <?php

            }

        }

    }

}



//Update add to cart messaging

add_filter( 'woocommerce_get_script_data', 'change_alert_text', 10, 2 );

function change_alert_text( $params, $handle ) {

    if ( $handle === 'wc-add-to-cart-variation' )

        $params['i18n_unavailable_text'] = __( 'This service requires a prescription from a Valhalla Vitality provider. To get started, please create an account.', 'domain' );



    return $params;

}



add_action( 'wp_footer', 'terms_and_conditions_button_checkout', 9999 );

function terms_and_conditions_button_checkout(){

    $current_page = trim( $_SERVER["REQUEST_URI"] , '/' );

    if ( is_user_logged_in() && $current_page=='checkout' ) {

        ?>

        <script>

            jQuery( document ).ready(function() {

                setTimeout(function() {

                    jQuery('.woocommerce-terms-and-conditions-link').removeClass();

                    console.log('<?php echo $current_page; ?>');

                }, 2000);

            });

        </script>

        <?php

    }

}





//Function to Show Membership Level//

function show_membership_level($user) {

    // Retrieve the user meta value

    $membership_level = get_user_meta($user->ID, 'membership_level', true);

    ?>

    <table class="form-table">

        <tr>

            <th><label for="membership_level">Membership Level</label></th>

            <td>

                <input type="text" name="membership_level" id="membership_level" value="<?php echo esc_attr($membership_level); ?>" class="regular-text" readonly />

            </td>

        </tr>

    </table>

    <?php

}



// Hook the function to 'show_user_profile' and 'edit_user_profile' actions

add_action('show_user_profile', 'show_membership_level');

add_action('edit_user_profile', 'show_membership_level');



// Add custom field to user profile

function add_usertype_field($user) {

    // Check if the current user is an admin

    if (current_user_can('administrator')) {

        $usertype = get_user_meta($user->ID, 'usertype', true);

        ?>

        <h3><?php _e("User's Account Type", "blank"); ?></h3>



        <table class="form-table">

            <tr>

                <th>

                    <label for="usertype"><?php _e("User's Account Type"); ?></label>

                </th>

                <td>

                    <select name="usertype" id="usertype">

                        <option value="patient" <?php selected($usertype, 'patient'); ?>>Patient</option>

                        <option value="scribe" <?php selected($usertype, 'scribe'); ?>>Scribe</option>

                        <option value="office" <?php selected($usertype, 'office'); ?>>Office</option>

                        <option value="provider" <?php selected($usertype, 'provider'); ?>>Provider</option>

                    </select>

                </td>

            </tr>

        </table>

        <?php

    }

}

add_action('show_user_profile', 'add_usertype_field');

add_action('edit_user_profile', 'add_usertype_field');



// Save custom field value

function save_usertype_field($user_id) {

    // Check if the current user is an admin

    if (current_user_can('administrator')) {

        update_user_meta($user_id, 'usertype', $_POST['usertype']);

    }

}

add_action('personal_options_update', 'save_usertype_field');

add_action('edit_user_profile_update', 'save_usertype_field');



add_action('wp_loaded', 'vip_rewards_page_redirect_logged_in_users');

// Redirect VIP rewards frontend to account page if logged in

function vip_rewards_page_redirect_logged_in_users() {

    $current_path = trim(parse_url(add_query_arg([]), PHP_URL_PATH), '/');

    // Check if the current page is "vip-rewards"

    if ($current_path === 'vip-rewards') {

        // Check if the user is logged in

        if (is_user_logged_in()) {

            // Redirect to the "my-account" page

            wp_safe_redirect(home_url('/my-account/points'));

            exit;

        }

    }

}



//Assign provider on initial consultation booking

add_action('woocommerce_before_cart', 'assign_provider_id_cart_object');

function assign_provider_id_cart_object() {

    global $wpdb;

    if ( is_user_logged_in() ) {

        $current_user = wp_get_current_user();

        $cart_contents_object = WC()->cart->cart_contents;

        $cart_content = json_decode(json_encode($cart_contents_object),true);

        foreach($cart_content as $cart){

            if($cart['product_id']=='12898'){

                if(isset($cart['bookly']['slots'][0][1])){

                    $staff_id = $cart['bookly']['slots'][0][1];

                    $query = $wpdb->prepare("SELECT wp_user_id FROM wpvw_bookly_staff WHERE id = %d",$staff_id);

                    $temp_provider_id = $wpdb->get_var($query);

                    $usermeta_value = get_user_meta($current_user->ID, 'provider', true);

                    if($usermeta_value==''){

                        update_user_meta($current_user->ID, 'provider', $temp_provider_id);

                    }

                }

            }

        }

    }

}

//Checkout routing (to be migrated to openpath)

add_filter('woocommerce_available_payment_gateways', 'filter_payment_gateways_based_on_usermeta');

function filter_payment_gateways_based_on_usermeta($available_gateways) {

    foreach ($available_gateways as $gateway_id => $gateway) {

        if ($gateway_id !== 'authorize_net_cim_credit_card') {

            unset($available_gateways[$gateway_id]);

        }

    }

    /* OLD Payment Gateway Routing

    $usermeta_key = 'provider';

    $current_user = wp_get_current_user();

    $usermeta_value = get_user_meta($current_user->ID, $usermeta_key, true);

    $billing_state = WC()->checkout->get_value('billing_state');

    switch($usermeta_value){

        case '34': //Rosanna

            foreach ($available_gateways as $gateway_id => $gateway) {

                if ($gateway_id !== 'authorize_net_cim_credit_card') {

                    unset($available_gateways[$gateway_id]);

                }

            }

            break;

        case '40405': //Allison

            foreach ($available_gateways as $gateway_id => $gateway) {

                if ($gateway_id !== 'authorize_net_cim_credit_card') {

                    unset($available_gateways[$gateway_id]);

                }

            }

            break;

        case '5372': //Mani

            foreach ($available_gateways as $gateway_id => $gateway) {

                if ($gateway_id !== 'openpathpay') {

                    unset($available_gateways[$gateway_id]);

                }

            }

            break;

        case '38900': //Princess

            foreach ($available_gateways as $gateway_id => $gateway) {

                if ($gateway_id !== 'openpathpay') {

                    unset($available_gateways[$gateway_id]);

                }

            }

            break;

        case '60554': //Sage

        case '47980': //Sage

        case '41048': //Jordan

        case '44230': //Mattio

        case '44232': //Helliwell

        case '44233': //McGuire

        case '45405': //Henley

        case '45406': //Harris

        case '45407': //Appel

        case '46908': //Mathew Upchurch

        case '53366': //Gina Corsaletti

        case '51790':

        case '51789':

        case '51788':

        case '51786':

        case '51785':

        case '50656':

        case '51174':

        case '48574':

        case '48573':

        case '48575':

        case '48129':

        case '48128':

        case '47986':

        case '47984':

        case '47983':

        case '47982':

        case '47980':

        case '47979':

        case '47978':

        case '48125':

        case '54535':

        case '59000':

        case '58999':

        case '58997':

        case '58996':

            foreach ($available_gateways as $gateway_id => $gateway) {

                if ($gateway_id !== 'openpathpay') {

                    unset($available_gateways[$gateway_id]);

                }

            }

            break;

        case '39071': //Kris

            foreach ($available_gateways as $gateway_id => $gateway) {

                if ($gateway_id !== 'openpathpay') {

                    unset($available_gateways[$gateway_id]);

                }

            }

            break;

        default: //Default

            switch ($billing_state) {

                //Rosanna

                case 'Rockaway Hotel':

                case 'AK':

                case 'AR':

                case 'CA':

                case 'CO':

                case 'MA':

                case 'MO':

                case 'NY':

                case 'NC':

                case 'OR':

                case 'PA':

                case 'RI':

                case 'SD':

                    foreach ($available_gateways as $gateway_id => $gateway) {

                        if ($gateway_id !== 'authorize_net_cim_credit_card') {

                            unset($available_gateways[$gateway_id]);

                        }

                    }

                    break;

                    //Kris

                case 'FL':

                case 'VA':

                    foreach ($available_gateways as $gateway_id => $gateway) {

                        if ($gateway_id !== 'openpathpay') {

                            unset($available_gateways[$gateway_id]);

                        }

                    }

                    break;

                //Mani

                default:

                    foreach ($available_gateways as $gateway_id => $gateway) {

                        if ($gateway_id !== 'openpathpay') {

                            unset($available_gateways[$gateway_id]);

                        }

                    }

                    break;

            }

            break;

    }

    */

    return $available_gateways;

}





function add_custom_js_to_admin_footer() {

    ?>

    <script type="text/javascript">

        document.addEventListener('DOMContentLoaded', function() {

            var targetNode = document.body;

            var config = { childList: true, subtree: true };



            var callback = function(mutationsList, observer) {

                for (var mutation of mutationsList) {

                    if (mutation.type === 'childList') {

                        var element = document.getElementById('paynote_status_update_all');

                        if (element) {

                            element.style.marginBottom = '30px';

                            element.style.backgroundColor = 'blue';

                            element.style.color = 'white';

                            element.style.marginLeft = '30px';



                            var formElement = document.querySelector('form#posts-filter');

                            if (formElement) {

                                formElement.parentNode.insertBefore(element, formElement);

                            }

                            observer.disconnect(); // Stop observing after the element is found and styled

                            break;

                        }

                    }

                }

            };



            var observer = new MutationObserver(callback);

            observer.observe(targetNode, config);

        });

    </script>

    <?php

}

add_action('admin_footer', 'add_custom_js_to_admin_footer');



function add_signup_button_to_login_form() {

    ?>

    <hr />

    <p class="woocommerce-SignUp-text">

        <?php esc_html_e( "Don't have an account yet?", 'woocommerce' ); ?>

    </p>

    <p class="woocommerce-SignUp">

        <a class="woocommerce-Button button" href="<?php echo esc_url( home_url( '/registration' ) ); ?>" style="padding-right: 60px !important;padding-left: 60px !important;padding: 16px 23px important;line-height: 1.2em;    width: 100%;    border-radius: 26px;    font-size: 16px;    font-weight: bold;">

            <?php esc_html_e( 'Sign Up', 'woocommerce' ); ?>

        </a>

    </p>

    <style>

        .woocommerce-SignUp-text {

            text-align: center;

            margin-top: 20px;

        }

        .woocommerce-SignUp {

            text-align: center;

            margin-top: 10px;

        }

        .woocommerce-SignUp .button {

            background-color: #E6B800; /* Change this to your desired color */

            border: none;

            border-radius: 25px; /* Adjust the border radius as per your design */

            color: #fff; /* Text color */

            padding: 10px 20px; /* Padding */

            text-decoration: none; /* Remove underline from the link */

            display: inline-block;

            margin-top: 10px;

        }

        .woocommerce-SignUp .button:hover {

            background-color: #ffd83e; /* Change this to your desired hover color */

        }

    </style>

    <?php

}

add_action( 'woocommerce_login_form_end', 'add_signup_button_to_login_form' );







//Show customer date of birth on order details page

//// Add custom user meta (birthdate) to order details in admin under Billing Details

add_action('woocommerce_admin_order_data_after_billing_address', 'display_custom_user_birthday_in_order', 10, 1);



function display_custom_user_birthday_in_order($order){

    // Get the user ID from the order

    $user_id = $order->get_user_id();



    // Check if the user ID exists

    if($user_id){

        // Get the user birthdate from user meta

        $user_birthdate = get_user_meta($user_id, '_my_bday', true);



        // Display the user birthdate

        if($user_birthdate){

            echo '<p><strong>'.__('Date of Birth').':</strong> ' . esc_html($user_birthdate) . '</p>';

        }

    }

}



//Limiting max input of characters on First and Last name on registration

function custom_enqueue_scripts_registration_character_limit() {

    if (is_page('registration')) {

        wp_enqueue_script('jquery');

    }

}

add_action('wp_enqueue_scripts', 'custom_enqueue_scripts_registration_character_limit');



// Add custom script to the registration page

function custom_registration_char_limit_script() {

    if (is_page('registration')) {

        ?>

        <script type="text/javascript">

            jQuery(document).ready(function($) {

                function updateCharacterCount(field) {

                    var maxLength = 35;

                    var currentLength = $(field).val().length;

                    var remaining = maxLength - currentLength;

                    $(field).next('.char-count').text(currentLength + ' / ' + maxLength + ' ');

                    $(field).next('.char-remaining').text(remaining + ' ');

                }



                $('#first_name, #last_name').each(function() {

                    $(this).after('<div class="char-count"></div><div class="char-remaining"></div>');

                    updateCharacterCount(this);

                    $(this).on('input', function() {

                        if ($(this).val().length > 35) {

                            $(this).val($(this).val().substring(0, 35));

                        }

                        updateCharacterCount(this);

                    });

                });

            });

        </script>

        <style type="text/css">

            .char-count, .char-remaining {

                font-size: 0.6em;

                text-align:right;

                margin-top:-15px;

                font-weight:bold;

                color: #666;

            }

        </style>

        <?php

    }

}

add_action('wp_footer', 'custom_registration_char_limit_script');



function custom_enqueue_scripts_checkout_character_limit() {

    if (is_checkout()) {

        wp_enqueue_script('jquery');

    }

}

add_action('wp_enqueue_scripts', 'custom_enqueue_scripts_checkout_character_limit');



//Limiting max input of characters on Address lines on checkout page

function custom_checkout_char_limit_script() {

    if (is_checkout()) {

        ?>

        <script type="text/javascript">

            jQuery(document).ready(function($) {

                function updateCharacterCount(field) {

                    var maxLength = 35;

                    var currentLength = $(field).val().length;

                    var remaining = maxLength - currentLength;

                    $(field).next('.char-count').text(currentLength + ' / ' + maxLength + ' characters');

                    $(field).next('.char-remaining').text(remaining + ' characters remaining');

                }



                $('#billing_address_1, #billing_address_2, #shipping_address_1, #shipping_address_2').each(function() {

                    $(this).after('<div class="char-count"></div><div class="char-remaining"></div>');

                    updateCharacterCount(this);

                    $(this).on('input', function() {

                        if ($(this).val().length > 35) {

                            $(this).val($(this).val().substring(0, 35));

                        }

                        updateCharacterCount(this);

                    });

                });

                setInterval(function(){

                    jQuery('.wc_payment_method.payment_method_eh_authorize_net_aim_card img').css({

                        'width': '32px',   // Set desired width

                        'height': 'auto'    // Maintain aspect ratio

                    });

                }, 2000);

                setInterval(function(){

                    jQuery('.wc_payment_method img').css({

                        'width': '32px',

                        'height': 'auto'

                    });

                },2000);



            });

        </script>

        <style type="text/css">

            .char-count, .char-remaining {

                font-size: 0.6em;

                text-align:right;

                margin-top:-8px;

                font-weight:bold;

                color: #666;

            }

            .wc_payment_method.payment_method_eh_authorize_net_aim_card img {

            'width': '32px'

            }

        </style>

        <?php

    }

}

add_action('wp_footer', 'custom_checkout_char_limit_script');

//Increase password reset key length

add_filter( 'password_reset_expiration', function() {

    return DAY_IN_SECONDS * 3; // Set the expiration to 3 days (72 hours)

});



if( !function_exists( 'custom_login_logo' ) ){

    function custom_login_logo() {

        echo '<style>

            .login h1 a { 

				background-image: url("/wp-content/uploads/2024/02/Valhalla_horiz_logo.png") !important; 

				background-size:300px;

				width:330px!important;

				}

        </style>';

    }

    add_action( 'login_head', 'custom_login_logo' );

}



//Tirzepatide Therapy

function custom_js_on_tirzepatide_product_page() {

    // Specify the product ID for which you want the JavaScript to run

    $product_id = 54541; // Replace with your actual product ID



    // Check if this is the specific product page

    if (is_product() && get_the_ID() == $product_id) {

        ?>

        <script type="text/javascript">

            jQuery(document).ready(function(){

                if(jQuery('#ppom-box-3').length){

                    jQuery("#ppom-box-3").insertBefore(".variations");

                    jQuery('#ppom-box-3').after('<p class="redirect-tirzepatide-message">Please <a href="/product/commercially-available-tirzepatide">click here</a> to purchase a prescription for the commercially available variation of this medication.</p>');

                    jQuery('.redirect-tirzepatide-message').show();

                    jQuery('.variations').hide();

                }

                jQuery('.shortage_question').change(function(){

                    if(jQuery(this).val()=='No'){

                        jQuery('.variations').hide();

                        jQuery('.redirect-tirzepatide-message').show();

                    }else{

                        jQuery('.variations').show();

                        jQuery('.redirect-tirzepatide-message').hide();

                    }

                });

            });

        </script>

        <?php

    }

}

add_action('wp_footer', 'custom_js_on_tirzepatide_product_page');



// Use jQuery to show/hide the medication dose field based on the first field selection

add_action( 'wp_footer', 'custom_checkout_field_js' );



function custom_checkout_field_js() {

    if (is_checkout()) :

        ?>

        <script type="text/javascript">



            jQuery(document).ready(function(){

                jQuery('#additional_wooccm2').append(jQuery('<option>', {

                    value: '',

                    text: 'Select One',

                    selected: 'selected'

                }));

                jQuery('.wooccm-field-wooccm2').css('background-color','#d1dfbb');

                jQuery('.wooccm-field-wooccm2').css('padding','20px');

                jQuery('.wooccm-field-wooccm3').css('background-color','#d1dfbb');

                jQuery('.wooccm-field-wooccm3').css('padding','20px');

                jQuery('.woocommerce-terms-and-conditions-wrapper').before('<a href="/cybersecurity" target="_blank"><img id="checkout_badges" src="/wp-content/uploads/2024/10/safe-checkout.png" /></a>');

                setTimeout(function(){

                    jQuery('#checkout_badges').remove();

                    jQuery('.woocommerce-terms-and-conditions-wrapper').before('<a href="/cybersecurity" target="_blank"><img id="checkout_badges" src="/wp-content/uploads/2024/10/safe-checkout.png" /></a>');

                }, 5000);



            });



        </script>

    <?php

    endif;

}





function restrict_admin_access_for_user($user_id) {

    if (is_admin()) {

        $current_user = wp_get_current_user();



        // Check if the current user ID is the restricted user

        if ($current_user->ID == 59033) {

            global $pagenow;



            // List of allowed pages and plugin menus

            $allowed_pages = [

                    'index.php', // Dashboard (optional, you can remove this)

                    'admin.php?page=wc-admin&path=/analytics', // WooCommerce Analytics

            ];



            $allowed_plugins = [

                    'woocommerce/woocommerce.php', // WooCommerce plugin

            ];



            // Allow access to WooCommerce analytics and plugin-specific pages

            $allowed = false;

            if (in_array($pagenow, $allowed_pages)) {

                $allowed = true;

            }



            if (isset($_GET['page']) && strpos($_GET['page'], 'wc-admin') !== false) {

                $allowed = true;

            }



            if (!$allowed) {

                wp_safe_redirect(admin_url('/admin.php?page=wc-admin&path=%2Fanalytics%2Foverview'));

                exit;

            }

        }

    }

}

add_action('admin_init', 'restrict_admin_access_for_user', 10, 1);



// Disable the admin bar for the restricted user

function disable_admin_bar_for_restricted_user($show_admin_bar) {

    $current_user = wp_get_current_user();

    if ($current_user->ID == 59033) {

        return false;

    }

    return $show_admin_bar;

}

add_filter('show_admin_bar', 'disable_admin_bar_for_restricted_user');





add_action('template_redirect', function () {

    // Check if the user is logged in

    if (is_user_logged_in()) {

        // Get the current page URL or slug

        global $wp;

        $current_url = home_url($wp->request);



        // Check if the current page is '/astrid-demo'

        if (strpos($current_url, '/astrid-demo') !== false) {

            // Your custom code to execute on this page

            ?><script>console.log('logged in');</script><?php

            require_once('astriddemo/astrid.php');

        }

    }

});



add_action('show_user_profile', 'display_provider_ratings');

add_action('edit_user_profile', 'display_provider_ratings');



function display_provider_ratings($user) {

    global $wpdb;



    // Check if the user has usermeta with `meta_key = usertype` and `meta_value = provider`

    $usertype = get_user_meta($user->ID, 'usertype', true);



    if ($usertype === 'provider') {

        // Run the custom query to fetch ratings for the provider

        $provider_id = $user->ID;

        $results = $wpdb->get_results(

                $wpdb->prepare("SELECT * FROM provider_ratings WHERE provider_id = %d ORDER BY rating_dt DESC", $provider_id),

                ARRAY_A

        );



        // Display the results

        if (!empty($results)) {

            echo '<h2>Provider Ratings</h2>';

            echo '<table class="wp-list-table widefat fixed striped">';

            echo '<thead><tr><th>Rating</th><th>Comment</th><th>Rating Date</th></tr></thead>';

            echo '<tbody>';

            foreach ($results as $row) {

                echo '<tr>';

                echo '<td>' . esc_html($row['rating']) . ' Stars</td>';

                echo '<td>' . esc_html($row['feedback']) . '</td>';

                echo '<td>' . esc_html(date('m/d/Y h:i A',strtotime($row['rating_dt']))) . '</td>';

                echo '</tr>';

            }

            echo '</tbody></table>';

        } else {

            echo '<h2>Provider Ratings</h2>';

            echo '<p>No ratings found for this provider.</p>';

        }

    }

}





/** Add class for upload page **/

function add_custom_body_class_for_upload_files( $classes ) {

    if ( strpos( $_SERVER['REQUEST_URI'], '/my-account/upload-files' ) !== false ) {

        $classes[] = 'upload-files-page';

    }

    return $classes;

}



add_filter( 'body_class', 'add_custom_body_class_for_points_page' );

/** Add class for upload page **/

function add_custom_body_class_for_points_page( $classes ) {

    if ( strpos( $_SERVER['REQUEST_URI'], '/my-account/points' ) !== false ) {

        $classes[] = 'points-page';

    }

    return $classes;

}



function exclude_category_or_404_on_specific_url($query) {

    if (is_admin() || !$query->is_main_query()) {

        return;

    }



    $taxonomy = get_query_var('taxonomy');

    $term = get_query_var('term');

    $paged = get_query_var('paged');



    // Check specifically for your URL structure

    if ($taxonomy === 'product_shipping_class' && ($term === 'standard' || $term === 'revive-rx-standard') && $paged > 1) {

        global $wp_query;

        $wp_query->set_404();

        status_header(404);

        nocache_headers();

        return;

    }



    // Original exclusion logic

    if ($taxonomy === 'product_shipping_class' && ($term === 'standard' || $term === 'revive-rx-standard')) {

        $excluded_category = 'wellness-therapies';



        $tax_query = $query->get('tax_query') ?: [];

        $tax_query[] = [

                'taxonomy' => 'product_cat',

                'field'    => 'slug',

                'terms'    => $excluded_category,

                'operator' => 'NOT IN',

        ];



        $query->set('tax_query', $tax_query);

        $query->set('paged', max(1, $paged)); // Ensure pagination works properly

    }

}

add_action('pre_get_posts', 'exclude_category_or_404_on_specific_url');

function add_category_selection_script() {

    if (isset($_GET['category']) && $_GET['category'] == 57 && is_page('book-your-consultation')) {

        ?>

        <script>

            (function() {

                function waitForElement(selector, callback, intervalTime = 100) {

                    const interval = setInterval(() => {

                        const element = document.querySelector(selector);

                        if (element) {

                            clearInterval(interval);

                            callback(element);

                        }

                    }, intervalTime);

                }



                // State abbreviation to option value mapping

                const stateMapping = {

                    "AL": 1, "AZ": 2, "AK": 51, "CA": 4, "AR": 3, "CO": 5, "CT": 6, "DE": 7,

                    "DC": 23, "FL": 8, "GA": 9, "ID": 10, "IL": 11, "IN": 12, "HI": 50, "IA": 13,

                    "KS": 14, "KY": 15, "LA": 16, "ME": 17, "MD": 18, "MA": 19, "MI": 20, "MN": 21,

                    "MS": 22, "MO": 24, "MT": 25, "NE": 26, "NV": 27, "NH": 28, "NJ": 29, "NY": 31,

                    "ND": 33, "OH": 34, "OK": 35, "PA": 37, "TN": 41, "NM": 30, "TX": 42, "UT": 43,

                    "NC": 32, "VT": 44, "WA": 46, "WV": 47, "WI": 48, "OR": 36, "WY": 49, "RI": 38,

                    "SC": 39, "SD": 40, "VA": 45

                };



                // Function to get URL parameter

                function getUrlParam(name) {

                    const params = new URLSearchParams(window.location.search);

                    return params.get(name);

                }



                document.addEventListener("DOMContentLoaded", function() {

                    // Find the select inside [data-type="location"] and set state

                    waitForElement('[data-type="location"] select', function(locationSelect) {

                        const stateAbbr = getUrlParam("state"); // Get state from URL

                        if (stateAbbr && stateMapping[stateAbbr.toUpperCase()]) {

                            locationSelect.value = stateMapping[stateAbbr.toUpperCase()];

                            locationSelect.dispatchEvent(new Event('change', { bubbles: true }));

                        }



                        // After location select is updated, wait for category select and set value 57

                        waitForElement('[data-type="category"] select', function(categorySelect) {

                            categorySelect.value = "57";

                            categorySelect.dispatchEvent(new Event('change', { bubbles: true }));

                        });

                    });

                });

            })();

        </script>

        <?php

    }

}

add_action('wp_footer', 'add_category_selection_script');



// Everest forms login URL message

function ef_login_register_links_shortcode() {

    if (!is_user_logged_in()) {

        $current_url = home_url(add_query_arg(null, null)); // Gets current page URL



        // custom page URLs

        $custom_login_url = home_url('/my-account'); // Change this

        $custom_register_url = home_url('/registration'); // Change this



        // Add redirect query to login URL

        $login_url = add_query_arg('redirect_to', urlencode($current_url), $custom_login_url);



        return '<div class="ef-login-register-links">

                    <a href="' . esc_url($login_url) . '">Log in</a> or <a href="' . esc_url($custom_register_url) . '">sign up</a> for free to access your customized Wellness Blueprint!

                </div>';

    }

    return ''; // Nothing for logged-in users

}

add_shortcode('ef_login_register_links', 'ef_login_register_links_shortcode');





add_action('template_redirect', 'redirect_wellnessplan_lp_based_on_login_status');



function redirect_wellnessplan_lp_based_on_login_status() {

    if (is_admin() || wp_doing_ajax()) {

        return; // Prevent interfering with admin or AJAX

    }



    $current_url = trim($_SERVER['REQUEST_URI'], '/');



    // If user is NOT logged in and visiting the 'existing' LP, redirect to 'new'

    if ($current_url === 'vitality-wellness-blueprint-lp-existing' && !is_user_logged_in()) {

        wp_redirect(home_url('/vitality-wellness-blueprint-lp-new'));

        exit;

    }



    // If user IS logged in and visiting the 'new' LP, redirect to 'existing'

    if ($current_url === 'vitality-wellness-blueprint-lp-new' && is_user_logged_in()) {

        wp_redirect(home_url('/vitality-wellness-blueprint-lp-existing'));

        exit;

    }

}



// Add a custom column after "Date" in WooCommerce Products list

add_filter( 'manage_edit-product_columns', 'add_last_modified_by_column', 20 );

function add_last_modified_by_column( $columns ) {

    $new_columns = [];



    foreach ( $columns as $key => $value ) {

        $new_columns[ $key ] = $value;



        if ( $key === 'date' ) {

            $new_columns['last_edited_by'] = __( 'Last Edited By', 'your-text-domain' );

        }

    }



    return $new_columns;

}



// Populate the custom column with the user who last modified the product

add_action( 'manage_product_posts_custom_column', 'show_last_modified_by_column', 10, 2 );

function show_last_modified_by_column( $column, $post_id ) {

    if ( $column === 'last_edited_by' ) {

        $last_user_id = get_post_meta( $post_id, '_edit_last', true );

        if ( $last_user_id ) {

            $user_info = get_userdata( $last_user_id );

            echo esc_html( $user_info->display_name );

        } else {

            echo '—';

        }

    }

}

add_action( 'admin_head', 'custom_admin_css_styles' );

function custom_admin_css_styles() {

    $screen = get_current_screen();

    if ( $screen && $screen->id === 'edit-product' ) { // limit to WooCommerce Products page

        echo '<style>

            .column-last_edited_by {

                width: 10%;

            }

        </style>';

    }

}

add_action('template_redirect', function() {

    if (!is_admin()) {

        if (isset($_GET['taxonomy'], $_GET['term']) && $_GET['taxonomy'] === 'product_shipping_class' && ($_GET['term'] === 'standard' || $_GET['term']==='revive-rx-standard')) {

            global $wp_query;

            $wp_query->set_404();

            status_header(404);

            nocache_headers();

            include(get_query_template('404'));

            exit;

        }

    }

});


add_action('woocommerce_order_status_processing', 'v_sync_order_to_spotdx', 10, 1);

function v_sync_order_to_spotdx($order_id) {
    if (!$order_id) return;

    $order = wc_get_order($order_id);

    if ($order->get_meta('_spot_order_id')) {
        return;
    }

    $api_token = "d5875286a42c779355d0345b4e5b12e6d00ae497";
    $api_url   = "https://app.spotdx.com/api/v2/orders/";

    $sku_map = [
            "522.10" => "valhalla_comprehensive_wellness_test",
            "522.20" => "valhalla_hormone_test",
            "522.21" => "valhalla_hormone_test",
            "522.30" => "valhalla_female_wellness_test"
    ];

    $kits_to_order = [];

    foreach ($order->get_items() as $item) {
        $product = $item->get_product();
        $sku     = $product ? $product->get_sku() : '';

        if (isset($sku_map[$sku])) {
            $kit_type = $sku_map[$sku];
            $qty      = (int) $item->get_quantity();

            for ($i = 0; $i < $qty; $i++) {
                $kits_to_order[] = $kit_type;
            }
        }
    }

    if (empty($kits_to_order)) {
        return;
    }

    $payload = [
            "recipient" => [
                    "first_name" => $order->get_shipping_first_name() ?: $order->get_billing_first_name(),
                    "last_name"  => $order->get_shipping_last_name() ?: $order->get_billing_last_name(),
                    "address" => [
                            "street1" => $order->get_shipping_address_1(),
                            "street2" => $order->get_shipping_address_2(),
                            "city"    => $order->get_shipping_city(),
                            "state"   => $order->get_shipping_state(),
                            "zip"     => $order->get_shipping_postcode(),
                    ],
                    "email" => $order->get_billing_email(),
                    "phone" => $order->get_billing_phone(),
            ],
            "kit_types" => $kits_to_order,
    ];

    $response = wp_remote_post($api_url, [
            'method'    => 'POST',
            'timeout'   => 45,
            'headers'   => [
                    'Content-Type'  => 'application/json',
                    'Authorization' => 'Token ' . $api_token,
            ],
            'body'      => json_encode($payload),
    ]);

    if (is_wp_error($response)) {
        v_log_spot_failure($order, "Connection Error: " . $response->get_error_message());
        return;
    }

    $code = wp_remote_retrieve_response_code($response);
    $body = json_decode(wp_remote_retrieve_body($response), true);

    if ($code === 200 || $code === 201) {
        $spot_id = $body['order_id'] ?? 'PROCESSED';

        $order->update_meta_data('_spot_order_id', $spot_id);
        $order->add_order_note("SpotDX Success. ID: $spot_id");
        $order->save();

        // Send Success Email
        $to      = "farhan@ironsail.ai";
        $subject = "SpotDX Sync Success - Order #" . $order_id;
        $message = "Order #" . $order_id . " has been successfully synced with SpotDX.\nSpot Order ID: " . $spot_id;
        wp_mail($to, $subject, $message);

    } else {
        $error_msg = $body['detail'] ?? wp_remote_retrieve_body($response);
        v_log_spot_failure($order, "API Error ($code): $error_msg");
    }
}

function v_log_spot_failure($order, $error_text) {
    $order->add_order_note("SpotDX Failure: $error_text");
    $order->save();
    error_log("SpotDX Fail | Order #{$order->get_id()} | $error_text");
    wp_mail("farhan@ironsail.ai", "SpotDX Fail - #" . $order->get_id(), $error_text);
}

add_filter('woocommerce_coupon_is_valid_for_product', 'prevent_coupon_for_bundle_products', 10, 4);

function prevent_coupon_for_bundle_products($valid, $product, $coupon, $values) {
    // Only continue if the product is valid so far
    if (!$valid) {
        return false;
    }

    // Get product ID (account for variation)
    $product_id = $product->get_id();

    // Get product tags (term slugs)
    $product_tags = wp_get_post_terms($product_id, 'product_tag', array('fields' => 'slugs'));

    // If the product has the 'bundle' tag, disallow the coupon
    if (in_array('bundle', $product_tags)) {
        return false;
    }

    return true;
}

add_action( 'wp_footer', function () {
    if ( is_page( 'book-your-consultation' ) && is_user_logged_in() ) {
        $wp_user_id = get_current_user_id();
        $provider_id = (int) get_user_meta( $wp_user_id, 'provider', true );
        ?>
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                let tries = 0;
                let interval = setInterval(function () {
                    const staffGroup = document.querySelector('.bookly-form-group[data-type="staff"]');
                    const staffSelect = staffGroup ? staffGroup.querySelector('select') : null;

                    if (staffSelect || tries > 20) {
                        if (staffSelect) {
                            <?php if ( $provider_id === 46908 ) : ?>
                            // Preselect Rosanna for Dr. Upchurch's patients
                            const preselectedStaffId = "11";
                            staffSelect.value = preselectedStaffId;
                            staffSelect.dispatchEvent(new Event('change', { bubbles: true }));
                            <?php endif; ?>
                        }

                        // Hide dropdown initially
                        if (staffGroup && window.getComputedStyle(staffGroup).display !== 'none') {
                            staffGroup.style.display = 'none';
                        }
                        clearInterval(interval);
                    }

                    tries++;
                }, 250);

                // Keep hiding it on DOM changes (e.g. step changes)
                const observer = new MutationObserver(() => {
                    const staffGroup = document.querySelector('.bookly-form-group[data-type="staff"]');
                    if (staffGroup && window.getComputedStyle(staffGroup).display !== 'none') {
                        staffGroup.style.display = 'none';
                    }
                });

                const formContainer = document.querySelector('.bookly-form');
                if (formContainer) {
                    observer.observe(formContainer, {
                        childList: true,
                        subtree: true,
                        attributes: true,
                    });
                }
            });
        </script>
        <?php
    }
});

if (!function_exists('custom_log')) {
    function custom_log($message)
    {
        if (is_array($message) || is_object($message)) {
            $message = print_r($message, true);
        }
        $log_file = __DIR__ . '/functions_debug.log';
        file_put_contents($log_file, date('Y-m-d H:i:s') . ' - ' . $message . PHP_EOL, FILE_APPEND);
    }
}


add_action( 'wp', 'remove_wc_memberships_product_message', 15 );
function remove_wc_memberships_product_message() {
    remove_action( 'woocommerce_before_single_product', 'wc_memberships_show_restricted_product_message' );
}


add_action( 'user_registration_form_bottom', 'ur_add_redirect_to_field' );
function ur_add_redirect_to_field() {
    if ( isset( $_GET['redirect_to'] ) ) {
        $redirect_to = esc_url( $_GET['redirect_to'] );
        echo '<input type="hidden" name="redirect_to" value="' . esc_attr( $redirect_to ) . '">';
    }
}

add_action( 'user_register', 'ur_redirect_after_registration', 100 );
function ur_redirect_after_registration( $user_id ) {
    if ( isset( $_POST['redirect_to'] ) && ! empty( $_POST['redirect_to'] ) ) {
        $redirect_url = esc_url_raw( $_POST['redirect_to'] );
        setcookie( 'ur_redirect_after_register', $redirect_url, time() + 300, COOKIEPATH, COOKIE_DOMAIN, is_ssl(), true );
    }
}

add_action( 'template_redirect', 'ur_handle_post_registration_redirect' );
function ur_handle_post_registration_redirect() {
    if ( is_user_logged_in() && isset( $_COOKIE['ur_redirect_after_register'] ) ) {
        $redirect_url = esc_url_raw( $_COOKIE['ur_redirect_after_register'] );
        setcookie( 'ur_redirect_after_register', '', time() - 3600, COOKIEPATH, COOKIE_DOMAIN );
        wp_safe_redirect( $redirect_url );
        exit;
    }
}

add_action( 'template_redirect', 'wc_membership_login_redirect_to_product' );
function wc_membership_login_redirect_to_product() {
    if ( is_user_logged_in() && isset( $_GET['redirect_to'] ) && is_account_page() ) {
        wp_safe_redirect( esc_url_raw( $_GET['redirect_to'] ) );
        exit;
    }
}

//Redirect after registration from product page

add_action('template_redirect', function () {
    if (is_singular('product')) {
        ob_start(function ($html) {
            $current_url = get_permalink();

            return preg_replace_callback(
                    '#<a([^>]+)href=["\']([^"\']*/registration)([^"\']*)["\']#i',
                    function ($matches) use ($current_url) {
                        $before_href = $matches[1];
                        $base = $matches[2];
                        $after_href = $matches[3];

                        if (strpos($after_href, 'redirect_to=') !== false) {
                            return $matches[0];
                        }

                        $delimiter = (strpos($after_href, '?') !== false || str_starts_with($after_href, '?')) ? '&' : '?';
                        $new_href = $base . $after_href . $delimiter . 'redirect_to=' . urlencode($current_url);

                        return "<a{$before_href}href=\"{$new_href}\"";
                    },
                    $html
            );
        });
    }
});

add_action('init', function () {
    if (isset($_GET['redirect_to'])) {
        $url = esc_url_raw($_GET['redirect_to']);
        setcookie('user_reg_redirect_to', $url, time() + 300, COOKIEPATH, COOKIE_DOMAIN, is_ssl());
    }
});

add_action('template_redirect', function () {
    if (is_user_logged_in() && !is_admin()) {
        $current_url = home_url(add_query_arg([], $_SERVER['REQUEST_URI']));

        if (!empty($_COOKIE['user_reg_redirect_to'])) {
            $target_url = esc_url_raw($_COOKIE['user_reg_redirect_to']);

            setcookie('user_reg_redirect_to', '', time() - 3600, COOKIEPATH, COOKIE_DOMAIN);

            if (!isset($_GET['redirected']) && strpos($current_url, $target_url) !== 0) {
                $redirect_url = add_query_arg([
                        'reg' => 'new',
                        'redirected' => 1,
                ], $target_url);

                wp_redirect($redirect_url);
                exit;
            }
        }
    }
});

add_action('wp_enqueue_scripts', function () {
    if (is_admin()) return;

    wp_register_script('nexus-iframe-listener', false, [], null, true);
    wp_enqueue_script('nexus-iframe-listener', '');
    wp_add_inline_script('nexus-iframe-listener', <<<JS
if (window.visualViewport) {
  let prevHeight = window.visualViewport.height;

  window.visualViewport.addEventListener('resize', () => {
    const newHeight = window.visualViewport.height;
    const diff = newHeight - prevHeight;

    if (diff > 100) {
        let el = jQuery('.tab-content.active .nexus-iframe-container');
        if (!el.length) {
            el = jQuery('.nexus-iframe-container');
        }
        if (el.length) {
            const elBottom = el.offset().top + el.outerHeight() - 50;
            const target = elBottom - jQuery(window).height();
            jQuery('html, body').animate({ scrollTop: Math.max(0, target) }, 500);
        }
    }

    prevHeight = newHeight;
  });
}
JS
    );
});

add_action('wp_footer', function () {
    ?>
    <script>
        (function () {
            const url = new URL(window.location.href);
            if (url.searchParams.has('redirected')) {
                url.searchParams.delete('redirected');
                window.history.replaceState({}, document.title, url.toString());
            }
        })();
    </script>
    <?php
});

//VV-235 - Converting field names to their original Labels on the Checkout page for AI API usage
add_filter('woocommerce_webhook_payload', 'vv_rename_woocommerce_custom_meta_fields', 10, 4);

function vv_rename_woocommerce_custom_meta_fields($payload, $resource, $resource_id, $webhook_id) {
    if ($resource !== 'order') {
        return $payload;
    }

    // Map normalized keys (no leading underscore) to human labels
    $map = [
            'additional_wooccm0' => 'I acknowledge that I am paying for concierge medical services and that if I choose to use the default pharmacy the cost of medications is included in this price. All sales are final unless I am found to be medically ineligible to receive a therapy by my Valhalla Vitality provider',
            'additional_wooccm1' => 'I understand that Valhalla Vitality will be sending my medication prescription to a licensed compounding pharmacy. The typical manufacturing and processing time for my medications will be at least 10 business days before shipping',
            'additional_wooccm2' => 'Are you currently taking any medications similar to or the same as those included in any of the therapies you are ordering today?',
            'additional_wooccm3' => 'Please enter what medication are you taking, and what your current dose is',
        // TODO: add the label for 4 if you need it:
        // 'additional_wooccm4' => '<< ORIGINAL LABEL FOR 4 >>',
            'additional_wooccm5' => 'Are you experiencing the benefits of treatment as expected at the current dose? (Please provide as much detail as possible)',
            'additional_wooccm6' => 'Are you experiencing any side effects at the current dose? (If so please explain the side effects with detail)',
            'consultation_type'  => 'Consultation Type',
            'consultationtype'   => 'Consultation Type', // just in case a camelCase key appears
    ];

    // Rename keys in a meta_data array (order-level or line-item level)
    $rename_meta = static function (&$meta_array) use ($map) {
        if (!is_array($meta_array)) return;
        foreach ($meta_array as &$m) {
            if (!isset($m['key'])) continue;
            $normalized = ltrim(strtolower($m['key']), '_'); // remove any leading underscores
            if (isset($map[$normalized])) {
                $m['key'] = $map[$normalized];
                // If present, keep display_key aligned as well
                if (isset($m['display_key'])) {
                    $m['display_key'] = $map[$normalized];
                }
            }
        }
        unset($m);
    };

    // Order-level meta
    if (!empty($payload['meta_data'])) {
        $rename_meta($payload['meta_data']);
    }

    // Line items meta (if any custom fields ended up there)
    if (!empty($payload['line_items']) && is_array($payload['line_items'])) {
        foreach ($payload['line_items'] as &$item) {
            if (!empty($item['meta_data'])) {
                $rename_meta($item['meta_data']);
            }
        }
        unset($item);
    }

    return $payload;
}

// Nexus Integration Shortcode | START
require_once "nexus-integration/nexus-integration.php";
// Nexus Integration Shortcode | END

//========Registartion form for unlogged users in a product page=======
require_once get_stylesheet_directory() . '/inc/reg-form-product-page.php';

//========Save last viewed link=======
require_once get_stylesheet_directory() . '/inc/last-viewed-product.php';

//========Redirect after login=======
require_once get_stylesheet_directory() . '/inc/redirect-after-login.php';

//========Woocommerce variable products quantity=======
require_once get_stylesheet_directory() . '/inc/variable-products-quantity.php';

//======== Redirect after registration from GLP page=======
require_once get_stylesheet_directory() . '/inc/glp-registration-redirect.php';

//======== My Account Reviews Section Hide =======
require_once get_stylesheet_directory() . '/inc/my-account-reviews-hide.php';

//======== Package rates badges =======
require_once get_stylesheet_directory() . '/inc/package-rates-badges.php';

//======== Woocommerce free shipping =======
require_once get_stylesheet_directory() . '/inc/woocommerce-free-shipping.php';

//======== Woocommerce shipping chosen method =======
require_once get_stylesheet_directory() . '/inc/woocommerce-shipping-chosen-method.php';

//======== Package rates badges in checkout =======
require_once get_stylesheet_directory() . '/inc/checkout-package-rates-badges.php';

//======== Show search by products tag for registered users only =======
require_once get_stylesheet_directory() . '/inc/tag-category.php';

//======== Update menu for some roles =======
require_once get_stylesheet_directory() . '/inc/roles-menu.php';

//======== Customize related products  =======
require_once get_stylesheet_directory() . '/inc/related-products.php';

//======== Order received page CTA  =======
require_once get_stylesheet_directory() . '/inc/order-received-cta-add.php';

//======== end user's tier to Active Campaign  =======
require_once get_stylesheet_directory() . '/inc/active-campaign-tier.php';

//======== Show tier note in a discounted price  =======
require_once get_stylesheet_directory() . '/inc/woocommerce-discounted-price.php';

//======== HubSpot Form  =======
//require_once get_stylesheet_directory() . '/inc/hubspot-form.php';

//======== Roles update allow  =======
//require_once get_stylesheet_directory() . '/inc/roles-update-allow.php';

//======== Supplements discount for therapies =======
//require_once get_stylesheet_directory() . '/inc/supplements-discount.php';



//======== Coupon  =======
require_once get_stylesheet_directory() . '/inc/coupon.php';
