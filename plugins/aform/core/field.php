<?php  
namespace aform\core;
class field{
	
	function __construct(){

	}

	function listFields(){
		$path = aFormSettings('viewsdir') . 'fields';
		$fieldsArr = array();
		$files = glob($path . "/*.php" , GLOB_BRACE );
		foreach ($files as $file) {
			$classname = basename($file , '.php');
			if ( $classname != 'submitbutton' ):
				$class = "aform\\fields\\{$classname}";
				$class = new $class;
				$fieldsArr[$class->fieldsettings->name] = $class->fieldsettings;
			endif;
			
		}
		return $fieldsArr;
	}

	function fieldDataSetup( $fieldData = null , $public = false ) {
		
		$this->input = new \StdClass;
		$inputValues = self::decodeInputJSON( (isset( $fieldData->input_values ) ) ? $fieldData->input_values : null );
		$inputSettings = self::decodeInputJSON( ( isset( $fieldData->input_settings ) ) ? $fieldData->input_settings : null );
		$this->fieldID = ( isset( $fieldData->ID ) ) ? $fieldData->ID : null ;
		$this->input->values = $inputValues;
		$this->input->settings = $inputSettings;
		
		//called only when rendering a form
		if( $public == true ):
			
			$fieldname = (isset($this->input->settings->field_name)) ? $this->input->settings->field_name : (isset($this->input->settings->display_name) ? $this->input->settings->display_name : null );
			$fieldname = (!empty($fieldname)) ? $fieldname : "field-{$this->fieldID}";
			$fieldname = sanitize_title($fieldname);
			
			$displayName = (isset($this->input->settings->display_name)) ? $this->input->settings->display_name : (isset($this->input->settings->field_name) ? $this->input->settings->field_name : null );
			$this->displayname = $displayName;
			
			if(!preg_match('/field-/i', $fieldname)):
				$this->fieldname = $fieldname."-{$this->fieldID}";	
			else:
				$this->fieldname = $fieldname;
			endif;

			$this->is_required = false;
			
			
			if( isset( $this->input->settings->required ) ):
				$this->is_required = true;
			endif;
			
			$this->postedFormData = array();

		endif;

		return $this;
	}
	
	function decodeInputJSON($data = null){
		if(!empty($data)):
			return json_decode($data);
		endif;
		return array();
	}

	function inputValues($value){
		if(isset($this->input->values->$value)):
			return $this->input->values->$value;
		endif;
		return false;
	}
	
	function inputSettings($setting , $default = false) {
		if( isset( $this->input->settings->$setting) ):
			return $this->input->settings->$setting;
		endif;
		return $default;
	}
	function advancedSettingsBottom(){
		ob_start();
		
		?>
			<div class="close-field-wrapper"><a href="" class="close-field button">Close Field</a></div>
		
		<?php
		/*
			Adding Hook
		*/
		$html = ob_get_clean();
		echo $html;
	}
	function formFieldSetup( $index, $label, $fieldData = null , $exlField = array() ) {
		global $post;
		$fieldIdAsIndex = (!empty($fieldData)) ? $fieldData->ID : $index;
		$display_name = $this->inputSettings( 'display_name' );
		$display_name = htmlentities($display_name);
		$field_name = $this->inputSettings( 'field_name' );
		$valueMatch = ( strtolower(str_ireplace(' ', '-', $display_name)) == $field_name ) ? true : false;
		?>
			<div class="field-create-basic value-bind-parent">

				<input type="hidden" name="aform-fields[<?php echo $fieldIdAsIndex;?>][input_type]" value="<?php echo $this->fieldsettings->name; ?>">
				<input type="hidden" class="aform-field-id" name="aform-fields[<?php echo $fieldIdAsIndex;?>][ID]" value="<?php echo $this->fieldID;?>">
				<input type="hidden" name="aform-fields[<?php echo $fieldIdAsIndex;?>][form_id]" value="<?php echo $post->ID; ?>">
				<input type="hidden" class="aform-input-order" name="aform-fields[<?php echo $fieldIdAsIndex;?>][input_order]" value="<?php echo $index;?>">
				<input type="hidden" name="aform-fields[<?php echo $fieldIdAsIndex;?>][input_settings]" value="">
				
				<div class="handle-div"><span class="handle"><i class="fa fa-bars"></i></span></div>
				
				<div class="field-type-label"><h3><?php echo $this->fieldsettings->label; ?></h3></div>
				
				<?php if(!in_array('field-label',$exlField)): ?>
					<div>					
						<label for="field-label">Field Label</label><br>
						<input 
						type="text" 
						value="<?php echo $display_name; ?>" 
						name="aform-fields[<?php echo $fieldIdAsIndex;?>][input_settings][display_name]" 
						class="form-control <?php echo ($valueMatch)  ? 'value-bind' : ''; ?>" 
						data-uglify="true" />
					</div>
				<?php endif; ?>
				<?php if(!in_array('field-name',$exlField)): ?>
					<div>
						<label for="field-name">Field Name</label><br>
						<input 
						type="text" 
						value="<?php echo $field_name; ?>" 
						name="aform-fields[<?php echo $fieldIdAsIndex; ?>][input_settings][field_name]" 
						class="form-control <?php echo( $valueMatch ) ? 'value-set' : ''; ?>" />
					</div>
				<?php endif; ?>
					
				<?php if(!in_array('field-required',$exlField)): ?>	
					<div>
						<label for="field-required[<?php echo $fieldIdAsIndex;?>]">Required</label>
						<span class="af-input-switch">
							<input type="checkbox" id="field-required[<?php echo $fieldIdAsIndex; ?>]" name="aform-fields[<?php echo $fieldIdAsIndex; ?>][input_settings][required]" value="true" <?php echo ($this->inputSettings( 'required' ) == true ) ? 'checked' : ''; ?>>
							<label for="field-required[<?php echo $fieldIdAsIndex;?>]"></label>
						</span>
					</div>
				<?php else: ?>
					<div></div><!-- spacer -->
				<?php endif; ?>
				
				<div class="delete-field"><a title="Delete Field"><span class="dash fa fa-trash-o"><b>Delete Field</b></span></a></div>
			</div>
	<?php 
	}
	
