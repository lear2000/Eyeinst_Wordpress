<?php
namespace aform\core\form;
class sender{

	private $formName;
	private $formInstance; 
	private $handler;
	private $confEmail = false;
	private $confEmailSettings;
	private $processStatus = array();
	
	public function getProperty($prop=null){
		// allows us to access private properties(variables) when $this is passed to another class. 
		return $this->$prop;
	}

	public function __construct($handler=null){
		$this->confEmailSettings = new \stdClass();
		$this->handler = $handler;
		$this->formInstance = $handler->getProperty('formInstance');
		$this->prepare();
	}

	
	private function prepare(){
	  	$this->processStatus = new \stdClass();
        $this->processStatus->adminsent = false;
        $this->processStatus->recipientsent = false;
		
		if(!empty($this->formInstance)):
			$form = $this->formInstance;	
			$formSettings = $form->formSettings;
			$this->formName = $form->formname;
			if( isset($formSettings->conf_email_enabled) 
				&& $formSettings->conf_email_enabled == true ):
				$this->confEmail = true;
				$this->confEmailSettings->conf_email_address = afGetAOV($formSettings,'conf_email_address',$default = $formSettings->admin_email_from);
				$this->confEmailSettings->conf_email_subject =  afGetAOV($formSettings , 'conf_email_subject','Form has been received');
				$this->confEmailSettings->conf_email_text = $formSettings->conf_email_text;
				$this->confEmailSettings->conf_email_text_applyfilter = afGetAOV($formSettings,'conf_email_text_applyfilter',$default = false);
			endif;
		endif;	
	}
	public function to($methodname=null){
		$response = false;
		if(!empty($methodname)):
			if(method_exists($this, $methodname)):
				$response = $this->$methodname();
			endif;
		endif;
		return $response; 
		
	}
	private function admin(){

		$_SENT 					= false;
		$_HEADERS 				= array();
		$UPLOADDIR 				= wp_upload_dir();
		$formInstance 			= $this->formInstance;
		$handlerInstance 		= $this->handler;
		$formPostObject 		= $formInstance->form;
		$formSettings 			= $formInstance->formSettings;
		$postedFormDataClean 	= $handlerInstance->getProperty('postedFormDataClean');
		$hasFile 				= ( isset( $postedFormDataClean['fields'] ) ) ? $this->fieldsCheck( $postedFormDataClean['fields'] , 'file' ) : false ;
		$postedData 			= json_encode($postedFormDataClean);
		
		$filesToAttach = ( $hasFile ) ? $this->getAttachments( 
			$postedFormDataClean['fields'] , 
			$hasFile['fields'] , 
			true , 
			array('replacewith'=> $UPLOADDIR['basedir'] , 'fieldObjects' => $formInstance->form->fields )
			) : array();

		if (isset($formSettings->use_custom_admin_subject) && $formSettings->use_custom_admin_subject == "true" && $formSettings->custom_admin_subject != ''):
			$_SUBJECT = $formSettings->custom_admin_subject;
			$_SUBJECT = helper::parseBraces( $_SUBJECT, $postedFormDataClean['fields'] );
		else:
			$_SUBJECT = "New {$formPostObject->post_title} Form Submission";		
		endif;

		ob_start();
		
			if( $formSettings->admin_email_type == 'plaintext' ):
				include( __DIR__ . DIRECTORY_SEPARATOR . 'templates/mail-plaintext-template.php' );
			else:
				include( __DIR__ . DIRECTORY_SEPARATOR . 'templates/mail-template.php' );			
			endif;

		$_MESSAGE = ob_get_clean(); 
			
		$_MESSAGE = apply_filters( 'aform/form/send/adminemail' , $_MESSAGE , array( 
			'formPostedData' 	=> $postedFormDataClean,
			'formPost' 			=> $formPostObject,
			'formSettings' 	=> $formSettings
		) , $this );
		
		// if admin_email is empty then use admin_email_form. If both are empty then use WP admin_email
		$_TO = ( isset( $formSettings->admin_email ) && !empty( $formSettings->admin_email ) ) ? $formSettings->admin_email : ( isset($formSettings->admin_email_from) && !empty($formSettings->admin_email_from) ? $formSettings->admin_email_from : get_bloginfo('admin_email') ) ;
		

		$_HEADERS[] = (isset($formSettings->admin_email_from) && !empty($formSettings->admin_email_from)) ? "From: " . $formSettings->admin_email_from : "From: " . get_bloginfo( 'admin_email' );

		
		if ( isset($formSettings->admin_bcc) && !empty($formSettings->admin_bcc) ):
			$_HEADERS[] = "Bcc: " . $formSettings->admin_bcc;
		endif;
		if ( isset($formSettings->primary_email_recipient) && $formSettings->primary_email_recipient != '' ):
			$_HEADERS[] = "Reply-To: " . $formSettings->primary_email_recipient;
		endif;
		

		if( $formSettings->admin_email_type != 'plaintext' ):
			add_filter( 'wp_mail_content_type', array( &$this, 'admin_html_mail') ); 
		endif;
		
		if( $hasFile ):
			/** 
			* ATTACH our files :) 
			* bada boom!
			*/
			$attachments = $filesToAttach;
			$_SENT = wp_mail( $_TO , $_SUBJECT , $_MESSAGE , $_HEADERS , $attachments );
		else:
			$_SENT = wp_mail( $_TO , $_SUBJECT , $_MESSAGE , $_HEADERS );
		endif;
		
		if($_SENT):
			$this->processStatus->adminsent = true;
		endif;
		
		remove_filter( 'wp_mail_content_type', array( &$this, 'admin_html_mail') );
		return $_SENT;

	}

