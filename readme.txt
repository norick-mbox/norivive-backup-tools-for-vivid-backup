=== Norivive Backup Tools for Vivid Backup ===
Contributors: noricksaeki
Tags: backup, wpvivid, migration, restore, download
Requires at least: 6.0
Tested up to: 7.0
Requires PHP: 7.4
Stable tag: 1.0.2
Required plugin: WPvivid Backup Plugin
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Bulk download and upload tools for WPvivid Backup Plugin.
WPvivid Backup 環境向けのバックアップバンドル管理プラグインです。

== Description ==

Norivive Backup Tools for Vivid Backup is an independent bulk backup management add-on for WPvivid Backup Plugin.
Norivive Backup Tools for Vivid Backup は、独立したバックアップバンドル管理プラグインです。

This plugin allows administrators to:
このプラグインでは、管理者が以下を行えます：

* Bulk download backup ZIP files
  バックアップ ZIP ファイルの一括ダウンロード

* Upload backup bundles
  バックアップバンドルのアップロード

* Restore multiple backup archives into WPvivid backup storage
  複数のバックアップアーカイブを WPvivid バックアップストレージへ復元

* Manage backup bundles more efficiently
  バックアップバンドルを効率的に管理

Features:
主な機能：

* Bulk ZIP bundle download
  ZIP バンドルの一括ダウンロード

* ZIP bundle upload/import
  ZIP バンドルのアップロード / インポート

* No recompression mode for faster processing
  高速処理のための再圧縮なしモード

* ZIP bomb protection
  ZIP Bomb 保護

* WordPress coding standards friendly
  WordPress コーディング規約に配慮

* Lightweight admin integration
  軽量な管理画面統合

This plugin is an independent third-party add-on and is not affiliated with any backup plugin vendor.
このプラグインは独立したサードパーティ製アドオンであり、いかなるバックアッププラグインベンダーとも提携していません。

== Installation ==

1. Upload the plugin files to the `/wp-content/plugins/norivive-backup-tools-for-vivid-backup` directory.
   プラグインファイルを `/wp-content/plugins/norivive-backup-tools-for-vivid-backup` ディレクトリへアップロードします。

2. Activate the plugin through the 'Plugins' screen in WordPress.
   WordPress 管理画面の「プラグイン」画面から有効化します。

3. Make sure WPvivid Backup Plugin is installed and activated.
   WPvivid Backup Plugin がインストール・有効化されていることを確認してください。

4. Open the WPvivid backup page.
   WPvivid のバックアップ画面を開きます。

== Frequently Asked Questions ==

= Does this plugin require the Vivid Backup plugin? =
= このプラグインは Vivid Backup プラグインが必要ですか？ =

Yes.
はい。

WPvivid Backup Plugin must be installed and activated.
WPvivid Backup Plugin のインストールおよび有効化が必要です。

= Does this plugin modify WPvivid core files? =
= このプラグインは WPvivid のコアファイルを変更しますか？ =

No.
いいえ。

This plugin works as an independent add-on.
このプラグインは独立したアドオンとして動作します。

= Are backup ZIP files recompressed? =
= バックアップ ZIP ファイルは再圧縮されますか？ =

No.
いいえ。

The plugin stores ZIP files without additional recompression whenever possible.
可能な限り追加の再圧縮を行わず ZIP ファイルを保存します。

== Screenshots ==

1. Bulk download toolbar
   一括ダウンロードツールバー

2. Backup bundle upload form
   バックアップバンドルアップロードフォーム

3. Imported backup list
   インポート済みバックアップ一覧

== Notes for Plugin Review Team ==

This plugin uses `readfile()` in the download endpoint intentionally for ZIP stream downloads.
このプラグインでは、ZIP ストリームダウンロードのために intentionally `readfile()` を使用しています。

The plugin handles potentially large backup bundle files generated from WPvivid backups.
本プラグインは、WPvivid バックアップから生成される大容量バックアップバンドルを扱います。

Using `WP_Filesystem::get_contents()` would load the entire ZIP into memory and may cause memory exhaustion on large backup archives.
`WP_Filesystem::get_contents()` を使用すると ZIP 全体をメモリへ読み込むため、大容量バックアップではメモリ不足を引き起こす可能性があります。

The implementation:
実装では以下を行っています：

* validates user capability
  ユーザー権限の検証

* validates download token
  ダウンロードトークンの検証

* validates file existence
  ファイル存在確認

* sends appropriate download headers
  適切なダウンロードヘッダー送信

* deletes the temporary ZIP after download
  ダウンロード後の一時 ZIP 削除

The file is generated temporarily and removed immediately after streaming.
ファイルは一時生成され、ストリーミング後に即時削除されます。

== Changelog ==
= 1.0.2 =

* Fixed download issues on some environments with mixed HTTP/HTTPS configurations.
* Added a fallback download method via admin-ajax.php when direct ZIP download fails.
* Improved download URL handling for HTTPS environments.
* Improved download reliability on servers with SSL configuration inconsistencies.


* 一部のHTTP/HTTPS混在環境で発生するダウンロードエラーを修正しました。
* ZIPファイルの直接ダウンロードに失敗した場合の救済として、admin-ajax.php経由のダウンロード機能を追加しました。
* HTTPS環境におけるダウンロードURLの処理を改善しました。
* SSL設定に不整合があるサーバー環境でのダウンロード安定性を向上しました。


= 1.0.1 =

* Initial release.
  初回リリース

== Upgrade Notice ==
= 1.0.2 =

* Fixed download issues on some hosting environments.
* Added a fallback download method for improved compatibility.
* Improved HTTPS download handling.
* 一部サーバー環境で発生するダウンロードエラーを修正しました。
* 互換性向上のためフォールバックダウンロード機能を追加しました。
* HTTPS環境でのダウンロード処理を改善しました。


= 1.0.1 =

Initial release.
初回リリース
