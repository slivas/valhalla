<?php
/** Add preloader */

add_action('woocommerce_account_points_endpoint', 'add_preloader_to_points');
function add_preloader_to_points()
{
    echo '<style>.points-page .woocommerce-MyAccount-content {position: relative;overflow: hidden;</style>';
    echo '<div class="preloader" style="position: absolute; top: 0; left:0; height: 100%; width: 100%; background-color: #FAF8F6; z-index: 10;"></div>';
}

//Auto upgrade user
add_action('template_redirect', 'custom_account_page_functionality', 1);
function custom_account_page_functionality()
{
    if (is_user_logged_in() && is_page('my-account')) {
        $user_id = get_current_user_id();
        $my_role = !empty(get_user_meta($user_id, 'membership_level', true)) ? get_user_meta($user_id, 'membership_level', true) : '';
        $get_points = (int)get_user_meta($user_id, 'wps_wpr_points', true);
        $get_points = !empty($get_points) ? $get_points : 0;
        $wps_wpr_overall__accumulated_points = get_user_meta($user_id, 'wps_wpr_overall__accumulated_points', true);
        $wps_wpr_overall__accumulated_points = !empty($wps_wpr_overall__accumulated_points) ? $wps_wpr_overall__accumulated_points : 0;
        $membership_settings_array = get_option('wps_wpr_membership_settings', true);
        $wps_wpr_membership_roles = isset($membership_settings_array['membership_roles']) && !empty($membership_settings_array['membership_roles']) ? $membership_settings_array['membership_roles'] : array();
        $current_user_membership_level_points = 0;
        if (isset($wps_wpr_membership_roles[$my_role]['Points'])) {
            $current_user_membership_level_points = $wps_wpr_membership_roles[$my_role]['Points'];
        }
        $current_user_earned_points = $wps_wpr_overall__accumulated_points;

        // Automatically upgrade user
        foreach ($wps_wpr_membership_roles as $role => $values) {
            if ($current_user_earned_points >= $values['Points']) {
                if ($current_user_membership_level_points < $values['Points']) {
                    $selected_role = $role;
                    if (isset($values['Exp_Number']) && !empty($values['Exp_Number']) && isset($values['Exp_Days']) && !empty($values['Exp_Days'])) {
                        $expiration_date = date_i18n('Y-m-d', strtotime('+' . $values['Exp_Number'] . ' ' . $values['Exp_Days']));
                    }
                    $points_for_new_membership = $values['Points'];
                }
            }
        }

        if (isset($selected_role) && $selected_role != '') {
            $user_auto_upgrade = true;
            $upgraded_membership = $selected_role;
            update_user_meta($user_id, 'membership_level', $selected_role);
            if (isset($expiration_date)) {
                update_user_meta($user_id, 'membership_expiration', $expiration_date);
                update_user_meta($user_id, 'membership_start_date', date('Y-m-d'));
            }
            // Send mail (assumed functionality, ensure this function exists or is defined correctly)
            $user = get_user_by('ID', $user_id);
            $wps_wpr_shortcode = array(
                '[USERLEVEL]' => $selected_role,
                '[USERNAME]' => $user->user_login,
            );

            $wps_wpr_subject_content = array(
                'wps_wpr_subject' => 'wps_wpr_membership_email_subject',
                'wps_wpr_content' => 'wps_wpr_membership_email_description_custom_id',
            );
            $wps_wpr_notificatin_array = get_option('wps_wpr_notificatin_array', true);
            /*check if not empty the notification array*/
            if (!empty($wps_wpr_notificatin_array) && is_array($wps_wpr_notificatin_array)) {
                /*Get the Email Subject*/
                $subject_id = $wps_wpr_subject_content['wps_wpr_subject'];
                $description_id = $wps_wpr_subject_content['wps_wpr_content'];
                $wps_wpr_email_subject = isset($wps_wpr_notificatin_array[$subject_id]) ? $wps_wpr_notificatin_array[$subject_id] : '';
                /*Get the Email Description*/
                $wps_wpr_email_discription = isset($wps_wpr_notificatin_array[$description_id]) ? $wps_wpr_notificatin_array[$description_id] : '';
                /*Replace the shortcode in the woocommerce*/
                if (!empty($shortcode) && is_array($shortcode)) {
                    foreach ($shortcode as $key => $value) {
                        $wps_wpr_email_discription = str_replace($key, $value, $wps_wpr_email_discription);
                    }
                }

                $user_id = get_current_user_id();

                /*Get the Email Subject*/
                $subject_id = $wps_wpr_subject_content['wps_wpr_subject'];
                $description_id = $wps_wpr_subject_content['wps_wpr_content'];
                $wps_wpr_email_subject = isset($wps_wpr_notificatin_array[$subject_id]) ? $wps_wpr_notificatin_array[$subject_id] : '';
                /*Get the Email Description*/
                $wps_wpr_email_discription = isset($wps_wpr_notificatin_array[$description_id]) ? $wps_wpr_notificatin_array[$description_id] : '';
                /*Replace the shortcode in the woocommerce*/
                if (!empty($shortcode) && is_array($shortcode)) {
                    foreach ($shortcode as $key => $value) {
                        $wps_wpr_email_discription = str_replace($key, $value, $wps_wpr_email_discription);
                    }
                }

                /*Send the email to user related to the signup*/
                $user = wp_get_current_user();
                $logged_in_user_email = $user->user_email;

                $message = '<span style="line-height:1.5!important;">Your Valhalla Rewards Tier has been Upgraded to ' . $upgraded_membership . '. Now you will get automatic discounts on all purchases for an entire year!</span>';
                //$email_sent = send_custom_email($logged_in_user_email, $email_body);

                $apiKey = 'pk_15c9289094b66891cd415011695f98749e';
                // Build the API endpoint URL with query parameters
                $url = 'https://a.klaviyo.com/api/profiles/?filter=equals(email,"' . $logged_in_user_email . '")';

                // Initialize cURL
                $ch = curl_init();

                // Set cURL options
                curl_setopt($ch, CURLOPT_URL, $url);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_HTTPHEADER, [
                    'accept: application/json',
                    'revision: 2024-02-15',
                    'Authorization: Klaviyo-API-Key ' . $apiKey
                ]);

                // Execute the request
                $response = curl_exec($ch);
                curl_close($ch);

                // Decode the JSON response
                $profiles = json_decode($response, true);
                if (isset($profiles['data'])) {
                    foreach ($profiles['data'] as $profile) {
                        $profileId = $profile['id'];
                        $jayParsedAry = [
                            "data" => [
                                "type" => "profile",
                                "id" => $profileId,
                                "attributes" => [
                                    "properties" => [
                                        "membership_tier" => $upgraded_membership
                                    ]
                                ]
                            ]
                        ];
                        $payload = json_encode($jayParsedAry);
                        $url = "https://a.klaviyo.com/api/profiles/" . $profileId;
                        $ch = curl_init();
                        curl_setopt($ch, CURLOPT_URL, $url);
                        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                        curl_setopt($ch, CURLOPT_HTTPHEADER, [
                            'Content-Type: application/json',
                            'Authorization: Klaviyo-API-Key ' . $apiKey,
                            'revision: 2024-02-15'
                        ]);
                        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PATCH'); // Note the PUT method
                        curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
                        $response = curl_exec($ch);
                        curl_close($ch);
                        // Check for success or handle errors
                        if (curl_getinfo($ch, CURLINFO_HTTP_CODE) === 200) {
                            //echo "Profile updated successfully!\n";
                            $jayParsedAry = [
                                "data" => [
                                    "type" => "event",
                                    "attributes" => [
                                        'properties' => [
                                            'VIP Membership Tier Unlocked' => '1'
                                        ],
                                        "metric" => [
                                            "data" => [
                                                "type" => "metric",
                                                "attributes" => [
                                                    "name" => "VIP Rewards Membership Upgrade"
                                                ]
                                            ]
                                        ],
                                        "profile" => [
                                            "data" => [
                                                "type" => "profile",
                                                "id" => $profileId
                                            ]
                                        ]
                                    ]
                                ]
                            ];
                            $payload = json_encode($jayParsedAry);
                            $url = "https://a.klaviyo.com/api/events/";
                            curl_setopt($ch, CURLOPT_URL, $url);
                            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                                'Content-Type: application/json',
                                'Authorization: Klaviyo-API-Key ' . $apiKey,
                                'revision: 2024-02-15'
                            ]);
                            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST'); // Note the PUT method
                            curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
                            $response = curl_exec($ch);
                            curl_close($ch);
                            // Check for success or handle errors
                            if (curl_getinfo($ch, CURLINFO_HTTP_CODE) === 200) {
                                //echo 'Trigger Established';
                            } else {
                                //echo "Error updating profile:\n";
                                //var_dump($response);
                            }
                        }
                    }
                }
            }
        }
    }
}

//VIP Membership shortcodes
//Membership level
function vip_membership_name_shortcode()
{
    if (is_user_logged_in()) {
        $user_id = get_current_user_id();

        $membership_level = get_user_meta($user_id, 'membership_level', true);

        return !empty($membership_level) ? esc_html($membership_level) : '';
    }
    return 'User is not logged in.';
}

add_shortcode('VIPMEMBERSHIPNAME', 'vip_membership_name_shortcode');

//Total Points
function vip_points_shortcode()
{
    if (is_user_logged_in()) {
        $user_id = get_current_user_id();

        $get_points = get_user_meta($user_id, 'wps_wpr_points', true);

        return !empty($get_points) ? esc_html($get_points) : '0';
    }
    return 'User is not logged in.';
}

add_shortcode('VIPPOINTS', 'vip_points_shortcode');

function vv_is_membership_active_by_meta(int $user_id): bool
{
    $raw = get_user_meta($user_id, 'membership_expiration', true);
    if (!$raw) return true;

    $exp_ts = is_numeric($raw) ? (int)$raw : strtotime($raw);
    if (!$exp_ts) return true;

    return $exp_ts >= current_time('timestamp');
}

