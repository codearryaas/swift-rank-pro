<?php
/**
 * Article Schema Pro Extension
 *
 * Extends the base Article schema with Pro features like relationship fields and paywall support.
 *
 * @package Swift_Rank_Pro
 */

if (!defined('ABSPATH')) {
	exit;
}

// Load the paywall fields helper
require_once dirname(__FILE__) . '/class-paywall-fields-helper.php';

/**
 * Schema_Article_Pro class
 */
class Schema_Article_Pro
{

	/**
	 * Initialize the pro extension
	 */
	public static function init()
	{
		// Hook into the schema fields filter for relationship fields
		add_filter('swift_rank_get_fields', array(__CLASS__, 'extend_article_fields'), 10, 2);

		// Hook into the Article schema build process to handle relationships
		// Priority 5 so it runs before variable replacement
		add_filter('swift_rank_output_schema', array(__CLASS__, 'process_article_relationships'), 5, 3);
	}

	/**
	 * Extend Article schema fields with relationship fields
	 *
	 * @param array  $fields    Existing fields.
	 * @param string $type_value Schema type (Article, Product, etc.).
	 * @return array Modified fields.
	 */
	public static function extend_article_fields($fields, $type_value)
	{
		// Only modify Article schema fields
		if ($type_value !== 'Article' && $type_value !== 'BlogPosting' && $type_value !== 'NewsArticle') {
			return $fields;
		}

		// Add Author Reference Fields
		foreach ($fields as $index => $field) {
			if (isset($field['name']) && $field['name'] === 'authorName') {
				// Add toggle checkbox before the author fields
				$use_author_reference_field = array(
					'name' => 'use_author_reference',
					'label' => __('Use Author Reference', 'swift-rank-pro'),
					'type' => 'checkbox',
					'tooltip' => __('Enable to link to a WordPress user or Person entity. Disable to use custom text or variables.', 'swift-rank-pro'),
					'default' => false,
				);

				// Add Pro reference field (will be shown when checkbox is enabled)
				$author_reference_field = array(
					'name' => 'author_reference',
					'label' => __('Author Reference', 'swift-rank-pro'),
					'type' => 'schema_reference',
					'tooltip' => __('Link to a Person entity. Select from WordPress users, global Person schemas, schema templates, or knowledge base.', 'swift-rank-pro'),
					'targets' => array('Person'), // Only allow Person type
					'sources' => array('users', 'schema_templates', 'knowledge_base'), // Users, Templates, KB
					'required' => false,
					'placeholder' => __('Select Author...', 'swift-rank-pro'),
					'dependsOn' => 'use_author_reference',
					'showWhen' => true, // Show when checkbox is true
				);

				// Update the original authorName field to show when checkbox is disabled
				$fields[$index]['dependsOn'] = 'use_author_reference';
				$fields[$index]['showWhen'] = false; // Show when checkbox is false

				// Insert the new fields before authorName
				array_splice($fields, $index, 0, array($use_author_reference_field, $author_reference_field));

				// Update authorUrl - hide it when checkbox is checked (reference mode)
				foreach ($fields as $url_index => $url_field) {
					if (isset($url_field['name']) && $url_field['name'] === 'authorUrl') {
						$fields[$url_index]['dependsOn'] = 'use_author_reference';
						$fields[$url_index]['showWhen'] = false; // Show when checkbox is false (same as authorName)
						$fields[$url_index]['tooltip'] = __('Author URL. Only visible when using custom text mode.', 'swift-rank-pro');
						break;
					}
				}

				break;
			}
		}

		// Add Publisher Reference Fields
		foreach ($fields as $index => $field) {
			if (isset($field['name']) && $field['name'] === 'publisherName') {
				// Add toggle checkbox before the publisher fields
				$use_publisher_reference_field = array(
					'name' => 'use_publisher_reference',
					'label' => __('Use Publisher Reference', 'swift-rank-pro'),
					'type' => 'checkbox',
					'tooltip' => __('Enable to link to an Organization or LocalBusiness entity. Disable to use custom text or variables.', 'swift-rank-pro'),
					'default' => false,
				);

				// Add Pro reference field (will be shown when checkbox is enabled)
				$publisher_reference_field = array(
					'name' => 'publisher_reference',
					'label' => __('Publisher Reference', 'swift-rank-pro'),
					'type' => 'schema_reference',
					'tooltip' => __('Link to an Organization or LocalBusiness entity. Select from knowledge base or schema templates.', 'swift-rank-pro'),
					'targets' => array('Organization', 'LocalBusiness'), // Allow Organization and LocalBusiness types
					'sources' => array('knowledge_base', 'schema_templates'), // KB and Templates
					'required' => false,
					'placeholder' => __('Select Publisher...', 'swift-rank-pro'),
					'dependsOn' => 'use_publisher_reference',
					'showWhen' => true, // Show when checkbox is true
				);

				// Update the original publisherName field to show when checkbox is disabled
				$fields[$index]['dependsOn'] = 'use_publisher_reference';
				$fields[$index]['showWhen'] = false; // Show when checkbox is false

				// Insert the new fields before publisherName
				array_splice($fields, $index, 0, array($use_publisher_reference_field, $publisher_reference_field));

				// Update publisherLogo - hide it when checkbox is checked (reference mode)
				foreach ($fields as $logo_index => $logo_field) {
					if (isset($logo_field['name']) && $logo_field['name'] === 'publisherLogo') {
						$fields[$logo_index]['dependsOn'] = 'use_publisher_reference';
						$fields[$logo_index]['showWhen'] = false; // Show when checkbox is false (same as publisherName)
						$fields[$logo_index]['tooltip'] = __('Publisher Logo. Only visible when using custom text mode.', 'swift-rank-pro');
						break;
					}
				}

				break;
			}
		}

		// Add Image Reference Fields
		foreach ($fields as $index => $field) {
			if (isset($field['name']) && $field['name'] === 'imageUrl') {
				// Add toggle checkbox before the image fields
				$use_image_reference_field = array(
					'name' => 'use_image_reference',
					'label' => __('Use Image Reference', 'swift-rank-pro'),
					'type' => 'checkbox',
					'tooltip' => __('Enable to link to an ImageObject entity. Disable to use custom URL or variables.', 'swift-rank-pro'),
					'default' => false,
				);

				// Add Pro reference field (will be shown when checkbox is enabled)
				$image_reference_field = array(
					'name' => 'image_reference',
					'label' => __('Image Reference', 'swift-rank-pro'),
					'type' => 'schema_reference',
					'tooltip' => __('Link to an ImageObject entity. Select from knowledge base or schema templates.', 'swift-rank-pro'),
					'targets' => array('ImageObject'), // Allow ImageObject types
					'sources' => array('knowledge_base', 'schema_templates', 'media'), // KB, Templates, Media
					'required' => false,
					'placeholder' => __('Select Image Object...', 'swift-rank-pro'),
					'dependsOn' => 'use_image_reference',
					'showWhen' => true, // Show when checkbox is true
				);

				// Update the original imageUrl field to show when checkbox is disabled
				$fields[$index]['dependsOn'] = 'use_image_reference';
				$fields[$index]['showWhen'] = false; // Show when checkbox is false
				$fields[$index]['tooltip'] = __('Article image. Only visible when using custom text mode.', 'swift-rank-pro');

				// Insert the new fields before imageUrl
				array_splice($fields, $index, 0, array($use_image_reference_field, $image_reference_field));

				break;
			}
		}

		return $fields;
	}

