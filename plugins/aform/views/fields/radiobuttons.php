<?php  
namespace aform\fields;

class radiobuttons extends \aform\core\field{
	
	function __construct(){
		$this->fieldsettings = new \StdClass;
		$this->fieldsettings->name  = 'radiobuttons';
		$this->fieldsettings->label = 'Radio Buttons';
	}

	function renderField( $fieldData = null , $index = null){
		global $post,$post_id;		
		
		ob_start();
		$fieldIdAsIndex = $fieldData->ID;
		$this->formfieldTop( $index , $fieldData );
		$this->formFieldSetup( $index, $this->fieldsettings->label , $fieldData );

		//custom field layout here
		\aform\core\view::fieldsTmpl('choices' , $this , $fieldData , $index );
		$this->formfieldBottom();
		$output = ob_get_contents();
		ob_end_clean();
		
		return $output;
	}
	
	
	function metaboxRender($label) {
		
		echo $label;
		
		/*
		?><span class="dummy-input">
			<?php echo $label; ?><br />
			<input type="radio" name="dummy" value="yes">Yes <input type="radio" name="dummy" value="no">No
		</span><?php
		*/
		
	}
	
	/*##############################################################*/
	/*#######################     PUBLIC   #########################*/
	/*##############################################################*/
	/* PUBLIC RENDER : used when creating form on the front-end */
	function publicRender( $form = null ){
		if(!empty($this->input->values)):
			$count = 0;
			foreach( $this->input->values as $valueindex => $option):
			$choiceForId = "choice-{$this->fieldID}-{$count}";
			$selectOption = $option->value;
			if(isset($this->input->settings->for_admin_email)):
				$selectOption = "{$valueindex}:{$selectOption}";
			endif;
			?>
				<div id="<?php echo ("field-{$choiceForId}");?>"><input type="radio" value="<?php echo ($selectOption); ?>" name="<?php echo (afFieldName($this->fieldname));?>" id="<?php echo ($choiceForId);?>" <?php $this->fieldRequired();?>><label for="<?php echo ($choiceForId);?>"><?php echo ($option->display);?></label></div>
			<?php
			$count++;
			endforeach;
		endif;;
	}
	
}
