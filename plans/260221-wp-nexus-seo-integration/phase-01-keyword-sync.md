# Phase 1: Keyword Sync

**Date:** 2026-02-21
**Description:** Auto-sync focus keywords from Yoast/Rank Math
**Status:** Pending

## Context
- Parent Plan: plan.md

## Requirements
1. On post save, sync keyword from Yoast/Rank Math to WP Nexus
2. Priority: Rank Math → Yoast
3. Only sync if WP Nexus keyword is empty (don't overwrite)

## Implementation

### Option A: Hook into Meta Box Save
Add sync logic after WP Nexus keyword is saved

```php
// In class-meta-box.php, add after save_meta

private function sync_from_seo_plugins( $post_id ) {
    $existing_keyword = get_post_meta( $post_id, '_seo_nexus_keyword', true );

    // Only sync if empty
    if ( ! empty( $existing_keyword ) ) {
        return;
    }

    // Priority 1: Rank Math
    $keyword = get_post_meta( $post_id, 'rank_math_focus_keyword', true );

    // Priority 2: Yoast
    if ( empty( $keyword ) ) {
        $keyword = get_post_meta( $post_id, '_yoast_wpseo_focuskw', true );
    }

    if ( ! empty( $keyword ) ) {
        update_post_meta( $post_id, '_seo_nexus_keyword', sanitize_text_field( $keyword ) );
    }
}
```

### Option B: Standalone Function
Add to main plugin file or create new class

```php
// In wp-nexus.php or new class

add_action( 'save_post', 'wp_nexus_sync_seo_keywords', 20 );

function wp_nexus_sync_seo_keywords( $post_id ) {
    // Skip autosave/revisions
    if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
        return $post_id;
    }

    $existing = get_post_meta( $post_id, '_seo_nexus_keyword', true );
    if ( ! empty( $existing ) ) {
        return $post_id;
    }

    // Rank Math
    $keyword = get_post_meta( $post_id, 'rank_math_focus_keyword', true );

    // Yoast
    if ( empty( $keyword ) ) {
        $keyword = get_post_meta( $post_id, '_yoast_wpseo_focuskw', true );
    }

    if ( $keyword ) {
        update_post_meta( $post_id, '_seo_nexus_keyword', sanitize_text_field( $keyword ) );
    }
}
```

## Configuration Option
Add setting to enable/disable auto-sync:

```php
// Option A: Always sync
// Option B: Only sync if WP Nexus is empty (DEFAULT)
// Option C: Manual sync only (button in meta box)
```

## Recommendation
Use Option B - only sync when WP Nexus keyword is empty, don't overwrite user input

## Additional: Bulk Sync for Old Posts

Add admin button to bulk sync keywords from all existing posts:

```php
// In admin-page.php - add bulk sync button

public function handle_bulk_sync() {
    if (!current_user_can('manage_options')) return;

    if (isset($_POST['wp_nexus_bulk_sync'])) {
        check_admin_referer('wp_nexus_bulk_sync');

        $post_types = get_post_types(array('public' => true), 'names');
        $posts = get_posts(array(
            'post_type' => array_values($post_types),
            'post_status' => 'publish',
            'posts_per_page' => -1,
        ));

        $synced = 0;
        foreach ($posts as $post) {
            $existing = get_post_meta($post->ID, '_seo_nexus_keyword', true);
            if (!empty($existing)) continue;

            // Rank Math
            $keyword = get_post_meta($post->ID, 'rank_math_focus_keyword', true);
            // Yoast
            if (empty($keyword)) {
                $keyword = get_post_meta($post->ID, '_yoast_wpseo_focuskw', true);
            }

            if (!empty($keyword)) {
                update_post_meta($post->ID, '_seo_nexus_keyword', sanitize_text_field($keyword));
                $synced++;
            }
        }

        echo '<div class="notice notice-success"><p>Synced ' . $synced . ' posts.</p></div>';
    }
}
```

## Todo
- [ ] Add sync function
- [ ] Add priority logic (Rank Math → Yoast)
- [ ] Add bulk sync button in admin page
- [ ] Add setting option (optional)
