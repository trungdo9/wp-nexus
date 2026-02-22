# Phase 1: Admin Menu Setup

**Date:** 2026-02-21
**Description:** Create admin page class and add menu item
**Status:** Pending

## Context
- Parent Plan: plan.md

## Requirements
1. Create class-admin-page.php
2. Add menu page using add_menu_page
3. Register assets (CSS/JS)

## Implementation

### Step 1: Create Admin Page Class
```php
<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class WP_Nexus_Admin_Page {
    private static $instance = null;

    public static function get_instance() {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        add_action( 'admin_menu', array( $this, 'add_menu_pages' ) );
        add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_assets' ) );
    }

    public function add_menu_pages() {
        add_menu_page(
            __( 'WP Nexus', 'wp-nexus' ),
            __( 'WP Nexus', 'wp-nexus' ),
            'manage_options',
            'wp-nexus',
            array( $this, 'render_admin_page' ),
            'dashicons-networking',
            30
        );
    }

    public function enqueue_assets( $hook ) {
        if ( 'toplevel_page_wp-nexus' !== $hook ) {
            return;
        }
        // Enqueue CSS/JS
    }

    public function render_admin_page() {
        // Render page
    }
}
```

## Todo
- [ ] Create class-admin-page.php
- [ ] Add menu page
- [ ] Add assets enqueue