//Valhalla VIP Rewards
function custom_points_page_functionality()
{
    $current_membership_color = 'ffbf37';
    $current_page = trim($_SERVER['REQUEST_URI'], '/');

    if (!function_exists('vv_is_membership_active_by_meta')) {
        function vv_is_membership_active_by_meta(int $user_id): bool {
            $raw = get_user_meta($user_id, 'membership_expiration', true);
            if (!$raw) return true;
            $exp_ts = is_numeric($raw) ? (int)$raw : strtotime($raw);
            if (!$exp_ts) return true;
            return $exp_ts >= current_time('timestamp');
        }
    }

    $on_points_endpoint = (function_exists('is_wc_endpoint_url') && is_wc_endpoint_url('points'))
        || ($current_page === 'my-account/points');

    if (!is_user_logged_in() || !$on_points_endpoint) {
        return;
    }

    $user_id               = get_current_user_id();
    $membership_start_date = get_user_meta($user_id, 'membership_start_date', true);
    $membership_level      = (string) get_user_meta($user_id, 'membership_level', true);
    $membership_exp_date   = (string) get_user_meta($user_id, 'membership_expiration', true);
    $current_points        = (int) get_user_meta($user_id, 'wps_wpr_points', true);

    $membership_settings_array = get_option('wps_wpr_membership_settings', true);
    if (!is_array($membership_settings_array)) $membership_settings_array = [];

    $roles = (!empty($membership_settings_array['membership_roles']) && is_array($membership_settings_array['membership_roles']))
        ? $membership_settings_array['membership_roles']
        : [];

    $membership_array = [];
    foreach ($roles as $memberships => $val) {
        $name   = trim(str_replace('Tier', '', (string) $memberships));
        $points = isset($val['Points'])   ? (int) $val['Points']   : 0;
        $disc   = isset($val['Discount']) ? (int) $val['Discount'] : 0;

        if     ($name === 'Bronze') $color = 'CD7F32';
        elseif ($name === 'Silver') $color = 'C0C0C0';
        elseif ($name === 'Gold')   $color = 'FFD700';
        else                        $color = 'E5E4E2';

        $membership_array[] = [
            'name'     => $name ?: '—',
            'points'   => $points,
            'color'    => $color,
            'discount' => $disc,
            'selected' => '',
        ];
    }

    foreach ($membership_array as &$m) { $m['points'] = (int) $m['points']; }
    unset($m);
    usort($membership_array, fn($a,$b) => $a['points'] <=> $b['points']);

    $norm = static function ($s) {
        $s = (string) $s;
        $s = preg_replace('/\s*tier$/i', '', $s);
        return strtolower(trim($s));
    };
    $name_to_index = [];
    foreach ($membership_array as $i => $m) {
        $name_to_index[ $norm($m['name']) ] = $i;
    }

    $has_active_meta = vv_is_membership_active_by_meta($user_id);

    $current_idx = -1;
    if ($membership_level && $has_active_meta) {
        $key = $norm($membership_level);
        if (isset($name_to_index[$key])) {
            $current_idx = $name_to_index[$key];
        }
    }
    if ($current_idx === -1) {
        foreach ($membership_array as $i => $m) {
            if ($current_points >= $m['points']) $current_idx = $i; else break;
        }
    }

    $current_membership = ($current_idx >= 0) ? $membership_array[$current_idx] : null;
    $next_membership    = isset($membership_array[$current_idx + 1]) ? $membership_array[$current_idx + 1] : null;

    foreach ($membership_array as $i => &$m) {
        $m['selected'] = ($i === $current_idx) ? 'selected' : '';
    }
    unset($m);

    $current_membership_name  = ($has_active_meta && $membership_level)
        ? $membership_level
        : ($current_membership['name'] ?? '');

    $current_membership_color = $current_membership['color'] ?? 'ffbf37';

    if ($next_membership) {
        $floor_points        = (int) ($current_membership['points'] ?? 0);
        $ceil_points         = (int) $next_membership['points'];
        $range               = max(1, $ceil_points - $floor_points);
        $effective_points    = max($floor_points, $current_points); // avoid negative if meta assigned manually
        $percentage_progress = (int) round(($effective_points - $floor_points) / $range * 100);
        $percentage_progress = max(0, min(100, $percentage_progress));
        $progress_max        = $ceil_points;
    } else {
        $percentage_progress = 100;
        $top_points          = $membership_array ? (int) end($membership_array)['points'] : $current_points;
        reset($membership_array);
        $progress_max        = max($current_points, $top_points);
    }

    $membership_exp_date_fmt = '';
    if ($membership_exp_date !== '') {
        $ts = is_numeric($membership_exp_date) ? (int) $membership_exp_date : strtotime($membership_exp_date);
        if ($ts) $membership_exp_date_fmt = date('F j, Y', $ts);
    }

    ?>
    <style>
        progress[value] {
            --color: #<?php echo $current_membership_color; ?>; /* the progress color */
            --background: #F3F3F3; /* the background color */;
            -webkit-appearance: none;
            -moz-appearance: none;
            appearance: none;
            border: none;
            width: 100%;
            margin: 0 10px;
            border-radius: 10em;
            background: var(--background);
        }

        progress[value]::-webkit-progress-bar {
            border-radius: 10em;
            background: var(--background);
        }

        progress[value]::-webkit-progress-value {
            border-radius: 10em;
            background: var(--color);
        }

        progress[value]::-moz-progress-bar {
            border-radius: 10em;
            background: var(--color);
        }

        .VIPmembershipProgress {
            font-size: 20px;
            font-weight: bold;
            display: block;
            margin: 20px 0;
        }

        .val_dash_title {
            color: #010101;
            text-align: center;
            font-family: 'Work Sans';
            font-size: 22px;
            font-style: normal;
            font-weight: 600;
            line-height: normal;
            text-transform: uppercase;
        }

        .val_dash_title_accent {
            color: #ffbf37;
        }

        input.wps_wpr_custom_coupon.button {
            background-color: #c0d1a3 !important;
        }
    </style>
    <script>
        var updatedTable = '<table class="woocommerce-MyAccount-points shop_table my_account_points account-points-table wps_wpr_membership_with_img acctpointtable" style="display: none;"><thead><tr><th class="wps-wpr-points-points"><span class="wps_wpr_nobr">Level</span></th><th class="wps-wpr-points-code"><span class="wps_wpr_nobr">Required Points</span></th><th class="wps-wpr-points-expiry"><span class="wps_wpr_nobr">Membership Benefits</span></th></tr></thead><tbody><?php foreach($membership_array as $membership) : ?><tr><td><?php if($membership['selected'] == 'selected'): ?><svg xmlns="http://www.w3.org/2000/svg" width="25" height="24" fill="none"><g clip-path="url(#a)"><path fill="#04C400" fill-rule="evenodd" d="M19.994 3.133a1 1 0 0 1 1.352.31l.99 1.51a1 1 0 0 1-.155 1.278l-.003.004-.014.013-.057.053-.225.215a83.91 83.91 0 0 0-3.62 3.736c-2.197 2.416-4.806 5.578-6.562 8.646-.49.856-1.687 1.04-2.397.301l-6.485-6.738a1 1 0 0 1 .051-1.436l1.96-1.768A1 1 0 0 1 6.1 9.2l3.31 2.481c5.168-5.097 8.1-7.053 10.585-8.548Zm.21 2.216c-2.29 1.432-5.148 3.51-9.998 8.358A1 1 0 0 1 8.9 13.8l-3.342-2.506-.58.524 5.316 5.526c1.846-3.07 4.387-6.126 6.49-8.438a85.913 85.913 0 0 1 3.425-3.552l-.004-.005Z" clip-rule="evenodd"/></g><defs><clipPath id="a"><path fill="#fff" d="M.5 0h24v24H.5z"/></clipPath></defs></svg>';
        var updatedTable = updatedTable + '<?php endif; ?><?php echo $membership['name']; ?> Tier<br><a class="wps_wpr_level_benefits" data-id="<?php echo strtolower(str_replace(' ', '', $membership['name'])); ?>_tier" href="javascript:void(0);" onclick="jQuery(\'#wps_wpr_popup_wrapper_<?php echo strtolower(str_replace(' ', '', $membership['name'])); ?>_tier\').show();">View Benefits</a></td><td><?php echo $membership['points']; ?></td><td><?php echo $membership['discount']; ?>% Discount on Therapies &amp; Services</td></tr><?php endforeach; ?></tbody></table>';
    </script>
    <script>
        var membership_level = '<?php echo $membership_level; ?>';
        var membership_exp_date = '<?php echo date('F j, Y', strtotime($membership_exp_date)); ?>';
        var membership_array = <?php echo json_encode($membership_array); ?>;
        (function ($) {
            $(document).ready(function () {
                $('.wps_wpr_points_wrapper_with_exp').after(
                    '<div class="VIPMembershipProgress" style="position: relative; margin:50px 0;">' +
                    '<div class="val_dash_title" style="width:100%;">Valhalla vitality <span class="val_dash_title_accent">VIP Membership</span> Progress</div>' +
                    '<div class="currentmembership" value="<?php echo (int)$current_points; ?>" style="position: absolute; bottom:0; left: <?php echo (int)$percentage_progress; ?>%;">&#8613;</div>' +
                    '<div class="currentmembershiptier" style="float:left; text-align:left; font-size:14px; line-height:1">' +
                    '<strong>Current Tier: </strong><br><?php echo esc_html($current_membership_name); ?><br>' +
                    ' (<?php echo (int)$current_points; ?> Points)' +
                    '</div>' +
                    '<div class="nextmembership" style="float:right; text-align:right; font-size:14px; line-height:1">' +
                    '<?php if ( !isset($next_membership["name"]) ) : ?>' +
                    '<strong>Max VIP Tier Unlocked</strong>' +
                    '<?php else : ?>' +
                    '<strong>Next Tier: </strong><br><?php echo esc_html($next_membership["name"]); ?><br>(<?php echo (int)$next_membership["points"]; ?> Points)' +
                    '<?php endif; ?>' +
                    '</div>' +
                    '<progress max="<?php echo (int)$progress_max; ?>" value="<?php echo (int)$current_points; ?>"></progress>' +
                    '</div>'
                );

                jQuery('.wps_wpr_upgrade_level').prev().before('<p class="wps_wpr_heading">Membership Benefits: </p>' +
                    '<div class="membership-benefits__wrapper">\n' +
                    '        <div class="membership-benefits__item">\n' +
                    '            <svg width="81" height="80" viewBox="0 0 81 80" fill="none" xmlns="http://www.w3.org/2000/svg">\n' +
                    '<g clip-path="url(#clip0_880_10830)">\n' +
                    '<mask id="path-1-outside-1_880_10830" maskUnits="userSpaceOnUse" x="0" y="4" width="80" height="72" fill="black">\n' +
                    '<rect fill="white" y="4" width="80" height="72"/>\n' +
                    '<path fill-rule="evenodd" clip-rule="evenodd" d="M61.2907 25.6012C63.8726 27.6188 65.9861 30.1631 67.4878 33.0614C68.9896 35.9597 69.8444 39.1441 69.9944 42.3985C75.2922 41.794 78.5221 36.3018 76.492 31.4464C76.0865 30.4809 75.493 29.604 74.7454 28.8657C74.5721 28.688 74.4762 28.4499 74.4784 28.2029C74.4806 27.9558 74.5806 27.7194 74.757 27.5447C74.9333 27.37 75.1719 27.2709 75.4213 27.2687C75.6707 27.2666 75.911 27.3615 76.0904 27.5332C77.4235 28.8541 78.3497 30.5236 78.7609 32.3465C79.172 34.1695 79.0511 36.0707 78.4123 37.8282C77.7735 39.5856 76.6431 41.1267 75.1532 42.2714C73.6633 43.4162 71.8754 44.1173 69.9983 44.293C69.6621 52.7162 64.5864 60.2099 57.0329 63.8393V74.0576C57.0329 74.3076 56.9327 74.5473 56.7543 74.724C56.5759 74.9007 56.3339 75 56.0817 75H47.0969C46.8446 75 46.6027 74.9007 46.4243 74.724C46.2459 74.5473 46.1457 74.3076 46.1457 74.0576V66.0995H30.1027V74.0576C30.1027 74.3076 30.0025 74.5473 29.8241 74.724C29.6457 74.9007 29.4038 75 29.1515 75H20.1672C19.915 75 19.673 74.9007 19.4946 74.724C19.3162 74.5473 19.216 74.3076 19.216 74.0576V63.8393C13.7969 61.228 9.61914 56.6261 7.57071 51.0115H1.95122C1.69894 51.0115 1.45699 50.9122 1.27861 50.7354C1.10022 50.5587 1 50.319 1 50.0691V36.7188C1 36.4689 1.10022 36.2292 1.27861 36.0525C1.45699 35.8757 1.69894 35.7764 1.95122 35.7764H7.57071C8.75222 32.5262 10.663 29.584 13.159 27.1716C11.6336 23.706 11.1323 19.8826 11.7133 16.1459C11.744 15.9432 11.8407 15.7559 11.9886 15.6125C12.1365 15.4691 12.3275 15.3774 12.5328 15.3513C15.3195 14.9268 18.1653 15.0872 20.8855 15.8221C23.6056 16.5571 26.1396 17.8501 28.323 19.6174C28.8198 15.5854 30.7888 11.8728 33.8585 9.18002C36.9282 6.48724 40.8865 5.00041 44.9866 5C55.8979 5 63.8799 15.1537 61.2907 25.6012ZM18.2247 33.6428C17.6127 33.6427 17.0145 33.8225 16.5056 34.1593C15.9968 34.4961 15.6001 34.9749 15.3659 35.535C15.1317 36.0951 15.0704 36.7115 15.1898 37.3061C15.3092 37.9008 15.6039 38.447 16.0366 38.8757C16.4694 39.3044 17.0207 39.5963 17.6209 39.7146C18.2212 39.8329 18.8433 39.7722 19.4087 39.5401C19.9741 39.3081 20.4573 38.9152 20.7973 38.4111C21.1373 37.9069 21.3187 37.3143 21.3187 36.708C21.3185 35.8951 20.9925 35.1155 20.4123 34.5407C19.8321 33.9659 19.0452 33.6429 18.2247 33.6428ZM24.0233 55.8274C29.4097 58.224 35.5283 58.4397 41.073 56.4287C45.3792 54.8854 49.9591 51.8739 54.4327 46.5809V50.431C54.4327 50.6809 54.5329 50.9206 54.7113 51.0973C54.8897 51.274 55.1317 51.3733 55.3839 51.3733C55.6362 51.3733 55.8782 51.274 56.0566 51.0973C56.2349 50.9206 56.3352 50.6809 56.3352 50.431V43.8713C56.3354 43.7253 56.3013 43.5814 56.2357 43.4507C56.1701 43.3201 56.0747 43.2064 55.957 43.1186C55.8393 43.0308 55.7027 42.9713 55.5578 42.9449C55.413 42.9184 55.2639 42.9258 55.1224 42.9663L48.8443 44.4015C48.7228 44.429 48.6079 44.48 48.5062 44.5515C48.4046 44.623 48.3181 44.7136 48.2518 44.8182C48.1855 44.9228 48.1406 45.0393 48.1198 45.1611C48.0989 45.2829 48.1025 45.4075 48.1303 45.528C48.158 45.6484 48.2095 45.7622 48.2816 45.8629C48.3538 45.9636 48.4453 46.0493 48.5509 46.115C48.6565 46.1806 48.7741 46.2251 48.897 46.2457C49.0199 46.2664 49.1457 46.2629 49.2673 46.2354L52.9602 45.391C48.73 50.3904 44.4398 53.2246 40.4273 54.6622C35.3465 56.5056 29.7396 56.3083 24.8036 54.1125C24.5743 54.0116 24.3139 54.0047 24.0795 54.0934C23.845 54.1821 23.6555 54.3591 23.5524 54.5858C23.4493 54.8124 23.441 55.0703 23.5292 55.3031C23.6174 55.5359 23.7951 55.7241 24.0233 55.8274ZM48.5299 19.5838C48.5299 18.1232 47.3425 17.0331 45.9379 16.7043V15.4914C45.9379 15.2415 45.8376 15.0018 45.6592 14.8251C45.4809 14.6483 45.2389 14.5491 44.9866 14.5491C44.7344 14.5491 44.4924 14.6483 44.314 14.8251C44.1356 15.0018 44.0354 15.2415 44.0354 15.4914V16.7043C42.6308 17.0331 41.4435 18.1239 41.4435 19.5838C41.4435 23.5575 46.6277 21.767 46.6277 23.6764C46.6194 23.8169 46.5793 23.9538 46.5103 24.0769C46.4412 24.1999 46.3451 24.306 46.2289 24.3871C45.1892 25.2079 43.3461 24.6397 43.3461 23.6764C43.3461 23.4265 43.2459 23.1868 43.0675 23.01C42.8891 22.8333 42.6472 22.734 42.3949 22.734C42.1426 22.734 41.9007 22.8333 41.7223 23.01C41.5439 23.1868 41.4437 23.4265 41.4437 23.6764C41.4437 25.1371 42.6311 26.2272 44.0356 26.556V27.769C44.0356 28.0189 44.1358 28.2586 44.3142 28.4353C44.4926 28.612 44.7345 28.7113 44.9868 28.7113C45.2391 28.7113 45.481 28.612 45.6594 28.4353C45.8378 28.2586 45.938 28.0189 45.938 27.769V26.5555C47.3426 26.2268 48.5301 25.136 48.5301 23.6759C48.5301 19.7023 43.3459 21.4928 43.3459 19.5834C43.3542 19.4428 43.3944 19.3059 43.4634 19.1829C43.5324 19.0599 43.6286 18.9538 43.7447 18.8727C44.7843 18.0518 46.6275 18.6203 46.6275 19.5834C46.6275 19.8333 46.7277 20.073 46.9061 20.2497C47.0845 20.4264 47.3264 20.5257 47.5787 20.5257C47.831 20.5257 48.0729 20.4264 48.2513 20.2497C48.4297 20.073 48.5299 19.8338 48.5299 19.5838ZM44.9866 9.55527C47.625 9.55548 50.1922 10.4038 52.3024 11.9728C54.4126 13.5418 55.952 15.7468 56.6893 18.2565C57.4266 20.7662 57.322 23.4452 56.3913 25.891C55.4606 28.3369 53.7539 30.4175 51.5277 31.8204H55.7473C57.7616 29.7324 59.1126 27.1045 59.6327 24.2626C60.1529 21.4208 59.8192 18.4902 58.6732 15.8346C57.5271 13.1789 55.6192 10.9151 53.1861 9.32414C50.753 7.73317 47.9019 6.88505 44.9866 6.88505C42.0713 6.88505 39.2201 7.73317 36.787 9.32414C34.3539 10.9151 32.446 13.1789 31.2999 15.8346C30.1539 18.4902 29.8202 21.4208 30.3404 24.2626C30.8605 27.1045 32.2115 29.7324 34.2258 31.8204H38.4456C31.8822 27.6768 30.8434 18.5654 36.3687 13.092C37.4993 11.969 38.8426 11.0785 40.3217 10.4716C41.8007 9.86461 43.3857 9.55322 44.9866 9.55527ZM52.2598 14.4242C50.8213 12.9991 48.9885 12.0285 46.9932 11.6353C44.9979 11.2421 42.9298 11.444 41.0502 12.2152C39.1707 12.9865 37.5643 14.2926 36.434 15.9684C35.3038 17.6442 34.7005 19.6144 34.7005 21.6299C34.7005 23.6453 35.3038 25.6155 36.434 27.2913C37.5643 28.9671 39.1707 30.2732 41.0502 31.0445C42.9298 31.8158 44.9979 32.0176 46.9932 31.6244C48.9885 31.2312 50.8213 30.2607 52.2598 28.8356C54.1888 26.9245 55.2724 24.3325 55.2724 21.6299C55.2724 18.9272 54.1888 16.3353 52.2598 14.4242ZM28.2051 22.0305C26.2709 20.1885 23.9494 18.7933 21.4068 17.9448C18.8643 17.0964 16.1638 16.8158 13.4989 17.1231C13.11 20.4798 13.6752 23.8774 15.1307 26.9329C15.2351 27.1176 15.2736 27.3317 15.2399 27.5407C15.2063 27.7497 15.1025 27.9413 14.9454 28.0847C13.6121 29.3004 12.441 30.6798 11.4604 32.1895C10.4958 33.6744 9.72623 35.2752 9.17035 36.953C9.11787 37.1556 8.99883 37.3352 8.83197 37.4634C8.66512 37.5917 8.45994 37.6612 8.24877 37.6612H2.90244V49.1267H8.24877V49.1283C8.44938 49.1282 8.64486 49.191 8.80719 49.3078C8.96953 49.4245 9.09039 49.5892 9.15243 49.7782C10.0631 52.5401 11.5427 55.0846 13.4974 57.2507C15.4522 59.4168 17.8395 61.1572 20.5084 62.3619C20.6881 62.4301 20.8426 62.5506 20.9517 62.7077C21.0607 62.8647 21.1192 63.0508 21.1193 63.2414V73.1146H28.2011V65.1571C28.2011 64.9072 28.3013 64.6675 28.4797 64.4907C28.6581 64.314 28.9 64.2147 29.1523 64.2147H47.0969C47.3492 64.2147 47.5911 64.314 47.7695 64.4907C47.9479 64.6675 48.0481 64.9072 48.0481 65.1571V73.1153H55.13V63.2419H55.1314C55.1314 63.0607 55.1843 62.8834 55.2835 62.7312C55.3828 62.5791 55.5243 62.4586 55.691 62.3842C66.5394 57.5347 71.2706 44.6892 65.8903 34.0973C64.6135 31.5875 62.8424 29.3564 60.6814 27.5354C60.09 29.0754 59.2704 30.5196 58.2495 31.8204H60.8218C61.074 31.8204 61.316 31.9197 61.4944 32.0964C61.6728 32.2731 61.773 32.5128 61.773 32.7627C61.773 33.0127 61.6728 33.2524 61.4944 33.4291C61.316 33.6058 61.074 33.7051 60.8218 33.7051H29.1515C28.8992 33.7051 28.6573 33.6058 28.4789 33.4291C28.3005 33.2524 28.2003 33.0127 28.2003 32.7627C28.2003 32.5128 28.3005 32.2731 28.4789 32.0964C28.6573 31.9197 28.8992 31.8204 29.1515 31.8204H31.7236C29.5208 29.0158 28.2871 25.5832 28.2051 22.0305Z"/>\n' +
                    '</mask>\n' +
                    '<path fill-rule="evenodd" clip-rule="evenodd" d="M61.2907 25.6012C63.8726 27.6188 65.9861 30.1631 67.4878 33.0614C68.9896 35.9597 69.8444 39.1441 69.9944 42.3985C75.2922 41.794 78.5221 36.3018 76.492 31.4464C76.0865 30.4809 75.493 29.604 74.7454 28.8657C74.5721 28.688 74.4762 28.4499 74.4784 28.2029C74.4806 27.9558 74.5806 27.7194 74.757 27.5447C74.9333 27.37 75.1719 27.2709 75.4213 27.2687C75.6707 27.2666 75.911 27.3615 76.0904 27.5332C77.4235 28.8541 78.3497 30.5236 78.7609 32.3465C79.172 34.1695 79.0511 36.0707 78.4123 37.8282C77.7735 39.5856 76.6431 41.1267 75.1532 42.2714C73.6633 43.4162 71.8754 44.1173 69.9983 44.293C69.6621 52.7162 64.5864 60.2099 57.0329 63.8393V74.0576C57.0329 74.3076 56.9327 74.5473 56.7543 74.724C56.5759 74.9007 56.3339 75 56.0817 75H47.0969C46.8446 75 46.6027 74.9007 46.4243 74.724C46.2459 74.5473 46.1457 74.3076 46.1457 74.0576V66.0995H30.1027V74.0576C30.1027 74.3076 30.0025 74.5473 29.8241 74.724C29.6457 74.9007 29.4038 75 29.1515 75H20.1672C19.915 75 19.673 74.9007 19.4946 74.724C19.3162 74.5473 19.216 74.3076 19.216 74.0576V63.8393C13.7969 61.228 9.61914 56.6261 7.57071 51.0115H1.95122C1.69894 51.0115 1.45699 50.9122 1.27861 50.7354C1.10022 50.5587 1 50.319 1 50.0691V36.7188C1 36.4689 1.10022 36.2292 1.27861 36.0525C1.45699 35.8757 1.69894 35.7764 1.95122 35.7764H7.57071C8.75222 32.5262 10.663 29.584 13.159 27.1716C11.6336 23.706 11.1323 19.8826 11.7133 16.1459C11.744 15.9432 11.8407 15.7559 11.9886 15.6125C12.1365 15.4691 12.3275 15.3774 12.5328 15.3513C15.3195 14.9268 18.1653 15.0872 20.8855 15.8221C23.6056 16.5571 26.1396 17.8501 28.323 19.6174C28.8198 15.5854 30.7888 11.8728 33.8585 9.18002C36.9282 6.48724 40.8865 5.00041 44.9866 5C55.8979 5 63.8799 15.1537 61.2907 25.6012ZM18.2247 33.6428C17.6127 33.6427 17.0145 33.8225 16.5056 34.1593C15.9968 34.4961 15.6001 34.9749 15.3659 35.535C15.1317 36.0951 15.0704 36.7115 15.1898 37.3061C15.3092 37.9008 15.6039 38.447 16.0366 38.8757C16.4694 39.3044 17.0207 39.5963 17.6209 39.7146C18.2212 39.8329 18.8433 39.7722 19.4087 39.5401C19.9741 39.3081 20.4573 38.9152 20.7973 38.4111C21.1373 37.9069 21.3187 37.3143 21.3187 36.708C21.3185 35.8951 20.9925 35.1155 20.4123 34.5407C19.8321 33.9659 19.0452 33.6429 18.2247 33.6428ZM24.0233 55.8274C29.4097 58.224 35.5283 58.4397 41.073 56.4287C45.3792 54.8854 49.9591 51.8739 54.4327 46.5809V50.431C54.4327 50.6809 54.5329 50.9206 54.7113 51.0973C54.8897 51.274 55.1317 51.3733 55.3839 51.3733C55.6362 51.3733 55.8782 51.274 56.0566 51.0973C56.2349 50.9206 56.3352 50.6809 56.3352 50.431V43.8713C56.3354 43.7253 56.3013 43.5814 56.2357 43.4507C56.1701 43.3201 56.0747 43.2064 55.957 43.1186C55.8393 43.0308 55.7027 42.9713 55.5578 42.9449C55.413 42.9184 55.2639 42.9258 55.1224 42.9663L48.8443 44.4015C48.7228 44.429 48.6079 44.48 48.5062 44.5515C48.4046 44.623 48.3181 44.7136 48.2518 44.8182C48.1855 44.9228 48.1406 45.0393 48.1198 45.1611C48.0989 45.2829 48.1025 45.4075 48.1303 45.528C48.158 45.6484 48.2095 45.7622 48.2816 45.8629C48.3538 45.9636 48.4453 46.0493 48.5509 46.115C48.6565 46.1806 48.7741 46.2251 48.897 46.2457C49.0199 46.2664 49.1457 46.2629 49.2673 46.2354L52.9602 45.391C48.73 50.3904 44.4398 53.2246 40.4273 54.6622C35.3465 56.5056 29.7396 56.3083 24.8036 54.1125C24.5743 54.0116 24.3139 54.0047 24.0795 54.0934C23.845 54.1821 23.6555 54.3591 23.5524 54.5858C23.4493 54.8124 23.441 55.0703 23.5292 55.3031C23.6174 55.5359 23.7951 55.7241 24.0233 55.8274ZM48.5299 19.5838C48.5299 18.1232 47.3425 17.0331 45.9379 16.7043V15.4914C45.9379 15.2415 45.8376 15.0018 45.6592 14.8251C45.4809 14.6483 45.2389 14.5491 44.9866 14.5491C44.7344 14.5491 44.4924 14.6483 44.314 14.8251C44.1356 15.0018 44.0354 15.2415 44.0354 15.4914V16.7043C42.6308 17.0331 41.4435 18.1239 41.4435 19.5838C41.4435 23.5575 46.6277 21.767 46.6277 23.6764C46.6194 23.8169 46.5793 23.9538 46.5103 24.0769C46.4412 24.1999 46.3451 24.306 46.2289 24.3871C45.1892 25.2079 43.3461 24.6397 43.3461 23.6764C43.3461 23.4265 43.2459 23.1868 43.0675 23.01C42.8891 22.8333 42.6472 22.734 42.3949 22.734C42.1426 22.734 41.9007 22.8333 41.7223 23.01C41.5439 23.1868 41.4437 23.4265 41.4437 23.6764C41.4437 25.1371 42.6311 26.2272 44.0356 26.556V27.769C44.0356 28.0189 44.1358 28.2586 44.3142 28.4353C44.4926 28.612 44.7345 28.7113 44.9868 28.7113C45.2391 28.7113 45.481 28.612 45.6594 28.4353C45.8378 28.2586 45.938 28.0189 45.938 27.769V26.5555C47.3426 26.2268 48.5301 25.136 48.5301 23.6759C48.5301 19.7023 43.3459 21.4928 43.3459 19.5834C43.3542 19.4428 43.3944 19.3059 43.4634 19.1829C43.5324 19.0599 43.6286 18.9538 43.7447 18.8727C44.7843 18.0518 46.6275 18.6203 46.6275 19.5834C46.6275 19.8333 46.7277 20.073 46.9061 20.2497C47.0845 20.4264 47.3264 20.5257 47.5787 20.5257C47.831 20.5257 48.0729 20.4264 48.2513 20.2497C48.4297 20.073 48.5299 19.8338 48.5299 19.5838ZM44.9866 9.55527C47.625 9.55548 50.1922 10.4038 52.3024 11.9728C54.4126 13.5418 55.952 15.7468 56.6893 18.2565C57.4266 20.7662 57.322 23.4452 56.3913 25.891C55.4606 28.3369 53.7539 30.4175 51.5277 31.8204H55.7473C57.7616 29.7324 59.1126 27.1045 59.6327 24.2626C60.1529 21.4208 59.8192 18.4902 58.6732 15.8346C57.5271 13.1789 55.6192 10.9151 53.1861 9.32414C50.753 7.73317 47.9019 6.88505 44.9866 6.88505C42.0713 6.88505 39.2201 7.73317 36.787 9.32414C34.3539 10.9151 32.446 13.1789 31.2999 15.8346C30.1539 18.4902 29.8202 21.4208 30.3404 24.2626C30.8605 27.1045 32.2115 29.7324 34.2258 31.8204H38.4456C31.8822 27.6768 30.8434 18.5654 36.3687 13.092C37.4993 11.969 38.8426 11.0785 40.3217 10.4716C41.8007 9.86461 43.3857 9.55322 44.9866 9.55527ZM52.2598 14.4242C50.8213 12.9991 48.9885 12.0285 46.9932 11.6353C44.9979 11.2421 42.9298 11.444 41.0502 12.2152C39.1707 12.9865 37.5643 14.2926 36.434 15.9684C35.3038 17.6442 34.7005 19.6144 34.7005 21.6299C34.7005 23.6453 35.3038 25.6155 36.434 27.2913C37.5643 28.9671 39.1707 30.2732 41.0502 31.0445C42.9298 31.8158 44.9979 32.0176 46.9932 31.6244C48.9885 31.2312 50.8213 30.2607 52.2598 28.8356C54.1888 26.9245 55.2724 24.3325 55.2724 21.6299C55.2724 18.9272 54.1888 16.3353 52.2598 14.4242ZM28.2051 22.0305C26.2709 20.1885 23.9494 18.7933 21.4068 17.9448C18.8643 17.0964 16.1638 16.8158 13.4989 17.1231C13.11 20.4798 13.6752 23.8774 15.1307 26.9329C15.2351 27.1176 15.2736 27.3317 15.2399 27.5407C15.2063 27.7497 15.1025 27.9413 14.9454 28.0847C13.6121 29.3004 12.441 30.6798 11.4604 32.1895C10.4958 33.6744 9.72623 35.2752 9.17035 36.953C9.11787 37.1556 8.99883 37.3352 8.83197 37.4634C8.66512 37.5917 8.45994 37.6612 8.24877 37.6612H2.90244V49.1267H8.24877V49.1283C8.44938 49.1282 8.64486 49.191 8.80719 49.3078C8.96953 49.4245 9.09039 49.5892 9.15243 49.7782C10.0631 52.5401 11.5427 55.0846 13.4974 57.2507C15.4522 59.4168 17.8395 61.1572 20.5084 62.3619C20.6881 62.4301 20.8426 62.5506 20.9517 62.7077C21.0607 62.8647 21.1192 63.0508 21.1193 63.2414V73.1146H28.2011V65.1571C28.2011 64.9072 28.3013 64.6675 28.4797 64.4907C28.6581 64.314 28.9 64.2147 29.1523 64.2147H47.0969C47.3492 64.2147 47.5911 64.314 47.7695 64.4907C47.9479 64.6675 48.0481 64.9072 48.0481 65.1571V73.1153H55.13V63.2419H55.1314C55.1314 63.0607 55.1843 62.8834 55.2835 62.7312C55.3828 62.5791 55.5243 62.4586 55.691 62.3842C66.5394 57.5347 71.2706 44.6892 65.8903 34.0973C64.6135 31.5875 62.8424 29.3564 60.6814 27.5354C60.09 29.0754 59.2704 30.5196 58.2495 31.8204H60.8218C61.074 31.8204 61.316 31.9197 61.4944 32.0964C61.6728 32.2731 61.773 32.5128 61.773 32.7627C61.773 33.0127 61.6728 33.2524 61.4944 33.4291C61.316 33.6058 61.074 33.7051 60.8218 33.7051H29.1515C28.8992 33.7051 28.6573 33.6058 28.4789 33.4291C28.3005 33.2524 28.2003 33.0127 28.2003 32.7627C28.2003 32.5128 28.3005 32.2731 28.4789 32.0964C28.6573 31.9197 28.8992 31.8204 29.1515 31.8204H31.7236C29.5208 29.0158 28.2871 25.5832 28.2051 22.0305Z" fill="#6EAFD0"/>\n' +
                    '<path fill-rule="evenodd" clip-rule="evenodd" d="M61.2907 25.6012C63.8726 27.6188 65.9861 30.1631 67.4878 33.0614C68.9896 35.9597 69.8444 39.1441 69.9944 42.3985C75.2922 41.794 78.5221 36.3018 76.492 31.4464C76.0865 30.4809 75.493 29.604 74.7454 28.8657C74.5721 28.688 74.4762 28.4499 74.4784 28.2029C74.4806 27.9558 74.5806 27.7194 74.757 27.5447C74.9333 27.37 75.1719 27.2709 75.4213 27.2687C75.6707 27.2666 75.911 27.3615 76.0904 27.5332C77.4235 28.8541 78.3497 30.5236 78.7609 32.3465C79.172 34.1695 79.0511 36.0707 78.4123 37.8282C77.7735 39.5856 76.6431 41.1267 75.1532 42.2714C73.6633 43.4162 71.8754 44.1173 69.9983 44.293C69.6621 52.7162 64.5864 60.2099 57.0329 63.8393V74.0576C57.0329 74.3076 56.9327 74.5473 56.7543 74.724C56.5759 74.9007 56.3339 75 56.0817 75H47.0969C46.8446 75 46.6027 74.9007 46.4243 74.724C46.2459 74.5473 46.1457 74.3076 46.1457 74.0576V66.0995H30.1027V74.0576C30.1027 74.3076 30.0025 74.5473 29.8241 74.724C29.6457 74.9007 29.4038 75 29.1515 75H20.1672C19.915 75 19.673 74.9007 19.4946 74.724C19.3162 74.5473 19.216 74.3076 19.216 74.0576V63.8393C13.7969 61.228 9.61914 56.6261 7.57071 51.0115H1.95122C1.69894 51.0115 1.45699 50.9122 1.27861 50.7354C1.10022 50.5587 1 50.319 1 50.0691V36.7188C1 36.4689 1.10022 36.2292 1.27861 36.0525C1.45699 35.8757 1.69894 35.7764 1.95122 35.7764H7.57071C8.75222 32.5262 10.663 29.584 13.159 27.1716C11.6336 23.706 11.1323 19.8826 11.7133 16.1459C11.744 15.9432 11.8407 15.7559 11.9886 15.6125C12.1365 15.4691 12.3275 15.3774 12.5328 15.3513C15.3195 14.9268 18.1653 15.0872 20.8855 15.8221C23.6056 16.5571 26.1396 17.8501 28.323 19.6174C28.8198 15.5854 30.7888 11.8728 33.8585 9.18002C36.9282 6.48724 40.8865 5.00041 44.9866 5C55.8979 5 63.8799 15.1537 61.2907 25.6012ZM18.2247 33.6428C17.6127 33.6427 17.0145 33.8225 16.5056 34.1593C15.9968 34.4961 15.6001 34.9749 15.3659 35.535C15.1317 36.0951 15.0704 36.7115 15.1898 37.3061C15.3092 37.9008 15.6039 38.447 16.0366 38.8757C16.4694 39.3044 17.0207 39.5963 17.6209 39.7146C18.2212 39.8329 18.8433 39.7722 19.4087 39.5401C19.9741 39.3081 20.4573 38.9152 20.7973 38.4111C21.1373 37.9069 21.3187 37.3143 21.3187 36.708C21.3185 35.8951 20.9925 35.1155 20.4123 34.5407C19.8321 33.9659 19.0452 33.6429 18.2247 33.6428ZM24.0233 55.8274C29.4097 58.224 35.5283 58.4397 41.073 56.4287C45.3792 54.8854 49.9591 51.8739 54.4327 46.5809V50.431C54.4327 50.6809 54.5329 50.9206 54.7113 51.0973C54.8897 51.274 55.1317 51.3733 55.3839 51.3733C55.6362 51.3733 55.8782 51.274 56.0566 51.0973C56.2349 50.9206 56.3352 50.6809 56.3352 50.431V43.8713C56.3354 43.7253 56.3013 43.5814 56.2357 43.4507C56.1701 43.3201 56.0747 43.2064 55.957 43.1186C55.8393 43.0308 55.7027 42.9713 55.5578 42.9449C55.413 42.9184 55.2639 42.9258 55.1224 42.9663L48.8443 44.4015C48.7228 44.429 48.6079 44.48 48.5062 44.5515C48.4046 44.623 48.3181 44.7136 48.2518 44.8182C48.1855 44.9228 48.1406 45.0393 48.1198 45.1611C48.0989 45.2829 48.1025 45.4075 48.1303 45.528C48.158 45.6484 48.2095 45.7622 48.2816 45.8629C48.3538 45.9636 48.4453 46.0493 48.5509 46.115C48.6565 46.1806 48.7741 46.2251 48.897 46.2457C49.0199 46.2664 49.1457 46.2629 49.2673 46.2354L52.9602 45.391C48.73 50.3904 44.4398 53.2246 40.4273 54.6622C35.3465 56.5056 29.7396 56.3083 24.8036 54.1125C24.5743 54.0116 24.3139 54.0047 24.0795 54.0934C23.845 54.1821 23.6555 54.3591 23.5524 54.5858C23.4493 54.8124 23.441 55.0703 23.5292 55.3031C23.6174 55.5359 23.7951 55.7241 24.0233 55.8274ZM48.5299 19.5838C48.5299 18.1232 47.3425 17.0331 45.9379 16.7043V15.4914C45.9379 15.2415 45.8376 15.0018 45.6592 14.8251C45.4809 14.6483 45.2389 14.5491 44.9866 14.5491C44.7344 14.5491 44.4924 14.6483 44.314 14.8251C44.1356 15.0018 44.0354 15.2415 44.0354 15.4914V16.7043C42.6308 17.0331 41.4435 18.1239 41.4435 19.5838C41.4435 23.5575 46.6277 21.767 46.6277 23.6764C46.6194 23.8169 46.5793 23.9538 46.5103 24.0769C46.4412 24.1999 46.3451 24.306 46.2289 24.3871C45.1892 25.2079 43.3461 24.6397 43.3461 23.6764C43.3461 23.4265 43.2459 23.1868 43.0675 23.01C42.8891 22.8333 42.6472 22.734 42.3949 22.734C42.1426 22.734 41.9007 22.8333 41.7223 23.01C41.5439 23.1868 41.4437 23.4265 41.4437 23.6764C41.4437 25.1371 42.6311 26.2272 44.0356 26.556V27.769C44.0356 28.0189 44.1358 28.2586 44.3142 28.4353C44.4926 28.612 44.7345 28.7113 44.9868 28.7113C45.2391 28.7113 45.481 28.612 45.6594 28.4353C45.8378 28.2586 45.938 28.0189 45.938 27.769V26.5555C47.3426 26.2268 48.5301 25.136 48.5301 23.6759C48.5301 19.7023 43.3459 21.4928 43.3459 19.5834C43.3542 19.4428 43.3944 19.3059 43.4634 19.1829C43.5324 19.0599 43.6286 18.9538 43.7447 18.8727C44.7843 18.0518 46.6275 18.6203 46.6275 19.5834C46.6275 19.8333 46.7277 20.073 46.9061 20.2497C47.0845 20.4264 47.3264 20.5257 47.5787 20.5257C47.831 20.5257 48.0729 20.4264 48.2513 20.2497C48.4297 20.073 48.5299 19.8338 48.5299 19.5838ZM44.9866 9.55527C47.625 9.55548 50.1922 10.4038 52.3024 11.9728C54.4126 13.5418 55.952 15.7468 56.6893 18.2565C57.4266 20.7662 57.322 23.4452 56.3913 25.891C55.4606 28.3369 53.7539 30.4175 51.5277 31.8204H55.7473C57.7616 29.7324 59.1126 27.1045 59.6327 24.2626C60.1529 21.4208 59.8192 18.4902 58.6732 15.8346C57.5271 13.1789 55.6192 10.9151 53.1861 9.32414C50.753 7.73317 47.9019 6.88505 44.9866 6.88505C42.0713 6.88505 39.2201 7.73317 36.787 9.32414C34.3539 10.9151 32.446 13.1789 31.2999 15.8346C30.1539 18.4902 29.8202 21.4208 30.3404 24.2626C30.8605 27.1045 32.2115 29.7324 34.2258 31.8204H38.4456C31.8822 27.6768 30.8434 18.5654 36.3687 13.092C37.4993 11.969 38.8426 11.0785 40.3217 10.4716C41.8007 9.86461 43.3857 9.55322 44.9866 9.55527ZM52.2598 14.4242C50.8213 12.9991 48.9885 12.0285 46.9932 11.6353C44.9979 11.2421 42.9298 11.444 41.0502 12.2152C39.1707 12.9865 37.5643 14.2926 36.434 15.9684C35.3038 17.6442 34.7005 19.6144 34.7005 21.6299C34.7005 23.6453 35.3038 25.6155 36.434 27.2913C37.5643 28.9671 39.1707 30.2732 41.0502 31.0445C42.9298 31.8158 44.9979 32.0176 46.9932 31.6244C48.9885 31.2312 50.8213 30.2607 52.2598 28.8356C54.1888 26.9245 55.2724 24.3325 55.2724 21.6299C55.2724 18.9272 54.1888 16.3353 52.2598 14.4242ZM28.2051 22.0305C26.2709 20.1885 23.9494 18.7933 21.4068 17.9448C18.8643 17.0964 16.1638 16.8158 13.4989 17.1231C13.11 20.4798 13.6752 23.8774 15.1307 26.9329C15.2351 27.1176 15.2736 27.3317 15.2399 27.5407C15.2063 27.7497 15.1025 27.9413 14.9454 28.0847C13.6121 29.3004 12.441 30.6798 11.4604 32.1895C10.4958 33.6744 9.72623 35.2752 9.17035 36.953C9.11787 37.1556 8.99883 37.3352 8.83197 37.4634C8.66512 37.5917 8.45994 37.6612 8.24877 37.6612H2.90244V49.1267H8.24877V49.1283C8.44938 49.1282 8.64486 49.191 8.80719 49.3078C8.96953 49.4245 9.09039 49.5892 9.15243 49.7782C10.0631 52.5401 11.5427 55.0846 13.4974 57.2507C15.4522 59.4168 17.8395 61.1572 20.5084 62.3619C20.6881 62.4301 20.8426 62.5506 20.9517 62.7077C21.0607 62.8647 21.1192 63.0508 21.1193 63.2414V73.1146H28.2011V65.1571C28.2011 64.9072 28.3013 64.6675 28.4797 64.4907C28.6581 64.314 28.9 64.2147 29.1523 64.2147H47.0969C47.3492 64.2147 47.5911 64.314 47.7695 64.4907C47.9479 64.6675 48.0481 64.9072 48.0481 65.1571V73.1153H55.13V63.2419H55.1314C55.1314 63.0607 55.1843 62.8834 55.2835 62.7312C55.3828 62.5791 55.5243 62.4586 55.691 62.3842C66.5394 57.5347 71.2706 44.6892 65.8903 34.0973C64.6135 31.5875 62.8424 29.3564 60.6814 27.5354C60.09 29.0754 59.2704 30.5196 58.2495 31.8204H60.8218C61.074 31.8204 61.316 31.9197 61.4944 32.0964C61.6728 32.2731 61.773 32.5128 61.773 32.7627C61.773 33.0127 61.6728 33.2524 61.4944 33.4291C61.316 33.6058 61.074 33.7051 60.8218 33.7051H29.1515C28.8992 33.7051 28.6573 33.6058 28.4789 33.4291C28.3005 33.2524 28.2003 33.0127 28.2003 32.7627C28.2003 32.5128 28.3005 32.2731 28.4789 32.0964C28.6573 31.9197 28.8992 31.8204 29.1515 31.8204H31.7236C29.5208 29.0158 28.2871 25.5832 28.2051 22.0305Z" stroke="#6EAFD0" stroke-width="1.2" mask="url(#path-1-outside-1_880_10830)"/>\n' +
                    '</g>\n' +
                    '<defs>\n' +
                    '<clipPath id="clip0_880_10830">\n' +
                    '<rect width="80" height="80" fill="white" transform="translate(0.0405273)"/>\n' +
                    '</clipPath>\n' +
                    '</defs>\n' +
                    '</svg>\n' +
                    '            <p class="title"><strong>Save on every purchase</strong></p>\n' +
                    '            <p class="description">Earn up to 10% off every purchase or appointment.</p>\n' +
                    '        </div>\n' +
                    '        <div class="membership-benefits__item">\n' +
                    '            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 81 80" fill="none"><path fill="#6EAFD0" d="M15.46 61.665a1.667 1.667 0 1 0 0-3.333 1.667 1.667 0 0 0 0 3.333ZM62.128 63.333a1.667 1.667 0 1 0 0-3.333 1.667 1.667 0 0 0 0 3.333ZM37.128 60.333a1.667 1.667 0 1 0 0-3.333 1.667 1.667 0 0 0 0 3.333ZM73.792 28.333a1.667 1.667 0 1 0 0-3.333 1.667 1.667 0 0 0 0 3.333ZM5.46 16.665a1.667 1.667 0 1 0 0-3.333 1.667 1.667 0 0 0 0 3.333Z"/><path fill="#4A9BC4" d="M10.46 30a1.667 1.667 0 1 0 0-3.334 1.667 1.667 0 0 0 0 3.333Z"/><path fill="#6EAFD0" d="M25.461 58.33c0-1-.666-1.666-1.666-1.666-1 0-1.667.667-1.667 1.667-1 0-1.667.666-1.667 1.666 0 1 .667 1.667 1.667 1.667 0 1 .667 1.667 1.667 1.667s1.666-.667 1.666-1.667c1 0 1.667-.667 1.667-1.667s-.667-1.666-1.667-1.666ZM53.794 56.665c0-1-.667-1.667-1.667-1.667s-1.667.667-1.667 1.667c-1 0-1.666.666-1.666 1.666 0 1 .666 1.667 1.666 1.667 0 1 .667 1.667 1.667 1.667s1.666-.667 1.666-1.667c1 0 1.667-.667 1.667-1.667s-.666-1.666-1.666-1.666ZM57.126 4.999c0-1-.667-1.667-1.667-1.667s-1.667.667-1.667 1.667c-1 0-1.666.666-1.666 1.666 0 1 .666 1.667 1.666 1.667 0 1 .667 1.667 1.667 1.667s1.666-.667 1.666-1.667c1 0 1.667-.667 1.667-1.667S58.126 5 57.126 5Z"/><path fill="#4A9BC4" d="M72.126 14.997h-3.334c-1 0-1.666-.666-1.666-1.666 0-1 .666-1.667 1.666-1.667h3.334c1 0 1.666.667 1.666 1.667s-.666 1.666-1.666 1.666Z"/><path fill="#6EAFD0" d="M22.125 11.665h-3.333c-1 0-1.667-.666-1.667-1.666 0-1 .667-1.667 1.667-1.667h3.333c1 0 1.667.667 1.667 1.667s-.667 1.666-1.667 1.666Z"/><path fill="#4A9BC4" d="M73.793 66.665v-20c0-4.666-3.666-8.333-8.333-8.333V24.999c0-3.667-3-6.667-6.666-6.667H46.127c.5-1 1-2.167 1-3.333 0-3.167-2.167-5.834-5.167-6.5-.666-3-3.333-5.167-6.5-5.167-1 0-1.666.667-1.666 1.667s.666 1.666 1.666 1.666c1.334 0 2.5.834 3 2C35.794 9.5 33.794 12 33.794 15a6.01 6.01 0 0 0 1 3.333H22.127c-3.667 0-6.667 3-6.667 6.667v13.333c-4.667 0-8.333 3.667-8.333 8.333v20A3.343 3.343 0 0 0 3.793 70v3.333c0 1.833 1.5 3.333 3.334 3.333h66.666c1.834 0 3.334-1.5 3.334-3.333v-3.333c0-1.834-1.5-3.334-3.334-3.334ZM37.127 15c0-1.834 1.5-3.334 3.333-3.334 1.834 0 3.334 1.5 3.334 3.334 0 1.833-1.5 3.333-3.334 3.333a3.343 3.343 0 0 1-3.333-3.333Zm-15 6.666h36.667c1.833 0 3.333 1.5 3.333 3.334v.333c-.5-.167-1.167-.333-1.667-.333-2 0-3.666.833-4.666 2.333-.834 1.167-3.167 1.167-3.834 0-.5-.833-1.333-1.5-2.333-2-1-.167-1.667-.333-2.5-.333-2 0-3.667.833-4.667 2.333-.833 1.167-3.166 1.167-3.833 0-1.167-1.5-2.834-2.333-4.834-2.333-2 0-3.666.833-4.666 2.333-.833 1.167-3.167 1.167-3.833 0-1.167-1.5-2.834-2.333-4.834-2.333-.5 0-1.166.166-1.666.333v-.333c0-1.834 1.5-3.334 3.333-3.334Zm-6.667 20h28.334c1 0 1.666-.666 1.666-1.666 0-1-.666-1.667-1.666-1.667h-25v-9.333c1-.834 3-.667 3.666.333 1 1.5 2.834 2.333 4.667 2.333 1.833 0 3.667-.833 4.667-2.333.833-1.167 3.166-1.167 3.833 0 1 1.5 2.833 2.333 4.666 2.333 1.834 0 3.667-.833 4.667-2.333.5-.833 2-1.167 3-.667.334.167.667.5 1 .667 1 1.5 2.834 2.333 4.667 2.333 1.833 0 3.666-.833 4.666-2.333.667-1 2.667-1.167 3.667-.333v9.333h-8.333c-1 0-1.667.667-1.667 1.667s.667 1.666 1.667 1.666h11.666c2.834 0 5 2.167 5 5V51l-.333-.334c-.5-.833-1.333-1.5-2.333-2-.667-.166-1.334-.333-2.167-.333-2 0-3.666.833-4.666 2.333-.834 1.167-3.167 1.167-3.834 0-1.166-1.5-2.833-2.333-4.833-2.333s-3.667.833-4.667 2.333c-.833 1.167-3.166 1.167-3.833 0-.5-.833-1.334-1.5-2.334-2-1-.166-1.666-.333-2.5-.333-2 0-3.666.833-4.666 2.333-.834 1.167-3.167 1.167-3.834 0-1.166-1.5-2.833-2.333-4.833-2.333s-3.666.833-4.666 2.333c-.834 1.167-3.167 1.167-3.834 0-1.167-1.5-2.833-2.333-4.833-2.333-.5 0-1.167.167-1.667.333v-2c0-2.833 2.167-5 5-5Zm-5 10.667c1-.833 3-.667 3.667.333 1 1.5 2.667 2.334 4.667 2.334s3.666-.834 4.666-2.334c.834-1.166 3.167-1.166 3.834 0 1 1.5 2.833 2.334 4.666 2.334 1.834 0 3.667-.834 4.667-2.334.5-.833 2-1.166 3-.666.333.166.666.5 1 .666 1 1.5 2.833 2.334 4.666 2.334 1.834 0 3.667-.834 4.667-2.334.834-1.166 3.167-1.166 3.834 0 1 1.5 2.833 2.334 4.666 2.334 1.834 0 3.667-.834 4.667-2.334.5-.833 2-1.166 3-.666.333.166.666.5 1 .666.666 1 1.833 1.834 3 2.167v11.833H10.46V52.332Zm63.334 21H7.126v-3.333h66.666v3.333Z"/><path fill="#6EAFD0" d="M48.792 41.665a1.667 1.667 0 1 0 0-3.333 1.667 1.667 0 0 0 0 3.333Z"/></svg>\n' +
                    '            <p class="title"><strong>Celebrate your birthday</strong></p>\n' +
                    '            <p class="description">Get a special birthday gift – 500 points on your special day!</p>\n' +
                    '        </div>\n' +
                    '        <div class="membership-benefits__item">\n' +
                    '            <svg width="81" height="80" viewBox="0 0 81 80" fill="none" xmlns="http://www.w3.org/2000/svg">\n' +
                    '<g clip-path="url(#clip0_360_5849)">\n' +
                    '<path d="M7.24316 25.8575V28.8007C7.24316 32.6498 10.3748 35.7814 14.2237 35.7814C18.0727 35.7814 21.2043 32.6498 21.2043 28.8007V25.8575C21.2043 22.0084 18.0727 18.877 14.2237 18.877C10.3748 18.877 7.24316 22.0084 7.24316 25.8575ZM18.8607 28.8009C18.8607 31.3576 16.7807 33.4378 14.2239 33.4378C11.6671 33.4378 9.58711 31.3578 9.58711 28.8009V28.4398C11.3735 28.2077 13.0322 27.3268 14.2239 25.9683C15.4156 27.3268 17.0744 28.2079 18.8607 28.4398V28.8009ZM14.2237 21.2207C16.7805 21.2207 18.8605 23.3008 18.8605 25.8575V26.0672C17.6152 25.8289 16.4835 25.128 15.7202 24.0928L15.4813 23.7687C15.188 23.3711 14.7177 23.1337 14.2236 23.1337C13.7295 23.1337 13.2594 23.3712 12.9662 23.7689L12.7273 24.0928C11.964 25.1278 10.8323 25.8289 9.58695 26.0672V25.8575C9.58695 23.3008 11.6671 21.2207 14.2237 21.2207Z" fill="#4A9BC4"/>\n' +
                    '<path d="M47.8998 28.8007V25.8575C47.8998 22.0084 44.7682 18.877 40.9191 18.877C37.0699 18.877 33.9385 22.0086 33.9385 25.8575V28.8007C33.9385 32.6498 37.0699 35.7814 40.9191 35.7814C44.7682 35.7814 47.8998 32.6498 47.8998 28.8007ZM40.9192 21.2206C43.3339 21.2206 45.3235 23.0761 45.5371 25.4361L44.861 24.76C44.1969 24.0961 43.3141 23.7303 42.375 23.7303H39.4631C38.524 23.7303 37.641 24.0959 36.977 24.76L36.3012 25.4358C36.5149 23.0759 38.5045 21.2206 40.9192 21.2206ZM36.2823 28.8007V28.2255C36.8521 28.011 37.3752 27.6766 37.8095 27.2424L38.6343 26.4174C38.8557 26.196 39.15 26.0743 39.4631 26.0743H42.375C42.688 26.0743 42.9824 26.1961 43.2038 26.4175L44.0286 27.2426C44.4629 27.6768 44.986 28.0113 45.556 28.2259V28.8009C45.556 31.3576 43.476 33.4378 40.9191 33.4378C38.3625 33.4375 36.2823 31.3575 36.2823 28.8007Z" fill="#6EAFD0"/>\n' +
                    '<path d="M74.5951 28.8007V25.8575C74.5951 22.0084 71.4635 18.877 67.6144 18.877C63.7652 18.877 60.6338 22.0084 60.6338 25.8575V28.8007C60.6338 32.6498 63.7654 35.7814 67.6145 35.7814C71.4636 35.7814 74.5951 32.6498 74.5951 28.8007ZM67.6144 21.2206C69.7016 21.2206 71.4711 22.607 72.0507 24.5072C70.9374 23.9991 69.7196 23.7303 68.4741 23.7303H66.7547C65.5092 23.7303 64.2913 23.9991 63.178 24.5072C63.7576 22.607 65.5271 21.2206 67.6144 21.2206ZM62.9776 28.8007V27.339C64.0626 26.5202 65.3856 26.0743 66.7548 26.0743H68.4742C69.8433 26.0743 71.1663 26.5202 72.2513 27.339V28.8007C72.2513 31.3575 70.1713 33.4377 67.6144 33.4377C65.0576 33.4375 62.9776 31.3575 62.9776 28.8007Z" fill="#4A9BC4"/>\n' +
                    '<path d="M77.1094 38.6608C79.9189 34.4121 81.2416 29.3336 80.8514 24.1838C79.9905 12.8229 70.7604 3.7193 59.3817 3.00835C52.581 2.58413 45.989 5.15214 41.2905 10.0552C41.0886 10.266 40.7489 10.266 40.5471 10.0552C35.8484 5.15198 29.2539 2.5835 22.4558 3.00835C20.7811 3.11289 19.1156 3.40492 17.5058 3.87602C16.8845 4.0579 16.5284 4.70869 16.7101 5.32995C16.8919 5.95106 17.5425 6.30731 18.1641 6.12559C19.6074 5.70324 21.1005 5.44152 22.6021 5.34761C28.7108 4.96542 34.6346 7.2728 38.855 11.6768C39.4022 12.2479 40.1352 12.5623 40.9189 12.5623C41.7027 12.5623 42.4357 12.2477 42.9829 11.6768C47.2031 7.2728 53.1263 4.96557 59.2358 5.34761C69.4535 5.9859 77.7416 14.1599 78.5145 24.361C78.8931 29.3563 77.48 34.2749 74.5299 38.2608C74.5112 38.2703 74.4926 38.2803 74.4745 38.2908H43.6534C43.0062 38.2908 42.4815 38.8153 42.4815 39.4625C42.4815 40.1097 43.0062 40.6344 43.6534 40.6344H49.4541V43.1441H48.1992C47.552 43.1441 47.0273 43.6688 47.0273 44.316V62.5585H34.8103V44.3159C34.8103 43.6687 34.2856 43.144 33.6384 43.144H32.3835V40.6343H38.1845C38.8317 40.6343 39.3564 40.1096 39.3564 39.4624C39.3564 38.8152 38.8317 38.2905 38.1845 38.2905H7.36342C7.34514 38.28 7.3267 38.27 7.3078 38.2605C4.35806 34.2751 2.94476 29.356 3.3232 24.3609C3.82462 17.7439 7.56639 11.7204 13.3323 8.24781C13.8866 7.9139 14.0656 7.19373 13.7316 6.63935C13.3979 6.08496 12.678 5.90606 12.1232 6.23997C5.70824 10.1035 1.54489 16.8114 0.986134 24.1838C0.595815 29.3336 1.91865 34.4123 4.72822 38.6608C2.50538 39.4936 0.918945 41.6401 0.918945 44.1501V68.6669C0.918945 70.6055 2.496 72.1826 4.43463 72.1826H5.77183V75.0829C5.77183 76.16 6.64794 77.0361 7.72499 77.0361H20.7232C21.8002 77.0361 22.6762 76.16 22.6762 75.0829V57.4198C23.0432 57.5506 23.4377 57.6219 23.8482 57.6219H25.2732L32.4665 63.8692V75.0831C32.4665 76.1601 33.3426 77.0362 34.4196 77.0362H47.418C48.495 77.0362 49.3711 76.1601 49.3711 75.0831V63.8692L56.5643 57.6219H57.9894C58.4 57.6219 58.7945 57.5506 59.1616 57.4198V75.0831C59.1616 76.1601 60.0375 77.0362 61.1146 77.0362H74.1129C75.1899 77.0362 76.0661 76.1601 76.0661 75.0831V72.1827H77.4033C79.3419 72.1827 80.9189 70.6057 80.9189 68.667V44.1501C80.9189 41.6401 79.3324 39.4936 77.1094 38.6608ZM78.5752 44.1501V47.9977H76.0661V46.7428C76.0661 46.0956 75.5414 45.5709 74.8942 45.5709C74.247 45.5709 73.7223 46.0956 73.7223 46.7428V62.5583H61.5057V46.7428C61.5057 46.0956 60.9805 45.5709 60.3333 45.5709C59.6861 45.5709 59.1614 46.0956 59.1614 46.7428V47.9977H56.6514V44.1501C56.6514 42.2115 58.2284 40.6344 60.1671 40.6344H75.0595C76.9979 40.6344 78.5752 42.2116 78.5752 44.1501ZM54.3942 43.144H51.7979V40.6343H55.4825C54.937 41.3593 54.5556 42.2144 54.3942 43.144ZM54.3074 45.4878V50.4232H49.3711V45.4878H54.3074ZM32.4665 45.4878V50.4232H27.5303V45.4878H32.4665ZM30.0397 43.144H27.4437C27.2822 42.2143 26.9009 41.3591 26.3554 40.6343H30.0397V43.144ZM4.43463 69.8388C3.78837 69.8388 3.26273 69.3131 3.26273 68.6669V50.3414H5.77183V53.2963C5.77183 53.9435 6.29653 54.4682 6.94373 54.4682C7.59092 54.4682 8.11562 53.9435 8.11562 53.2963V46.7428C8.11562 46.0956 7.59092 45.5709 6.94373 45.5709C6.29653 45.5709 5.77183 46.0956 5.77183 46.7428V47.9977H3.26273V44.1501C3.26273 42.2115 4.83979 40.6344 6.77841 40.6344H21.6708C23.6095 40.6344 25.1865 42.2115 25.1865 44.1501V47.9977H22.6763V46.7428C22.6763 46.0956 22.1516 45.5709 21.5044 45.5709C20.8572 45.5709 20.3322 46.0956 20.3322 46.7428V62.5583H8.11562V58.765C8.11562 58.1178 7.59092 57.5931 6.94373 57.5931C6.29653 57.5931 5.77183 58.1178 5.77183 58.765V69.8388H4.43463ZM15.3959 74.6924V68.5839C15.3959 67.9367 14.8712 67.412 14.224 67.412C13.5768 67.412 13.0521 67.9367 13.0521 68.5839V74.6924H8.11562V64.9021H20.3325V74.6923H15.3959V74.6924ZM22.6763 54.1146C22.6763 54.1127 22.6763 50.3414 22.6763 50.3414H25.1865V51.5952C25.1865 52.2424 25.7112 52.7671 26.3584 52.7671H32.4665V55.2781H23.8481C23.2065 55.2781 22.6807 54.7562 22.6763 54.1146ZM32.4665 57.6219V60.7647L28.8477 57.6219H32.4665ZM42.0908 74.6924V68.5839C42.0908 67.9367 41.5661 67.412 40.9189 67.412C40.2717 67.412 39.7471 67.9367 39.7471 68.5839V74.6924H34.8103V64.9021H47.0273V74.6923H42.0908V74.6924ZM49.3711 60.7647V57.6219H52.9899L49.3711 60.7647ZM59.1613 54.1146C59.1569 54.756 58.6311 55.2779 57.9894 55.2779H49.371V52.7669H55.479C56.1262 52.7669 56.6509 52.2422 56.6509 51.5951V50.3413H59.1614C59.1616 50.3414 59.1613 54.1063 59.1613 54.1146ZM73.7223 74.6924H68.7856V68.5839C68.7856 67.9367 68.2609 67.412 67.6137 67.412C66.9665 67.412 66.4418 67.9367 66.4418 68.5839V74.6924H61.5052V64.9021H73.7223V74.6924ZM77.4033 69.8388H76.0661V50.3414H78.5752V68.6669C78.5752 69.3131 78.0494 69.8388 77.4033 69.8388Z" fill="#4A9BC4"/>\n' +
                    '</g>\n' +
                    '<defs>\n' +
                    '<clipPath id="clip0_360_5849">\n' +
                    '<rect width="80" height="80" fill="white" transform="translate(0.918945)"/>\n' +
                    '</clipPath>\n' +
                    '</defs>\n' +
                    '</svg>\n' +
                    '            <p class="title"><strong>Earn points with friends</strong></p>\n' +
                    '            <p class="description">Get 500 points for each friend you invite!</p>\n' +
                    '        </div>\n' +
                    '        <div class="membership-benefits__item">\n' +
                    '            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 80 81" fill="none"><path fill="#4A9BC4" d="M67.745 29.239h-15.52v-6.143c0-.744-.524-1.348-1.171-1.348s-1.172.604-1.172 1.348v46.135h-3.74v-47.25c.478-.185.872-.599 1.072-1.155.276-.765.125-1.639-.384-2.225l-4.795-5.515c-.655-.753-1.526-1.168-2.452-1.168-.926 0-1.796.415-2.451 1.168l-4.795 5.515c-.51.586-.66 1.46-.385 2.225.2.556.595.97 1.072 1.154v47.251h-3.74V9.536c0-3.22 2.279-5.84 5.079-5.84h10.44c2.8 0 5.079 2.62 5.079 5.84v7.09c0 .745.524 1.348 1.172 1.348.647 0 1.171-.603 1.171-1.347v-7.09c0-4.708-3.33-8.537-7.421-8.537H34.363C30.27 1 26.94 4.83 26.94 9.536v7.166H11.42C7.33 16.702 4 20.532 4 25.238v7.51c0 .744.525 1.347 1.172 1.347.647 0 1.172-.603 1.172-1.348v-7.509c0-3.22 2.278-5.84 5.078-5.84h15.52V69.23h-3.74V38.154c.477-.185.871-.598 1.071-1.154.276-.766.125-1.64-.384-2.225l-4.796-5.515c-1.351-1.555-3.55-1.555-4.902 0l-4.795 5.515c-.51.586-.66 1.459-.385 2.225.2.556.595.97 1.072 1.154V69.23h-3.74V39.037c0-.744-.524-1.348-1.171-1.348S4 38.293 4 39.037v31.542c0 .744.525 1.348 1.172 1.348h68.823c.647 0 1.172-.604 1.172-1.348V37.775c0-4.707-3.33-8.536-7.422-8.536ZM12.427 69.23V37.607c0-.681-.291-1.285-.739-1.657l4.16-4.784c.438-.503 1.15-.504 1.588 0l4.16 4.784c-.447.371-.738.976-.738 1.657V69.23h-8.431Zm22.941 0V21.433c0-.681-.291-1.285-.739-1.656l4.16-4.785c.213-.244.495-.378.794-.378.3 0 .582.134.794.378l4.16 4.785c-.447.37-.738.975-.738 1.656v47.798h-8.431Zm22.941 0V50.186c0-.68-.291-1.285-.738-1.656l4.16-4.785c.437-.503 1.15-.503 1.587 0l4.16 4.785c-.447.371-.738.975-.738 1.656v19.045h-8.43Zm14.514 0h-3.74V50.734c.478-.185.872-.598 1.072-1.154.276-.766.125-1.64-.384-2.226l-4.796-5.515c-1.35-1.554-3.55-1.554-4.902 0l-4.795 5.515c-.51.586-.66 1.46-.385 2.225.2.556.595.97 1.072 1.154v18.498h-3.74V31.934h15.52c2.8 0 5.078 2.62 5.078 5.841v31.456Z"/></svg>\n' +
                    '            <p class="title"><strong>Automatic tier upgrade</strong></p>\n' +
                    '            <p class="description">Earn enough points to automatically upgrade your tier.</p>\n' +
                    '        </div>\n' +
                    '        <div class="membership-benefits__item">\n' +
                    '            <svg width="81" height="81" viewBox="0 0 81 81" fill="none" xmlns="http://www.w3.org/2000/svg">\n' +
                    '<g clip-path="url(#clip0_360_5867)">\n' +
                    '<path d="M72.6148 48.9896V12.9278C72.6148 9.98217 70.2271 7.59445 67.2815 7.59445H61.9482V4.92779C61.9482 2.71828 60.1574 0.92749 57.9479 0.92749H56.6149C54.4053 0.92749 52.6146 2.71828 52.6146 4.92779V7.59445H40.6143V4.92779C40.6143 2.71828 38.8235 0.92749 36.6146 0.92749H35.281C33.0721 0.92749 31.2807 2.71828 31.2807 4.92779V7.59445H20.614V4.92779C20.614 2.71828 18.8232 0.92749 16.6143 0.92749H15.2807C13.0718 0.92749 11.2804 2.71828 11.2804 4.92779V7.59445H5.94709C3.0015 7.59445 0.61377 9.98217 0.61377 12.9278V63.5949C0.61377 66.5405 3.0015 68.9282 5.94709 68.9282H46.7862C49.1373 76.197 55.9697 81.0689 63.6071 80.9242C71.2452 80.7796 77.8877 75.6514 79.9617 68.2996C82.0351 60.9472 79.051 53.1041 72.6148 48.9896ZM55.2812 4.92779C55.2812 4.19108 55.8781 3.59415 56.6149 3.59415H57.9479C58.6846 3.59415 59.2815 4.19108 59.2815 4.92779V12.9278C59.2815 13.6639 58.6846 14.2608 57.9479 14.2608H56.6149C55.8781 14.2608 55.2812 13.6639 55.2812 12.9278V4.92779ZM33.9473 4.92779C33.9473 4.19108 34.5449 3.59415 35.281 3.59415H36.6146C37.3507 3.59415 37.9476 4.19108 37.9476 4.92779V12.9278C37.9476 13.6639 37.3507 14.2608 36.6146 14.2608H35.281C34.5449 14.2608 33.9473 13.6639 33.9473 12.9278V4.92779ZM13.9471 4.92779C13.9471 4.19108 14.5446 3.59415 15.2807 3.59415H16.6143C17.3504 3.59415 17.9474 4.19108 17.9474 4.92779V12.9278C17.9474 13.6639 17.3504 14.2608 16.6143 14.2608H15.2807C14.5446 14.2608 13.9471 13.6639 13.9471 12.9278V4.92779ZM3.28043 12.9278C3.28043 11.455 4.47429 10.2611 5.94709 10.2611H11.2804V12.9278C11.2804 15.1367 13.0718 16.9275 15.2807 16.9275H16.6143C18.8232 16.9275 20.614 15.1367 20.614 12.9278V10.2611H31.2807V12.9278C31.2807 15.1367 33.0721 16.9275 35.281 16.9275H36.6146C38.8235 16.9275 40.6143 15.1367 40.6143 12.9278V10.2611H52.6146V12.9278C52.6146 15.1367 54.4053 16.9275 56.6149 16.9275H57.9479C60.1574 16.9275 61.9482 15.1367 61.9482 12.9278V10.2611H67.2815C68.7543 10.2611 69.9482 11.455 69.9482 12.9278V22.2614H3.28043V12.9278ZM5.94709 66.2616C4.47429 66.2616 3.28043 65.0677 3.28043 63.5949V24.928H69.9482V47.5852C69.9054 47.5681 69.8603 47.5547 69.8145 47.5376C69.5374 47.4241 69.2548 47.324 68.9722 47.2269C68.8385 47.1787 68.7055 47.1256 68.5626 47.0816C68.1384 46.948 67.7106 46.8253 67.2784 46.724C62.1215 45.5094 56.6918 46.7173 52.5358 50.0041C48.3799 53.2908 45.9537 58.2964 45.9476 63.5949C45.9519 64.2535 45.9934 64.9109 46.0733 65.5646C46.088 65.6976 46.1014 65.8313 46.1197 65.9643C46.1331 66.0632 46.1399 66.1645 46.1545 66.2616H5.94709ZM63.2812 78.2619C56.92 78.2826 51.2766 74.1841 49.329 68.1281C49.2466 67.8552 49.1624 67.5842 49.0867 67.2498C48.7693 66.0577 48.6106 64.8285 48.6143 63.5949C48.6198 59.1106 50.6736 54.8747 54.1917 52.0933C57.7092 49.3119 62.3046 48.2908 66.6693 49.3198C67.0691 49.4132 67.4615 49.528 67.851 49.6561C68.8129 49.968 69.7406 50.38 70.6177 50.8842C76.3765 54.1997 79.1866 60.9728 77.4666 67.3914C75.7466 73.8099 69.9262 78.2698 63.2812 78.2619Z" fill="#4A9BC4"/>\n' +
                    '<path d="M21.949 28.9275H13.9484C12.4762 28.9275 11.2817 30.1214 11.2817 31.5942V39.5941C11.2817 41.0669 12.4762 42.2614 13.9484 42.2614H21.949C23.4218 42.2614 24.6156 41.0669 24.6156 39.5941V31.5942C24.6156 30.1214 23.4218 28.9275 21.949 28.9275ZM13.9484 39.5941V31.5942H21.949V39.5941H13.9484Z" fill="#6EAFD0"/>\n' +
                    '<path d="M40.6163 28.9275H32.6164C31.1436 28.9275 29.9497 30.1214 29.9497 31.5942V39.5941C29.9497 41.0669 31.1436 42.2614 32.6164 42.2614H40.6163C42.0891 42.2614 43.283 41.0669 43.283 39.5941V31.5942C43.283 30.1214 42.0891 28.9275 40.6163 28.9275ZM32.6164 39.5941V31.5942H40.6163V39.5941H32.6164Z" fill="#6EAFD0"/>\n' +
                    '<path d="M21.949 44.9275H13.9484C12.4762 44.9275 11.2817 46.1214 11.2817 47.5942V55.5941C11.2817 57.0669 12.4762 58.2608 13.9484 58.2608H21.949C23.4218 58.2608 24.6156 57.0669 24.6156 55.5941V47.5942C24.6156 46.1214 23.4218 44.9275 21.949 44.9275ZM13.9484 55.5941V47.5942H21.949V55.5941H13.9484Z" fill="#6EAFD0"/>\n' +
                    '<path d="M40.6163 44.9275H32.6164C31.1436 44.9275 29.9497 46.1214 29.9497 47.5942V55.5941C29.9497 57.0669 31.1436 58.2608 32.6164 58.2608H40.6163C42.0891 58.2608 43.283 57.0669 43.283 55.5941V47.5942C43.283 46.1214 42.0891 44.9275 40.6163 44.9275ZM32.6164 55.5941V47.5942H40.6163V55.5941H32.6164Z" fill="#6EAFD0"/>\n' +
                    '<path d="M51.2804 42.2614H59.281C60.7538 42.2614 61.9477 41.0669 61.9477 39.5941V31.5942C61.9477 30.1214 60.7538 28.9275 59.281 28.9275H51.2804C49.8076 28.9275 48.6138 30.1214 48.6138 31.5942V39.5941C48.6138 41.0669 49.8076 42.2614 51.2804 42.2614ZM51.2804 31.5942H59.281V39.5941H51.2804V31.5942Z" fill="#6EAFD0"/>\n' +
                    '<path d="M68.962 57.3639L60.2528 66.9466L57.602 64.0303C57.1064 63.4859 56.2629 63.4456 55.7178 63.9412C55.1734 64.4368 55.1331 65.2803 55.6287 65.8254L59.2646 69.825C59.5173 70.104 59.8762 70.2627 60.2522 70.2627C60.6282 70.2627 60.9864 70.104 61.2397 69.825L70.9353 59.1584C71.2564 58.8062 71.3644 58.3094 71.2191 57.8553C71.0745 57.4012 70.6985 57.0594 70.2328 56.9581C69.7671 56.8567 69.2831 57.0112 68.962 57.3639Z" fill="#6EAFD0"/>\n' +
                    '<path d="M7.28139 51.5951C8.01748 51.5951 8.61441 50.9982 8.61441 50.2621V48.9285C8.61441 48.1924 8.01748 47.5955 7.28139 47.5955C6.54469 47.5955 5.94775 48.1924 5.94775 48.9285V50.2621C5.94775 50.9982 6.54469 51.5951 7.28139 51.5951Z" fill="#4A9BC4"/>\n' +
                    '<path d="M12.6147 60.9283H8.61441V55.595C8.61441 54.8583 8.01748 54.2614 7.28139 54.2614C6.54469 54.2614 5.94775 54.8583 5.94775 55.595V60.9283C5.94775 62.4011 7.14162 63.595 8.61441 63.595H12.6147C13.3508 63.595 13.9477 62.998 13.9477 62.2619C13.9477 61.5252 13.3508 60.9283 12.6147 60.9283Z" fill="#4A9BC4"/>\n' +
                    '<path d="M19.2804 60.9275H17.9468C17.2107 60.9275 16.6138 61.5244 16.6138 62.2611C16.6138 62.9972 17.2107 63.5942 17.9468 63.5942H19.2804C20.0165 63.5942 20.6135 62.9972 20.6135 62.2611C20.6135 61.5244 20.0165 60.9275 19.2804 60.9275Z" fill="#4A9BC4"/>\n' +
                    '</g>\n' +
                    '<defs>\n' +
                    '<clipPath id="clip0_360_5867">\n' +
                    '<rect width="80" height="80" fill="white" transform="translate(0.459473 0.92749)"/>\n' +
                    '</clipPath>\n' +
                    '</defs>\n' +
                    '</svg>\n' +
                    '            <p class="title"><strong>Free consultation</strong></p>\n' +
                    '            <p class="description">Use 250 points to book your follow-up consultation for a free period.</p>\n' +
                    '        </div>\n' +
                    '        <div class="membership-benefits__item">\n' +
                    '            <svg width="81" height="81" viewBox="0 0 81 81" fill="none" xmlns="http://www.w3.org/2000/svg">\n' +
                    '<g clip-path="url(#clip0_360_5882)">\n' +
                    '<path d="M77.9111 19.1369H56.5341C56.7404 18.19 56.8502 17.2076 56.8502 16.1998C56.8502 14.3397 56.4857 12.5345 55.7666 10.8348C55.5144 10.2387 54.8266 9.96014 54.2308 10.2122C53.6349 10.4642 53.3558 11.1519 53.6082 11.7479C54.2041 13.1573 54.5064 14.655 54.5064 16.1998C54.5064 22.507 49.3752 27.6384 43.068 27.6384C36.7607 27.6384 31.6294 22.507 31.6294 16.1998C31.6294 9.89264 36.7607 4.76123 43.068 4.76123C45.875 4.76123 48.5744 5.78779 50.6691 7.65201C51.1529 8.08248 51.8935 8.0392 52.3236 7.55576C52.7539 7.07217 52.7108 6.33154 52.2274 5.90123C49.703 3.65467 46.45 2.41748 43.068 2.41748C35.4683 2.41748 29.2857 8.60014 29.2857 16.1998C29.2857 17.2076 29.3954 18.19 29.6018 19.1369H20.7674V13.2665C20.7674 9.61264 17.7947 6.63998 14.1408 6.63998L3.60348 6.64061C2.12316 6.64045 0.918945 7.84482 0.918945 9.32498V10.6656C0.918945 12.1459 2.12316 13.3501 3.60348 13.3501L14.058 13.3494V48.3244C14.058 52.3109 17.0852 55.6026 20.9611 56.0233L17.9341 59.7898C16.5507 61.5098 16.2702 63.8212 17.2024 65.8231C18.1449 67.8428 20.2318 69.1478 22.5191 69.1478H25.7716V71.0622C23.9854 71.5729 22.6735 73.2194 22.6735 75.1673C22.6735 77.5217 24.5889 79.4373 26.9435 79.4373C29.298 79.4373 31.2135 77.5217 31.2135 75.1673C31.2135 73.2192 29.9016 71.5729 28.1154 71.0622V69.1478H53.0264V71.0622C51.2402 71.5729 49.9285 73.2194 49.9285 75.1673C49.9285 77.5217 51.8439 79.4373 54.1983 79.4373C56.5527 79.4373 58.4683 77.5217 58.4683 75.1673C58.4683 73.2194 57.1564 71.5729 55.3702 71.0622V69.1478H58.3269C59.2208 69.1478 60.0627 68.7995 60.6993 68.1656C61.3327 67.5322 61.6816 66.6897 61.6816 65.7929C61.6816 63.9431 60.1768 62.4383 58.3269 62.4383H24.4127L29.5305 56.0695H37.261C37.9082 56.0695 38.4329 55.545 38.4329 54.8976C38.4329 54.2503 37.9082 53.7258 37.261 53.7258H21.8018C18.8241 53.7258 16.4018 51.3026 16.4018 48.3242V13.2665C16.4018 12.0198 15.3875 11.0056 14.1408 11.0056H11.5347V8.98373H14.1408C16.5024 8.98373 18.4236 10.905 18.4236 13.2665V48.3244C18.4236 50.1879 19.9391 51.704 21.8018 51.704H59.6377C61.0466 51.704 62.321 50.8153 62.8093 49.4917L71.0535 27.0934H73.2077L64.7068 50.19C63.9285 52.3048 61.8913 53.7259 59.6377 53.7259H42.9044C42.2571 53.7259 41.7325 54.2504 41.7325 54.8978C41.7325 55.5451 42.2571 56.0697 42.9044 56.0697H59.6377C62.8694 56.0697 65.7904 54.032 66.9063 50.9995L75.7052 27.0933H77.9113C79.5697 27.0933 80.9191 25.7447 80.9191 24.087V22.1448C80.9189 20.4862 79.5696 19.1369 77.9111 19.1369ZM28.8697 75.1675C28.8697 76.2297 28.0057 77.0937 26.9435 77.0937C25.8814 77.0937 25.0172 76.2297 25.0172 75.1675C25.0172 74.1053 25.8813 73.2412 26.9435 73.2412C28.0055 73.2412 28.8697 74.1053 28.8697 75.1675ZM56.1244 75.1675C56.1244 76.2297 55.2604 77.0937 54.1982 77.0937C53.1361 77.0937 52.2721 76.2297 52.2721 75.1675C52.2721 74.1053 53.1361 73.2412 54.1982 73.2412C55.2604 73.2412 56.1244 74.1053 56.1244 75.1675ZM21.3302 62.5328C20.993 62.959 20.9321 63.5037 21.1663 63.9919C21.4016 64.4869 21.8688 64.7822 22.4174 64.7822H58.3268C58.8841 64.7822 59.3377 65.2356 59.3377 65.7931C59.3377 66.0634 59.2327 66.3173 59.0435 66.5065C58.851 66.6984 58.5963 66.804 58.3268 66.804H22.5189C21.1385 66.804 19.8852 66.03 19.3264 64.8328C18.7744 63.6472 18.9405 62.2776 19.7605 61.2584L23.9305 56.0697H26.5238L21.3302 62.5328ZM3.2627 10.6656V9.32498C3.2627 9.13717 3.41551 8.9842 3.60348 8.9842H9.19082V11.0062H3.60348C3.41551 11.0064 3.2627 10.8534 3.2627 10.6656ZM42.276 49.3603L43.3002 39.0869H53.133L51.2714 49.3603H42.276ZM31.6299 49.3603V39.0869H40.9449L39.9207 49.3603H31.6299ZM20.7674 27.0931H29.2861V36.7431H20.7674V27.0931ZM31.6299 27.0931H34.6349C36.6677 28.6705 39.1519 29.6919 41.858 29.9284L41.1786 36.7431H31.6299V27.0931ZM43.5338 36.7431L44.2125 29.9344C46.9435 29.7087 49.451 28.6836 51.5 27.0939L55.306 27.0937L53.5575 36.7431H43.5338ZM30.3382 21.4806C30.83 22.6619 31.4814 23.7605 32.2657 24.7494H20.7672V21.4806H30.3382ZM20.7674 48.3244V39.0869H29.2861V49.3603H21.8018C21.2314 49.3603 20.7674 48.8954 20.7674 48.3244ZM60.61 48.6812C60.4602 49.0875 60.0694 49.3603 59.6375 49.3603H53.6533L55.5149 39.0869H64.1414L60.61 48.6812ZM65.0043 36.7431H55.9396L57.688 27.0937L68.556 27.0934L65.0043 36.7431ZM78.5752 24.0869C78.5752 24.4522 78.2774 24.7494 77.9111 24.7494L53.8696 24.75C54.6541 23.7609 55.3057 22.662 55.7977 21.4806H77.9111C78.2774 21.4806 78.5752 21.7784 78.5752 22.1447V24.0869Z" fill="#4A9BC4"/>\n' +
                    '<path d="M42.9884 7.52686C41.2653 7.52686 39.8634 8.92873 39.8634 10.6519V14.8404C38.8632 14.6292 37.7792 14.9114 37.004 15.6865C36.4139 16.2767 36.0889 17.0615 36.0889 17.8962C36.0889 18.7311 36.414 19.5158 37.0042 20.1061L40.8579 23.9597C41.4481 24.55 42.2329 24.875 43.0678 24.875C43.9025 24.875 44.6872 24.55 45.2776 23.9598L49.1314 20.1061C50.3497 18.8876 50.3497 16.9053 49.1314 15.6867L49.1312 15.6865C48.315 14.8706 47.1561 14.6009 46.1137 14.8783V10.6519C46.1134 8.92873 44.7115 7.52686 42.9884 7.52686ZM46.369 17.3439C46.6734 17.0395 47.169 17.0392 47.4737 17.3439C47.7782 17.6484 47.7782 18.144 47.4737 18.4486L43.62 22.3023C43.4725 22.45 43.2762 22.5312 43.0676 22.5312C42.8589 22.5312 42.6628 22.45 42.5153 22.3023L38.6614 18.4486C38.5139 18.3011 38.4326 18.105 38.4326 17.8962C38.4326 17.6876 38.5139 17.4914 38.6614 17.3439C38.8137 17.1915 39.0139 17.1154 39.214 17.1154C39.414 17.1154 39.6142 17.1915 39.7664 17.3437L40.2068 17.7842C40.542 18.1195 41.0464 18.2195 41.484 18.0384C41.922 17.857 42.2075 17.4297 42.2075 16.9556V10.6519C42.2075 10.2211 42.5579 9.87061 42.9887 9.87061C43.4195 9.87061 43.77 10.2211 43.77 10.6519V17.1137C43.77 17.5876 44.0554 18.0151 44.4934 18.1964C44.9315 18.3781 45.4351 18.2776 45.7706 17.9425L46.369 17.3439Z" fill="#6EAFD0"/>\n' +
                    '</g>\n' +
                    '<defs>\n' +
                    '<clipPath id="clip0_360_5882">\n' +
                    '<rect width="80" height="80" fill="white" transform="translate(0.918945 0.92749)"/>\n' +
                    '</clipPath>\n' +
                    '</defs>\n' +
                    '</svg>\n' +
                    '            <p class="title"><strong>Shop with points</strong></p>\n' +
                    '            <p class="description">Use accumulated points to purchase therapies a period here too so each point is consistent.</p>\n' +
                    '        </div>\n' +
                    '    </div>');
                //jQuery('.my_account_points').remove();
                //jQuery('.wps_wpr_membrship_update_heading').before(updatedTable);
                //jQuery('.acctpointtable').show();
                <?php foreach($membership_array as $membership): ?>
                //jQuery('#wps_wpr_popup_wrapper_<?php echo strtolower(str_replace(' ', '', $membership['name'])); ?>_tier .wps_wpr_close a').attr('onclick',"jQuery('#wps_wpr_popup_wrapper_<?php echo strtolower(str_replace(' ', '', $membership['name'])); ?>_tier').hide()");
                <?php endforeach; ?>
            })
        })(jQuery)
    </script>

    <?php

}

