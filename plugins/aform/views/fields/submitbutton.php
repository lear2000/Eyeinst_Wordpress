<?php  
namespace aform\fields;

class submitbutton extends \aform\core\field {
	
	var $inputvalues;
	
	function __construct(){
		$this->fieldsettings = new \StdClass;
		$this->fieldsettings->name  = 'submitbutton';
		$this->fieldsettings->label = 'Submit Button';
	}
	
	function renderField( $fieldData = null , $index = null){
		global $post,$post_id;
		ob_start(); ?>
		<?php
			$fieldIdAsIndex = (!empty($fieldData->ID)) ? $fieldData->ID : $index;
		?>
		<div class="form--field submit-field" data-index="<?php echo $index; ?>" id="submitField">
			<div class="field-create">
				<div class="field-create-basic">
					<input type="hidden" name="aform-fields[<?php echo $fieldIdAsIndex;?>][input_type]" value="<?php echo $this->fieldsettings->name; ?>">
					<input type="hidden" class="aform-field-id" name="aform-fields[<?php echo $fieldIdAsIndex;?>][ID]" value="<?php echo $this->fieldID;?>">
					<input type="hidden" name="aform-fields[<?php echo $fieldIdAsIndex;?>][form_id]" value="<?php echo $post->ID; ?>">
					<input type="hidden" class="aform-input-order" name="aform-fields[<?php echo $fieldIdAsIndex;?>][input_order]" value="<?php echo $index;?>">
					<input type="hidden" name="aform-fields[<?php echo $fieldIdAsIndex;?>][input_settings]" value="">

					<div class="field-type-label"><h3>Submit Button</h3></div>

					<div>
						<label>Submit button text:</label><br>
						<input name="aform-fields[<?php echo $fieldIdAsIndex; ?>][input_settings][submit_text]" type="text" value="<?php echo ( $this->inputSettings( 'submit_text' ) ) ? : 'Submit';?>" />
					</div>
					
					<div>
						<label>Custom Class:</label><br>
						<input name="aform-fields[<?php echo $fieldIdAsIndex; ?>][input_settings][submit_class]" type="text" value="<?php echo $this->inputSettings( 'submit_class' ); ?>" />
					</div>

				</div> <!-- .field-create-basic -->
			</div> <!-- .field-create -->
		</div>
		
		<?php		
		$output = ob_get_contents();
		ob_end_clean();
		return $output;
	}
	
	
	/*##############################################################*/
	/*#######################     PUBLIC   #########################*/
	/*##############################################################*/
	/* PUBLIC RENDER : used when creating form on the front-end */
	function publicRender( $form = null ){
		$settings = $this->input->settings;
		
		?>
		
			<div class="stopyenoh robotic" id="pot">
				 <!-- The following fields are for robots only, invisible to humans: -->
				<label>do or do not , there is no try</label>
				<input type="text" name="the-fax-number" value="" tabindex="-1" autocomplete="off"><br>
				<input type="checkbox" name="contact_by_fax" value="1" tabindex="-1" autocomplete="off">
			</div> <!-- .stopyenoh -->
		
			<input class="submit-btn <?php echo $this->inputSettings('submit_class'); ?>" type="submit" id="<?php echo $this->fieldname;?>" name="<?php echo afFieldName($this->fieldname); ?>" value="<?php echo $settings->submit_text; ?>" >
		<?php
	}
	
	
}