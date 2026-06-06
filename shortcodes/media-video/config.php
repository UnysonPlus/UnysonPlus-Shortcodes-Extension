<?php if (!defined('FW')) die('Forbidden');

$cfg = array();

$cfg['page_builder'] = array(
    'title'         => __('Video', 'fw'),
    'description'   => __('Add a Video', 'fw'),
    'tab'           => __('Media Elements', 'fw'),
    'title_template' => '
    {{ if (o.url) { }}

        {{
            var url = o.url;

            // Width is a unit-input { value, unit }. Accept a legacy bare number too.
            var w = o.width, width_val = 600, width_unit = "px";
            if (w && typeof w === "object") {
                width_val = (w.value !== "" && w.value != null) ? w.value : 600;
                width_unit = w.unit || "px";
            } else if (w) {
                width_val = w;
            }
            var max_width = "" + width_val + width_unit;

            // Aspect ratio as a unit-independent CSS "W / H" string.
            var ratio_css_map = {
                "16x9": "16 / 9",
                "4x3":  "4 / 3",
                "1x1":  "1 / 1",
                "21x9": "21 / 9",
                "9x16": "9 / 16",
                "3x4":  "3 / 4"
            };
            var ratio_css = ratio_css_map[o.ratio || "16x9"] || "16 / 9";

            var embed_url = "";
            
            // YouTube (watch?v=, youtu.be/, /shorts/, /live/) — strip any query string.
            if (url.indexOf("youtube.com") !== -1 || url.indexOf("youtu.be") !== -1) {
                var video_id = url.indexOf("v=") !== -1
                    ? url.split("v=")[1].split("&")[0]
                    : url.split("/").pop().split("?")[0];
                embed_url = video_id ? "https://www.youtube.com/embed/" + video_id : "";
            }

            // Vimeo
            else if (url.indexOf("vimeo.com") !== -1) {
                var vimeo_id = url.split("/").pop().split("?")[0];
                embed_url = vimeo_id ? "https://player.vimeo.com/video/" + vimeo_id : "";
            }

            // Dailymotion
            else if (url.indexOf("dailymotion.com") !== -1) {
                var dm_id = url.split("/video/")[1] ? url.split("/video/")[1].split("?")[0] : "";
                embed_url = dm_id ? "https://www.dailymotion.com/embed/video/" + dm_id : "";
            }
        }}

        {{ if (embed_url) { }}
            <iframe 
                src="{{- embed_url }}" 
                frameborder="0" 
                allowfullscreen 
                style="
                    display:block;
                    width:100%;
                    max-width:{{- max_width }};
                    height:auto;
                    aspect-ratio: {{- ratio_css }};
                "
            ></iframe>
        {{ } else { }}
            URL: {{- url }}
        {{ } }}

    {{ } else { }}
        <em>No video URL defined</em>
    {{ } }}
    ',
);
