<?php
namespace aform\core\form;
class helper{

	/**
	* clean the submitted data
	*/
	public static function sanitizePost($value=null){
		$self = __CLASS__;
		if ( is_array( $value ) ) {
			$value = array_map( array( $self , 'sanitizePost' ), $value );
		} elseif ( is_string( $value ) ) {
			$value = wp_check_invalid_utf8( $value );
			// script tag will be stripped
			$allowedtags = array(
			    //formatting
			    'strong' => array(),
			    'em'     => array(),
			    'b'      => array(),
			    'i'      => array(),

			    //links
			    'a'     => array(
			        'href' => array()
			    )
			);
			$value = wp_kses($value , $allowedtags);
			$value = wp_kses_no_null( $value );
		}
		return $value;
	}
	
	public static function fieldHaystack($fields = null , $field = null){
		if( empty($fields) && empty($field)) return false;
		if(isset($fields[$field])):
			return true;
		endif;
		return false;
	}

	/**
	* is the WP nonce valid? pass if yes
	*/
	public static function nonceCheck($formPost){
		if(isset($formPost['af']['_afnonce'])):
			$_nonce = $formPost['af']['_afnonce'];
			if(empty($_nonce)):
				return false;
			endif;
			if ( ! wp_verify_nonce( $_nonce , '_afnonce' ) ):
				return false;
			endif;
		else:
			return false;
		endif;
		return true;	
	}

	public static function spamCheck($formPost){
		if( isset($formPost['contact_by_fax']) || ( isset($formPost['thefaxnumber9tX4bPz']) && !empty($formPost['thefaxnumber9tX4bPz']))):
			return false;	
		endif;
		return true;
	}

	public static function postDataUpdate($formFields = null,$formPost = null){
		
		$__POSTEDDATA = $formPost;
		$__NEWPOSTDATA = array('form'=>array(),'fields'=>array());
		foreach($formFields as $fieldID => $formfield):
			if(isset($__POSTEDDATA[$fieldID])):
				
				$fieldValue = $formfield->postedFormData;

				if(is_array($fieldValue)):
					$fieldValue = implode(',',$fieldValue);
				endif;
				/**
				 * USES The Label of the choice as the new Value
				 * FOR EMAIL ONLY
				 * Updated : 04-12-17
				*/
				if( isset($formfield->input->settings->for_admin_email) && isset($formfield->input->settings->use_choicelabel_as_value) ):
					if(!empty($formfield->input->values)):
						if(preg_match('/^\d+:/i' , $fieldValue)):
							preg_match('/(^\d+):/i', $fieldValue , $captured);
							$valueIndex = (isset($captured[0]))? $captured[0] : null;
							$valueIndex = (!empty($valueIndex)) ? explode(':',$valueIndex) : null;
							$valueIndex = (isset($valueIndex[0])) ? $valueIndex[0] : null;
							if( is_numeric($valueIndex) && $valueIndex >= 0 ):
								$choices = $formfield->input->values;
								$choice='';
								if(is_array($choices)):
									if(isset($choices[$valueIndex])):
										$choice = $choices[$valueIndex];
									endif;
								else:
									if(isset($choices->$valueIndex)):
										$choice = $choices->$valueIndex;
									endif;
								endif;

								if(!empty($choice)):
									$fieldValue = $choice->display;
								endif;
							endif;
						endif;	
					endif;
				endif;

				if($formfield->fieldsettings->name != 'submitbutton'):
					$skipadminmail = (isset($formfield->input->settings->display_only)) ? true : false;
					$__NEWPOSTDATA['fields'][$fieldID] = array(
						'display'   	 =>  (!empty($formfield->displayname)) ? $formfield->displayname : $fieldID,
						'field'     	 =>  $fieldID,
						'fieldtype' 	 =>  $formfield->fieldsettings->name,
						'value'			 =>  $fieldValue,
						'skipadminmail' =>  $skipadminmail
					);
				endif;
				unset($__POSTEDDATA[$fieldID]);
			else:
				if($formfield->fieldsettings->name == 'text'):
					$skipadminmail = (isset($formfield->input->settings->display_only)) ? true : false;
					if($formfield->input->settings->html_tag == 'fieldset_bot' || $formfield->input->settings->html_tag == 'fieldset_top'):
						$skipadminmail = true;
					endif;
					$__NEWPOSTDATA['fields'][$fieldID] = array(
						'display'   	 =>  $fieldID,
						'field'     	 =>  $fieldID,
						'fieldtype' 	 =>  $formfield->fieldsettings->name,
						'value'			 =>  $formfield->input->settings->text,
						'skipadminmail' =>  $skipadminmail
					);
				endif;
			endif;
		endforeach;

		$__CUSTOMFIELDS = array();
		$__CUSTOMFIELDPREFIX = AF_C_FIELDNAME_PREFIX;
		foreach($__POSTEDDATA as $k => $v):
			if(preg_match("/".$__CUSTOMFIELDPREFIX."/i", $k)):
				$newKey = str_replace($__CUSTOMFIELDPREFIX, '', $k);
				$__CUSTOMFIELDS[$newKey] = $v;
				unset($__POSTEDDATA[$k]);
			endif;
		endforeach;
		$__POSTEDDATA[AF_C_FIELDNAME_PREFIX] = $__CUSTOMFIELDS;
		$__NEWPOSTDATA['form'] = $__POSTEDDATA;
		return $__NEWPOSTDATA;

	}

	public static function parseBraces($string=null,$fields=null){

		$string = preg_replace_callback('/{.*?}/', function($match) use ( $fields ){

			if( isset( $match[0] ) && preg_match('/-/i' , $match[0] )):
				$fieldName = $match[0];
				$fieldName = preg_replace('/{|}/', '', $fieldName);
				$fieldName = explode('-',$fieldName);
				$fieldName = array_pop($fieldName);
				$match[0] = "{{$fieldName}}"; /* write {field-name-1} to {1} */
			endif;
			foreach( $fields as $field ):
				$useid = explode('-',$field['field']);
				$useid = array_pop($useid);
				if($match[0] == "{{$useid}}"):
					return str_ireplace("{{$useid}}", $field['value'] , $match[0] );
				endif; 
			endforeach;
			return $match[0];

		}, $string);

		return $string;

	}

}
