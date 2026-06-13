<?php
    /**
 * Plugin Name: Norivive Backup Tools for Vivid Backup
 * Plugin URI: https://github.com/norick-mbox/norivive-backup-tools-for-vivid-backup
 * Description: Bulk download and upload manager for WPvivid Backup Plugin.
 * Version: 1.0.2
 * Requires Plugins: wpvivid-backuprestore
 * Author: Norick Saeki
 * Author URI: https://norick-mbox.com/
 * Text Domain: norivive-backup-tools-for-vivid-backup
 * Domain Path: /languages
 * Requires at least: 6.0
 * Requires PHP: 7.4
 * Tested up to: 7.0
 * License: GPLv2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 *
 * @package NoriviveBackupToolsForVividBackup
 */

    if (!defined('ABSPATH')) {
    exit;
    }

    /**
 * Plugin version.
 */
    define('BBMWPV_VERSION', '1.0.2');

    /**
 * Plugin path.
 */
    define('BBMWPV_PLUGIN_PATH', plugin_dir_path(__FILE__));

    /**
 * Plugin URL.
 */
    define('BBMWPV_PLUGIN_URL', plugin_dir_url(__FILE__));

    /**
 * Plugin basename.
 */
    define('BBMWPV_PLUGIN_BASENAME', plugin_basename(__FILE__));

    /**
 * Check if WPvivid is active.
 *
 * @return bool
 */
    function bbmwpv_is_wpvivid_active()
    {

    include_once ABSPATH . 'wp-admin/includes/plugin.php';

    return is_plugin_active('wpvivid-backuprestore/wpvivid-backuprestore.php');
    }

    /**
 * Admin notice when WPvivid is not active.
 *
 * @return void
 */
    function bbmwpv_wpvivid_missing_notice()
    {

    if (bbmwpv_is_wpvivid_active()) {
        return;
    }

    ?>
	<div class="notice notice-error">
		<p>
			<?php
                echo esc_html__(
                        'Bulk Backup Manager for WPvivid requires WPvivid Backup Plugin to be installed and activated.',
                        'norivive-backup-tools-for-vivid-backup'
                    );
                ?>
		</p>
	</div>
	<?php
        }
        add_action('admin_notices', 'bbmwpv_wpvivid_missing_notice');


        /**
         * Load plugin files.
         *
         * @return void
         */
        function bbmwpv_load_plugin()
        {

            if (!bbmwpv_is_wpvivid_active()) {
        return;
            }

            require_once BBMWPV_PLUGIN_PATH . 'includes/class-plugin.php';

            if (class_exists('BBMWPV_Plugin')) {

        $plugin = new BBMWPV_Plugin();
        $plugin->init();
            }
    }
    add_action('plugins_loaded', 'bbmwpv_load_plugin', 20);