add_action('wp_footer', 'custom_points_page_functionality', 9999);


/** Add title to an Account details page */

function add_custom_heading_to_edit_account_page()
{
    echo '<h2 class="registration-page__title">Personal Details</h2>';
}

add_action('woocommerce_account_edit-account_endpoint', 'add_custom_heading_to_edit_account_page', 5);

/** Add additional text to a referral link section**/

function add_custom_paragraphs_script()
{
    ?>
    <script type="text/javascript">
        document.addEventListener('DOMContentLoaded', function () {
            var elements = document.querySelectorAll('.wps_wpr_refrral_code_copy');
            elements.forEach(function (el) {
                el.insertAdjacentHTML('afterend', '<p class="referral-info">Use this link to invite others to start their wellness journey</p><p class="referral-points-info">Each accepted invitation earns <strong>500</strong> reward points.</p>');
            });
        });

        window.addEventListener('load', function () {
            var preloader = document.querySelector('.preloader');
            if (preloader) {
                preloader.style.display = 'none';
            }
        });
    </script>
    <?php
}

add_action('wp_footer', 'add_custom_paragraphs_script');

if (!function_exists('vv_is_membership_active_by_meta')) {
    function vv_is_membership_active_by_meta(int $user_id): bool {
        $raw = get_user_meta($user_id, 'membership_expiration', true);
        if (!$raw) return true;
        $exp_ts = is_numeric($raw) ? (int)$raw : strtotime($raw);
        if (!$exp_ts) return true;
        return $exp_ts >= current_time('timestamp');
    }
}

