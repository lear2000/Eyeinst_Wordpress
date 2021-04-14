<?php
namespace aform\core\form;
class handler{

	private static $instance;
	private $successResponse = array('ajax_confirmation'=>'failure','failure'=>true ,'message'=>'Unsuccessful Process');
	private $formInstance;
	private $postedFormData = null;
	private $postedFormDataClean = null;
	private $invalidFields = array();
	private $formStatus = 'ready';
	private $ajax = false;
	private $validate;
	private $files = null;
	private $hasFiles = false;
	private $reponse;

	private $groupedData;

	function __construct(){
		// we don't use
	}
	/* PUBLIC FUNCTIONS */
	public function getProperty($prop){ 
	// allows us to access private properties(variables) when $this is passed to another class. 
		if(!isset($prop)):
			return false;
		endif;
		return $this->$prop;
	}
	public function isAjax(){
		return $this->ajax;
	}
	public function getFiles(){
		return $this->files;
	}
	public function getFormStatus(){
		return $this->formStatus;
	}
	public function getInvalidFields(){
		return $this->invalidFields;
	}

	public static function get_instance( $formInstance = null ){

		 if(empty(self::$instance)):
			if(null == $formInstance): 
				return null; 
			endif;

            self::$instance = new self();
            self::$instance->formInstance = $formInstance;
            /**
            * Prepares all the posted data
            */
            $preparepass = self::$instance->prepare();

            if( $preparepass == false ){
            	return self::$instance;
            }

            $spamCheck = helper::spamCheck($_POST);
            
            if(!$spamCheck):
            	self::$instance->formStatus = 'spamcheck:fail';
            	self::$instance->successResponse['message'] = 'Spam Check: Failed';
            	return self::$instance;
            endif;

						if(isset($_POST['af']['recaptcha'])):
							
							$_AFORMGLOBALSETTINGS = get_option('_aform_settings',array());
							/**
							 *	Build POST request:
							*/ 
							$recaptchaUrl = 'https://www.google.com/recaptcha/api/siteverify';
							$recaptchaSecret = $_AFORMGLOBALSETTINGS['recaptcha-server'];
							$recaptchaScore = (isset($_AFORMGLOBALSETTINGS['recaptcha-score']) && !empty($_AFORMGLOBALSETTINGS['recaptcha-score'])) ? floatval ($_AFORMGLOBALSETTINGS['recaptcha-score']) : 0.5;
							$recaptchaResponse = $_POST['af']['recaptcha'];

							/**
							 *	Make and decode POST request:
							*/ 
							$recaptcha = file_get_contents("{$recaptchaUrl}?secret={$recaptchaSecret}&response={$recaptchaResponse}");
							$recaptcha = json_decode($recaptcha);

							/** 
							 * Take action based on the score returned:
							*/
							if ($recaptcha->score < $recaptchaScore):
									self::$instance->successResponse['recaptcha_failed'] = true;
									self::$instance->successResponse['recaptcha_score'] = $recaptcha->score;
									//self::$instance->successResponse['recaptcha_setting'] = $recaptchaScore;
									self::$instance->successResponse['message'] = "Human verification failed.";
									return self::$instance;
							else:
								self::$instance->successResponse['recaptcha_score'] = $recaptcha->score;
								//self::$instance->successResponse['recaptcha_setting'] = $recaptchaScore;
							endif;
						endif;
            /** 
             * NONCE CHECK 
            */
            $nonceCheck = helper::nonceCheck($_POST);

            if($nonceCheck):
            	/**
            	 * it passed? start processing the form
            	*/
            	$processResponse = self::$instance->process();

            	self::$instance->response($processResponse);

            else:

            	self::$instance->successResponse['message'] = 'Verification Failure';
            	return self::$instance;
           	endif;
           
        elseif( null != $formInstance ):
			return null;
		endif;

		return self::$instance;
	}

