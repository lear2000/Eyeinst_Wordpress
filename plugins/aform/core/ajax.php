<?php  
namespace aform\core;
class ajax{
	
	////////////////////////////////////////////////////////////////////
	public $autoloads;//do not delete, this triggers our include method to load this class
	////////////////////////////////////////////////////////////////////
	
	public function __construct(){
		add_action( 'wp_ajax_afAjaxSwitch', array( &$this, 'ajaxSwitch' ) );

		add_action( 'wp_ajax_formSubmissions', array( &$this, 'form_submissions' ) );
		add_action( 'wp_ajax_dashSubsPagi' , array( &$this, 'dashSubsPagi' ) );
		add_action( 'wp_ajax_deleteSubmission', array( &$this, 'deleteSubmission' ) );
		add_action( 'wp_ajax_downloads_fs' , array(&$this , 'downloads_fs') );
		//add_action( 'wp_ajax_nopriv_downloads_fs', 'download_attachment' );
		/** 
		* REUSABLE FIELDS
		*/
		//add_action( 'wp_ajax_afCloneField' , array( &$this , 'cloneField' ));

	}
	public function ajaxSwitch(){
		$ajaxpost = $_POST;
		if(isset($ajaxpost['method'])):
			switch ($ajaxpost['method']):
				case 'createChoicesFromString':
					$this->createChoicesFromString($ajaxpost['choices'],$ajaxpost);
				break;
				case 'cloneField':
					$this->cloneField($ajaxpost);
				break;
				case 'newFormField':
					$this->newFormField($ajaxpost);
				break;
				case 'deleteFormField':
					$this->deleteFormField($ajaxpost);
				break;
				case 'createChoice':
					$this->createChoice($ajaxpost);
				break;
				case 'renderWPField':
					$this->renderWPField($ajaxpost);
				break;
			endswitch;
		endif;
		wp_die();
	}
	public function createChoicesFromString($val,$ajaxpost){
		$response = array('success'=>false);
		//'value:label';
		if(preg_match('/|/i',$val)):
			$response = array('success'=>true);
			$choices = explode('|',$val);
			$field = new field(); 
			$choiceHtml  = '';
			$count = $ajaxpost['count'];
			ob_start();
			foreach($choices as $choice):
				$choice  = explode(':',$choice);
				$value   = (isset($choice[0])) ? $choice[0] : '';
				$display = (isset($choice[1])) ? $choice[1] : $value;
				$field->renderFieldValue( $ajaxpost['fieldId'] , $count , $value , $display );
				$count++;
			endforeach;
			$choiceHtml = ob_get_contents();
			ob_end_clean();
			$response['html'] = $choiceHtml;
		endif;
		echo  wp_json_encode($response);
	}
	/** 
	* Reusable Fields 
	* 
	* Functions for cloning a form field
	* 
	* AJAX returns a serialized string & parsed with parst_str. 
	* Unused fields are removed : ID , form_id
	*/
	public function cloneField($ajaxpost){
		$results = array('success' => false );
		if(isset($ajaxpost['fields'])):
						
			parse_str( $ajaxpost['fields'] , $field );
			
			$field = $field['aform-fields'];

			$field = array_pop($field);
			
			unset($field['ID']);
			unset($field['form_id']);
			
			$field['input_order'] = '';
			
			if(isset($ajaxpost['fieldlabel'])):
				$field['field_label'] = $ajaxpost['fieldlabel'];
			endif;

			$data = $field;
			$saved = _AFORMDB()->saveReusableField( $data );
						
			if($saved):
				if(property_exists($saved , 'insert_id')):
					
					$reusableField = _AFORMDB()->getReusableFields($saved->insert_id);
					
					$html = null;
					if(!empty($reusableField)):
						$reusableField = array_pop($reusableField);
						$html = view::reusableField( $reusableField );
					endif;
					$results  = array(
						'success'   	=> true,
						'is_saved'  	=> true,
						'input_type' 	=> $field['input_type'],
						'ID'   			=> $saved->insert_id,
						'html'			=> $html
					);
					if(isset($ajaxpost['fieldlabel'])):
						$results['field_label'] = $ajaxpost['fieldlabel'];
					endif;
				endif;
			endif;
		endif;
		echo  wp_json_encode($results);
	}

