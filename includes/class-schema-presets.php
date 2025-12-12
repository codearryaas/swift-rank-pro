<?php
/**
 * Schema Presets Manager
 * 
 * Provides predefined schema templates for common use cases
 *
 * @package Swift_Rank_Pro
 * @since 1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

class Swift_Rank_Pro_Presets
{

    /**
     * Get all available schema presets
     * 
     * @return array
     */
    public static function get_presets()
    {
        $presets = array(
            // Article Presets
            array(
                'id' => 'blog-post',
                'name' => __('Blog Post', 'swift-rank-pro'),
                'description' => __('Standard blog post with author and publication details', 'swift-rank-pro'),
                'icon' => 'file-text',
                'type' => 'Article',
                'conditions' => array(
                    array(
                        'conditionType' => 'post_type',
                        'operator' => 'equal_to',
                        'value' => array('post')
                    )
                ),
                'fields' => array(
                    'articleType' => 'BlogPosting',
                    'headline' => '{post_title}',
                    'description' => '{post_excerpt}',
                    'image' => '{featured_image}',
                    'datePublished' => '{post_date}',
                    'dateModified' => '{post_modified}',
                    'use_author_reference' => true,
                    'author_reference' => array(
                        'type' => 'reference',
                        'source' => 'user',
                        'id' => '{post_author_id}'
                    ),
                    'use_publisher_reference' => true,
                    'publisher_reference' => array(
                        'type' => 'reference',
                        'source' => 'knowledge_base',
                        'id' => 'knowledge_base'
                    )
                )
            ),
            array(
                'id' => 'news-article',
                'name' => __('News Article', 'swift-rank-pro'),
                'description' => __('News article with dateline and news organization', 'swift-rank-pro'),
                'icon' => 'newspaper',
                'type' => 'Article',
                'conditions' => array(
                    array(
                        'conditionType' => 'post_type',
                        'operator' => 'equal_to',
                        'value' => array('post')
                    )
                ),
                'fields' => array(
                    'articleType' => 'NewsArticle',
                    'headline' => '{post_title}',
                    'description' => '{post_excerpt}',
                    'image' => '{featured_image}',
                    'datePublished' => '{post_date}',
                    'dateModified' => '{post_modified}',
                    'use_author_reference' => true,
                    'author_reference' => array(
                        'type' => 'reference',
                        'source' => 'user',
                        'id' => '{post_author_id}'
                    ),
                    'use_publisher_reference' => true,
                    'publisher_reference' => array(
                        'type' => 'reference',
                        'source' => 'knowledge_base',
                        'id' => 'knowledge_base'
                    )
                )
            ),

            // Video Presets
            array(
                'id' => 'youtube-video',
                'name' => __('YouTube Video', 'swift-rank-pro'),
                'description' => __('Video hosted on YouTube with embed URL', 'swift-rank-pro'),
                'icon' => 'youtube',
                'type' => 'VideoObject',
                'conditions' => array(),
                'fields' => array(
                    'name' => '{post_title}',
                    'description' => '{post_excerpt}',
                    'thumbnailUrl' => '{featured_image}',
                    'uploadDate' => '{post_date}',
                    'contentUrl' => '',
                    'embedUrl' => ''
                )
            ),
            array(
                'id' => 'tutorial-video',
                'name' => __('Tutorial Video', 'swift-rank-pro'),
                'description' => __('Educational or tutorial video content', 'swift-rank-pro'),
                'icon' => 'graduation-cap',
                'type' => 'VideoObject',
                'conditions' => array(),
                'fields' => array(
                    'name' => '{post_title}',
                    'description' => '{post_excerpt}',
                    'thumbnailUrl' => '{featured_image}',
                    'uploadDate' => '{post_date}',
                    'contentUrl' => '',
                    'embedUrl' => ''
                )
            ),

            // Product Presets
            array(
                'id' => 'physical-product',
                'name' => __('Physical Product', 'swift-rank-pro'),
                'description' => __('Physical product with price and availability', 'swift-rank-pro'),
                'icon' => 'package',
                'type' => 'Product',
                'conditions' => array(
                    array(
                        'conditionType' => 'post_type',
                        'operator' => 'equal_to',
                        'value' => array('product')
                    )
                ),
                'fields' => array(
                    'name' => '{post_title}',
                    'description' => '{post_excerpt}',
                    'image' => '{featured_image}',
                    'sku' => '',
                    'brand' => '',
                    'offers' => array(
                        'price' => '',
                        'priceCurrency' => 'USD',
                        'availability' => 'https://schema.org/InStock'
                    )
                )
            ),
            array(
                'id' => 'digital-product',
                'name' => __('Digital Product', 'swift-rank-pro'),
                'description' => __('Digital download or software product', 'swift-rank-pro'),
                'icon' => 'download',
                'type' => 'Product',
                'conditions' => array(
                    array(
                        'conditionType' => 'post_type',
                        'operator' => 'equal_to',
                        'value' => array('product')
                    )
                ),
                'fields' => array(
                    'name' => '{post_title}',
                    'description' => '{post_excerpt}',
                    'image' => '{featured_image}',
                    'sku' => '',
                    'brand' => '',
                    'offers' => array(
                        'price' => '',
                        'priceCurrency' => 'USD',
                        'availability' => 'https://schema.org/InStock'
                    )
                )
            ),

            // Event Presets
            array(
                'id' => 'online-event',
                'name' => __('Online Event', 'swift-rank-pro'),
                'description' => __('Virtual event with online attendance', 'swift-rank-pro'),
                'icon' => 'video',
                'type' => 'Event',
                'conditions' => array(),
                'fields' => array(
                    'name' => '{post_title}',
                    'description' => '{post_excerpt}',
                    'image' => '{featured_image}',
                    'startDate' => '',
                    'endDate' => '',
                    'eventAttendanceMode' => 'https://schema.org/OnlineEventAttendanceMode',
                    'location' => array(
                        '@type' => 'VirtualLocation',
                        'url' => ''
                    )
                )
            ),
            array(
                'id' => 'in-person-event',
                'name' => __('In-Person Event', 'swift-rank-pro'),
                'description' => __('Physical event at a specific location', 'swift-rank-pro'),
                'icon' => 'map-pin',
                'type' => 'Event',
                'conditions' => array(),
                'fields' => array(
                    'name' => '{post_title}',
                    'description' => '{post_excerpt}',
                    'image' => '{featured_image}',
                    'startDate' => '',
                    'endDate' => '',
                    'eventAttendanceMode' => 'https://schema.org/OfflineEventAttendanceMode',
                    'location' => array(
                        '@type' => 'Place',
                        'name' => '',
                        'address' => ''
                    )
                )
            ),

            // Recipe Presets
            array(
                'id' => 'simple-recipe',
                'name' => __('Simple Recipe', 'swift-rank-pro'),
                'description' => __('Basic recipe with ingredients and instructions', 'swift-rank-pro'),
                'icon' => 'chef-hat',
                'type' => 'Recipe',
                'conditions' => array(),
                'fields' => array(
                    'name' => '{post_title}',
                    'description' => '{post_excerpt}',
                    'image' => '{featured_image}',
                    'prepTime' => 'PT30M',
                    'cookTime' => 'PT1H',
                    'totalTime' => 'PT1H30M',
                    'recipeYield' => '4 servings',
                    'recipeIngredient' => array(),
                    'recipeInstructions' => array()
                )
            ),

            // HowTo Presets
            array(
                'id' => 'diy-guide',
                'name' => __('DIY Guide', 'swift-rank-pro'),
                'description' => __('Step-by-step DIY instructions', 'swift-rank-pro'),
                'icon' => 'wrench',
                'type' => 'HowTo',
                'conditions' => array(),
                'fields' => array(
                    'name' => '{post_title}',
                    'description' => '{post_excerpt}',
                    'image' => '{featured_image}',
                    'totalTime' => 'PT2H',
                    'step' => array()
                )
            ),

            // Review Presets
            array(
                'id' => 'product-review',
                'name' => __('Product Review', 'swift-rank-pro'),
                'description' => __('Product review with rating', 'swift-rank-pro'),
                'icon' => 'star',
                'type' => 'Review',
                'conditions' => array(),
                'fields' => array(
                    'itemReviewed' => array(
                        '@type' => 'Product',
                        'name' => '{post_title}'
                    ),
                    'reviewRating' => array(
                        '@type' => 'Rating',
                        'ratingValue' => '4.5',
                        'bestRating' => '5'
                    ),
                    'author' => array(
                        'type' => 'reference',
                        'source' => 'users',
                        'id' => '{post_author_id}'
                    ),
                    'reviewBody' => '{post_content}'
                )
            ),

            // LocalBusiness Presets
            array(
                'id' => 'restaurant',
                'name' => __('Restaurant', 'swift-rank-pro'),
                'description' => __('Restaurant with menu and hours', 'swift-rank-pro'),
                'icon' => 'utensils',
                'type' => 'LocalBusiness',
                'conditions' => array(),
                'fields' => array(
                    'businessType' => 'Restaurant',
                    'name' => '{post_title}',
                    'description' => '{post_excerpt}',
                    'image' => '{featured_image}',
                    'address' => '',
                    'telephone' => '',
                    'priceRange' => '$$',
                    'openingHours' => array()
                )
            ),
            array(
                'id' => 'store',
                'name' => __('Store', 'swift-rank-pro'),
                'description' => __('Retail store location', 'swift-rank-pro'),
                'icon' => 'store',
                'type' => 'LocalBusiness',
                'conditions' => array(),
                'fields' => array(
                    'businessType' => 'Store',
                    'name' => '{post_title}',
                    'description' => '{post_excerpt}',
                    'image' => '{featured_image}',
                    'address' => '',
                    'telephone' => '',
                    'priceRange' => '$$',
                    'openingHours' => array()
                )
            ),

            // WooCommerce Presets
            array(
                'id' => 'woocommerce-simple-product',
                'name' => __('WooCommerce Simple Product', 'swift-rank-pro'),
                'description' => __('Standard WooCommerce product with price and stock', 'swift-rank-pro'),
                'icon' => 'shopping-cart',
                'type' => 'Product',
                'conditions' => array(
                    array(
                        'conditionType' => 'post_type',
                        'operator' => 'equal_to',
                        'value' => array('product')
                    )
                ),
                'fields' => array(
                    'name' => '{post_title}',
                    'description' => '{post_excerpt}',
                    'image' => '{featured_image}',
                    'sku' => '{meta:_sku}',
                    'brand' => '',
                    'offers' => array(
                        'price' => '{meta:_price}',
                        'priceCurrency' => 'USD',
                        'availability' => 'https://schema.org/InStock',
                        'url' => '{post_url}'
                    ),
                    'aggregateRating' => array(
                        'ratingValue' => '{meta:_wc_average_rating}',
                        'reviewCount' => '{meta:_wc_review_count}'
                    )
                )
            ),
            array(
                'id' => 'woocommerce-variable-product',
                'name' => __('WooCommerce Variable Product', 'swift-rank-pro'),
                'description' => __('Product with multiple variations (size, color, etc.)', 'swift-rank-pro'),
                'icon' => 'grid',
                'type' => 'Product',
                'conditions' => array(
                    array(
                        'conditionType' => 'post_type',
                        'operator' => 'equal_to',
                        'value' => array('product')
                    )
                ),
                'fields' => array(
                    'name' => '{post_title}',
                    'description' => '{post_excerpt}',
                    'image' => '{featured_image}',
                    'sku' => '{meta:_sku}',
                    'brand' => '',
                    'offers' => array(
                        '@type' => 'AggregateOffer',
                        'lowPrice' => '{meta:_min_variation_price}',
                        'highPrice' => '{meta:_max_variation_price}',
                        'priceCurrency' => 'USD',
                        'availability' => 'https://schema.org/InStock',
                        'url' => '{post_url}'
                    ),
                    'aggregateRating' => array(
                        'ratingValue' => '{meta:_wc_average_rating}',
                        'reviewCount' => '{meta:_wc_review_count}'
                    )
                )
            ),

            // Easy Digital Downloads Presets
            array(
                'id' => 'edd-download',
                'name' => __('EDD Digital Download', 'swift-rank-pro'),
                'description' => __('Easy Digital Downloads product schema', 'swift-rank-pro'),
                'icon' => 'download-cloud',
                'type' => 'Product',
                'conditions' => array(
                    array(
                        'conditionType' => 'post_type',
                        'operator' => 'equal_to',
                        'value' => array('download')
                    )
                ),
                'fields' => array(
                    'name' => '{post_title}',
                    'description' => '{post_excerpt}',
                    'image' => '{featured_image}',
                    'sku' => '{meta:edd_sku}',
                    'brand' => '',
                    'offers' => array(
                        'price' => '{meta:edd_price}',
                        'priceCurrency' => 'USD',
                        'availability' => 'https://schema.org/InStock',
                        'url' => '{post_url}'
                    ),
                    'aggregateRating' => array(
                        'ratingValue' => '{meta:_edd_reviews_average_rating}',
                        'reviewCount' => '{meta:_edd_reviews_count}'
                    )
                )
            ),
            array(
                'id' => 'edd-software',
                'name' => __('EDD Software Product', 'swift-rank-pro'),
                'description' => __('Digital software product for Easy Digital Downloads', 'swift-rank-pro'),
                'icon' => 'code',
                'type' => 'Product',
                'conditions' => array(
                    array(
                        'conditionType' => 'post_type',
                        'operator' => 'equal_to',
                        'value' => array('download')
                    )
                ),
                'fields' => array(
                    'name' => '{post_title}',
                    'description' => '{post_excerpt}',
                    'image' => '{featured_image}',
                    'sku' => '{meta:edd_sku}',
                    'brand' => '',
                    'offers' => array(
                        'price' => '{meta:edd_price}',
                        'priceCurrency' => 'USD',
                        'availability' => 'https://schema.org/InStock',
                        'url' => '{post_url}'
                    ),
                    'aggregateRating' => array(
                        'ratingValue' => '{meta:_edd_reviews_average_rating}',
                        'reviewCount' => '{meta:_edd_reviews_count}'
                    )
                )
            ),

            // WP Job Manager Presets
            array(
                'id' => 'job-listing-full-time',
                'name' => __('Full-Time Job Listing', 'swift-rank-pro'),
                'description' => __('Full-time employment opportunity', 'swift-rank-pro'),
                'icon' => 'briefcase',
                'type' => 'JobPosting',
                'conditions' => array(
                    array(
                        'conditionType' => 'post_type',
                        'operator' => 'equal_to',
                        'value' => array('job_listing')
                    )
                ),
                'fields' => array(
                    'title' => '{post_title}',
                    'description' => '{post_content}',
                    'datePosted' => '{post_date}',
                    'validThrough' => '{meta:_job_expires}',
                    'employmentType' => 'FULL_TIME',
                    'hiringOrganization' => array(
                        '@type' => 'Organization',
                        'name' => '{meta:_company_name}',
                        'sameAs' => '{meta:_company_website}',
                        'logo' => '{meta:_company_logo}'
                    ),
                    'jobLocation' => array(
                        '@type' => 'Place',
                        'address' => array(
                            '@type' => 'PostalAddress',
                            'addressLocality' => '{meta:_job_location}'
                        )
                    ),
                    'baseSalary' => array(
                        '@type' => 'MonetaryAmount',
                        'currency' => 'USD',
                        'value' => array(
                            '@type' => 'QuantitativeValue',
                            'value' => '{meta:_job_salary}',
                            'unitText' => 'YEAR'
                        )
                    )
                )
            ),
            array(
                'id' => 'job-listing-remote',
                'name' => __('Remote Job Listing', 'swift-rank-pro'),
                'description' => __('Remote/work-from-home position', 'swift-rank-pro'),
                'icon' => 'home',
                'type' => 'JobPosting',
                'conditions' => array(
                    array(
                        'conditionType' => 'post_type',
                        'operator' => 'equal_to',
                        'value' => array('job_listing')
                    )
                ),
                'fields' => array(
                    'title' => '{post_title}',
                    'description' => '{post_content}',
                    'datePosted' => '{post_date}',
                    'validThrough' => '{meta:_job_expires}',
                    'employmentType' => 'FULL_TIME',
                    'jobLocationType' => 'TELECOMMUTE',
                    'hiringOrganization' => array(
                        '@type' => 'Organization',
                        'name' => '{meta:_company_name}',
                        'sameAs' => '{meta:_company_website}',
                        'logo' => '{meta:_company_logo}'
                    ),
                    'applicantLocationRequirements' => array(
                        '@type' => 'Country',
                        'name' => 'USA'
                    ),
                    'baseSalary' => array(
                        '@type' => 'MonetaryAmount',
                        'currency' => 'USD',
                        'value' => array(
                            '@type' => 'QuantitativeValue',
                            'value' => '{meta:_job_salary}',
                            'unitText' => 'YEAR'
                        )
                    )
                )
            ),

            // FAQ Presets
            array(
                'id' => 'faq-page',
                'name' => __('FAQ Page', 'swift-rank-pro'),
                'description' => __('Frequently asked questions page', 'swift-rank-pro'),
                'icon' => 'help-circle',
                'type' => 'FAQPage',
                'conditions' => array(),
                'fields' => array(
                    'mainEntity' => array()
                )
            ),

            // Course Presets
            array(
                'id' => 'online-course',
                'name' => __('Online Course', 'swift-rank-pro'),
                'description' => __('Educational course with lessons and instructor', 'swift-rank-pro'),
                'icon' => 'book-open',
                'type' => 'Custom',
                'conditions' => array(),
                'fields' => array(
                    'custom_structure' => json_encode(array(
                        '@type' => 'Course',
                        'name' => '{post_title}',
                        'description' => '{post_excerpt}',
                        'provider' => array(
                            '@type' => 'Organization',
                            'name' => '{site_name}',
                            'sameAs' => '{site_url}'
                        ),
                        'hasCourseInstance' => array(
                            '@type' => 'CourseInstance',
                            'courseMode' => 'online',
                            'courseWorkload' => 'PT10H'
                        )
                    ), JSON_PRETTY_PRINT)
                )
            ),

            // Book Presets
            array(
                'id' => 'book',
                'name' => __('Book', 'swift-rank-pro'),
                'description' => __('Book with author and ISBN', 'swift-rank-pro'),
                'icon' => 'book',
                'type' => 'Custom',
                'conditions' => array(),
                'fields' => array(
                    'custom_structure' => json_encode(array(
                        '@type' => 'Book',
                        'name' => '{post_title}',
                        'description' => '{post_excerpt}',
                        'image' => '{featured_image}',
                        'author' => array(
                            '@type' => 'Person',
                            'name' => ''
                        ),
                        'isbn' => '',
                        'numberOfPages' => '',
                        'publisher' => array(
                            '@type' => 'Organization',
                            'name' => ''
                        ),
                        'datePublished' => ''
                    ), JSON_PRETTY_PRINT)
                )
            ),

            // Movie Presets
            array(
                'id' => 'movie',
                'name' => __('Movie', 'swift-rank-pro'),
                'description' => __('Movie or film with cast and crew', 'swift-rank-pro'),
                'icon' => 'film',
                'type' => 'Custom',
                'conditions' => array(),
                'fields' => array(
                    'custom_structure' => json_encode(array(
                        '@type' => 'Movie',
                        'name' => '{post_title}',
                        'description' => '{post_excerpt}',
                        'image' => '{featured_image}',
                        'director' => array(
                            '@type' => 'Person',
                            'name' => ''
                        ),
                        'dateCreated' => '',
                        'duration' => 'PT2H',
                        'aggregateRating' => array(
                            '@type' => 'AggregateRating',
                            'ratingValue' => '',
                            'reviewCount' => ''
                        )
                    ), JSON_PRETTY_PRINT)
                )
            ),

            // Music Presets
            array(
                'id' => 'music-album',
                'name' => __('Music Album', 'swift-rank-pro'),
                'description' => __('Music album with tracks and artist', 'swift-rank-pro'),
                'icon' => 'music',
                'type' => 'Custom',
                'conditions' => array(),
                'fields' => array(
                    'custom_structure' => json_encode(array(
                        '@type' => 'MusicAlbum',
                        'name' => '{post_title}',
                        'description' => '{post_excerpt}',
                        'image' => '{featured_image}',
                        'byArtist' => array(
                            '@type' => 'MusicGroup',
                            'name' => ''
                        ),
                        'datePublished' => '',
                        'numTracks' => ''
                    ), JSON_PRETTY_PRINT)
                )
            ),

            // Podcast Presets - Keep PodcastEpisode as registered type
            array(
                'id' => 'podcast-episode',
                'name' => __('Podcast Episode', 'swift-rank-pro'),
                'description' => __('Single podcast episode', 'swift-rank-pro'),
                'icon' => 'mic',
                'type' => 'PodcastEpisode',
                'conditions' => array(),
                'fields' => array(
                    'name' => '{post_title}',
                    'description' => '{post_excerpt}',
                    'datePublished' => '{post_date}',
                    'duration' => 'PT45M',
                    'associatedMedia' => array(
                        '@type' => 'MediaObject',
                        'contentUrl' => ''
                    ),
                    'partOfSeries' => array(
                        '@type' => 'PodcastSeries',
                        'name' => '',
                        'url' => '{site_url}'
                    )
                )
            ),
            array(
                'id' => 'podcast-series',
                'name' => __('Podcast Series', 'swift-rank-pro'),
                'description' => __('Complete podcast series', 'swift-rank-pro'),
                'icon' => 'radio',
                'type' => 'Custom',
                'conditions' => array(),
                'fields' => array(
                    'custom_structure' => json_encode(array(
                        '@type' => 'PodcastSeries',
                        'name' => '{post_title}',
                        'description' => '{post_excerpt}',
                        'image' => '{featured_image}',
                        'url' => '{post_url}',
                        'author' => array(
                            '@type' => 'Person',
                            'name' => ''
                        ),
                        'webFeed' => ''
                    ), JSON_PRETTY_PRINT)
                )
            ),

            // Service Presets
            array(
                'id' => 'professional-service',
                'name' => __('Professional Service', 'swift-rank-pro'),
                'description' => __('Professional service offering', 'swift-rank-pro'),
                'icon' => 'tool',
                'type' => 'Custom',
                'conditions' => array(),
                'fields' => array(
                    'custom_structure' => json_encode(array(
                        '@type' => 'Service',
                        'name' => '{post_title}',
                        'description' => '{post_excerpt}',
                        'image' => '{featured_image}',
                        'provider' => array(
                            '@type' => 'Organization',
                            'name' => '{site_name}'
                        ),
                        'areaServed' => '',
                        'serviceType' => ''
                    ), JSON_PRETTY_PRINT)
                )
            ),

            // Software Presets
            array(
                'id' => 'software-app',
                'name' => __('Software Application', 'swift-rank-pro'),
                'description' => __('Software or mobile application', 'swift-rank-pro'),
                'icon' => 'smartphone',
                'type' => 'Custom',
                'conditions' => array(),
                'fields' => array(
                    'custom_structure' => json_encode(array(
                        '@type' => 'SoftwareApplication',
                        'name' => '{post_title}',
                        'description' => '{post_excerpt}',
                        'image' => '{featured_image}',
                        'operatingSystem' => 'Windows, macOS, Linux',
                        'applicationCategory' => 'BusinessApplication',
                        'offers' => array(
                            '@type' => 'Offer',
                            'price' => '0',
                            'priceCurrency' => 'USD'
                        ),
                        'aggregateRating' => array(
                            '@type' => 'AggregateRating',
                            'ratingValue' => '',
                            'reviewCount' => ''
                        )
                    ), JSON_PRETTY_PRINT)
                )
            ),

            // Organization Presets - Keep as registered type
            array(
                'id' => 'organization',
                'name' => __('Organization', 'swift-rank-pro'),
                'description' => __('Company or organization profile', 'swift-rank-pro'),
                'icon' => 'building',
                'type' => 'Organization',
                'conditions' => array(),
                'fields' => array(
                    'name' => '{post_title}',
                    'description' => '{post_excerpt}',
                    'logo' => '{featured_image}',
                    'url' => '{post_url}',
                    'address' => '',
                    'telephone' => '',
                    'email' => '',
                    'sameAs' => array()
                )
            ),

            // Person Presets - Keep as registered type
            array(
                'id' => 'person-profile',
                'name' => __('Person Profile', 'swift-rank-pro'),
                'description' => __('Individual person or professional profile', 'swift-rank-pro'),
                'icon' => 'user',
                'type' => 'Person',
                'conditions' => array(),
                'fields' => array(
                    'name' => '{post_title}',
                    'description' => '{post_excerpt}',
                    'image' => '{featured_image}',
                    'url' => '{post_url}',
                    'jobTitle' => '',
                    'worksFor' => array(
                        '@type' => 'Organization',
                        'name' => ''
                    ),
                    'sameAs' => array()
                )
            ),

            // WebPage Presets
            array(
                'id' => 'webpage',
                'name' => __('Web Page', 'swift-rank-pro'),
                'description' => __('Generic web page', 'swift-rank-pro'),
                'icon' => 'file',
                'type' => 'Custom',
                'conditions' => array(),
                'fields' => array(
                    'custom_structure' => json_encode(array(
                        '@type' => 'WebPage',
                        'name' => '{post_title}',
                        'description' => '{post_excerpt}',
                        'url' => '{post_url}',
                        'datePublished' => '{post_date}',
                        'dateModified' => '{post_modified}',
                        'author' => array(
                            '@type' => 'Person',
                            'name' => '{post_author}'
                        )
                    ), JSON_PRETTY_PRINT)
                )
            ),

            // Medical Presets
            array(
                'id' => 'medical-condition',
                'name' => __('Medical Condition', 'swift-rank-pro'),
                'description' => __('Health or medical condition information', 'swift-rank-pro'),
                'icon' => 'heart',
                'type' => 'Custom',
                'conditions' => array(),
                'fields' => array(
                    'custom_structure' => json_encode(array(
                        '@type' => 'MedicalCondition',
                        'name' => '{post_title}',
                        'description' => '{post_excerpt}',
                        'associatedAnatomy' => array(
                            '@type' => 'AnatomicalStructure',
                            'name' => ''
                        ),
                        'possibleTreatment' => array()
                    ), JSON_PRETTY_PRINT)
                )
            ),

            // Real Estate Presets
            array(
                'id' => 'real-estate-listing',
                'name' => __('Real Estate Listing', 'swift-rank-pro'),
                'description' => __('Property for sale or rent', 'swift-rank-pro'),
                'icon' => 'home',
                'type' => 'Custom',
                'conditions' => array(),
                'fields' => array(
                    'custom_structure' => json_encode(array(
                        '@type' => 'RealEstateListing',
                        'name' => '{post_title}',
                        'description' => '{post_excerpt}',
                        'image' => '{featured_image}',
                        'address' => array(
                            '@type' => 'PostalAddress',
                            'streetAddress' => '',
                            'addressLocality' => '',
                            'addressRegion' => '',
                            'postalCode' => ''
                        ),
                        'price' => '',
                        'priceCurrency' => 'USD'
                    ), JSON_PRETTY_PRINT)
                )
            )
        );

        // Validate presets and allow filtering
        $validated_presets = array_values(array_filter($presets, array(__CLASS__, 'validate_preset')));

        return apply_filters('swift_rank_pro_presets', $validated_presets);
    }

    /**
     * Get presets filtered by type
     * 
     * @param string $type Schema type
     * @return array
     */
    public static function get_presets_by_type($type = '')
    {
        $presets = self::get_presets();

        if (empty($type)) {
            return $presets;
        }

        return array_values(array_filter($presets, function ($preset) use ($type) {
            return $preset['type'] === $type;
        }));
    }

    /**
     * Get unique schema types from presets
     * 
     * @return array
     */
    public static function get_preset_types()
    {
        $presets = self::get_presets();
        $types = array();

        foreach ($presets as $preset) {
            if (!in_array($preset['type'], $types)) {
                $types[] = $preset['type'];
            }
        }

        return $types;
    }

    /**
     * Validate preset against registered schema types
     * 
     * @param array $preset Preset definition.
     * @return bool
     */
    public static function validate_preset($preset)
    {
        // Must have ID, name, and type
        if (empty($preset['id']) || empty($preset['name']) || empty($preset['type'])) {
            return false;
        }

        // Check if schema type is registered
        if (!class_exists('Schema_Type_Helper')) {
            // Can't validate, assume true if type matches pattern
            return true;
        }

        $all_types = Schema_Type_Helper::get_schema_types();

        // Allow 'Custom' type always
        if ('Custom' === $preset['type']) {
            return true;
        }

        if (!isset($all_types[$preset['type']])) {
            // Type not registered
            return false;
        }

        // Validate fields keys if fields are defined in type
        $type_definition = $all_types[$preset['type']];
        if (isset($type_definition['fields']) && isset($preset['fields']) && is_array($preset['fields'])) {
            // This is loose validation - we just check if the preset uses fields that aren't in the definition?
            // Actually, we should probably check if the preset's keys exist in the type definition options.
            // But 'fields' in type definition is an array of field objects (id, label, type, etc).
            // We'd need to map field Ids.

            // For now, let's just ensure fields is an array
            return is_array($preset['fields']);
        }

        return true;
    }
}
