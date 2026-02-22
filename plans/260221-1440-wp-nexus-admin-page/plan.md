# Plan: WP Nexus Admin Page with Sitemap

**Date:** 2026-02-21
**Description:** Create admin page to display endpoint info and interactive sitemap/mindmap
**Priority:** High

## Overview
Create a WordPress admin page under "WP Nexus" menu that displays:
1. API endpoint information (URL, API key)
2. Interactive sitemap visualization showing posts organized by Pillar > Sub-Pillar > Cluster

## Requirements
1. Add admin menu page using WordPress API
2. Display endpoint info with copy-to-clipboard
3. Fetch all posts with nexus meta data
4. Build hierarchical tree: Pillar → Sub-Pillar → Cluster
5. Render interactive mindmap/sitemap with clickable nodes

## Architecture

### New Class: WP_Nexus_Admin_Page
```php
class WP_Nexus_Admin_Page {
    public function add_menu_pages() {
        add_menu_page(
            'WP Nexus',
            'WP Nexus',
            'manage_options',
            'wp-nexus',
            array( $this, 'render_admin_page' ),
            'dashicons-networking',
            30
        );
    }

    public function render_admin_page() {
        // Render endpoint info + sitemap
    }

    public function get_posts_by_nexus_type() {
        // Query posts grouped by nexus type
    }
}
```

### Hierarchy Logic
- **Pillar**: Top level, no parent
- **Sub-Pillar**: Has pillar as parent (via parent_keyword matching pillar keyword)
- **Cluster**: Has sub-pillar as parent

### Frontend Visualization
- Use CSS-based tree or simple JavaScript
- No external libraries to keep it simple
- Click node → navigate to post edit URL

## Implementation Phases

| Phase | Name | Status |
|-------|------|--------|
| 1 | Admin Menu Setup | Pending |
| 2 | Endpoint Info Display | Pending |
| 3 | Posts Query & Hierarchy | Pending |
| 4 | Sitemap Visualization | Pending |

## Files to Create/Modify
- `includes/class-admin-page.php` (new)
- `wp-nexus.php` (add include)
- `assets/css/admin-page.css` (new)
- `assets/js/admin-page.js` (new)

## Risk Assessment
- Low: Standard WordPress admin page implementation
- Medium: JavaScript visualization - keep simple

## Timeline Estimate
- Phase 1-2: 30 min
- Phase 3-4: 1 hour
