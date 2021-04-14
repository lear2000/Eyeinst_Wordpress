<?php
/**
 * Authored by MyMedLeads.Com
 * This file contains the classes that are responsible for
 * performing lead tracking persistence
 * Upgrades will overwrite any changes you make within this
 * file.  Consult MyMedLeads.Com for possible upgrades.
 *
 * @package		PHP Integration
 * @created 	2013-01-18
 * @copyright	2013 MyMedLeads.Com
 * @version		1.0
 */
define("MML_PARAM_FULLNAME","FullName");
define("MML_PARAM_FIRSTNAME","FirstName");
define("MML_PARAM_LASTNAME","LastName");
define("MML_PARAM_EMAILADDRESS","EmailAddress");
define("MML_PARAM_ADDRESS","Address");
define("MML_PARAM_ADDRESS2","Address2");
define("MML_PARAM_CITY","City");
define("MML_PARAM_STATENAME","StateName");
define("MML_PARAM_ZIPCODE","PostalCode");
define("MML_PARAM_PHONE","PhoneNumber");
define("MML_PARAM_PHONE2","PhoneNumber2");
define("MML_PARAM_PHONEEXTENSION","PhoneExtension");
define("MML_PARAM_COMMENTS","Comment");
define("MML_PARAM_GENDER","IsMale");
define("MML_PARAM_BESTTIMETOCALL","BestTimeToCall");
define("MML_PARAM_SURGERYTYPE","SurgeryType");
define("MML_PARAM_PROCEDUREDATA","ProcedureData");
define("MML_PARAM_EXTRAFIELD","ExtraField");
define("MML_PARAM_NEXWEB","SendByNexWeb");
define("MML_PARAM_CAPTCHA","Captcha");
define("MML_PARAM_LOCATION","Location");
define("MML_PARAM_DOCTOR","Doctor");

class MMLConfig
{
	//	the url to the mml wsdl
	public static $wsdl = "https://api.mymedleads.com/data.svc?wsdl";
	public static $apiurl = "https://api.mymedleads.com/data.svc";

}
class MMLClient
{
	//	the name of our tracking cookie
    const COOKIE_NAME = '__mml';
	
	//	the nusoap client object
	private $client = null;
	
	//	holds the last error (or false if no error has occured)
	private $error = false;
	
	//	houses the transaction key for this specific form
	private $transaction_key;

	//	a list of MML specific Key/Value pairs
	public $current_keys;
	
	//	a list of elementIds that map to MML specific keys
	public $mapping_keys;
	
	//	a list of groupIds that map checkboxes and radios to MML specific keys
	public $grouping_keys;
	
	//	(not used yet), this will map Procedures with IDs.  The mapping will take in the value passed in from the control and map it to the Id
	private $mapped_procedures;
	
	private $referurl;

	//	$transactionkey - this is the MML assigned key for this form
	//	$keys - an already prepared list of current keys
	function __construct($transactionkey, $keys = null) {
		$this->transaction_key = $transactionkey;
		$this->mapping_keys = array();
		$this->grouping_keys = array();
		if ($keys != null) {
			foreach ($keys as $key => $value) {
				$this->mapping_keys[$key] = $value;
			}
		}
		$this->mapped_procedures = array();
		$this->current_keys = array();
	}	

