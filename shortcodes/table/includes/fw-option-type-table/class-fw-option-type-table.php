<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

class FW_Option_Type_Table extends FW_Option_Type
{
	protected function _init() {}

	public function get_type() {
		return 'table';
	}

	/**
	 * Inline HTML allowed inside a tabular cell.
	 * Keeps basic formatting from pasted Word / HTML / typed content, strips the rest.
	 * @return array
	 */
	public static function allowed_cell_html() {
		return apply_filters( 'fw_option_type_table_allowed_cell_html', array(
			'a'      => array( 'href' => true, 'title' => true, 'target' => true, 'rel' => true ),
			'strong' => array(), 'b' => array(),
			'em'     => array(), 'i' => array(),
			'u'      => array(), 's' => array(),
			'br'     => array(),
			'span'   => array( 'style' => true ),
			'sup'    => array(), 'sub' => array(),
			'small'  => array(),
			'code'   => array(),
		) );
	}

	private static function allowed_aligns() {
		return array( '', 'left', 'center', 'right', 'justify' );
	}

	/**
	 * @internal
	 * {@inheritdoc}
	 */
	protected function _enqueue_static( $id, $option, $data ) {
		$table_shortcode = fw()->extensions->get( 'shortcodes' )->get_shortcode( 'table' );

		$static_uri = $table_shortcode->get_declared_uri() . '/includes/fw-option-type-table/static/';

		wp_enqueue_style( 'font-awesome' );

		wp_enqueue_style(
			'fw-option-' . $this->get_type() . '-default',
			$static_uri . 'css/default-styles.css',
			array(),
			fw()->theme->manifest->get_version()
		);
		wp_enqueue_style(
			'fw-option-' . $this->get_type() . '-extended',
			$static_uri . 'css/extended-styles.css',
			array(),
			fw()->theme->manifest->get_version()
		);
		// New tabular editor styles
		wp_enqueue_style(
			'fw-option-' . $this->get_type() . '-tabular',
			$static_uri . 'css/tabular-editor.css',
			array( 'fw-option-' . $this->get_type() . '-default' ),
			fw()->theme->manifest->get_version()
		);

		// Legacy editor (drives the pricing-table grid + the purpose toggle)
		wp_enqueue_script(
			'fw-option-' . $this->get_type(),
			$static_uri . 'js/scripts.js',
			array( 'jquery', 'fw-events', 'jquery-ui-sortable' ),
			fw()->theme->manifest->get_version(),
			true
		);
		// New tabular editor (import/export parsers load first)
		wp_enqueue_script(
			'fw-option-' . $this->get_type() . '-io',
			$static_uri . 'js/import-export.js',
			array(),
			fw()->theme->manifest->get_version(),
			true
		);
		wp_enqueue_script(
			'fw-option-' . $this->get_type() . '-tabular',
			$static_uri . 'js/tabular-editor.js',
			array( 'jquery', 'fw-events', 'jquery-ui-sortable', 'fw-option-' . $this->get_type() . '-io' ),
			fw()->theme->manifest->get_version(),
			true
		);

		wp_localize_script(
			'fw-option-' . $this->get_type(),
			'localizeTableBuilder',
			array(
				'msgEdit' => __( 'Edit', 'fw' ),
				'maxCols' => apply_filters( 'fw_ext_shortcodes_table_max_columns', 6 )
			)
		);

		wp_localize_script(
			'fw-option-' . $this->get_type() . '-tabular',
			'localizeTabularTable',
			array(
				'maxCols'        => apply_filters( 'fw_ext_shortcodes_table_max_columns', 50 ),
				'l10n'           => array(
					'col'            => __( 'Column', 'fw' ),
					'row'            => __( 'Row', 'fw' ),
					'headerRows'     => __( 'Header rows', 'fw' ),
					'footerRows'     => __( 'Footer rows', 'fw' ),
					'insertLeft'     => __( 'Insert column left', 'fw' ),
					'insertRight'    => __( 'Insert column right', 'fw' ),
					'insertAbove'    => __( 'Insert row above', 'fw' ),
					'insertBelow'    => __( 'Insert row below', 'fw' ),
					'duplicate'      => __( 'Duplicate', 'fw' ),
					'delete'         => __( 'Delete', 'fw' ),
					'moveLeft'       => __( 'Move left', 'fw' ),
					'moveRight'      => __( 'Move right', 'fw' ),
					'moveUp'         => __( 'Move up', 'fw' ),
					'moveDown'       => __( 'Move down', 'fw' ),
					'alignLeft'      => __( 'Align left', 'fw' ),
					'alignCenter'    => __( 'Align center', 'fw' ),
					'alignRight'     => __( 'Align right', 'fw' ),
					'cantDeleteLast' => __( 'A table needs at least one row and one column.', 'fw' ),
					'merge'          => __( 'Merge', 'fw' ),
					'unmerge'        => __( 'Unmerge', 'fw' ),
					'mergeHint'      => __( 'Select cells (Shift+click) then merge', 'fw' ),
					'selectMerge'    => __( 'Shift+click to select 2+ cells, then Merge.', 'fw' ),
					'noMerge'        => __( 'No merged cell selected.', 'fw' ),
					'import'         => __( 'Import', 'fw' ),
					'export'         => __( 'Export', 'fw' ),
					'pasteHtml'      => __( 'Paste HTML / Word table', 'fw' ),
					'uploadCsv'      => __( 'Upload CSV', 'fw' ),
					'downloadCsv'    => __( 'Download CSV', 'fw' ),
					'copyClipboard'  => __( 'Copy to clipboard', 'fw' ),
					'pastePlaceholder' => __( 'Paste your table here…', 'fw' ),
					'pasteHtmlNote'  => __( 'Paste a table copied from Word, Google Docs, Excel or a web page, then click Import. Existing content is replaced.', 'fw' ),
					'csvNote'        => __( 'Choose a .csv exported from Excel or Google Sheets. The delimiter is auto-detected.', 'fw' ),
					'firstRowHeader' => __( 'First row is a header', 'fw' ),
					'chooseFile'     => __( 'Choose a file first.', 'fw' ),
					'noTableFound'   => __( 'No table content detected.', 'fw' ),
					'imported'       => __( 'Imported %d rows.', 'fw' ),
					'copied'         => __( 'Copied to clipboard.', 'fw' ),
					'cancel'         => __( 'Cancel', 'fw' ),
				),
			)
		);

		fw()->backend->option_type( 'popup' )->enqueue_static();
		fw()->backend->option_type( 'textarea-cell' )->enqueue_static();
	}

