<?php  
namespace aform\fields;

class singlelinetext extends \aform\core\field{
	var $inputValues;

	function __construct(){
		$this->fieldsettings = new \StdClass;
		$this->fieldsettings->name  = 'singlelinetext';
		$this->fieldsettings->label = 'Single Line Text';
	}
	
	function renderField( $fieldData = null , $index = null){
		global $post,$post_id;		
		ob_start();
		$fieldIdAsIndex = $fieldData->ID;
		$this->formfieldTop($index , $fieldData);
		$this->formFieldSetup( $index, $this->fieldsettings->label , $fieldData);
			
		//custom field layout here		
		?>

		<?php $this->formfieldAdvancedToggle(); ?>

		<div class="field-create-advanced field-tab" id="fieldCreateAdvanced">
			
			<?php 
				$this->standardAdvSettings($index , $fieldData , array('for_admin_email') ); 
				$inputString = "aform-fields[$fieldIdAsIndex][input_settings]"; 
			?>
			<div class="settings-holder">
				<div>
				<p>
					<label for="<?php echo $inputString; ?>[use_as_confirmation_subject]">Use value as email confirmation subject</label>
					<span class="af-input-switch">
						<input id="<?php echo $inputString; ?>[use_as_confirmation_subject]" name="<?php echo $inputString; ?>[use_as_confirmation_subject]" type="checkbox" value="true" <?php echo ( $this->inputSettings( 'use_as_confirmation_subject' ) == true ) ? 'checked' : ''; ?>>
						<label for="<?php echo $inputString; ?>[use_as_confirmation_subject]"></label>
					</span>
				</p>
				
				<p>
					<label for="<?php echo $inputString; ?>[placeholder]">Use label as placeholder text</label> 
					<span class="af-input-switch">
						<input id="<?php echo $inputString; ?>[placeholder]" name="<?php echo $inputString; ?>[placeholder]" type="checkbox" value="true" <?php echo ( $this->inputSettings( 'placeholder' ) == true ) ? 'checked' : ''; ?>>
						<label for="<?php echo $inputString; ?>[placeholder]"></label>
					</span>
				</p>
				<div class="af-toggle-grouped">
					<p>
						<label for="<?php echo $inputString; ?>[html5]">Use an HTML5 input type</label>
						<span class="af-input-switch">
							<input class="enabler" data-target="html5-field" id="<?php echo $inputString; ?>[html5]" name="<?php echo $inputString; ?>[html5]" type="checkbox" value="true" <?php echo ( $this->inputSettings( 'html5' ) == true ) ? 'checked' : ''; ?>>
							<label for="<?php echo $inputString; ?>[html5]"></label>
						</span>
					</p>

					<p class="html5-field" <?php echo ( $this->inputSettings( 'html5' ) == false ) ? 'style="display: none;"' : ''; ?>><label for="<?php echo $inputString; ?>[html5_type]">Select type:</label>
					<select id="<?php echo $inputString; ?>[html5_type]" class="html5_type_options" name="<?php echo $inputString; ?>[html5_type]">
						<?php $html5val = $this->inputSettings( 'html5_type' ); ?>
						<option value=""></option>
						<option value="email" <?php echo( $html5val == "email" ) ? 'selected' : '';?>>Email</option>
						<option value="tel" <?php echo( $html5val == "tel" ) ? 'selected' : '';?>>Phone</option>
						<option value="url" <?php echo( $html5val == "url" ) ? 'selected' : '';?>>URL</option>
						<option value="date" <?php echo( $html5val == "date" ) ? 'selected' : '';?>>Date</option>
						<option value="range" <?php echo( $html5val == "range" ) ? 'selected' : '';?>>Range</option>
					</select>
					</p>
					<p class="dateValidationFormat"<?php echo (!empty($html5val) && $html5val == "date") ? '': ' style="display:none;"';?>>
						<?php $dateValidationFormat = $this->inputSettings( 'date_validation_format' ); ?>
						<label for="<?php echo $inputString; ?>[date_validation_format]">Select Validation Format</label>
						<select id="<?php echo $inputString; ?>[date_validation_format]" name="<?php echo $inputString; ?>[date_validation_format]">
							<option value=""></option>
							<option value="mm/dd/yyyy"<?php echo( $dateValidationFormat == "mm/dd/yyyy" ) ? ' selected' : '';?>>mm/dd/yyyy</option>
							<option value="dd/mm/yyyy"<?php echo( $dateValidationFormat == "dd/mm/yyyy" ) ? ' selected' : '';?>>dd/mm/yyyy</option>
							<option value="yyyy/mm/dd"<?php echo( $dateValidationFormat == "yyyy/mm/dd" ) ? ' selected' : '';?>>yyyy/mm/dd</option>
						</select>
					</p>
					<p class="html5RangeOptions"<?php echo (!empty($html5val) && $html5val == "range") ? '': ' style="display:none;"';?>>
						<span class="settings-holder" style="margin-bottom:3px;">
								<?php $range_min = $this->inputSettings( 'range_min' , 0 ); ?>
							<label for="<?php echo $inputString; ?>[range_min]">Range Min</label>
							<input placeholder="0" style="text-align:center;" type="number" value="<?php echo $range_min; ?>" id="<?php echo $inputString; ?>[range_min]" name="<?php echo $inputString; ?>[range_min]">
						</span>
						<span class="settings-holder" style="margin-bottom:3px;">
								<?php $range_max = $this->inputSettings( 'range_max' , 100 ); ?>
							<label for="<?php echo $inputString; ?>[range_max]">Range Max</label>
							<input placeholder="100" style="text-align:center;" type="number" value="<?php echo $range_max; ?>" id="<?php echo $inputString; ?>[range_max]" name="<?php echo $inputString; ?>[range_max]">
						</span>
						<span class="settings-holder" style="margin-bottom:3px;">
								<?php $range_step = $this->inputSettings( 'range_step' , 0 ); ?>
							<label for="<?php echo $inputString; ?>[range_step]">Range Step</label>
							<input style="text-align:center;" type="number" value="<?php echo $range_step; ?>" id="<?php echo $inputString; ?>[range_step]" name="<?php echo $inputString; ?>[range_step]">
						</span>
					</p>	
					<p class="html5-field" <?php echo ( $this->inputSettings( 'html5' ) == false ) ? 'style="display: none;"' : ''; ?>>
						<label for="<?php echo $inputString; ?>[primary_email_recipient]">Enable Email Confirmation</label>
						<span class="af-input-switch">
							<input type="checkbox" id="<?php echo $inputString; ?>[primary_email_recipient]" name="<?php echo $inputString; ?>[primary_email_recipient]" value="true" <?php echo ( $this->inputSettings( 'primary_email_recipient' ) == true ) ? 'checked' : ''; ?>>
							<label for="<?php echo $inputString; ?>[primary_email_recipient]"></label>
						</span>
					</p>
				</div>
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
		/*
		?>
		<span class="dummy-input">
			<?php echo $label; ?>
			<input type="text" />
		</span> 
		<?php
		*/
		echo $label;
	}

