<?php  
namespace aform\fields;

class checkboxgroup extends \aform\core\field {
	
	var $inputvalues;
	
	function __construct(){
		$this->fieldsettings = new \StdClass;
		$this->fieldsettings->name  = 'checkboxgroup';
		$this->fieldsettings->label = 'Checkbox Group';
	}
	
	function renderField( $fieldData = null , $index = null){
		global $post,$post_id;		
		ob_start();
		$fieldIdAsIndex = $fieldData->ID;
		$this->formfieldTop( $index , $fieldData );
		$this->formFieldSetup( $index, $this->fieldsettings->label , $fieldData );
		
		//custom field layout here	
		\aform\core\view::fieldsTmpl('choices-cond-conf' , $this , $fieldData , $index );
		
		$this->formfieldBottom();
		$output = ob_get_contents();
		ob_end_clean();
		return $output;
	}
	
	function metaboxRender($label) {
		/*
		?>
		<span class="dummy-input"><?php echo $label; ?><br />
			<input type="checkbox" /> A <input type="checkbox" /> B
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
		
		$checkedArr = ( isset($this->returnValue) ) ? $this->returnValue : null ;

		if(!empty($this->input->values)):
			$count = 0;
			foreach( $this->input->values as $valueindex => $option):
			$checked = (!empty($checkedArr) && in_array( $option->value , $checkedArr )) ? 'checked="checked"': null;
			$choiceForId = "choice-{$this->fieldID}-{$count}";
			$selectOption = $option->value;
			if(isset($this->input->settings->for_admin_email)):
				$selectOption = "{$valueindex}:{$selectOption}";
			endif;
			?>
				<div id="<?php echo ("field-{$choiceForId}");?>"><input type="checkbox" <?php echo ($checked);?> value="<?php echo ($selectOption);?>" <?php echo (( $this->inputSettings( 'max_selected' ) ) ? 'class="max-select-set"' : '');?> id="<?php echo ($choiceForId);?>" name="<?php echo (afFieldName($this->fieldname));?>[<?php //echo $count;?>]" <?php echo (( $this->inputSettings( 'max_selected' ) ) ? 'data-max-selectable="' . $this->inputSettings( 'max_selected') . '"' : ''); ?>><label for="<?php echo ($choiceForId);?>"><?php echo ($option->display);?></label></div>
			<?php
			$count++;
			endforeach;
		endif;;
	}
	
	function getRules() {
		$rules = array();
		
		if( $this->inputSettings( 'max_selected' ) ):
			$rules['maxlength'] = $this->inputSettings( 'max_selected' );
		endif; 
		
		return ( !empty( $rules ) ) ?  $rules  : false;
	}	
	
}