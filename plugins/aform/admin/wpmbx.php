<?php
namespace aform\admin;

class wpmbx{

	////////////////////////////////////////////////////////////////////
	public $autoloads;//do not delete, this triggers our include method to load this class
	////////////////////////////////////////////////////////////////////
	
	public function __construct(){
		$cptName = aFormSettings( 'cptname' );
		$cptSubm = aFormSettings('cptnamesub');
		
		add_action( "add_meta_boxes_$cptName", array(&$this , 'metaboxes') );
		add_action( "add_meta_boxes_$cptSubm", array(&$this , 'metaboxesSub') );
		add_action( 'save_post' , array(&$this , 'savePost') );
		add_action( 'edit_form_after_title', array(&$this , 'afterTitle'));

	}
	public function metaboxesSub($post){
		global $post_type;
		add_meta_box( $id = 'submissionpost', $title = '-', $callback = array( 'aform\\core\\view' , 'metabox' ) , $screen = $post_type , $context = 'advanced', $priority = 'core' , $callback_args = array(
				'metabox' => 'viewsubmission'
			) );
	}
	public function afterTitle(){
		$cptName = aFormSettings( 'cptname' );
		if( get_post_type() == $cptName ): 
			?>
			<?php 
				if( isset($_GET['action']) && $_GET['action'] == 'edit'):
				global $post;
				do_action('aform/admin/before-shortcode');
				?>						
					<div id="afsc" class="clearfix" style="margin-top:10px;">
						<p class="afsc-edit-controls"><a class="afsc-close hidden"><span class="dashicons dashicons-no"></span></a><a class="afsc-edit"><span><i class="fa fa-pencil" aria-hidden="true"></i></span></a></p>					
						<p class="afsc-name">[aform name="<span class="post_name"><?php echo ($post->post_name);?></span>"]</p>
						<p class="afsc-input hidden"><input type="text" class="widefat" name="post_name" value="<?php echo ($post->post_name);?>"></p>
						<div class="hidden permalink-ph"></div>
					</div>
					<div style="display:none;">
						<div id="shortcodeUpdateDialog">
							
							<p><center>You're about to change the name/slug of this form.</center></p>
							<p><center>An update to the shortcode will need to be applied to any page, template and/or function.</center></p>
						</div>
					</div>								
				<?php
				do_action('aform/admin/after-shortcode');
				endif;
			?>
			<ul id="formTabs" data-tabfor="#formFields,#formsettings">
				<li class="selected"><a href="#formFields"><i class="fa fa-tasks" aria-hidden="true"></i> Fields</a></li>
				<li><a href="#formsettings"><i class="fa fa-cogs" aria-hidden="true"></i> Settings</a></li>
			</ul>

			<?php
		endif;
	}
	public function metaboxes( $post ){
			
			global $post_type;
			
			add_filter('screen_options_show_screen', '__return_false');

			add_meta_box( $id = 'availablefields', $title = 'Standard Fields', $callback = array( 'aform\\core\\view' , 'metabox' ) , $screen = $post_type , $context = 'side', $priority = 'core' , $callback_args = array(
				'metabox' => 'fields'
			) );
			
			add_meta_box( $id = 'formFields', $title = 'Form Fields', $callback = array( 'aform\\core\\view' , 'metabox' ) , $screen = $post_type , $context = 'advanced', $priority = 'core' , $callback_args = array(
				'metabox' => 'formfields',
			) );
			
			add_meta_box( $id = 'formsettings', $title = 'Form Settings', $callback = array( 'aform\\core\\view' , 'metabox' ) , $screen = $post_type , $context = 'advanced', $priority = 'core' , $callback_args = array(
				'metabox' => 'formsettings'
			) );

			add_meta_box( $id = 'reusablefields', $title = 'Reusable Fields', $callback = array( 'aform\\core\\view' , 'metabox' ) , $screen = $post_type , $context = 'side', $priority = 'core' , $callback_args = array(
				'metabox' => 'reusablefields'
			) );
			
			remove_meta_box( 'slugdiv', $post_type , 'normal' );
	}
	
	public function savePost($post_id){
		global $post , $post_type , $aformPlugin;
		$cpt = aFormSettings('cptname');
		if(  $post_type == $cpt):
			if( defined('DOING_AUTOSAVE') && DOING_AUTOSAVE ) return;
			if( ! ( wp_is_post_revision( $post_id) && wp_is_post_autosave( $post_id ) ) ):
					if(isset($_POST['aform-fields'])):
						_AFORMDB()->saveFields( $_POST['aform-fields'] );	
					endif;
					
					if(isset($_POST['form-settings'])):
						$formSettings = $_POST['form-settings'];
						$formSettings = apply_filters( 'aform/admin/before/save', $formSettings );					
						_AFORMDB()->saveSettings( $post_id, $formSettings );
					endif;
					/**/
					do_action('aform/admin/save');
			endif;
		endif;
	}
	
}