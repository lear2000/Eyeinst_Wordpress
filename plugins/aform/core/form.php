<?php  
namespace aform\core;
class form{

	private static $formInstance = array();

	public $formname = null;
	public $formid = null;
	public $isFormPosted = false;//used in PHP POST(non-ajax)
	public $form = null;//all the form data (db:fields+settings)
	public $formSettings = null;
	public $responseData = null;
	

	public $handler = false;

	function __construct(  $formname = null , $posted = false ){
			
			$this->formname = $formname;
			$this->isFormPosted = $posted;
			$this->form = _AFORMDB()->getForm( $formname );//get me some data!
			if(isset($this->form)):
				
				if(empty($this->form->fields)) return;
				
				/**
				*  
				* prepares all the form settings + fields before render 
				* 
				*/
				
				if( isset($this->form->fields) && !empty($this->form->fields ) ):
					$this->applyFieldClass();
				endif;
				$this->formSettings = (!empty($this->form->settings)) ? json_decode($this->form->settings->settings) : null;
				if(!empty($this->formSettings)):
					unset($this->form->settings);

					$this->getFieldSetting( $this->form->fields , 'primary_email_recipient' );
					$this->getFieldSetting( $this->form->fields , 'for_admin_email' );
					$this->getFieldSetting( $this->form->fields , 'cmIntegration' );
				
				endif;
				$this->afname 	= $this->form->post_name;
				$this->afid 	= $this->form->ID;
				$this->formid 	= $this->form->ID;
				$this->nodata 	= false;	
			else:
				$this->nodata = true;
			endif;
	}

	/**
	* Creates an instance of the form (array) 
	*/
	public static function get_instance( $formname = null , $posted = false ) {
		
		/*if no instance is available then we create one*/
		if(!isset(self::$formInstance[$formname])):
			self::$formInstance[$formname] = new self( $formname , $posted );
		endif;

		return self::$formInstance[$formname];
	
	}

	/**
	* When we can't find the form, we display a friendly error.
	* */
	function noForm(){
		$html = "<p class=\"aform_four-oh-four\" style=\"background:#efefef;color:#666666;border:1px solid;margin:0 auto;max-width: 70%;padding:10px;text-align: center;\">";
		$html .= "{$this->formname}<br>404 &#8212; Form Not Found";
		$html .= "</p>";
		echo $html;
	}

	/**
	* - checks if a field-setting is linked to a form-settings
	* 
	* - if a field & form setting are linked then we park 
	* 	the field-{id} in the form setting for later use
	* 
	* - once a form is posted then we replace with field-value
	* 
	* - if the field value is empty then we delete the form settings
	*  
	*/
	private function getFieldSetting($data = null , $fieldSetting = null){
		foreach ($data as $key => $class) {
			if( isset($class->input->settings->$fieldSetting ) ):
				$this->formSettings->$fieldSetting = $key;
			endif;
		}
	}

	/** 
	* - Updates data->fields with instance of input_type 
	* - merges input_settings with input_type object(class)
	*/
	public function applyFieldClass(){
		$formData = $this->form;
		$this->form->fieldsUpdate = null;
		foreach($formData->fields as $index => $fieldData):
			$classname = $fieldData->input_type;
			$class = "aform\\fields\\{$classname}";
			$fieldClass = new $class;
			$fieldClass = $fieldClass->fieldDataSetup( $fieldData  , $public = true );
			$this->form->fieldsUpdate[$fieldClass->fieldname] = $fieldClass;// applies All field data to class , modified class	
		endforeach;
		$this->form->fields = $this->form->fieldsUpdate;
		unset($this->form->fieldsUpdate);
	}


