<?php
/**
 * Pro Admin Class
 *
 * @package Swift_Rank_Pro
 */

if (!defined('ABSPATH')) {
	exit;
}

/**
 * Swift_Rank_Pro_Admin class
 */
class Swift_Rank_Pro_Admin
{

	/**
	 * Instance of this class
	 *
	 * @var object
	 */
	private static $instance = null;

	/**
	 * Get singleton instance
	 *
	 * @return Swift_Rank_Pro_Admin
	 */
	public static function get_instance()
	{
		if (null === self::$instance) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Constructor
	 */
	private function __construct()
	{
		// Enqueue scripts
		add_action('admin_enqueue_scripts', array($this, 'enqueue_scripts'));

		// Register Pro schema builders with the output handler
		add_action('init', array($this, 'register_pro_schema_builders'), 20);

		// Enable Pro schema types via PHP filter
		add_filter('swift_rank_schema_types', array($this, 'enable_pro_schema_types'));

		// Hide License menu item visually
		add_action('admin_head', array($this, 'hide_license_menu_item'));
	}

	/**
	 * Enqueue scripts
	 */
	public function enqueue_scripts()
	{
		$screen = get_current_screen();
		if (!$screen) {
			return;
		}

		// Load on Schema Template edit screen, Post edit screen, and Settings page
		$is_template_screen = 'sr_template' === $screen->post_type;
		$is_post_screen = 'post' === $screen->base;
		$is_settings_page = isset($_GET['page']) && 'swift-rank-settings' === sanitize_text_field(wp_unslash($_GET['page']));

		if ($is_template_screen || $is_post_screen || $is_settings_page) {
			$asset_file = include SWIFT_RANK_PRO_PATH . 'build/index.asset.php';

			// Determine which base plugin script to depend on
			$base_dependency = array();
			if ($is_template_screen) {
				// Template editor - depend on template metabox script
				$base_dependency = array('swift-rank-metabox');
			} elseif ($is_settings_page) {
				// Settings page - depend on settings script
				$base_dependency = array('swift-rank-settings');
			} else {
				// Post editor - depend on post metabox script
				$base_dependency = array('swift-rank-post-metabox');
			}

			// Merge Base plugin dependencies with Pro dependencies
			$dependencies = array_merge(
				$asset_file['dependencies'],
				$base_dependency
			);

			wp_enqueue_script(
				'swift-rank-pro',
				SWIFT_RANK_PRO_URL . 'build/index.js',
				$dependencies,
				$asset_file['version'],
				true
			);

			// Enqueue Pro styles
			wp_enqueue_style(
				'swift-rank-pro',
				SWIFT_RANK_PRO_URL . 'build/index.css',
				array(),
				$asset_file['version']
			);

			wp_set_script_translations('swift-rank-pro', 'swift-rank-pro');

			// Localize presets data for template and post screens
			if ($is_template_screen || $is_post_screen) {
				// Get all registered schema types (already ordered by filters)
				$all_schema_types = apply_filters('swift_rank_register_types', array());

				// Get unique preset types
				$preset_type_values = Swift_Rank_Pro_Presets::get_preset_types();

				// Build type metadata array with labels and icons
				// Order types according to the schema types order
				$type_metadata = array();

				// First, iterate through all_schema_types to maintain the order
				foreach ($all_schema_types as $type_value => $type_data) {
					// Only include types that have presets
					if (in_array($type_value, $preset_type_values)) {
						$type_metadata[$type_value] = array(
							'value' => $type_value,
							'label' => $type_data['label'],
							'icon' => $type_data['icon'],
						);
					}
				}

				// Add any preset types that weren't in all_schema_types (fallback)
				foreach ($preset_type_values as $type_value) {
					if (!isset($type_metadata[$type_value])) {
						$type_metadata[$type_value] = array(
							'value' => $type_value,
							'label' => $type_value,
							'icon' => 'file-text',
						);
					}
				}

				$presets_data = array(
					'presets' => Swift_Rank_Pro_Presets::get_presets(),
					'types' => $type_metadata,
				);
				wp_localize_script('swift-rank-pro', 'swiftRankProPresets', $presets_data);
			}

			// Localize license data for settings page.
			if ($is_settings_page && function_exists('swift_rank_pro_fs')) {
				$fs = swift_rank_pro_fs();
				$license = $fs->_get_license();
				$license_data = array(
					'status' => $fs->is_paying() ? 'active' : 'inactive',
					'plan' => $fs->is_registered() ? $fs->get_plan_name() : '',
					'expiration' => ($license && $license->expiration) ? date_i18n(get_option('date_format'), strtotime($license->expiration)) : '',
					'accountUrl' => $fs->is_registered() ? $fs->get_account_url() : '',
					'activationUrl' => $fs->get_activation_url(),
				);

				wp_localize_script('swift-rank-pro', 'swiftRankProLicense', $license_data);
			}
		}
	}

	/**
	 * Register Pro schema builders with the output handler
	 */
	public function register_pro_schema_builders()
	{
		// Check if base plugin output handler exists
		if (!class_exists('Schema_Output_Handler')) {
			return;
		}

		// Get the output handler instance
		$output_handler = Schema_Output_Handler::get_instance();

		// Register Pro schema builders
		$builders = Swift_Rank_Pro_Schema_Helper::get_pro_schema_builders();

		foreach ($builders as $type => $builder) {
			$output_handler->register_builder($type, $builder);
		}
	}

	/**
	 * Enable Pro schema types via PHP filter
	 *
	 * @param array $schema_types Schema types array.
	 * @return array Modified schema types.
	 */
	public function enable_pro_schema_types($schema_types)
	{
		$pro_types = array('PodcastEpisode', 'Recipe', 'Product');

		foreach ($schema_types as &$type) {
			if (in_array($type['value'], $pro_types, true)) {
				$type['isDisabled'] = false;
			}
		}

		return $schema_types;
	}

	/**
	 * Hide License menu item visually
	 */
	public function hide_license_menu_item()
	{
		?>
		<style>
			/* Hide only the License submenu item under Swift Rank parent menu */
			#adminmenu #toplevel_page_swift-rank .wp-submenu li a[href="admin.php?page=swift-rank-pro"],
			#adminmenu #toplevel_page_swift-rank .wp-submenu li a[href="admin.php?page=swift-rank-pro-account"] {
				display: none !important;
			}

			/* Hide the parent li if possible with :has */
			#adminmenu #toplevel_page_swift-rank .wp-submenu li:has(a[href="admin.php?page=swift-rank-pro"]),
			#adminmenu #toplevel_page_swift-rank .wp-submenu li:has(a[href="admin.php?page=swift-rank-pro-account"]) {
				display: none !important;
			}
		</style>
		<?php
	}
}
