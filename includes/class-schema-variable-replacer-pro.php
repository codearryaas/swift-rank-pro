<?php
/**
 * Schema Variable Replacer Pro Extension
 *
 * Extends the base variable replacer to add Pro-specific variables.
 *
 * @package Swift_Rank_Pro
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Schema_Variable_Replacer_Pro class
 *
 * Extends base variable replacer with Pro-specific variables.
 */
class Schema_Variable_Replacer_Pro extends Schema_Variable_Replacer
{

    /**
     * Register variable groups
     *
     * Extends parent to add Pro variables.
     */
    protected function register_variable_groups()
    {
        // Register base variables first
        parent::register_variable_groups();

        // Add Categories & Tags group (Pro feature)
        $this->register_group('taxonomy', array(
            'label' => __('Categories & Tags', 'swift-rank-pro'),
            'icon' => 'category',
            'variables' => array(
                array(
                    'value' => '{categories}',
                    'label' => __('Categories', 'swift-rank-pro'),
                    'description' => __('Comma-separated list of categories', 'swift-rank-pro'),
                ),
                array(
                    'value' => '{primary_category}',
                    'label' => __('Primary Category', 'swift-rank-pro'),
                    'description' => __('First/primary category name', 'swift-rank-pro'),
                ),
                array(
                    'value' => '{tags}',
                    'label' => __('Tags', 'swift-rank-pro'),
                    'description' => __('Comma-separated list of tags', 'swift-rank-pro'),
                ),
            ),
        ));

        // Add Custom & Advanced group (Pro feature)
        $this->register_group('custom', array(
            'label' => __('Custom', 'swift-rank-pro'),
            'icon' => 'admin-generic',
            'variables' => array(
                array(
                    'value' => '{meta:field_name}',
                    'label' => __('Custom Field', 'swift-rank-pro'),
                    'description' => __('Replace field_name with your meta key', 'swift-rank-pro'),
                ),
                array(
                    'value' => '{option:option_name}',
                    'label' => __('WP Option', 'swift-rank-pro'),
                    'description' => __('Replace option_name with option key', 'swift-rank-pro'),
                ),
                array(
                    'value' => '{acf:field_name}',
                    'label' => __('ACF Field', 'swift-rank-pro'),
                    'description' => __('Advanced Custom Fields value', 'swift-rank-pro'),
                ),
                array(
                    'value' => '{term_meta:key}',
                    'label' => __('Term Meta', 'swift-rank-pro'),
                    'description' => __('Taxonomy term meta value', 'swift-rank-pro'),
                ),
                array(
                    'value' => '{user_meta:key}',
                    'label' => __('User Meta', 'swift-rank-pro'),
                    'description' => __('Current user meta value', 'swift-rank-pro'),
                ),
            ),
        ));

        // Add Date/Time group (Pro feature)
        $this->register_group('datetime', array(
            'label' => __('Date/Time', 'swift-rank-pro'),
            'icon' => 'clock',
            'variables' => array(
                array(
                    'value' => '{current_date}',
                    'label' => __('Current Date', 'swift-rank-pro'),
                    'description' => __('Current date in ISO format', 'swift-rank-pro'),
                ),
                array(
                    'value' => '{current_year}',
                    'label' => __('Current Year', 'swift-rank-pro'),
                    'description' => __('Current year (e.g., 2024)', 'swift-rank-pro'),
                ),
                array(
                    'value' => '{current_month}',
                    'label' => __('Current Month', 'swift-rank-pro'),
                    'description' => __('Current month name', 'swift-rank-pro'),
                ),
                array(
                    'value' => '{current_time}',
                    'label' => __('Current Time', 'swift-rank-pro'),
                    'description' => __('Current time (HH:MM:SS)', 'swift-rank-pro'),
                ),
            ),
        ));

        // Add User group (Pro feature)
        $this->register_group('user', array(
            'label' => __('User', 'swift-rank-pro'),
            'icon' => 'admin-users',
            'variables' => array(
                array(
                    'value' => '{user_display_name}',
                    'label' => __('Display Name', 'swift-rank-pro'),
                    'description' => __('User display name', 'swift-rank-pro'),
                ),
                array(
                    'value' => '{user_firstname}',
                    'label' => __('First Name', 'swift-rank-pro'),
                    'description' => __('User first name', 'swift-rank-pro'),
                ),
                array(
                    'value' => '{user_lastname}',
                    'label' => __('Last Name', 'swift-rank-pro'),
                    'description' => __('User last name', 'swift-rank-pro'),
                ),
                array(
                    'value' => '{user_email}',
                    'label' => __('Email', 'swift-rank-pro'),
                    'description' => __('User email address', 'swift-rank-pro'),
                ),
                array(
                    'value' => '{user_id}',
                    'label' => __('User ID', 'swift-rank-pro'),
                    'description' => __('User numeric ID', 'swift-rank-pro'),
                ),
                array(
                    'value' => '{user_description}',
                    'label' => __('Biographical Info', 'swift-rank-pro'),
                    'description' => __('User description/bio', 'swift-rank-pro'),
                ),
                array(
                    'value' => '{user_url}',
                    'label' => __('Website', 'swift-rank-pro'),
                    'description' => __('User website URL', 'swift-rank-pro'),
                ),
                array(
                    'value' => '{user_nicename}',
                    'label' => __('Nicename', 'swift-rank-pro'),
                    'description' => __('User nicename (slug)', 'swift-rank-pro'),
                ),
            ),
        ));

        // Add Pro-exclusive WooCommerce variables to the existing WooCommerce group from Free plugin
        if (class_exists('WooCommerce') && isset($this->variable_groups['woocommerce'])) {
            // Add Pro-exclusive variables to the WooCommerce group
            $pro_variables = array(
                array(
                    'value' => '{woo_product_stock_quantity}',
                    'label' => __('Stock Quantity', 'swift-rank-pro'),
                    'description' => __('Available stock quantity', 'swift-rank-pro'),
                ),
                array(
                    'value' => '{woo_product_rating}',
                    'label' => __('Average Rating', 'swift-rank-pro'),
                    'description' => __('Product average rating (0-5)', 'swift-rank-pro'),
                ),
                array(
                    'value' => '{woo_product_review_count}',
                    'label' => __('Review Count', 'swift-rank-pro'),
                    'description' => __('Total number of reviews', 'swift-rank-pro'),
                ),
                array(
                    'value' => '{woo_product_categories}',
                    'label' => __('Product Categories', 'swift-rank-pro'),
                    'description' => __('Comma-separated product categories', 'swift-rank-pro'),
                ),
                array(
                    'value' => '{woo_product_tags}',
                    'label' => __('Product Tags', 'swift-rank-pro'),
                    'description' => __('Comma-separated product tags', 'swift-rank-pro'),
                ),
                array(
                    'value' => '{woo_product_gtin}',
                    'label' => __('GTIN', 'swift-rank-pro'),
                    'description' => __('Global Trade Item Number', 'swift-rank-pro'),
                ),
                array(
                    'value' => '{woo_product_mpn}',
                    'label' => __('MPN', 'swift-rank-pro'),
                    'description' => __('Manufacturer Part Number', 'swift-rank-pro'),
                ),
            );

            // Merge Pro variables into the existing WooCommerce group
            $this->variable_groups['woocommerce']['variables'] = array_merge(
                $this->variable_groups['woocommerce']['variables'],
                $pro_variables
            );
        }
    }

