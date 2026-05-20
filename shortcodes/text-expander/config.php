<?php if ( ! defined( 'FW' ) ) {
    die( 'Forbidden' );
}

$cfg = array();

$cfg['page_builder'] = array(
    'title'          => __( 'Text Expander', 'fw' ),
    'description'    => __( 'Inline read-more / reveal toggle. Hide part of a sentence or paragraph behind a Show More button.', 'fw' ),
    'tab'            => __( 'Content Elements', 'fw' ),
    'popup_size'    => 'large', // can be large, medium or small
    'title_template' => '
        {{ if ( o["visible_content"] ) { }}
            <div>{{= o["visible_content"] }}</div>
            <em style="color:#999">[+ hidden content]</em>
            <div>{{= o["hidden_content"] }}</div>
            <em style="color:#999">[- hidden content]</em>
        {{ } else { }}
            <em>Empty Text Expander</em>
        {{ } }}
    ',
);
