<?php
/**
 * Schema Relationships REST API
 *
 * Provides REST API endpoints for fetching available relationship targets
 * (Users, Posts, Global schemas) for the React UI.
 *
 * @package Swift_Rank_Pro
 */

if (!defined('ABSPATH')) {
	exit;
}

/**
 * Schema_Relationships_API class
 */
class Schema_Relationships_API
{

	/**
	 * Initialize API endpoints
	 */
	public static function init()
	{
		add_action('rest_api_init', array(__CLASS__, 'register_routes'));
	}

	/**
	 * Register REST API routes
	 */
	public static function register_routes()
	{
		register_rest_route('swift-rank-pro/v1', '/entities', array(
			'methods' => 'GET',
			'callback' => array(__CLASS__, 'get_entities'),
			'permission_callback' => array(__CLASS__, 'check_permissions'),
			'args' => array(
				'type' => array(
					'required' => false,
					'type' => 'string',
					'description' => 'Filter by target type (Person, Organization, etc.)',
				),
				'search' => array(
					'required' => false,
					'type' => 'string',
					'description' => 'Search query for filtering results',
				),
			),
		));

		register_rest_route('swift-rank-pro/v1', '/users', array(
			'methods' => 'GET',
			'callback' => array(__CLASS__, 'get_users'),
			'permission_callback' => array(__CLASS__, 'check_permissions'),
			'args' => array(
				'search' => array(
					'required' => false,
					'type' => 'string',
					'description' => 'Search query for user names',
				),
			),
		));
	}

	/**
	 * Check permissions for API access
	 *
	 * @return bool True if user has permissions.
	 */
	public static function check_permissions()
	{
		return current_user_can('edit_posts');
	}

	/**
	 * Get available entities for relationships
	 *
	 * @param WP_REST_Request $request Request object.
	 * @return WP_REST_Response Response object.
	 */
	public static function get_entities($request)
	{
		$type_param = $request->get_param('type');
		$search = $request->get_param('search');

		// Parse comma-separated types
		$types = !empty($type_param) ? array_map('trim', explode(',', $type_param)) : array();

		$entities = array(
			'global' => self::get_global_entities($types),
			'users' => self::get_user_entities($search, $types),
			'schema_templates' => self::get_schema_template_entities($search, $types),
			'knowledge_base' => self::get_knowledge_base_entities($types),
			'media' => self::get_media_entities($search, $types),
		);

		return new WP_REST_Response($entities, 200);
	}

	/**
	 * Get users endpoint
	 *
	 * @param WP_REST_Request $request Request object.
	 * @return WP_REST_Response Response object.
	 */
	public static function get_users($request)
	{
		$search = $request->get_param('search');
		$users = self::get_user_entities($search);

		return new WP_REST_Response($users, 200);
	}

	/**
	 * Get global schema entities (Organization, Website, etc.)
	 *
	 * @param array $types Optional. Filter by types array.
	 * @return array Array of global entities.
	 */
	private static function get_global_entities($types = array())
	{
		return array();
	}

	/**
	 * Get user entities
	 *
	 * @param string $search Optional. Search query.
	 * @param array $types Optional. Filter by types array (only 'Person' for users).
	 * @return array Array of user entities.
	 */
	private static function get_user_entities($search = null, $types = array())
	{
		// If type filter is set and doesn't include Person, return empty
		if (!empty($types) && !in_array('Person', $types)) {
			return array();
		}

		$args = array(
			'number' => 50, // Limit for performance
			'orderby' => 'display_name',
			'order' => 'ASC',
		);

		// Add search if provided
		if (!empty($search)) {
			$args['search'] = '*' . $search . '*';
			$args['search_columns'] = array('user_login', 'user_nicename', 'display_name');
		}

		$users = get_users($args);
		$entities = array();

		foreach ($users as $user) {
			$entities[] = array(
				'id' => $user->ID,
				'label' => $user->display_name,
				'type' => 'Person',
				'source' => 'user',
				'icon' => 'user',
				'description' => $user->user_email,
			);
		}

		return $entities;
	}

	/**
	 * Get schema template entities
	 *
	 * @param string $search Optional. Search query.
	 * @param array $types Optional. Filter by types array (Person, Organization, etc.).
	 * @return array Array of schema template entities.
	 */
	private static function get_schema_template_entities($search = null, $types = array())
	{
		$args = array(
			'post_type' => 'sr_template',
			'post_status' => 'publish',
			'posts_per_page' => 50,
			'orderby' => 'title',
			'order' => 'ASC',
		);

		// Add search if provided
		if (!empty($search)) {
			$args['s'] = $search;
		}

		$templates = get_posts($args);
		$entities = array();

		foreach ($templates as $template) {
			$schema_type = get_post_meta($template->ID, '_schema_type', true);

			// Filter by type if specified
			if (!empty($types) && !in_array($schema_type, $types)) {
				continue;
			}

			$entities[] = array(
				'id' => $template->ID,
				'label' => $template->post_title,
				'type' => $schema_type,
				'source' => 'schema_template',
				'icon' => 'template',
				'description' => sprintf(__('Schema Template: %s', 'swift-rank-pro'), $schema_type),
			);
		}

		return $entities;
	}

