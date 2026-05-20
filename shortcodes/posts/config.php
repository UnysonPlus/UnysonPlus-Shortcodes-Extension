<?php if ( ! defined( 'FW' ) ) {
    die( 'Forbidden' );
}

$cfg = array();

$cfg['page_builder'] = array(
    'title'       => __( 'Posts', 'fw' ),
    'description' => __( 'Display posts in a grid, list, masonry, slider, overlay, or hero layout with full element-positioning control.', 'fw' ),
    'tab'         => __( 'Content Elements', 'fw' ),
    'popup_size'  => 'large',

    'title_template' => '
        {{ if ( o ) {
            var styleLabel = o["card_style"] || "standard";
            var ptLabel    = o["post_type"]  || "post";
            var count      = o["posts_per_page"] || 6;
        }}
            <div style="margin-top:.5rem; display:flex; align-items:center; gap:6px; flex-wrap:wrap;">
                <strong>{{- ptLabel }}</strong>
                <span style="opacity:.6;">×</span>
                <span>{{- count }}</span>
                <span style="opacity:.4;">|</span>
                <em style="opacity:.7;">{{- styleLabel }}</em>
            </div>
        {{ } }}
    ',
);
