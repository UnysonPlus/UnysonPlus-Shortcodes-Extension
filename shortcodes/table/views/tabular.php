<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

/**
 * Tabular (data table) renderer.
 *
 * Reads the canonical {header_options, cols, rows, content} value produced by
 * the table option type and outputs a semantic <table> with <thead>/<tbody>/
 * <tfoot>, per-column alignment + width, and colspan/rowspan merges. Display +
 * visitor-feature options come from the shortcode atts (options.php).
 *
 * @var array $atts
 */

$table   = isset( $atts['table'] ) ? $atts['table'] : array();
$cols    = ( isset( $table['cols'] ) && is_array( $table['cols'] ) ) ? array_values( $table['cols'] ) : array();
$content = ( isset( $table['content'] ) && is_array( $table['content'] ) ) ? array_values( $table['content'] ) : array();
$ho      = isset( $table['header_options'] ) ? $table['header_options'] : array();

$nrow = count( $content );
$ncol = count( $cols );

if ( ! $nrow || ! $ncol ) {
	return;
}

if ( isset( $ho['header_rows'] ) && '' !== $ho['header_rows'] ) {
	$header_rows = max( 0, (int) $ho['header_rows'] );
} else {
	// Back-compat: pre-rebuild tables marked header rows via the row name.
	$header_rows = 0;
	$legacy_rows = ( isset( $table['rows'] ) && is_array( $table['rows'] ) ) ? array_values( $table['rows'] ) : array();
	foreach ( $legacy_rows as $lr ) {
		if ( isset( $lr['name'] ) && 'heading-row' === $lr['name'] ) {
			$header_rows ++;
		} else {
			break;
		}
	}
}
$footer_rows = isset( $ho['footer_rows'] ) ? max( 0, (int) $ho['footer_rows'] ) : 0;
if ( $header_rows > $nrow ) {
	$header_rows = $nrow;
}
if ( $footer_rows > $nrow - $header_rows ) {
	$footer_rows = max( 0, $nrow - $header_rows );
}

$opt = function ( $key, $default ) use ( $atts ) {
	return isset( $atts[ $key ] ) && '' !== $atts[ $key ] ? $atts[ $key ] : $default;
};

// Display options
$striped   = 'yes' === $opt( 'style_striped', 'yes' );
$hover     = 'yes' === $opt( 'style_hover', 'yes' );
$bordered  = 'yes' === $opt( 'style_bordered', 'no' );
$condensed = 'yes' === $opt( 'style_condensed', 'no' );
$sticky    = 'yes' === $opt( 'sticky_header', 'no' );

// Visitor features (handled by the lightweight datatable.js enhancer)
$f_sort   = 'yes' === $opt( 'enable_sort', 'no' );
$f_search = 'yes' === $opt( 'enable_search', 'no' );
$f_page   = 'yes' === $opt( 'enable_pagination', 'no' );
$f_len    = 'yes' === $opt( 'enable_length_change', 'yes' );
$f_info   = 'yes' === $opt( 'enable_info', 'yes' );
$page_len = max( 1, (int) $opt( 'pagination_length', 10 ) );

$caption     = trim( (string) $opt( 'caption', '' ) );
$caption_top = 'top' === $opt( 'caption_position', 'bottom' );

// Presets (Theme Settings → Components). table_preset styles the table itself
// (.tbl-{slug} on the wrapper); frame_preset wraps the table in a .colb-{slug}
// box so the two stack on different elements without conflicting.
$table_preset = preg_replace( '/[^a-z0-9_-]/i', '', (string) $opt( 'table_preset', '' ) );
$frame_preset = preg_replace( '/[^a-z0-9_-]/i', '', (string) $opt( 'frame_preset', '' ) );

// Detect merges: sorting / pagination assume uniform rows, so disable the
// interactive enhancer when any cell spans.
$has_merge = false;
$has_width = false;
foreach ( $cols as $col ) {
	if ( ! empty( $col['width'] ) ) {
		$has_width = true;
		break;
	}
}
foreach ( $content as $row ) {
	foreach ( (array) $row as $cell ) {
		if ( ! empty( $cell['merged'] )
			|| ( isset( $cell['colspan'] ) && (int) $cell['colspan'] > 1 )
			|| ( isset( $cell['rowspan'] ) && (int) $cell['rowspan'] > 1 ) ) {
			$has_merge = true;
			break 2;
		}
	}
}

$is_datatable = ( $f_sort || $f_search || $f_page ) && ! $has_merge;

if ( $is_datatable ) {
	wp_enqueue_script(
		'fw-shortcode-table-datatable',
		fw_ext( 'shortcodes' )->get_declared_URI( '/shortcodes/table/static/js/datatable.js' ),
		array( 'jquery' ),
		fw()->theme->manifest->get_version(),
		true
	);
}

// Wrapper attributes
$atts['base_class']       = 'table';
$atts['unique_id_prefix'] = 'tb-';
$attr = sc_build_wrapper_attr( $atts );

