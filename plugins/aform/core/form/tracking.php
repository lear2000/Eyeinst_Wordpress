<?php
namespace aform\core\form;

class tracking{
	////////////////////////////////////////////////////////////////////
	public $autoloads;//do not delete, this triggers our include method to load this class
	////////////////////////////////////////////////////////////////////

	public function __construct(){

		global $AFORMSTRACKING;
		$AFORMSTRACKING = $this;
		add_action( 'wp_head', array(&$this , 'campaignTrackingCookie') , 1 );

	}

	public function campaignTrackingCookie(){
		global $pagenow;
		if( is_admin() || $pagenow == 'wp-login.php' ) return;

		if( $this->queryCheck() == false ) return;//check if query has utm_
			$haveTrackingOn = _AFORMDB()->findFormSetting('"include_campaign_trackingdata":"true"');
			$parseQuery = $this->parseQuery();
			if($haveTrackingOn == true && count($parseQuery) > 1 ):
			$trackingJson = json_encode($parseQuery);
			?>
				<script>
				window.onload = () => {
				var __formUtmTracking = <?php echo $trackingJson;?> , __formUtmTrackingExpire = new Date();
				__formUtmTrackingExpire.setTime(__formUtmTrackingExpire.getTime() + (1 * 24 * 60 * 60 * 1000));
				document.cookie = "__formUtmTracking="+ JSON.stringify(__formUtmTracking)+";expires="+__formUtmTrackingExpire+";path=/;samesite=LAX";
				};
				</script>
			<?php
		endif;
	}
	public function queryCheck(){
		if(!empty($_SERVER["QUERY_STRING"])):
			if(preg_match('/utm_/i' , $_SERVER["QUERY_STRING"] )):
				return true;
			endif;
		endif;
		return false;
	}
	public function parseQuery(){
		$queryParts = array();
		$urlQuery = $_SERVER["QUERY_STRING"];
		parse_str( $urlQuery , $queryParts );
		return $queryParts;
	}

}
