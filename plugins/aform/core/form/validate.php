<?php
namespace aform\core\form;

class validate{
	private static $instance;

	private $fields;
	private $formInstance;
	private $invalidFields = array();
	private $emailfields = array();
	public static function get_instance($handler = null){

		 if ( empty( static::$instance ) ):
			if (null == $handler):
				return null;
			endif;

            static::$instance = new self();
            static::$instance->formInstance = $handler->getProperty('formInstance');
          	static::$instance->setup();
          	static::$instance->run( $handler );

        elseif (null != $handler):
			return null;
		endif;

		return static::$instance;
	}
	public function getProperty($prop){ 
	// allows us to access private properties(variables) when $this is passed to another class. 
		return $this->$prop;
	}
	public function getInvalidFields(){
		return $this->invalidFields || array();
	}


	private function setup( ){
		/* SAVE FIELDS TO this class and check if admin or recipient fields are present */
		$this->fields = $this->formInstance->form->fields;
		
		foreach($this->fields as $fieldId => $object):
			if(isset($object->input->settings->html5) && $object->input->settings->html5_type == 'email' && $object->is_required ):
				$this->emailfields[] = $fieldId;
			endif;
			if(isset($object->input->settings->primary_email_recipient) && $object->is_required):
				$this->emailfields[] = $fieldId;
			endif;
			if(isset($object->input->settings->for_admin_email)):
				$this->emailfields[] = $fieldId;
			endif;
			
		endforeach;

	}
	private function getBlockedDomains(){
		$optionSetings = get_option('_aform_settings',array());
		$blockdomains = afGetAOV( $optionSetings , 'domain-block' , '');
		if(!empty($blockdomains)):
			$blockdomains = preg_replace('/\s+/', '', $blockdomains);
			$blockdomains = explode(',',$blockdomains);
		else:
			$blockdomains = array();
		endif;
		return $blockdomains;
	}
	private function run( $handler = null ){
		/* CHECK FIELDS , validate email fields */
		$fields = $this->fields;
		$postedData = $handler->getProperty('postedFormData');
		$ajax = (isset($postedData['ajax'])) ? $postedData['ajax'] : false;
		$postDataKeys = array();
		if($ajax):
			$postDataKeys = array_keys($postedData);
		endif;

		$blockdomains = $this->getBlockedDomains();

		$alreadychecked = array();
		foreach($fields as $index => $field):
			//check emails
			if( in_array( $index , $this->emailfields) ):
				//if if any of these types then it will most def be an array				
				if(in_array($field->fieldsettings->name , array('checkboxgroup','selectbox' , 'radiobuttons' ) ) ):
					$tempData = $field->postedFormData;
					if(!empty($tempData)):
						$tempData = explode(':' , $field->postedFormData);
						$tempData = $tempData[1];
						$tempData = preg_replace('/\s+/', '', $tempData);
						$tempData = explode(',',$tempData);
						if(is_array($tempData))://of course its an array
							foreach($tempData as $tempDataItem):
								if(!filter_var( $tempDataItem , FILTER_VALIDATE_EMAIL)):
									$this->invalidFields[$index] = 'Invalid Email';
									break;
								endif;
							endforeach;
						endif;
					endif;
				elseif( 
					!filter_var( $field->postedFormData , FILTER_VALIDATE_EMAIL) 
					&& !empty($field->postedFormData)
					&& !preg_match('/\,/i' , $field->postedFormData )
					):
					$this->invalidFields[$index] = 'Invalid Email';// get from global options (settings page)
					if($field->fieldsettings->name == 'selectbox'):
						$this->invalidFields[$index] = 'Invalid Email : Select an Item';// get from global options (settings page)
					endif;
					$alreadychecked[] = $index;
				elseif( 
					!filter_var( $field->postedFormData , FILTER_VALIDATE_EMAIL) 
					&& !empty($field->postedFormData)
					&& preg_match('/\,/i' , $field->postedFormData )
					):
					$tempData = preg_replace('/\s+/', '', $field->postedFormData);
					$tempData = explode(',',$tempData);
					foreach($tempData as $tempDataItem):
						if(!filter_var( $tempDataItem , FILTER_VALIDATE_EMAIL)):
							$this->invalidFields[$index] = 'Invalid Email';
							break;
						endif;
					endforeach;
					$alreadychecked[] = $index;
				endif;
				if(!in_array($field->fieldsettings->name , array('checkboxgroup','selectbox' , 'radiobuttons' ) ) && !empty($blockdomains)):
					foreach($blockdomains as $domain):
						if(preg_match('/'.$domain.'/i' , $field->postedFormData)):
							$this->invalidFields[$index] = 'Invalid Email';
							break;
						endif;
					endforeach;				
				endif;
			endif;
			if( (isset($field->is_required) && $field->is_required == true ) && !in_array( $index , $alreadychecked)):
				if( empty($field->postedFormData) || ctype_space( $field->postedFormData )):
					if( is_numeric($field->postedFormData ) ): continue; endif;//if the value is zero then treat as valid value
					if(ctype_space( $field->postedFormData)):
						$this->invalidFields['empty__fields'] = 'spaces_are_not_value';
					endif;
					if( $ajax == true && $field->fieldsettings->name == 'file' ):
						/* DONT DO ANYTHING */
						//$this->invalidFields[$index] = 'NOT USED';
					else:
						$this->invalidFields[$index] = 'This Field is Required';// get from global options (settings page)
					endif;
				endif;
			endif;
			if($field->fieldsettings->name == 'file'):
				if(is_array($field->postedFormData)):
					$error = array();
					foreach($field->postedFormData as $file):
						$check = fileupload::checkFile($file , $field->input->settings);
						if(!empty($check)):
							$error[] = $check;
						endif; 
					endforeach;
					if(!empty($error)):
						$this->invalidFields[$index] = $error;
					endif;
				endif;
			endif;
		endforeach;
	}
}
