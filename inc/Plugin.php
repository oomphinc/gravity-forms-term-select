<?php

/*
Gravity Forms Term Select main plugin class

@todo
- option to allow new terms
- additional options available thru chosen
- can we use the chosen version bundled with GF? will our copy conflict?
- do we need any tool tips?
*/

namespace OomphInc\GFormsTermSelect;

class Plugin {

	const type = 'term-select'; // gforms field type
	protected static $post_id; // post id for editing an existing post

	/**
	 * Hook some stuffs!
	 */
	static function init() {
		add_filter( 'gform_add_field_buttons', [ __CLASS__, 'add_field_buttons' ] );
		add_filter( 'gform_field_type_title', [ __CLASS__, 'field_title' ] );
		add_filter( 'gform_field_input', [ __CLASS__, 'field' ], 10, 5 );
		add_action( 'gform_field_standard_settings', [ __CLASS__, 'settings' ], 10, 2 );
		add_action( 'gform_editor_js_set_default_values', [ __CLASS__, 'field_edit_defaults' ] );
		add_action( 'gform_editor_js', [ __CLASS__, 'editor_js' ] );
		add_action( 'gform_enqueue_scripts', [ __CLASS__, 'enqueue_scripts' ], 10, 2 );
		add_action( 'gform_after_submission', [ __CLASS__, 'after_submission' ], 1, 2 );
		add_action( 'gform_update_post/setup_form', [ __CLASS__, 'capture_post_id' ] );
		add_action( 'gform_loaded', function() {
			require __DIR__ . '/TermSelectField.php';
			\GF_Fields::register( new TermSelectField );
		} );
	}

	/**
	 * Add a new button to the field types add panel
	 * @param  array $field_groups array of field groups which each contain buttons for fields
	 * @filter gform_add_field_buttons
	 */
	static function add_field_buttons( $field_groups ) {
		foreach ( $field_groups as &$group ) {
			if ( $group['name'] === 'post_fields' ) {
				$group['fields'][] = [
					'class' => 'button',
					'data-type' => self::type,
					'value' => 'Terms',
				];
			}
		}
		return $field_groups;
	}

	/**
	 * Filter the title for the field as it appears in the editor
	 * @param  string $type field type
	 * @filter gform_field_type_title
	 */
	static function field_title( $type ) {
		if ( $type === self::type ) {
			return 'Term Select';
		}
		return $type;
	}

	/**
	 * Determine if the given field object is of the correct type.
	 * @param  object $field field object
	 * @return bool        is it?
	 */
	static function field_is_this( $field ) {
		return $field->type === self::type;
	}

	/**
	 * Filter the HTML markup for a particular field
	 * @param  string $input   original markup (at this point, always an empty string)
	 * @param  object $field   field object
	 * @param  string $value   default value
	 * @param  int    $lead_id lead id
	 * @param  int    $form_id form id
	 * @filter gform_field_input
	 */
	static function field( $input, $field, $value, $lead_id, $form_id ) {
		$html_id = 'input_' . ( is_admin() || !$form_id ? '' : $form_id . '_' ) . $field->id;
		$is_single = $field->termSelectMax == 1;
		if ( self::field_is_this( $field ) ) {
			$markup = '<div class="ginput_container"><select';
			// no need to populate on admin
			if ( !is_admin() ) {
				// add attributes
				foreach ( [
					'class' => 'medium gform-term-select',
					'name' => "input_{$field->id}" . ( !$is_single ? '[]' : '' ),
					'id' => $html_id,
					'data-placeholder' => $field->placeholder,
					'data-max-selected' => $field->termSelectMax,
				] as $name => $value ) {
					$markup .= " $name=\"" . esc_attr( $value ) . '"';
				}
				$markup .= $is_single ? '><option></option>' : ' multiple>';
				$terms = get_terms( $field->termSelectTax, [
					'taxonomy' => $field->termSelectTax,
					'hide_empty' => false,
				] );
				foreach ( $terms as $term ) {
					$markup .= '<option value="' . esc_attr( $term->term_id ) . '"' . selected( self::$post_id && has_term( $term->term_id, $field->termSelectTax, self::$post_id ), true, false ) . '>' . esc_html( $term->name ) . '</option>';
				}
			} else {
				$markup .= ' class="medium" disabled>';
			}
			$markup .= '</select></div>';
			return $markup;
		}
		return $input;
	}

