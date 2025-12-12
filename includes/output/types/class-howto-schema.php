<?php
/**
 * HowTo Schema Builder
 *
 * @package Swift_Rank_Pro
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Schema_HowTo class
 *
 * Builds HowTo schema type.
 */
class Schema_HowTo implements Schema_Builder_Interface
{

    /**
     * Build HowTo schema from fields
     *
     * @param array $fields Field values.
     * @return array Schema array (without @context).
     */
    public function build($fields)
    {
        // Get field values with fallback to variables
        $name = !empty($fields['name']) ? $fields['name'] : '{post_title}';
        $url = !empty($fields['url']) ? $fields['url'] : '{post_url}';

        $schema = array(
            '@type' => 'HowTo',
            'name' => $name,
            'url' => $url,
        );

        // Description
        if (!empty($fields['description'])) {
            $schema['description'] = $fields['description'];
        }

        // Total time
        if (!empty($fields['totalTime'])) {
            $schema['totalTime'] = $fields['totalTime'];
        }

        // Image with fallback to featured_image variable
        $image_url = !empty($fields['imageUrl']) ? $fields['imageUrl'] : (!empty($fields['image']) ? $fields['image'] : '{featured_image}');
        if (!empty($image_url)) {
            $schema['image'] = $image_url;
        }

        // Steps
        if (!empty($fields['steps']) && is_array($fields['steps'])) {
            $schema['step'] = array();
            foreach ($fields['steps'] as $step) {
                if (!empty($step['text'])) {
                    $step_item = array(
                        '@type' => 'HowToStep',
                        'text' => $step['text'],
                    );

                    if (!empty($step['name'])) {
                        $step_item['name'] = $step['name'];
                    }

                    if (!empty($step['url'])) {
                        $step_item['url'] = $step['url'];
                    }

                    if (!empty($step['image'])) {
                        $step_item['image'] = $step['image'];
                    }

                    $schema['step'][] = $step_item;
                }
            }
        }

        // Tools
        if (!empty($fields['tools']) && is_array($fields['tools'])) {
            $schema['tool'] = array();
            foreach ($fields['tools'] as $tool) {
                if (!empty($tool['name'])) {
                    $schema['tool'][] = array(
                        '@type' => 'HowToTool',
                        'name' => $tool['name'],
                    );
                }
            }
        }

        // Supplies
        if (!empty($fields['supplies']) && is_array($fields['supplies'])) {
            $schema['supply'] = array();
            foreach ($fields['supplies'] as $supply) {
                if (!empty($supply['name'])) {
                    $schema['supply'][] = array(
                        '@type' => 'HowToSupply',
                        'name' => $supply['name'],
                    );
                }
            }
        }

        return $schema;
    }

