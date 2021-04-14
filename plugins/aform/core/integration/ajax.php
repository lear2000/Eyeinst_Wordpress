<?php
namespace aform\core\integration;

class ajax{

	////////////////////////////////////////////////////////////////////
	public $autoloads;//do not delete, this triggers our include method to load this class
	////////////////////////////////////////////////////////////////////

	public function __construct(){
		add_action('wp_ajax_aformintegration' , array($this,'ajaxSwitch'));
	}

	public function ajaxSwitch(){

		if(isset($_POST['method']) && ( !empty($_POST['method']) || $_POST['method'] != '')):
			$method = $_POST['method'];
			if(method_exists($this, $method)):
				$this->$method($_POST);
				return;
			endif;			
		endif;
		wp_send_json_success(array(
			'action' => 'no method found'
		));
		die();
	}
	public function getFormFields($_post){
		if(isset($_post['id'])):
			$fields = get_transient( 'fields_' . $_post['id'] );
			if($fields):
				$isTransient = 1;
			else:
				$fields = _AFORMDB()->getFields($_post['id']);
				foreach($fields as $field):
					$field->input_settings = json_decode( $field->input_settings );
					$field->input_name = $field->input_settings->display_name;
				endforeach;
				$setTransient = set_transient( 'fields_' . $_post['id'] , $fields , 365 * DAY_IN_SECONDS );
				$isTransient = 0;
			endif;
			wp_send_json_success(array(
				'fields' => $fields,
				'transient' => $isTransient
			));
		endif;
		wp_send_json_success(array(
			'fields' => [],
			'transient' => 0
		));
	}
	public function mailchimplist($_post){
		//
		if(isset($_post['key']) && !empty($_post['key'])):
			$apikey = $_post['key'];//'933550c1a5e4ff3ab0d0f63464b99962-us1';
			$location = array_pop(explode('-',$apikey));
			$key = array_shift(explode('-',$apikey));
			
			//MAILCHIMP SETTINGS
			$apiurl = "https://{$location}.api.mailchimp.com/3.0";
			$auth = base64_encode( "use:{$key}" );
			$list = "/lists";
			delete_transient( $apikey.$list );
			$listTransient = get_transient($apikey.$list);
			$failuremessage = '';
			if($listTransient):
				//does nothing
				$isTransient = 1;
			else:
				$ch = curl_init();
				curl_setopt($ch, CURLOPT_URL, $apiurl . $list );
				curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json',"Authorization: Basic {$auth}"));
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
				$result = curl_exec($ch);
				$result = json_decode($result);
				if(isset($result->lists)):
					$setTransient = set_transient( $apikey.$list , $result , 365 * DAY_IN_SECONDS );
				else:
					$failuremessage = 'Invalid API Key';
				endif;
				$isTransient = 0;
				$listTransient = $result;
			endif;
			
			if(!empty($listTransient)):
				$response = array(
					'mc' => $listTransient,
					'key' => $apikey.$list,
					'is_transient' => $isTransient
				);
				if($failuremessage != ''):
					$response['fail_message'] = $failuremessage;
 				endif;
				wp_send_json_success($response);
			endif;
		endif;

	}	

	public function mailchimplistdata($_post){
		//https://usX.api.mailchimp.com/3.0/lists/57afe96172/merge-fields

		if(isset($_post['key']) && !empty($_post['key'])):
			$apikey = $_post['key'];//'933550c1a5e4ff3ab0d0f63464b99962-us1';
			$location = array_pop(explode('-',$apikey));
			$key = array_shift(explode('-',$apikey));			
			$listId = $_post['listid'];

			//MAILCHIMP SETTINGS
			$apiurl = "https://{$location}.api.mailchimp.com/3.0";
			$auth = base64_encode( "use:{$key}" );
			$list = "/lists/{$listId}/merge-fields";

			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $apiurl . $list );
			curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json',"Authorization: Basic {$auth}"));
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			$result1 = curl_exec($ch);
			$result1 = json_decode($result1);

			$list = "/lists/{$listId}/interest-categories";
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $apiurl . $list );
			curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json',"Authorization: Basic {$auth}"));
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			$result2 = curl_exec($ch);
			$result2 = json_decode($result2);

			
			$fieldsTransient = get_transient($listId);
			if($fieldsTransient):
				//do nothing
				$isTransient = 1;
			else:
				$result = array(
					'fields' => $result1,
					'interest' => $result2
				);
				$fieldsTransient = $result;
				$fieldsTransientSet = set_transient( $listId , $fieldsTransient , 365 * DAY_IN_SECONDS );
				$isTransient = 0;
			endif;
			
			wp_send_json_success(array(
				'mc' => $fieldsTransient,
				'is_transient' => $isTransient
			));

		endif;

	}

}