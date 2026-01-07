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
            var width = o.width ? o.width : 600;
            
            // Map ratio to multiplier for height calculation
            var ratio_map = {
                "16x9": 9/16,
                "4x3": 3/4,
                "1x1": 1/1,
                "21x9": 9/21,
                "9x16": 16/9,
                "3x4": 4/3
            };
            var ratio = o.ratio || "16x9";
            var height = Math.round(width * (ratio_map[ratio] || 9/16));

            var embed_url = "";
            
            // YouTube
            if (url.indexOf("youtube.com") !== -1 || url.indexOf("youtu.be") !== -1) {
                var video_id = url.split("v=")[1] ? url.split("v=")[1].split("&")[0] : url.split("/").pop();
                embed_url = video_id ? "https://www.youtube.com/embed/" + video_id : "";
            }
            
            // Vimeo
            else if (url.indexOf("vimeo.com") !== -1) {
                var vimeo_id = url.split("/").pop();
                embed_url = "https://player.vimeo.com/video/" + vimeo_id;
            }
            
            // Dailymotion
            else if (url.indexOf("dailymotion.com") !== -1) {
                var dm_id = url.split("/video/")[1];
                embed_url = "https://www.dailymotion.com/embed/video/" + dm_id;
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
                    max-width:{{- width }}px;
                    height:auto;
                    aspect-ratio: {{- width }} / {{- height }};
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