	private function recipient(){

		$formInstance = $this->formInstance;	
		if($this->confEmail && isset($formInstance->formSettings->primary_email_recipient)):
			
			$handlerInstance = $this->handler;

			#Form Settings
			$_FORMSETTINGS = $formInstance->formSettings;
			
			#Form Fields ( not post data )
			$_FORMFIELDS = $formInstance->form->fields;
			
			#Check if conditional is applied
			$hasConditionalConfirmation = $this->findAdvancedSetting( $_FORMFIELDS , 'conditional_confirmation' );
			$hasCustomConfirmationSubject = $this->findAdvancedSetting( $_FORMFIELDS , 'use_as_confirmation_subject' );
			
			# POST DATA
			$postedFormDataClean = $handlerInstance->getProperty('postedFormDataClean');
			$fields = $postedFormDataClean['fields'];
			
			#FROM EMAIL
			$confEmailSettings = $this->confEmailSettings;
			
			#CONFIRMATION MESSAGE
			$_MESSAGE = helper::parseBraces( $confEmailSettings->conf_email_text , $fields );
			
			if($confEmailSettings->conf_email_text_applyfilter):
				$_MESSAGE = apply_filters( 'the_content' , $_MESSAGE );
			endif;

			#SUBJECT
			$_SUBJECT = (!empty($confEmailSettings->conf_email_subject)) ? $confEmailSettings->conf_email_subject : 'Form has been received';



			########################## 
			# Conditional Confirmation 
			#########################
			if(!empty($hasConditionalConfirmation)):
				$checkfields = $fields;
				$keycheck = key($hasConditionalConfirmation);
				$checkfields = (isset($checkfields[$keycheck])) ? $checkfields[$keycheck] : false;
				$hasConditionalConfirmation = (isset($hasConditionalConfirmation[$keycheck])) ? $hasConditionalConfirmation[$keycheck] : false;
				#
				# this is where the magic happens 
				#
				if($checkfields != false && $hasConditionalConfirmation != false):	
					$fieldToCheck = sanitize_title( $checkfields['value'] );
					foreach($hasConditionalConfirmation as $kk => $vv):
						if( $fieldToCheck == $kk ):
							if(!empty($vv['message'])):
								#CONFIRMATION MESSAGE
								$_MESSAGE =  helper::parseBraces( $vv['message'] , $fields ); ## use our custom message
								if(preg_match('/<!--apply-wpc-->/', $_MESSAGE )): # applies WP the_content()
									/* applies html tags */
									$_MESSAGE = preg_replace_callback('/<!--apply-wpc-->(.*)<!--apply-wpc-->/si' , function($matches){
											if(isset($matches[1])):
												return apply_filters( 'the_content' , $matches[1] );
											endif;
									} , $_MESSAGE);
								endif;
								
								if(!empty($vv['subject'])):## subject gets modified only if the message is available
									
									#SUBJECT
									$_SUBJECT = $vv['subject']; 
								
								endif;
							endif;
						endif;
					endforeach;
				endif;
				## done 
			endif;

			$_SUBJECT = helper::parseBraces( $_SUBJECT , $fields );
			###### Conditional Confirmation [ END ]

			#################################################### 
			# Custom Subject line through SingleLineText #
			####################################################

			if(!empty($hasCustomConfirmationSubject)):
				$customsubjectfield = $hasCustomConfirmationSubject['field'];
				$customsubjectfield = $fields[$customsubjectfield];
				if(isset($customsubjectfield['value']) && !empty($customsubjectfield['value'])):
					
					$_SUBJECT = $customsubjectfield['value'];
				
				endif;
			endif;	

			###### Custom Subject line through SingleLineText [ END ]

			$from = ( isset($confEmailSettings->conf_email_address) 
				&& !empty($confEmailSettings->conf_email_address)) ? $confEmailSettings->conf_email_address : ( isset($_FORMSETTINGS->admin_email_from) 
				&& !empty($_FORMSETTINGS->admin_email_from) ? $_FORMSETTINGS->admin_email_from : get_bloginfo('admin_email') ) ;
			
			
			
			$_HEADERS = array();
			
			$_HEADERS['FROM'] = 'From: ' . $from;
			$_TO = $_FORMSETTINGS->primary_email_recipient;
			
			## BCC Message
			if( property_exists($_FORMSETTINGS, 'enable_bcc_confirmation') ):
				
				if(!empty($_FORMSETTINGS->confirmation_email_bcc)): #
					$_HEADERS['BCC'] = "Bcc: " . $_FORMSETTINGS->confirmation_email_bcc;
				endif;
			endif;

			add_filter( 'wp_mail_content_type', array( &$this, 'admin_html_mail') ); # 'text/html'

			$_SENT = wp_mail( $_TO  , $_SUBJECT , $_MESSAGE , $_HEADERS );
						
			remove_filter( 'wp_mail_content_type', array( &$this, 'admin_html_mail') );
			
			if($_SENT):
				$this->processStatus->recipientsent = true;
			endif;
		
			return $_SENT;
		endif;
		return false;
	}