$wrap_classes = array();
if ( $striped )   { $wrap_classes[] = 'table-striped'; }
if ( $hover )     { $wrap_classes[] = 'table-hover'; }
if ( $bordered )  { $wrap_classes[] = 'table-bordered'; }
if ( $condensed ) { $wrap_classes[] = 'table-condensed'; }
if ( $sticky )    { $wrap_classes[] = 'fw-table-sticky'; }
if ( $is_datatable ) { $wrap_classes[] = 'fw-table-interactive'; }
if ( $table_preset !== '' ) { $wrap_classes[] = $table_preset; }
if ( $wrap_classes ) {
	$attr['class'] = ( isset( $attr['class'] ) ? $attr['class'] . ' ' : '' ) . implode( ' ', $wrap_classes );
}

// Inner <table> attributes (datatable config rides on data-* here)
$table_class = array();
$table_data  = '';
if ( $is_datatable ) {
	$table_class[] = 'fw-datatable';
	$table_data .= ' data-sort="' . ( $f_sort ? 1 : 0 ) . '"';
	$table_data .= ' data-search="' . ( $f_search ? 1 : 0 ) . '"';
	$table_data .= ' data-paginate="' . ( $f_page ? 1 : 0 ) . '"';
	$table_data .= ' data-page-length="' . $page_len . '"';
	$table_data .= ' data-length-change="' . ( $f_len ? 1 : 0 ) . '"';
	$table_data .= ' data-info="' . ( $f_info ? 1 : 0 ) . '"';
}
$table_class_attr = $table_class ? ' class="' . esc_attr( implode( ' ', $table_class ) ) . '"' : '';

/**
 * Render a single cell. Skips cells covered by a merge.
 */
$render_cell = function ( $cell, $col, $tag ) {
	if ( ! empty( $cell['merged'] ) ) {
		return '';
	}
	$colspan = isset( $cell['colspan'] ) ? max( 1, (int) $cell['colspan'] ) : 1;
	$rowspan = isset( $cell['rowspan'] ) ? max( 1, (int) $cell['rowspan'] ) : 1;

	$a = '';
	if ( $colspan > 1 ) { $a .= ' colspan="' . $colspan . '"'; }
	if ( $rowspan > 1 ) { $a .= ' rowspan="' . $rowspan . '"'; }
	if ( ! empty( $col['name'] ) ) { $a .= ' class="' . esc_attr( $col['name'] ) . '"'; }
	if ( ! empty( $col['align'] ) ) { $a .= ' style="text-align:' . esc_attr( $col['align'] ) . '"'; }

	$val = isset( $cell['textarea'] ) ? $cell['textarea'] : '';

	return '<' . $tag . $a . '>' . wp_kses_post( $val ) . '</' . $tag . '>';
};

$body_end = $nrow - $footer_rows;
?>

<?php if ( $frame_preset !== '' ) : ?><div class="<?php echo esc_attr( $frame_preset ); ?> fw-table-frame"><?php endif; ?>
<div <?php echo fw_attr_to_html( $attr ); ?>>
	<table<?php echo $table_class_attr . $table_data; ?>>

		<?php if ( $caption && $caption_top ) : ?>
			<caption class="fw-table-caption fw-table-caption-top"><?php echo wp_kses_post( $caption ); ?></caption>
		<?php endif; ?>

		<?php if ( $has_width ) : ?>
			<colgroup>
				<?php foreach ( $cols as $col ) : ?>
					<col<?php echo ! empty( $col['width'] ) ? ' style="width:' . esc_attr( $col['width'] ) . '"' : ''; ?> />
				<?php endforeach; ?>
			</colgroup>
		<?php endif; ?>

		<?php if ( $header_rows > 0 ) : ?>
			<thead>
				<?php for ( $r = 0; $r < $header_rows; $r ++ ) : ?>
					<tr>
						<?php for ( $c = 0; $c < $ncol; $c ++ ) :
							echo $render_cell( isset( $content[ $r ][ $c ] ) ? $content[ $r ][ $c ] : array(), $cols[ $c ], 'th' ); // already escaped
						endfor; ?>
					</tr>
				<?php endfor; ?>
			</thead>
		<?php endif; ?>

		<tbody>
			<?php for ( $r = $header_rows; $r < $body_end; $r ++ ) : ?>
				<tr>
					<?php for ( $c = 0; $c < $ncol; $c ++ ) :
						echo $render_cell( isset( $content[ $r ][ $c ] ) ? $content[ $r ][ $c ] : array(), $cols[ $c ], 'td' ); // already escaped
					endfor; ?>
				</tr>
			<?php endfor; ?>
		</tbody>

		<?php if ( $footer_rows > 0 ) : ?>
			<tfoot>
				<?php for ( $r = $body_end; $r < $nrow; $r ++ ) : ?>
					<tr>
						<?php for ( $c = 0; $c < $ncol; $c ++ ) :
							echo $render_cell( isset( $content[ $r ][ $c ] ) ? $content[ $r ][ $c ] : array(), $cols[ $c ], 'td' ); // already escaped
						endfor; ?>
					</tr>
				<?php endfor; ?>
			</tfoot>
		<?php endif; ?>

		<?php if ( $caption && ! $caption_top ) : ?>
			<caption class="fw-table-caption fw-table-caption-bottom"><?php echo wp_kses_post( $caption ); ?></caption>
		<?php endif; ?>

	</table>
</div>
<?php if ( $frame_preset !== '' ) : ?></div><?php endif; ?>
