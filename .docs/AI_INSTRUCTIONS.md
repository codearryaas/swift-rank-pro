# Schema Engine Pro - AI Instructions

## Overview

**Schema Engine Pro** is a premium addon for the **Schema Engine** WordPress plugin. It extends the functionality of Schema Engine by adding advanced features and premium capabilities.

## Core Information

### Plugin Purpose
- Premium addon that extends Schema Engine functionality
- Adds advanced schema features not available in the free version
- Uses Freemius.com package to enable and manage premium features
- Integrates seamlessly with Schema Engine's existing architecture

### Key Features
- Premium licensing and updates via Freemius
- Advanced schema configuration options
- Extended settings and customization capabilities

## Technical Architecture

### Plugin Structure

```
schema-engine-pro/
├── .docs/
│   └── AI_INSTRUCTIONS.md
├── includes/
│   ├── class-schema-engine-pro-admin.php
│   └── class-schema-engine-pro-freemius.php
├── assets/
│   ├── css/
│   └── js/
└── schema-engine-pro.php (main plugin file)
```

### Integration with Schema Engine

Schema Engine Pro extends the base Schema Engine plugin:
- Hooks into Schema Engine's existing menu structure
- Extends Schema Engine's schema output capabilities
- Adds premium-only schema types and features
- Maintains compatibility with Schema Engine's core functionality

### Required Base Plugin Hooks
To support the features described, the base plugin (`schema-engine`) must be updated to include the following hooks:

1. **Output Location Filter**
   - **File:** `includes/class-schema-engine-output.php`
   - **Method:** `init_schema_output_hook`
   - **Code:** `$placement = apply_filters( 'schema_engine_output_location', $placement );`

2. **Default Image Filter**
   - **File:** `includes/class-schema-engine-output.php`
   - **Method:** `build_article_schema_from_fields` (and others)
   - **Code:** `$image_url = apply_filters( 'schema_engine_default_image', $image_url );`

### Dependencies
- **Required:** Schema Engine (base plugin) must be installed and active
- **Licensing:** Freemius SDK for license management, updates, and premium feature activation
- **WordPress:** Minimum version 5.0
- **PHP:** Minimum version 7.0

## Settings Page Implementation

### Admin Menu Integration

Schema Engine Pro adds its settings page under the existing "Schema Engine" menu from the base plugin.

**Menu Structure:**
```
WordPress Admin
└── Settings
    └── Schema Engine (from base plugin)
        ├── Knowledge Graph (base plugin)
        ├── Help (base plugin)
        └── Pro Settings (Schema Engine Pro) ← NEW
```

### Settings Page Tabs

The Pro Settings page includes two main tabs:

#### Tab 1: General Settings

**Schema Code Placement**
- **Field Type:** Dropdown select
- **Label:** "Add Schema Code in"
- **Options:**
  - Head (default) - Outputs schema in `<head>` section
  - Footer - Outputs schema before `</body>` closing tag
- **Description:** "Choose where to output the schema JSON-LD code on your site."
- **Default:** Head
- **Setting Name:** `schema_engine_pro_settings[code_placement]`

**Default Image**
- **Field Type:** Image upload with media library integration
- **Label:** "Default Schema Image"
- **Features:**
  - Upload button that opens WordPress media library
  - Image preview after selection
  - Remove/clear button
  - URL input field (read-only or manual entry)
- **Description:** "Set a default image to use in schema markup when posts/pages don't have a featured image. This is used as fallback for Article, BlogPosting, and other schema types that require an image."
- **Help Text:**
  - "Recommended: Minimum 1200x675px (16:9 ratio)"
  - "Formats: JPG, PNG, WebP"
  - "This image will be used when no featured image is available"
- **Setting Name:** `schema_engine_pro_settings[default_image]`

**Additional General Settings (Future Expansion):**
- Premium schema types toggle
- Advanced output options
- Custom schema templates

#### Tab 2: Help

**Content Sections:**

1. **Getting Started with Schema Engine Pro**
   - Overview of premium features
   - Quick setup guide
   - Links to documentation

2. **Premium Features**
   - List and description of all premium features
   - How to use each premium feature
   - Best practices

3. **Schema Code Placement**
   - Explanation of Head vs Footer placement
   - When to use each option
   - Performance considerations

4. **Default Image Settings**
   - Why default images are important
   - Image size and format recommendations
   - How fallback images work in schema markup

5. **Support & Documentation**
   - Link to premium support
   - Documentation resources
   - Video tutorials (if available)
   - Contact information

6. **License Information**
   - Current license status (pulled from Freemius)
   - Activation/deactivation
   - Upgrade options

## Code Implementation Guidelines

### Main Plugin File (schema-engine-pro.php)

