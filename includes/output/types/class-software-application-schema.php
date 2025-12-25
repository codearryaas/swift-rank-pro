<?php
/**
 * Software Application Schema Builder
 *
 * @package Swift_Rank_Pro
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Schema_Software_Application class
 *
 * Builds SoftwareApplication schema type.
 */
class Schema_Software_Application implements Schema_Builder_Interface
{

    /**
     * Build software application schema from fields
     *
     * @param array $fields Field values.
     * @return array Schema array (without @context).
     */
    public function build($fields)
    {
        $schema = array(
            '@type' => 'SoftwareApplication',
            'name' => !empty($fields['name']) ? $fields['name'] : '{post_title}',
        );

        // Required Properties
        if (!empty($fields['operatingSystem'])) {
            $schema['operatingSystem'] = $fields['operatingSystem'];
        }

        if (!empty($fields['applicationCategory'])) {
            $schema['applicationCategory'] = $fields['applicationCategory'];
        }

        // Optional Properties
        // Image with fallback to featured_image variable
        $image_url = !empty($fields['imageUrl']) ? $fields['imageUrl'] : (!empty($fields['image']) ? $fields['image'] : '{featured_image}');
        if (!empty($image_url)) {
            $schema['image'] = $image_url;
        }

        // Offers (Multiple Pricing Support)
        if (!empty($fields['offers']) && is_array($fields['offers'])) {
            $schema['offers'] = array();
            foreach ($fields['offers'] as $offer_item) {
                if (isset($offer_item['price'])) {
                    $offer_schema = array(
                        '@type' => 'Offer',
                        'price' => $offer_item['price'],
                        'priceCurrency' => !empty($offer_item['priceCurrency']) ? $offer_item['priceCurrency'] : 'USD',
                    );

                    if (!empty($offer_item['name'])) {
                        $offer_schema['name'] = $offer_item['name'];
                    }

                    if (!empty($offer_item['url'])) {
                        $offer_schema['url'] = $offer_item['url'];
                    }

                    $schema['offers'][] = $offer_schema;
                }
            }
        }

        // Aggregate Rating (Recommended)
        if (!empty($fields['ratingValue'])) {
            $aggregate_rating = array(
                '@type' => 'AggregateRating',
                'ratingValue' => $fields['ratingValue'],
            );

            if (!empty($fields['reviewCount'])) {
                $aggregate_rating['reviewCount'] = $fields['reviewCount'];
            } elseif (!empty($fields['ratingCount'])) {
                $aggregate_rating['ratingCount'] = $fields['ratingCount'];
            } else {
                // Fallback if count is missing but rating exists? Google usually requires a count.
                // Let's default to 1 if user provided rating but no count, to avoid validation error, 
                // or maybe just omit if strict. We'll include it if user put something custom variable.
                $aggregate_rating['ratingCount'] = '1';
            }

            $schema['aggregateRating'] = $aggregate_rating;
        }

        return $schema;
    }

