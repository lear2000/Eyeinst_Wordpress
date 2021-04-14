<?php
namespace aform\admin\integration;

class init{

	////////////////////////////////////////////////////////////////////
	public $autoloads;//do not delete, this triggers our include method to load this class
	////////////////////////////////////////////////////////////////////
	
	public $POSTTYPE;
	public $PREFIX;
	public $PARENTPOSTTYPE;

	public function __construct(){
		$this->PARENTPOSTTYPE = aFormSettings('cptname');
		$this->POSTTYPE = aFormSettings('cptnameinteg');
		$this->PREFIX = aFormSettings('prefix');

		add_action('init' , array($this,'init'));
		add_action('aform/adminmenu' , array($this,'adminInit'));

		add_action('edit_form_advanced' , function(){
			$cpt = get_post_type();
			if($cpt == $this->POSTTYPE):
				\aform\core\view::getLayout('integration-edit');
			endif;
		});

		add_action( 'admin_enqueue_scripts' , array( &$this , 'scripts') );

		//save
		add_action( 'save_post' , array(&$this , 'savePost') );

	}
	public function savePost(){
		global $post , $post_type , $aformPlugin;
		if($post_type == 'aform_integ'):
			

			$settings = (isset($_POST['_integ'])) ? $_POST['_integ'] : array();
			update_post_meta($post->ID , '_integ' , $settings );

		endif;
	}
	public function init(){
		$prefix = $this->PREFIX;
		$_LABELS3 = array(
				'name'								=> __( 'Integrations' , $prefix ),
				'singular_name'				=> __( 'Integrations' , $prefix ),
				'add_new'							=> __( 'Add New Integration', $prefix),
				'add_new_item'				=> __( 'Add New Integration', $prefix),
				'edit_item'						=> __( '&nbsp;' , $prefix ),
				'view_item'						=> __( 'View Integration', $prefix ),
				'search_items'				=> __( 'Search Integrations', $prefix ),
				'not_found'						=> __( 'No Integration found', $prefix ),
				'not_found_in_trash'	=> __( 'No Integration found in Trash', $prefix ), 
			);
			$_INTEGRATIONARGS = array(
				'labels'  					=> $_LABELS3 ,
				'public'						=> false,
				'show_ui'						=> true,
				'_builtin'					=> false,
				'capability_type'		=> 'post',
				'capabilities'			=> array(
					'edit_post'			=> 'manage_options',
					'delete_post'		=> 'manage_options',
					'edit_posts'		=> 'manage_options',
					'delete_posts'	=> 'manage_options',
				),
				'rewrite' 							=> false,//only used to write form name(js)
				'hierarchical'					=> false,
				'query_var'							=> false,
				'supports' 							=> array('title'),
				'show_in_menu'					=> false,
 				'show_in_admin_bar'   	=> false,
        'show_in_nav_menus'   	=> true,
        'can_export'          	=> true,
        'exclude_from_search' 	=> true			
			);
			register_post_type( $this->POSTTYPE , $_INTEGRATIONARGS );
	}
	public function adminInit($parentadmin){
		//add_submenu_page("edit.php?post_type={$parentadmin}" , __('Integrations',$this->PREFIX), __('Integrations',$this->PREFIX), 'read', "edit.php?post_type={$this->POSTTYPE}");
	}

	public function scripts(){
		global $pagenow , $post_type;
		if( $post_type == $this->POSTTYPE && in_array( $pagenow , array('post.php','post-new.php')) ):
			wp_register_script('aform-mc' , aFormSettings('dir') . 'assets/vue/integration.mc.js' , array() , time() , true );
			wp_enqueue_script('aform-mc');
		endif;	
	}



}