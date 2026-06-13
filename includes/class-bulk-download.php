<?php

if (!defined('ABSPATH')) {
    exit;
}

class BBMWPV_Bulk_Download
{

    /**
     * Initialize hooks.
     *
     * @return void
     */
    public function init()
    {
        add_action(
            'wp_ajax_bbmwpv_bulk_download',
            array($this, 'handle_bulk_download')
        );

        add_action(
            'wp_ajax_bbmwpv_download_bundle',
            array($this, 'download_bundle')
        );

        add_action(
            'bbmwpv_cleanup_zip',
            array($this, 'cleanup_zip')
        );
    }

    /**
     * Handle bulk download.
     *
     * @return void
     */
    public function handle_bulk_download()
    {

        @set_time_limit(0);

        @ini_set('memory_limit', '-1');

        $this->cleanup_old_files();

        if (!current_user_can('manage_options')) {

            wp_send_json_error(
                array(
                    'message' => 'Permission denied.',
                )
            );
        }

        check_ajax_referer(
            'bbmwpv_nonce',
            'nonce'
        );

        $backup_ids = isset($_POST['files'])
        ? array_map(
            'sanitize_text_field',
            (array) wp_unslash($_POST['files'])
        )
        : array();

        if (empty($backup_ids)) {

            wp_send_json_error(
                array(
                    'message' => 'No backups selected.',
                )
            );
        }

        $backup_dir =
            WP_CONTENT_DIR . '/wpvividbackups';

        if (!is_dir($backup_dir)) {

            wp_send_json_error(
                array(
                    'message' =>
                    'Backup directory not found.',
                )
            );
        }

        $all_files = scandir($backup_dir);

        if (false === $all_files) {

            wp_send_json_error(
                array(
                    'message' =>
                    'Failed to scan backup directory.',
                )
            );
        }

        $matched_files = array();

        foreach ($all_files as $file) {

            if (
                '.' === $file ||
                '..' === $file
            ) {
                continue;
            }

            foreach ($backup_ids as $backup_id) {

                if (
                    false !== strpos(
                        $file,
                        $backup_id
                    )
                ) {

                    $matched_files[] = $file;

                    break;
                }
            }
        }

        if (empty($matched_files)) {

            wp_send_json_error(
                array(
                    'message' =>
                    'No matching backup files found.',
                )
            );
        }

        if (!class_exists('ZipArchive')) {

            wp_send_json_error(
                array(
                    'message' =>
                    'ZipArchive is not available.',
                )
            );
        }

        $site_url = set_url_scheme(
            home_url(),
            'https'
        );

        /**
         * Remove protocol.
         */
        $site_url = preg_replace(
            '#^https?://#',
            '',
            $site_url
        );

        /**
         * Convert unsafe filename chars.
         */
        $site_url = str_replace(
            array(
                '/',
                '\\',
                ':',
                '?',
                '&',
                '=',
                '.',
            ),
            '-',
            $site_url
        );

        /**
         * Cleanup duplicate dashes.
         */
        $site_url = preg_replace(
            '/-+/',
            '-',
            $site_url
        );

        $site_url = trim(
            $site_url,
            '-'
        );

        $date = wp_date(
            'Y-m-d-His'
        );

        $zip_filename =
            $site_url .
            '-wpvivid-bundle-' .
            $date .
            '.zip';

        /**
         * Download directory.
         */
        $upload_dir = wp_upload_dir();

        $bbmwpv_download_dir =
            trailingslashit(
            $upload_dir['basedir']
        ) . 'bbmwpv-downloads';

        if (
            !file_exists(
                $bbmwpv_download_dir
            )
        ) {

            wp_mkdir_p(
                $bbmwpv_download_dir
            );
        }

        /**
         * Protect directory listing.
         */
        $htaccess =
            $bbmwpv_download_dir .
            '/.htaccess';

        if (!file_exists($htaccess)) {

            file_put_contents(
                $htaccess,
                "Options -Indexes\n"
            );
        }

        $zip_path =
            trailingslashit(
            $bbmwpv_download_dir
        ) . $zip_filename;

        $zip = new ZipArchive();

        if (
            true !== $zip->open(
                $zip_path,
                ZipArchive::CREATE |
                ZipArchive::OVERWRITE
            )
        ) {

            wp_send_json_error(
                array(
                    'message' =>
                    'Failed to create ZIP archive.',
                )
            );
        }

        foreach ($matched_files as $file) {

            $file_path =
                trailingslashit(
                $backup_dir
            ) . $file;

            if (file_exists($file_path)) {

                $zip->addFile(
                    $file_path,
                    $file
                );

                /**
                 * Do not recompress ZIP files.
                 */
                $zip->setCompressionName(
                    $file,
                    ZipArchive::CM_STORE
                );
            }
        }

        if (!$zip->close()) {

            wp_send_json_error(
                array(
                    'message' =>
                    'Failed to finalize ZIP archive.',
                )
            );
        }

        if (
            !file_exists($zip_path) ||
            filesize($zip_path) < 100
        ) {

            wp_send_json_error(
                array(
                    'message' =>
                    'Generated ZIP file is invalid.',
                )
            );
        }

        /**
         * Public download URL.
         */
        $download_url =
        trailingslashit(
            set_url_scheme(
                $upload_dir['baseurl'],
                'https'
            )
        ) .
        'bbmwpv-downloads/' .
        rawurlencode(
            $zip_filename
        );

        /**
         * Schedule cleanup.
         */
        wp_schedule_single_event(
            time() + HOUR_IN_SECONDS,
            'bbmwpv_cleanup_zip',
            array($zip_path)
        );

        $fallback_url = wp_nonce_url(
            admin_url(
                'admin-ajax.php?action=bbmwpv_download_bundle&file=' .
                rawurlencode($zip_filename)
            ),
            'bbmwpv_download_bundle'
        );

        wp_send_json_success(
            array(
                'message' =>
                'ZIP bundle created.',

                'url' =>
                $download_url,

                'fallback_url' =>
                $fallback_url,
            )
        );

    }

