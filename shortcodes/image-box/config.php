<?php if ( ! defined( 'FW' ) ) {
    die( 'Forbidden' );
}

$cfg = array();

$cfg['page_builder'] = array(
    'title'       => __( 'Image Box', 'fw' ),
    'description' => __( 'An image paired with a heading, text and link, in many hover-overlay, caption, card and frame designs.', 'fw' ),
    'tab'         => __( 'Media Elements', 'fw' ),
    'popup_size'  => 'large',

    'title_template' => '
        {{ if ( o ) {
            var t = o["title"] || "";
            var d = o["design"] || "stacked";
        }}
            {{ if ( o.image && o.image.url ) { }}
                <div style="margin-top:.5rem;">
                    <img src="{{- o.image.url }}" style="max-width:100%; max-height:90px; display:block; border-radius:3px;" />
                </div>
            {{ } }}
            <div style="margin-top:.4rem; display:flex; align-items:center; gap:6px; flex-wrap:wrap;">
                {{ if ( t ) { }}<strong>{{- t }}</strong><span style="opacity:.4;">|</span>{{ } }}
                <em style="opacity:.7;">{{- d }}</em>
            </div>
        {{ } }}
    ',
);