    /**
     * Get all variable replacements
     * 
     * Overrides parent to include user replacements.
     *
     * @return array
     */
    protected function get_replacements()
    {
        $replacements = parent::get_replacements();
        $replacements = array_merge($replacements, $this->get_user_replacements());
        return $replacements;
    }

    /**
     * Get user-specific variable replacements
     *
     * @return array
     */
    protected function get_user_replacements()
    {
        $user = null;

        if (is_author()) {
            $user = get_queried_object();
        } elseif (is_user_logged_in()) {
            $user = wp_get_current_user();
        }

        if (!$user || !($user instanceof WP_User)) {
            return array(
                '{user_display_name}' => '',
                '{user_firstname}' => '',
                '{user_lastname}' => '',
                '{user_email}' => '',
                '{user_id}' => '',
                '{user_description}' => '',
                '{user_url}' => '',
                '{user_nicename}' => '',
            );
        }

        return array(
            '{user_display_name}' => $user->display_name,
            '{user_firstname}' => $user->first_name,
            '{user_lastname}' => $user->last_name,
            '{user_email}' => $user->user_email,
            '{user_id}' => (string) $user->ID,
            '{user_description}' => $user->description,
            '{user_url}' => $user->user_url,
            '{user_nicename}' => $user->user_nicename,
        );
    }

