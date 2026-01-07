<?php if (!defined('FW')) die('Forbidden');

$manifest = array();

$manifest['name']        = __( 'Shortcodes', 'fw' );
$manifest['slug']        = 'unysonplus-shortcodes';
$manifest['description'] = __( 
	'This extension adds a powerful drag & drop shortcode system. Use it to insert styled content elements anywhere on your site.', 
	'fw' 
);

$manifest['version']     = '1.3.34';
$manifest['display']     = false;
$manifest['standalone']  = true;

// Requirements
$manifest['requirements'] = array(
	'extensions' => array(
		'builder' => array(),
	),
);

// Repository Info
$manifest['github_update'] = 'UnysonPlus/UnysonPlus-Shortcodes-Extension';
$manifest['github_repo']   = 'https://github.com/UnysonPlus/UnysonPlus-Shortcodes-Extension';
$manifest['github_branch'] = 'master';

// Author Info
$manifest['author']     = 'UnysonPlus';
$manifest['author_uri'] = 'https://www.lastimosa.com.ph/unysonplus';

// Meta
$manifest['license']      = 'GPL-2.0-or-later';
$manifest['text_domain']  = 'fw';
$manifest['requires_php'] = '7.4';
$manifest['requires_wp']  = '5.8';
