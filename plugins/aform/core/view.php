<?php  
namespace aform\core;
class view{
	
	public static function metabox( $post , $args ){
		$args = $args['args'];
		$filepath =  aFormSettings('viewsdir') . 'metabox/' . $args['metabox'] . '.php';
		if(file_exists($filepath)):
			include $filepath;
		endif;
	}
	public static function field( $type = null , $data = null , $scope = null ){
		$file = aFormSettings('viewsdir') . 'form/field/' . $type . '.php';
		if(file_exists($file)):
			include $file;
		endif;
	}
	public static function fieldsTmpl( $file = null , $self = null , $fieldData = null , $index = null , $func = null ){
		$file =  aFormSettings('viewsdir') . 'fields-tmpl/'.$file.'.php';
		if(file_exists($file)):
			if(is_callable($func)):
				call_user_func($func , $file);
			else:
				include $file;
			endif;
		endif;
	}
	public static function reusableField( $field ){
		$filepath =  aFormSettings('viewsdir') . 'metabox/reusablefield-item.php';
		if(file_exists($filepath)):
			ob_start();
				include $filepath;
			return ob_get_clean();
		endif;
	}
	public static function getLayout($filename , $args = null){
		$file =  aFormSettings('viewsdir') . 'layouts/'.$filename.'.php';
		if(file_exists($file)):
			include $file;
		endif;
	}
	public static function submenuPage(){
		global $post;
		$cptname = aFormSettings('cptname');
		$screen = get_current_screen();
		if(isset($_GET['page'])):
			$subpage = $_GET['page'];
			$subpage = str_replace($cptname.'-' , '' , $subpage);
			$filepath =  aFormSettings('viewsdir') . 'subpage/' . $subpage . '.php';
			if(file_exists($filepath)):
				include $filepath;
			endif;
		endif;
	}	
}