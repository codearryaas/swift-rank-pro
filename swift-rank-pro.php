<?php
/**
 * Plugin Name: Swift Rank Pro
 * Plugin URI: https://toolpress.net/swift-rank
 * Description: Premium features for Swift Rank.
 * Version: 1.0.0
 * Author: ToolPress
 * Author URI: https://toolpress.net
 * License: GPLv2 or later
 * Requires Plugins: swift-rank
 * Text Domain: swift-rank-pro
 * Domain Path: /languages
 *
 * @package Swift_Rank_Pro
 */

if (!defined('ABSPATH')) {
	exit;
}

// Define constants.
define('SWIFT_RANK_PRO_VERSION', '1.0.0');
define('SWIFT_RANK_PRO_FILE', __FILE__);
define('SWIFT_RANK_PRO_PATH', plugin_dir_path(__FILE__));
define('SWIFT_RANK_PRO_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('SWIFT_RANK_PRO_URL', plugin_dir_url(__FILE__));

if (!function_exists('swift_rank_pro_fs')) {
	// Create a helper function for easy SDK access.
	function swift_rank_pro_fs()
	{
		global $swift_rank_pro_fs;

		if (!isset($swift_rank_pro_fs)) {
			// Include Freemius SDK.
			require_once dirname(__FILE__) . '/freemius/start.php';

			$swift_rank_pro_fs = fs_dynamic_init(array(
				'id' => '22065',
				'slug' => 'swift-rank',
				'premium_slug' => 'swift-rank-pro',
				'type' => 'plugin',
				'public_key' => 'pk_0fe47c9861fffbe19397465f2b465',
				'is_premium' => true,
				'is_premium_only' => true,
				'has_addons' => false,
				'has_paid_plans' => true,
				// Automatically removed in the free version. If you're not using the
				// auto-generated free version, delete this line before uploading to wp.org.
				'wp_org_gatekeeper' => 'OA7#BoRiBNqdf52FvzEf!!074aRLPs8fspif$7K1#4u4Csys1fQlCecVcUTOs2mcpeVHi#C2j9d09fOTvbC0HloPT7fFee5WdS3G',
				'menu' => array(
					'slug' => 'swift-rank-pro',
					'override_exact' => true,
					'first-path' => 'edit.php?post_type=sr_template',
					'contact' => false,
					'support' => false,
					'parent' => array(
						'slug' => 'swift-rank',
					),
				),
			));
		}

		return $swift_rank_pro_fs;
	}

	// Init Freemius.
	swift_rank_pro_fs();
	// Signal that SDK was initiated.
	do_action('swift_rank_pro_fs_loaded');

	function swift_rank_pro_fs_settings_url()
	{
		return admin_url('admin.php?page=swift-rank-pro');
	}

	function swift_rank_pro_fs_after_activation_url()
	{
		return admin_url('admin.php?page=swift-rank-settings#license');
	}

	swift_rank_pro_fs()->add_filter('connect_url', 'swift_rank_pro_fs_settings_url');
	swift_rank_pro_fs()->add_filter('after_skip_url', 'swift_rank_pro_fs_settings_url');
	swift_rank_pro_fs()->add_filter('after_connect_url', 'swift_rank_pro_fs_after_activation_url');
	swift_rank_pro_fs()->add_filter('after_pending_connect_url', 'swift_rank_pro_fs_settings_url');
}

/**
 * Main Swift Rank Pro Class
 */
class Swift_Rank_Pro
{


	/**
	 * Instance of this class.
	 *
	 * @var object
	 */
	private static $instance = null;

	/**
	 * Get singleton instance.
	 *
	 * @return Swift_Rank_Pro
	 */
	public static function get_instance()
	{
		if (null === self::$instance) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Constructor.
	 */
	private function __construct()
	{
		add_action('plugins_loaded', array($this, 'init'));
		add_action('admin_menu', array($this, 'register_admin_menu'), 100000);
	}

	/**
	 * Initialize plugin.
	 */
	public function init()
	{
		// Check if base plugin is active.
		if (!class_exists('Swift_Rank')) {
			add_action('admin_notices', array($this, 'admin_notice_missing_base_plugin'));
			return;
		}

		// Load includes.
		$this->includes();

		// Initialize admin (needed on both frontend and backend for schema filters).
		Swift_Rank_Pro_Admin::get_instance();
	}

	/**
	 * Register admin menu pages.
	 */
	public function register_admin_menu()
	{
		add_submenu_page(
			'swift-rank',
			__('License', 'swift-rank-pro'),
			__('License', 'swift-rank-pro'),
			'manage_options',
			'swift-rank-pro',
			'__return_empty_string'
		);
		// remove_submenu_page('swift-rank', 'swift-rank-pro');
	}

	/**
	 * Load required files.
	 */
	private function includes()
	{
		require_once SWIFT_RANK_PRO_PATH . 'includes/class-swift-rank-pro-admin.php';

		// Register Pro schema types
		require_once SWIFT_RANK_PRO_PATH . 'includes/schema-types-registration.php';



		// Load Pro variable replacer extension
		require_once SWIFT_RANK_PRO_PATH . 'includes/class-schema-variable-replacer-pro.php';

		// Load Schema Relationships (Pro feature)
		// Schema_Reference_Resolver is now loaded in the base plugin
		require_once SWIFT_RANK_PRO_PATH . 'includes/class-schema-relationships-api.php';

		// Load Paywall Fields Helper (must load before schema extensions)
		require_once SWIFT_RANK_PRO_PATH . 'includes/output/extend-types/class-paywall-fields-helper.php';

		// Load Pro extensions for schema types
		require_once SWIFT_RANK_PRO_PATH . 'includes/output/extend-types/class-article-schema.php';
		require_once SWIFT_RANK_PRO_PATH . 'includes/output/extend-types/class-video-schema.php';

		// Load Pro Conditions
		require_once SWIFT_RANK_PRO_PATH . 'includes/class-swift-rank-pro-conditions.php';
		Swift_Rank_Pro_Conditions::get_instance();

		// Load Schema Presets (Pro feature)
		require_once SWIFT_RANK_PRO_PATH . 'includes/class-schema-presets.php';

		// Load User Profile Schema (Pro feature)
		if (is_admin()) {
			require_once SWIFT_RANK_PRO_PATH . 'includes/class-swift-rank-pro-user-profile.php';
			Swift_Rank_Pro_User_Profile::get_instance();
		}
	}

	/**
	 * Admin notice for missing base plugin.
	 */
	public function admin_notice_missing_base_plugin()
	{
		?>
		<div class="notice notice-error">
			<p><?php esc_html_e('Swift Rank Pro requires Swift Rank to be installed and active.', 'swift-rank-pro'); ?>
			</p>
		</div>
		<?php
	}
}

// Initialize the plugin.
Swift_Rank_Pro::get_instance();