	public static function renderTrackingCookie($domain, $with_tags = true) {
	
        // compressed with YUI - uncompressed version in mml.js
		if ($with_tags == true) {
        return '<!-- MML Lead Tracking -->
<script type="text/javascript">
function setCookie(e,o,n){var i=new Date;i.setMinutes(i.getMinutes()+n);var t=escape(o)+(null==n?"":"; expires="+i.toUTCString()+";path=/;");document.cookie=e+"="+t}function getCookie(e){var o,n,i,t=document.cookie.split(";");for(o=0;o<t.length;o++)if(n=t[o].substr(0,t[o].indexOf("=")),i=t[o].substr(t[o].indexOf("=")+1),n=n.replace(/^\s+|\s+$/g,""),n==e)return unescape(i)}function checkCookie(){var e=getCookie(cookie_name);null!=e&&""!=e||(e=window.location.href.indexOf("gclid=")>0?encodeURIComponent(window.location.href):document.referrer!=window.location.href&&document.referrer?encodeURIComponent(document.referrer):window.location.href.indexOf("utm_")>0?encodeURIComponent(window.location.href):"",null!=e&&""!=e&&setCookie(cookie_name,e,10080))}var cookie_name="__mml";checkCookie();
</script>
<!-- MML Lead Tracking -->
';
		} else {
        return '
 ////////////////////////////		
//   MML Lead Tracking
function setCookie(e,o,n){var i=new Date;i.setMinutes(i.getMinutes()+n);var t=escape(o)+(null==n?"":"; expires="+i.toUTCString()+";path=/;");document.cookie=e+"="+t}function getCookie(e){var o,n,i,t=document.cookie.split(";");for(o=0;o<t.length;o++)if(n=t[o].substr(0,t[o].indexOf("=")),i=t[o].substr(t[o].indexOf("=")+1),n=n.replace(/^\s+|\s+$/g,""),n==e)return unescape(i)}function checkCookie(){var e=getCookie(cookie_name);null!=e&&""!=e||(e=window.location.href.indexOf("gclid=")>0?encodeURIComponent(window.location.href):document.referrer!=window.location.href&&document.referrer?encodeURIComponent(document.referrer):window.location.href.indexOf("utm_")>0?encodeURIComponent(window.location.href):"",null!=e&&""!=e&&setCookie(cookie_name,e,10080))}var cookie_name="__mml";checkCookie();
// MML Lead Tracking
/////////////////////////
';		
		}
		
	}
	  /*************************************************/
	 /*         Custom Form Management Methods        */
	/*************************************************/ 	
	function addMapping($elementId, $mmlParameterName)	{
		$found = 'false';
		foreach ($this->mapping_keys as $map_key => $mml_key) {
			if ($map_key == $elementId) {
					$this->mapping_keys[$elementId] = $mmlParameterName;
				$found = 'true';
			}
		}
		if ($found == 'false') {// need to add it
			$this->mapping_keys[$elementId] = $mmlParameterName;
		}
	}
	function addValue($value, $mmlParameterName)	{
		if (!isset($this->current_keys))
			$this->current_key = array();
		if ($mmlParameterName == MML_PARAM_FULLNAME) {
			//	create 2 mappings (first and last name)
			$arrName=explode(" ",trim($value));
			$fname="";
			$lname="";

			if(count($arrName)>1){
			for($i=0;$i<count($arrName)-1;$i++){
			$fname.=$arrName[$i]." ";
			}
			$fname=trim($fname);
			$lname=$arrName[count($arrName)-1];
			}else{
			$fname=trim($value);
			$lname="";
			}
			if ($fname == "" && $lname == "")
			{
				$fname = $value;
			}
			$this->current_keys[MML_PARAM_FIRSTNAME] = $fname;
			$this->current_keys[MML_PARAM_LASTNAME] = $lname;
		} else if ($mmlParameterName == MML_PARAM_GENDER) {
			// we need to map this to isMale == true or false;
			if (strtolower($value) == 'yes' ||strtolower($value) == 'true' || strtolower($value) == 'on' || strtolower($value) == '1') {
				$value = 'true';
			} else {
				$value = 'false';
			}
			$this->current_keys[$mmlParameterName] = $value;
		} else if ($mmlParameterName == MML_PARAM_NEXWEB) {
			// we need to map this to isMale == true or false;
			if (strtolower($value) == 'yes' ||strtolower($value) == 'true' || strtolower($value) == 'on' || strtolower($value) == '1') {
				$value = 'true';
			} else {
				$value = 'false';
			}
			$this->current_keys[$mmlParameterName] = $value;
		} else {	
			$this->current_keys[$mmlParameterName] = $value;
		}
	}
	function addCustomValue($value, $displayText)	{	
		if (!isset($this->current_keys))
			$this->current_key = array();	
		$this->current_keys[MML_PARAM_EXTRAFIELD.'|||'.$displayText] = $value;
	}
	function addCustomMapping($elementId, $displayText)	{
		$found = 'false';
		foreach ($this->mapping_keys as $map_key => $mml_key) {
			if ($map_key == $elementId) {
				if ($displayText == '')
					$this->mapping_keys[$elementId] = MML_PARAM_EXTRAFIELD.'|||'.$displayText;
				$found = 'true';
			}
		}
		if ($found == 'false') {// need to add it
			$this->mapping_keys[$elementId] = MML_PARAM_EXTRAFIELD.'|||'.$displayText;
		}
	}	
	function addSelectionMapping($groupName, $mmlParameterName)	{
		$found = 'false';
		foreach ($this->grouping_keys as $group_key => $mml_key) {
			if ($group_key == $groupName) {
				$this->grouping_keys[$groupName] = $mmlParameterName;
				$found = 'true';
			}
		}
		if ($found == 'false') {// need to add it
			$this->grouping_keys[$groupName] = $mmlParameterName;
		}
	}
	function parseGravityFormForFullName($entry, $entryid)	{
		$this->current_keys[MML_PARAM_FIRSTNAME] = $entry[$entryid.'.3'];
		$this->current_keys[MML_PARAM_LASTNAME] = $entry[$entryid.'.6'];
	}
	function parseGravityFormForAddress($entry, $entryid)	{
		$this->current_keys[MML_PARAM_ADDRESS] = $entry[$entryid.'.1'];
		$this->current_keys[MML_PARAM_ADDRESS2] = $entry[$entryid.'.2'];
		$this->current_keys[MML_PARAM_CITY] = $entry[$entryid.'.3'];
		$this->current_keys[MML_PARAM_STATENAME] = $entry[$entryid.'.4'];
		$this->current_keys[MML_PARAM_ZIPCODE] = $entry[$entryid.'.5'];
		$this->current_keys[MML_PARAM_EXTRAFIELD.'|||Country: '] = $entry[$entryid.'.6'];
	}
	function parseGravityFormCheckboxesForProcedures($entry, $entryid,$count = 100)	{
		$value = '';
		$count = $count + 1;
		for ($i=0; $i<$count; $i++) {
			if ($entry[$entryid.'.'.$i] != null)
			if ($value == '')
				$value = $entry[$entryid.'.'.$i];
			else
				$value = $value.','.$entry[$entryid.'.'.$i];
		}
		$this->current_keys[MML_PARAM_PROCEDUREDATA] = $value;
	}
	function addGravityFormCustomCheckbox($entry, $entryid, $question, $count=100)	{
		$value = '';
		for ($i=0; $i<$count; $i++) {
			if ($entry[$entryid.'.'.$i] != null)
			if ($value == '')
				$value = $entry[$entryid.'.'.$i];
			else
				$value = $value.','.$entry[$entryid.'.'.$i];
		}
		if ($question != '')
			$index = MML_PARAM_EXTRAFIELD.'|||'.$question;
		else
			$index = MML_PARAM_EXTRAFIELD;
		$this->current_keys[$index] = $value;
	}
	function sendToNexWeb(){
		$this->current_keys[MML_PARAM_NEXWEB] = 'true';
	}
	
	
	//	adds the ProcedureId to the value that is returned within the POST
	//		normally the key would be the 'group' name of checkbox/radio list
	function addProcedure($procedureId, $elementValue) {
		$found = 'false';
		foreach ($this->mapped_procedures as $key => $value) {
			if ($key == $elementValue) {
				$this->mapped_procedures[$elementValue] = $procedureId;
				$found = 'true';
			}
		}
		if ($found == 'false') {// need to add it
			$this->mapped_procedures[$elementValue] = $procedureId;
		}
	}
	function resetMappings() {
		$this->mapping_keys = array();
		$this->current_keys = array();
	}
    function querifyKeys() {
		$result = '';
		foreach ($this->current_keys as $key => $value) {
			$result = $result.'&'.$key.'='.$value;
		}
		return $result;
    }
	// this function will correct the mappings and produce an applicable list
	//	of keys and values to pass into any send method.  This function applies your premade decoder ring
	//	to the current request.
	function AnalyzeRequest() {
		//$this->current_keys = array();// reset
		$extra_fields = '';
    	if (isset($_COOKIE[self::COOKIE_NAME])) {
			$this->referurl = urlencode($_COOKIE[self::COOKIE_NAME]);
		} else {
			$this->referurl = urlencode($_SERVER['HTTP_REFERER']);
		}
		foreach ($this->mapping_keys as $map_key => $mml_key) {
			foreach( $_POST as $post_key => $post_value) {
				if ($post_key == $map_key) {
					if (is_array($post_value)) {
						//	this is a checkbox, we need to do additional logic here.
						//		within the <input> tag, the name needs to end with [] to make this applicable
						if (empty($post_value)){
							$post_value = '';
						} else {
							//	we need to create an applicable string to represent the array
							$temp = '';
							$count = count($post_value);
							for ($i=0; $i<$count; $i++) {
								if ($temp == '') {
									$temp = $post_value[$i];
								} else {
									$temp = $temp.','.$post_value[$i];
								}
							}
							$post_value = $temp;
						}
					}
					//	check for exact mapping or general data for comments
					if ($mml_key == MML_PARAM_EXTRAFIELD) {
						// Additional Fields Heading logic
						$this->current_keys[$map_key] = $post_value;
					} else if ($mml_key == MML_PARAM_FULLNAME) {
						//	create 2 mappings (first and last name)
						$arrName=split(" ",trim($post_value));
						$fname="";
						$lname="";

						if(count($arrName)>1){
						for($i=0;$i<count($arrName)-1;$i++){
						$fname.=$arrName[$i]." ";
						}
						$fname=trim($fname);
						$lname=$arrName[count($arrName)-1];
						}else{
						$fname=trim($post_value);
						$lname="";
						}
						$this->current_keys[MML_PARAM_FIRSTNAME] = $fname;
						$this->current_keys[MML_PARAM_LASTNAME] = $lname;
						
						
					} else if ($mml_key == MML_PARAM_GENDER) {
						// we need to map this to isMale == true or false;
						if (strtolower($post_value) == 'yes' ||strtolower($post_value) == 'true' || strtolower($post_value) == 'on' || strtolower($post_value) == '1') {
							$post_value = 'true';
						} else {
							$post_value = 'false';
						}
						$this->current_keys[$mml_key] = $post_value;
					} else if ($mml_key == MML_PARAM_NEXWEB) {
						// we need to map this to SendToNexweb == true or false;
						if (strtolower($post_value) == 'yes' ||strtolower($post_value) == 'true' || strtolower($post_value) == 'on' || strtolower($post_value) == '1') {
							$post_value = 'true';
						} else {
							$post_value = 'false';
						}
						$this->current_keys[$mml_key] = $post_value;
					} else {
					$this->current_keys[$mml_key] = $post_value;
					}
				}
			}
		}
		
		//	is it necessary to append extra fields to the comments?
		//		the $extra_fields is a throw-away variable, only used to contain the value from each input
		if ($extra_fields != '') {
			if ($this->current_keys[MML_PARAM_COMMENTS] == null) { // create the comments
				$this->current_keys[MML_PARAM_COMMENTS] = $extra_fields;
			} else { // just append
				$this->current_keys[MML_PARAM_COMMENTS] = $this->current_keys[MML_PARAM_COMMENTS].$extra_fields;
			}
		}
		//print_r($this->current_keys);
	}
	
