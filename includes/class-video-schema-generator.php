<?php
/**
 * Video Schema Generator Class
 *
 * Automatically generates VideoObject schema from Gutenberg video blocks, embeds, or video tags in content.
 *
 * @package Swift_Rank_Pro
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Video_Schema_Generator class
 *
 * Handles automatic generation of VideoObject schema markup from post content.
 * Supports 14+ video platforms including YouTube, Vimeo, Wistia, and local video files.
 */
class Video_Schema_Generator
{
    /**
     * Instance of this class.
     *
     * @var Video_Schema_Generator
     */
    private static $instance = null;

    /**
     * Video platform URL patterns for detection
     *
     * @var array
     */
    private $platform_patterns = array(
        'youtube' => '/(?:youtube\.com\/(?:watch\?v=|embed\/|v\/)|youtu\.be\/)([a-zA-Z0-9_-]{11})/',
        'vimeo' => '/vimeo\.com\/(?:video\/)?(\d+)/',
        'wistia' => '/(?:wistia\.com|wi\.st)\/(?:medias|embed)\/([a-zA-Z0-9]+)/',
        'dailymotion' => '/dailymotion\.com\/(?:video|embed\/video)\/([a-zA-Z0-9]+)/',
        'videopress' => '/videopress\.com\/(?:v|embed)\/([a-zA-Z0-9]+)/',
        'vzaar' => '/vzaar\.com\/(?:videos\/)?(\d+)/',
        'viddler' => '/viddler\.com\/(?:v|embed)\/([a-zA-Z0-9]+)/',
        'screenr' => '/screenr\.com\/([a-zA-Z0-9]+)/',
        'metacafe' => '/metacafe\.com\/watch\/(\d+)/',
        'flickr' => '/flickr\.com\/photos\/[^\/]+\/(\d+)/',
        'veoh' => '/veoh\.com\/watch\/([a-zA-Z0-9]+)/',
        'wordpress_tv' => '/wordpress\.tv\/\d{4}\/\d{2}\/\d{2}\/([^\/]+)/',
        'vippy' => '/vippy\.co\/(?:view|play)\/([a-zA-Z0-9]+)/',
    );

