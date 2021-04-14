<?php  
namespace aform\core;
class db{
	
	////////////////////////////////////////////////////////////////////
	public $autoloads;//do not delete, this triggers our include method to load this class
	////////////////////////////////////////////////////////////////////
	
	public $settings;

	public function __construct(){
		global $aFormDb;
		$aFormDb = $this;
	}

	public function adminFieldsPrepareBeforeSave( $data = null ){

		if($data == null ) return array();
		
		if(!empty($data['input_values'])):
			$data['input_values'] = array_map('stripslashes_deep', $data['input_values']);
			$data['input_values'] = json_encode( $data['input_values'] );
		endif;
	
		if(!empty($data['input_settings'])):
			$data['input_settings'] = array_map('stripslashes_deep', $data['input_settings']);
			$data['input_settings'] = json_encode( $data['input_settings'] );
		endif;

		return $data;
	}

	public function saveFields($dataArr ){
				

		foreach($dataArr as $data):

			$data = $this->adminFieldsPrepareBeforeSave( $data );			
			
			if(empty($data['ID'])):
				$this->insertField($data);
			else:
				$this->updateField($data);
			endif;
		endforeach;
	
	}
	/*
		$formID = $post->ID || $post_id
		ORDER BY input_order (0,1,2,3)
	*/
	public function getFields( $formID = null , $orderBy = 'input_order' , $select = '*' , $outputType = OBJECT ){
		global $wpdb;

		$fieldsTable = $this->wpdb['fields'];
		
		$results = $wpdb->get_results( "SELECT $select FROM {$fieldsTable} WHERE form_id = {$formID} ORDER BY {$orderBy} ", $outputType );	
		if(!empty($results)):
			return $results;
		else:
			return null;
		endif;
	}

	// getFields + getFormSettings + post
	public function getForm( $value = null ){
		global $wpdb;
		$formData = null;
		if( is_string( $value )):
			$formData = get_page_by_path( $value , OBJECT , $this->settings['cptname'] )?:null;
			if( empty($formData ) ) return ; 
			$formData->settings = null;
			$formData->fields = null;
			if(empty($formData)) return;
			$formID = $formData->ID;
			/* GET FORM FIELDS */
			$formFields = $this->getFields( $formID );
			if(!empty($formFields)):
				$formData->fields = $formFields;
			endif;
			/* GET FORM SETTINGS */
			$formSettings = $this->getSettings( $formID );
			$formData->settings = $formSettings;
		endif;
		return $formData;	
	}

	public function getFormPosts( $select = '*' , $status = 'publish'){
		global $wpdb;
		$posttype = aFormSettings('cptname');
		$querystring = "SELECT {$select} FROM {$wpdb->prefix}posts WHERE post_type = '{$posttype}' AND post_status = '{$status}' ORDER BY post_date DESC";
		$results = $wpdb->get_results( $querystring , OBJECT);
		return ( !empty($results) ) ? $results : array() ;
	}
	
	public function findField( $fieldID = null ){
		global $wpdb;
		$fieldsTable = $this->wpdb['fields'];
		$results = $wpdb->get_results( "SELECT * FROM {$fieldsTable} WHERE ID = {$fieldID}", OBJECT );
		$results = (!empty($results)) ? $results[0] : null;	
		if(!empty($results)):
			return $results;
		else:
			return null;
		endif;
	}
	/*
		
	*/
	public function insertField( $data = null){
		global $wpdb;
		$fieldsTable = $this->wpdb['fields'];
		unset($data['ID']);
		$wpdb->insert( $fieldsTable , $data , array("%s") );
		return $wpdb->insert_id;
	}

	public function updateField($data){

		global $wpdb;
		$fieldsTable = $this->wpdb['fields'];
		$id = $data['ID'];
		unset($data['ID']);
		//print_r($data);
		$wpdb->update(
			$fieldsTable,
			$data,
			array( 'ID' => $id )				
		);			
	}

	/***
	* DELETE form fields & settings
	****/

	# Delete form fields + settings / used in wpinit -> add_action('delete_post') && 
	