	/**
	 * Settings fields for this field type.
	 * @action gform_field_standard_settings
	 */
	static function settings( $position, $form_id ) {
		if ( $position == 25 ) {
		?>
			<li class="term_select_settings field_setting">
				<label for="term_select_tax">
					Taxonomy
					<select id="term_select_tax" onchange="SetFieldProperty('termSelectTax', jQuery(this).val());">
						<option> - select - </option>
					<?php
					// list out all of the taxonomies
					foreach ( get_taxonomies( [], 'objects' ) as $name => $tax ) {
						echo '<option value="' . esc_attr( $name ) . '">' . esc_html( $tax->label ) . '</option>';
					}
					?>
					</select>
				</label>
				<label for="term_select_max">
					Maximum Selected Terms
					<input type="text" id="term_select_max" onkeyup="SetFieldProperty('termSelectMax', this.value);" size="4">
				</label>
			</li>
		<?php
		}
	}

	/**
	 * Fill in defaults for a new field
	 * @action gform_editor_js_set_default_values
	 */
	static function field_edit_defaults() {
	?>
		case <?php echo json_encode( self::type ); ?>:
			field.label = 'Terms';
		break;
	<?php
	}

	/**
	 * Additional JS to be included after gravity forms JS
	 * @action gform_editor_js
	 */
	static function editor_js() {
	?>
		<script type="text/javascript">
			//defining settings for the new custom field
			fieldSettings[<?php echo json_encode( self::type ); ?>] = '.term_select_settings, .conditional_logic_field_setting, .error_message_setting, .label_setting, .label_placement_setting, .rules_setting, .admin_label_setting, .size_setting, .visibility_setting, .placeholder_setting, .description_setting, .css_class_setting';

			(function($) {
				//binding to the load field settings event to initialize the field
				$(document).bind('gform_load_field_settings', function(event, field, form){
					$("#term_select_tax").val(field['termSelectTax']);
					$("#term_select_max").val(field['termSelectMax']);
				});
			})(jQuery);
		</script>
	<?php
	}

	/**
	 * Enqueue scripts needed for the geo complete field
	 * @param  array $form form properties
	 * @param  bool $ajax ajax form or not
	 * @action gform_enqueue_scripts
	 */
	static function enqueue_scripts( $form, $ajax ) {
		foreach ( $form['fields'] as $field ) {
			if ( self::field_is_this( $field ) ) {
				wp_enqueue_script( 'chosen-js-term-select', PLUGINS_URL . '/assets/chosen.jquery.min.js', [ 'jquery' ] );
				wp_enqueue_style( 'chosen-js-css', PLUGINS_URL . '/assets/chosen.min.css' );
				wp_enqueue_script( 'gforms-term-select', PLUGINS_URL . '/assets/term-select.js', [ 'jquery', 'chosen-js-term-select' ] );
				break;
			}
		}
	}

	/**
	 * @action gform_after_submission
	 */
	static function after_submission( $entry, $form ) {
		// Check if the submission contains a WordPress post
		if ( !empty( $entry['post_id'] ) ) {
			// cycle thru to see if there are any term select fields
			foreach ( $form['fields'] as $field ) {
				if ( !self::field_is_this( $field ) ) {
					continue;
				}
				// split on comma, convert to int, filter out falsey values
				$term_ids = array_filter( array_map( 'intval', explode( ',', $entry[ $field->id ] ) ) );
				// replace the terms by ID, making sure they are integers
				wp_set_object_terms( $entry['post_id'], $term_ids, $field->termSelectTax, false );
			}
		}
	}

	/**
	 * Capture the post id for the currently edited post when it is set up
	 * by Gravity Forms: Post Updates plugin.
	 *
	 * @action gform_update_post/setup_form
	 */
	static function capture_post_id( $data ) {
		if ( !empty( $data['post_id'] ) ) {
			self::$post_id = $data['post_id'];
		}
	}

}
