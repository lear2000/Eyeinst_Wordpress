<?php
namespace aform\core;
class submissions{
	public $gotFields;
	public function __construct($subId = null){ 
		if($subId){
			$this->getFields($subId);
		}
	}
	/**
	* gets submitted data from DB 
	*/ 
	public function getFields($subId){
		$submittedData = _AFORMDB()->getSubmissionFields($subId);
		$submittedDataClean = array();
		foreach($submittedData as $allField):
			$data = maybe_unserialize($allField->meta_value);
			if(isset($data['skipadminmail'])) unset($data['skipadminmail']);
			$fieldId = explode('-',$data['field']);
			$fieldId = array_pop($fieldId);
			$submittedDataClean[$fieldId] = _aFormMakeObject($data);
		endforeach;	
		$this->gotFields = $submittedDataClean;
		return $submittedDataClean;
	}

	public function getValue($field){
		$fieldValue = '';
		if(is_array($this->gotFields) && isset($this->gotFields[$field])):
			$field = $this->gotFields[$field];
			if(is_object($field)):
				$fieldValue = $field->value;
			else:
				$fieldValue = $field['value'];
			endif;
			return $fieldValue;
		endif;
		return $fieldValue;
	}
	//uses current data from handler and rebuilds they keys based on 'id' or 'string'
	/**
	* keysby : 'name' or 'id' 
	*/
	public function keysby( $data = null, $type = 'id'){
		if( (is_array($data) || is_object($data)) && !empty($data) ):
			$newArr = array();
			foreach($data as $index => $arrData):
				$id = explode('-' ,$index);
				if($type == 'name')://true is for string
					array_pop($id);
				  $id = implode('-',$id);
				else:
					$id = array_pop($id);
				endif;
					$newArr[$id] = $arrData;
			endforeach;
			$this->gotFields = $newArr;
			return $newArr;
		endif;
		$this->gotFields = $data;
		return $data;
	}
	
}
