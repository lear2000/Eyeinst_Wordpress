<?php
/**
* Plugin Name: aForm MML -
* Plugin URI: http://www.firesuite.net
* Description: Adds MML SYNC -
* Version: 1
* Author: FIRESUITE
* Author URI: http://www.firesuite.net
* License: GPL2
*/
?>
<?php

// // CREATE A OPTIONS SETTING
if( function_exists('acf_add_options_page') ) {
  acf_add_options_page(array(
      'page_title'  => 'MML General Settings',
      'menu_title'  => 'MML Settings',
      'menu_slug'   => 'mml-general-settings',
      'capability'  => 'edit_posts',
      'redirect'    => false,
      'parent_slug' => 'edit.php?post_type=aform'
    ));
}

// // CREATE FIELDS
if( function_exists('acf_add_local_field_group') ):

acf_add_local_field_group(array (
  'key' => 'group_5b46b32655fc6',
  'title' => 'MML SYNC',
  'fields' => array (
    array (
      'key' => 'field_5b46b333c87ff',
      'label' => 'Transaction Key',
      'name' => 'transaction_key',
      'type' => 'text',
      'instructions' => '',
      'required' => 0,
      'conditional_logic' => 0,
      'wrapper' => array (
        'width' => '',
        'class' => '',
        'id' => '',
      ),
      'default_value' => '',
      'placeholder' => '',
      'prepend' => '',
      'append' => '',
      'maxlength' => '',
    ),
  ),
  'location' => array (
    array (
      array (
         'param' => 'options_page',
         'operator' => '==',
        'value' => 'mml-general-settings',
      ),
    ),
  ),
  'menu_order' => 0,
  'position' => 'normal',
  'style' => 'default',
  'label_placement' => 'top',
  'instruction_placement' => 'label',
  'hide_on_screen' => '',
  'active' => 1,
  'description' => '',
));

endif;



add_action( 'aform/after/post' , 'add_to_mml' , 10 , 2 );

function add_to_mml( $formData , $handlerInstance ){



// CREATE EASY ARRAY
function _entryDataReformatMML($arr , $stringOrId = true){
  //$stringId : if true then check for string {name}-6
  $newArr = array();
  if((!empty($arr) && is_array($arr))):
    foreach($arr as $index => $arrData):
      $id = explode('-' ,$index);
      if($stringOrId == true){//true is for string
        array_pop($id);
        $id = implode('-',$id);
      }else{
        $id = array_pop($id);
      }
     $newArr[$id] = $arrData;
    endforeach;
  endif;
  return  $newArr;
}


     $newArray = _entryDataReformatMML($formData->postchunks['fields'],TRUE);
    error_log("this here ".print_r($newArray, TRUE));
    // $listid = (isset($newArray['groupid'])) ? $newArray['groupid']['value'] : null;//so you don't get an error when that index is not available



     // error_log( print_r($formData->postchunks['fields'], TRUE) );
    //error_log( print_r($newArray, TRUE) );




			 			// ACF OPTIONS GET TRANS KEY
            $key = get_field('transaction_key','option');
            // If formname is in aforms
            $formname = $newArray['formname']['value'];

            // error_log($key);

function emailmml($key,$firstname,$lastname,$emailaddress,$procsofinst,$comments,$howsoon){
              // MML SECTION
              include 'mmlclient.php';  // inc MML file
              //  TransactionKey can be stored in database or pulled from
              $mml = new MMLClient($key);

              //  **  you are able to pass in values directly
                  $mml->addValue($fullname, MML_PARAM_FULLNAME);
                  $mml->addValue($firstname, MML_PARAM_FIRSTNAME);
                  $mml->addValue($lastname, MML_PARAM_LASTNAME);
                  $mml->addValue($emailaddress, MML_PARAM_EMAILADDRESS);
                  $mml->addValue($address,MML_PARAM_ADDRESS);
                  $mml->addValue($address2,MML_PARAM_ADDRESS2);
                  $mml->addValue($city,MML_PARAM_CITY);
                  $mml->addValue($state,MML_PARAM_STATENAME);
                  $mml->addValue($zip,MML_PARAM_ZIPCODE);
                  $mml->addValue($phone,MML_PARAM_PHONE);
                  $mml->addValue($phone2,MML_PARAM_PHONE2);
                  $mml->addValue($comments,MML_PARAM_COMMENTS);
                  $mml->addValue($gender,MML_PARAM_GENDER);
                  // $mml->addValue($procedure,MML_PARAM_PROCEDUREDATA);




              //  **  adding a line to the Lead's Comments describing his/her
              //  **   selection or selections.  Lead's Comments will read:
              //  **  How did you hear about us? Magazine
                  //$mml->addCustomValue('lstHearSource','How did you hear about us?');
                   $mml->addCustomValue($howsoon,'How Soon Would You Like Procedure?');
                    // $mml->addCustomValue($kit,'Want a free information kit mailed to your home?');



                    function getProcedureId($procName) {
                      $procedureArray = array(
                      'botox' => '22',
                      'bellafill'=> '3026'
                      );
                      if ($procName != '')
                      {
                        $procName = strtolower($procName);
                        foreach ($procedureArray as $term => $pId) {
                          if (strpos($procName, $term) !== false){
                              return $pId;
                          }
                        }
                      }
                      return 0; // other procedure
                    }



                  foreach( $procsofinst as $value){
                  	$procId = getProcedureId($value);
                  	if ($procId > 0){
                  		$procIds = $procIds.$delimiter.$procId;
                  		$delimiter = ',';
                  	}
                  }
                 	if ($procIds != '') {
                 			$mml->addValue($procIds, MML_PARAM_PROCEDUREDATA);
               		}








                 // DO COOKIE
                  add_action('wp_footer', 'add_mml_tracking');
                  function add_mml_tracking() {
                    $domain = $_SERVER['SERVER_NAME'];
                    echo MMLClient::renderTrackingCookie($domain);
                  }

              //  Post the lead
              $result = $mml->PostLead();
              error_log($result);
              error_log("howsoon ".$howsoon);
              sleep(1);
             // wp_mail( "matt@firesuite.net", "MML", print_r($result,true), $headers, $attachments);
              return json_encode($result);
}





            // PROCESS MML
           switch ($formname) {
             case 'calculator':
             // GLOBAL FROM FORMS
               $firstname = $newArray['name']['value'];
               $emailaddress = $newArray['email']['value'];
               $lastname = "";
               $comments = "";
               $howsoon = $newArray['how-soon']['value'];
               // error_log('PROCS '.$procedures);
               $procsofinst = explode(',', $procedures);
               //error_log(print_r($procsofinst,true));

               //wp_mail( 'matt@firesuite.net', 'MML', print_r($howsoon,true), $headers , $attachments );
               emailmml($key,$firstname,$lastname,$emailaddress,$procsofinst,$comments,$howsoon);
            break;

             default:

             break;
           }





}