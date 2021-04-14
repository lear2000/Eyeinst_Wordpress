<?php  
namespace aform\fields;

class selectbox extends \aform\core\field {

	var $inputvalues;
	
	function __construct(){
		$this->fieldsettings = new \StdClass;
		$this->fieldsettings->name  = 'selectbox';
		$this->fieldsettings->label = 'Select Box';
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
		
		echo $label;
		/*
		?>
		<span class="dummy-input">
			<select>
				<option>Select Box</option>
			</select>
		</span> 
		<?php
		*/
	}

	/*##############################################################*/
	/*#######################     PUBLIC   #########################*/
	/*##############################################################*/
	/* PUBLIC RENDER : called when creating Form on the front-end */
	function publicRender( $form = null ){
		
		$selected = ( isset($this->returnValue) ) ? $this->returnValue : null ;
		$count = 0;
		// echo '<pre>'; 
		// 	print_r($this->input->settings); 
		// echo '</pre>';
		?>
		<select name="<?php echo (afFieldName($this->fieldname)); ?>" class="" id="<?php echo ($this->fieldname); ?>" <?php $this->fieldRequired();?>>
			<?php 
			if($this->inputSettings( 'required' ) && !$this->inputSettings('ignore_first')):
				//check if required, if required we add an empty option
				echo "<option value=\"\">Select One</option>";
			endif;
			if(!empty($this->input->values)):
				foreach($this->input->values as $valueindex => $option):
				$selectOption = ($this->findValue( 'value', $option ));
				if(isset($this->input->settings->for_admin_email)):
					$selectOption = "{$valueindex}:{$selectOption}";
				endif;
				if($this->inputSettings('ignore_first') && $count == 0):
					$selectOption = "";
				endif;
				?>
					<option <?php selected( $selected , $this->findValue( 'value', $option ) );?> value="<?php echo $selectOption;?>"><?php echo ($this->findValue( 'display|value', $option ));?></option>
				<?php
				$count++;
				endforeach;
			endif;
			?>
		</select>
		<?php
	}


}