	/**
	 * @internal
	 */
	protected function _render( $id, $option, $data ) {
		$table_shortcode = fw()->extensions->get( 'shortcodes' )->get_shortcode( 'table' );

		if ( ! $table_shortcode ) {
			trigger_error(
				__( 'table-builder option type must be inside the table shortcode', 'fw' ),
				E_USER_ERROR
			);
		}

		if ( ! isset( $data['value'] ) || empty( $data['value'] ) ) {
			$data['value'] = $option['value'];
		}

		$this->replace_with_defaults( $option );

		$view_path = $table_shortcode->get_declared_path() . '/includes/fw-option-type-table/views/view.php';

		return fw_render_view( $view_path, array(
			'id'            => $option['attr']['id'],
			'option'        => $option,
			'data'          => $data,
			'editor_model'  => $this->build_editor_model( $data['value'] ),
		) );
	}

	protected function replace_with_defaults( &$option ) {
		$defaults                                           = $this->_get_defaults();
		$option['header_options']                           = $defaults['header_options'];
		$option['row_options']                              = $defaults['row_options'];
		$option['columns_options']                          = $defaults['columns_options'];
		$option['content_options']                          = $defaults['content_options'];
		$option['row_options']['name']['attr']['class']     = isset( $option['row_options']['name']['attr']['class'] ) ? $option['row_options']['name']['attr']['class'] . ' fw-table-builder-row-style' : 'fw-table-builder-row-style';
		$option['columns_options']['name']['attr']['class'] = isset( $option['columns_options']['name']['attr']['class'] ) ? $option['columns_options']['name']['attr']['class'] . ' fw-table-builder-col-style' : 'fw-table-builder-col-style';
	}

