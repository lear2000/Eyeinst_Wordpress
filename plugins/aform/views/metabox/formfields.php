<?php  
/**
 * $post
 * $args
 */

global $aformPlugin , $post;

?>
<script>
	<?php /*our localized script / don't move */ ?>
	<?php  $jsdata = $post; ?>
	var aformPost = <?php echo json_encode($jsdata); ?>
</script>
<div class="field--dropzone rm-droppable-field" id="fieldDropzone">

	<?php  
		
		$oldpost = $post;
		$form_id = $post->ID;
		$fields = _AFORMDB()->getFields( $form_id );
	
		if(!empty($fields)):
			foreach( $fields as $index => $field ):				
				aformRender( $inputType = $field->input_type , $index , $fieldData = $field );
			endforeach;
		else:	
			//aformRender( 'submitbutton', 0, '' );
		endif;
	?>
</div>
<?php do_action('aform/admin/after_fields' , $fields , $oldpost); ?>

<div style="display:none" class="aform-dialogs">
	<div id="deleteDialog">Do you really want to delete this?</div>
	<div id="createChoicesDialog">
		+ Choices are separated by a pipe <small><strong>( | )</strong></small><br>
		+ A choice is made up of (<em>value:label</em>) or just a (<em>value</em>)<br>
		+ A choice made up of (<em>value:label</em>) must separated by a colon <small><strong>( : )</strong></small><br>
		+ A choice without a label will be interpreted as <small><strong>value:value</strong></small>
		<div class="af-codeblock">
		example
		<hr>
		<strong>one|two:Two</strong> Is interpreted as:<br>
		<strong>one:one|two:Two</strong>
		</div>
		<textarea style="width:100%;" rows="3"></textarea>
	</div>
</div>