```php
<?php
/**
 * Plugin Name: Schema Engine Pro
 * Plugin URI: https://toolpress.net/plugins/schema-engine-pro/
 * Description: Premium addon for Schema Engine with advanced schema features and capabilities.
 * Version: 1.0.0
 * Requires at least: 5.0
 * Requires PHP: 7.0
 * Author: Rakesh Lawaju
 * Author URI: https://racase.com.np
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: schema-engine-pro
 * Requires Plugins: schema-engine
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Check if Schema Engine is active
if ( ! class_exists( 'Schema_Engine' ) ) {
    add_action( 'admin_notices', 'schema_engine_pro_missing_notice' );
    return;
}

// Plugin constants
define( 'SCHEMA_ENGINE_PRO_VERSION', '1.0.0' );
define( 'SCHEMA_ENGINE_PRO_PLUGIN_FILE', __FILE__ );
define( 'SCHEMA_ENGINE_PRO_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'SCHEMA_ENGINE_PRO_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

// Initialize Freemius
require_once SCHEMA_ENGINE_PRO_PLUGIN_DIR . 'includes/class-schema-engine-pro-freemius.php';

// Main plugin class
class Schema_Engine_Pro {
    private static $instance = null;

    public static function get_instance() {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        $this->load_dependencies();
        $this->init_hooks();
    }

    private function load_dependencies() {
        require_once SCHEMA_ENGINE_PRO_PLUGIN_DIR . 'includes/class-schema-engine-pro-admin.php';
    }

    private function init_hooks() {
        if ( is_admin() ) {
            Schema_Engine_Pro_Admin::get_instance();
        }

        // Hook into Schema Engine output
        add_filter( 'schema_engine_output_location', array( $this, 'modify_output_location' ) );
        add_filter( 'schema_engine_default_image', array( $this, 'get_default_image' ) );
    }

    public function modify_output_location( $location ) {
        $options = get_option( 'schema_engine_pro_settings', array() );
        return isset( $options['code_placement'] ) ? $options['code_placement'] : $location;
    }

    public function get_default_image( $image ) {
        $options = get_option( 'schema_engine_pro_settings', array() );
        return ! empty( $options['default_image'] ) ? $options['default_image'] : $image;
    }
}

// Initialize
Schema_Engine_Pro::get_instance();
```

### Admin Class Structure

The admin class should follow the same pattern as Schema Engine's admin class:

**File:** `includes/class-schema-engine-pro-admin.php`

**Key Methods:**
- `add_admin_menu()` - Add submenu under Schema Engine
- `register_settings()` - Register settings and fields
- `render_settings_page()` - Render main settings page with tabs
- `render_general_tab()` - Render General settings tab
- `render_help_tab()` - Render Help tab
- `sanitize_settings()` - Sanitize and validate settings
- `enqueue_admin_assets()` - Enqueue CSS/JS for media library

### Freemius Integration

**File:** `includes/class-schema-engine-pro-freemius.php`

This file handles:
- Freemius SDK initialization
- License activation/deactivation
- Premium feature gates
- Update mechanism
- Trial management (if applicable)

### Settings Storage

Settings should be stored in a single option:
- **Option Name:** `schema_engine_pro_settings`
- **Format:** Serialized array
- **Fields:**
  ```php
  array(
      'code_placement' => 'head', // or 'footer'
      'default_image'  => 'https://example.com/image.jpg',
  )
  ```

### Hooks and Filters

**Filters to provide:**
```php
// Allow base plugin to check if Pro is active
apply_filters( 'schema_engine_pro_is_active', true );

// Allow modification of code placement
apply_filters( 'schema_engine_output_location', 'head' );

// Provide default image
apply_filters( 'schema_engine_default_image', '' );
```

## UI/UX Guidelines

### Design Consistency
- Match Schema Engine's existing admin UI style
- Use WordPress admin UI components and patterns
- Follow WordPress coding standards
- Use Dashicons for icons
- Maintain consistent spacing and typography

### Form Fields
- Use WordPress Settings API
- Implement proper nonce verification
- Add helpful descriptions under each field
- Include tooltip help text where needed
- Show validation errors clearly

### Image Upload Field Implementation
```php
// Use WordPress media library
wp_enqueue_media();

// Render upload button
<button type="button" class="button button-secondary schema-engine-pro-upload-image-btn">
    <span class="dashicons dashicons-format-image"></span>
    Upload Image
</button>

// JavaScript to handle media library
jQuery('.schema-engine-pro-upload-image-btn').on('click', function(e) {
    e.preventDefault();
    var mediaUploader = wp.media({
        title: 'Select Default Schema Image',
        button: { text: 'Use this image' },
        multiple: false
    });
    // Handle selection...
});
```