if (!function_exists('vv_is_membership_active_by_meta')) {
    function vv_is_membership_active_by_meta(int $user_id): bool {
        $raw = get_user_meta($user_id, 'membership_expiration', true);
        if (!$raw) return true;
        $exp_ts = is_numeric($raw) ? (int)$raw : strtotime($raw);
        if (!$exp_ts) return true;
        return $exp_ts >= current_time('timestamp');
    }
}

add_action('wp_footer', function () {
    if (!is_user_logged_in()) return;

    $on_points = (function_exists('is_wc_endpoint_url') && is_wc_endpoint_url('points'));
    if (!$on_points) {
        $uri = isset($_SERVER['REQUEST_URI']) ? trim($_SERVER['REQUEST_URI'], '/') : '';
        $on_points = (strpos($uri, 'my-account/points') !== false);
    }
    if (!$on_points) return;

    $active = vv_is_membership_active_by_meta(get_current_user_id());
    if ($active) return;

    ?>
    <style>
        .wps_wpr_membership_with_img .wps_wpr_tick { display:none !important; }
    </style>
    <script>
        (function(){
            function removeTicks(){
                document.querySelectorAll('.wps_wpr_membership_with_img .wps_wpr_tick')
                    .forEach(function(el){ el.remove(); });
            }
            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', removeTicks);
            } else {
                removeTicks();
            }
            var root = document.querySelector('.wps_wpr_membership_with_img') || document.body;
            if (!root) return;
            var obs = new MutationObserver(function(){
                removeTicks();
            });
            obs.observe(root, {childList:true, subtree:true});
        })();
    </script>
    <?php
}, 999);



