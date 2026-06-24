<?php if (!defined('FW')) die('Forbidden');

// The container relies on the page-builder grid (.fw-container / .fw-row) the same
// way the section and row shortcodes do.
wp_enqueue_style('fw-ext-builder-frontend-grid');
