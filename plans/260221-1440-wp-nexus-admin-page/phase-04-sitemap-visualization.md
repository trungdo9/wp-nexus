# Phase 4: Sitemap Visualization (Updated)

**Date:** 2026-02-21
**Description:** Render collapsible sitemap/mindmap with expand/collapse
**Status:** Pending

## Context
- Parent Plan: plan.md
- Dependency: Phase 3
- Updated: Add collapsible functionality for large datasets

## Requirements
1. Display posts in collapsible tree format
2. Cluster nodes collapsed by default
3. Click Pillar/Sub-Pillar to expand children
4. Visual distinction between Pillar/Sub-Pillar/Cluster
5. Simple implementation (no external libraries)

## Implementation

### HTML Structure (Collapsible)
```php
private function render_sitemap( $hierarchy ) {
    ?>
    <div class="wp-nexus-sitemap">
        <h2><?php _e( 'Content Sitemap', 'wp-nexus' ); ?></h2>
        <?php if ( empty( $hierarchy['pillars'] ) ) : ?>
            <p><?php _e( 'No posts with nexus type found.', 'wp-nexus' ); ?></p>
        <?php else : ?>
            <div class="sitemap-tree">
                <?php foreach ( $hierarchy['pillars'] as $pillar ) : ?>
                    <?php $sp_count = count( $pillar['sub_pillars'] ?? array() ); ?>
                    <div class="sitemap-node pillar" data-type="pillar" data-id="<?php echo esc_attr( $pillar['id'] ); ?>">
                        <div class="node-header">
                            <span class="toggle-icon <?php echo $sp_count > 0 ? 'expandable' : ''; ?>"><?php echo $sp_count > 0 ? '&#9658;' : ''; ?></span>
                            <a href="<?php echo esc_url( $pillar['edit_url'] ); ?>" class="node-link" target="_blank">
                                <span class="node-title"><?php echo esc_html( $pillar['title'] ); ?></span>
                                <span class="node-keyword"><?php echo esc_html( $pillar['keyword'] ); ?></span>
                            </a>
                            <span class="node-count"><?php echo $sp_count; ?> sub-pillars</span>
                        </div>
                        <?php if ( $sp_count > 0 ) : ?>
                            <div class="sitemap-children" style="display: none;">
                                <?php foreach ( $pillar['sub_pillars'] as $sp ) : ?>
                                    <?php
                                    $cluster_key = $sp['keyword'];
                                    $cluster_count = count( $hierarchy['clusters_by_sp'][ $cluster_key ] ?? array() );
                                    ?>
                                    <div class="sitemap-node sub-pillar" data-type="sub-pillar" data-id="<?php echo esc_attr( $sp['id'] ); ?>">
                                        <div class="node-header">
                                            <span class="toggle-icon <?php echo $cluster_count > 0 ? 'expandable' : ''; ?>"><?php echo $cluster_count > 0 ? '&#9658;' : ''; ?></span>
                                            <a href="<?php echo esc_url( $sp['edit_url'] ); ?>" class="node-link" target="_blank">
                                                <span class="node-title"><?php echo esc_html( $sp['title'] ); ?></span>
                                                <span class="node-keyword"><?php echo esc_html( $sp['keyword'] ); ?></span>
                                            </a>
                                            <span class="node-count"><?php echo $cluster_count; ?> clusters</span>
                                        </div>
                                        <?php if ( $cluster_count > 0 ) : ?>
                                            <div class="sitemap-children" style="display: none;">
                                                <?php foreach ( $hierarchy['clusters_by_sp'][ $cluster_key ] as $cluster ) : ?>
                                                    <div class="sitemap-node cluster" data-type="cluster" data-id="<?php echo esc_attr( $cluster['id'] ); ?>">
                                                        <div class="node-header">
                                                            <span class="toggle-icon"></span>
                                                            <a href="<?php echo esc_url( $cluster['edit_url'] ); ?>" class="node-link" target="_blank">
                                                                <span class="node-title"><?php echo esc_html( $cluster['title'] ); ?></span>
                                                                <span class="node-keyword"><?php echo esc_html( $cluster['keyword'] ); ?></span>
                                                            </a>
                                                        </div>
                                                    </div>
                                                <?php endforeach; ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
    <?php
}
```

### JavaScript (Expand/Collapse)
```javascript
jQuery(document).ready(function($) {
    // Toggle expand/collapse
    $('.sitemap-node').on('click', '.toggle-icon.expandable', function(e) {
        e.preventDefault();
        e.stopPropagation();

        var $node = $(this).closest('.sitemap-node');
        var $children = $node.find('> .sitemap-children').first();
        var $icon = $(this);

        if ($children.is(':hidden')) {
            $children.slideDown(200);
            $icon.html('&#9660;'); // Down arrow
        } else {
            $children.slideUp(200);
            $icon.html('&#9658;'); // Right arrow
        }
    });

    // Also toggle on node header click (except link)
    $('.sitemap-node .node-header').on('click', function(e) {
        if ($(this).find('a').is(e.target)) return;

        var $icon = $(this).find('.toggle-icon.expandable');
        if ($icon.length) {
            $icon.trigger('click');
        }
    });
});
```

### CSS Updates
```css
.sitemap-node .node-header {
    display: flex;
    align-items: center;
    padding: 8px 12px;
    cursor: pointer;
    border-radius: 4px;
}

.sitemap-node .node-header:hover {
    background-color: rgba(0, 0, 0, 0.05);
}

.toggle-icon {
    width: 20px;
    text-align: center;
    font-size: 10px;
    color: #666;
}

.toggle-icon.expandable {
    cursor: pointer;
    font-size: 14px;
}

.node-count {
    margin-left: auto;
    font-size: 11px;
    color: #999;
    background: rgba(0, 0, 0, 0.05);
    padding: 2px 8px;
    border-radius: 10px;
}

.sitemap-children {
    margin-left: 24px;
    border-left: 1px dashed #ddd;
    padding-left: 10px;
}

/* Collapsed state indicator */
.sitemap-node[data-type="pillar"] > .node-header::before,
.sitemap-node[data-type="sub-pillar"] > .node-header::before {
    content: '';
    display: inline-block;
    width: 8px;
    height: 8px;
    border-radius: 50%;
    margin-right: 8px;
}

.sitemap-node.pillar > .node-header::before {
    background: #1976d2;
}

.sitemap-node.sub-pillar > .node-header::before {
    background: #7b1fa2;
}

.sitemap-node.cluster > .node-header::before {
    background: #388e3c;
}
```

## Todo
- [ ] Update render_sitemap to include toggle icons
- [ ] Add expand/collapse JavaScript
- [ ] Add CSS for collapsible tree
- [ ] Add node count badges