	public function getStatus(){
		return $this->processStatus;
	}
	/**
	* @return array
	* 
	* checks if the formInstance:fields(array) has a fieldType
	*/
	private function fieldsCheck( $fieldsArray = array() , $fieldType = '' ){
		$results = array();
		foreach($fieldsArray as $key => $value ):
			if(isset($value['fieldtype'])):
				if( $value['fieldtype'] == $fieldType ):
					$results['fields'][] = $key;
				endif;
			endif;
		endforeach;
		return $results;
	}
	/**
	* @return array 
	* array of attachment URLs
	*/
	private function getAttachments( $fieldsArray = array() , $list = array() , $filesystempath = false , $settings = array() ){
		$attachments = array();
		$settings = array_merge( array( 'regex'=>'/^(.*?)uploads/i' , 'replacewith' => "" ) , $settings );
		foreach($fieldsArray as $key => $v ):
			if( in_array( $key , $list ) ):
				$fileFieldObject = $settings['fieldObjects'];
				$fileFieldObject = $fileFieldObject[ $v['field'] ];
				if(isset($fileFieldObject->input->settings->no_attachement)) continue;
				$arrAttch = explode(',', $v['value'] );
				if( $filesystempath ):
					foreach( $arrAttch as $index => $file):
						$arrAttch[$index] = preg_replace( $settings['regex'] , $settings['replacewith'] , $file );
					endforeach;
				endif;
				$attachments = array_merge( $attachments , $arrAttch );
			endif;
		endforeach;
		return $attachments;
	}


	
	public function admin_html_mail() {
		return 'text/html';			
	}
		
	/*
		stored for later: plain text version:
		
		--sfboundary
		Content-Type: text/plain; charset=us-ascii
		
		Form Name: <?php echo $form['sfname']; ?>
		
		Submitted From Page: <?php echo $form['submission-page']; ?>
		
		
		Submitted Info:
		
		<?php foreach( $fields as $field ): ?>
				<?php echo ( $field['display'] != '' ) ? $field['display'] : $field['field']; ?> : <?php echo $field['value']; ?>
		<?php endforeach; ?>
		
		--sfboundary
		Content-Type: text/html; charset=utf-8
		
	*/
	
	
	private function findAdvancedSetting( $formFields = null , $_FIND = null ){
		
		if(empty($formFields)) return;
		
		$res = array();
		
		$fieldKey = '';
		
		foreach($formFields as $key => $value):
		
			switch ($_FIND):
				
				case 'conditional_confirmation':#
					if(property_exists($value->input->settings, 'conditional_confirmation')):
						$temp = $formFields[$key];
						$fieldKey = $key;
						if(property_exists($value->input->settings, 'ignore_first')):
							$count = 1;
							foreach($temp->input->values as $k => $v):
								if( $count > 1 )continue;
								unset($temp->input->values->$k);
								$count++;
							endforeach;
						endif;
						$res = $temp->input->values;
					endif;
				break;
				
				case 'use_as_confirmation_subject':#
					if(property_exists($value->input->settings, 'use_as_confirmation_subject')):
						$temp = $formFields[$key];
						$fieldKey = $key;
						$res = array('key'=>$key);
					endif;
				break;
			
			endswitch;
		endforeach;
		
		$custom = array();
		if(!empty($res)):
			foreach($res as $k => $v ):
				switch ($_FIND):
					case 'conditional_confirmation':#
						if(!empty($v->value)):
							$custom[$fieldKey][sanitize_title($v->value)] = array( 'message' => $v->message , 'subject' => $v->subject );
						endif;
					break;
					case 'use_as_confirmation_subject':#
						$custom = array('field'=>$v);
					break;
				endswitch;
			endforeach;
		endif;	

		return $custom;
	}	

}

