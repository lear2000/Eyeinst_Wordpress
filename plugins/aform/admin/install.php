<?php  
namespace aform\admin;

class install{
	////////////////////////////////////////////////////////////////////
	public $autoloads;//do not delete, this triggers our include method to load this class
	////////////////////////////////////////////////////////////////////

	public $pluginSettings;

	public function __construct($aformClassSettings = null){
		if($aformClassSettings == null) return;
		$this->pluginSettings = $aformClassSettings;
		register_activation_hook( $aformClassSettings['basename'] , array(&$this, 'onRegister') );
	}

	public function onRegister(){
		
		//create the tables for this plugin
		$pluginSettings = $this->pluginSettings;
		\aform\admin\install::createTables( $pluginSettings );

		//create the uploads folder for this plugin
		$wp_upload_dir = wp_upload_dir();
		$wp_upload_dir = $wp_upload_dir['basedir'];
		$aformPluginUploadFolder = $wp_upload_dir . '/' . $pluginSettings['uploadfolder'];
		if(is_writable($wp_upload_dir)):
			if(!file_exists($aformPluginUploadFolder)):
				mkdir($aformPluginUploadFolder , 0775 , true);
			endif;
		endif;
	
	}
	
	public static function createTables( $pluginSettings ){
		global $wpdb;
		$dbt = static::tables($pluginSettings['tablenames']);
		foreach ($dbt as $obj => $table):
			$tableName = $table->name;
			$tableStructure = $table->structure;
			if( $wpdb->get_var("SHOW TABLES LIKE '{$tableName}'") != $tableName ):
				$sql = "CREATE TABLE {$tableName} ( {$tableStructure} );";
				dbDelta( $sql );
			endif;
		endforeach;
		$dbt = '';	
	}

	public static function tables( $tableNames ){
		$dbt = new \StdClass;
		$dbt->forms 							= new \StdClass;
		$dbt->forms->name						= $tableNames['forms'];
		$dbt->forms->structure  			= 
			'form_id mediumint UNSIGNED NOT NULL,
			settings text NOT NULL,
			UNIQUE KEY form_id (form_id)';

		
		$dbt->fields 							= new \StdClass;
		$dbt->fields->name 	   			= $tableNames['fields'];
		$dbt->fields->structure 			= 
			'ID bigint UNSIGNED NOT NULL AUTO_INCREMENT,
			input_type text NOT NULL, 
			form_id mediumint UNSIGNED NOT NULL, 
			input_values text NOT NULL, 
			input_order mediumint UNSIGNED NOT NULL,
			input_settings text NOT NULL, 
			UNIQUE KEY ID (ID)';


		$dbt->reusablefields 					= new \StdClass;
		$dbt->reusablefields->name 	   	= $tableNames['reusablefields'];
		$dbt->reusablefields->structure 		= 
			'ID bigint UNSIGNED NOT NULL AUTO_INCREMENT,
			input_type text NOT NULL, 
			field_label text NOT NULL, 
			input_values text NOT NULL, 
			input_settings text NOT NULL,
			input_order mediumint UNSIGNED NOT NULL, 
			UNIQUE KEY ID (ID)';		
		return $dbt;
	}
}
