<?php if (!defined('FW')) die('Forbidden');

$cfg = array();

$cfg['page_builder'] = array(
    'title'         => __('Video', 'fw'),
    'description'   => __('Add a Video', 'fw'),
    'tab'           => __('Media Elements', 'fw'),
    'title_template' => '
    {{
        // Resolve source (Embed vs Self-hosted), tolerating the legacy flat `url`.
        var st     = (o.source_type && typeof o.source_type === "object") ? o.source_type : {};
        var source = st.source || (o.url ? "embed" : "embed");
        var emb    = (st.embed && typeof st.embed === "object") ? st.embed : {};
        var self   = (st.self_hosted && typeof st.self_hosted === "object") ? st.self_hosted : {};

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
            "16x9": "16 / 9", "4x3": "4 / 3", "1x1": "1 / 1",
            "21x9": "21 / 9", "9x16": "9 / 16", "3x4": "3 / 4"
        };
        var ratio_css = ratio_css_map[o.ratio || "16x9"] || "16 / 9";

        // Pull a URL out of an `upload` value ({ attachment_id, url }).
        function up(v){ return (v && typeof v === "object" && v.url) ? v.url : ""; }
    }}

    {{ if (source === "self_hosted") { }}

        {{
            var vfile   = up(self.video_file) || up(self.video_webm) || (self.video_url || "");
            var vposter = up(self.poster);
            var vfit    = (self.object_fit === "cover") ? "cover" : "contain";
        }}
        {{ if (vfile) { }}
            <video
                src="{{- vfile }}"
                {{ if (vposter) { }}poster="{{- vposter }}"{{ } }}
                muted
                playsinline
                preload="metadata"
                style="
                    display:block;
                    width:100%;
                    max-width:{{- max_width }};
                    aspect-ratio: {{- ratio_css }};
                    object-fit: {{- vfit }};
                    background:#000;
                "
            ></video>
        {{ } else if (vposter) { }}
            <img src="{{- vposter }}" alt="" style="display:block;width:100%;max-width:{{- max_width }};aspect-ratio:{{- ratio_css }};object-fit:{{- vfit }};background:#000;" />
        {{ } else { }}
            <em>No video file selected</em>
        {{ } }}

    {{ } else { }}

        {{
            var url = emb.url || o.url || "";
            var embed_url = "";

            // YouTube (watch?v=, youtu.be/, /shorts/, /live/) — strip any query string.
            if (url && (url.indexOf("youtube.com") !== -1 || url.indexOf("youtu.be") !== -1)) {
                var video_id = url.indexOf("v=") !== -1
                    ? url.split("v=")[1].split("&")[0]
                    : url.split("/").pop().split("?")[0];
                embed_url = video_id ? "https://www.youtube.com/embed/" + video_id : "";
            }
            // Vimeo
            else if (url && url.indexOf("vimeo.com") !== -1) {
                var vimeo_id = url.split("/").pop().split("?")[0];
                embed_url = vimeo_id ? "https://player.vimeo.com/video/" + vimeo_id : "";
            }
            // Dailymotion
            else if (url && url.indexOf("dailymotion.com") !== -1) {
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
        {{ } else if (url) { }}
            URL: {{- url }}
        {{ } else { }}
            <em>No video URL defined</em>
        {{ } }}

    {{ } }}
    ',
);
