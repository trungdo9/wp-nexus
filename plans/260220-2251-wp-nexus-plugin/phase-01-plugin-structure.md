# Phase 1: Plugin Structure & Header

**Date:** 2026-02-20
**Description:** Create main plugin file with header and basic structure
**Priority:** High
**Status:** Completed
**Review Status:** Not Reviewed

## Context Links
- Parent Plan: [plan.md](./plan.md)

## Key Insights
- WordPress plugin requires standard header comment
- Plugin should follow WordPress coding standards
- Use WordPress hooks for initialization

## Requirements
1. Create `wp-nexus.php` main file
2. Add WordPress plugin header with Name, Description, Version, Author
3. Define constants for plugin path and URL
4. Initialize hooks (admin init, rest api init)
5. Create activation/deactivation hooks

## Architecture

```
wp-nexus/
├── wp-nexus.php          (main plugin file)
├── includes/
│   ├── class-meta-box.php
│   ├── class-rest-api.php
│   └── class-keyword-manager.php
├── assets/
│   ├── css/
│   │   └── admin.css
│   └── js/
│       └── admin.js
└── plans/
```

## Related Code Files
- `wp-nexus.php` - Main plugin file

## Implementation Steps

### Step 1: Create main plugin file
```php
<?php
/**
 * Plugin Name: WP Nexus
 * Description: SEO nexus type management plugin for pillar content strategy
 * Version: 1.0.0
 * Author: Developer
 * Text Domain: wp-nexus
 */

if ( ! defined( 'WP_NEXUS_VERSION' ) ) {
    define( 'WP_NEXUS_VERSION', '1.0.0' );
}

if ( ! defined( 'WP_NEXUS_PATH' ) ) {
    define( 'WP_NEXUS_PATH', plugin_dir_path( __FILE__ ) );
}

if ( ! defined( 'WP_NEXUS_URL' ) ) {
    define( 'WP_NEXUS_URL', plugin_dir_url( __FILE__ ) );
}

if ( ! defined( 'WP_NEXUS_API_KEY' ) ) {
    define( 'WP_NEXUS_API_KEY', '1234567890' );
}

class WP_Nexus {
    private static $instance = null;

    public static function get_instance() {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        add_action( 'init', array( $this, 'init' ) );
    }

    public function init() {
        // Load text domain
        load_plugin_textdomain( 'wp-nexus', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );

        // Initialize components
        require_once WP_NEXUS_PATH . 'includes/class-meta-box.php';
        require_once WP_NEXUS_PATH . 'includes/class-rest-api.php';
        require_once WP_NEXUS_PATH . 'includes/class-keyword-manager.php';

        WP_Nexus_Meta_Box::get_instance();
        WP_Nexus_REST_API::get_instance();
        WP_Nexus_Keyword_Manager::get_instance();
    }
}

function wp_nexus() {
    return WP_Nexus::get_instance();
}

wp_nexus();
```

### Step 2: Create includes directory structure
```bash
mkdir -p includes assets/{css,js} languages
```

## Todo List
- [ ] Create wp-nexus.php with plugin header
- [ ] Define constants for version, path, url, api key
- [ ] Create singleton pattern main class
- [ ] Initialize hooks for admin and REST API
- [ ] Create includes directory with class stubs

## Success Criteria
- Plugin appears in WordPress plugins list
- Plugin can be activated without errors
- Constants are properly defined

## Risk Assessment
- Low: Standard WordPress plugin structure

## Security Considerations
- Use `defined()` checks before defining constants
- Sanitize plugin basename