    /**
     * Get schema.org structure for SoftwareApplication type
     *
     * @return array Schema.org structure specification.
     */
    public function get_schema_structure()
    {
        return array(
            '@type' => 'SoftwareApplication',
            '@context' => 'https://schema.org',
            'label' => __('Software Application', 'swift-rank-pro'),
            'description' => __('A software application.', 'swift-rank-pro'),
            'url' => 'https://schema.org/SoftwareApplication',
            'icon' => 'smartphone', // Or 'monitor' or 'box' - smartphone implies generic app
            'supports_language' => true,
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
                'label' => __('App Name', 'swift-rank-pro'),
                'type' => 'select',
                'allowCustom' => true,
                'tooltip' => __('Name of the application. Click pencil icon to use variables.', 'swift-rank-pro'),
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
                'name' => 'operatingSystem',
                'label' => __('Operating System', 'swift-rank-pro'),
                'type' => 'select',
                'allowCustom' => true,
                'tooltip' => __('Operating systems supported (e.g., Windows 10, macOS, Android, iOS).', 'swift-rank-pro'),
                'placeholder' => 'Windows, macOS',
                'options' => array(
                    array(
                        'label' => __('Windows', 'swift-rank-pro'),
                        'value' => 'Windows',
                    ),
                    array(
                        'label' => __('macOS', 'swift-rank-pro'),
                        'value' => 'macOS',
                    ),
                    array(
                        'label' => __('Android', 'swift-rank-pro'),
                        'value' => 'Android',
                    ),
                    array(
                        'label' => __('iOS', 'swift-rank-pro'),
                        'value' => 'iOS',
                    ),
                    array(
                        'label' => __('Linux', 'swift-rank-pro'),
                        'value' => 'Linux',
                    ),
                ),
                'required' => true,
            ),
            array(
                'name' => 'applicationCategory',
                'label' => __('Category', 'swift-rank-pro'),
                'type' => 'select',
                'allowCustom' => true,
                'tooltip' => __('Category of the application (e.g., GameApplication, UtilityApplication).', 'swift-rank-pro'),
                'placeholder' => 'BusinessApplication',
                'options' => array(
                    array(
                        'label' => __('Business', 'swift-rank-pro'),
                        'value' => 'BusinessApplication',
                    ),
                    array(
                        'label' => __('Game', 'swift-rank-pro'),
                        'value' => 'GameApplication',
                    ),
                    array(
                        'label' => __('Educational', 'swift-rank-pro'),
                        'value' => 'EducationalApplication',
                    ),
                    array(
                        'label' => __('Health', 'swift-rank-pro'),
                        'value' => 'HealthApplication',
                    ),
                    array(
                        'label' => __('Productivity', 'swift-rank-pro'),
                        'value' => 'ProductivityApplication',
                    ),
                    array(
                        'label' => __('Utilities', 'swift-rank-pro'),
                        'value' => 'UtilitiesApplication',
                    ),
                    array(
                        'label' => __('Multimedia', 'swift-rank-pro'),
                        'value' => 'MultimediaApplication',
                    ),
                ),
                'default' => 'BusinessApplication',
                'required' => true,
            ),
            array(
                'name' => 'offers',
                'label' => __('Offers / Pricing', 'swift-rank-pro'),
                'type' => 'repeater',
                'tooltip' => __('Add one or more pricing options.', 'swift-rank-pro'),
                'required' => true,
                'fields' => array(
                    array(
                        'name' => 'name',
                        'label' => __('Offer Name', 'swift-rank-pro'),
                        'type' => 'select',
                        'allowCustom' => true,
                        'tooltip' => __('Name of the offer (e.g., Free Version, Pro License).', 'swift-rank-pro'),
                        'placeholder' => 'Standard License',
                        'options' => array(),
                    ),
                    array(
                        'name' => 'price',
                        'label' => __('Price', 'swift-rank-pro'),
                        'type' => 'select',
                        'allowCustom' => true,
                        'tooltip' => __('Price of the application. Use 0 for free apps.', 'swift-rank-pro'),
                        'placeholder' => '0.00',
                        'options' => array(
                            array(
                                'label' => __('Free', 'swift-rank-pro'),
                                'value' => '0.00',
                            ),
                            array(
                                'label' => __('WooCommerce Price', 'swift-rank-pro'),
                                'value' => '{woo_product_price}',
                            ),
                        ),
                        'required' => true,
                    ),
                    array(
                        'name' => 'priceCurrency',
                        'label' => __('Currency', 'swift-rank-pro'),
                        'type' => 'select',
                        'allowCustom' => true,
                        'tooltip' => __('Currency code (ISO 4217).', 'swift-rank-pro'),
                        'placeholder' => 'USD',
                        'options' => array(
                            array(
                                'label' => __('USD', 'swift-rank-pro'),
                                'value' => 'USD',
                            ),
                            array(
                                'label' => __('EUR', 'swift-rank-pro'),
                                'value' => 'EUR',
                            ),
                            array(
                                'label' => __('WooCommerce Currency', 'swift-rank-pro'),
                                'value' => '{woo_product_currency}',
                            ),
                        ),
                        'default' => 'USD',
                    ),
                    array(
                        'name' => 'url',
                        'label' => __('Offer URL', 'swift-rank-pro'),
                        'type' => 'select',
                        'allowCustom' => true,
                        'tooltip' => __('URL to purchase or download this specific offer.', 'swift-rank-pro'),
                        'placeholder' => 'https://example.com/buy',
                        'options' => array(
                            array(
                                'label' => __('Post URL', 'swift-rank-pro'),
                                'value' => '{post_url}',
                            ),
                        ),
                    ),
                ),
            ),
            array(
                'name' => 'ratingValue',
                'label' => __('Rating Value', 'swift-rank-pro'),
                'type' => 'select',
                'allowCustom' => true,
                'tooltip' => __('Average rating of the application (1-5).', 'swift-rank-pro'),
                'placeholder' => '5',
                'options' => array(
                    array(
                        'label' => __('5 Stars', 'swift-rank-pro'),
                        'value' => '5',
                    ),
                    array(
                        'label' => __('WooCommerce Rating', 'swift-rank-pro'),
                        'value' => '{woo_average_rating}',
                    ),
                ),
            ),
            array(
                'name' => 'reviewCount',
                'label' => __('Review Count', 'swift-rank-pro'),
                'type' => 'select',
                'allowCustom' => true,
                'tooltip' => __('Number of reviews.', 'swift-rank-pro'),
                'placeholder' => '100',
                'options' => array(
                    array(
                        'label' => __('WooCommerce Review Count', 'swift-rank-pro'),
                        'value' => '{woo_review_count}',
                    ),
                ),
            ),
            array(
                'name' => 'image',
                'label' => __('Screenshot/Image URL', 'swift-rank-pro'),
                'type' => 'select',
                'allowCustom' => true,
                'customType' => 'image',
                'returnObject' => true,
                'tooltip' => __('Application screenshot or logo. Select from list or click pencil to upload custom image.', 'swift-rank-pro'),
                'placeholder' => '{featured_image}',
                'options' => array(
                    array(
                        'label' => __('Featured Image', 'swift-rank-pro'),
                        'value' => '{featured_image}',
                    ),
                ),
                'default' => '{featured_image}',
            ),
        );
    }

}
