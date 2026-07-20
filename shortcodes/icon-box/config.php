<?php if ( ! defined( 'FW' ) ) {
    die( 'Forbidden' );
}

$cfg = array();

$cfg['page_builder'] = array(
    'title'       => __( 'Icon Box', 'fw' ),
    'description' => __( 'Add an Icon Box', 'fw' ),
    'tab'         => __( 'Components', 'fw' ),
    'popup_size'    => 'large', // can be large, medium or small
    'title_template' => '
        {{ if ( o ) {

            var ic = o["icon"] || {};
            var hasCustomIcon = o["custom_icon"] && (\'\' + o["custom_icon"]).trim().length > 0;
            var hasIconSvg    = ic["type"] === "svg" && ic["markup"];
            var hasIconEmoji  = ic["type"] === "emoji" && ic["char"];
            var hasIconUpload = ic["type"] === "custom-upload" && ic["url"];
            var hasIconFont   = ic["type"] === "icon-font" && ic["icon-class"];
            var hasIcon       = hasCustomIcon || hasIconSvg || hasIconEmoji || hasIconUpload || hasIconFont;

            // svg (Lucide library / pasted inline): render the markup, resized small.
            var svgMarkup = "";
            if ( hasIconSvg ) {
                svgMarkup = (\'\' + ic["markup"]).replace(/width="[^"]*"/i, \'width="22"\').replace(/height="[^"]*"/i, \'height="22"\');
            }

            // custom_icon (legacy inline SVG): a viewBox-only <svg> has no intrinsic size and
            // collapses to 0x0 inside the inline-flex preview span (the frontend CSS that sizes
            // it is not loaded in wp-admin). Inject a width/height when the <svg> has no explicit
            // width so these legacy icons still preview. A space before "width" avoids matching
            // stroke-width; SVGs that already carry a width pass through unchanged.
            var customIconMarkup = "";
            if ( hasCustomIcon ) {
                var rawCI = (\'\' + o["custom_icon"]);
                customIconMarkup = /<svg[^>]* width=/i.test( rawCI ) ? rawCI : rawCI.replace(/<svg\\b/i, \'<svg width="20" height="20"\');
            }

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

            <div style="margin-top:.5rem; display:flex; flex-direction:column; align-items:flex-start; gap:6px;">

                {{ if ( hasCustomIcon ) { }}
                    <span style="display:inline-flex; align-items:center; font-size:20px; line-height:1; max-width:32px; max-height:32px; overflow:hidden;">{{= customIconMarkup }}</span>
                {{ } else if ( hasIconSvg ) { }}
                    <span style="display:inline-flex; align-items:center; line-height:1;">{{= svgMarkup }}</span>
                {{ } else if ( hasIconEmoji ) { }}
                    <span style="font-size:20px; line-height:1;">{{= ic["char"] }}</span>
                {{ } else if ( hasIconUpload ) { }}
                    <img src="{{= ic["url"] }}" style="max-width:24px; max-height:24px; vertical-align:middle;">
                {{ } else if ( hasIconFont ) { }}
                    <i class="{{= ic["icon-class"] }}" style="font-size:20px;"></i>
                {{ } }}

                {{ if ( hasTitle ) { }}
                    <strong>{{- o["title"] }}</strong>
                {{ } }}

                {{ if ( hasContent ) { }}
                    <div style="opacity:.7; font-size:12px;">
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
