<?php  
/**
* gets database object so we can use across plugin
* no need to call a global every time, method does it for us
*/
function _AFORMDB(){
	global $aFormDb;
	return $aFormDb;
}

function _aFormMakeObject($array){
	if(is_array($array)):
		$array = json_encode($array);
		$array = json_decode($array);
	endif;
	return $array;
}
function __aFormRemoveHttp($url=null){
	$url = preg_replace('(^https?://)', '', $url ); // removes 'http(s)' from url
	return $url;
}
function __aFormGetRealUri(){
	$ex = preg_replace('(^https?://)', '', home_url() ); // removes 'http(s)' from url
	$ex = preg_replace('/www./i' , '' , $ex ); // remove 'www.' from url
	$output = home_url() . preg_replace( '(^'.$ex.')' , '' , preg_replace( '/www./i' , '' , $_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'] ) );
	return $output;
}

function __aFormGetRequestUri(){
	$ex = preg_replace('(^https?://)', '', get_bloginfo('url') ); // removes 'http(s)' from url
	$ex = preg_replace('/www./i' , '' , $ex ); // remove 'www.' from url
	$output = explode('/' , preg_replace( '(^'.$ex.')' , '' , preg_replace( '/www./i' , '' , $_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'] ) ) ); //clean URI / SLUG
	$output = array_filter($output);
	$output = array_values($output);
	if(!empty($output)):
		$output =  "/".implode('/', $output)."/";
		return $output;
	elseif(empty($output)):
		return 'Homepage';
	endif;
	return implode($output);
}

function aFormSettings($setting=null){
	global $aformPlugin;
	$s = $aformPlugin->settings;
	if(isset($s[$setting])):
		return $s[$setting];
	endif;
}

/**
* finds the key in an object||array and returns
* if it finds the value return it else return null OR return a given default value
*/
function afGetAOV($data = null , $key = null , $default = null ){
	if( $data == null || empty($data) ) return $default ;
	if(is_array($data)):
		return (isset($data[$key])) ? $data[$key] : $default ;
	else: 
		return (isset($data->$key)) ? $data->$key : $default ;
	endif;
}
/**
* creates the html for the form
*/
function aformRender( $inputType , $index , $fieldData = null ){
		$class = "aform\\fields\\{$inputType}";
		$fieldClass = new $class;
		echo $fieldClass->fieldDataSetup($fieldData)->renderField( $fieldData , $index);
}

function getAFormInstance( $name = null ){
	return aform\core\form::get_instance( $name );

}

function insertAForm( $name = null , $settings = null ){
		$form = aform\core\form::get_instance( $name , false, $settings);
		$form->render();
}

function afFieldName($value){
	echo AF_FIELDNAME_PREFIX."[$value]";
}
function afCustomFieldName($value){
	echo AF_FIELDNAME_PREFIX."[".AF_C_FIELDNAME_PREFIX."{$value}]";
}