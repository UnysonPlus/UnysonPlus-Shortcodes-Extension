<?php if (!defined('FW')) die('Forbidden');

$cfg = array();

$cfg['page_builder'] = array(
	'title'       => __('Image', 'fw'),
	'description' => __('Add an Image', 'fw'),
	'tab'         => __('Media Elements', 'fw'),
	'title_template' => '
		{{ if (o.image) { }}
			{{
				// Width/height are unit-inputs { value, unit }; build a CSS length
				// (accept a legacy bare number = px). Apply via inline style so any
				// unit previews correctly on the canvas.
				var dimCss = function (d) {
					if (d && typeof d === "object") {
						var v = (d.value !== "" && d.value != null) ? d.value : "";
						return v !== "" ? v + (d.unit || "px") : "";
					}
					return d ? d + "px" : "";
				};
				var w = dimCss(o.width), h = dimCss(o.height), s = "";
				if (w) { s += "width:" + w + ";"; }
				if (h) { s += "height:" + h + ";"; }
			}}
			<div>
				<img class="media-image" src="{{-o.image.url }}" style="{{- s }}" />
			</div>
		{{ } }}
	',
);
