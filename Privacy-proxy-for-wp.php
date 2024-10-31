<?php
/**
 * Plugin Name: Privacy Proxy for Wp
 * Plugin URI:  https://piiguard.com/products/privacy-proxy.php
 * Description: "Privacy Proxy for Wp" adds a script link in the header that activates the Privacy Proxy solution from PII Guard.
 * Version:     1.0
 * Author:      PII Guard
 * Author URI:  https://piiguard.com/
 * License:     GPL2
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 */

function privacy_proxy_add_settings_page() {
add_submenu_page(
'options-general.php',
'Settings for Privacy Proxy',
'Privacy Proxy WP',
'manage_options',
'your-plugin-settings',
'privacy_proxy_render_settings_page'
);
}
add_action( 'admin_menu', 'privacy_proxy_add_settings_page' );

function privacy_proxy_render_settings_page() {
// Get the user's custom code and name from the database
$pii_guard_host_id = get_option( 'privacy_proxy_host_id', '' );
$pii_guard_site_tag = get_option( 'privacy_proxy_site_tag', '' );
$site_url = home_url();
// Output the settings page HTML
?>
<div class="wrap">
<div class="flexHeader" style="display:flex; align-items: center;">
<img src="<?php echo plugins_url( 'privacy-proxy-selected.svg', __FILE__ ); ?>">
<h1>Privacy Proxy for Wp</h1>
</div>
<form method="post" action="options.php">
<?php settings_fields( 'privacy_proxy_settings_group' ); ?>
<?php wp_nonce_field( 'privacy_proxy_settings_action', 'privacy_proxy_settings_nonce' ); ?>
<label for="privacy_proxy_host_id">Insert host ID from "License & configuration" (XXXXXXXX)</label><br>
<textarea name="privacy_proxy_host_id" id="privacy_proxy_host_id" rows="10" cols="80"><?php echo esc_textarea( $pii_guard_host_id ); ?></textarea><br>
<label for="privacy_proxy_site_tag">Insert site tag from "License & configuration" (PP-XXXXXXXX)</label><br>
<textarea name="privacy_proxy_site_tag" id="privacy_proxy_site_tag" rows="10" cols="80"><?php echo esc_textarea( $pii_guard_site_tag ); ?></textarea><br>
<p>When pressing Save Changes the plugin will add a script link in your header activating the Privacy Proxy. </p>
<input type="submit" name="submit" class="button button-primary" value="Save Changes">
</form>
<div class="checkActivation">
<h3>Deactivate Google Analytics and verify</h3>
<div class="userinformation">Remember to remove other plugins that activates or embeds Google Tag Manager and/or Google Analytics.<br><br>
We recommends that you verify that your website is correctly configured by using PII Guard's Google Analytics Scanner, which can be found on the web address below: <div class="button button-primary linkToScanner"><a href="https://piiguard.com/ga-scanner" target="_blank">Google Analytics scanner</a></div></div>
</div>
<div class="bottomContent">
<div class="leftBottom">When the plugin is deactived the code in the header is removed. If you need help or assistence please contact us at <a href="mailto:info@piiguard.com">info@piiguard.com</a>. </div>
<div class="rightBottom">
<img src="<?php echo plugins_url( 'pii-guard-primary.svg', __FILE__ ); ?>">
</div>
</div>
</div>
<?php
}

function privacy_proxy_enqueue_styles( $hook ) {
    // Only enqueue styles on the plugin's settings page
    if ( 'settings_page_your-plugin-settings' !== $hook ) {
        return; 
    }
    
    wp_enqueue_style( 'privacy-proxy-styles', plugin_dir_url( __FILE__ ) . 'style.css', array(), '1.0.0' );
}

add_action( 'admin_enqueue_scripts', 'privacy_proxy_enqueue_styles' );

function privacy_proxy_save_settings() {
// Check if the form was submitted and the user has permission
if ( isset( $_POST['submit'] ) && current_user_can( 'manage_options' ) ) {
// Verify the security token
check_admin_referer( 'privacy_proxy_settings_action', 'privacy_proxy_settings_nonce' );

// Sanitize and save the custom code
$pii_guard_host_id = sanitize_textarea_field( $_POST['privacy_proxy_host_id'] );
update_option( 'privacy_proxy_host_id', $pii_guard_host_id );
$pii_guard_site_tag = sanitize_textarea_field( $_POST['privacy_proxy_site_tag'] );
update_option( 'privacy_proxy_site_tag', $pii_guard_site_tag );
}
}

add_action( 'admin_init', 'privacy_proxy_save_settings' );

function privacy_proxy_output_host_id() {
    $pii_guard_host_id = get_option( 'privacy_proxy_host_id', '' );
    $pii_guard_site_tag = get_option( 'privacy_proxy_site_tag', '' );

    if ($pii_guard_host_id !== "" && $pii_guard_site_tag !== "") {
        wp_enqueue_script( "piiguard-pp-script", "https://" . $pii_guard_host_id . ".gaprivacy.io/analytics.js?tid=" . $pii_guard_site_tag);
    } 

}

add_action( 'wp_print_scripts', 'privacy_proxy_output_host_id' );

function privacy_proxy_register_settings() {
register_setting( 'privacy_proxy_settings_group', 'privacy_proxy_host_id', 'sanitize_textarea_field' );
}
add_action( 'admin_init', 'privacy_proxy_register_settings' );

?>