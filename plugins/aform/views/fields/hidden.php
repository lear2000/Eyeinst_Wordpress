<?php  
namespace aform\fields;

class hidden extends \aform\core\field {
	
	var $inputvalues;
	
	function __construct(){
		$this->fieldsettings = new \StdClass;
		$this->fieldsettings->name  = 'hidden';
		$this->fieldsettings->label = 'Hidden Field';
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
				$this->standardAdvSettings($index , $fieldData , array('placeholder','field_description','reverse_display','hide_label')); 
				$inputString = "aform-fields[$fieldIdAsIndex][input_settings]"; 
			?>
			<div class="settings-holder">
				<div>
					<p>
						<label for="<?php echo $inputString; ?>[display_only]">Display Only<br><small>Excluded in Admin Email</small></label>
						<span class="af-input-switch">
							<input type="checkbox" id="<?php echo $inputString;?>[display_only]" name="<?php echo $inputString;?>[display_only]" value="true" <?php echo ( $this->inputSettings( 'display_only' ) ) ? "checked" : ''; ?>>
							<label for="<?php echo $inputString; ?>[display_only]"></label>
						</span>
					</p>
					<p>
						<label for="<?php echo $inputString; ?>[hidden_default]">Default value:</label> 
						<input type="text" id="<?php echo $inputString; ?>[hidden_default]" name="<?php echo $inputString; ?>[hidden_default]" value="<?php echo $this->inputSettings( 'hidden_default' ); ?>">
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
		?>
		<span class="dummy-input">
			<label><?php echo $label; ?></label>
		</span> <?php
	}
	
	/*##############################################################*/
	/*#######################     PUBLIC   #########################*/
	/*##############################################################*/
	/* PUBLIC RENDER : used when creating form on the front-end */
	function publicRender( $form = null ){
		$settings = $this->input->settings;
		
		?>
			<input type="hidden" name="<?php echo afFieldName($this->fieldname); ?>" id="<?php echo $this->fieldname;?>" value="<?php echo $settings->hidden_default; ?>">
		<?php
	}
	
	
}