	public function prepare(){
		
		$this->groupedData = new \stdClass();

		/**
		 * @var $FORMPOSTDATA : data that was submitted
		 */
		$FORMPOSTDATA = (isset($_POST[AF_FIELDNAME_PREFIX])) ? $_POST[AF_FIELDNAME_PREFIX] : null ;
		
		if($FORMPOSTDATA == null ):
			return false;
		endif;		
		/**
		 * Run our posted data through sanitation to remove any unwanted html
		 */	
		//$this->postedFormData = $this->sanitize( $FORMPOSTDATA );
		
		$this->postedFormData = helper::sanitizePost($FORMPOSTDATA);
		
		$this->postedFormData = array('afid'=>$this->formInstance->form->ID) + $this->postedFormData;
		
		/**
		 * Check if submission came through ajax
		 */
		if(isset($FORMPOSTDATA['ajax']) && $FORMPOSTDATA['ajax']==true):
			$this->ajax = true;
		endif;

		/**
		 * We save our post data into their fieldObjects for later use
		 */
		if(!empty($this->postedFormData)):
			foreach ($this->postedFormData as $key => $value):
				if(isset($this->formInstance->form->fields[$key])):
					$this->formInstance->form->fields[$key]->postedFormData = $value;
				endif;
			endforeach;
		endif;
		
		foreach(array('primary_email_recipient','for_admin_email') as $setSetting ):
			if(isset($this->formInstance->formSettings->$setSetting)):
				//fieldName
				$fieldName = $this->formInstance->formSettings->$setSetting;
				if(isset($this->postedFormData[$fieldName])):	
					$postedFieldData  = $this->postedFormData[$fieldName];
					if(!empty($postedFieldData)):
						if(is_array($postedFieldData) && count($postedFieldData) == 1 ):
							$postedFieldData = $postedFieldData[0];
						endif;
						//removes index
						if(preg_match('/^\d+:/i' , $postedFieldData)):
							$postedFieldData = preg_replace('/^\d+:/i','',$postedFieldData);
						endif;
						$this->formInstance->formSettings->$setSetting = $postedFieldData;						
						if($setSetting == 'for_admin_email' && strpos($postedFieldData, '@') !== false):
							$fieldObject = $this->formInstance->form->fields[$fieldName];
							/*
								append_admin_emails
							*/
							if(isset($fieldObject->input->settings->append_admin_emails) && $fieldObject->input->settings->append_admin_emails == true ):
								if(!empty( $this->formInstance->formSettings->admin_email )):
									$adminEmail = $this->formInstance->formSettings->admin_email;
									$postedFieldData = "{$adminEmail},{$postedFieldData}";						
								endif;
							endif;
							$this->formInstance->formSettings->admin_email = $postedFieldData;
						endif;
					else:
						if($setSetting == 'for_admin_email'):
							// we remove since its empty
							unset($this->formInstance->formSettings->$setSetting);
						endif;
						if($setSetting == 'primary_email_recipient'):
							// we remove since its empty
							unset($this->formInstance->formSettings->$setSetting);
						endif;
					endif;//
				endif;
			endif;
		endforeach;

		/* FOR NON-ajax submissions */
		if( !$this->isAjax() && isset( $_FILES[AF_FIELDNAME_PREFIX] ) ):
			$preparedFiles = fileupload::prepareFiles( $_FILES[AF_FIELDNAME_PREFIX] , $_POST , false );
			$this->files = $preparedFiles;
			if(!empty($preparedFiles)):
				$this->hasFiles = true;
				foreach($preparedFiles as $fieldID => $file):
					$fieldParts = explode('--',$fieldID);
					$key = $fieldParts[0];
					$index = $fieldParts[1];
					$this->formInstance->form->fields[$key]->postedFormData[$index] = $file;
				endforeach;
			endif;
		endif;
	
		//keep at end, this is passed to action hooks and filters
		$this->groupedData->form = $this->formInstance->form;
		$this->groupedData->post = $this->postedFormData;	

		return true;
	}
	
