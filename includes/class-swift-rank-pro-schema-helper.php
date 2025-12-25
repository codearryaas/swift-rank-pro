<?php
/**
 * Swift Rank Pro Schema Helper
 *
 * @package Swift_Rank_Pro
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Swift_Rank_Pro_Schema_Helper class
 */
class Swift_Rank_Pro_Schema_Helper
{
    /**
     * Get all Pro schema builder instances
     *
     * @return array Array of schema builder instances keyed by type.
     */
    public static function get_pro_schema_builders()
    {
        // Ensure classes are loaded
        self::load_pro_schema_files();

        return array(
            'Recipe' => new Schema_Recipe(),
            'PodcastEpisode' => new Schema_Podcast_Episode(),
            'Event' => new Schema_Event(),
            'HowTo' => new Schema_Howto(),
            'SoftwareApplication' => new Schema_Software_Application(),
            'Custom' => new Schema_Custom(),
        );
    }

    /**
     * Load all Pro schema class files
     */
    public static function load_pro_schema_files()
    {
        if (!defined('SWIFT_RANK_PRO_PATH')) {
            return;
        }

        $files = array(
            'podcast-episode',
            'recipe',
            'custom',
            'event',
            'howto',
            'software-application',
        );

        foreach ($files as $file) {
            $path = SWIFT_RANK_PRO_PATH . 'includes/output/types/class-' . $file . '-schema.php';
            if (file_exists($path)) {
                require_once $path;
            }
        }
    }
}
