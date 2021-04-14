<?php
add_filter( 'attachment_fields_to_edit',  'model_tag_field', 10, 2 );
function model_tag_field( $fields, $post ) {
	ob_start();
		?>
			<label class="setting">
				<span class="name">Model Tag</span><select id="model_tag" class="model_tag" name="model_tag">
				<option value="0">False</option>
				<option value="1">True</option>
			</select>
			</label>
			<label class="setting">
				<span class="name">Class</span><input class="model_tag_class" type="text" name="model_tag_class" value="model_tag">
			</label>
			<label class="setting">
				<span class="name">Class : <b>Default is 'model_tag'</b></span>
			</label>
		<?php
	$_html = ob_get_contents();
	ob_end_clean();
	$fields['model_tag'] = array(
		'label' => '<h3>Model Tag Wrap</h3>',
		'input' => 'html',
		'html'	=> $_html,
		'show_in_edit' => true,
	);
	return $fields;
}
add_action( 'admin_enqueue_scripts', 'model_tag_field_scripts' );
function  model_tag_field_scripts($screen){
	global $current_screen;
	if( $current_screen->base != 'post' ) return;
	$assets = get_template_directory_uri() . '/js/libs/';
	wp_register_script( $handle = 'mode_tag_wpmu', $src = $assets . 'uploader.js', $deps = array('jquery') , $ver = '1', $in_footer = true );
	wp_enqueue_script( $handle = 'mode_tag_wpmu' );
	wp_register_style( $handle = 'mode_tag_wpmu' , $src = $assets . 'uploader.css' , $deps = array(), $ver = '1', $media = 'all' );
	wp_enqueue_style( $handle = 'mode_tag_wpmu' );

}
?>