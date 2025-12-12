<?php
/**
 * Custom Schema Builder
 *
 * @package Swift_Rank_Pro
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Schema_Custom class
 */
class Schema_Custom
{

    /**
     * Build schema
     *
     * @param array $fields Field values.
     * @return array
     */
    public function build($fields)
    {
        // The custom schema structure is stored in $fields['custom_structure']
        // It should be a JSON string or array representing the schema
        if (empty($fields['custom_structure'])) {
            return array();
        }

        $structure = $fields['custom_structure'];

        if (is_string($structure)) {
            $structure = json_decode($structure, true);
        }

        if (!is_array($structure)) {
            return array();
        }

        // Ensure context is set
        if (!isset($structure['@context'])) {
            $structure['@context'] = 'https://schema.org';
        }

        return $structure;
    }

    /**
     * Get schema structure
     *
     * @return array
     */
    public function get_schema_structure()
    {
        return array(
            '@type' => 'Custom',
            'label' => __('Custom Schema', 'swift-rank-pro'),
            'description' => __('Build your own custom schema structure visually.', 'swift-rank-pro'),
            'icon' => 'code',
        );
    }

    /**
     * Get field definitions for the admin UI
     *
     * @return array Array of field configurations for React components.
     */
    public function get_fields()
    {
        return array(
            array(
                'name' => 'custom_structure',
                'type' => 'custom_builder',
                'label' => __('Schema Structure', 'swift-rank-pro'),
                'description' => __('Use the builder to create your schema.', 'swift-rank-pro'),
            ),
        );
    }
}
