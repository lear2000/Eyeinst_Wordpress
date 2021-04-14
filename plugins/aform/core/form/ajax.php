<?php
namespace aform\core\form;

class ajax{
	////////////////////////////////////////////////////////////////////
	public $autoloads;//do not delete, this triggers our include method to load this class
	////////////////////////////////////////////////////////////////////
	
	
	public function __construct(){
		add_action( 'wp_ajax_nopriv_aformPublic' , array( &$this , 'ajaxSwitch' ) );	
		add_action( 'wp_ajax_aformPublic' , array( &$this , 'ajaxSwitch' ) );
	}

	function ajaxSwitch(){
		$formPost = $_POST;
		if(isset($formPost['method'])):
			$method = $formPost['method'];
			if(method_exists(__CLASS__, $method)):
				$this->$method($formPost);
			endif;
		endif;
		wp_die();
	}
	private function preparepost($formPost){
		$rawData = json_decode(stripslashes( $formPost['form'] ), true );
		$formData = array();
		if( count($rawData) > 0 ):
			foreach($rawData as $r):
				$name = ( preg_match('/af\[([a-z0-9-_]+)]/i', $r['name'], $matches ) ) ? $matches[1] : $r['name'];
				$isArray = false;
				if( preg_match('/af\[([a-z0-9-_]+)](\[(.*)\])/i', $r['name'] ) ){
					$isArray = true;
				}
				if( array_key_exists($name , $formData) ):
					if(is_array($formData[ $name ])):
						array_push($formData[$name], $r['value']);
					endif;				
				else:
					if($isArray):
						$formData[ $name ] = array($r['value']);
					else:
						$formData[ $name ] = $r['value'];
					endif;
				endif;
			endforeach;
		endif;
		
		if(isset($formPost['utmtracking'])):
			$formData['utmtracking'] = json_decode(stripslashes( $formPost['utmtracking'] ), true );
		endif;

		return $formData; 
	}

	private function masterSubmit($FORMPOSTDATA=null){
		$form = \aform\core\form::get_instance( $FORMPOSTDATA['afname'] , true );
		if(!empty($form)):
			$formHandler = $form->submit();
			//get status of form
			$status = $formHandler->getProperty('formStatus');
			if( $status == 'success' ):
				//success!! redirect or ajax response :)
				$response = $formHandler->getProperty('successResponse');
				wp_send_json_success( json_encode($response) );
			else:
				$response = $formHandler->getProperty('successResponse');
				wp_send_json_error($response);
			endif;
		endif;
	}
	/**
	* methods below take advantage of fileReader , File, formData , Blob 
	**/
	private function dolphinValidatesFile($formPost){

		$postFile = $formPost;
		$realtype = 'unknown';
		if(isset($postFile['filedata']) && !empty($postFile['filedata'])):
			$filedata = explode( ';base64,', $postFile['filedata'] );
			if(isset($filedata[1]) && !empty($filedata[1])):
				
				$filedata = base64_decode( $filedata[1] );
				$finfo = new \finfo(FILEINFO_MIME_TYPE);
				$servertype = $finfo->buffer($filedata);
				$clienttype = (isset($postFile['filetype'])) ? $postFile['filetype'] : '';
				$filename = (isset($postFile['filename'])) ? $postFile['filename'] : '';

				$realtype = fileupload::checkMime($servertype , $clienttype , $filename);
			endif;
		endif;
		wp_send_json_success(array('mime'=>$realtype));
	}
	/* dolphins are fast ? */
	//handles file uploads + form submission
	public function dolphin(){
		// used by modern browsers
		// file validation has already occurred so we don't do it here.
		$filesUploaded = array();
		$preparedPost = $this->preparepost($_POST);//holds posted data, not files.

		/* prepares all the files if any */
		if(isset($_FILES[AF_FIELDNAME_PREFIX]) && !empty($_FILES[AF_FIELDNAME_PREFIX])):			
			$preparedFiles = fileupload::prepareFiles( $fileData = $_FILES[AF_FIELDNAME_PREFIX] , $r = $_POST , $isAjax = true );
			if(!empty( $preparedFiles ) ):
				$files = $preparedFiles;
				$filecount = count($files);
				$uploads = fileupload::ajaxmove($files);	
				if(!empty($uploads)):
					foreach($uploads as $field => $urls ):
						$urlslist  = implode(", ", $urls);
						$filesUploaded[] = array('name'=> $field , 'value' => $urlslist );
					endforeach;
				endif;
			endif;
		endif;

		if(!empty($filesUploaded)):
			foreach($filesUploaded as $fieldFile):
				$preparedPost[$fieldFile['name']] = $fieldFile['value'];
			endforeach;
		endif;

		if( isset( $preparedPost['afname'] ) && !empty($preparedPost['afname'])):
			//if(!isset($formPost[AF_FIELDNAME_PREFIX])) return ;
				$preparedPost['ajax'] = true ;
				$FORMPOSTDATA = $preparedPost;
				
				$_POST = array(AF_FIELDNAME_PREFIX => $FORMPOSTDATA );//redefine our post data
				$this->masterSubmit($FORMPOSTDATA);
		endif;

	}
	
	
	/**
	* The Methods below are for older browsers that don't support 
	* JS - FileReader , File , formData , Blob API 
	**/
	
	/* 
		this is for old browser support
		this is slower... since it needs the entire file
	*/
	private function whaleValidatesFile(){
		$mimesize = array('mime'=>'unknown','filesize'=>0);
		if(!empty($_FILES[AF_FIELDNAME_PREFIX])):
			$preparedFiles = fileupload::prepareFiles( $fileData = $_FILES[AF_FIELDNAME_PREFIX] , $r = $_POST , $isAjax = true );
			
			if(!empty($preparedFiles)):
				
				$file = array_shift($preparedFiles);
				$finfo = new \finfo(FILEINFO_MIME_TYPE);
				$servertype = $finfo->file($file['tmp_name']);	
				
				$realtype = fileupload::checkMime($servertype , $file['type'] , $file['name']);
				$mimesize['mime'] = $realtype;
				$mimesize['filesize'] = $file['size'];

			endif;
		endif;
		wp_send_json_success($mimesize);
	}

}