	public function render( $value = null ){
		global $aformPlugin;
		
		//no field data no form
		if(!isset($this->form->fields) || empty($this->form->fields)) return;

		// continue with the render	
		$formData = $this->form;
		
		if(is_object($this->handler)): 
			$formData = $this->handler->getProperty('formInstance')->form; 
		endif;	
		
		ob_start();
		
		/**
		* if form not found then we end render here :( 
		**/	
		if(empty($formData)):
				$this->noForm();
				$render = ob_get_contents();
			ob_end_clean();	
			echo $render;
			return;
		endif;

		/**
		* check: when non-ajax submission
		* */
		if(is_object($this->handler)):
			if(preg_match('/spamcheck\:fail/i', $this->handler->getProperty('formStatus') ) ):
				echo '<strong><small>Validation Failed: Possible Robot.</small></strong>';
			elseif( preg_match('/validation\:fail/i', $this->handler->getProperty('formStatus') ) ):
				echo '<strong><small>Validation Failed: Fields</small></strong>';
			endif;
		endif;			

		/**
		* prints start of form (html)
		*/
		$this->printFormStart($formData);

			/**
			* prints hidden fields (html)
			*/
			$this->printHiddenFields($formData);
			
			/*
				hook in to add custom html or custom inputs
			*/
			do_action('aform/form/before', $formData );

			/**
			* prints fields (html)
			*/
			if(!empty($formData->fields)):
				foreach ($formData->fields as $fieldData):
					if(empty($fieldData->input->settings)) continue;//if field has no settings then it should not show				
					$this->printField( $fieldData );
				endforeach;
			endif; 

			/*
				hook in to add custom html or custom inputs
			*/
			do_action('aform/form/after', $formData );


			/* checks if submit field is available, if not then we use our new method*/
			if(!$this->hasSubmitField($formData->fields)):
				$this->submitButton();
			endif;

			/*
				hook in to add custom html or custom inputs
			*/
			do_action('aform/form/after-submit', $formData );

		/**
		* prints end of form (html)
		*/
		$this->printFormEnd( $formData );


		$render = ob_get_contents();
		ob_end_clean();	
		//remove extra spacings
		$htmlCompress = apply_filters( 'aform/form/compress', true , $formData );
		
		if($htmlCompress){
			$render = trim(preg_replace('/\s\s+/', ' ', $render));
			$render = preg_replace('~[\r\n]+~', '',$render);	
		}
		echo $render;
	}
	public function hasSubmitField($fields){
		$hassubmit = false;
		foreach ($fields as $fieldData):							
			if($fieldData->fieldsettings->name == 'submitbutton'):
				$hassubmit = true;
			endif;
		endforeach;
		return $hassubmit;
	}
	public function submitButton(){
		$formSettings = $this->formSettings;
		$submitText =  (!empty($formSettings->form_submit_text)) ? $formSettings->form_submit_text : 'Submit'; 
		$submitClass = array('submit-btn');
		if(!empty($formSettings->form_submit_class)):
			$customClass = $formSettings->form_submit_class;
			$customClass = explode(' ',$customClass);
			$submitClass = array_merge($submitClass,$customClass);
		endif;
		?>
			<div class="type-submitbutton field-wrap">
				<input class="<?php echo (join(' ',$submitClass));?>" type="submit" name="form-submits" value="<?php echo ($submitText);?>">
			</div>
		<?php
	}
	public function printFormStart($formData = null){
		$formSettings = $this->formSettings;

		$formClass = apply_filters('aform/form/class',array('aform',"aform-{$formData->ID}"),$formData);

		if(!empty($formSettings->form_class)):
			$customClass = $formSettings->form_class;
			$customClass = explode(' ',$customClass);
			$formClass = array_merge($formClass,$customClass);
		endif;


		$formAttr = array();
		$formAttr['method']  		= 'post';
		$formAttr['class']			= implode(' ',$formClass);
		$formAttr['id']   			= "aform-{$formData->ID}";
		$formAttr['action']  		=  esc_url( $_SERVER['REQUEST_URI'] );
		$formAttr['enctype'] 		= 'multipart/form-data';
		$formAttr['encoding'] 		= 'multipart/form-data';
		$formAttr['data-form-id'] 	= $formData->ID;
		$formAttr['data-aform-id'] 	= $formData->ID;
		$formAttr = apply_filters( 'aform/form/attr' , $formAttr , $formData );

		$attrText= array();
		foreach($formAttr as $attr => $attrValue):
			$attrText[] = "{$attr}=\"{$attrValue}\"";
		endforeach;
		$attrText = implode(" ",$attrText);
		
		echo "<form {$attrText}>";

	}
	public function printFormEnd($formData = null){
				echo "</form>";
				global $_AFORMSCLIENTJS;
				$_AFORMSCLIENTJS = true;
				$this->outputRules( $formData ); 
	}
	public function printHiddenFields($formData = null){
		global $_AFORMGLOBALSETTINGS;
		if($_AFORMGLOBALSETTINGS['recaptcha']):
			?>
				<input type="hidden" name="af[recaptcha]" class="af-recaptcha">
			<?php
		endif;	
		?>
			<input type="hidden" name="<?php afFieldName('afname');?>" value="<?php echo $formData->post_name;?>">
			<input type="hidden" name="<?php afFieldName('_afnonce');?>" value="<?php echo wp_create_nonce( '_afnonce' );?>">
			<input type="hidden" name="<?php afFieldName('http_referrer');?>" value="<?php echo __aFormGetRequestUri(); ?>">
			<div class="stopyenoh robotic" id="<?php echo $formData->ID;?>__pot">
				<label class="">do or do not , there is no try</label>
				<input class="" type="text" name="thefaxnumber9tX4bPz" value="" tabindex="-1" autocomplete="off">
				<input class="" type="checkbox" name="contact_by_fax" value="1" tabindex="-1" autocomplete="off">
			</div>
			<?php if( is_page() && is_single() ): ?>
				<?php global $post; ?>
				<input type="hidden" name="<?php afFieldName('submission-page'); ?>" value="<?php echo $post->post_title; ?>" />
			<?php endif; ?>
		<?php
	}

