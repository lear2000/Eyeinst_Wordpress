<?php  
global $post , $post_id;
$fieldIdAsIndex = $fieldData->ID;
?>
<div class="toggle-tab toggle-choices"><h4><a>Choices <span class="dashicons dashicons-arrow-right"></span></a></h4></div>
<div class="field-tab field-tab-choices">	
	<div><button class="choicesfromstring button" data-fieldid="<?php echo ($fieldIdAsIndex);?>"><i class="fa fa-list" aria-hidden="true"></i> Bulk Create</button></div>
	<div class="input-values holds-choices">
		<?php
		$count = 0;
		foreach( $self->input->values as $value ):				
			 $self->renderFieldValue( $fieldIdAsIndex , $count , $value->value, $value->display );
			$count++;
		endforeach; ?>
	</div>
	<button class="add-input-value button"><span><i class="fa fa-plus" aria-hidden="true"></i> Add New</span></button>
</div>

<?php $self->formfieldAdvancedToggle(); ?>
<div class="field-create-advanced field-tab">

	<?php  
	$self->standardAdvSettings($index , $fieldData , array('placeholder')); 
	$inputString = "aform-fields[$fieldIdAsIndex][input_settings]"; 
	?>
	
	<?php  $fieldtype = $self->fieldsettings->name; ?>
	<?php if($fieldtype == 'selectbox'): ?>
	<p>
		<label for="<?php echo $inputString; ?>[ignore_first]">Ignore first item in validation</label>
		<input id="<?php echo $inputString; ?>[ignore_first]" name="<?php echo $inputString; ?>[ignore_first]" type="checkbox" <?php echo ( $self->inputSettings( 'ignore_first' ) == true ) ? 'checked' : '';?> value="true" >
	</p>
	<?php endif; ?>
	<?php if($fieldtype == 'checkboxgroup'): ?>
	<p>
		<label for="<?php echo $inputString; ?>[max_selected]">Maximum selectable items:</label>
		<input type="number" id="<?php echo $inputString; ?>[max_selected]" name="<?php echo $inputString; ?>[max_selected]" value="<?php echo $self->inputSettings( 'max_selected' ); ?>">
	</p>
	<?php endif; ?>
<?php $self->advancedSettingsBottom(); ?>

</div> <!-- .field-create-advanced-->