	public function newFormField( $ajaxpost ){ //used for adding new field to back-end
		global $wp_query , $post , $query , $post_id;

		$fieldData     = new \StdClass;
		$field         = $ajaxpost['field'];
		$count   	   = (isset($ajaxpost['fieldcount'])) ? $ajaxpost['fieldcount'] : 1 ;
		$formID 	   	= $ajaxpost['aformPost']['ID'];
		$post    	   = get_post( $formID );

		if(isset($ajaxpost['reusableID'])):
			$getFieldData = _AFORMDB()->getReusableFields( $ajaxpost['reusableID'] );
			if(!empty($getFieldData)):
				
				$fieldData = array_pop($getFieldData);
				$fieldData->form_id = $formID;
				$fieldData->ID = "";
				$fieldData->input_order = "";
				unset($fieldData->field_label);
				
			endif;
		else:
			$fieldData = new \StdClass;
		endif;
		
		/** 
		* @since after 1.3.3
		*	parks field in aforms_fields table 
		*/
		
		$fieldData->ID = _AFORMDB()->insertField( array( 'input_type' => $field , 'form_id' => $post->ID ) );
		aformRender( $inputType = $field , $index = $count , $fieldData = $fieldData );
	}


	/**
	* deleteField
	* 
	* Used to delete field from wp_aforms_reusablefields & wp_aforms_fields
	*/
	public function deleteFormField( $ajaxpost ) { //deletes field on admin interface

		if( isset($ajaxpost['fieldID']) && $ajaxpost['fieldID'] != '' ):		
			$table = ( isset( $ajaxpost['t'] ) ) ? $ajaxpost['t'] : 'fields';
			$deleted = _AFORMDB()->deleteField( $ajaxpost['fieldID'] , $table );		
			if($deleted):
				wp_send_json_success();
			endif;
		endif;

		wp_send_json_error();
		
	}


	public function createChoice( $ajaxpost ) {
		
		$index  = (isset($ajaxpost['index'])) ? $ajaxpost['index'] : null ; //also fieldId
		$count  = (isset($ajaxpost['count'])) ? $ajaxpost['count'] : null ;
		$option = (isset($ajaxpost['option'])) ? $ajaxpost['option'] : null ;
		$inputtype = (isset($ajaxpost['inputtype'])) ? $ajaxpost['inputtype'] : null ;
			
		$fieldSettings = _AFORMDB()->findField($index);

		$field  = new field;
		$field->renderFieldValue( $index , $count , '' , '' , $option , '' , $fieldSettings);
				
	}
	/**
	* renderFieldOption
	* 
	* Used to add a New Field || Reusable Field
	*/
	public function renderWPField($ajaxpost){
		$field = (isset($ajaxpost['field'])) ? $ajaxpost['field'] : '';
		$fieldID = (isset($ajaxpost['id'])) ? $ajaxpost['id'] : '';
		switch ($field):
			case 'wysiwyg':
					wp_editor( '' , 'textAreaPH'.$fieldID , array(
						'wpautop' 		=> false,
						'textarea_name' => 'textAreaPH'.$fieldID,
						'tinymce'		=> false,
						'teeny'			=> true
					));
				break;
		endswitch;
	}

