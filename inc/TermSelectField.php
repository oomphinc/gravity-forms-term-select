<?php

namespace OomphInc\GFormsTermSelect;

if ( !class_exists( '\GF_Field' ) ) {
	return;
}

class TermSelectField extends \GF_Field {

	public $type = 'term-select';

	/**
	 * Acts like a filter to convert a raw value to a display value for this field type.
	 * @see \GF_Field::get_value_entry_detail
	 * @see  other derivative classes in GF core as examples
	 */
	public function get_value_entry_detail( $value, $currency = '', $use_text = false, $format = 'html', $media = 'screen' ) {
		// ensure the raw value is a comma-separated list
		if ( empty( $value ) || !preg_match( '/^\d+(?:,\d+)*$/', $value ) ) {
			return $value;
		}
		// grab the term objects associated with the selected term IDs
		$terms = get_terms( [
			'hide_empty' => false,
			'taxonomy' => $field->termSelectTax,
			'include' => $value,
		] );
		// did we match terms?
		if ( empty( $terms ) ) {
			return $value;
		}
		// pluck out the display names
		$labels = wp_list_pluck( $terms, 'name' );
		// format for html context?
		if ( $format === 'html' ) {
			$out = '<ul class="bulleted">';
			foreach ( $labels as $label ) {
				$out .= '<li>' . esc_html( $label ) . '</li>';
			}
			return $out . '</ul>';
		// otherwise plain text
		} else {
			return implode( ', ', $labels );
		}
	}

}
