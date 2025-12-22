<?php
/**
 * Swift Rank Pro Conditions
 *
 * @package Swift_Rank_Pro
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Swift_Rank_Pro_Conditions class
 */
class Swift_Rank_Pro_Conditions
{

    /**
     * Instance
     *
     * @var Swift_Rank_Pro_Conditions
     */
    private static $instance = null;

    /**
     * Get instance
     *
     * @return Swift_Rank_Pro_Conditions
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
        add_filter('swift_rank_evaluate_rule', array($this, 'evaluate_pro_rules'), 10, 2);
    }

    /**
     * Evaluate Pro rules
     *
     * @param bool  $match Match result so far.
     * @param array $rule  Rule data.
     * @return bool
     */
    public function evaluate_pro_rules($match, $rule)
    {
        if ($match) {
            return true; // Already matched
        }

        $condition_type = isset($rule['conditionType']) ? $rule['conditionType'] : '';

        if ('user_role' === $condition_type) {
            return $this->evaluate_user_role_rule($rule);
        }

        // Note: author_archive is now handled as a location value in the base class
        // No need for separate handling here

        return $match;
    }

    /**
     * Evaluate user role rule
     *
     * @param array $rule Rule data.
     * @return bool
     */
    private function evaluate_user_role_rule($rule)
    {
        $value = isset($rule['value']) ? (array) $rule['value'] : array();

        if (empty($value)) {
            return false;
        }

        // User role condition ONLY works on author archives
        // This prevents SEO issues where schema is hidden from Googlebot
        if (!is_author()) {
            return false;
        }

        // On author archive, check the author's role
        $author = get_queried_object();
        if (!$author || !isset($author->roles)) {
            return false;
        }

        $user_roles = (array) $author->roles;

        foreach ($value as $role) {
            if (in_array($role, $user_roles, true)) {
                return true;
            }
        }

        return false;
    }
}
