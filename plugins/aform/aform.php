<?php
/*
Plugin Name: aForm
Description: A simple form
Author: Ruben Marin
Version: 1.2.7
*/

require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

if(PHP_VERSION < '5.3.14'){
	add_action( 'admin_notices', function(){
		?><div class="error error-nag"><p><strong>aForm:</strong> A minimum PHP Version of <strong>5.3.14</strong> is required. <br><small><strong>Current Version: <?php echo PHP_VERSION;?></strong></small></p></div><?php
	});	
return;
}

/* START PLUGIN */
if( ! class_exists('aform')):

	class aform{
		public $settings;

		public function __construct() {

			/* Do nothing here */

		}
		public function initialize(){
			global $aFormDb , $wpdb;
			$this->settings = array(

				'pluginfile'			=> __FILE__,
				'basenamefile'		=> plugin_basename( __FILE__ ),
				'basename'				=> plugin_basename( __FILE__ ),
				'path'						=> plugin_dir_path( __FILE__ ),
				'dir'							=> plugin_dir_url( __FILE__ ),
				'viewsdir'				=> plugin_dir_path( __FILE__ ) . 'views/',
				'cptname'					=> 'aform',
				'cptnamesub'			=> 'aform_sub',
				'cptnameinteg'		=> 'aform_integ',
				'cptlabel'				=> 'aForm',
				'namespace'				=> 'aform',
				'prefix'					=> 'aform',
				'uploadfolder'    => 'aform/uploads',
				'tablenames'			=> array(
					'fields' 					=> "{$wpdb->prefix}aform_fields",
					'reusablefields' 	=> "{$wpdb->prefix}aform_reusablefields", 
					'forms'						=> "{$wpdb->prefix}aform_forms",
				)
			);						

			$this->_define();

			/*
			* CORE:PLUGIN
			*/
			$this->includeFile('core/helpers.php');
			$this->includeFile('core/db.php');//autoloads
			$this->includeFile('core/ajax.php');//autoloads
			$this->includeFile('core/view.php');
			$this->includeFile('core/field.php');
			$this->includeFile('core/submissions.php');

			/*
				setup AFormDB global; aform db
			*/

			$aFormDb->settings = array( 'cptname' => 'aform' );
			$aFormDb->wpdb = $this->settings['tablenames'];
			
			/**
			*  CORE:FORM
			*/

			$this->includeFile('core/form/helper.php');
			$this->includeFile('core/form/ajax.php');//autoloads
			$this->includeFile('core/form/tracking.php');//autoloads
			$this->includeFile('core/form/validate.php');
			$this->includeFile('core/form/fileupload.php');
			$this->includeFile('core/form/mail.php');
			$this->includeFile('core/form/sender.php');
			$this->includeFile('core/form/save.php');
			$this->includeFile('core/form/handler.php');
			$this->includeFile('core/form/controller.php');
			$this->includeFile('core/form.php');
			
			
			/*
			* ADMIN
			*/
			$this->includeFile('admin/install.php' , $this->settings );//auto
			$this->includeFile('admin/update.php');//auto
			$this->includeFile('admin/wpinit.php');//auto
			$this->includeFile('admin/submissions.php');//auto
			$this->includeFile('admin/wpmbx.php');//auto

			/*
			* Integration
			*/
			$this->includeFile('admin/integration/init.php');
			$this->includeFile('core/integration/ajax.php');

			/*
			* fields // AUTO INCLUDE FIELDS 
			*/
			$this->includeFilesFromFolder('views/fields');
		
			/*
				Public 
			*/
			$this->includeFile('core/pub.php');
			
		}
		public function _define(){
			if (!defined('DS')) {
				define('DS' , DIRECTORY_SEPARATOR);
			}
			if (!defined('AF_FIELDNAME_PREFIX')) {
				define('AF_FIELDNAME_PREFIX' , 'af');
			}
			if (!defined('AF_C_FIELDNAME_PREFIX')) {
				define('AF_C_FIELDNAME_PREFIX' , 'cf___');
			}
		}
		public function includeFile($path = null , $mainSettings = null ){
			$pluginPath = $this->settings['path'];
			$file = $pluginPath . $path;
			
			$class = preg_replace('/\//' , '\\' ,$path);
			$class = preg_replace('/\.php/','',$class);
			$class = $this->settings['namespace']."\\$class";

			if(file_exists( $file )): include $file; endif;

			//loads class
			if(class_exists($class) && property_exists($class, 'autoloads')): 
				if($mainSettings == null):
					new $class(); 
				else:
					new $class($mainSettings); 
				endif;
			endif;

		}

		public function includeFilesFromFolder( $folder = null ,$prefix = null){
			$path = $this->settings['path'] . $folder;
			$files = glob($path . "/*{$prefix}.php" , GLOB_BRACE );
			foreach( $files as $file ):
					include $file;
			endforeach;	
		}
		
	}// end of class
	/**/
	function __aform() {
		global $aformPlugin;	
		if( !isset($aformPlugin) ):
			$aformPlugin = new aform();
			$aformPlugin->initialize();
		endif;
		return $aformPlugin;
	}
	// initialize
	__aform();
endif;

