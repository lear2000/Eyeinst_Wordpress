<?php  
namespace aform\core;
class pub {

	////////////////////////////////////////////////////////////////////
	public $autoloads;//do not delete, this triggers our include method to load this class
	////////////////////////////////////////////////////////////////////
	
	function __construct() {
		add_action( 'wp_enqueue_scripts', array(&$this, 'frontendJS') );
		add_action( 'wp_footer' , array(&$this, 'wpFooter'));		
		add_shortcode( 'aform', array(&$this, 'shortcode') );
		add_action( 'wp_head', array(&$this, 'honeypotCSS') );
		add_action('init' , array(&$this, 'redirectWithPostData'));
		add_action('wp_loaded' , array(&$this, 'loadGlobals'));
	}
	
	function loadGlobals(){
		global $_AFORMGLOBALSETTINGS;
		
		$_AFORMGLOBALSETTINGS = get_option('_aform_settings',array());
		$_AFORMGLOBALSETTINGS['recaptcha'] = false;
		$recaptchaEnabled =  afGetAOV( $_AFORMGLOBALSETTINGS , 'recaptcha-enabled' , false);
		$recaptchaClient =  afGetAOV( $_AFORMGLOBALSETTINGS , 'recaptcha-client' , '');
		if($recaptchaEnabled && $recaptchaClient):
			$_AFORMGLOBALSETTINGS['recaptcha'] = true;
		endif;
		

	}

	function redirectWithPostData(){
		if(!is_admin()):
			global $aformSuccessWithPost;
			if(isset($_REQUEST['aform-redirect-post']) && ($_REQUEST['aform-redirect-post']['submission_id'] && !empty($_REQUEST['aform-redirect-post']['submission_id']))):
				$aformSuccessWithPost = new submissions($_REQUEST['aform-redirect-post']['submission_id']);
				if(isset($_REQUEST['aform-redirect-post']['form_id']) && !empty($_REQUEST['aform-redirect-post']['form_id'])):
					$aformSuccessWithPost->formId = $_REQUEST['aform-redirect-post']['form_id'];
				endif;				
			endif;
		endif;
	}
	function wpFooter(){
		global $_AFORMRULES,$_AFORMSCLIENTJS,$_AFORMGLOBALSETTINGS;
		$formRules = $_AFORMRULES;
		if($_AFORMSCLIENTJS == true):	
			?><script id="aformRules"> var aformRules = [];<?php if(!empty($formRules)): foreach($formRules as $formId => $formRule): ?> aformRules[<?php echo $formId;?>] = <?php echo json_encode($formRule);?>;<?php endforeach; endif; ?></script>
			<?php
			wp_print_scripts('aform.public.js');
			if($_AFORMGLOBALSETTINGS['recaptcha']):
				$recaptchaClient =  afGetAOV( $_AFORMGLOBALSETTINGS , 'recaptcha-client' , '');
				echo "<script src=\"https://www.google.com/recaptcha/api.js?render={$recaptchaClient}\"></script>";
				?>
					<script>
						var recaptchaInputs = document.querySelectorAll('input.af-recaptcha');
						grecaptcha.ready(function () {
							grecaptcha.execute('<?php echo 	$recaptchaClient;?>', { action: 'contact' }).then(function (token) {
							for (let index = 0; index < recaptchaInputs.length; index++) {
								recaptchaInputs[index].value = token;
							}
							});
						});
					</script>
				<?php
			endif;
		endif;

	}
	function frontendJS() {
		$pluginUri = aFormSettings('dir');
		if( wp_script_is( 'jquery.validate.min.js', 'enqueued' ) ):
			wp_deregister_script( 'jquery.validate.min.js' );
		endif;
		
		$jsDeps = array('jquery', 'aform.libs.js');

		/**
		* combined scripts: jquery-validate, jquery-validate-methods, jquery-transport 
		*/
		wp_register_script( 'aform.libs.js', $pluginUri . 'assets/js/libs/jquery.form.libs.js', array( 'jquery' ), false, true );
		/**
		 * aform public script
		 */
		wp_register_script( 'aform.public.js', $pluginUri . 'assets/js/form.js', 	$jsDeps, time(), true );
		/**
		 * aform public localized data
		 */
		wp_localize_script( 'aform.public.js' , 'afData', array( 'blogurl' => get_bloginfo( 'url' ) ,'afAjaxUrl' => admin_url( 'admin-ajax.php' ) ) );	

	}
	
	function honeypotCSS() {	
		ob_start(); ?>
			<style type="text/css">.stopyenoh{height:0!important;overflow:hidden!important;width:0!important;visibility:hidden!important;position:absolute!important;}</style>
		<?php
		$style = ob_get_contents();
		ob_end_clean();
		echo $style;
	}
	
	function shortcode( $atts ) {
		
		$atts = shortcode_atts( array(			
			'name' 		=> null,
			'formclass' => ''
		), $atts  );
		
		$formName = ( isset($atts['name']) ) ? $atts['name'] : null;
		
		$atts = apply_filters( 'aform/shortcode/atts' , $atts );

		if( $formName == null ):
			return;
		endif;
		ob_start();
			insertAForm( $formName , $atts );
		$formOutput = ob_get_contents();
		ob_end_clean();
		return $formOutput;		
	}
	
		
}
