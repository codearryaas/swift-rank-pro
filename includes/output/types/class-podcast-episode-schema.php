<?php
/**
 * Podcast Episode Schema Builder (Pro)
 *
 * @package Swift_Rank_Pro
 */

if (!defined('ABSPATH')) {
	exit;
}

/**
 * Schema_Podcast_Episode class
 *
 * Builds PodcastEpisode schema type.
 */
class Schema_Podcast_Episode implements Schema_Builder_Interface
{

	/**
	 * Build podcast episode schema from fields
	 *
	 * @param array $fields Field values.
	 * @return array Schema array (without @context).
	 */
	public function build($fields)
	{
		$schema = array(
			'@type' => 'PodcastEpisode',
			'name' => isset($fields['headline']) ? $fields['headline'] : '{post_title}',
			'url' => isset($fields['url']) ? $fields['url'] : '{post_url}',
		);

		if (!empty($fields['description'])) {
			$schema['description'] = $fields['description'];
		}

		// Image with fallback to featured_image variable
		$image_url = !empty($fields['imageUrl']) ? $fields['imageUrl'] : '{featured_image}';
		$schema['image'] = $image_url;

		if (!empty($fields['authorName'])) {
			$schema['author'] = array(
				'@type' => 'Person',
				'name' => $fields['authorName'],
			);
		}

		if (!empty($fields['partOfSeries'])) {
			$schema['partOfSeries'] = array(
				'@type' => 'PodcastSeries',
				'url' => $fields['partOfSeries'],
			);
		}

		if (!empty($fields['episodeNumber'])) {
			$schema['episodeNumber'] = $fields['episodeNumber'];
		}

		if (!empty($fields['seasonNumber'])) {
			$schema['seasonNumber'] = $fields['seasonNumber'];
		}

		if (!empty($fields['duration'])) {
			$schema['timeRequired'] = $fields['duration'];
		}

		if (!empty($fields['audioUrl'])) {
			$schema['associatedMedia'] = array(
				'@type' => 'MediaObject',
				'contentUrl' => $fields['audioUrl'],
				'uploadDate' => isset($fields['datePublished']) ? $fields['datePublished'] : '{post_date}',
			);
		}

		return $schema;
	}

	/**
	 * Get field configuration for React components
	 *
	 * @return array Field configuration array.
	 */
	public function get_fields()
	{
		return array(
			array(
				'name' => 'headline',
				'label' => __('Episode Title', 'swift-rank-pro'),
				'type' => 'select',
				'allowCustom' => true,
				'tooltip' => __('Episode title. Click pencil icon to use variables.', 'swift-rank-pro'),
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
				'label' => __('Episode URL', 'swift-rank-pro'),
				'type' => 'select',
				'allowCustom' => true,
				'tooltip' => __('Episode URL. Click pencil icon to enter custom URL.', 'swift-rank-pro'),
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
				'tooltip' => __('Episode description. Click pencil icon to use variables.', 'swift-rank-pro'),
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
				'default' => '',
			),
			array(
				'name' => 'imageUrl',
				'label' => __('Episode Image', 'swift-rank-pro'),
				'type' => 'select',
				'allowCustom' => true,
				'tooltip' => __('Episode image URL. Click pencil icon to enter custom URL.', 'swift-rank-pro'),
				'placeholder' => '{featured_image}',
				'options' => array(
					array(
						'label' => __('Featured Image', 'swift-rank-pro'),
						'value' => '{featured_image}',
					),
				),
				'default' => '{featured_image}',
			),
			array(
				'name' => 'authorName',
				'label' => __('Host/Author Name', 'swift-rank-pro'),
				'type' => 'select',
				'allowCustom' => true,
				'tooltip' => __('Host/Author name. Click pencil icon to use variables.', 'swift-rank-pro'),
				'placeholder' => '{author_name}',
				'options' => array(
					array(
						'label' => __('Author Name', 'swift-rank-pro'),
						'value' => '{author_name}',
					),
				),
				'default' => '',
			),
			array(
				'name' => 'partOfSeries',
				'label' => __('Podcast Series URL', 'swift-rank-pro'),
				'type' => 'select',
				'allowCustom' => true,
				'tooltip' => __('Podcast Series URL. Click pencil icon to enter custom URL.', 'swift-rank-pro'),
				'placeholder' => 'https://example.com/podcast/',
				'options' => array(
					array(
						'label' => __('Site URL', 'swift-rank-pro'),
						'value' => '{site_url}',
					),
				),
				'default' => '',
			),
			array(
				'name' => 'episodeNumber',
				'label' => 'Episode Number',
				'type' => 'number',
				'tooltip' => 'The episode number in the series',
				'placeholder' => '1',
				'default' => '',
			),
			array(
				'name' => 'seasonNumber',
				'label' => 'Season Number',
				'type' => 'number',
				'tooltip' => 'The season number (if applicable)',
				'placeholder' => '1',
				'default' => '',
			),
			array(
				'name' => 'duration',
				'label' => 'Duration',
				'type' => 'text',
				'tooltip' => 'Episode duration in ISO 8601 format (e.g., PT30M for 30 minutes)',
				'placeholder' => 'PT30M',
				'default' => '',
			),
			array(
				'name' => 'audioUrl',
				'label' => __('Audio URL', 'swift-rank-pro'),
				'type' => 'select',
				'allowCustom' => true,
				'tooltip' => __('Audio file URL. Click pencil icon to enter custom URL.', 'swift-rank-pro'),
				'placeholder' => 'https://example.com/episode.mp3',
				'options' => array(
					array(
						'label' => __('Custom URL', 'swift-rank-pro'),
						'value' => '',
					),
				),
				'default' => '',
			),
			array(
				'name' => 'datePublished',
				'label' => __('Date Published', 'swift-rank-pro'),
				'type' => 'date',
				'tooltip' => __('The date the episode was published. Click pencil icon to use variables or pick date.', 'swift-rank-pro'),
				'placeholder' => '{post_date}',
				'default' => '{post_date}',
			),
		);
	}

	/**
	 * Get schema.org structure for PodcastEpisode type
	 *
	 * @return array Schema.org structure specification.
	 */
	public function get_schema_structure()
	{
		return array(
			'@type' => 'PodcastEpisode',
			'@context' => 'https://schema.org',
			'label' => __('Podcast Episode', 'swift-rank-pro'),
			'description' => __('A single episode of a podcast series.', 'swift-rank-pro'),
			'url' => 'https://schema.org/PodcastEpisode',
			'icon' => 'podcast',
			'showInDropdown' => false,
		);
	}

}