### Tab Navigation
- Use WordPress native tab styling (`nav-tab-wrapper`, `nav-tab`, `nav-tab-active`)
- Maintain tab state in URL with `?page=schema-engine-pro&tab=general`
- Load appropriate content based on active tab

## Freemius Integration Details

### SDK Setup
```php
// Initialize Freemius
function schema_engine_pro_fs() {
    global $schema_engine_pro_fs;

    if ( ! isset( $schema_engine_pro_fs ) ) {
        require_once SCHEMA_ENGINE_PRO_PLUGIN_DIR . 'freemius/start.php';

        $schema_engine_pro_fs = fs_dynamic_init( array(
            'id'             => 'YOUR_PRODUCT_ID',
            'slug'           => 'schema-engine-pro',
            'type'           => 'plugin',
            'public_key'     => 'YOUR_PUBLIC_KEY',
            'is_premium'     => true,
            'has_addons'     => false,
            'has_paid_plans' => true,
            'menu'           => array(
                'slug'    => 'schema-engine-pro',
                'parent'  => array(
                    'slug' => 'options-general.php',
                ),
            ),
        ) );
    }

    return $schema_engine_pro_fs;
}
```

### License Checks
```php
// Check if premium features are enabled
if ( schema_engine_pro_fs()->is_premium() ) {
    // Enable premium features
}
```

## Best Practices

### Code Quality
- Follow WordPress coding standards
- Use proper escaping and sanitization
- Implement proper capability checks
- Add inline documentation
- Use translatable strings with text domain `schema-engine-pro`

### Performance
- Only load admin assets on plugin pages
- Minimize database queries
- Cache settings when possible
- Use WordPress transients for temporary data

### Security
- Verify nonces for all form submissions
- Check user capabilities (`manage_options`)
- Sanitize all inputs
- Escape all outputs
- Validate file uploads (images)

### Compatibility
- Test with Schema Engine updates
- Maintain backward compatibility
- Handle missing base plugin gracefully
- Support multisite installations

## Testing Checklist

- [ ] Plugin activates successfully
- [ ] Base plugin dependency check works
- [ ] Settings page appears under correct menu
- [ ] Both tabs load correctly
- [ ] Code placement setting saves and applies
- [ ] Image upload works with media library
- [ ] Image preview displays correctly
- [ ] Default image is used in schema output
- [ ] Settings are properly sanitized
- [ ] Help content is clear and helpful
- [ ] Freemius integration works
- [ ] License activation/deactivation works
- [ ] Update mechanism functions properly

## Next Steps (Agent Plan)

Based on the analysis of the current codebase, the following steps should be taken to implement Schema Engine Pro:

1.  **Prepare Base Plugin (`schema-engine`)**:
    *   Add the `schema_engine_output_location` filter in `includes/class-schema-engine-output.php`.
    *   Add the `schema_engine_default_image` filter in `includes/class-schema-engine-output.php`.
    *   Bump version if necessary to indicate compatibility.

2.  **Scaffold Pro Plugin**:
    *   Populate `schema-engine-pro.php` with the code provided in "Main Plugin File".
    *   Create `includes/class-schema-engine-pro-admin.php` structure.
    *   Create `includes/class-schema-engine-pro-freemius.php` structure.

3.  **Implement Features**:
    *   Implement the Admin settings page (General and Help tabs).
    *   Implement the Image Upload logic.
    *   Implement the Freemius integration.

4.  **Verify**:
    *   Test the integration hooks.
    *   Verify the settings page UI/UX matches the base plugin.

## Future Enhancements

### Planned Features
- Additional premium schema types
- Advanced conditional logic
- Custom schema templates
- Schema import/export
- Multi-language support
- Schema validation tools
- Analytics and reporting

### Extensibility
- Provide hooks for further extensions
- Allow custom schema types registration
- Support third-party integrations
- Developer API documentation

## Support Resources

### Documentation
- Installation guide
- Configuration tutorials
- Video walkthroughs
- FAQ section
- Troubleshooting guide

### Support Channels
- Premium support tickets (via Freemius)
- Documentation site
- Email support
- Knowledge base

## Version Control

### Semantic Versioning
- **Major:** Breaking changes
- **Minor:** New features, backward compatible
- **Patch:** Bug fixes

### Changelog
Maintain detailed changelog in readme.txt following WordPress standards.

## Localization

### Text Domain
- Primary: `schema-engine-pro`
- Fallback: `schema-engine`

### Translation Ready
- Use `__()`, `_e()`, `_n()`, `_x()` functions
- Provide context where needed
- Include `.pot` file for translations
- Support RTL languages

---

**Last Updated:** 2025-11-29
**Version:** 1.0.0
**Author:** Rakesh Lawaju
