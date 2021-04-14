<?php  
namespace aform\admin;

class wpinit{

	////////////////////////////////////////////////////////////////////
	public $autoloads;//do not delete, this triggers our include method to load this class
	////////////////////////////////////////////////////////////////////
	
	public function __construct(){
		global $aformPlugin;
		add_action( 'init' , array(&$this, 'postType') , 5 );
		add_action( 'admin_menu', array(&$this, 'adminMenu') );
		add_action( 'admin_enqueue_scripts' , array( &$this , 'scripts') );
		add_action( 'admin_notices', array( &$this , 'notices') );
		add_action( 'admin_init', array(&$this , 'registerSettings') );
		

		/* DELETE FORM DATA */
		add_action( 'delete_post' , array( _AFORMDB() , 'deletePost') );
		add_filter( 'user_has_cap', array(&$this , 'userHasCaps'), 10, 3 );
		add_action( 'admin_head', function(){
			?>
			<style>
			#toplevel_page_edit-post_type-aform .wp-menu-image:before { content: "\f465";}
			.type-aform_sub .inline.hide-if-no-js{ display: none; }
			#sub_id{
				width: 75px;
			}
			#formnameid{
				width: 150px;
			}
			</style>
			<?php
			if(get_post_type() == aFormSettings('cptnamesub') ):
			?>
				<style>
					@media screen and (min-width: 961px){
						#adminmenu #toplevel_page_edit-post_type-aform > ul{
							position: static;
						}
						.folded #adminmenu #toplevel_page_edit-post_type-aform > ul{
							position: absolute;
						}
					}
					
					#submitpost #minor-publishing{
						display: none;
					}
					#postbox-container-2 #normal-sortables , #post-body #post-body-content{
						display: none;
					}
					#the-list tr > *{
						border-bottom: 1px solid #dfdfdf;
					}
				</style>
			<?php
			endif;
		});

		/* Custom Column */
		$cpt = aFormSettings('cptname');
		$cptSubmissions = aFormSettings('cptnamesub');
		add_filter( "manage_{$cpt}_posts_columns", array(&$this , 'managePostColumns') );
		add_action( "manage_{$cpt}_posts_custom_column" , array(&$this , 'managePostColumnsData') , 10, 2 );

		add_filter( "manage_{$cptSubmissions}_posts_columns", array(&$this,'managePostColumnsSubmissions'));
		add_action( "manage_{$cptSubmissions}_posts_custom_column" , array(&$this,'managePostColumnsSubmissionsData'), 10, 2 );
		
		/* Filter Submissions */
		add_action( 'restrict_manage_posts' , array(&$this , 'submissionsManagePosts'));
		

		// adds duplicate form link
		add_filter( 'page_row_actions' , array(&$this , 'addRowLinks') , 10 , 2 );
		add_filter( 'post_row_actions' , array(&$this , 'addRowLinks') , 10 , 2 );
		add_action( 'admin_action_duplicateaform', array( &$this , 'duplicateForm' ) );
		
		/* admin bar custom */
		add_action('admin_bar_menu', array(&$this , 'BarMenu'), 999);

		/* edit custom */
		add_filter( 'enter_title_here', array(&$this , 'enterTitleHere') );


		add_filter('screen_layout_columns', function($columns){
			if( aFormSettings('cptname') == get_post_type() ):
				$columns['post'] = 2;
			endif;
			return $columns;
		});
		add_filter("get_user_option_screen_layout_{$cpt}",function(){
			if( aFormSettings('cptname') == get_post_type() ):
				return 2;
			endif;	
		});
	}
	public function userHasCaps($allcaps, $cap, $args){
		$sub_cap = 'manage_options';
     	if (!empty($allcaps[$sub_cap])) {
         $allcaps['aform_sub'] = true;
     	}
     return $allcaps;	
	}
	public function enterTitleHere( $title ){
		$cpt = aFormSettings('cptname');
		$screen = get_current_screen();

		if ( $cpt == $screen->post_type ):
			 $title = 'Enter Form Name Here';
		endif;

		return $title;
	}
	public function registerSettings(){
		register_setting( 'aform-settings', '_aform_settings');
	}

	public function BarMenu($wp_admin_bar){
		global $post , $pagenow;
		$cptname = aFormSettings('cptname');
		if( get_post_type() == $cptname && ( isset($_GET['action']) && $_GET['action'] == 'edit' ) || ( in_array( $pagenow , array('post.php','post-new.php')) && isset($_GET['post_type']) && $_GET['post_type'] == $cptname ) ):

			$wp_admin_bar->add_node(array(
			'id' => 'AvailableAForms',
			'title' => '<span style="color:#ffffff;display:inline-block;vertical-align: text-top;" class="dashicons-before dashicons-email"></span> <i style="display:inline-block;vertical-align:middle;color:#ffffff;font-style:normal;">aForms</i>', 
			'href' => admin_url( "edit.php?post_type={$cptname}"), 
			'meta' => array(
				'class' => 'available-aforms', 
				'title' => 'Available aForms'
				)
			));

			$allForms = get_posts(array(
				'post_type' 		=> $cptname,
				'posts_per_page' 	=> -1,
				'exclude'			=> $post->ID
			));

			if(!empty($allForms)):
				foreach($allForms as $allForm):
					$wp_admin_bar->add_node(array(
						'id' 		=> "AvailableAForms{$allForm->ID}",
						'title' 	=> $allForm->post_title,
						'href'		=> get_edit_post_link($allForm->ID),
						'parent' 	=> 'AvailableAForms', 
					));
				endforeach;
			endif;

		endif;

	}

	/** 
	* Custom Column
	*/
	public function managePostColumns( $columns ){
		$columns['shortcode'] = __('Shortcode' , aFormSettings('cptname') );
		$columns['submissioncount'] = __('Submissions' , aFormSettings('cptname') );
		return $columns;
	}
	public function managePostColumnsSubmissions( $columns ){
		$columns = array();
		$columns['cb'] = '<input type="checkbox" />';
		$columns['sub_id'] = 'ID';
		$columns['formnameid'] = 'Form';
		$columns['sub_preview'] = 'Preview';
		$columns['meta'] = 'Meta';
		$columns['date'] = 'Date';

		return $columns;
	}
	public function managePostColumnsData( $column , $post_id ){
		switch ( $column ):
			case 'shortcode':
				$form = get_post($post_id);
				echo '[aform name="'.$form->post_name.'"]';
			break;
			case 'submissioncount':
				echo _AFORMDB()->getFormSubmissionCount($post_id);
				
			break;

		endswitch;
		//return $column;
	}
	public function managePostColumnsSubmissionsData($column , $postId){
		$submission = get_post($postId);
		switch ( $column ):
			case 'sub_preview':
				$previewString = array();
				$submittedFields = _AFORMDB()->getSubmissionFields( $postId , 3 );
				if(!empty($submittedFields)):
					foreach($submittedFields as $index => $field):
						$fieldData = maybe_unserialize($field->meta_value);
						$fieldData = _aFormMakeObject($fieldData);
						if(!empty($fieldData->value)):
							if(strlen($fieldData->value) > 25 && !filter_var($fieldData->value, FILTER_VALIDATE_EMAIL) ):
								$fieldData->value = substr($fieldData->value , 0 , 25).'...';
							endif;
							$previewString[] = "&#8226; <strong>{$fieldData->display}:</strong> {$fieldData->value}<br>";
						endif;
					endforeach;
					echo '<p><a style="color:#666666;" href="'.get_edit_post_link( $postId ).'">'.implode('',$previewString).'</a></p>';
				else:
					echo 'No Data';
				endif;

			break;
			case 'sub_id':
				echo "<a href=\"".get_edit_post_link( $postId )."\">#{$postId}</a>";
			break;
			case 'meta':
				$utmTracking = get_post_meta( $postId , 'utmtracking', false );
				if($utmTracking != false):
					$utmTrackingText = array();
					foreach($utmTracking[0] as $KEY => $VAL):
						$utmTrackingText[] = "<span><b>{$KEY}:</b> {$VAL}</span>";
					endforeach;
				?>
					<div title="Campaign Tracking" class="aform--meta-item aform-meta--utm"><label>CT</label> <div class="aform-meta--utm-info"><div><?php echo implode('', $utmTrackingText); ?></div></div></div>
				<?php
				endif;

			break;
			case 'formnameid':
				$formname = get_the_title($submission->post_parent);
				$formname = (!empty($formname)) ? "$formname" : $submission->post_parent;
				echo "{$formname}";
			break;
		endswitch;
	}
	/** 
	* Custom Manage Posts
	*/	
	public function submissionsManagePosts($postType){
		if($postType == aFormSettings('cptnamesub')):
			

			$_FORMS = _AFORMDB()->getFormPosts('ID,post_title');
		?>
			<select name="aform_id">
				<option value=""><?php _e('All Forms', $postType); ?></option>
				<?php $selected = (isset($_GET['aform_id']) && !empty($_GET['aform_id'])) ? $_GET['aform_id'] : false;?>
				<?php if(!empty($_FORMS)): 
				foreach($_FORMS as $form): ?>
					<option <?php selected( $selected, $form->ID ); ?> value="<?php echo $form->ID;?>"><?php echo $form->post_title; ?></option>
				<?php endforeach; endif;?>
			</select>
		<?php
		endif;
	}
	// public function submissionsParseQuery($query){
	// 	global $pagenow;
 //   	$postType = isset( $_GET['post_type'] ) ? $_GET['post_type'] : '';
 //   	if ( is_admin() 
 //   		&& $postType == aFormSettings('cptnamesub') 
 //   		&& $pagenow == 'edit.php'
 //     		&& isset( $_GET['aform_id'] ) 
 //     		&& $_GET['aform_id'] != ''):
 //   		$query->query_vars['meta_key'] = 'aform_id';
 //    		$query->query_vars['meta_value'] = $_GET['aform_id'];
 //     	endif;
 //     	return $query;
	// }
	/** 
	* Page Row Actions
	*/
	public function addRowLinks( $actions , $post ){
		
		if(get_post_type() == 'aform' && current_user_can('edit_posts') ):
			if( isset($_GET['post_status']) && $_GET['post_status'] == 'trash'):
				return $actions;
			endif;

			$link = admin_url("admin.php?action=duplicateaform&post={$post->ID}");
			$actions['duplicateaform'] = '<a href="'.$link.'" title="Duplicate Form">Duplicate Form</a>';
		endif;

		return $actions;
	}

	/*
	https://rudrastyh.com/wordpress/duplicate-post.html
	*/
	public function duplicateForm(){
		if (! ( isset( $_GET['post']) || isset( $_POST['post'])  || ( isset($_REQUEST['action']) && 'duplicateaform' == $_REQUEST['action'] ) ) ):
			wp_redirect( admin_url( 'edit.php?post_type=' . aFormSettings('cptname') ) );
			exit;
		endif;
		
		if( $_GET['action'] != 'duplicateaform' ):
			wp_redirect( admin_url( 'edit.php?post_type=' . aFormSettings('cptname') ) );
			exit;
		endif;
		/*
		* get the original post id
		*/
		$post_id = (isset($_GET['post']) ? absint( $_GET['post'] ) : absint( $_POST['post'] ) );
		/*
		* and all the original post data
		*/
		$post = get_post( $post_id );



		if (isset( $post ) && $post != null):

			$args = array(
				'comment_status' => $post->comment_status,
				'ping_status'    => $post->ping_status,
				'post_author'    => $post->post_author,
				'post_content'   => $post->post_content,
				'post_excerpt'   => $post->post_excerpt,
				'post_name'      => $post->post_name,
				'post_parent'    => $post->post_parent,
				'post_password'  => $post->post_password,
				'post_status'    => 'draft',
				'post_title'     => "Cloned : {$post->post_title}",
				'post_type'      => $post->post_type,
				'to_ping'        => $post->to_ping,
				'menu_order'     => $post->menu_order
			);
			
			/**
			 * form Settings we're duplicating
			 */
			$formSettings = _AFORMDB()->getSettings($form_id = $post->ID , ARRAY_A );

			$formFields = _AFORMDB()->getFields( $formID = $post->ID , $orderBy = 'input_order' , $select = '*' , $outputType = ARRAY_A );

			/**
			 * create record so we can get an ID
			 */

			$newFormiID = wp_insert_post(array( 'post_type' => $post->post_type )); 

			/** 
			*	Update Record 
			*/
			
			$args['ID'] = $newFormiID;

			$post->post_title = preg_replace('/^copy(.*):: /i' ,'',$post->post_title);

			$args['post_title'] = "Copy-{$newFormiID} :: {$post->post_title}";
			
			$args['post_name'] = "{$post->post_name}-{$newFormiID}";

			wp_insert_post( $args );// update

			/**
			* Copy Form Settings
			*/
			if(!empty($formSettings)):
				$formSettings['form_id'] = $newFormiID;
				_AFORMDB()->saveSettings( $formSettings['form_id'] , $formSettings['settings'] , $isJSON = true );
			endif;

			/**
			* Copy Form Fields
			*/
			if(!empty($formFields)):
				foreach($formFields as $index => $ff ):
					$ff['form_id'] = $newFormiID;
					unset($ff['ID']);
					_AFORMDB()->insertField($ff);
				endforeach;
			endif;

			wp_redirect( admin_url( 'edit.php?post_type=' . aFormSettings('cptname') ) );

			exit;

		endif;

	}

	public function notices(){
		global $pagenow,$post,$aformPlugin;
		$wp_upload_dir = wp_upload_dir();
		$wp_upload_dir = $wp_upload_dir['basedir'];

		if(get_post_type() == 'aform' || ( isset($_GET['page']) && $_GET['page'] == 'aform-dashboard' ) || $pagenow == 'plugins.php'):
			if(!is_writable($wp_upload_dir)):
				printf( '<div class="error"><p>%s</p></div>', 
						"WordPress <strong>Uploads</strong> Folder needs to be writable<br>
						- <strong>{$wp_upload_dir}</strong>");	
			endif;
			$aformPluginUploadFolder = $wp_upload_dir . '/' .$aformPlugin->settings['uploadfolder'];
			if(!file_exists( $aformPluginUploadFolder )):
				printf( '<div class="error"><p>%s</p></div>', 
						"<strong>aForm Uploads Folder</strong><br>
						- Not Found : <strong>{$aformPluginUploadFolder}</strong><br>
						- Try Deactivating/Activating the aForm Plugin");
			endif;
		endif;
		
		if( (isset($_GET['post_type']) && $_GET['post_type'] == 'aform') && $pagenow == 'edit.php'):
			$parseArr = array();
			parse_str($_SERVER['QUERY_STRING'] , $parseArr);
			
			if( count($parseArr) == 1):
				global $wpdb;
				$fieldsTable = _AFORMDB()->wpdb['fields'];
				$orphanedFields = $wpdb->get_var( "SELECT COUNT(*) FROM {$fieldsTable} WHERE form_id = 0" );
				if($orphanedFields != 0):
					$wpdb->delete( $fieldsTable , array( 'form_id' => 0 ) ,  array( '%d' )  );
					$fieldtext = ($orphanedFields == 1) ? 'field' : 'fields';
					printf( '<div class="update-nag">%s</div>', 
						"<strong>Maintenance:</strong> {$orphanedFields} Orphaned {$fieldtext} have been deleted.");	
				endif;
				
			endif;
		endif;

	}
	public function postType(){
			$prefix = aFormSettings('prefix');
			$cptname = aFormSettings('cptname');
			$cptlabel = aFormSettings('cptlabel');
			$_LABELS = array(
				'name'					=> __( $cptlabel , $prefix ),
				'singular_name'			=> __( $cptlabel , $prefix ),
			    'add_new'				=> __( 'Add New Form' , $prefix ),
			    'add_new_item'			=> __( 'Add New Form' , $prefix ),
			    'edit_item'				=> __( 'Edit Form' , $prefix ),
			    'new_item'				=> __( 'New Form' , $prefix ),
			    'view_item'				=> __( 'View Form', $prefix ),
			    'search_items'			=> __( 'Search Form', $prefix ),
			    'not_found'				=> __( 'No Form found', $prefix ),
			    'not_found_in_trash'	=> __( 'No Form found in Trash', $prefix ), 
			);
			$_ARGS = array(
				'labels'  			=> $_LABELS ,
				'public'			=> false,
				'show_ui'			=> true,
				'_builtin'			=> false,
				'capability_type'	=> 'post',
				'capabilities'		=> array(
					'edit_post'			=> 'manage_options',
					'delete_post'		=> 'manage_options',
					'edit_posts'		=> 'manage_options',
					'delete_posts'		=> 'manage_options',
				),
				'rewrite' => array('slug' => 'aform-slug'),//only used to write form name(js)
				'hierarchical'		=> false,
				'query_var'			=> false,
				'supports' 			=> array('title'),
				'show_in_menu'		=> false,
			);
			register_post_type( $cptname , $_ARGS );

			
			$_LABELS2 = array(
				'name'					=> __( 'Submissions' , $prefix ),
				'singular_name'			=> __( 'Submissions' , $prefix ),
				'edit_item'				=> __( '&nbsp;' , $prefix ),
				'view_item'				=> __( 'View Submission', $prefix ),
				'search_items'			=> __( 'Search Submissions', $prefix ),
				'not_found'				=> __( 'No Submission found', $prefix ),
				'not_found_in_trash'	=> __( 'No Submission found in Trash', $prefix ), 
			);
			$_SUBMISSIONARGS = array(
				'labels'  			=> $_LABELS2 ,
				'public'				=> false,
				'show_ui'			=> true,
				'_builtin'			=> false,
				'capability_type'	=> 'post',
				'capabilities'		=> array(
					'edit_post'			=> 'manage_options',
					'delete_post'		=> 'manage_options',
					'edit_posts'		=> 'manage_options',
					'delete_posts'		=> 'manage_options',
				),
				'rewrite' => array('slug' => 'aform_sub'),//only used to write form name(js)
				'hierarchical'				=> false,
				'query_var'					=> false,
				'supports' 					=> false,
				'show_in_menu'				=> false,
 				'show_in_admin_bar'   	=> false,
            'show_in_nav_menus'   	=> true,
            'can_export'          	=> true,
            'exclude_from_search' 	=> true,
            'capability_type' => 'aform_sub',
            'capabilities' => array(
                'publish_posts' => 'aform_sub',
                'edit_posts' => 'aform_sub',
                'edit_others_posts' => 'aform_sub',
                'delete_posts' => 'aform_sub',
                'delete_others_posts' => 'aform_sub',
                'read_private_posts' => 'aform_sub',
                'edit_post' => 'aform_sub',
                'delete_post' => 'aform_sub',
                'read_post' => 'aform_sub',
                'create_posts' => false,
            ),				
			);
			register_post_type( aFormSettings('cptnamesub') , $_SUBMISSIONARGS );
			

		}
	public function adminMenu(){
		$prefix = aFormSettings('prefix');
		$cptname = aFormSettings('cptname');
		$cptlabel = aFormSettings('cptlabel');
		add_menu_page(__($cptlabel,$prefix), __($cptlabel,$prefix), 'manage_options', 'edit.php?post_type='.$cptname, false, false, '43');
		add_submenu_page('edit.php?post_type='.$cptname, __('Add New Form',$prefix), __('Add New Form',$prefix), 'manage_options','post-new.php?post_type='.$cptname );
		add_submenu_page('edit.php?post_type='.$cptname , __('Submissions',$prefix), __('Submissions',$prefix), 'read', "edit.php?post_type=" . aFormSettings('cptnamesub') );
		
		do_action('aform/adminmenu' , $cptname );

		add_submenu_page( 'edit.php?post_type=' . $cptname, __('Settings'), __('Settings'), 'manage_options', $cptname.'-settings' , array( 'aform\\core\\view' , 'submenuPage') );

	}

	public function scripts(){
		global $pagenow , $post_type;
		$screen = get_current_screen();
		$pluginUri = aFormSettings('dir');
		$cptname = aFormSettings('cptname');
		$cptnameSub = aFormSettings('cptnamesub');
		$cptnameInteg = aFormSettings('cptnameinteg');
		

		

		if( ( ( in_array($post_type , array( $cptname )) && in_array( $pagenow , array('post.php','post-new.php')) ) 
		|| ( $pagenow == 'edit.php' && $screen->base == 'aform_page_aform-dashboard') 
		|| ( isset($_GET['page']) && $_GET['page'] == $cptname.'-settings') )):

		
			wp_register_style( $handle = 'af-bootstrap', $src = $pluginUri . 'vendor/bootstrap/css/mybootstrap.css' );
			wp_enqueue_style( $handle = 'af-bootstrap' );
			
			wp_enqueue_media();//removes error caused when we don't include an editor
			wp_enqueue_style( $handle = 'aforms-global' , $src = $pluginUri . 'assets/css/cpt.css', $deps=false, $ver='1', $media ='');
			
			wp_register_style( $handle = 'af-fontawesome', $src = $pluginUri . 'vendor/fontawesome/css/font-awesome.min.css' );
			wp_enqueue_style( $handle = 'af-fontawesome' );

		endif;

		if($post_type == $cptname ):		
			wp_register_script( 
				$handle = 'add-edit-form', 
				$src = $pluginUri . 'assets/js/add-edit-form.js', 
				$deps = array('jquery', 'jquery-ui-dialog' ,'jquery-ui-draggable','jquery-ui-droppable','jquery-ui-sortable','jquery-ui-slider'), 
				$ver = '1', 
				$in_footer = true );
			
			wp_enqueue_script( 'add-edit-form' );
			wp_enqueue_style("wp-jquery-ui-dialog");

		elseif( $screen->base == 'aform_page_aform-dashboard' ):

			wp_register_script( 'paginathor', $pluginUri . 'assets/js/paginathor.js', array(), false, true );
			wp_register_script( 'fancybox', $pluginUri . 'assets/js/libs/fancybox/jquery.fancybox.pack.js', array( 'jquery' ), false, true );
			wp_enqueue_style( 'jquery-ui-css', '//ajax.googleapis.com/ajax/libs/jqueryui/1.11.4/themes/smoothness/jquery-ui.css', false, '1.11.4', '' );
			wp_enqueue_style( 'fancybox-css', $pluginUri . 'assets/js/libs/fancybox/jquery.fancybox.css', false, 1, '' );			
			wp_register_script( 'submissions-page', $pluginUri . 'assets/js/submissions-page.js', array( 'jquery', 'jquery-ui-dialog' , 'jquery-ui-datepicker', 'fancybox' , 'paginathor'), false, true );
			wp_enqueue_script ( 'submissions-page' );	

		endif;

	}	
}