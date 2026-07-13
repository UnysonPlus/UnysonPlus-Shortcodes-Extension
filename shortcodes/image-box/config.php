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
            var d = ( o["design_settings"] && o["design_settings"].family ) || o["design"] || "stacked";
            var txt = ( o["text"] || "" ).replace( /<[^>]*>/g, " " ).replace( /&nbsp;/g, " " ).replace( /\s+/g, " " ).trim();
            if ( txt.length > 90 ) { txt = txt.slice( 0, 90 ) + "…"; }
            var btn = ( o["button_label"] || "" ).toString().trim();
        }}
            {{ if ( o.image && o.image.url ) { }}
                <div style="margin-top:.5rem;">
                    <img src="{{- o.image.url }}" style="max-width:100%; display:block; border-radius:3px;" />
                </div>
            {{ } }}
            <div style="margin-top:.4rem; display:flex; align-items:center; gap:6px; flex-wrap:wrap;">
                {{ if ( t ) { }}<strong>{{- t }}</strong><span style="opacity:.4;">|</span>{{ } }}
                <em style="opacity:.7;">{{- d }}</em>
            </div>
            {{ if ( txt ) { }}<div style="margin-top:.25rem; opacity:.65; font-size:.9em; line-height:1.35;">{{- txt }}</div>{{ } }}
            {{ if ( btn ) { }}<div style="margin-top:.35rem;"><span style="display:inline-block; padding:1px 9px; border:1px solid rgba(0,0,0,.18); border-radius:999px; font-size:.85em; opacity:.85;">{{- btn }}</span></div>{{ } }}
        {{ } }}
    ',
);