    /**
     * Get schema.org structure for HowTo type
     *
     * @return array Schema.org structure specification.
     */
    public function get_schema_structure()
    {
        return array(
            '@type' => 'HowTo',
            '@context' => 'https://schema.org',
            'label' => __('How-To', 'swift-rank-pro'),
            'description' => __('Instructions that explain how to achieve a result by performing a sequence of steps.', 'swift-rank-pro'),
            'url' => 'https://schema.org/HowTo',
            'icon' => 'list-checks',
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
                'name' => 'name',
                'label' => __('How-To Name', 'swift-rank-pro'),
                'type' => 'select',
                'allowCustom' => true,
                'tooltip' => __('How-To name. Click pencil icon to use variables.', 'swift-rank-pro'),
                'placeholder' => '{post_title}',
                'options' => array(
                    array(
                        'label' => __('Post Title', 'swift-rank-pro'),
                        'value' => '{post_title}',
                    ),
                ),
                'default' => '{post_title}',
                'required' => true,
            ),
            array(
                'name' => 'url',
                'label' => __('URL', 'swift-rank-pro'),
                'type' => 'select',
                'allowCustom' => true,
                'tooltip' => __('How-To URL. Click pencil icon to enter custom URL.', 'swift-rank-pro'),
                'placeholder' => '{post_url}',
                'options' => array(
                    array(
                        'label' => __('Post URL', 'swift-rank-pro'),
                        'value' => '{post_url}',
                    ),
                ),
                'default' => '{post_url}',
            ),
            array(
                'name' => 'description',
                'label' => __('Description', 'swift-rank-pro'),
                'type' => 'select',
                'allowCustom' => true,
                'customType' => 'textarea',
                'rows' => 4,
                'tooltip' => __('How-To description. Click pencil icon to use variables.', 'swift-rank-pro'),
                'placeholder' => '{post_excerpt}',
                'options' => array(
                    array(
                        'label' => __('Post Excerpt', 'swift-rank-pro'),
                        'value' => '{post_excerpt}',
                    ),
                    array(
                        'label' => __('Post Content', 'swift-rank-pro'),
                        'value' => '{post_content}',
                    ),
                ),
                'default' => '{post_excerpt}',
            ),
            array(
                'name' => 'imageUrl',
                'label' => __('Image URL', 'swift-rank-pro'),
                'type' => 'select',
                'allowCustom' => true,
                'tooltip' => __('How-To image. Click pencil icon to enter custom URL.', 'swift-rank-pro'),
                'placeholder' => '{featured_image}',
                'options' => array(
                    array(
                        'label' => __('Featured Image', 'swift-rank-pro'),
                        'value' => '{featured_image}',
                    ),
                ),
                'default' => '{featured_image}',
                'required' => true,
            ),
            array(
                'name' => 'totalTime',
                'label' => __('Total Time', 'swift-rank-pro'),
                'type' => 'text',
                'tooltip' => __('Total time required in ISO 8601 duration format (e.g., PT30M for 30 minutes, PT2H for 2 hours).', 'swift-rank-pro'),
                'placeholder' => 'PT30M',
            ),
            array(
                'name' => 'steps',
                'label' => __('Steps', 'swift-rank-pro'),
                'type' => 'repeater',
                'tooltip' => __('Add the steps required to complete this how-to.', 'swift-rank-pro'),
                'required' => true,
                'fields' => array(
                    array(
                        'name' => 'name',
                        'label' => __('Step Name', 'swift-rank-pro'),
                        'type' => 'select',
                        'allowCustom' => true,
                        'options' => [],
                        'placeholder' => __('Step 1: Prepare ingredients', 'swift-rank-pro'),
                    ),
                    array(
                        'name' => 'text',
                        'label' => __('Step Instructions', 'swift-rank-pro'),
                        'type' => 'select',
                        'allowCustom' => true,
                        'customType' => 'textarea',
                        'rows' => 3,
                        'options' => [],
                        'placeholder' => __('Detailed instructions for this step', 'swift-rank-pro'),
                        'required' => true,
                    ),
                    array(
                        'name' => 'image',
                        'label' => __('Step Image', 'swift-rank-pro'),
                        'type' => 'select',
                        'allowCustom' => true,
                        'options' => [],
                        'tooltip' => __('Step image URL.', 'swift-rank-pro'),
                    ),
                    array(
                        'name' => 'url',
                        'label' => __('Step URL', 'swift-rank-pro'),
                        'type' => 'select',
                        'allowCustom' => true,
                        'options' => [],
                        'tooltip' => __('Optional URL with more details.', 'swift-rank-pro'),
                    ),
                ),
            ),
            array(
                'name' => 'tools',
                'label' => __('Tools', 'swift-rank-pro'),
                'type' => 'repeater',
                'tooltip' => __('Tools needed to complete this how-to.', 'swift-rank-pro'),
                'fields' => array(
                    array(
                        'name' => 'name',
                        'label' => __('Tool Name', 'swift-rank-pro'),
                        'type' => 'select',
                        'allowCustom' => true,
                        'options' => [],
                        'placeholder' => __('Hammer', 'swift-rank-pro'),
                        'required' => true,
                    ),
                ),
            ),
            array(
                'name' => 'supplies',
                'label' => __('Supplies', 'swift-rank-pro'),
                'type' => 'repeater',
                'tooltip' => __('Supplies consumed when performing this how-to.', 'swift-rank-pro'),
                'fields' => array(
                    array(
                        'name' => 'name',
                        'label' => __('Supply Name', 'swift-rank-pro'),
                        'type' => 'select',
                        'allowCustom' => true,
                        'options' => [],
                        'placeholder' => __('Wood glue', 'swift-rank-pro'),
                        'required' => true,
                    ),
                ),
            ),
        );
    }

}
