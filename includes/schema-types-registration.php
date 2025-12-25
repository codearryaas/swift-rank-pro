<?php
/**
 * Schema Types Registration for Pro Plugin
 *
 * Registers all schema types from the Pro plugin.
 * This file hooks into the swift_rank_register_types filter.
 *
 * @package Swift_Rank_Pro
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Register Pro plugin schema types
 *
 * @param array $types Existing schema types.
 * @return array Modified schema types.
 */
function swift_rank_pro_register_types($types)
{
    // Check if Pro plugin constant is defined
    if (!defined('SWIFT_RANK_PRO_PLUGIN_DIR')) {
        return $types;
    }

    // Register Pro schema types
    $pro_builders = Swift_Rank_Pro_Schema_Helper::get_pro_schema_builders();

    foreach ($pro_builders as $type_value => $builder) {
        if (method_exists($builder, 'get_schema_structure')) {
            $structure = $builder->get_schema_structure();
            $description = isset($structure['description']) ? $structure['description'] : '';
            $label = isset($structure['label']) ? $structure['label'] : $type_value;

            // Get fields from the builder if method exists
            $fields = array();
            if (method_exists($builder, 'get_fields')) {
                $fields = $builder->get_fields();
            }

            // Check if schema type should be hidden from dropdown
            $show_in_dropdown = isset($structure['showInDropdown']) ? $structure['showInDropdown'] : true;
            if (!$show_in_dropdown) {
                continue;
            }

            $types[$type_value] = array(
                'label' => $label,
                'value' => $type_value,
                'description' => $description,
                'icon' => isset($structure['icon']) ? $structure['icon'] : '',
                'isPro' => true,
                'isDisabled' => false,
                'structure' => $structure,
                'fields' => $fields,
            );
        }
    }

    return $types;
}
add_filter('swift_rank_register_types', 'swift_rank_pro_register_types', 20);

/**
 * Register Pro plugin schema subtypes
 *
 * @param array $subtypes Existing schema subtypes.
 * @return array Modified schema subtypes.
 */
function swift_rank_pro_register_subtypes($subtypes)
{
    // Check if Pro plugin constant is defined
    if (!defined('SWIFT_RANK_PRO_PLUGIN_DIR')) {
        return $subtypes;
    }

    $types_dir = SWIFT_RANK_PRO_PLUGIN_DIR . 'includes/output/types/';

    // Manually load Pro schema type builders
    require_once $types_dir . 'class-recipe-schema.php';
    require_once $types_dir . 'class-podcast-episode-schema.php';
    require_once $types_dir . 'class-event-schema.php';
    require_once $types_dir . 'class-howto-schema.php';
    require_once $types_dir . 'class-software-application-schema.php';
    require_once $types_dir . 'class-custom-schema.php';

    // Register Pro schema subtypes
    $pro_builders = array(
        new Schema_Recipe(),
        new Schema_Podcast_Episode(),
        new Schema_Event(),
        new Schema_Howto(),
        new Schema_Software_Application(),
        new Schema_Custom(),
    );

    foreach ($pro_builders as $builder) {
        if (method_exists($builder, 'get_schema_structure')) {
            $structure = $builder->get_schema_structure();

            // Get parent type
            $parent_type = isset($structure['@type']) ? $structure['@type'] : '';

            if ($parent_type) {
                // Initialize parent type array if not exists
                if (!isset($subtypes[$parent_type])) {
                    $subtypes[$parent_type] = array();
                }

                // Add main type as a subtype option
                $subtypes[$parent_type][$parent_type] = $parent_type;

                // Add subtypes if defined
                if (isset($structure['subtypes']) && is_array($structure['subtypes'])) {
                    foreach ($structure['subtypes'] as $subtype_value => $subtype_description) {
                        // Format label nicely (e.g., LocalBusiness -> Local Business)
                        $label = preg_replace('/(?<!^)([A-Z])/', ' $1', $subtype_value);
                        $subtypes[$parent_type][$subtype_value] = $label;
                    }
                }
            }
        }
    }

    return $subtypes;
}
add_filter('swift_rank_register_subtypes', 'swift_rank_pro_register_subtypes', 20);

/**
 * Reorder PRO schema types by popularity
 *
 * @param array $types Existing schema types.
 * @return array Reordered schema types.
 */
function swift_rank_pro_reorder_types($types)
{
    // Define popular order for PRO types only
    $pro_order = array(
        'Recipe',
        'Event',
        'SoftwareApplication',
        'HowTo',
        'PodcastEpisode',
        'Custom', // Always last
    );

    $ordered_types = array();
    $pro_types = array();

    // Separate Pro types from the rest
    foreach ($pro_order as $type_key) {
        if (isset($types[$type_key])) {
            $pro_types[$type_key] = $types[$type_key];
            unset($types[$type_key]);
        }
    }

    // Keep all non-Pro types in their current order (already sorted by Free plugin)
    $ordered_types = $types;

    // Append Pro types at the end in popularity order
    foreach ($pro_types as $key => $value) {
        $ordered_types[$key] = $value;
    }

    return $ordered_types;
}
add_filter('swift_rank_register_types', 'swift_rank_pro_reorder_types', 30);

