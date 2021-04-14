<?php  
namespace aform\fields;

class checkbox extends \aform\core\field {
	
	var $inputvalues;
	
	function __construct(){
		$this->fieldsettings = new \StdClass;
		$this->fieldsettings->name  = 'checkbox';
		$this->fieldsettings->label = 'Checkbox';
	}
	
	function renderField( $fieldData = null , $index = null){
		global $post,$post_id;		
		ob_start();
		$fieldIdAsIndex = $fieldData->ID;
		$this->formfieldTop( $index , $fieldData );
		$this->formFieldSetup( $index, $this->fieldsettings->label , $fieldData );

		//custom field layout here
		?>
		<?php $this->formfieldAdvancedToggle(); ?>
		<div class="field-create-advanced field-tab">
			<?php 
				$this->standardAdvSettings($index , $fieldData , array('for_admin_email','placeholder')); 
				$inputString = "aform-fields[$fieldIdAsIndex][input_settings]"; 
			?>	
			<div class="settings-holder">
				<div>
					<p>
						<label for="<?php echo $inputString; ?>[checked_default]">Checked by default</label>
						<span class="af-input-switch">
							<input type="checkbox" id="<?php echo $inputString;?>[checked_default]" name="<?php echo $inputString;?>[checked_default]" value="true" <?php echo ( $this->inputSettings( 'checked_default' ) ) ? "checked" : ''; ?>>
							<label for="<?php echo $inputString; ?>[checked_default]"></label>
						</span>
					</p>
					<p>
						<label for="<?php echo $inputString; ?>[checkbox_message]">Checkbox Message</label> <input class="form-control-inline" id="<?php echo $inputString; ?>[checkbox_message]" name="<?php echo $inputString;?>[checkbox_message]" type="text" value="<?php echo htmlentities($this->inputSettings( 'checkbox_message' )); ?>">
						<br><small>Message is placed after checkbox.</small>
					</p>
				</div>
			</div>
		<?php $this->advancedSettingsBottom(); ?>
		</div> <!-- .field-create-advanced-->
		<?php
		$this->formfieldBottom();
		$output = ob_get_contents();
		ob_end_clean();
		return $output;
	}
	
	function metaboxRender($label) {
		echo $label;
		/*
		?>
		<span class="dummy-input">
			<input type="checkbox" /> <?php echo $label; ?>
		</span> 
		<?php
		*/
	}

	/*##############################################################*/
	/*#######################     PUBLIC   #########################*/
	/*##############################################################*/
	/* PUBLIC RENDER : used when creating form on the front-end */
	function publicRender( $form = null ){
		$settings = $this->input->settings;
		?>
			<input type="checkbox" name="<?php echo afFieldName($this->fieldname); ?>" id="<?php echo $this->fieldname;?>" value="true" <?php $this->fieldRequired();?> <?php echo ( isset($settings->checked_default) && $settings->checked_default == "true" ) ? "checked" : '';?>>
		<?php
		if(isset($settings->checkbox_message) && !empty($settings->checkbox_message)):
			echo "<label for=\"{$this->fieldname}\"><span>{$settings->checkbox_message}</span></label>";
		endif;
	}
	
	
	
}