	/* 
		stays the same 
	*/
	function formfieldTop($index , $fieldData = null ){
		$fieldID = (!empty($fieldData)) ?  $fieldData->ID : $index ;
		$inputType = (isset($fieldData->input_type)) ? $fieldData->input_type : null;
		ob_start();
		
		?>
			<div id="field--<?php echo $fieldID;?>" data-input-type="<?php echo $inputType;?>" class="form--field fldtype-<?php echo $inputType;?> rm-droppable-item" data-index="<?php echo $index;?>"><div class="form--field-inside">
		<?php
		$output = ob_get_clean();
		echo $output;
	}

	function formfieldBottom(){
		ob_start();
		?>
			<div class="field-extra-info-wrapper">
				<div class="field-extra-info">
					<div class="field-id-ref" data-field-ref-id="<?php echo $this->fieldID;?>">ID:<span><?php echo $this->fieldID;?></span></div>
					<div><a href="" class="duplicate-field"><small>Make Reusable Field</small></a></div>
				</div>
			</div>
		</div></div><!--end-of-field-<?php echo $this->fieldID;?>-->
		<?php
		$output = ob_get_clean();
		echo $output;
	}

	function formfieldAdvancedToggle(){
		ob_start();
		?>
			<div class="toggle-tab adv-toggle-tab"><h4><a title="Advanced Options"><span><i class="fa fa-cog" aria-hidden="true"></i></span></a></h4></div>
		<?php
		$html = ob_get_clean();
		echo $html;
	}
	
	
	function standardAdvSettings( $index , $fieldData = null , $excludeSetting = array() ) {
		$fieldIdAsIndex = (!empty($fieldData)) ? $fieldData->ID : $index;
		$inputString = "aform-fields[$fieldIdAsIndex][input_settings]"; ?>
		<div class="settings-holder">
			<div>
				<?php 
					$self = $this;
					//campaign monitor has been removed. Will be added back as a module
					$settingsOrder = array('css_class','placeholder','field_description','hide_label','reverse_display','for_admin_email');
					//$settingsOrder = array('css_class','field_description','hide_label','reverse_display','for_admin_email','send_to_CM');
					foreach($settingsOrder as $settingsOrderItem):
						if(!in_array( $settingsOrderItem , $excludeSetting )):
							$filepath = "advanced/{$settingsOrderItem}";
							\aform\core\view::fieldsTmpl(
								$filepath , 
								$self , 
								$fieldData , 
								$index , 
								function($file) use ($fieldIdAsIndex,$inputString,$self){ include $file; } );
						endif;
					endforeach; 
					/* */
					$fieldObject = $this;
					do_action( 'sf_adv_settings', $fieldname = $inputString , $fieldsettings = $this->input->settings , $fieldclass = $fieldObject ); 
				?>
			</div>
		</div><!-- .settings-holder -->	
		<?php 
	}

