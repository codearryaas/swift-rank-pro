<?php
/**
 * Video Schema Pro Extension
 *
 * Extends Video schema with Pro features including paywall support.
 *
 * @package Swift_Rank_Pro
 */

if (!defined('ABSPATH')) {
    exit;
}

// Load the paywall fields helper
require_once dirname(__FILE__) . '/class-paywall-fields-helper.php';

/**
 * Schema_Video_Pro class
 *
 * Extends Video schema with Pro features.
 */
class Schema_Video_Pro
{

    /**
     * Instance of this class
     *
     * @var object
     */
    private static $instance = null;

    /**
     * Get singleton instance
     *
     * @return Schema_Video_Pro
     */
    public static function get_instance()
    {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Constructor
     */
    private function __construct()
    {
        add_filter('swift_rank_get_fields', array($this, 'add_pro_fields'), 10, 2);
        add_filter('swift_rank_output_schema', array($this, 'add_pro_schema_data'), 10, 3);
    }

    /**
     * Add Pro fields to Video schema
     *
     * @param array  $fields      Existing fields.
     * @param string $schema_type Schema type.
     * @return array Modified fields.
     */
    public function add_pro_fields($fields, $schema_type)
    {
        if ('VideoObject' !== $schema_type) {
            return $fields;
        }
        $pro_fields = array(
            // SeekToAction
            array(
                'name' => 'seekTarget',
                'label' => __('Seek To Action Target', 'swift-rank-pro'),
                'type' => 'text',
                'tooltip' => __('URL pattern for deep linking (e.g., https://example.com/video?t={seek_to_second_number}).', 'swift-rank-pro'),
                'placeholder' => 'https://example.com/video?t={seek_to_second_number}',
            ),

            // BroadcastEvent (Publication)
            array(
                'name' => 'isLiveBroadcast',
                'label' => __('Live Broadcast', 'swift-rank-pro'),
                'type' => 'checkbox',
                'tooltip' => __('Check if this is a live broadcast.', 'swift-rank-pro'),
            ),
            array(
                'name' => 'broadcastStartDate',
                'label' => __('Broadcast Start Date', 'swift-rank-pro'),
                'type' => 'text',
                'tooltip' => __('Start date/time in ISO 8601 format.', 'swift-rank-pro'),
                'placeholder' => '{post_date}',
            ),
            array(
                'name' => 'broadcastEndDate',
                'label' => __('Broadcast End Date', 'swift-rank-pro'),
                'type' => 'text',
                'tooltip' => __('End date/time in ISO 8601 format.', 'swift-rank-pro'),
            ),

            // Clip (HasPart) - Repeater
            array(
                'name' => 'clips',
                'label' => __('Video Clips', 'swift-rank-pro'),
                'type' => 'repeater',
                'tooltip' => __('Add key clips or segments for this video.', 'swift-rank-pro'),
                'fields' => array(
                    array(
                        'name' => 'clipName',
                        'label' => __('Clip Name', 'swift-rank-pro'),
                        'type' => 'text',
                        'tooltip' => __('Name of the clip.', 'swift-rank-pro'),
                        'required' => true,
                    ),
                    array(
                        'name' => 'clipStartOffset',
                        'label' => __('Start Offset', 'swift-rank-pro'),
                        'type' => 'text',
                        'tooltip' => __('Start time in seconds (e.g., 30).', 'swift-rank-pro'),
                        'required' => true,
                    ),
                    array(
                        'name' => 'clipEndOffset',
                        'label' => __('End Offset', 'swift-rank-pro'),
                        'type' => 'text',
                        'tooltip' => __('End time in seconds (e.g., 60).', 'swift-rank-pro'),
                    ),
                    array(
                        'name' => 'clipUrl',
                        'label' => __('Clip URL', 'swift-rank-pro'),
                        'type' => 'url',
                        'tooltip' => __('Direct URL to the clip.', 'swift-rank-pro'),
                    ),
                ),
            ),
        );

        return array_merge($fields, $pro_fields);
    }

    /**
     * Add Pro data to Video schema output
     *
     * @param array  $schema      Schema array.
     * @param string $schema_type Schema type.
     * @param array  $fields      Field values.
     * @return array Modified schema.
     */
    public function add_pro_schema_data($schema, $schema_type, $fields)
    {
        if ('VideoObject' !== $schema_type) {
            return $schema;
        }

        // SeekToAction
        if (!empty($fields['seekTarget'])) {
            $schema['potentialAction'] = array(
                '@type' => 'SeekToAction',
                'target' => $fields['seekTarget'],
                'startOffset-input' => 'required name=seek_to_second_number',
            );
        }

        // Publication (BroadcastEvent)
        if (!empty($fields['isLiveBroadcast'])) {
            $publication = array(
                '@type' => 'BroadcastEvent',
                'isLiveBroadcast' => true,
            );

            if (!empty($fields['broadcastStartDate'])) {
                $publication['startDate'] = $fields['broadcastStartDate'];
            }

            if (!empty($fields['broadcastEndDate'])) {
                $publication['endDate'] = $fields['broadcastEndDate'];
            }

            $schema['publication'] = $publication;
        }

        // Clips (HasPart)
        if (!empty($fields['clips']) && is_array($fields['clips'])) {
            $has_part = array();

            foreach ($fields['clips'] as $clip_data) {
                if (empty($clip_data['clipName']) || empty($clip_data['clipStartOffset'])) {
                    continue;
                }

                $clip = array(
                    '@type' => 'Clip',
                    'name' => $clip_data['clipName'],
                    'startOffset' => $clip_data['clipStartOffset'],
                );

                if (!empty($clip_data['clipEndOffset'])) {
                    $clip['endOffset'] = $clip_data['clipEndOffset'];
                }

                if (!empty($clip_data['clipUrl'])) {
                    $clip['url'] = $clip_data['clipUrl'];
                }

                $has_part[] = $clip;
            }

            if (!empty($has_part)) {
                $schema['hasPart'] = $has_part;
            }
        }

        // Process Paywall Fields
        $schema = Paywall_Fields_Helper::process_paywall_schema($schema, $fields);

        return $schema;
    }

}

// Initialize
Schema_Video_Pro::get_instance();