	function fieldSettings(){

	}

	/*##############################################################*/
	/*#######################     PUBLIC   #########################*/
	/*##############################################################*/
	/* PUBLIC RENDER : used when creating form on the front-end */
	function publicRender( $form = null ){
		
		$inputType = ( ( isset($this->input->settings->html5) && $this->input->settings->html5) == true && $this->input->settings->html5_type != '' ) ? $this->input->settings->html5_type : 'text';
		
		$value = ( isset($this->returnValue) ) ? $this->returnValue : null ;	
		
		$dateValidation = '';
		$inputTypeClone = $inputType;
		if($inputType == 'date'):
			$dateValidation = $this->inputSettings( 'date_validation_format' );
			if(!empty($dateValidation)):
				$inputType = 'text';
			endif;
		elseif($inputType == 'range'):
			$rangeMin = $this->inputSettings( 'range_min' , 0);
			$rangeMax = $this->inputSettings( 'range_max' , 100);
			$rangeStep = $this->inputSettings( 'range_step', 0);
			if($value == null){ $value = 0; }
		endif;
		?>
			
			<?php if($inputType == 'range'): echo "<div class=\"{$inputTypeClone}-inputtype-wrap\">"; endif;?>
			<?php if($inputType == 'range'): echo "<div class=\"{$inputTypeClone}-inputtype-currentvalue\"><span>{$value}</span></div>"; endif;?>
			<?php if($inputType == 'range'): echo "<div class=\"{$inputTypeClone}-inputtype-inner\">"; endif;?>
			<?php if($inputType == 'range'): echo "<span class=\"{$inputTypeClone}-inputtype-min\">{$rangeMin}</span>"; endif;?>
			<input 
			type="<?php echo $inputType; ?>" 
			id="<?php echo $this->fieldname; ?>" 
			name="<?php echo afFieldName($this->fieldname);?>" 
			value="<?php echo $value;?>"
			<?php
			if($inputTypeClone == 'date' && !empty($dateValidation)):
				echo "data-use-format=\"{$dateValidation}\"";
			endif;
			if($inputTypeClone == 'range'):
				echo "style=\"padding-left:0;padding-right:0;\" ";
				echo "min=\"{$rangeMin}\" ";
				echo "max=\"{$rangeMax}\" ";
				if($rangeStep != 0){
					echo "step=\"{$rangeStep}\" ";
				}
			endif;	
			?> 
			class="<?php echo ( (!empty($dateValidation)) ? "{$inputTypeClone}-inputtype-format" : "{$inputTypeClone}-inputtype" );?>"
			<?php $this->fieldRequired();?> 
			<?php 
			if( ( $this->inputSettings( 'input_placeholder' ) && in_array($inputTypeClone , array('text','email','url','tel'))) && ( !$this->inputSettings( 'placeholder' ))):
				echo 'placeholder="'.$this->inputSettings( 'input_placeholder' ).'"';
			else:
				echo ( $this->inputSettings( 'placeholder' ) || ($inputTypeClone == 'date' && !empty($dateValidation) ) ) ? 'placeholder="' . ( ( $inputTypeClone == 'date' ) ? $dateValidation : $this->inputSettings( 'display_name' ) ) . '" data-placeholder-text="' . ( ( $inputTypeClone == 'date' ) ? $dateValidation : $this->inputSettings( 'display_name' ) ) . '"' : '';
			endif;
			?>> 
			<?php if($inputType == 'range'): echo "<span class=\"{$inputTypeClone}-inputtype-max\">{$rangeMax}</span>"; endif;?>
			<?php if($inputType == 'range'): echo "</div>"; endif;?>
			<?php if($inputType == 'range'): echo "</div>"; endif;?>

			<?php
	}
	
	function getRules() {
		
		$rules = array();
		if ( $this->inputSettings( 'html5' ) == true && $this->inputSettings( 'html5_type' ) != '' && $this->inputSettings( 'html5_type') == 'email' ):
			$rules['email'] = true;
		endif;
		$dateValidation = $this->inputSettings( 'date_validation_format' );
		if( $this->inputSettings( 'html5_type') == 'date' && !empty( $dateValidation ) ):
			$rules['datevalidation'] = $dateValidation;
		endif;
		
		return ( !empty( $rules ) ) ?  $rules  : false;
		
	}

}