	function renderFieldValue( $index = null , $count = null , $value = null , $display = null , $option = null , $fieldData = null , $fieldOptions = null ) {
		$inputString = "aform-fields[$index][input_values]";
		$valueMatch = ( $value == $display ) ? true : false;
		ob_start();
		?>
		<?php 
		
		$labelOptional = 'optional';
		if(isset($this->input->settings->for_admin_email) && isset($this->input->settings->use_choicelabel_as_value)):
			$labelOptional = 'required';
		elseif(!empty($option) && $option == 'label_is_required'):
			$labelOptional = 'required';
		endif;
		?>
		<div class="value-bind-parent" data-choiceparent="<?php echo $index;?>" data-currentindex="<?php echo $count;?>"><span class="handle"><i class="fa fa-bars"></i></span>
			<label for="<?php echo $inputString . "[" . $count. "]"; ?>[value]">Value</label> 
			<input type="text" name="<?php echo $inputString . "[" . $count. "]"; ?>[value]" id="<?php echo $inputString . "[" . $count. "]"; ?>[value]" value="<?php echo htmlentities($value); ?>" class="form-control-inline <?php echo ( $valueMatch ) ? 'value-bind' : '';?>" />
			
			<label for="<?php echo $inputString . "[" . $count. "]"; ?>[display]">Label<small class="is-label-optional" style="display:block;"><?php echo ($labelOptional);?></small></label> 
			<input type="text" name="<?php echo $inputString . "[" . $count. "]"; ?>[display]" id="<?php echo $inputString . "[" . $count. "]"; ?>[display]" value="<?php echo htmlentities($display); ?>" class="form-control-inline <?php echo ( $valueMatch ) ? 'value-set is-label-optional-input' : 'is-label-optional-input';?>" <?php echo (( $labelOptional == 'required' )? 'required' :'');?>/>
			
			<a href="#" <?php echo ($option == 'conditional_confirmation') ? '' : 'style="display:none;"';?> class="conditional-confirmation-launch"><span class="dashicons dashicons-text"></span></a><!-- will launch a wysiwyg-->
			
			
			<div style="display:none;">
				
				<textarea name="<?php echo $inputString . "[" . $count. "]"; ?>[message]"><?php echo (!empty($fieldData) && property_exists($fieldData, 'message') ) ? $fieldData->message : '';?></textarea>
				<input type="hidden" class="cond-conf-subject" name="<?php echo $inputString . "[" . $count. "]"; ?>[subject]" value="<?php echo (!empty($fieldData) && property_exists($fieldData, 'subject')) ? $fieldData->subject : '';?>">
			</div>
			
			<span class="delete-value"><i class="dashicons dashicons-trash"></i></span><?/* deletes field*/?>

			<?php if(isset( $fieldOptions->input_type) && $fieldOptions->input_type == 'file'): ?>
				<div class="after-file-input">
					<?php  
						$fileinput_after = (!empty($fieldData) && property_exists($fieldData, 'fileinput_after') ) ? $fieldData->fileinput_after : '';
					?>
					<span>After File Input</span>
					<input placeholder="text/html placed after input:file" type="text" id="<?php echo $inputString . "[" . $count. "]"; ?>[fileinput_after]" name="<?php echo $inputString . "[" . $count. "]"; ?>[fileinput_after]" value="<?php echo htmlentities($fileinput_after);?>" />
				</div>
			<?php endif; ?>

		</div>

		<?php 
		$html = ob_get_contents();
		ob_end_clean();
		echo $html;
	}
	
	//Renders the field in the drag-n-drop interface. Should be overwritten in individual class files, just provided as a default
	function metaboxRender($label) { 
		echo $label;
	}

	/*##############################################################*/
	/*#######################     PUBLIC   #########################*/
	/*##############################################################*/
	/* PUBLIC RENDER : used when creating form on the front-end */
	function fieldRequired(){		
		echo ( isset($this->input->settings->required) && $this->input->settings->required == true ) ? 'required' : null ;
	}
	function publicRender( $form = null ){
		echo ' -- ' . $this->fieldsettings->name.' -- ';
	}


	function findValue( $find = null , $subject = null){ # loop till you find value , subject must be object
		$myArray = explode( '|', $find );
		foreach($myArray as $val):
			if(!isset($subject->$val)) continue;
			if(empty($subject->$val)) continue;
			return $subject->$val;
		endforeach;
		return null;
	}


}