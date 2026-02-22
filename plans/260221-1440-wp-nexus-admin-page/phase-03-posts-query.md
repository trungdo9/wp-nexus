# Phase 3: Posts Query & Hierarchy

**Date:** 2026-02-21
**Description:** Query posts and build hierarchy tree
**Status:** Pending

## Context
- Parent Plan: plan.md
- Dependency: Phase 1

## Requirements
1. Query all public posts with nexus meta
2. Build hierarchical structure: Pillar > Sub-Pillar > Cluster
3. Pass data to frontend for visualization

## Implementation

### Query Posts
```php
public function get_posts_hierarchy() {
    $post_types = get_post_types( array( 'public' => true ), 'names' );

    $args = array(
        'post_type'      => array_values( $post_types ),
        'post_status'    => 'publish',
        'posts_per_page' => -1,
        'meta_query'     => array(
            array(
                'key'     => '_seo_nexus_type',
                'value'   => array( 'pillar', 'sub-pillar', 'cluster' ),
                'compare' => 'IN',
            ),
        ),
    );

    $query = new WP_Query( $args );
    $posts = array();

    foreach ( $query->posts as $post ) {
        $type          = get_post_meta( $post->ID, '_seo_nexus_type', true );
        $keyword       = get_post_meta( $post->ID, '_seo_nexus_keyword', true );
        $parent_keyword = get_post_meta( $post->ID, '_seo_nexus_parent_keyword', true );

        $posts[] = array(
            'id'            => $post->ID,
            'title'         => $post->post_title,
            'type'          => $type,
            'keyword'       => $keyword,
            'parent_keyword'=> $parent_keyword,
            'edit_url'      => get_edit_post_link( $post->ID ),
        );
    }

    return $this->build_hierarchy( $posts );
}
```

### Build Hierarchy
```php
private function build_hierarchy( $posts ) {
    $pillars      = array();
    $sub_pillars  = array();
    $clusters     = array();

    // Separate by type
    foreach ( $posts as $post ) {
        if ( 'pillar' === $post['type'] ) {
            $pillars[] = $post;
        } elseif ( 'sub-pillar' === $post['type'] ) {
            $sub_pillars[] = $post;
        } else {
            $clusters[] = $post;
        }
    }

    // Build tree
    $hierarchy = array(
        'pillars' => array(),
    );

    // Assign sub-pillars to pillars
    foreach ( $sub_pillars as $sp ) {
        $found = false;
        foreach ( $pillars as $p ) {
            if ( $sp['parent_keyword'] === $p['keyword'] ) {
                $p['sub_pillars'][] = $sp;
                $found = true;
                break;
            }
        }
        if ( ! $found ) {
            // Orphan sub-pillar
            $hierarchy['orphans']['sub_pillars'][] = $sp;
        }
    }

    // Assign clusters to sub-pillars
    foreach ( $clusters as $c ) {
        $found = false;
        foreach ( $sub_pillars as $sp ) {
            if ( $c['parent_keyword'] === $sp['keyword'] ) {
                $hierarchy['clusters_by_sp'][$sp['keyword']][] = $c;
                $found = true;
                break;
            }
        }
    }

    return $hierarchy;
}
```

## Output Format
```json
{
  "pillars": [
    {
      "id": 1,
      "title": "Pillar Post",
      "keyword": "seo",
      "edit_url": "...",
      "sub_pillars": [...]
    }
  ]
}
```

## Todo
- [ ] Query posts with meta
- [ ] Build hierarchy tree
- [ ] Pass to frontend
