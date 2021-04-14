<?php  
namespace aform\core\form;
use aform\core\form\controller as afCONTROLLER; 
class controller{
	function __construct(){
		add_action( 'init', array(&$this , '_REQUEST') , 11 );
	}
	function _REQUEST(){
		global $contactAForm;
		if ( ! isset( $_SERVER['REQUEST_METHOD'] ) ) return;
		if ( 'POST' == $_SERVER['REQUEST_METHOD'] ):
			if(!isset($_POST[AF_FIELDNAME_PREFIX])) return ;
				$FORMPOSTDATA = $_POST[AF_FIELDNAME_PREFIX];
				// get instance of form to submit
				$form = \aform\core\form::get_instance( $FORMPOSTDATA['afname'] , true );
				$submit = $form->submit();
		endif;
	}
}
$afCONTROLLER = new afCONTROLLER;