	public function deletePost( $formID = null ){
		$this->deleteFields( $formID );
		$this->deleteForm( $formID );
	}

	public function deleteFields( $formID = null ){
		global $wpdb;

		$fieldsTable = $this->wpdb['fields'];
		
		$post_type = ( isset( $_GET['post_type'] ) ) ? $_GET['post_type'] : get_post_type();
		
		if( $post_type != $this->settings['cptname'] ) return;
		
		if( $formID == null ) return;
		
		$fields = $this->getFields($formID);
		
		if( empty( $fields ) ) return;
		
		foreach ($fields as $field ) :
			$this->deleteField( $field->ID );
		endforeach;
			
	}
			
	public function saveSettings( $postID , $data = array() , $isJSON = false ) {
		global $wpdb;
		$table = $this->wpdb['forms'];
		
		if( $isJSON == false ):
			$data = array_map('stripslashes_deep', $data);
			$data = json_encode($data);
		endif;

		$wpdb->replace(
			$table,
			$sqldata = array(
				'form_id'	=> $postID,
				'settings'	=> $data
			)
		);			
	}
	
	public function getSettings( $form_id = null , $outputType = OBJECT ) {
		global $wpdb;
		$table = $this->wpdb['forms'];
		
		$results = $wpdb->get_results( "SELECT * FROM {$table} WHERE form_id = {$form_id} LIMIT 1", $outputType );
		
		return ( !empty($results) ) ? $results[0] : array();
		
	}
	public function findFormSetting( $value = ''){
		
		global $wpdb;
		
		$table = $this->wpdb['forms'];
		/*
			'settings' column in db holds JSON 
		*/
		$results = $wpdb->get_results( "SELECT form_id FROM {$table} WHERE settings LIKE '%{$value}%'", OBJECT );

		return $results;
	}
	/**
	* deleteField
	* 
	* Used to delete field from wp_seaforms_reusablefields & wp_seaforms_fields
	*/
	public function deleteField( $id = null , $table = 'fields'){
		global $wpdb;
		$fieldsTable = $this->wpdb[ $table ];
		$ID = array( 'ID' => $id );
		return $wpdb->delete( $fieldsTable , $ID );
	}

	// public function deleteSubmission( $id = null ){
	// 	global $wpdb;
	// 	$submissionsTable = $this->wpdb['submissions'];
	// 	$ID = array( 'ID' => $id );
	// 	$deleted = $wpdb->delete( $submissionsTable , $ID );
	// 	return $deleted;
	// }

	public function deleteForm( $formID = null ){
		global $wpdb;

		$formsTable = $this->wpdb['forms'];
		
		$post_type = ( isset( $_GET['post_type'] ) ) ? $_GET['post_type'] : get_post_type();
		
		if( $post_type != $this->settings['cptname'] ) return;
		
		if( $formID == null ) return;

		$form_id = array( 'form_id' => $formID );
		
		$wpdb->delete( $formsTable , $form_id );
	}
	public function getSubmission( $id , $getcount = false ){
		global $wpdb;
		$submissionsTable = $this->wpdb['submissions'];
		$results = $wpdb->get_results(" SELECT * FROM {$submissionsTable} WHERE id = {$id}");
		return ( !empty($results) ) ? $results[0] : array() ;
	}
	
	// public function getSubmissionsMeta( $submission_id = null ){
	// 	global $wpdb;
		
	// 	if( $submission_id == null ) return new \stdClass();

	// 	$submissionsMetaTable = $this->wpdb['submissionsmeta'];

	// 	$sql = "SELECT * FROM {$submissionsMetaTable} WHERE submission_id = {$submission_id}";
		
	// 	$results = $wpdb->get_results( $sql );

	// 	return $results;

	// }
	/* depracated */
	// public function getFormSubmissions( $args = array() ) {
	// 	global $wpdb;
	// 	$submissionsTable = $this->wpdb['submissions'];
		