	public function process(){
	
		$validate = $this->validate( $this );
		$invalidFields = $validate->getProperty('invalidFields');
		if( !empty( $invalidFields ) ):
			$this->invalidFields 	= $invalidFields;
			$this->formStatus 		= 'validation:fail';
			$this->successResponse['invalid-fields'] = $invalidFields;
			return;
		endif;

		$checkIp = $this->isIpBlocked();//checks for black listed IPs
		if($checkIp == true):
			$this->formStatus = 'validation:fail';
			return;
		endif;

		/*
			Validation Pass ?
			Run through data Beautifier before we submit to database , send mail.
		*/
		
		if( $this->hasFiles == true ):
			$preparedFiles = $this->files;
			if(!empty($preparedFiles)):
				$uploaded = fileupload::phpmove($preparedFiles , $this->formInstance->form->fields );
				if(!empty($uploaded )):
					foreach($uploaded  as $k => $links):
						$this->postedFormData[$k] = implode(', ', $links);
						if(isset($this->formInstance->form->fields[$k])):
							$this->formInstance->form->fields[$k]->postedFormData = implode(', ', $links);
						endif;
					endforeach;
				endif;
			endif;		
		endif;
		
		/**
		* separates post data into 'form' and 'fields', usable data across handler
		*/
		$this->postedFormDataClean = helper::postDataUpdate($this->formInstance->form->fields, $this->postedFormData);
		$this->groupedData->_POST_FORM = $this->postedFormDataClean['form'];
		$this->groupedData->_POST_FORM_FIELDS = $this->postedFormDataClean['fields'];
		$this->groupedData->postchunks = $this->postedFormDataClean;

		
		$response 							= array();
		$response['formsaved'] 			= false;
		$response['formsent'] 			= false;
		$response['recipientsent'] 	= false;
		$response['adminsent']			= false;
		$response['submissionid']		= 0;
		$response['redirectwithpost'] = (isset($this->formInstance->formSettings->redirect_with_post)) ? true : false;

		do_action('aform/before/post', $this->groupedData , $this );//can call to 3rd party here

		////// WE START SAVING/SENDING SUBMISSION 

		$save = new save();
		$sender = new sender($this);
		$senderNeeds = ($sender->getProperty('confEmail')) ? 2 : 1;
		$senderHas = 0;

		###########
		## Save Form
		###########
		$response['formsaved'] = $save->submission($this);//send helper instance
		
		if($response['formsaved'] != 0):
			$response['submissionid']				= $response['formsaved'];
			$this->groupedData->submission_id 	= $response['formsaved'];
			$response['formsaved'] = true;		
		else:
			$response['formsaved'] = false;
		endif;
		
		if($response['formsaved']): $senderHas = ($senderHas+1); endif;
		###########
		## Send Mail
		###########
		
		//send admin email
		$response['adminsent'] = $sender->to('admin');//will always send

		//send recipient email
		if($sender->getProperty('confEmail')):
			$response['recipientsent'] = $sender->to('recipient');
			if($response['formsaved']): $senderHas = ($senderHas+1); endif;
		endif;

		if( $response['adminsent'] || $response['recipientsent'] || $response['formsaved'] ):
			if( ($senderNeeds == $senderHas) && $response['formsaved'] ):
				
				$response['formsent'] 							= true;	
				$this->formStatus 									= 'success';
				$formSettings 											= $this->formInstance->formSettings;
				$response['use_ajax'] 							= afGetAOV($formSettings , 'ajax_redirect' , false );
				$response['url'] 										= afGetAOV($formSettings , 'conf_page');
				$AJAX_CONF_MESSAGE 									= stripslashes_deep( afGetAOV($formSettings , 'ajax_confirmation') );
				$AJAX_CONF_MESSAGE 									= helper::parseBraces( $AJAX_CONF_MESSAGE ,  $this->postedFormDataClean['fields'] );
				$response['confirmation_message'] 	= $AJAX_CONF_MESSAGE;
				$response['ajax_confirmation']			= $AJAX_CONF_MESSAGE;
				$response['message'] 								= $this->formStatus;
				$response['failure']								= false;

				//add option to apply this filter // off by default			
				//$AJAX_CONF_MESSAGE = apply_filters( 'the_content' , $AJAX_CONF_MESSAGE );

			else:
				$this->formStatus = 'error';
			endif;
		endif;

		$response['formstatus'] = $this->formStatus;
		$this->groupedData->response = $response;

		do_action('aform/after/post', $this->groupedData , $this );//can call to 3rd party here
		

		return $response;		
	}

	private function response($response=null){
		if(!empty($response)):
			
			$response = apply_filters( 'aform/form/response' , $response ,  $this->groupedData , $this );////can call to 3rd party here and update ajax response

			$response = array_merge($this->successResponse, $response);
			$this->successResponse = $response;
			$this->formInstance->responseData = $response;
		endif;
	}

	private function validate($handler = null){

		if( empty($handler) ) return null;
		$this->validate = validate::get_instance( $handler );
		return $this->validate;
	}

	private function isIpBlocked(){
		$remoteaddr = (isset($_SERVER['REMOTE_ADDR'])) ? $_SERVER['REMOTE_ADDR'] : '';

		$list = apply_filters('aform/form/blockip',array());

		if(in_array($remoteaddr , $list )):
			return true;
		endif;

		return false;
	}	

}
