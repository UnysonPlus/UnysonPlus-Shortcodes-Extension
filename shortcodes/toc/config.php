<?php if ( ! defined( 'FW' ) ) {
    die( 'Forbidden' );
}

$cfg = array();

$cfg['page_builder'] = array(
    'title'          => __( 'Table of Contents', 'fw' ),
    'description'    => __( 'Auto-generated, clickable list of the page\'s headings (H2/H3…) that anchor-jumps to each section.', 'fw' ),
    'tab'            => __( 'Content Elements', 'fw' ),
    'popup_size'     => 'medium', // can be large, medium or small
    'title_template' => '
        <div><strong>{{= o.title ? o.title : "Table of Contents" }}</strong></div>
        <em style="color:#999">[auto-built from page headings]</em>
    ',
);
