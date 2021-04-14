<?php  
namespace aform\fields;

class text extends \aform\core\field{
	var $inputValues;

	function __construct(){
		$this->fieldsettings = new \StdClass;
		$this->fieldsettings->name  = 'text';
		$this->fieldsettings->label = 'Text Only';
	}
	

	function renderField( $fieldData = null , $index = null){
		global $post,$post_id;		
		ob_start();
		$this->formfieldTop($index , $fieldData);

		$this->formFieldSetup( $index, $this->fieldsettings->label , $fieldData , array('field-label','field-name','field-required'));
		$fieldIdAsIndex = $fieldData->ID;
		$inputString = "aform-fields[$fieldIdAsIndex][input_settings]"; 
		?>
			<div class="only-text">
				<div>					
					<label for="html_tag_<?php echo $fieldIdAsIndex;?>">HTML Tag</label> 
					<select name="<?php echo $inputString; ?>[html_tag]" class="read-only-html-tag" id="html_tag_<?php echo $fieldIdAsIndex;?>">
						<?php $htmlTag = $this->inputSettings( 'html_tag' ); ?>
						<option <?php echo( $htmlTag == "p" )  ?   'selected' : '';?> value="p">&lt;p&gt;</option>
						<option <?php echo( $htmlTag == "h1" ) ?  'selected' : '';?>  value="h1">&lt;h1&gt;</option>
						<option <?php echo( $htmlTag == "h2" ) ?  'selected' : '';?>  value="h2">&lt;h2&gt;</option>
						<option <?php echo( $htmlTag == "h3" ) ?  'selected' : '';?>  value="h3">&lt;h3&gt;</option>
						<option <?php echo( $htmlTag == "h4" ) ?  'selected' : '';?>  value="h4">&lt;h4&gt;</option>
						<option <?php echo( $htmlTag == "h5" ) ?  'selected' : '';?>  value="h5">&lt;h5&gt;</option>
						<option <?php echo( $htmlTag == "div" ) ?  'selected' : '';?>  value="div">&lt;div&gt;</option>
						<option <?php echo( $htmlTag == "fieldset_top" ) ?  'selected' : '';?>  value="fieldset_top">&lt;fieldset&gt;</option>
						<option <?php echo( $htmlTag == "fieldset_bot" ) ?  'selected' : '';?>  value="fieldset_bot">&lt;/fieldset&gt;</option>
					</select>
					<?php $htmlText = stripslashes( $this->inputSettings( 'text' ) ); ?>
					<textarea style="margin-top:10px;display:block;padding:7px;" name="<?php echo $inputString; ?>[text]" id="" class="widefat" rows="2"><?php echo $htmlText; ?></textarea>
				</div>				
			</div>
		<?php $this->formfieldAdvancedToggle(); ?>
		<div class="field-create-advanced field-tab">
			<?php 
				$this->standardAdvSettings($index , $fieldData , array('placeholder','mml_mapping','hide_label','reverse_display','for_admin_email','send_to_CM','field_description')); 
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
			<p>Sample Text</p>
		</span> 
		<?php
		*/
		echo "Layout : Text";
	}


	/*##############################################################*/
	/*#######################     PUBLIC   #########################*/
	/*##############################################################*/
	/* PUBLIC RENDER : used when creating form on the front-end */
	function publicRender( $form = null ){
		$htmlTag 	= $this->input->settings->html_tag;
		$text 		= stripslashes($this->input->settings->text);
		
		if(!in_array( $htmlTag , array('fieldset_top','fieldset_bot'))):
			$output 	= "<{$htmlTag}>{$text}</{$htmlTag}>"; 
		endif;
		if($htmlTag == 'fieldset_top'):
			$output = "{$text}";
		endif;
		if($htmlTag == 'fieldset_bot'):
			$output = "{$text}";
		endif;
		echo $output;

	}
	

}