	public function label($fieldData = null){
		if( in_array( $fieldData->fieldsettings->name , array('text', 'hidden') ) ):
			return;
		endif;

		$displayName = (isset($fieldData->input->settings->display_name)) ? $fieldData->input->settings->display_name : '';
		$fieldName = $fieldData->fieldname;
		$labelFor = ( ($fieldName) ? sanitize_title($fieldName) : sanitize_title($displayName) );
		$labelText = ( ($displayName) ? $displayName : $fieldName );
		
		echo "<label for=\"{$labelFor}\">{$labelText}</label>";
	}
	
	public function printField( $fieldClass = null  ){

			$inputType = $fieldClass->fieldsettings->name;
			$settings = $fieldClass->input->settings;			

			/** 
			* CREATE USABLE ARRAY OF CLASSES FOR OUR FIELD
			*/
			$cssClass = ( isset($settings->css_class) && $settings->css_class != '' ) ? $settings->css_class : '';
			$cssClass = explode(' ',$cssClass);//array			
			//if is required
			$isFieldRequired = ( afGetAOV( $settings , 'required') ) ? "field-required" : '';
			//field type
			$isFieldType 	= 'type-'.strtolower($inputType);
			//field-ID
			$isFieldId 		= "field-{$fieldClass->fieldID}";
			array_push($cssClass , $isFieldRequired , $isFieldType , $isFieldId);
			//remove empty array items
			$cssClass = array_filter($cssClass);
			$cssClass = apply_filters( 'aform/form/field/class' , $cssClass);#hook added

			/**
			* text only field settings
			**/
			$isOnlyTextField = ( afGetAOV( $settings , 'html_tag') ) ? true: false;
			$onlyTextTag = ($isOnlyTextField == true ) ? $fieldClass->input->settings->html_tag : '';

			/**
			* Field Set Check : top
			**/	
			if(preg_match('/fieldset/i',$onlyTextTag)):
				$cssClass[] = 'fieldset-wrap';
				$cssClass = implode(" ",$cssClass);
				if($onlyTextTag == 'fieldset_top'): echo "<fieldset class=\"$cssClass\"><div class=\"fieldset-wrap-inner\">"; endif;
			else:
				$cssClass[] = 'field-wrap';
				$cssClass = implode(" ",$cssClass);
				echo "<div class=\"{$cssClass}\">";
			endif;
				
			/** 
			* VALIDATION FAIL STARTS BELOW
			*/
				if( is_object($this->handler) && preg_match('/validation\:fail/i', $this->handler->getProperty('formStatus') ) ):
					$invalidfields = $this->handler->getProperty('validate')->getInvalidFields();
					if( array_key_exists( $fieldClass->fieldname , $invalidfields ) && !is_array($invalidfields[$fieldClass->fieldname]) ):
						echo '<p class="validation-fail">' . $invalidfields[$fieldClass->fieldname] . '</p>';
					elseif(array_key_exists( $fieldClass->fieldname , $invalidfields )):
						foreach( $invalidfields[$fieldClass->fieldname] as $fieldErrors):
								foreach($fieldErrors as $filename => $fieldError):
									echo '<p class="validation-fail"><strong>'.$filename.'</strong> &rsaquo;&rsaquo;&rsaquo; '.join("<br>",$fieldError).'</p>';
								endforeach;
						endforeach;
					endif;
					$fieldClass->returnValue = $fieldClass->postedFormData;
				endif;
			/** 
			* VALIDATION FAIL ENDS ABOVE 
			*/
			
			if( afGetAOV( $settings , 'enable_field_desc' , false ) == true ):
				$fieldDescriptionHtml = afGetAOV( $settings , 'field_desc_html');
				if( afGetAOV( $settings , 'field_desc_wpautop' , false ) == true ):
					$fieldDescriptionHtml = wpautop($fieldDescriptionHtml);
				endif;
				echo "<div class=\"field-description\">{$fieldDescriptionHtml}</div>";
			endif;
			if( afGetAOV( $settings , 'reverse_display' , false ) == true ) {
				$fieldClass->publicRender( $form = $this );//$this = we reference the Form Class

				if( !isset($settings->hide_label) ):
					$this->label(  $fieldClass );
				endif;

			} else {
				if( !afGetAOV( $settings , 'hide_label') && $inputType != 'submitbutton' ):
					$this->label(  $fieldClass );
				endif;
				$fieldClass->publicRender( $form = $this );//$this = we reference the Form Class				
			}
			
			/**
			* Field Set Check : end
			**/									
			if(preg_match('/fieldset/i',$onlyTextTag)):
				if($onlyTextTag == 'fieldset_bot'): echo "</div></fieldset>"; endif;
			else:
				echo "</div>";
			endif;
	}
	