    /**
     * Cleanup generated ZIP file.
     *
     * @param string $zip_path ZIP path.
     *
     * @return void
     */
    public function cleanup_zip(
        $zip_path
    ) {

        if (
            empty($zip_path) ||
            !file_exists($zip_path)
        ) {
            return;
        }

        wp_delete_file($zip_path);
    }

    /**
     * Download generated ZIP bundle.
     *
     * @return void
     */
    public function download_bundle()
    {
        if (!current_user_can('manage_options')) {
            wp_die('Permission denied');
        }

        check_admin_referer(
            'bbmwpv_download_bundle'
        );

        $filename = isset($_GET['file'])
        ? sanitize_file_name(
            wp_unslash($_GET['file'])
        )
        : '';

        if (empty($filename)) {
            wp_die('Invalid file');
        }

        $upload_dir = wp_upload_dir();

        $file =
            trailingslashit(
            $upload_dir['basedir']
        ) .
            'bbmwpv-downloads/' .
            $filename;

        if (!file_exists($file)) {
            wp_die('File not found');
        }

        nocache_headers();

        header('Content-Type: application/zip');
        header(
            'Content-Disposition: attachment; filename="' .
            basename($file) .
            '"'
        );
        header(
            'Content-Length: ' .
            filesize($file)
        );

        readfile($file);

        exit;
    }

    /**
     * Cleanup old ZIP files.
     *
     * @return void
     */
    private function cleanup_old_files()
    {

        $upload_dir = wp_upload_dir();

        $dir =
            trailingslashit(
            $upload_dir['basedir']
        ) . 'bbmwpv-downloads';

        if (!file_exists($dir)) {
            return;
        }

        $files = glob(
            $dir . '/*.zip'
        );

        if (empty($files)) {
            return;
        }

        foreach ($files as $file) {

            if (
                filemtime($file) <
                (
                    time() -
                    DAY_IN_SECONDS
                )
            ) {

                wp_delete_file($file);
            }
        }
    }
}
