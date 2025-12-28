<?php
/**
 * Image Schema Generator Class
 *
 * Automatically generates ImageObject schema from Gutenberg image blocks or IMG tags in content.
 *
 * @package Swift_Rank_Pro
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Image_Schema_Generator class
 *
 * Handles automatic generation of ImageObject schema markup from post content.
 */
class Image_Schema_Generator
{
    /**
     * Instance of this class.
     *
     * @var Image_Schema_Generator
     */
    private static $instance = null;

    /**
     * Get singleton instance.
     *
     * @return Image_Schema_Generator
     */
    public static function get_instance()
    {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Constructor.
     */
    private function __construct()
    {
        // Hook into schema output to add ImageObject schemas
        // Priority 20 ensures this runs after image variable resolution (priority 10)
        add_filter('swift_rank_schemas', array($this, 'add_image_schemas'), 20, 1);
    }

    /**
     * Add ImageObject schemas to the schema graph
     *
     * @param array $schemas Existing schemas.
     * @return array Modified schemas with ImageObject added.
     */
    public function add_image_schemas($schemas)
    {
        // Check if feature is enabled
        $settings = get_option('swift_rank_settings', array());
        if (empty($settings['auto_image_schema_enabled'])) {
            return $schemas;
        }

        // Only process on singular posts/pages
        if (!is_singular()) {
            return $schemas;
        }

        global $post;
        if (!$post) {
            return $schemas;
        }

        // Get featured image URL if it exists
        $featured_image_url = '';
        $featured_image_id = null;
        if (has_post_thumbnail($post->ID)) {
            $featured_image_id = get_post_thumbnail_id($post->ID);
            $featured_image_url = wp_get_attachment_url($featured_image_id);
        }

        // Extract images from content
        $images = $this->extract_images_from_content($post->post_content);

        if (empty($images)) {
            return $schemas;
        }

        // Check if featured image is in content to avoid duplicates
        $featured_in_content = false;
        $featured_image_index = null;

        if ($featured_image_url) {
            foreach ($images as $index => $image_data) {
                if (isset($image_data['url']) && $image_data['url'] === $featured_image_url) {
                    $featured_in_content = true;
                    $featured_image_index = $index;
                    break;
                }
            }
        }

        // Generate ImageObject schemas with @id references
        $image_ids = array();
        $featured_image_schema_id = null;

        foreach ($images as $index => $image_data) {
            $image_schema = $this->build_image_object_schema($image_data, $index);
            if (!empty($image_schema)) {
                $schemas[] = $image_schema;
                // Store the @id for later reference
                if (isset($image_schema['@id'])) {
                    $image_ids[] = $image_schema['@id'];

                    // Track featured image schema ID if this is the featured image
                    if ($featured_in_content && $index === $featured_image_index) {
                        $featured_image_schema_id = $image_schema['@id'];
                    }
                }
            }
        }

        // If featured image is NOT in content, we need to ensure it has an ImageObject
        // Check existing schemas for featured image ImageObject
        if ($featured_image_url && !$featured_in_content) {
            $has_featured_image_object = false;

            // Check if there's already an ImageObject for the featured image
            foreach ($schemas as $schema) {
                if (isset($schema['@type']) && $schema['@type'] === 'ImageObject') {
                    if (isset($schema['url']) && $schema['url'] === $featured_image_url) {
                        $has_featured_image_object = true;
                        // Use this @id for featured image reference
                        if (isset($schema['@id'])) {
                            $featured_image_schema_id = $schema['@id'];
                        }
                        break;
                    }
                }
            }

            // If no ImageObject exists for featured image, it will be created by resolve_image_variable_references
            // We just need to track its expected @id
            if (!$has_featured_image_object) {
                $featured_image_schema_id = $featured_image_url; // Will be set by resolve_image_variable_references
            }
        }

        // Update Article and WebPage schemas to reference all images by @id
        if (!empty($image_ids)) {
            $schemas = $this->update_article_webpage_image_references($schemas, $image_ids, $featured_image_schema_id);
        }

        return $schemas;
    }

    /**
     * Update Article and WebPage schemas to reference images by @id
     *
     * @param array $schemas Existing schemas.
     * @param array $image_ids Array of image @id references from content.
     * @param string|null $featured_image_schema_id The @id of the featured image schema (if exists).
     * @return array Modified schemas.
     */
    private function update_article_webpage_image_references($schemas, $image_ids, $featured_image_schema_id = null)
    {
        foreach ($schemas as &$schema) {
            // Check if this is an Article or WebPage schema
            if (
                isset($schema['@type']) &&
                (strpos($schema['@type'], 'Article') !== false || $schema['@type'] === 'WebPage')
            ) {

                // If the schema has an image field, merge it with content images
                if (isset($schema['image'])) {
                    $all_image_refs = array();

                    // First, check if there's an existing featured image from resolve_image_variable_references
                    // If it's an ImageObject with a URL, extract the @id or create one
                    if (is_array($schema['image']) && isset($schema['image']['@type']) && $schema['image']['@type'] === 'ImageObject') {
                        // Featured image exists - preserve it as the first image
                        if (isset($schema['image']['@id'])) {
                            // Already has an @id, use it
                            $all_image_refs[] = array(
                                '@type' => 'ImageObject',
                                '@id' => $schema['image']['@id'],
                            );
                        } elseif (isset($schema['image']['url'])) {
                            // Has URL but no @id - use the URL as @id for now
                            $all_image_refs[] = array(
                                '@type' => 'ImageObject',
                                '@id' => $schema['image']['url'],
                            );
                        }
                    }

                    // Then add all content images
                    foreach ($image_ids as $image_id) {
                        // Skip if this is the same as featured image (avoid duplicates)
                        $is_duplicate = false;
                        foreach ($all_image_refs as $existing_ref) {
                            if (isset($existing_ref['@id']) && $existing_ref['@id'] === $image_id) {
                                $is_duplicate = true;
                                break;
                            }
                        }

                        if (!$is_duplicate) {
                            $all_image_refs[] = array(
                                '@type' => 'ImageObject',
                                '@id' => $image_id,
                            );
                        }
                    }

                    // Update the image field with combined references
                    if (count($all_image_refs) === 1) {
                        // Single image - use object directly
                        $schema['image'] = $all_image_refs[0];
                    } elseif (count($all_image_refs) > 1) {
                        // Multiple images - use array
                        $schema['image'] = $all_image_refs;
                    }
                }
            }
        }

        return $schemas;
    }

    /**
     * Extract images from post content
     *
     * Checks for Gutenberg image blocks first, then falls back to IMG tags.
     *
     * @param string $content Post content.
     * @return array Array of image data.
     */
    private function extract_images_from_content($content)
    {
        $images = array();

        // First, try to extract from Gutenberg blocks
        if (has_blocks($content)) {
            $blocks = parse_blocks($content);
            $images = $this->extract_from_gutenberg_blocks($blocks);
        }

        // If no Gutenberg images found, fall back to IMG tags
        if (empty($images)) {
            $images = $this->extract_from_img_tags($content);
        }

        return $images;
    }

    /**
     * Extract images from Gutenberg blocks
     *
     * @param array $blocks Parsed Gutenberg blocks.
     * @return array Array of image data.
     */
    private function extract_from_gutenberg_blocks($blocks)
    {
        $images = array();

        foreach ($blocks as $block) {
            // Check for core/image block
            if ('core/image' === $block['blockName']) {
                $image_data = $this->parse_gutenberg_image_block($block);
                if ($image_data) {
                    $images[] = $image_data;
                }
            }

            // Check for core/gallery block
            if ('core/gallery' === $block['blockName']) {
                $gallery_images = $this->parse_gutenberg_gallery_block($block);
                $images = array_merge($images, $gallery_images);
            }

            // Recursively check inner blocks
            if (!empty($block['innerBlocks'])) {
                $inner_images = $this->extract_from_gutenberg_blocks($block['innerBlocks']);
                $images = array_merge($images, $inner_images);
            }
        }

        return $images;
    }

    /**
     * Parse Gutenberg image block
     *
     * @param array $block Gutenberg block data.
     * @return array|null Image data or null.
     */
    private function parse_gutenberg_image_block($block)
    {
        $attrs = isset($block['attrs']) ? $block['attrs'] : array();

        // Get attachment ID
        $attachment_id = isset($attrs['id']) ? $attrs['id'] : 0;

        if (!$attachment_id) {
            // Try to extract URL from block content
            $url = $this->extract_url_from_block_content($block['innerHTML']);
            if ($url) {
                $attachment_id = attachment_url_to_postid($url);
            }
        }

        if (!$attachment_id) {
            return null;
        }

        return $this->get_image_data_from_attachment($attachment_id, $attrs);
    }

    /**
     * Parse Gutenberg gallery block
     *
     * @param array $block Gutenberg gallery block data.
     * @return array Array of image data.
     */
    private function parse_gutenberg_gallery_block($block)
    {
        $images = array();
        $attrs = isset($block['attrs']) ? $block['attrs'] : array();

        // Get image IDs from gallery
        if (isset($attrs['ids']) && is_array($attrs['ids'])) {
            foreach ($attrs['ids'] as $attachment_id) {
                $image_data = $this->get_image_data_from_attachment($attachment_id);
                if ($image_data) {
                    $images[] = $image_data;
                }
            }
        }

        return $images;
    }

    /**
     * Extract images from IMG tags
     *
     * @param string $content HTML content.
     * @return array Array of image data.
     */
    private function extract_from_img_tags($content)
    {
        $images = array();

        // Match all img tags
        preg_match_all('/<img[^>]+>/i', $content, $img_tags);

        if (empty($img_tags[0])) {
            return $images;
        }

        foreach ($img_tags[0] as $img_tag) {
            // Extract src
            preg_match('/src=["\']([^"\']+)["\']/i', $img_tag, $src_match);
            if (empty($src_match[1])) {
                continue;
            }

            $url = $src_match[1];

            // Try to get attachment ID from URL
            $attachment_id = attachment_url_to_postid($url);

            if ($attachment_id) {
                $image_data = $this->get_image_data_from_attachment($attachment_id);
                if ($image_data) {
                    $images[] = $image_data;
                }
            } else {
                // Fall back to extracting data directly from tag
                $image_data = $this->parse_img_tag($img_tag, $url);
                if ($image_data) {
                    $images[] = $image_data;
                }
            }
        }

        return $images;
    }

    /**
     * Get image data from attachment ID
     *
     * Delegates to Image_Schema_Output_Handler for centralized metadata handling.
     *
     * @param int   $attachment_id Attachment ID.
     * @param array $attrs Optional block attributes.
     * @return array|null Image data or null.
     */
    private function get_image_data_from_attachment($attachment_id, $attrs = array())
    {
        if (!$attachment_id) {
            return null;
        }

        // Load Image Schema Output Handler if not already loaded
        if (!class_exists('Image_Schema_Output_Handler')) {
            require_once WP_CONTENT_DIR . '/plugins/swift-rank/includes/output/class-image-schema-output-handler.php';
        }

        // Use centralized handler for metadata
        $handler = Image_Schema_Output_Handler::get_instance();
        return $handler->get_image_data_from_attachment($attachment_id, $attrs);
    }
    /**
     * Parse IMG tag to extract data
     *
     * @param string $img_tag IMG tag HTML.
     * @param string $url Image URL.
     * @return array|null Image data or null.
     */
    private function parse_img_tag($img_tag, $url)
    {
        // Extract alt text
        preg_match('/alt=["\']([^"\']+)["\']/i', $img_tag, $alt_match);
        $alt = !empty($alt_match[1]) ? $alt_match[1] : '';

        // Extract width
        preg_match('/width=["\']?(\d+)["\'> ]/i', $img_tag, $width_match);
        $width = !empty($width_match[1]) ? intval($width_match[1]) : 0;

        // Extract height
        preg_match('/height=["\']?(\d+)["\'> ]/i', $img_tag, $height_match);
        $height = !empty($height_match[1]) ? intval($height_match[1]) : 0;

        // Extract title
        preg_match('/title=["\']([^"\']+)["\']/i', $img_tag, $title_match);
        $title = !empty($title_match[1]) ? $title_match[1] : '';

        return array(
            'url' => $url,
            'width' => $width,
            'height' => $height,
            'caption' => '',
            'alt' => $alt,
            'name' => $title,
            'description' => '',
        );
    }

    /**
     * Extract URL from block innerHTML
     *
     * @param string $html Block HTML content.
     * @return string|null Image URL or null.
     */
    private function extract_url_from_block_content($html)
    {
        preg_match('/src=["\']([^"\']+)["\']/i', $html, $match);
        return !empty($match[1]) ? $match[1] : null;
    }

    /**
     * Build ImageObject schema
     *
     * @param array $image_data Image data.
     * @param int   $index Image index for generating unique @id.
     * @return array ImageObject schema.
     */
    private function build_image_object_schema($image_data, $index = 0)
    {
        if (empty($image_data['url'])) {
            return array();
        }

        // Validate that the image URL is actually present in the post content
        // This ensures we only generate schemas for images that are displayed
        // Note: WordPress uses resized versions (-1024x682.jpg), so we check for the base filename
        global $post;
        if ($post) {
            // Extract base filename without size suffix (e.g., "image-1024x682.jpg" -> "image")
            $url_parts = pathinfo($image_data['url']);
            $base_name = preg_replace('/-\d+x\d+$/', '', $url_parts['filename']);

            // Check if the base filename exists in content (handles both original and resized versions)
            if (strpos($post->post_content, $base_name) === false) {
                // Image not found in content, skip schema generation
                return array();
            }
        }

        // Load Image Schema Output Handler if not already loaded
        if (!class_exists('Image_Schema_Output_Handler')) {
            require_once WP_CONTENT_DIR . '/plugins/swift-rank/includes/output/class-image-schema-output-handler.php';
        }

        // Use centralized handler to generate ImageObject
        $handler = Image_Schema_Output_Handler::get_instance();

        $context = array(
            'type' => 'content',
            'post_id' => get_the_ID(),
            'index' => $index,
            'image_data' => $image_data,
        );

        $schema = $handler->generate_image_object($image_data['url'], $context);

        return $schema ? $schema : array();
    }
}