	public function outputRules( $data = null ) {
		global $_AFORMRULES;
		$_AFORMRULES = (!empty($_AFORMRULES)) ? $_AFORMRULES : array();
		$fields = $data->fields;		
		$thereAreRules = array();
		foreach( $fields as $field ):
			if( method_exists($field, 'getRules') ):
				$fieldRules = $field->getRules();
				if( $fieldRules ):
					$thereAreRules[$field->fieldID] = $fieldRules;
				endif;
			endif;	
		endforeach;
		$_AFORMRULES[$data->ID] = array('rules'=>$thereAreRules);
	}

	/**
	* [php]  called from : aform\core\form\controller
	* [ajax] called from : aform\core\ajax
	*/
	public function submit(){
		$handler = form\handler::get_instance( $this );
		$this->handler = $handler;
		
		/**
		* [php]
		* Only runs when the submission is non-ajax
		*/
		if( $handler->getProperty('ajax') == false && $handler->getProperty('formStatus') == 'success' ):
			$response = $handler->getProperty('successResponse');
			if( isset( $response['url'] ) && !empty($response['url']) ):
				if( strpos($response['url'], home_url()) ):
					wp_redirect($response['url'] );
				else:				
					wp_redirect( home_url() . '/' . ltrim($response['url'] , '/') );
				endif;
				exit();
			else:
				wp_redirect(__aFormGetRealUri());// redirect to self
				exit();
			endif;
		endif;

		// [ajax]
		return $handler;
	}
}