	/**
	 * Get knowledge base entities
	 *
	 * @param array $types Optional. Filter by types array.
	 * @return array Array of knowledge base entities.
	 */
	private static function get_knowledge_base_entities($types = array())
	{
		$entities = array();

		// Get knowledge base settings
		$settings = get_option('swift_rank_settings', array());

		// Check if knowledge base is enabled
		$kb_enabled = isset($settings['knowledge_base_enabled']) ? $settings['knowledge_base_enabled'] : false;

		if (!$kb_enabled) {
			return $entities;
		}

		$kb_type = isset($settings['knowledge_base_type']) ? $settings['knowledge_base_type'] : 'Organization';

		// Filter by type if specified
		if (!empty($types) && !in_array($kb_type, $types)) {
			return $entities;
		}

		// Get name from appropriate fields based on type
		if ($kb_type === 'Person') {
			$person_fields = isset($settings['person_fields']) ? $settings['person_fields'] : array();
			$kb_name = isset($person_fields['name']) ? $person_fields['name'] : get_bloginfo('name');
		} elseif ($kb_type === 'LocalBusiness') {
			$business_fields = isset($settings['localbusiness_fields']) ? $settings['localbusiness_fields'] : array();
			$kb_name = isset($business_fields['name']) ? $business_fields['name'] : get_bloginfo('name');
		} else {
			$org_fields = isset($settings['organization_fields']) ? $settings['organization_fields'] : array();
			$kb_name = isset($org_fields['name']) ? $org_fields['name'] : get_bloginfo('name');
		}

		// Replace variables in the name for display
		$kb_name = self::replace_variables_in_string($kb_name);

		$entities[] = array(
			'id' => 'knowledge_base',
			'label' => $kb_name . ' (Knowledge Base)',
			'type' => $kb_type,
			'source' => 'knowledge_base',
			'icon' => $kb_type === 'Person' ? 'user' : 'building',
			'description' => sprintf(__('Knowledge Base %s', 'swift-rank-pro'), $kb_type),
		);

		return $entities;
	}

	/**
	 * Get media library entities (images)
	 *
	 * @param string $search Optional. Search query.
	 * @param array $types Optional. Filter by types array.
	 * @return array Array of media entities.
	 */
	private static function get_media_entities($search = null, $types = array())
	{
		// Only return media if types includes ImageObject or is empty
		if (!empty($types) && !in_array('ImageObject', $types)) {
			return array();
		}

		$args = array(
			'post_type' => 'attachment',
			'post_status' => 'inherit',
			'post_mime_type' => 'image',
			'posts_per_page' => 50,
			'orderby' => 'date',
			'order' => 'DESC',
		);

		// Add search if provided
		if (!empty($search)) {
			// Use a custom search filter for attachments to ensure we find them
			// WP_Query 's' parameter can be inconsistent for attachments
			add_filter('posts_where', array(__CLASS__, 'search_attachments_where'), 10, 2);
			$args['swift_rank_attachment_search'] = $search;
			$args['suppress_filters'] = false;
		}

		$attachments = get_posts($args);

		if (!empty($search)) {
			remove_filter('posts_where', array(__CLASS__, 'search_attachments_where'), 10);
		}

		$entities = array();

		foreach ($attachments as $attachment) {
			$url = wp_get_attachment_url($attachment->ID);

			$entities[] = array(
				'id' => $attachment->ID,
				'label' => $attachment->post_title ?: basename($url),
				'type' => 'ImageObject',
				'source' => 'media',
				'icon' => 'image',
				'description' => basename($url),
				'thumbnail' => wp_get_attachment_image_url($attachment->ID, 'thumbnail'),
			);
		}

		return $entities;
	}

	/**
	 * Custom WHERE clause for attachment search
	 *
	 * @param string $where WHERE clause.
	 * @param WP_Query $wp_query Query object.
	 * @return string Modified WHERE clause.
	 */
	public static function search_attachments_where($where, $wp_query)
	{
		$search = $wp_query->get('swift_rank_attachment_search');
		if ($search) {
			global $wpdb;
			$search = esc_sql($wpdb->esc_like($search));
			$where .= " AND ({$wpdb->posts}.post_title LIKE '%{$search}%' OR {$wpdb->posts}.post_content LIKE '%{$search}%' OR {$wpdb->posts}.post_excerpt LIKE '%{$search}%')";
		}
		return $where;
	}

	/**
	 * Replace variables in a string (for API display purposes)
	 *
	 * @param string $string String that may contain variables.
	 * @return string String with variables replaced.
	 */
	private static function replace_variables_in_string($string)
	{
		// If no variables detected, return as-is
		if (strpos($string, '{') === false) {
			return $string;
		}

		// Load variable replacer if not already loaded
		if (!class_exists('Schema_Variable_Replacer')) {
			require_once SWIFT_RANK_PLUGIN_DIR . 'includes/utils/class-schema-variable-replacer.php';
		}

		// Get replacer class (Pro plugin may override)
		$replacer_class = apply_filters('swift_rank_variable_replacer_class', 'Schema_Variable_Replacer');

		if (!class_exists($replacer_class)) {
			return $string;
		}

		// Create replacer instance
		$replacer = new $replacer_class();

		// Wrap string in JSON to use replace_variables method
		$json = wp_json_encode(array('value' => $string));
		$replaced_json = $replacer->replace_variables($json);
		$decoded = json_decode($replaced_json, true);

		return isset($decoded['value']) ? $decoded['value'] : $string;
	}
}

// Initialize the API
Schema_Relationships_API::init();
