<?php
/**
 * Paywall Fields Helper
 *
 * Provides common paywall field definitions for schema types that support paywalled content.
 * This helper ensures consistency across all types (Article, Video, Course, Book, Podcast, etc.)
 * and follows Google's structured data guidelines for paywalled content.
 *
 * @package Swift_Rank_Pro
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Paywall_Fields_Helper class
 *
 * Static helper class that provides standardized paywall field definitions
 * and automatically extends supported schema types with paywall fields.
 */
class Paywall_Fields_Helper
{

    /**
     * List of schema types that support paywall fields
     *
     * @var array
     */
    private static $supported_types = array(
        'Article',
        'BlogPosting',
        'NewsArticle',
        'ScholarlyArticle',
        'TechArticle',
        'VideoObject',
    );

    /**
     * Initialize the paywall fields extension
     *
     * Registers the hook to automatically add paywall fields to supported types.
     */
    public static function init()
    {
        // Hook into the schema fields filter at priority 15 (after main fields at priority 10)
        add_filter('swift_rank_get_fields', array(__CLASS__, 'extend_fields'), 15, 2);
    }

    /**
     * Extend schema fields with paywall fields for supported types
     *
     * This method automatically adds paywall fields to all supported schema types.
     *
     * @param array  $fields    Existing fields.
     * @param string $type_value Schema type.
     * @return array Modified fields.
     */
    public static function extend_fields($fields, $type_value)
    {
        // Only add paywall fields to supported types
        if (!in_array($type_value, self::$supported_types)) {
            return $fields;
        }

        // Add paywall fields
        $paywall_fields = self::get_paywall_fields();
        return array_merge($fields, $paywall_fields);
    }

    /**
     * Get common paywall field definitions
     *
     * Returns an array of field configurations for paywall support.
     * These fields can be inserted into any schema type's field array.
     *
     * Fields included:
     * - use_paywall: Checkbox to enable paywall markup
     * - isAccessibleForFree: Boolean select (True/False)
     * - paywall_css_selector: CSS selector for the paywalled section
     *
     * @return array Array of field configurations.
     */
    public static function get_paywall_fields()
    {
        return array(
            array(
                'name' => 'use_paywall',
                'label' => __('Enable Paywall Markup', 'swift-rank-pro'),
                'type' => 'checkbox',
                'tooltip' => __('Enable this to mark content as behind a paywall. This helps search engines understand that some content requires a subscription or payment to access.', 'swift-rank-pro'),
                'default' => false,
            ),
            array(
                'name' => 'isAccessibleForFree',
                'label' => __('Is Accessible For Free', 'swift-rank-pro'),
                'type' => 'select',
                'tooltip' => __('Indicates whether the content is freely accessible. Set to "False" for paywalled content. This property helps Google distinguish legitimate paywalls from cloaking.', 'swift-rank-pro'),
                'options' => array(
                    array(
                        'label' => __('Paywalled Content', 'swift-rank-pro'),
                        'value' => 'False',
                        'description' => __('Content requires payment or subscription to access', 'swift-rank-pro'),
                    ),
                    array(
                        'label' => __('Free Content', 'swift-rank-pro'),
                        'value' => 'True',
                        'description' => __('Content is freely accessible to everyone', 'swift-rank-pro'),
                    ),
                ),
                'default' => 'False',
                'dependsOn' => 'use_paywall',
                'showWhen' => true,
            ),
            array(
                'name' => 'paywall_css_selector',
                'label' => __('Paywall CSS Selector (Optional)', 'swift-rank-pro'),
                'type' => 'text',
                'tooltip' => __('CSS selector that identifies the paywalled section of your page (e.g., ".paywall-content" or "#premium-section"). Only needed for paywalled content to help search engines locate the restricted content. Leave empty for free content.', 'swift-rank-pro'),
                'placeholder' => '.paywall-content',
                'default' => '',
                'dependsOn' => 'use_paywall',
                'showWhen' => true,
            ),
        );
    }

    /**
     * Process paywall fields and add to schema output
     *
     * This method processes the paywall field values and adds the appropriate
     * Schema.org properties to the schema array.
     *
     * When paywall is enabled, it adds:
     * - isAccessibleForFree property
     * - hasPart array with WebPageElement containing cssSelector
     *
     * @param array $schema Schema array to modify.
     * @param array $fields Raw field values from meta.
     * @return array Modified schema array.
     */
    public static function process_paywall_schema($schema, $fields)
    {
        // Check if paywall is enabled
        $use_paywall = isset($fields['use_paywall']) && $fields['use_paywall'];

        if (!$use_paywall) {
            return $schema;
        }

        // Add isAccessibleForFree property
        $is_accessible_for_free = isset($fields['isAccessibleForFree']) ? $fields['isAccessibleForFree'] : 'False';
        $schema['isAccessibleForFree'] = $is_accessible_for_free;

        // Add hasPart with WebPageElement if CSS selector is provided
        $css_selector = isset($fields['paywall_css_selector']) ? trim($fields['paywall_css_selector']) : '';

        if (!empty($css_selector)) {
            // Initialize hasPart array if it doesn't exist
            if (!isset($schema['hasPart'])) {
                $schema['hasPart'] = array();
            }

            // Add WebPageElement for the paywalled section
            $schema['hasPart'][] = array(
                '@type' => 'WebPageElement',
                'isAccessibleForFree' => $is_accessible_for_free,
                'cssSelector' => $css_selector,
            );
        }

        return $schema;
    }
}

// Initialize the paywall fields extension
Paywall_Fields_Helper::init();