	/**
	 * Build the normalized model the JS tabular editor boots from.
	 * Tolerant of both new tabular values and legacy/pricing values so a table
	 * saved before this rebuild keeps rendering when switched to tabular mode.
	 *
	 * @param array $value
	 * @return array
	 */
	public function build_editor_model( $value ) {
		$value = is_array( $value ) ? $value : array();

		$cols = ( isset( $value['cols'] ) && is_array( $value['cols'] ) ) ? array_values( $value['cols'] ) : array();
		$rows = ( isset( $value['rows'] ) && is_array( $value['rows'] ) ) ? array_values( $value['rows'] ) : array();
		$content = ( isset( $value['content'] ) && is_array( $value['content'] ) ) ? array_values( $value['content'] ) : array();

		if ( empty( $cols ) ) {
			$cols = array( array( 'name' => 'default-col' ), array( 'name' => 'default-col' ), array( 'name' => 'default-col' ) );
		}
		if ( empty( $rows ) ) {
			$rows = array( array( 'name' => 'heading-row' ), array( 'name' => 'default-row' ), array( 'name' => 'default-row' ) );
		}

		$out_cols = array();
		foreach ( $cols as $col ) {
			$align = ( isset( $col['align'] ) && in_array( $col['align'], self::allowed_aligns(), true ) ) ? $col['align'] : '';
			$out_cols[] = array(
				'name'  => isset( $col['name'] ) ? (string) $col['name'] : 'default-col',
				'align' => $align,
				'width' => isset( $col['width'] ) ? (string) $col['width'] : '',
			);
		}

		$all_empty = true;
		$out_content = array();
		foreach ( $rows as $ri => $row ) {
			$line = array();
			foreach ( $out_cols as $ci => $col ) {
				$cell = ( isset( $content[ $ri ][ $ci ] ) && is_array( $content[ $ri ][ $ci ] ) ) ? $content[ $ri ][ $ci ] : array();
				$text = isset( $cell['textarea'] ) ? (string) $cell['textarea'] : '';
				if ( '' !== trim( $text ) ) {
					$all_empty = false;
				}
				$line[] = array(
					'textarea' => $text,
					'colspan'  => isset( $cell['colspan'] ) ? max( 1, (int) $cell['colspan'] ) : 1,
					'rowspan'  => isset( $cell['rowspan'] ) ? max( 1, (int) $cell['rowspan'] ) : 1,
					'merged'   => ! empty( $cell['merged'] ),
				);
			}
			$out_content[] = $line;
		}

		// Derive header / footer row counts.
		$header = ( isset( $value['header_options'] ) && is_array( $value['header_options'] ) ) ? $value['header_options'] : array();

		if ( isset( $header['header_rows'] ) && '' !== $header['header_rows'] ) {
			$header_rows = max( 0, (int) $header['header_rows'] );
		} else {
			$header_rows = 0;
			foreach ( $rows as $row ) {
				if ( isset( $row['name'] ) && 'heading-row' === $row['name'] ) {
					$header_rows ++;
				} else {
					break;
				}
			}
			if ( 0 === $header_rows && $all_empty ) {
				$header_rows = 1; // pleasant default for a freshly inserted table
			}
		}
		$header_rows = min( $header_rows, count( $out_content ) );

		$footer_rows = ( isset( $header['footer_rows'] ) && '' !== $header['footer_rows'] ) ? max( 0, (int) $header['footer_rows'] ) : 0;
		$footer_rows = min( $footer_rows, max( 0, count( $out_content ) - $header_rows ) );

		return array(
			'header_options' => array(
				'header_rows' => $header_rows,
				'footer_rows' => $footer_rows,
			),
			'cols'    => $out_cols,
			'content' => $out_content,
		);
	}

