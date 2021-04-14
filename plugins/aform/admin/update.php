<?php  
namespace aform\admin;

class update{
	////////////////////////////////////////////////////////////////////
	public $autoloads;//do not delete, this triggers our include method to load this class
	////////////////////////////////////////////////////////////////////

	public function __construct(){
		add_action( 'admin_init', array(&$this , 'tableUpdates') );
		add_action( 'init' , array(&$this , 'pluginUpdate'));
	}

	public function pluginUpdate(){
		global $aformPlugin;
		$pluginFolder = $aformPlugin->settings['path'];
		$pucFile = $pluginFolder . 'vendor/_puc2/plugin-update-checker.php';
		if(file_exists($pucFile) && !class_exists('\\Puc_v4_Factory')):
			require $pucFile;
		endif;
		if(class_exists('\\Puc_v4_Factory')):
			$myUpdateChecker = \Puc_v4_Factory::buildUpdateChecker(
				'https://bitbucket.org/rubenmarin/aform',
				$aformPlugin->settings['pluginfile'],
				'aform'
			);
			$myUpdateChecker->setAuthentication(array(
				'consumer_key' => 'fd9nTuNWVxLKjnSv6c',
				'consumer_secret' => 'zfWb3UX9qpZVJZZvNJ3AdWrFFrbDWbSe'
			));			
		endif;
	}	



	public function tableUpdates(){
		global $wpdb , $aformPlugin , $aFormDb;
		if( is_admin() && current_user_can( 'activate_plugins' ) ):

			$dbt = \aform\admin\install::tables($aformPlugin->settings['tablenames']);;	
			$dbcheck = 0;
			foreach ($dbt as $obj => $table):
				$tableName = $table->name;
				if( $wpdb->get_var("SHOW TABLES LIKE '{$tableName}'") != $tableName ):
					$dbcheck = ($dbcheck + 1);
				endif;
			endforeach;
			if($dbcheck > 0){
				add_action( 'admin_notices', function(){
					?><br><div class="update-nag"><strong>Aform</strong> : <em>A Database upgrade has been detected.</em> <a href="<?php echo admin_url('edit.php?post_type=aform&dbupdate=1'); ?>">Please upgrade now.</a></div><?php
				});	
			}
			
			if( ( isset($_GET['post_type']) && $_GET['post_type'] == 'aform' ) && ( isset($_GET['dbupdate']) && $_GET['dbupdate'] == 1 ) ):
				/** 
				*	update our tables 
				*/
				\aform\admin\install::createTables( $aformPlugin->settings );

				wp_redirect( admin_url('edit.php?post_type=aform&dbupdate=0') ); /* redirect to false update */
			
			elseif( ( isset($_GET['post_type']) && $_GET['post_type'] == 'aform' ) && ( isset($_GET['dbupdate']) && $_GET['dbupdate'] == 0 ) ):
				add_action( 'admin_notices', function(){
					?>
					<div class="updated" style="position:relative;"><p><strong>AForm</strong> : Database has been upgraded.<a class="button" href="<?php echo admin_url('edit.php?post_type=aform');?>" style="position:absolute;right:5px;top:5px;">close</a></p></div>
					<?php
				});	
			endif;
		endif;
	}


}