	////////////////////////////////
	////// DASHBOARD METHODS
	////////////////////////////////
	public function dashSubsPagi(){
		
		$page = isset($_POST['page']) ? $_POST['page'] : 1;
		$totalitems = isset( $_POST['totalitems'] ) ? $_POST['totalitems'] : '';
		$limit = isset( $_POST['limit'] ) ? $_POST['limit'] : 10 ;
		$adjacents = isset( $_POST['adjacents'] ) ? $_POST['adjacents'] : 1 ;
		$targetpage = isset( $_POST['targetpage'] ) ? $_POST['targetpage'] : '#';
		
		echo \aform\admin\dashboard\pagination::render( $page , $totalitems , $limit , $adjacents , $targetpage );
		
		wp_die();

	}
	public function deleteSubmission(){
		$id = (isset($_POST['id'])) ? $_POST['id'] : '';
		$deleted = _AFORMDB()->deleteSubmission( $id );
		$results = array(
			'success' => true,
			'deleted' => $deleted,
			);
		echo  wp_json_encode($results);
		wp_die();
	}

	
	public function form_submissions( $data ) {
		
		global $wpdb , $tableNames;

		$limit = ( isset($_POST['limit']) && $_POST['limit'] != '' ) ? $_POST['limit'] : 10;		
		$ids = ( isset($_POST['fieldID']) && $_POST['fieldID'] != '' ) ? $_POST['fieldID'] : '';		
		$page = ( isset($_POST['page']) && $_POST['page'] != '' ) ? $_POST['page'] : '';
		$fromTo = array( 
			'from' => ( isset($_POST['from']) ) ? $_POST['from'] : null , 
			'to'   => ( isset($_POST['to']) ) ? $_POST['to'] : null 
			);
		$defaultArray = array( 
			'form_id' => $ids, 
			'limit' => $limit , 
			'between' => $fromTo , 
			'page' => $page,
			);

		if( isset($_POST['orderby']) && $_POST['orderby'] != '' ):
			$defaultArray['orderby'] = $_POST['orderby'];
		endif;

		$results = _AFORMDB()->getFormSubmissions( $defaultArray );
		
		$submissionMetaTableName = $tableNames['submissionsmeta'];

		if($wpdb->get_var("SHOW TABLES LIKE '{$submissionMetaTableName}'") == $submissionMetaTableName ):

			foreach($results as $key => $result):
				$val = _AFORMDB()->getSubmissionsMeta( $result->id );
				if(!empty($val)):
					$results[$key]->submissionmeta = $val;
				endif;
			endforeach;	
		
		endif;


		// global is set in core/db.php
		global $afSubmissionsQueryMaxRows;
		$resultCount = count($results);
		$submissionsresults = new \stdClass();
		
		$submissionsresults->results =  $results;
		
		$submissionsresults->pages = 0;
		if( $resultCount < $afSubmissionsQueryMaxRows ):
			$pages = $afSubmissionsQueryMaxRows / $resultCount;
			$pages = (is_float($pages)) ? (floor($pages)+1) : $pages;
			$submissionsresults->pages = $pages;
			$submissionsresults->total = $afSubmissionsQueryMaxRows;
		endif;
		
		if( !empty($results) ):		
			wp_send_json_success( json_encode( $submissionsresults ) );
		else:
			wp_send_json_error();
		endif;		
		wp_die();
	}
	public function downloads_fs(){
		// make sure we add a nonce
		// send back the url so we can download
		if( isset($_POST['ajax']) && $_POST['ajax'] == true):
			unset($_POST['ajax']);
			$ajaxurl = $_POST['ajaxurl'];
			unset($_POST['ajaxurl']);
			echo $ajaxurl.'?'.http_build_query($_POST);
			wp_die();
		endif;
		/* single_submissions , single_form , all_forms*/
		$GLOBALS['_POST'] = $_GET;
		if( isset($_POST['datafor']) ):

			$datafor = $_POST['datafor'];
			switch ( $datafor ):
				case 'filtered':					
					$getsubmissionsArr = array( 
						'form_id' => (isset($_POST['fieldID'])) ? $_POST['fieldID'] : '', 
						'limit' => ( isset($_POST['limit']) ) ? filter_var($_POST['limit'], FILTER_VALIDATE_BOOLEAN) : false, 
						'between' => array('from'=> (isset($_POST['from'])) ? $_POST['from'] : '', 'to'=> (isset($_POST['to'])) ? $_POST['to'] : '')
						);
						if( isset($_POST['orderby']) && $_POST['orderby'] != '' ):
							$getsubmissionsArr['orderby'] = $_POST['orderby'];
						endif;
					$submissions = _AFORMDB()->getFormSubmissions( $getsubmissionsArr );
					foreach($submissions as $s => $submission):
						$form_data = json_decode($submission->form_data);// make data usable
						$newFields = new \stdClass();
						//create new field names to prevent orphaned data /* field-{fieldid}*/
						if(!isset($form_data->fields))continue;
						foreach($form_data->fields as $d => $field):
							
							$newid = explode('-', $d);
							$newid = array_reverse($newid);
							$newid = 'field-'.$newid[0];

							if(empty($field->display)):
								$form_data->fields->$d->display = $d;
								$field->display = $d;
							endif;
							$newFields->$newid = $field;

						endforeach;
						$submissions[$s]->form_data = $form_data;
						$submissions[$s]->form_data->fields = $newFields;
					endforeach;	


					$newSubmissionsArr = array();
					foreach($submissions as $index => $submission):
						
						// create an array for the form;
						if(!isset($newSubmissionsArr[$submission->post_name])):
							$newSubmissionsArr[$submission->post_name] = array();
						endif;
						// add the default labels;
						if(!array_key_exists('labels', $newSubmissionsArr[$submission->post_name])):
							$newSubmissionsArr[$submission->post_name]['labels'] = array('Form Name'=>'Form Name' , 'Data Time'=>'Data Time' , 'Submitter Email'=>'Submitter Email');
						endif;
						
						$newSubmissionsArr[$submission->post_name]['submissions'][$index] = $submission;
						
					endforeach;

						
					/* BUILD OUR CSV FILE*/
					header('Content-Type: text/csv; charset=utf-8');
					header('Content-Disposition: attachment; filename=afdata.csv');
					header("Cache-control: private");
					header("Pragma: no-cache");
					header("Expires: 0");

					foreach ($newSubmissionsArr as $formname => $form):
						$formdata = _AFORMDB()->getForm($formname);
						$formfieldlabels = array();
						foreach($formdata->fields as $field):	
							if($field->input_type != 'submitbutton'):
								$input_settings = json_decode($field->input_settings);
								$fieldlabel2 = ($input_settings->display_name) ? $input_settings->display_name : 'field-'.$field->ID;
								$fieldlabel = 'field-'.$field->ID;
								$formfieldlabels[$fieldlabel] = $fieldlabel2;
							endif;
						endforeach;

						$newlabels = array_merge($form['labels'] ,$formfieldlabels);
						
						$output = fopen('php://output', 'w');
						fputcsv($output, $newlabels );
						
						foreach($form['submissions'] as $i => $v):
							if(!isset($v->form_data->fields))continue;
							$staticvalues = array($v->post_name , $v->date_time , $v->email);
							
							$tempValues = array_merge($staticvalues , $formfieldlabels);
							$values2 = array();
							
							foreach($v->form_data->fields as $i => $f):
								$values2[$i] = $f->value;
							endforeach;//
							
							foreach($tempValues as $ii => $vv):
								if(array_key_exists($ii, $values2) ):
									$tempValues[$ii] = $values2[$ii];
								elseif( array_key_exists($ii, $staticvalues) ):
										$tempValues[$ii] = $staticvalues[$ii];
								else:
									$tempValues[$ii] = '-----';
								endif;
							endforeach;//
							fputcsv($output, $tempValues);
						
						endforeach;							
					endforeach;


				break;
				
				case 'single_submission':
					$submission = _AFORMDB()->getSubmission($_POST['query']);
					$formData = json_decode($submission->form_data);
					$heading = array('Form');
					foreach($formData->fields as $field):
						$heading[] = $field->display;
					endforeach;
					header('Content-Type: text/csv; charset=utf-8');
					header('Content-Disposition: attachment; filename=data.csv');
					header("Cache-control: private");
			        header("Pragma: no-cache");
			        header("Expires: 0");
					$output = fopen('php://output', 'w');
					fputcsv($output, $heading);
					$values = array($formData->form->afname);
					foreach($formData->fields as $field):
						$values[] = $field->value;
					endforeach;
					fputcsv($output, $values);
				break;
				/**/
				case 'single_form':
					$submissions = _AFORMDB()->getFormSubmissions(array('form_id'=>$_POST['query'] , 'limit'=> false ));
					$formdata = array();
					header('Content-Type: text/csv; charset=utf-8');
					header('Content-Disposition: attachment; filename=data.csv');
					header("Cache-control: private");
			        header("Pragma: no-cache");
			        header("Expires: 0");
					foreach($submissions as $submission):
						$formdata[] = json_decode($submission->form_data);
					endforeach;
					$heading = array('Form');
					foreach($formdata[0]->fields as $field):
						$heading[] = $field->display;
					endforeach;
					$output = fopen('php://output', 'w');
					fputcsv($output, $heading);
					foreach($formdata as $form):
						$values = array($form->form->afname);
						foreach($form->fields as $field):
							$values[] = $field->value;
						endforeach;
						fputcsv($output, $values);
					endforeach;
					
				break;
				
				case 'all_forms':
				
				break;
			endswitch;
		endif;
		wp_die();
	}


	

}