	/**
	 * Decode + sanitize the JSON value produced by the new tabular editor
	 * into the canonical {header_options, cols, rows, content} db shape.
	 *
	 * @param array $option
	 * @param array $input_value
	 * @return array
	 */
	private function get_value_from_json( $option, $input_value ) {
		$decoded = json_decode( (string) $input_value['__json'], true );

		if ( ! is_array( $decoded ) ) {
			return $option['value'];
		}

		$value = array( 'header_options' => array(), 'cols' => array(), 'rows' => array(), 'content' => array() );

		// table_purpose comes from the rendered <select>; header/footer rows from the JSON.
		$purpose = 'tabular';
		if ( isset( $input_value['header_options']['table_purpose'] ) ) {
			$purpose = (string) $input_value['header_options']['table_purpose'];
		} elseif ( isset( $decoded['header_options']['table_purpose'] ) ) {
			$purpose = (string) $decoded['header_options']['table_purpose'];
		}

		$dec_header = isset( $decoded['header_options'] ) && is_array( $decoded['header_options'] ) ? $decoded['header_options'] : array();
		$header_rows = isset( $dec_header['header_rows'] ) ? max( 0, (int) $dec_header['header_rows'] ) : 0;
		$footer_rows = isset( $dec_header['footer_rows'] ) ? max( 0, (int) $dec_header['footer_rows'] ) : 0;

		// Columns
		$cols = isset( $decoded['cols'] ) && is_array( $decoded['cols'] ) ? array_values( $decoded['cols'] ) : array();
		foreach ( $cols as $col ) {
			$align = ( isset( $col['align'] ) && in_array( $col['align'], self::allowed_aligns(), true ) ) ? $col['align'] : '';
			$value['cols'][] = array(
				'name'  => isset( $col['name'] ) ? sanitize_html_class( $col['name'], 'default-col' ) : 'default-col',
				'align' => $align,
				'width' => isset( $col['width'] ) ? preg_replace( '/[^0-9a-z%.\\s]/i', '', (string) $col['width'] ) : '',
			);
		}
		$col_count = count( $value['cols'] );
		if ( 0 === $col_count ) {
			return $option['value'];
		}

		// Content (+ derive per-row name from header position so the renderer gets a real <thead>)
		$content = isset( $decoded['content'] ) && is_array( $decoded['content'] ) ? array_values( $decoded['content'] ) : array();
		$allowed = self::allowed_cell_html();
		$row_count = count( $content );

		$header_rows = min( $header_rows, $row_count );
		$footer_rows = min( $footer_rows, max( 0, $row_count - $header_rows ) );

		foreach ( $content as $ri => $row ) {
			$row = is_array( $row ) ? array_values( $row ) : array();
			$line = array();
			for ( $ci = 0; $ci < $col_count; $ci ++ ) {
				$cell = ( isset( $row[ $ci ] ) && is_array( $row[ $ci ] ) ) ? $row[ $ci ] : array();
				$text = isset( $cell['textarea'] ) ? (string) $cell['textarea'] : '';
				$line[ $ci ] = array(
					'textarea' => wp_kses( $text, $allowed ),
					'colspan'  => isset( $cell['colspan'] ) ? max( 1, (int) $cell['colspan'] ) : 1,
					'rowspan'  => isset( $cell['rowspan'] ) ? max( 1, (int) $cell['rowspan'] ) : 1,
					'merged'   => ! empty( $cell['merged'] ),
				);
			}
			$value['content'][ $ri ] = $line;

			$is_header = $ri < $header_rows;
			$value['rows'][ $ri ] = array( 'name' => $is_header ? 'heading-row' : 'default-row' );
		}

		$value['header_options'] = array(
			'table_purpose' => 'pricing' === $purpose ? 'pricing' : 'tabular',
			'header_rows'   => $header_rows,
			'footer_rows'   => $footer_rows,
		);

		return $value;
	}

