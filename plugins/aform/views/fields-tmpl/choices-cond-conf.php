<?php  
global $post , $post_id;
$fieldIdAsIndex = $fieldData->ID;
?>

<?php $inputString = "aform-fields[$fieldIdAsIndex][input_values]"; ?>

<div class="toggle-tab toggle-choices"><h4><a>Choices <span class="dashicons dashicons-arrow-right"></span></a></h4></div>
<div class="field-tab field-tab-choices">
	<?php  
	if(isset($self->input->settings->for_admin_email) && isset($self->input->settings->use_choicelabel_as_value)):
		$labelOptionalData = true;
	else:
		$labelOptionalData = false;
	endif;
	?>
	<div><button class="choicesfromstring button" data-fieldid="<?php echo ($fieldIdAsIndex);?>"><i class="fa fa-list" aria-hidden="true"></i> Bulk Create</button></div>
	<div class="input-values holds-choices" data-label-optional="<?php echo ($labelOptionalData);?>">
		<?php
		$fieldSettings = $self->input->settings;
		$option = null;
		$count = 0;
		foreach( $self->input->values as $value ):				
			if(property_exists( $fieldSettings , 'conditional_confirmation')):
				$option = 'conditional_confirmation';
			endif;
			$self->renderFieldValue( $fieldIdAsIndex , $count , $value->value , $value->display , $option , $value );
			$count++;
		endforeach; ?>

	</div>
	<div class="wysiwig-wrapper" style="display: none;">
		<p style="text-align: center;"class="clearfix"><a href="#apply" class="button apply-content">Apply</a> <a href="#apply" class="button close-content">Cancel</a></p>
		<br>
		<h3><center>Conditional Confirmation</center></h3>
		<br>
		<h4>Subject</h4>
		<div>
			<input type="text" placeholder="Optional" class="widefat custom-subject-line-temp">
		</div>
		<br>
		<h4>Message</h4>
		<div class="content-box"></div>
		
	</div>

	<button class="add-input-value button"><span><i class="fa fa-plus" aria-hidden="true"></i> Add New</span></button>
</div>


<?php $self->formfieldAdvancedToggle(); ?>

<div class="field-create-advanced field-tab">
	
	<?php 
		$self->standardAdvSettings($index , $fieldData , array('placeholder')); 
		$inputString = "aform-fields[$fieldIdAsIndex][input_settings]";  
	?>

	<div class="settings-holder">
		<div>
			<?php  
				$fieldtype = $self->fieldsettings->name;
			?>
			<?php if($fieldtype == 'selectbox'): ?>
				<p>
					<label for="<?php echo $inputString; ?>[ignore_first]">Ignore first item in validation</label>
					<span class="af-input-switch">
						<input id="<?php echo $inputString; ?>[ignore_first]" name="<?php echo $inputString; ?>[ignore_first]" type="checkbox" <?php echo ( $self->inputSettings( 'ignore_first' ) == true ) ? 'checked' : '';?> value="true" >
						<label for="<?php echo $inputString; ?>[ignore_first]"></label>
					</span>
				</p>
			<?php endif; ?>
			<?php if($fieldtype == 'checkboxgroup'): ?>
				<p>
					<label for="<?php echo $inputString; ?>[max_selected]">Maximum selectable items</label>
					<input type="number" id="<?php echo $inputString; ?>[max_selected]" name="<?php echo $inputString; ?>[max_selected]" value="<?php echo $self->inputSettings( 'max_selected' ); ?>">
				</p>
			<?php endif; ?>
			
			<p>
				<label for="<?php echo $inputString; ?>[conditional_confirmation]">Apply conditional confirmation</label>
				<span class="af-input-switch">
					<input id="<?php echo $inputString; ?>[conditional_confirmation]" class="apply-conditional-conf" name="<?php echo $inputString; ?>[conditional_confirmation]" type="checkbox" <?php echo ( $self->inputSettings( 'conditional_confirmation' ) == true ) ? 'checked' : '';?> value="true" >
					<label for="<?php echo $inputString; ?>[conditional_confirmation]"></label>
				</span>
			</p>

		</div>
	</div>
<?php $self->advancedSettingsBottom(); ?>


</div> <!-- .field-create-advanced-->