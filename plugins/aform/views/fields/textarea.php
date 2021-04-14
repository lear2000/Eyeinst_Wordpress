<?php  
namespace aform\fields;
class textarea extends \aform\core\field{
	
	function __construct(){
		$this->fieldsettings = new \StdClass;
		$this->fieldsettings->name  = 'textarea';
		$this->fieldsettings->label = 'Textarea';	
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
				$this->standardAdvSettings($index , $fieldData , array('for_admin_email')); 
				$inputString = "aform-fields[$fieldIdAsIndex][input_settings]"; 
			?>	
			<div class="settings-holder">
				<div>
					<p>
						<label for="<?php echo $inputString; ?>[placeholder]">Use label as placeholder text</label> 
						<span class="af-input-switch">
							<input id="<?php echo $inputString; ?>[placeholder]" name="<?php echo $inputString; ?>[placeholder]" type="checkbox" value="true" <?php echo ( $this->inputSettings( 'placeholder' ) == true ) ? 'checked' : ''; ?>>
							<label for="<?php echo $inputString; ?>[placeholder]"></label>
						</span>
					</p>
				</div>
			</div>
			<?php $this->advancedSettingsBottom(); ?>
		</div> <!-- .field-create-advanced-->
		<?php $this->formfieldBottom();
		$output = ob_get_contents();
		ob_end_clean();
		return $output;
	}
	
	
	function metaboxRender($label) {
		/*
		?>
		<span class="dummy-input">
			<?php echo $label; ?>
			<textarea rows="2"></textarea>
		</span>
		<?php 
		*/
		echo $label;

	}


	/*##############################################################*/
	/*#######################     PUBLIC   #########################*/
	/*##############################################################*/
	/* PUBLIC RENDER : used when creating form on the front-end */

	function publicRender( $form = null ){
		?>
			<textarea name="<?php echo afFieldName($this->fieldname); ?>" id="<?php echo $this->fieldname;?>" cols="30" rows="10" <?php $this->fieldRequired();?> 
			<?php 
			if( ($this->inputSettings( 'input_placeholder' ) && !empty($this->inputSettings( 'input_placeholder' ))) && !$this->inputSettings( 'placeholder' )):
				echo 'placeholder="'.$this->inputSettings( 'input_placeholder' ).'"';
			else:
				echo ( $this->inputSettings( 'placeholder' ) ) ? 'placeholder="' . $this->inputSettings( 'display_name' ) . '" data-placeholder-text="' . $this->inputSettings( 'display_name') . '"' : '';
			endif;
			?>></textarea>
		<?php
	}

}