	/**
	 * @internal
	 */
	protected function _get_value_from_input( $option, $input_value ) {
		// New tabular editor: a single JSON blob. Branch only when tabular is the
		// selected purpose so the legacy pricing parser below stays authoritative
		// for pricing tables.
		if ( is_array( $input_value ) && isset( $input_value['__json'] ) ) {
			$purpose = isset( $input_value['header_options']['table_purpose'] )
				? (string) $input_value['header_options']['table_purpose']
				: 'tabular';

			if ( 'pricing' !== $purpose ) {
				return $this->get_value_from_json( $option, $input_value );
			}
		}

		if ( ! is_array( $input_value ) ) {
			/**
			 * Execute get_value_from_input() on custom options
			 * because there may be `unique` option type that it must be updated
			 */
			foreach (array('button-row') as $row_type) {
				if (empty($option['content_options'][$row_type])) {
					continue;
				}

				$only_options = fw_extract_only_options($option['content_options'][$row_type]);

				foreach ($option['value']['rows'] as $i => $row) {
					if ($row['name'] !== $row_type || empty($option['value']['content'][$i])) {
						continue;
					}

					foreach ($option['value']['content'][$i] as &$row_values) {
						/**
						 * Move values in each $option['value'] because these values are in db format
						 * not $inpute_value (html) format
						 */
						foreach ($only_options as $o_id => $o_o) {
							if (isset($row_values[$o_id])) {
								$only_options[$o_id]['value'] = $row_values[$o_id];
							} else {
								unset($only_options[$o_id]['value']);
							}
						}

						$row_values = fw_get_options_values_from_input($only_options, array());
					}
				}
			}

			return $option['value'];
		}

		if ( ! isset( $input_value['content'] ) || empty( $input_value['content'] ) ) {
			$input_value['content'] = $option['value']['content'];
		}

		if ( ! isset( $input_value['rows'] ) || empty( $input_value['rows'] ) ) {
			$input_value['rows'] = $option['value']['rows'];
		}

		if ( ! isset( $input_value['cols'] ) || empty( $input_value['cols'] ) ) {
			$input_value['cols'] = $option['value']['cols'];
		}

		if ( isset( $input_value['content']['_template_key_row_'] ) ) {
			unset( $input_value['content']['_template_key_row_'] );
		}

		if ( isset( $input_value['rows']['_template_key_row_'] ) ) {
			unset( $input_value['rows']['_template_key_row_'] );
		}

		$value = array();

		if ( is_array( $input_value ) ) {
			if ( isset( $input_value['rows'] ) ) {
				$i = 0;
				foreach ($input_value['rows'] as $input_val) {
					$value['rows'][$i] = $input_val;
					$i++;
				}
			}

			if ( isset( $input_value['cols'] ) && is_array($input_value['cols']) ) {
				$value['cols'] =  $input_value['cols'] ;
			}

			if ( isset( $input_value['header_options'] ) and is_array( $input_value['header_options'] ) ) {
				$value['header_options'] = $input_value['header_options'];
			}

			if ( isset( $input_value['content'] ) && is_array( $input_value['content'] ) ) {
				$row_count = 0;
				foreach ( $input_value['content'] as $row => $input_value_rows_data ) {
					$cols = array();

					foreach ( $input_value_rows_data as $column => $input_value_cols_data ) {
						$row_name = $input_value['rows'][ $row ]['name'];

						foreach ( $option['content_options'][ $row_name ] as $id => $options ) {
							if ( $value['cols'][$column]['name'] == 'desc-col' ) {
								$cols[ $column ][ 'textarea' ]
									= fw()->backend->option_type( 'textarea-cell' )->get_value_from_input(
										$options,
										$input_value_cols_data[ 'default-row' ][ 'textarea-' . $row . '-' . $column ]
									);
								continue;
							}
							$cols[ $column ][ $id ]
								= fw()->backend->option_type( $options['type'] )->get_value_from_input(
									$options,
									$input_value_cols_data[ $row_name ][ $id . '-' . $row . '-' . $column ]
								);
						}

					}
					$value['content'][ $row_count++ ] = $cols;
				}
			}
		}

		return $value;
	}

