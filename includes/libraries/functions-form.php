<?php
/**
 * @author Shazzad Hossain Khan
 * @url https://shazzad.me/about
**/


function fbpf_form_fields( $fields, $values = array(), $form_args = array() ) {

	if( ! is_array( $fields ) ) {
		$fields = array();
	}

	if( ! is_array( $values ) ) {
		$values = array();
	}

	if( ! is_array($form_args) ) {
		$form_args = array();
	}

	if( empty( $form_args['method'] )){
		$form_args['method'] = 'POST';
	}
	if( empty( $form_args['class'] )){
		$form_args['class'] = 'wf wf-basic';
	} else {
		$form_args['class'] = 'wf '. $form_args['class'];
	}
	if( ! empty( $form_args['ajax'] ) ) {
		$form_args['class'] .= ' wff_ajax_form';
	}
	if( ! empty( $form_args['ajax'] ) && empty($form_args['loading_text']) ) {
		$form_args['loading_text'] = 'Updating';
	}
	if( empty($form_args['button_text']) ) {
		$form_args['button_text'] = 'Update';
	}
	// @since v:2.0
	if( empty( $form_args['attr'] )){
		$form_args['attr'] = '';
	}
	if( ! empty($form_args['loading_text']) ) {
		$form_args['attr'] .= ' data-loading_text="'. esc_attr($form_args['loading_text']) .'"';
	}
	if( ! empty( $form_args['context'] ) ) {
		$form_args['class'] .= ' wff-context-'. $form_args['context'];
	}

	if( empty( $form_args['action']) ) {
		$form_args['action'] = (is_ssl() ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
	}

	// query variables
	if( ! empty($form_args['qv']) ) {
		$query_vars = array();
		foreach( $form_args['qv'] as $q) {
			if( isset($_GET[$q]) && $_GET[$q] != '' ) {
				$query_vars[$q] = trim($_GET[$q]);
			}
		}

		if( ! empty( $query_vars) ){
			$form_args['action'] = add_query_arg( $query_vars, $form_args['action'] );
		}
	}


	$html = '';

	// form opening tag
	if( ! isset($form_args['no_form']) ) {

		$html .= '<form';
		$attr_keys = array( 'class', 'id', 'name', 'title', 'enctype', 'method', 'action' );
		foreach( $form_args as $name => $attr ) {
			if( ! empty($name) && in_array($name, $attr_keys) ) {
				$html .= ' '. $name .'="'. esc_attr( $attr ) .'"';
			}
		}
		if( ! empty($form_args['attr']) ) {
			$html .= $form_args['attr'];
		}
		$html .= '>';
	}

	if( ! empty( $form_args['title'] ) ) {
		$html .= '<div class="wf_form_title">' . $form_args['title'] . '</div>';
	}

	if( ! empty( $form_args['after_tag'] ) ) {
		$html .= $form_args['after_tag'];
	}

	if( isset( $form_args['button_before'] ) &&  $form_args['button_before'] === true ) {
		$html .= "<div class='wffw wffwt_submit wffwt_submit_top'><input type='submit' value='". $form_args['button_text'] ."' class='button button-primary form_button button_top'></div>";
	}

	foreach( $fields as $field ) {
		if( isset($field['name']) && $field['name'] != '' && ! array_key_exists('value', $field) ) {
			$name = isset($field['option_name']) ? $field['option_name'] : $field['name'];
			if( array_key_exists($name, $values) ) {
				$field['value'] = $values[$name];
			} else {
				$field['value'] = '';
			}
		}

		if( empty($field['input_class']) && isset($field['name']) && 'action' == $field['name'] ){
			$field['input_class'] = 'form_action';
		}

		$html .= fbpf_form_field_html( $field );
	}

	if( ! isset( $form_args['button_after'] ) ||  $form_args['button_after'] !== false ) {
		$html .= "<div class='wffw wffwt_submit wffwt_submit_bottom'>
			<input type='submit' value='". $form_args['button_text'] ."' class='form_button button_bottom'>
		</div>";
	}

	if( ! empty( $form_args['form_closing'] ) ) {
		$html .= $form_args['form_closing'];
	}

	if( ! isset( $form_args['no_form'] ) ) {
		$html .= '</form>';
	}

	return $html;
}

function fbpf_form_child_field_html( $args = array() ){

	$args['label'] = '';
	$args['field_wrap'] = false;
	$args['label_wrap'] = false;
	$args['input_wrap'] = false;

	return fbpf_form_field_html( $args );
}

function fbpf_form_field_html( $args = array()){

	if( ! is_array($args) ) {
		return;
	}

	$_args = $args;

	$defaults = array(
		'label' 			=> '',
		'name' 				=> '',
		'type'				=> 'html',
		'html'				=> '',
		'desc'				=> '',
		'default' 			=> '',
		'value' 			=> '',
		'required' 			=> 'n',
		'placeholder'		=> '',
		'readonly'			=> '',

		'id' 				=> '',
		'class'				=> '',
		'style' 			=> '',
		'attrs' 			=> array(),

		'before'			=> '',
		'after'				=> '',

		'field_wrap'		=> true,
		'field_before'		=> '',
		'field_after'		=> '',

		'label_wrap'		=> true,
		'label_wrap_before' => '',
		'label_before'		=> '',
		'label_after'		=> '',

		'input_wrap'		=> true,
		'input_wrap_before'	=> '',
		'input_wrap_class'	=> '',
		'input_wrap_attr'	=> '',
		'input_before'		=> '',
		'input_after'		=> '',
		'input_class'		=> '',
		'input_html'		=> '',
		'input_attr'		=> '',
		'input_style'		=> ''
	);


	$args = wp_parse_args( $args, $defaults );

	if( empty($args['id']) && false !== $args['id'] ){
		$args['id'] = fbpf_form_field_id( $args['name'] );
	}

	extract( $args );

	if( '' === $value ) {
		$value = $default;
	}

	if( ! isset($attr) ){
		$attr = '';
	}
	if( ! empty($style) ) {
		$attrs['style'] = $style;
	}
	foreach( $attrs as $an => $av ) {
		$attr .= ' '. $an .'="'. esc_attr($av) .'"';
	}

	$input_attrs = array();
	if( ! empty($placeholder) ) {
		$input_attrs['placeholder'] = $placeholder;
	}
	if( ! empty($readonly) ) {
		$input_attrs['readonly'] = 'readonly';
	}
	if( ! empty($input_style) ) {
		$input_attrs['style'] = $input_style;
	}

	foreach( $input_attrs as $an => $av ) {
		$input_attr .= ' '. $an .'="'. esc_attr($av) .'"';
	}

	// simply include a pre option for combo fields.
	if( in_array($type, array('select', 'select_multi', 'select2', 'checkbox', 'radio') ) ){
		if( isset($option_pre) && !empty($option_pre) && is_array($option_pre) ){
			$_option = $option_pre;
			if( ! empty($option) ){
				foreach( $option as $k => $v )
				{ $_option[$k] = $v; }
			}
			$option = $_option;
		}
	}

	// escape text and hidden field values to pass double or single quote
	if( in_array($type, array('hidden', 'text') ) ){
		$value = esc_attr( $value );
	}

	$html .= $before;

	if( ! in_array($type, array('html', 'hidden') ) && $field_wrap ){
		$html .= sprintf( '<div class="%1$s"%2$s>', fbpf_form_pitc_class('wffw', $id, $type, $class), $attr );
	}

	$html .= $field_before;

	switch( $type ):

	case "hidden":
		$html .= sprintf( '<input class="%1$s %5$s" id="%2$s" name="%3$s" value="%4$s" type="hidden" />', fbpf_form_pitc_class('wff', $id, $type), $id, $name, $value, $input_class );
	break;

	case "text":
	case "email":
	case "password":
	case "number":
	case "url":

	case "image":
	case "image_src":
	case "text_combo":

	case "view":
	case "html_input":

	case "textarea":
	case "select":
	case "select_multi":
	case "select2":
	case "radio":
	case "checkbox":


		// label
		$html .= $label_wrap_before;
		$html .= fbpf_form_field_label( $args );

		// description
		if( ! empty($desc) ){
			$html .= sprintf( '<div class="%1$s">%2$s</div>', fbpf_form_pitc_class('wffdw', $id, $type), $desc );
		}

		// input
		$html .= $input_wrap_before;
		if( $input_wrap ){
			$html .= sprintf( '<div class="%1$s %2$s"%3$s>', fbpf_form_pitc_class('wffew', $id, $type), $input_wrap_class, $input_wrap_attr );
		}

		$html .= $input_before;

		if( in_array($type, array('text', 'email', 'password', 'number', 'url'))){
			$html .= sprintf( 
				'<input class="%1$s %5$s" id="%2$s" name="%3$s" value="%4$s" type="%7$s"%6$s />', 
				fbpf_form_pitc_class('wff', $id, $type), $id, $name, $value, $input_class, $input_attr, $type
			);
		}
		elseif( $type == 'view' ){
			$html .= $value;
		}
		elseif( $type == 'image' ){
			$image = '';
			if( !isset($size) ){
				$size = 'thumbnail';
			}

			if( isset($src_url) && !empty($src_url) ){
				$image = sprintf('<img src="%s" />', $src_url);
			}
			if( $value ){
				$icon = ! wp_attachment_is_image( $value );
				if( $img = wp_get_attachment_image($value, $size, $icon) ){
					$image = $img;
				}
			}

			if( ! isset($submit) || empty($submit) ) {
				$submit = ' file';
			}

			$html .= sprintf( 
				'<input class="%1$s %5$s" id="%2$s_input" name="%3$s" value="%4$s" type="hidden" />
				<div id="%2$s_img" data-size="%8$s">%6$s</div>
				<a href="#" rel="%2$s" class="button wff_image_btn" data-field="id">Choose%7$s</a>
				<a href="#" rel="%2$s" class="button wff_image_remove_btn" data-field="id">Remove%7$s</a>', 
				fbpf_form_pitc_class('wff', $id, $type), $id, $name, $value, $input_class, $image, $submit, $size
			);
		}

		elseif( $type == 'image_src' ){
			$image = '';
			if( $value ) {
				$image = sprintf('<img src="%s" class="image_preview" />', $value);
			}

			$html .= sprintf( 
				'<input class="%1$s %5$s" rel="%2$s" id="%2$s_input" name="%3$s" value="%4$s" type="text" />
				<div id="%2$s_img" data-size="full">%6$s</div>
				<a href="#" rel="%2$s" class="button wff_image_btn" data-field="url">Choose file</a>
				<a href="#" rel="%2$s" class="button wff_image_remove_btn" data-field="url">Remove file</a>', 
				fbpf_form_pitc_class('wff', $id, $type), $id, $name, $value, $input_class, $image
			);
		}

		elseif( $type == 'textarea' ){
			$html .= sprintf( 
				'<textarea id="%2$s" class="%1$s %5$s" name="%3$s"%6$s>%4$s</textarea>', 
				fbpf_form_pitc_class('wff', $id, $type), $id, $name, $value, $input_class, $input_attr
			);
		}

		else if( $type == 'select' ) {

			$html .= sprintf( 
				'<select class="%1$s %5$s" id="%2$s" name="%3$s"%4$s>', 
				fbpf_form_pitc_class('wff', $id, $type), 
				$id, 
				$name, 
				$input_attr, 
				$input_class 
			);

			foreach( $option as $key => $label ){
				if( empty($label) ){
					continue;
				}
				elseif( is_array($label) && isset($label['optgroup_open']) ) {
					$html .= $label['optgroup_open'];
					continue;
				}
				elseif( is_array($label) && isset($label['optgroup_close']) ) {
					$html .= $label['optgroup_close'];
					continue;
				}

				$child_input_attr = '';
				$child_input_class = '';
				$_label = $label;

				if( is_array($_label) && isset($_label['child_input_before']) ) {
					$html .= $_label['child_input_before'];
				}

				if( isset($label->id) && isset($label->name) ){
					$key = $label->id;
					$label = $label->name;
				}
				elseif( $label instanceof FBPF_Data ){
					$key = $label->get_id();
					$label = $label->get_name();
				}
				elseif( isset($label['key']) && isset($label['name']) ){
					$key = $label['key'];
					$label = $label['name'];
					$child_input_attr = isset($_label['input_attr']) ? $_label['input_attr'] : '';
					$child_input_class = isset($_label['input_class']) ? $_label['input_class'] : '';
				}
				elseif( is_array($label) ) {
					$child_input_attr = isset($label['attr']) ? $label['attr'] : '';
					$label = $l['label'];
				}

				$selected = esc_attr($value) == esc_attr($key) ? ' selected="selected"' : '';
				$html .= sprintf( 
					'<option value="%1$s"%2$s class="%4$s" %5$s>%3$s</option>', 
					$key, $selected, $label, $child_input_class, $child_input_attr
				);

				if( is_array($_label) && isset($_label['child_input_after']) ) {
					$html .= $_label['child_input_after'];
				}
			}
			$html .= '</select>';
		}

		elseif( $type == 'select_multi' ){
			if( ! is_array($value) )
			{ $value = (array) $value; }

			$html .= sprintf( 
				'<select class="%1$s %5$s" id="%2$s" name="%3$s[]"%4$s multiple="multiple">', 
				fbpf_form_pitc_class('wff', $id, $type), $id, $name, $input_attr, $input_class
			);

			foreach( $option as $k => $l ){

				$_attr = '';
				if( isset($label->id) && isset($label->name) ){
					$k = $label->id;
					$l = $label->name;
				}
				elseif( isset($l['key']) && isset($l['name']) ){
					$k = $l['key'];
					$l = $l['name'];
				}
				elseif( is_array($l) ) {
					$_attr = isset($l['attr']) ? $l['attr'] : '';
					$l = $l['label'];
				}

				$sel = in_array($k, $value) ? ' selected="selected"' : '';

				$html .= sprintf( '<option value="%1$s"%2$s%4$s>%3$s</option>', $k, $sel, $l, $_attr );
			}
			$html .= '</select>';
		}

		elseif( $type == 'select2' ){
			if( ! is_array($value) )
			{ $value = (array) $value; }

			$html .= sprintf( 
				'<select class="%1$s %5$s" id="%2$s" name="%3$s"%4$s multiple="multiple">', 
				fbpf_form_pitc_class('wff', $id, $type), $id, $name, $input_attr, $input_class
			);

			foreach( $option as $key => $label ){

				$_attr = '';
				if( isset($label->id) && isset($label->name) ){
					$key = $label->id;
					$label = $label->name;
				}
				elseif( isset($label['key']) && isset($label['name']) ){
					$key = $label['key'];
					$label = $label['name'];
				}
				elseif( is_array($label) ) {
					$_attr = isset($label['attr']) ? $label['attr'] : '';
					$label = isset($label['label']) ? $label['label'] : '';
				}

				$html .= sprintf( '<option value="%1$s"%2$s%4$s>%3$s</option>', $key, $sel, $label, $_attr );
			}
			$html .= '</select>';
		}

		elseif( $type == 'radio' ){

			#FBPF_Utils::d($option);

			foreach( $option as $key => $label ){
				if( empty($label) ){
					continue;
				}

				$child_input_attr = '';
				$child_input_class = '';
				$_label = $label;

				if( is_array($_label) && isset($_label['child_input_before']) ) {
					$html .= $_label['child_input_before'];
				}
				if( isset($label->id) && isset($label->name) ){
					$key = $label->id;
					$label = $label->name;
				}
				elseif( isset($label['key']) && isset($label['name']) ){
					$key = $label['key'];
					$label = $label['name'];
					$child_input_attr = isset($_label['input_attr']) ? $_label['input_attr'] : '';
					$child_input_class = isset($_label['input_class']) ? $_label['input_class'] : '';
					#FBPF_Utils::d($label);
				}
				elseif( is_array($label) ) {
					$child_input_attr = isset($label['attr']) ? $label['attr'] : '';
					$label = $l['label'];
				}

				$checked = $value == $key ? ' checked="checked"' : '';
				$html .= sprintf( 
					'<label><input id="%1$s_%2$s" class="%6$s" name="%3$s" value="%2$s" type="radio"%4$s%7$s /> %5$s</label>', 
					$id, $key, $name, $checked, $label, $child_input_class, $child_input_attr
				);

				if( is_array($_label) && isset($_label['child_input_after']) ) {
					$html .= $_label['child_input_after'];
				}
			}
		}

		elseif( $type == 'checkbox' )
		{
			foreach( $option as $key => $label )
			{
				$_attr = '';
				if( is_array($label) && isset($label['child_input_before']) ) {
					$html .= $label['child_input_before'];
				}

				if( isset($label->id) && isset($label->name) ) {
					$key = $label->id;
					$label = $label->name;
				}
				elseif( isset($label['key']) && isset($label['name']) ) {
					$key = $label['key'];
					$label = $label['name'];
				}
				elseif( is_array($label) ) {
					$_attr = isset($label['attr']) ? $label['attr'] : '';
					$label = $label['label'];
				}

				$sel = is_array($value) && in_array($key, $value) ? ' checked="checked"' : '';
				$html .= sprintf( 
					'<label class="%6$s"><input id="%1$s_%2$s" name="%3$s[]" value="%2$s" type="checkbox"%4$s%6$s /> %5$s</label>', 
					$id, $key, $name, $sel, $label, $input_class, $_attr
				);
			}
		}

		elseif( ! empty($input_html) ){
			$html .= $input_html;
		}

		$html .= $input_after;

		if( $input_wrap ){
			$html .= '</div>';
		}
	break;

	default:
		$html .= apply_filters( 'fbpf_form_field_input/'. $type, '', compact( array_keys($args) ), $_args );
	break;

	endswitch;

	$html .= $field_after;

	if( isset($desc_after) ){
		if( ! empty($desc_after) ){
			$html .= sprintf( '<div class="%1$s">%2$s</div>', fbpf_form_pitc_class('wffdaw', $id, $type), $desc_after );
		}
	}

	if( ! in_array($type, array('html', 'hidden') ) && $field_wrap ){
		$html .= '</div>';
	}

	$html = apply_filters( 'fbpf_form_field/'. $type, $html, compact( array_keys($args) ), $_args );

	return $html;
}


// prefix id type class
function fbpf_form_field_label( $args ){
	extract( $args );
	$html = '';

	if( !empty($label) ){
		if( $label_wrap ){
			$html .= sprintf( '<div class="%1$s">', fbpf_form_pitc_class('wfflw', $id, $type) );
		}
		$html .= $label_before;

		if( $required == 'y' ){
			$label .= '<span class="req">*</span>';
		}

		// radio checkbox would use span, not label
		if( in_array($type, array('radio', 'checkbox', 'image', 'image_src', 'html_input', 'style') ) ){
			$html .= sprintf( '<span class="%1$s">%2$s</span>', fbpf_form_pitc_class('wffl', $id, $type), $label );
		}
		else{
			$html .= sprintf( '<label class="%1$s" for="%2$s">%3$s</label>', fbpf_form_pitc_class('wffl', $id, $type), $id, $label );
		}

		$html .= $label_after;
		if( $label_wrap ){
			$html .= '</div>';
		}
	}

	return $html;
}

// prefix id type class
function fbpf_form_pitc_class( $pref = '', $id = '', $type = '', $class = '' ){
	$return = "{$pref}";
	if( !empty($id) )
	{ $return .= " {$pref}i_{$id}"; }
	if( !empty($type) )
	{ $return .= " {$pref}t_{$type}"; }
	if( !empty($class) )
	{ $return .= " {$class}"; }

	return trim( esc_attr($return) );
}

// sanitize id
function fbpf_form_field_id( $raw_id = '' ){
	$sanitized = preg_replace( '|%[a-fA-F0-9][a-fA-F0-9]|', '', $raw_id );
	$sanitized = preg_replace( '/[^A-Za-z0-9_-]/', '_', $sanitized );
	$sanitized = str_replace( '__', '_', $sanitized );
	$sanitized = trim( $sanitized, '_' );
	return $sanitized;
}


/*
 * Additional field types
*/

	add_filter( 'fbpf_form_field_input/repeater', 'fbpf_form_field_repeater', 10, 3 );

function fbpf_form_field_repeater( $html, $args, $field ) {

	$fields = $args['fields'];
	$values = $args['values'];
	unset( $args['fields'], $args['values'], $field['fields'] );

	#FBPF_Utils::p( $args );
	#die();
	#return '';

	extract( $args );

	$html  = '';
	$html .= $label_wrap_before;
	$html .= fbpf_form_field_label( $args );

	// description
	if( ! empty($desc) ){
		$html .= sprintf( '<div class="%1$s">%2$s</div>', fbpf_form_pitc_class('wffdw', $id, $type), $desc );
	}

	// input
	$html .= $input_wrap_before;
	if( $input_wrap ){
		$html .= sprintf( '<div class="%1$s">', fbpf_form_pitc_class('wffew', $id, $type) );
	}

	$html .= $input_before;

	if( empty($value) ) {
		$value = $default;
	}

	$total_columns = 0;
	foreach( $fields as $key => $rf ) {

		if( in_array($rf['type'], array('text', 'number', 'html', 'select') ) ){
			++ $total_columns;
		}
		if( ! empty($rf['name']) ){
			$fields[$key]['name'] = $field['name'] ."[KEY][". $rf['name'] . "]";
			$fields[$key]['option_name'] = $rf['name'];
		}
		if( empty($rf['id']) ){
			$rf['id'] = $fields[$key]['id'] =  $rf['name'];
		}
		if( ! empty($rf['id']) ){
			$fields[$key]['id'] = $field['id'] ."_". $rf['id'];
		}
		if( empty($rf['class']) ){
			$fields[$key]['class'] = $rf['id'];
		}
	}

	$key = $field['id'];

	$html .= '<table id="wf_repeated_'.$key.'" class="wf_repetable" data-parent="'.$key.'"><thead><tr>';
	foreach( $fields as $repeat_field ) {
		if( in_array($repeat_field['type'], array('text', 'number', 'html', 'select') ) ){
			$html .= '<th class="wf_col '. $repeat_field['class'] .'">'. $repeat_field['label'] .'</th>';
		}
	}
	$html .= sprintf( '<th>%s</th>', 'Action' );
	$html .= '</tr></thead><tbody>';

	// load existing fields
	if( ! empty($values) ){

		$i = 1;
		foreach( $values as $_value ){

			$hiddens = '';
			$html .= '<tr class="wf_row">';

			$row_key = 'row-'. $i;

			foreach( $fields as $repeat_field ) {

				$repeat_field['name'] = str_replace( 'KEY', $row_key, $repeat_field['name'] );

				$option_name = $repeat_field['option_name'];
				if( isset($_value[$option_name]) ) {
					$repeat_field['value'] = $_value[$option_name];
				}

				if( in_array($repeat_field['type'], array('hidden') ) ) {
					$hiddens .= fbpf_form_child_field_html( $repeat_field );
				}
				elseif( in_array($repeat_field['type'], array('text', 'number', 'html', 'select') ) ) {
					$html .= '<td class="wf_col '. $repeat_field['class'] .'">';
					$html .= fbpf_form_child_field_html( $repeat_field );
					$html .= '</td>';
				}
			}

			$html .= '<td>';
			$html .= '<a href="#" class="wf_repeater_remove" data-parent="'.$key.'">Remove</a>';
			$html .= $hiddens;
			$html .= '</td>';
			$html .= '</tr>';
			
			++ $i;
		}
	}

	$html .= '</tbody><tfoot><tr>';
	$html .= '<td colspan="'. ( $total_columns + 1 ) .'"><a href="#" class="wf_repeater_add" data-parent="'.$key.'">Add Item</a></td>';
	$html .= '<tr></tfoot></table>';

	$hiddens = '';

	$html .= '<table id="wf_repeater_'. $key .'" class="wf_repeater" data-parent="'.$key.'"><tbody>';
	$html .= '<tr class="wf_row">';
	foreach( $fields as $repeat_field ) {

		if( in_array($repeat_field['type'], array('hidden') ) ) {
			$hiddens .= fbpf_form_child_field_html( $repeat_field );
		}
		elseif( in_array($repeat_field['type'], array('text', 'number', 'html', 'select') ) ) {
			$html .= '<td class="wf_col '. $repeat_field['class'] .'">';
			$html .= fbpf_form_child_field_html( $repeat_field );
			$html .= '</td>';
		}
	}
	$html .= '<td>';
	$html .= '<a href="#" class="wf_repeater_remove" data-parent="'.$key.'">Remove</a>';
	$html .= $hiddens;
	$html .= '</td>';
	$html .= '</tr>';
	$html .= '</tbody></table>';

	$html .= $input_after;
	if( $input_wrap ){
		$html .= '</div>';
	}

	return $html;
}
?>