    /**
     * Get singleton instance.
     *
     * @return Video_Schema_Generator
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
        // Hook into schema output to add VideoObject schemas
        // Priority 20 ensures this runs after video variable resolution
        add_filter('swift_rank_schemas', array($this, 'add_video_schemas'), 20, 1);
    }

    /**
     * Add VideoObject schemas to the schema graph
     *
     * @param array $schemas Existing schemas.
     * @return array Modified schemas with VideoObject added.
     */
    public function add_video_schemas($schemas)
    {
        // Check if feature is enabled
        $settings = get_option('swift_rank_settings', array());
        if (empty($settings['auto_video_schema_enabled'])) {
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

        // Extract videos from content
        $videos = $this->extract_videos_from_content($post->post_content);

        if (empty($videos)) {
            return $schemas;
        }

        // Generate VideoObject schema for each video
        foreach ($videos as $index => $video_data) {
            $video_schema = $this->build_video_object_schema($video_data, $index + 1);
            if (!empty($video_schema)) {
                $schemas[] = $video_schema;
            }
        }

        return $schemas;
    }

    /**
     * Extract videos from post content
     *
     * @param string $content Post content.
     * @return array Array of video data.
     */
    private function extract_videos_from_content($content)
    {
        $videos = array();

        // Parse Gutenberg blocks if content has blocks
        if (has_blocks($content)) {
            $blocks = parse_blocks($content);
            $videos = array_merge($videos, $this->parse_gutenberg_blocks($blocks));
        }

        // Also parse HTML for non-block videos (classic editor, manual HTML)
        $html_videos = $this->extract_from_html($content);
        $videos = array_merge($videos, $html_videos);

        // Remove duplicates based on URL
        $videos = $this->deduplicate_videos($videos);

        return $videos;
    }

    /**
     * Parse Gutenberg blocks for videos
     *
     * @param array $blocks Parsed blocks.
     * @return array Array of video data.
     */
    private function parse_gutenberg_blocks($blocks)
    {
        $videos = array();

        foreach ($blocks as $block) {
            // Handle core/video block
            if ($block['blockName'] === 'core/video') {
                $video_data = $this->parse_gutenberg_video_block($block);
                if ($video_data) {
                    $videos[] = $video_data;
                }
            }

            // Handle core/embed blocks (YouTube, Vimeo, etc.)
            if (strpos($block['blockName'], 'core/embed') === 0 || strpos($block['blockName'], 'core-embed/') === 0) {
                $video_data = $this->parse_gutenberg_embed_block($block);
                if ($video_data) {
                    $videos[] = $video_data;
                }
            }

            // Recursively parse inner blocks
            if (!empty($block['innerBlocks'])) {
                $inner_videos = $this->parse_gutenberg_blocks($block['innerBlocks']);
                $videos = array_merge($videos, $inner_videos);
            }
        }

        return $videos;
    }

    /**
     * Parse Gutenberg video block
     *
     * @param array $block Block data.
     * @return array|null Video data or null.
     */
    private function parse_gutenberg_video_block($block)
    {
        $attrs = isset($block['attrs']) ? $block['attrs'] : array();

        // Get video ID from attributes
        $video_id = isset($attrs['id']) ? $attrs['id'] : null;

        // Try to extract URL from block HTML
        $video_url = '';
        if (!empty($block['innerHTML'])) {
            if (preg_match('/<video[^>]+src=["\']([^"\']+)["\']/', $block['innerHTML'], $matches)) {
                $video_url = $matches[1];
            }
        }

        if (empty($video_url) && $video_id) {
            $video_url = wp_get_attachment_url($video_id);
        }

        if (empty($video_url)) {
            return null;
        }

        return array(
            'url' => $video_url,
            'platform' => 'local',
            'attachment_id' => $video_id,
            'caption' => isset($attrs['caption']) ? $attrs['caption'] : '',
        );
    }

    /**
     * Parse Gutenberg embed block
     *
     * @param array $block Block data.
     * @return array|null Video data or null.
     */
    private function parse_gutenberg_embed_block($block)
    {
        $attrs = isset($block['attrs']) ? $block['attrs'] : array();
        $url = isset($attrs['url']) ? $attrs['url'] : '';

        if (empty($url)) {
            // Try to extract from innerHTML
            if (!empty($block['innerHTML'])) {
                if (preg_match('/(?:src|href)=["\']([^"\']+)["\']/', $block['innerHTML'], $matches)) {
                    $url = $matches[1];
                }
            }
        }

        if (empty($url)) {
            return null;
        }

        $platform_data = $this->detect_video_platform($url);
        if (!$platform_data) {
            return null;
        }

        return array(
            'url' => $url,
            'platform' => $platform_data['platform'],
            'video_id' => $platform_data['video_id'],
            'caption' => isset($attrs['caption']) ? $attrs['caption'] : '',
        );
    }

    /**
     * Extract videos from HTML content
     *
     * @param string $content HTML content.
     * @return array Array of video data.
     */
    private function extract_from_html($content)
    {
        $videos = array();

        // Extract from <video> tags
        $videos = array_merge($videos, $this->extract_from_video_tags($content));

        // Extract from <iframe> tags
        $videos = array_merge($videos, $this->extract_from_iframes($content));

        return $videos;
    }

    /**
     * Extract videos from HTML video tags
     *
     * @param string $content HTML content.
     * @return array Array of video data.
     */
    private function extract_from_video_tags($content)
    {
        $videos = array();

        if (preg_match_all('/<video[^>]*>.*?<\/video>/is', $content, $video_tags)) {
            foreach ($video_tags[0] as $video_tag) {
                // Extract src attribute
                if (preg_match('/src=["\']([^"\']+)["\']/', $video_tag, $src_match)) {
                    $videos[] = array(
                        'url' => $src_match[1],
                        'platform' => 'local',
                        'attachment_id' => null,
                    );
                }
            }
        }

        return $videos;
    }

    /**
     * Extract videos from iframe tags
     *
     * @param string $content HTML content.
     * @return array Array of video data.
     */
    private function extract_from_iframes($content)
    {
        $videos = array();

        if (preg_match_all('/<iframe[^>]+src=["\']([^"\']+)["\'][^>]*>/i', $content, $iframes)) {
            foreach ($iframes[1] as $iframe_src) {
                $platform_data = $this->detect_video_platform($iframe_src);
                if ($platform_data) {
                    $videos[] = array(
                        'url' => $iframe_src,
                        'platform' => $platform_data['platform'],
                        'video_id' => $platform_data['video_id'],
                    );
                }
            }
        }

        return $videos;
    }

    /**
     * Detect video platform from URL
     *
     * @param string $url Video URL.
     * @return array|null Platform data or null.
     */
    private function detect_video_platform($url)
    {
        foreach ($this->platform_patterns as $platform => $pattern) {
            if (preg_match($pattern, $url, $matches)) {
                return array(
                    'platform' => $platform,
                    'video_id' => isset($matches[1]) ? $matches[1] : '',
                );
            }
        }

        return null;
    }

    /**
     * Deduplicate videos based on URL
     *
     * @param array $videos Array of video data.
     * @return array Deduplicated videos.
     */
    private function deduplicate_videos($videos)
    {
        $seen_urls = array();
        $unique_videos = array();

        foreach ($videos as $video) {
            $url = isset($video['url']) ? $video['url'] : '';
            if (!empty($url) && !in_array($url, $seen_urls)) {
                $seen_urls[] = $url;
                $unique_videos[] = $video;
            }
        }

        return $unique_videos;
    }

    /**
     * Build VideoObject schema from video data
     *
     * @param array $video_data Video data.
     * @param int   $index Video index (for unique @id).
     * @return array|null VideoObject schema or null.
     */
    private function build_video_object_schema($video_data, $index = 1)
    {
        global $post;

        $url = isset($video_data['url']) ? $video_data['url'] : '';
        $platform = isset($video_data['platform']) ? $video_data['platform'] : '';

        if (empty($url)) {
            return null;
        }

        // Get video metadata
        $metadata = $this->get_video_metadata($video_data);

        // Build base schema
        $schema = array(
            '@type' => 'VideoObject',
            '@id' => get_permalink($post->ID) . '#video-' . $index,
            'name' => !empty($metadata['name']) ? $metadata['name'] : get_the_title($post->ID),
            'description' => !empty($metadata['description']) ? $metadata['description'] : get_the_excerpt($post->ID),
            'uploadDate' => get_the_date('c', $post->ID),
        );

        // Add thumbnail
        if (!empty($metadata['thumbnailUrl'])) {
            $schema['thumbnailUrl'] = $metadata['thumbnailUrl'];
        }

        // Add content URL
        if ($platform === 'local') {
            $schema['contentUrl'] = $url;
        } else {
            $schema['embedUrl'] = $url;
        }

        // Add dimensions if available
        if (!empty($metadata['width'])) {
            $schema['width'] = $metadata['width'];
        }
        if (!empty($metadata['height'])) {
            $schema['height'] = $metadata['height'];
        }

        // Add duration if available
        if (!empty($metadata['duration'])) {
            $schema['duration'] = $metadata['duration'];
        }

        return $schema;
    }

    /**
     * Get video metadata from various sources
     *
     * @param array $video_data Video data.
     * @return array Metadata.
     */
    private function get_video_metadata($video_data)
    {
        $platform = isset($video_data['platform']) ? $video_data['platform'] : '';
        $video_id = isset($video_data['video_id']) ? $video_data['video_id'] : '';
        $attachment_id = isset($video_data['attachment_id']) ? $video_data['attachment_id'] : null;

        $metadata = array();

        // Get metadata based on platform
        switch ($platform) {
            case 'youtube':
                $metadata = $this->get_youtube_metadata($video_id);
                break;
            case 'vimeo':
                $metadata = $this->get_vimeo_metadata($video_id);
                break;
            case 'local':
                if ($attachment_id) {
                    $metadata = $this->get_local_video_metadata($attachment_id);
                }
                break;
            default:
                // Try generic oEmbed for other platforms
                $metadata = $this->get_oembed_metadata($video_data['url']);
                break;
        }

        return $metadata;
    }

    /**
     * Get YouTube video metadata via oEmbed
     *
     * @param string $video_id YouTube video ID.
     * @return array Metadata.
     */
    private function get_youtube_metadata($video_id)
    {
        $cache_key = 'swift_rank_youtube_' . $video_id;
        $cached = get_transient($cache_key);

        if ($cached !== false) {
            return $cached;
        }

        $oembed_url = 'https://www.youtube.com/oembed?url=https://www.youtube.com/watch?v=' . $video_id . '&format=json';
        $response = wp_remote_get($oembed_url, array('timeout' => 5));

        if (is_wp_error($response)) {
            return array();
        }

        $data = json_decode(wp_remote_retrieve_body($response), true);

        $metadata = array(
            'name' => isset($data['title']) ? $data['title'] : '',
            'thumbnailUrl' => isset($data['thumbnail_url']) ? $data['thumbnail_url'] : "https://img.youtube.com/vi/{$video_id}/maxresdefault.jpg",
            'width' => isset($data['width']) ? $data['width'] : '',
            'height' => isset($data['height']) ? $data['height'] : '',
        );

        // Cache for 24 hours
        set_transient($cache_key, $metadata, DAY_IN_SECONDS);

        return $metadata;
    }

    /**
     * Get Vimeo video metadata via oEmbed
     *
     * @param string $video_id Vimeo video ID.
     * @return array Metadata.
     */
    private function get_vimeo_metadata($video_id)
    {
        $cache_key = 'swift_rank_vimeo_' . $video_id;
        $cached = get_transient($cache_key);

        if ($cached !== false) {
            return $cached;
        }

        $oembed_url = 'https://vimeo.com/api/oembed.json?url=https://vimeo.com/' . $video_id;
        $response = wp_remote_get($oembed_url, array('timeout' => 5));

        if (is_wp_error($response)) {
            return array();
        }

        $data = json_decode(wp_remote_retrieve_body($response), true);

        $metadata = array(
            'name' => isset($data['title']) ? $data['title'] : '',
            'description' => isset($data['description']) ? $data['description'] : '',
            'thumbnailUrl' => isset($data['thumbnail_url']) ? $data['thumbnail_url'] : '',
            'width' => isset($data['width']) ? $data['width'] : '',
            'height' => isset($data['height']) ? $data['height'] : '',
            'duration' => isset($data['duration']) ? 'PT' . $data['duration'] . 'S' : '',
        );

        // Cache for 24 hours
        set_transient($cache_key, $metadata, DAY_IN_SECONDS);

        return $metadata;
    }

    /**
     * Get local video metadata from attachment
     *
     * @param int $attachment_id Attachment ID.
     * @return array Metadata.
     */
    private function get_local_video_metadata($attachment_id)
    {
        $metadata = wp_get_attachment_metadata($attachment_id);

        return array(
            'name' => get_the_title($attachment_id),
            'thumbnailUrl' => get_the_post_thumbnail_url($attachment_id, 'large'),
            'width' => isset($metadata['width']) ? $metadata['width'] : '',
            'height' => isset($metadata['height']) ? $metadata['height'] : '',
            'duration' => isset($metadata['length_formatted']) ? $this->convert_duration_to_iso8601($metadata['length']) : '',
        );
    }

    /**
     * Get generic oEmbed metadata
     *
     * @param string $url Video URL.
     * @return array Metadata.
     */
    private function get_oembed_metadata($url)
    {
        $cache_key = 'swift_rank_oembed_' . md5($url);
        $cached = get_transient($cache_key);

        if ($cached !== false) {
            return $cached;
        }

        $oembed_data = wp_oembed_get($url);

        if (!$oembed_data) {
            return array();
        }

        // Parse oEmbed HTML response
        $metadata = array();
        if (preg_match('/<title>([^<]+)<\/title>/', $oembed_data, $title_match)) {
            $metadata['name'] = $title_match[1];
        }

        // Cache for 24 hours
        set_transient($cache_key, $metadata, DAY_IN_SECONDS);

        return $metadata;
    }

    /**
     * Convert duration in seconds to ISO 8601 format
     *
     * @param int $seconds Duration in seconds.
     * @return string ISO 8601 duration.
     */
    private function convert_duration_to_iso8601($seconds)
    {
        if (empty($seconds)) {
            return '';
        }

        $hours = floor($seconds / 3600);
        $minutes = floor(($seconds % 3600) / 60);
        $secs = $seconds % 60;

        $duration = 'PT';
        if ($hours > 0) {
            $duration .= $hours . 'H';
        }
        if ($minutes > 0) {
            $duration .= $minutes . 'M';
        }
        if ($secs > 0 || ($hours == 0 && $minutes == 0)) {
            $duration .= $secs . 'S';
        }

        return $duration;
    }
}