	// 	/* SET DEFAULTS */
	// 	$id = ( isset($args['form_id']) ) ? $args['form_id'] : '';
	// 	$limit = ( isset($args['limit']) ) ? $args['limit'] : '5';
	// 	$page = ( isset($args['page']) ) ? $args['page'] : '';
		
	// 	/*
	// 		Add condition to get count of submitted for single form 
			
	// 	*/
	// 	if( ( isset($args['onlycount']) && $args['onlycount'] == true ) && !empty($id) ):// only accepts single id
	// 			$sql = "SELECT COUNT(*) FROM {$submissionsTable} WHERE form_id = {$id}";
	// 			$results = $wpdb->get_var( $sql );
	// 		return ($results) ? $results : 0 ;
	// 	endif;
	// 	/*
	// 	   else : we run our original function
	// 	*/
	// 	$offset = '';
	// 	if(!empty($page)):
	// 		$offset = ($page - 1) * $limit;
	// 		$limit = "LIMIT " . $limit ." OFFSET {$offset}";
	// 	else:
	// 		$limit = "LIMIT " . $limit;
	// 	endif;
	// 	if( isset( $args['limit'] ) && $args['limit'] == false ){
	// 		$limit = '';
	// 	}

	// 	$where = array();
	// 	$whereSQL = '';		

	// 	//set up $where array of clauses
	// 	if ( $id != '' ) $where['form_id'] = $id;
		
	// 	//loop through $where to build $whereSQL string for get_results
	// 	if( !empty($where) ):
	// 		$whereSQL = "WHERE ";
	// 		$keys = array_keys($where);
	// 		for( $i = 0; $i < count($where); $i++ ):							
	// 			$val = $where[$keys[$i]];				
	// 			$whereSQL .= ($i == 0 ) ? ( preg_match('/,/i', $val ) && $keys[$i] == 'form_id') ? $keys[$i] . " IN($val) " : $keys[$i] . " = " . $val : " AND " . $keys[$i] . " = " . $val;
	// 		endfor;
	// 	endif;
		
	// 	$BETWEEN = '';
	// 	if(isset($args['between'])):
	// 		$from = ( !empty( $args['between']['from'] ) ) ? new \DateTime( $args['between']['from'] ) : new \DateTime( '01/01/1985' ) ;
	// 		$from = $from->format('Y-m-d H:i:s');
	// 		$to   = ( !empty( $args['between']['to'] ) ) ? new \DateTime( $args['between']['to'] ) : null ;
	// 		if(!empty($to)):
	// 			$to->modify('+1 day');
	// 			$to = $to->format('Y-m-d H:i:s');
	// 			$BETWEEN = "AND s.date_time BETWEEN '{$from}' AND '{$to}'";
	// 		else:
	// 			$BETWEEN = "AND s.date_time BETWEEN '{$from}' AND NOW()";
	// 		endif;	
	// 	endif;

	// 	$ORDERBY = ( isset($args['orderby']) && !empty($args['orderby']) ) ? $args['orderby'] : 'date_time DESC';
		
	// 	$sql = "SELECT SQL_CALC_FOUND_ROWS s.id, s.email, s.date_time, s.form_data, p.post_title , p.post_name FROM {$submissionsTable} s INNER JOIN {$wpdb->posts} p ON p.ID = s.form_id 
	// 	{$whereSQL} {$BETWEEN} ORDER BY {$ORDERBY} {$limit}";
		


	// 	$results = $wpdb->get_results( $sql );
		

	// 	global $sfSubmissionsQueryMaxRows;
	// 	$sfSubmissionsQueryMaxRows = $wpdb->get_results('SELECT FOUND_ROWS()');// total rows
	// 	$sfSubmissionsQueryMaxRows = $sfSubmissionsQueryMaxRows[0];
	// 	$found_rows = 'FOUND_ROWS()';
	// 	$sfSubmissionsQueryMaxRows = $sfSubmissionsQueryMaxRows->$found_rows;
		

	// 	return ( !empty($results) ) ? $results : array() ;
		
	// }

