<?php

define( 'AP_IS_SILVER', true );
define( 'AP_IS_GOLD', true );
define( 'AP_UPGRADE_TO_SILVER', '' );
define( 'AP_UPGRADE_TO_GOLD', '' );
register_uninstall_hook( AP_PLUGIN_FILE_URL, 'ap_uninstall_logic' );

function ap_uninstall_logic()
{
    delete_option( 'actions_pack' );
    delete_option( 'elementor_ap_google_sheet_auth_code' );
    delete_option( 'elementor_ap_google_sheet_client_id' );
    delete_option( 'elementor_ap_google_sheet_client_secret' );
    delete_option( 'elementor_ap_google_sheet_credentials_validate' );
    delete_option( 'elementor_ap_sms_account_sid' );
    delete_option( 'elementor_ap_sms_auth_token' );
    delete_option( 'elementor_ap_sms_from_number' );
}
