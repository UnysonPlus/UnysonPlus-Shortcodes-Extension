<?php if ( ! defined( 'FW' ) ) {
    die( 'Forbidden' );
}

$cfg = array();

$cfg['page_builder'] = array(
    'title'       => __( 'Icon Box', 'fw' ),
    'description' => __( 'Add an Icon Box', 'fw' ),
    'tab'         => __( 'Content Elements', 'fw' ),

    'title_template' => '
        {{ if ( o ) {

            var hasCustomIcon = o["custom_icon"] && (\'\' + o["custom_icon"]).trim().length > 0;
            var hasIconUpload = o["icon"] && o["icon"]["type"] === "custom-upload" && o["icon"]["url"];
            var hasIconFont   = o["icon"] && o["icon"]["type"] === "icon-font" && o["icon"]["icon-class"];
            var hasIcon       = hasCustomIcon || hasIconUpload || hasIconFont;

            var hasTitle      = o["title"] && (\'\' + o["title"]).trim().length > 0;

            var contentText   = "";
            if ( o["content"] ) {
                contentText = (\'\' + o["content"])
                    .replace(/<[^>]+>/g, " ")
                    .replace(/&nbsp;/g, " ")
                    .replace(/\\s+/g, " ")
                    .trim();
            }
            var hasContent = contentText.length > 0;
            if ( contentText.length > 100 ) {
                contentText = contentText.slice(0, 100) + "…";
            }
        }}

            <div style="margin-top:.5rem; display:flex; align-items:center; flex-wrap:wrap; gap:6px;">

                {{ if ( hasCustomIcon ) { }}
                    <span style="display:inline-flex; align-items:center; font-size:18px; line-height:1; max-width:32px; max-height:32px; overflow:hidden;">{{= o["custom_icon"] }}</span>
                {{ } else if ( hasIconUpload ) { }}
                    <img src="{{= o["icon"]["url"] }}" style="max-width:24px; max-height:24px; vertical-align:middle;">
                {{ } else if ( hasIconFont ) { }}
                    <i class="{{= o["icon"]["icon-class"] }}" style="font-size:18px;"></i>
                {{ } }}

                {{ if ( hasTitle ) { }}
                    <strong>{{- o["title"] }}</strong>
                {{ } }}

                {{ if ( hasContent ) { }}
                    <div style="opacity:.7; font-size:12px; margin-top:4px; width:100%;">
                        {{- contentText }}
                    </div>
                {{ } }}

                {{ if ( !hasIcon && !hasTitle && !hasContent ) { }}
                    <em>No content set</em>
                {{ } }}
            </div>
        {{ } }}
    ',
);
