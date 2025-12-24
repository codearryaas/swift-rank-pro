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
        add_filter('swift_rank_schemas', array($this, 'add_image_schemas'), 10, 1);
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

        // Extract images from content
        $images = $this->extract_images_from_content($post->post_content);

        // Generate ImageObject schemas
        foreach ($images as $image_data) {
            $image_schema = $this->build_image_object_schema($image_data);
            if (!empty($image_schema)) {
                $schemas[] = $image_schema;
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
     * @param int   $attachment_id Attachment ID.
     * @param array $attrs Optional block attributes.
     * @return array|null Image data or null.
     */
    private function get_image_data_from_attachment($attachment_id, $attrs = array())
    {
        if (!$attachment_id) {
            return null;
        }

        $image_url = wp_get_attachment_url($attachment_id);
        if (!$image_url) {
            return null;
        }

        $metadata = wp_get_attachment_metadata($attachment_id);
        $alt_text = get_post_meta($attachment_id, '_wp_attachment_image_alt', true);
        $caption = wp_get_attachment_caption($attachment_id);
        $attachment = get_post($attachment_id);

        // Width and height
        $width = isset($metadata['width']) ? $metadata['width'] : 0;
        $height = isset($metadata['height']) ? $metadata['height'] : 0;

        // Check for block-level width/height overrides
        if (!empty($attrs['width'])) {
            $width = $attrs['width'];
        }
        if (!empty($attrs['height'])) {
            $height = $attrs['height'];
        }

        return array(
            'url' => $image_url,
            'width' => $width,
            'height' => $height,
            'caption' => $caption ? $caption : '',
            'alt' => $alt_text ? $alt_text : '',
            'name' => $attachment ? $attachment->post_title : '',
            'description' => $attachment ? $attachment->post_content : '',
        );
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
     * @return array ImageObject schema.
     */
    private function build_image_object_schema($image_data)
    {
        if (empty($image_data['url'])) {
            return array();
        }

        $schema = array(
            '@type' => 'ImageObject',
            'url' => $image_data['url'],
            'contentUrl' => $image_data['url'],
        );

        // Add width and height if available
        if (!empty($image_data['width'])) {
            $schema['width'] = $image_data['width'];
        }
        if (!empty($image_data['height'])) {
            $schema['height'] = $image_data['height'];
        }

        // Add caption
        if (!empty($image_data['caption'])) {
            $schema['caption'] = $image_data['caption'];
        }

        // Add name (title)
        if (!empty($image_data['name'])) {
            $schema['name'] = $image_data['name'];
        }

        // Add description
        if (!empty($image_data['description'])) {
            $schema['description'] = $image_data['description'];
        }

        return $schema;
    }
}
