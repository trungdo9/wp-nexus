# Phase 2: Admin Meta Box UI

**Date:** 2026-02-20
**Description:** Create admin meta box for editing SEO nexus type and keyword
**Priority:** High
**Status:** Completed
**Review Status:** Not Reviewed

## Context Links
- Parent Plan: [plan.md](./plan.md)
- Dependency: Phase 1 - Plugin Structure

## Key Insights
- Use WordPress meta box API for post editing screen
- Add nonce field for security
- Save meta values on post save hook

## Requirements
1. Create meta box on post editing screen (post, page, custom post types)
2. Dropdown for seo-nexus-type: pillar, sub-pillar, cluster
3. Autocomplete input for seo-nexus-keyword
4. Save meta values to post meta table
5. Nonce verification for security

## Architecture

### Database Schema
- `_seo_nexus_type` (string): pillar, sub-pillar, or cluster
- `_seo_nexus_keyword` (string): keyword associated with post

### Meta Box Class
```php
class WP_Nexus_Meta_Box {
    public function add_meta_boxes() {
        add_meta_box(
            'wp-nexus-meta-box',
            __( 'WP Nexus SEO', 'wp-nexus' ),
            array( $this, 'render_meta_box' ),
            $this->get_supported_post_types(),
            'side',
            'default'
        );
    }

    private function get_supported_post_types() {
        return get_post_types( array( 'public' => true ), 'names' );
    }
}
```

## Related Code Files
- `includes/class-meta-box.php`

## Implementation Steps

### Step 1: Create meta box class
```php
<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class WP_Nexus_Meta_Box {
    private static $instance = null;

    public static function get_instance() {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        add_action( 'add_meta_boxes', array( $this, 'add_meta_boxes' ) );
        add_action( 'save_post', array( $this, 'save_meta' ) );
    }

    public function add_meta_boxes() {
        $post_types = $this->get_supported_post_types();

        foreach ( $post_types as $post_type ) {
            add_meta_box(
                'wp-nexus-meta-box',
                __( 'WP Nexus SEO', 'wp-nexus' ),
                array( $this, 'render_meta_box' ),
                $post_type,
                'side',
                'default'
            );
        }
    }

    private function get_supported_post_types() {
        return get_post_types( array( 'public' => true ), 'names' );
    }

    public function render_meta_box( $post ) {
        wp_nonce_field( 'wp_nexus_meta_box', 'wp_nexus_meta_box_nonce' );

        $seo_nexus_type = get_post_meta( $post->ID, '_seo_nexus_type', true );
        $seo_nexus_keyword = get_post_meta( $post->ID, '_seo_nexus_keyword', true );

        ?>
        <p>
            <label for="seo-nexus-type"><strong><?php _e( 'Nexus Type', 'wp-nexus' ); ?></strong></label>
            <select id="seo-nexus-type" name="seo_nexus_type" style="width: 100%; margin-top: 5px;">
                <option value=""><?php _e( '-- Select Type --', 'wp-nexus' ); ?></option>
                <option value="pillar" <?php selected( $seo_nexus_type, 'pillar' ); ?>><?php _e( 'Pillar', 'wp-nexus' ); ?></option>
                <option value="sub-pillar" <?php selected( $seo_nexus_type, 'sub-pillar' ); ?>><?php _e( 'Sub-Pillar', 'wp-nexus' ); ?></option>
                <option value="cluster" <?php selected( $seo_nexus_type, 'cluster' ); ?>><?php _e( 'Cluster', 'wp-nexus' ); ?></option>
            </select>
        </p>
        <p>
            <label for="seo-nexus-keyword"><strong><?php _e( 'Keyword', 'wp-nexus' ); ?></strong></label>
            <input type="text" id="seo-nexus-keyword" name="seo_nexus_keyword" value="<?php echo esc_attr( $seo_nexus_keyword ); ?>" style="width: 100%; margin-top: 5px;" autocomplete="off" />
            <span class="description"><?php _e( 'Start typing to see existing keywords or add a new one.', 'wp-nexus' ); ?></span>
        </p>
        <?php
    }

    public function save_meta( $post_id ) {
        if ( ! isset( $_POST['wp_nexus_meta_box_nonce'] ) ) {
            return $post_id;
        }

        if ( ! wp_verify_nonce( $_POST['wp_nexus_meta_box_nonce'], 'wp_nexus_meta_box' ) ) {
            return $post_id;
        }

        if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
            return $post_id;
        }

        if ( ! current_user_can( 'edit_post', $post_id ) ) {
            return $post_id;
        }

        if ( isset( $_POST['seo_nexus_type'] ) ) {
            update_post_meta( $post_id, '_seo_nexus_type', sanitize_text_field( $_POST['seo_nexus_type'] ) );
        }

        if ( isset( $_POST['seo_nexus_keyword'] ) ) {
            update_post_meta( $post_id, '_seo_nexus_keyword', sanitize_text_field( $_POST['seo_nexus_keyword'] ) );
        }
    }
}
```

## Todo List
- [ ] Create class-meta-box.php
- [ ] Implement add_meta_boxes() with all public post types
- [ ] Implement render_meta_box() with dropdown and input
- [ ] Implement save_meta() with nonce verification
- [ ] Add text domain support

## Success Criteria
- Meta box appears on all public post type edit screens
- Dropdown shows pillar, sub-pillar, cluster options
- Keyword input works correctly
- Values are saved and retrieved properly

## Risk Assessment
- Low: Standard WordPress meta box implementation

## Security Considerations
- Use nonce verification
- Sanitize all input with sanitize_text_field()
- Check user capabilities before saving
