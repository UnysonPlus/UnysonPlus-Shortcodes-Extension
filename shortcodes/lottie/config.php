<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

$cfg = array();

$cfg['page_builder'] = array(
	'title'       => __( 'Lottie Animation', 'fw' ),
	'description' => __( 'Play an animated Lottie vector (.json from After Effects / LottieFiles) with autoplay, hover, on-scroll or click triggers, loop, speed and direction.', 'fw' ),
	'tab'         => __( 'Media Elements', 'fw' ),
	'popup_size'  => 'medium',

	'title_template' => '
		{{ var src = ( o && o["lottie_file"] && o["lottie_file"].url ) ? o["lottie_file"].url : ( o ? o["lottie_url"] : "" ); }}
		<div style="margin-top:.4rem;color:#555;">
			{{ if ( src ) { }}<span>&#9654; Lottie</span> <em style="opacity:.65;">{{- ( o["trigger"] || "autoplay" ) }}</em>{{ } else { }}<em>Lottie — add a .json animation</em>{{ } }}
		</div>
	',
);