	/**
	 * Process Article schema relationships before output
	 *
	 * This runs AFTER the base schema builder, so the schema is already constructed.
	 * We check if the user enabled reference mode and if so, resolve the author_reference
	 * or publisher_reference fields and replace them with @id references.
	 *
	 * @param array  $schema      Schema data.
	 * @param string $schema_type Schema type.
	 * @param array  $fields      Raw field values from meta.
	 * @return array Processed schema.
	 */
	public static function process_article_relationships($schema, $schema_type, $fields)
	{
		// Only process Article-type schemas
		if (!isset($schema['@type']) || !in_array($schema['@type'], array('Article', 'BlogPosting', 'NewsArticle', 'ScholarlyArticle', 'TechArticle'))) {
			return $schema;
		}

		// Process Author Reference
		$use_author_reference = isset($fields['use_author_reference']) && $fields['use_author_reference'];

		if ($use_author_reference && isset($fields['author_reference'])) {
			// Check if author_reference is a reference object
			if (Schema_Reference_Resolver::is_reference($fields['author_reference'])) {
				// Resolve the reference to get @id
				$resolved_author = Schema_Reference_Resolver::resolve($fields['author_reference']);

				if ($resolved_author !== null) {
					// Replace the entire author object with @id reference
					$schema['author'] = $resolved_author;
				}
			}
		}
		// Otherwise, the base builder uses authorName field (custom text/variable)

		// Process Publisher Reference
		$use_publisher_reference = isset($fields['use_publisher_reference']) && $fields['use_publisher_reference'];

		if ($use_publisher_reference && isset($fields['publisher_reference'])) {
			// Check if publisher_reference is a reference object
			if (Schema_Reference_Resolver::is_reference($fields['publisher_reference'])) {
				// Resolve the reference to get @id
				$resolved_publisher = Schema_Reference_Resolver::resolve($fields['publisher_reference']);

				if ($resolved_publisher !== null) {
					// Replace the entire publisher object with @id reference
					$schema['publisher'] = $resolved_publisher;
				}
			}
		}
		// Otherwise, the base builder uses publisherName field (custom text/variable)

		// Process Image Reference
		$use_image_reference = isset($fields['use_image_reference']) && $fields['use_image_reference'];

		if ($use_image_reference && isset($fields['image_reference'])) {
			// Check if image_reference is a reference object
			if (Schema_Reference_Resolver::is_reference($fields['image_reference'])) {
				// Resolve the reference to get @id
				$resolved_image = Schema_Reference_Resolver::resolve($fields['image_reference']);

				if ($resolved_image !== null) {
					// Replace the entire image field with @id reference
					$schema['image'] = $resolved_image;
				}
			}
		}

		// Process Paywall Fields
		$schema = Paywall_Fields_Helper::process_paywall_schema($schema, $fields);

		return $schema;
	}
}

// Initialize the Article Pro extension
Schema_Article_Pro::init();
