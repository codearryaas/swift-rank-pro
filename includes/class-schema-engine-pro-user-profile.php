<?php
/**
 * User Profile Schema Handler (Pro)
 *
 * @package Swift_Rank_Pro
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Swift_Rank_Pro_User_Profile class
 * 
 * Extends the Free version to add Pro-specific features:
 * - Template matching logic
 * - Save functionality for overrides
 * - Hidden input field for React state persistence
 */
class Swift_Rank_Pro_User_Profile extends Swift_Rank_User_Profile
{

    /**
     * Instance
     *
     * @var Swift_Rank_Pro_User_Profile
     */
    private static $instance = null;

    /**
     * Get instance
     *
     * @return Swift_Rank_Pro_User_Profile
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
        parent::__construct();

        // Add save hooks (Pro only)
        add_action('personal_options_update', array($this, 'save_fields'));
        add_action('edit_user_profile_update', array($this, 'save_fields'));
    }

    /**
     * Override to remove Pro check (Pro is active)
     *
     * @param WP_User $user User object.
     */
    public function render_fields($user)
    {
        if (!current_user_can('manage_options')) {
            return;
        }

        $this->enqueue_assets($user);
        $this->render_html($user);
    }

    /**
     * Override to add matching templates and saved overrides
     *
     * @param WP_User $user User object.
     * @return array
     */
    protected function get_localized_data($user)
    {
        $saved_overrides = get_user_meta($user->ID, '_swift_rank_overrides', true);

        // Get available schema templates
        $templates = get_posts(array(
            'post_type' => 'sr_template',
            'posts_per_page' => -1,
            'post_status' => 'publish',
        ));

        // Prepare matching templates array
        $matching_templates = array();

        foreach ($templates as $template) {
            $conditions = get_post_meta($template->ID, '_schema_template_conditions', true);

            // Check if this template matches the user
            if ($this->user_matches_conditions($user, $conditions)) {
                $schema_data = get_post_meta($template->ID, '_schema_template_data', true);
                if ($schema_data) {
                    $matching_templates[] = array(
                        'id' => $template->ID,
                        'title' => $template->post_title,
                        'schemaType' => isset($schema_data['schemaType']) ? $schema_data['schemaType'] : '',
                        'fields' => isset($schema_data['fields']) ? $schema_data['fields'] : array(),
                    );
                }
            }
        }

        return array(
            'postId' => $user->ID,
            'matchingTemplates' => $matching_templates,
            'savedOverrides' => is_array($saved_overrides) ? $saved_overrides : array(),
            'schemaTypes' => Schema_Type_Helper::get_types_for_select(),
            'nonce' => wp_create_nonce('swift_rank_metabox'),
            'context' => 'user-profile',
        );
    }

    /**
     * Override to add hidden input field and update description
     *
     * @param WP_User $user User object.
     */
    protected function render_html($user)
    {
        $saved_overrides = get_user_meta($user->ID, '_swift_rank_overrides', true);
        ?>
        <h3><?php esc_html_e('Swift Rank Settings', 'swift-rank'); ?></h3>

        <div class="swift-rank-user-profile-wrapper" style="max-width: 800px; margin-top: 20px;">
            <p class="description" style="margin-bottom: 15px;">
                <?php esc_html_e('Templates matching this user\'s role are automatically applied. Use the form below to override specific fields.', 'swift-rank'); ?>
            </p>

            <div id="swift-rank-user-profile-root"></div>

            <!-- Hidden input for overrides -->
            <input type="hidden" id="swift-rank-overrides-input" name="swift_rank_overrides"
                value="<?php echo esc_attr(json_encode($saved_overrides)); ?>">
        </div>
        <?php
    }

    /**
     * Check if user matches template conditions
     * 
     * @param WP_User $user The user object.
     * @param array $conditions Conditions array.
     * @return bool
     */
    private function user_matches_conditions($user, $conditions)
    {
        if (empty($conditions) || empty($conditions['groups'])) {
            return false;
        }

        // Check each group (OR logic between groups)
        foreach ($conditions['groups'] as $group) {
            if (empty($group['rules'])) {
                continue;
            }

            $group_match = true; // AND logic within group

            foreach ($group['rules'] as $rule) {
                $rule_match = false;
                $condition_type = isset($rule['conditionType']) ? $rule['conditionType'] : '';
                $operator = isset($rule['operator']) ? $rule['operator'] : 'equal_to';

                // Handle user_role condition
                if ($condition_type === 'user_role') {
                    $roles = isset($rule['value']) ? (array) $rule['value'] : array();

                    // Check if user has any of the required roles
                    foreach ($roles as $role) {
                        if (in_array($role, (array) $user->roles)) {
                            $rule_match = true;
                            break;
                        }
                    }
                }

                // Handle location condition
                if ($condition_type === 'location') {
                    $location_value = isset($rule['value'][0]) ? $rule['value'][0] : '';

                    // In user profile context, check for author_archive
                    if ($location_value === 'author_archive') {
                        $rule_match = true; // Always true in user profile context
                    } else if ($location_value === 'whole_site') {
                        $rule_match = true; // Whole site always matches
                    }
                    // Other location values don't apply in user profile context
                }

                // Apply operator (invert for not_equal_to)
                if ($operator === 'not_equal_to') {
                    $rule_match = !$rule_match;
                }

                // If any rule in the group doesn't match (AND logic), group fails
                if (!$rule_match) {
                    $group_match = false;
                    break;
                }
            }

            // If any group matches (OR logic), return true
            if ($group_match) {
                return true;
            }
        }

        return false;
    }

    /**
     * Save fields
     *
     * @param int $user_id User ID.
     */
    public function save_fields($user_id)
    {
        if (!current_user_can('manage_options')) {
            return;
        }

        if (isset($_POST['swift_rank_overrides'])) {
            $overrides = json_decode(wp_unslash($_POST['swift_rank_overrides']), true);
            if (!empty($overrides)) {
                update_user_meta($user_id, '_swift_rank_overrides', $overrides);
            } else {
                delete_user_meta($user_id, '_swift_rank_overrides');
            }
        }
    }
}
