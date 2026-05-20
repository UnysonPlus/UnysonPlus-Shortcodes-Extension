<?php if ( ! defined( 'FW' ) ) { die( 'Forbidden' ); }

$cfg = array();

$cfg['page_builder'] = array(
        'title'       => __( 'Image Content', 'fw' ),
        'description' => __( 'Image alongside text content in a responsive two-column layout', 'fw' ),
        'tab'         => __( 'Content Elements', 'fw' ),
        'popup_size'  => 'large',
        'title_template' => '
                <div style="display:flex; align-items:center; gap:12px;">
                        {{ if (o.layout === "image-right") { }}
                                {{ if (o.content) { }}
                                        <div>{{= o.content }}</div>
                                {{ } }}
                                {{ if (o.image && o.image.url) { }}
                                        <img src="{{= o.image.url }}" style="max-width:120px; max-height:80px; object-fit:cover; border-radius:4px; flex-shrink:0;">
                                {{ } }}
                        {{ } else { }}
                                {{ if (o.image && o.image.url) { }}
                                        <img src="{{= o.image.url }}" style="max-width:120px; max-height:80px; object-fit:cover; border-radius:4px; flex-shrink:0;">
                                {{ } }}
                                {{ if (o.content) { }}
                                        <div>{{= o.content }}</div>
                                {{ } }}
                        {{ } }}
                </div>
        ',
);