	public function getSubmissionFields($postid , $limit = null){
		global $wpdb;

		$querystring = "SELECT * FROM {$wpdb->prefix}postmeta WHERE meta_key LIKE '_aformfield__%' AND post_id = {$postid} ORDER BY meta_id ASC";
		if(!empty($limit) && is_numeric($limit)):
			$querystring .= " LIMIT {$limit}";
		endif;
		$results = $wpdb->get_results( $querystring , OBJECT);

		return ( !empty($results) ) ? $results : array() ;
	}

	public function getFormSubmissionCount($metavalue , $metakey = 'aform_id' , $status = 'publish'){
		global $wpdb;
		$querystring = "SELECT COUNT(*) FROM {$wpdb->prefix}postmeta 
		INNER JOIN {$wpdb->prefix}posts ON {$wpdb->prefix}postmeta.post_id = {$wpdb->prefix}posts.ID
		WHERE meta_key = '{$metakey}' AND meta_value = {$metavalue} AND {$wpdb->prefix}posts.post_status = '{$status}'";
		$count = $wpdb->get_var($querystring);
		return $count;
	}

	
	public function getFormCount( $dates = array() ) {
		
		global $wpdb;
		$submissionsTable = $this->wpdb['submissions'];
		
		if ( empty($dates) || !isset($dates['from']) || !isset($dates['to']) ):
			return;
		endif;
		
		$from = new \DateTime( $dates['from'] );
		$to = new \DateTime( $dates['to'] );
		$to->modify( '+1 day' );

		//we'll need this later
		$dateDiff = $from->diff($to);

		$from = $from->format('Y-m-d');
		$to = $to->format('Y-m-d');		
		
		
		$getdates = "SELECT DATE_FORMAT(date_time, '%m-%d-%Y' ) as 'date', COUNT(ID) as 'count'  FROM {$submissionsTable} WHERE `date_time` BETWEEN '$from' and '$to'
		GROUP BY DATE_FORMAT(date_time, '%m-%d-%Y')";

		/*$getdates = "SELECT DATE_FORMAT(date_time, '%m-%d-%Y' ), COUNT(ID)  FROM `wp_seaforms_submissions` WHERE `date_time` BETWEEN '2015-09-01 11:59:59' and '2015-09-04 11:59:59'
		GROUP BY DATE_FORMAT(date_time, '%m-%d-%Y')
		LIMIT 0, 30";*/
		
		$results = $wpdb->get_results( $getdates );
		
		return $this->formatCount( $results, $from, $to, $dateDiff );
	}


	public function formatCount( $results, $from, $to, $dateDiff ) {
		
		$formCount = array();
		
		foreach( $results as $r ) {			
			$formCount[$r->date] = $r->count;			
		}
		
		
		if ( intval( $dateDiff->format('%a') ) == count($formCount) ):
			return $formCount;
			
		else:
			$from = new \DateTime( $from );
			$to = new \DateTime( $to );
			
			while( $from != $to ):
			
				$f = $from->format('m-d-Y');
				if( !isset($formCount[$f]) ):
					$formCount[$f] = 0;
				endif;
				
			
			$from->modify( '+1 day' );
			endwhile;
		
			ksort($formCount);
			return $formCount;
		
		endif;
				
		
	}
	/**
	* Admin : Reusable fields 
	*/
	public function saveReusableField( $data = null ){
		
		if($data == null ) return array();
		
		global $wpdb;

		$fieldsTable = $this->wpdb['reusablefields'];
		
		$data = $this->adminFieldsPrepareBeforeSave( $data );
		
		$wpdb->insert( $fieldsTable , $data, array("%s") );
		
		return $wpdb;
	}
	public function getReusableFields($id = null){
		global $wpdb;
		$TABLE = $this->wpdb['reusablefields'];
		$WHERE = (!empty($id)) ? "WHERE ID={$id}" : "";
		$results = $wpdb->get_results( "SELECT * FROM {$TABLE} {$WHERE}", OBJECT );	
		if(!empty($results)):
			return $results;
		else:
			return null;
		endif;	
	}


}
