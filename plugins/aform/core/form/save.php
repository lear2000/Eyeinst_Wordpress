<?php
namespace aform\core\form;

class save{
	
	public function __construct(){}

	public function submissionMeta( $formSettings=null , $insertId=null , $submissionMetaData = null ){
		global $wpdb , $tableNames;
		if( $formSettings == null || $insertId == null ) return;
		$submissionMetaTableName = $tableNames['submissionsmeta'];
		if($wpdb->get_var("SHOW TABLES LIKE '{$submissionMetaTableName}'") != $submissionMetaTableName) return;
		//tracking data
		if(isset($formSettings->include_campaign_trackingdata) && isset($submissionMetaData['utmtracking'])):
			
				$newArray = array();
				foreach ( $submissionMetaData['utmtracking'] as $key => $value ): 
				 	$key = str_replace('utm_', '', $key);
					$newArray[$key] = $value; 
				endforeach;				
				$insertMetaArr = array(
					'submission_id' => $insertId, 
					'meta_type'		=> 'tracking',
					'meta_key'		=> 'campaign',
					'meta_value'	=> json_encode( $newArray )
				);
				$wpdb->insert( $tableNames['submissionsmeta'] , $insertMetaArr , array('%d','%s','%s','%s') );
		endif;
	}


	public function submission($handlerInstance=null){
		global $wpdb , $tableNames;

		if(empty($handlerInstance)) return;

		$insert 				= null;
		$formInstance		= $handlerInstance->getProperty('formInstance');
		$formdata 			= $handlerInstance->getProperty('postedFormDataClean');
		$formid 				= $formInstance->afid;
		$formSettings 		= $formInstance->formSettings;
		$fields 				= $formInstance->form->fields;
		$email 				= afGetAOV( $formSettings , 'primary_email_recipient' );
		$cm 					= afGetAOV( $formSettings , 'cmIntegration' );//campaignMonitor is active?		
		$cmIntegrationIsActive 	= helper::fieldHaystack( $formdata['fields'] , $cm );

		/**
		 * 
		 * Campaign Monitor
		 * 
		*/ 
		if( $cmIntegrationIsActive && $email ):
			$mappings 	= array();
			$cmAPI 		= $fields[$cm];			
			$api 		= $cmAPI->input->settings->cmAPI;//API KEY
			$listID 	= $cmAPI->input->settings->cmListID;//LIST ID
			
			if(!empty($api) || !empty($listID)):
				if(!empty($fields)):
					foreach( $fields as $field):
						if( isset( $field->input->settings->send_to_CM ) && $field->input->settings->CM_field != '' ):
							$mappings[$field->input->settings->CM_field] = $field->postedFormData;
						endif;
					endforeach;
					$cmAPI->sendToAPI( $api, $listID, $email, $mappings );
				endif;
			endif;
		endif;
		/* END OF CM */

		if( isset($formdata['ajax'])): unset($formdata['ajax']); endif;
		
		if(isset($formdata['fields'])):
			foreach($formdata['fields'] as $key => $field):
				if($field['fieldtype']=='text'):
					unset($formdata['fields'][$key]);
				endif;
			endforeach;
		endif;
		
		/**
		 * remove slashes. to prevent double slashing... only freddy can do that
		*/
		if(!empty($formdata['fields'])):
			foreach($formdata['fields'] as $key => $arr):
				$formdata['fields'][$key] = array_map('stripslashes_deep', $arr );
			endforeach;
		endif;
		
		if(isset($formSettings->include_client_ip)):
			$formdata['form']['client_ip'] = $_SERVER['REMOTE_ADDR'];
		endif;
		
		$submissionMetaData = array();
		if(isset($formdata['form']['utmtracking']) && !empty($formdata['form']['utmtracking'])):
			$submissionMetaData['utmtracking'] = $formdata['form']['utmtracking'];
			unset($formdata['form']['utmtracking']);
		endif;

		
		$cptname = aFormSettings('cptname');
		$insertID = wp_insert_post( array(
    		'post_content' 	=> maybe_serialize($formdata),
    		'post_parent'		=> $formid,
    		'post_status'   	=> 'publish',
   		'post_type' 		=> "{$cptname}_sub"
		));
		
		wp_update_post(array(
			'ID' 				=> $insertID,
			'post_title' 	=> $insertID,
		));
		add_post_meta( $insertID , 'aform' , "aform_{$formid}" );
		add_post_meta( $insertID , 'aform_id' , $formid );

		foreach($formdata['form'] as $k => $v):
			if(!in_array($k , array('form-submits','thefaxnumber9tX4bPz','_afnonce','afname','utmtracking','afid'))):
				add_post_meta( $insertID , $k , $v );
			endif;
		endforeach;
		//each field gets a meta
		if(isset($formdata['fields']) && !empty($formdata['fields'])):
			foreach($formdata['fields'] as $k => $v):
				$boom = explode('-',$k);
				$fieldid = array_pop($boom);
				add_post_meta($insertID , "_aformfield__{$fieldid}" , $v );	
			endforeach;
		endif;

		//include utm tracking data
		if(isset($formSettings->include_campaign_trackingdata) && isset($submissionMetaData['utmtracking'])):
			update_post_meta($insertID , "has_utmtracking" , 'utmtracking' );
			update_post_meta($insertID , "utmtracking" , $submissionMetaData['utmtracking'] );	
		endif;

		
		return is_numeric($insertID) ? $insertID : 0;
	}

}