	  /*************************************************/
	 /*         MML SOAP COMMUNICATIONS METHODS       */
	/*************************************************/ 

	// Function for basic field validation (present and neither empty nor only white space
	private function IsNullOrEmptyString($string){
		return (!isset($string) || trim($string)==='');
	}
	function xmlEscape($string) {
    return str_replace(array('&', '<', '>', '\'', '"'), array('&amp;', '&lt;', '&gt;', '&apos;', '&quot;'), $string);
	}
	public function PostLead()
	{
		$this->AnalyzeRequest();
		$soap_message = '<?xml version="1.0"?>
<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:tem="http://mymedleads.com/" xmlns:sys="http://schemas.datacontract.org/2004/07/System.Collections.Generic">
   <soapenv:Header/>   
   <soapenv:Body>
      <tem:PostLead>
         <tem:transactionKey>'.$this->transaction_key.'</tem:transactionKey>
         <tem:customFields>';
		foreach ($this->current_keys as $key => $value) {
               $soap_message = $soap_message.'<sys:KeyValuePairOfstringstring><sys:key>'.$this->xmlEscape($key).'</sys:key><sys:value>'.$this->xmlEscape($value).'</sys:value></sys:KeyValuePairOfstringstring>';
		}
		if (trim($this->referurl)!='') {
			   $soap_message = $soap_message.'<sys:KeyValuePairOfstringstring><sys:key>ReferUrl</sys:key><sys:value>'.$this->referurl.'</sys:value></sys:KeyValuePairOfstringstring>';
		}
		$soap_message = $soap_message.'
         </tem:customFields>
      </tem:PostLead>
   </soapenv:Body>
</soapenv:Envelope>';		
		
		$session = curl_init(MMLConfig::$apiurl.'/PostLead');
		curl_setopt($session, CURLOPT_HTTPHEADER,array('Content-Type: text/xml; charset=utf-8',"Accept: text/xml","Cache-Control: no-cache","Pragma: no-cache",'SOAPAction: http://mymedleads.com/IData/PostLead', 'Content-Length: '.strlen($soap_message)));
        curl_setopt($session, CURLOPT_POST, true);
		curl_setopt($session, CURLOPT_CONNECTTIMEOUT, 10);
		curl_setopt($session, CURLOPT_TIMEOUT,        10);
		curl_setopt($session, CURLOPT_RETURNTRANSFER, true );
		curl_setopt($session, CURLOPT_POST,           true );
		curl_setopt($session, CURLOPT_POSTFIELDS, $soap_message);
        if (strstr(MMLConfig::$apiurl, 'https:')) {
            curl_setopt($session, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($session, CURLOPT_SSL_VERIFYHOST, false);
        }

        $response = curl_exec($session);
        curl_close($session);

		return $response;
	}
}
 
?>