    /**
     * Get Pro post-level variable replacements
     *
     * @param WP_Post $post Post object.
     * @return array
     */
    protected function get_post_replacements($post)
    {
        // Get base replacements
        $replacements = parent::get_post_replacements($post);

        // Add Pro-specific post variables
        $categories = get_the_category($post->ID);
        $replacements['{categories}'] = !empty($categories) ? implode(', ', wp_list_pluck($categories, 'name')) : '';
        $replacements['{primary_category}'] = !empty($categories) ? $categories[0]->name : '';

        $tags = get_the_tags($post->ID);
        $replacements['{tags}'] = !empty($tags) ? implode(', ', wp_list_pluck($tags, 'name')) : '';

        // Add WooCommerce Pro variables (Pro-exclusive only - basic variables are in Free plugin)
        if (class_exists('WooCommerce') && function_exists('wc_get_product')) {
            $product = wc_get_product($post->ID);

            if ($product) {
                // Pro-exclusive: Stock quantity
                $stock_quantity = $product->get_stock_quantity();
                $replacements['{woo_product_stock_quantity}'] = $stock_quantity !== null ? (string) $stock_quantity : '';

                // Pro-exclusive: Ratings & Reviews
                $replacements['{woo_product_rating}'] = $product->get_average_rating() ? (string) $product->get_average_rating() : '';
                $replacements['{woo_product_review_count}'] = $product->get_review_count() ? (string) $product->get_review_count() : '';

                // Pro-exclusive: Product Categories
                $product_cats = get_the_terms($post->ID, 'product_cat');
                $replacements['{woo_product_categories}'] = !empty($product_cats) ? implode(', ', wp_list_pluck($product_cats, 'name')) : '';

                // Pro-exclusive: Product Tags
                $product_tags = get_the_terms($post->ID, 'product_tag');
                $replacements['{woo_product_tags}'] = !empty($product_tags) ? implode(', ', wp_list_pluck($product_tags, 'name')) : '';

                // Pro-exclusive: GTIN (Global Trade Item Number)
                $gtin = get_post_meta($post->ID, '_wc_gtin', true);
                if (empty($gtin)) {
                    $gtin = get_post_meta($post->ID, 'gtin', true);
                }
                $replacements['{woo_product_gtin}'] = $gtin ? $gtin : '';

                // Pro-exclusive: MPN (Manufacturer Part Number)
                $mpn = get_post_meta($post->ID, '_wc_mpn', true);
                if (empty($mpn)) {
                    $mpn = get_post_meta($post->ID, 'mpn', true);
                }
                $replacements['{woo_product_mpn}'] = $mpn ? $mpn : '';
            } else {
                // Not a product - return empty values
                $replacements['{woo_product_stock_quantity}'] = '';
                $replacements['{woo_product_rating}'] = '';
                $replacements['{woo_product_review_count}'] = '';
                $replacements['{woo_product_categories}'] = '';
                $replacements['{woo_product_tags}'] = '';
                $replacements['{woo_product_gtin}'] = '';
                $replacements['{woo_product_mpn}'] = '';
            }
        }

        return $replacements;
    }

    /**
     * Get site-level variable replacements
     *
     * @return array
     */
    protected function get_site_replacements()
    {
        // Get base replacements
        $replacements = parent::get_site_replacements();

        // Add Pro date/time variables
        $replacements['{current_date}'] = current_time('c');
        $replacements['{current_year}'] = current_time('Y');
        $replacements['{current_month}'] = current_time('F');
        $replacements['{current_time}'] = current_time('H:i:s');

        return $replacements;
    }

    /**
     * Replace dynamic variables (option, meta, etc.)
     *
     * Extends parent to add Pro-specific dynamic variables.
     *
     * @param string $json JSON string.
     * @return string
     */
    protected function replace_dynamic_variables($json)
    {
        global $post;

        // Call parent method first
        $json = parent::replace_dynamic_variables($json);

        // Replace {acf:field_name} variables
        if ($post && function_exists('get_field')) {
            $json = preg_replace_callback(
                '/\{acf:([a-zA-Z0-9_-]+)\}/',
                function ($matches) use ($post) {
                    $field_name = $matches[1];
                    $field_value = get_field($field_name, $post->ID);
                    return $field_value ? $field_value : $matches[0];
                },
                $json
            );
        }

        // Replace {term_meta:key} variables
        if (is_tax() || is_category() || is_tag()) {
            $term = get_queried_object();
            if ($term) {
                $json = preg_replace_callback(
                    '/\{term_meta:([a-zA-Z0-9_-]+)\}/',
                    function ($matches) use ($term) {
                        $meta_key = $matches[1];
                        $meta_value = get_term_meta($term->term_id, $meta_key, true);
                        return $meta_value ? $meta_value : $matches[0];
                    },
                    $json
                );
            }
        }

        // Replace {user_meta:key} variables
        if (is_user_logged_in()) {
            $user_id = get_current_user_id();
            $json = preg_replace_callback(
                '/\{user_meta:([a-zA-Z0-9_-]+)\}/',
                function ($matches) use ($user_id) {
                    $meta_key = $matches[1];
                    $meta_value = get_user_meta($user_id, $meta_key, true);
                    return $meta_value ? $meta_value : $matches[0];
                },
                $json
            );
        }

        return $json;
    }
}

/**
 * Register Pro variable replacer class
 */
function swift_rank_pro_register_variable_replacer($class_name)
{
    return 'Schema_Variable_Replacer_Pro';
}
add_filter('swift_rank_variable_replacer_class', 'swift_rank_pro_register_variable_replacer');