	/**
	 * @internal
	 */
	protected function _get_defaults() {
		/** @var FW_Extension_Shortcodes $shortcodes */
		$shortcodes = fw_ext('shortcodes');
		/** @var FW_Shortcode_Table $table */
		$table = $shortcodes->get_shortcode('table');

		return apply_filters( 'fw_option_type_table_defaults', array(
			'header_options'  => array(
				'table_purpose' => array(
					'type'    => 'select',
					'label'   => __( 'Table Styling', 'fw' ),
					'desc'    => __( 'Choose the table styling options', 'fw' ),
					'choices' => array(
						'tabular' => __( 'Use the table to display tabular data', 'fw' ),
						'pricing' => __( 'Use the table as a pricing table', 'fw' ),
					),
					'value'   => 'tabular',
					'attr'    => array(
						'data-allowed-rows' => json_encode( array(
								'pricing' => 'default-row heading-row pricing-row button-row switch-row',
								'tabular' => 'default-row heading-row'
							)
						),
						'data-allowed-cols' => json_encode( array(
							'pricing' => 'default-col highlight-col desc-col',
							'tabular' => 'default-col desc-col'
						) ),
					)
				)
			),
			'row_options'     => array(
				'name' => array(
					'type'    => 'select',
					'label'   => false,
					'desc'    => false,
					'choices' => array(
						'default-row' => __( 'Default row', 'fw' ),
						'heading-row' => __( 'Heading row', 'fw' ),
						'pricing-row' => __( 'Pricing row', 'fw' ),
						'button-row'  => __( 'Button row', 'fw' ),
						'switch-row'  => __( 'Row switch', 'fw' )
					),
				)
			),
			'columns_options' => array(
				'name' => array(
					'type'    => 'select',
					'label'   => false,
					'desc'    => false,
					'choices' => array(
						'default-col'   => __( 'Default column', 'fw' ),
						'desc-col'      => __( 'Description column', 'fw' ),
						'highlight-col' => __( 'Highlight column', 'fw' ),
						'center-col'    => __( 'Center text column', 'fw' )
					),
				)
			),
			'content_options' => array(
				'default-row' => array(
					'textarea' => array(
						'type'  => 'textarea-cell',
						'label' => false,
						'desc'  => false,
						'value' => '',
					)
				),
				'heading-row' => array(
					'textarea' => array(
						'type'  => 'textarea-cell',
						'label' => false,
						'desc'  => false,
						'value' => '',
					)
				),
				'pricing-row' => array(
					'amount'      => array(
						'type'         => 'text',
						'label'        => false,
						'desc'         => false,
						'value'        => '',
						'wrapper_attr' => array(
							'class' => 'fw-col-sm-6'
						)
					),
					'description' => array(
						'type'         => 'text',
						'label'        => false,
						'desc'         => false,
						'value'        => '',
						'attr'         => array(
							'placeholder' => __( 'per month', 'fw' )
						),
						'wrapper_attr' => array(
							'class' => 'fw-col-sm-6'
						)
					),
				),
				'button-row'  => array(
					'button' => ( $button = $table->get_button_shortcode() )
						? array(
							'type'          => 'popup',
							'popup-title'   => __( 'Button', 'fw' ),
							'button'        => __( 'Add', 'fw' ),
							'popup-options' => $button->get_options()
						)
						: array(
							'type' => 'multi',
							'label' => false,
						)
				),
				'switch-row'  => array(
					'switch' => array(
						'label'        => false,
						'type'         => 'switch',
						'right-choice' => array(
							'value' => 'yes',
							'label' => __( 'Yes', 'fw' )
						),
						'left-choice'  => array(
							'value' => 'no',
							'label' => __( 'No', 'fw' )
						),
						'value'        => 'no',
						'desc'         => false,
					)
				)

			),
			'value'           => array(
				'header_options' => array(
					'table_purpose' => 'tabular',
					'header_rows'   => 1,
					'footer_rows'   => 0,
				),
				'cols'           => array(
					array( 'name' => 'default-col' ),
					array( 'name' => 'default-col' ),
					array( 'name' => 'default-col' )
				),
				'rows'           => array(
					array( 'name' => 'heading-row' ),
					array( 'name' => 'default-row' ),
					array( 'name' => 'default-row' )
				),
				'content'        => $this->_fw_generate_default_values()
			)
		) );
	}

	private function _fw_generate_default_values( $cols = 3, $rows = 3 ) {
		$result = array();
		for ( $i = 0; $i < $rows; $i ++ ) {
			for ( $j = 0; $j < $cols; $j ++ ) {
				$result[ $i ][ $j ] = array(
					'textarea' => '',
					'amount' => '',
					'description' => '',
					'switch' => 'no',
					'button' => '',
				);
			}
		}

		return $result;
	}


	/**
	 * @internal
	 */
	public function _get_backend_width_type() {
		return 'full';
	}

}

FW_Option_Type::register( 'FW_Option_Type_Table' );
