<?php
/**
 * Recipe Schema Builder (Pro)
 *
 * @package Swift_Rank_Pro
 */

if (!defined('ABSPATH')) {
	exit;
}

/**
 * Schema_Recipe class
 *
 * Builds Recipe schema type.
 */
class Schema_Recipe implements Schema_Builder_Interface
{

	/**
	 * Build recipe schema from fields
	 *
	 * @param array $fields Field values.
	 * @return array Schema array (without @context).
	 */
	public function build($fields)
	{
		$schema = array(
			'@type' => 'Recipe',
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

		if (!empty($fields['prepTime'])) {
			$schema['prepTime'] = $fields['prepTime'];
		}

		if (!empty($fields['cookTime'])) {
			$schema['cookTime'] = $fields['cookTime'];
		}

		if (!empty($fields['totalTime'])) {
			$schema['totalTime'] = $fields['totalTime'];
		}

		if (!empty($fields['recipeYield'])) {
			$schema['recipeYield'] = $fields['recipeYield'];
		}

		if (!empty($fields['calories'])) {
			$schema['nutrition'] = array(
				'@type' => 'NutritionInformation',
				'calories' => $fields['calories'],
			);
		}

		if (!empty($fields['recipeCategory'])) {
			$schema['recipeCategory'] = $fields['recipeCategory'];
		}

		if (!empty($fields['recipeCuisine'])) {
			$schema['recipeCuisine'] = $fields['recipeCuisine'];
		}

		if (!empty($fields['keywords'])) {
			$schema['keywords'] = $fields['keywords'];
		}

		return $schema;
	}

	/**
	 * Get schema.org structure for Recipe type
	 *
	 * @return array Schema.org structure specification.
	 */
	public function get_schema_structure()
	{
		return array(
			'@type' => 'Recipe',
			'@context' => 'https://schema.org',
			'label' => __('Recipe', 'swift-rank'),
			'description' => __('A recipe to make a dish.', 'swift-rank'),
			'url' => 'https://schema.org/Recipe',
			'icon' => 'chef-hat',
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
				'name' => 'headline',
				'label' => __('Recipe Name', 'swift-rank'),
				'type' => 'select',
				'allowCustom' => true,
				'tooltip' => __('Recipe name. Click pencil icon to use variables.', 'swift-rank'),
				'placeholder' => '{post_title}',
				'options' => array(
					array(
						'label' => __('Post Title', 'swift-rank'),
						'value' => '{post_title}',
					),
				),
				'default' => '{post_title}',
				'required' => true,
			),
			array(
				'name' => 'url',
				'label' => __('Recipe URL', 'swift-rank'),
				'type' => 'select',
				'allowCustom' => true,
				'tooltip' => __('Recipe URL. Click pencil icon to enter custom URL.', 'swift-rank'),
				'placeholder' => '{post_url}',
				'options' => array(
					array(
						'label' => __('Post URL', 'swift-rank'),
						'value' => '{post_url}',
					),
				),
				'default' => '{post_url}',
			),
			array(
				'name' => 'description',
				'label' => __('Description', 'swift-rank'),
				'type' => 'select',
				'allowCustom' => true,
				'customType' => 'textarea',
				'rows' => 4,
				'tooltip' => __('Recipe description. Click pencil icon to use variables.', 'swift-rank'),
				'placeholder' => '{post_excerpt}',
				'options' => array(
					array(
						'label' => __('Post Excerpt', 'swift-rank'),
						'value' => '{post_excerpt}',
					),
					array(
						'label' => __('Post Content', 'swift-rank'),
						'value' => '{post_content}',
					),
				),
				'default' => '{post_excerpt}',
			),
			array(
				'name' => 'imageUrl',
				'label' => __('Recipe Image URL', 'swift-rank'),
				'type' => 'select',
				'allowCustom' => true,
				'tooltip' => __('Recipe image. Click pencil icon to enter custom URL.', 'swift-rank'),
				'placeholder' => '{featured_image}',
				'options' => array(
					array(
						'label' => __('Featured Image', 'swift-rank'),
						'value' => '{featured_image}',
					),
				),
				'default' => '{featured_image}',
				'required' => true,
			),
			array(
				'name' => 'authorName',
				'label' => __('Author Name', 'swift-rank'),
				'type' => 'select',
				'allowCustom' => true,
				'tooltip' => __('Author name. Click pencil icon to use variables.', 'swift-rank'),
				'placeholder' => '{author_name}',
				'options' => array(
					array(
						'label' => __('Author Name', 'swift-rank'),
						'value' => '{author_name}',
					),
				),
				'default' => '{author_name}',
			),
			array(
				'name' => 'prepTime',
				'label' => __('Prep Time', 'swift-rank'),
				'type' => 'duration',
				'tooltip' => __('Preparation time in ISO 8601 duration format (e.g., PT30M for 30 minutes). Use picker to set time.', 'swift-rank'),
				'placeholder' => 'PT30M',
			),
			array(
				'name' => 'cookTime',
				'label' => __('Cook Time', 'swift-rank'),
				'type' => 'duration',
				'tooltip' => __('Cooking time in ISO 8601 duration format (e.g., PT1H for 1 hour). Use picker to set time.', 'swift-rank'),
				'placeholder' => 'PT1H',
			),
			array(
				'name' => 'totalTime',
				'label' => __('Total Time', 'swift-rank'),
				'type' => 'duration',
				'tooltip' => __('Total time in ISO 8601 duration format (e.g., PT1H30M for 1 hour 30 minutes). Use picker to set time.', 'swift-rank'),
				'placeholder' => 'PT1H30M',
			),
			array(
				'name' => 'recipeYield',
				'label' => __('Recipe Yield', 'swift-rank'),
				'type' => 'text',
				'tooltip' => __('Number of servings or quantity produced (e.g., "4 servings" or "8 cookies").', 'swift-rank'),
				'placeholder' => '4 servings',
			),
			array(
				'name' => 'calories',
				'label' => __('Calories', 'swift-rank'),
				'type' => 'text',
				'tooltip' => __('Calories per serving (e.g., "240 calories").', 'swift-rank'),
				'placeholder' => '240 calories',
			),
			array(
				'name' => 'recipeCategory',
				'label' => __('Category', 'swift-rank'),
				'type' => 'text',
				'tooltip' => __('Type of dish (e.g., appetizer, entree, dessert).', 'swift-rank'),
				'placeholder' => 'Dinner',
			),
			array(
				'name' => 'recipeCuisine',
				'label' => __('Cuisine', 'swift-rank'),
				'type' => 'text',
				'tooltip' => __('Type of cuisine (e.g., Italian, Mexican, Indian).', 'swift-rank'),
				'placeholder' => 'Italian',
			),
			array(
				'name' => 'keywords',
				'label' => __('Keywords', 'swift-rank'),
				'type' => 'text',
				'tooltip' => __('Comma-separated keywords for the recipe.', 'swift-rank'),
				'placeholder' => 'pasta, easy, quick',
			),
		);
	}

}
