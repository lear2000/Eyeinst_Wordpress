<?php
namespace aform\core\form;
class fileupload{
	public static function ajaxmove( $files ){
		$sendtofield = array();
		
		$wp_upload = wp_upload_dir();
		$wp_upload['basedir'];
		$uploadfolder = aFormSettings('uploadfolder');
		$uploadDir = $wp_upload['basedir'] . DS . $uploadfolder;		
		$uploadUrl = $wp_upload['baseurl'] . DS . $uploadfolder;

		foreach($files as $key => $file ):
			if(file_exists($uploadDir)):
				if( move_uploaded_file( $file['tmp_name'] , $uploadDir . DS . $file['newname'] ) ):
					$pushto = $file['pushto'];
					$sendtofield[$pushto][] = $uploadUrl . DS . $file['newname'];
				endif;
			endif;
		endforeach;
		return $sendtofield;

	}
	public static function phpmove( $files , $fieldData = null ){		
		
		$sendtofield = array();
		
		$wp_upload = wp_upload_dir();
		$wp_upload['basedir'];
		$uploadfolder = aFormSettings('uploadfolder');
		$uploadDir = $wp_upload['basedir'] . DS . $uploadfolder;		
		$uploadUrl = $wp_upload['baseurl'] . DS . $uploadfolder;

		$finfo = finfo_open(FILEINFO_MIME_TYPE);
		
		$notSupportedExt = array('php');//will most likely pull from a config files/database

		foreach($files as $key => $file ):
			if(file_exists($uploadDir)):
				$pushto = $file['pushto'];
				
				$acceptedFiles = $fieldData[$pushto]->input->settings->use_extensions;
				
				$fileinfo = finfo_file( $finfo , $file['tmp_name'] );	
				$fileparts = pathinfo( $file['name'] );
				$fileExt = strtolower($fileparts['extension']);

				if(in_array( $fileExt , $acceptedFiles )):
					if(!in_array( $fileExt , $notSupportedExt )):
						if( move_uploaded_file( $file['tmp_name'] , $uploadDir . DS . $file['newname'] ) ):
							$sendtofield[$pushto][] = $uploadUrl . DS . $file['newname'];
						endif;
					else:
						$sendtofield[$pushto][] = $file['name'] ." [ {$fileinfo} | {$fileExt} -- Not Supported ]";
					endif;
				
				else:
					$sendtofield[$pushto][] = $file['name'] ." [ {$fileinfo} -- Not Supported ]";
				endif;
			endif;
		endforeach;
		return $sendtofield;

	}

	public static function prepareFiles( $fileData = null , $r = null , $isAjax = null ){
		$fieldnames = array_keys( $fileData['name'] );
		$files = array();
		$timestamp =  explode (' ', microtime());
		$timestamp = $timestamp[1];
		
		$uploadType = ( isset($r['iframe']) && $r['iframe'] == true ) ? 'IFRAME' : 'WEBAPI';
		$uploadType = ( isset($isAjax) && $isAjax == true ) ? $uploadType : 'PHP';
			foreach( $fileData as $key => $value ):
				foreach( $fieldnames as $fn ):
					switch ( $uploadType ):
						case 'IFRAME':
						case 'PHP':
							foreach($value[$fn] as $i => $v):
								if(empty($v) || $v == 4) continue;
								$files[$fn."--{$i}"][$key] = $v;	
								if($key == 'name'):
									$fileinfo = pathinfo($v);
									$newname = preg_replace( '/(\s+|\,)/', '-', $fileinfo['filename'] ) . '--' . $timestamp .'.'.$fileinfo['extension'];						
									$files[$fn."--{$i}"]['newname'] = $newname;
									$files[$fn."--{$i}"]['pushto'] = $fn; 
								endif;
								$timestamp++;	
							endforeach;
						break;
						case 'WEBAPI':
							$files[$fn][$key] = $value[$fn];
							if($key == 'name'):
								$fileinfo = pathinfo($value[$fn]);
								$newname = preg_replace( '/(\s+|\,)/', '-', $fileinfo['filename'] ) . '--' . $timestamp .'.'.$fileinfo['extension'];
								$files[$fn]['newname'] = $newname;
								$fieldnametemp = explode('--', $fn);
								$files[$fn]['pushto'] = $fieldnametemp[0]; 
							endif;
							$timestamp++;
						break;
					endswitch;/* end of switch */
				endforeach;
			endforeach;	
		return $files;
	}

	/* used by php */
	public static function checkFile( $file = null , $settings = null){
		
		if(empty($file)) return;

		$errors = array();
		$filename = $file['name'];
		
		$max_filesize = $settings->max_filesize * 1024;
		$use_extensions = $settings->use_extensions;

		$usevalid = join(',' , $settings->use_extensions);
		
		$fileinfo = pathinfo( $file['name'] );
		$ext = strtolower($fileinfo['extension']);
		
		if(!in_array( $ext, $use_extensions ) ):
			$errors[$filename][] = ".{$ext} is not a valid extension.";
		endif;
		if( ($file['size'] / 1024) > $max_filesize ):
			$errors[$filename][] = "File size must be less than {$max_filesize}KB ({$settings->max_filesize}MB).";
		endif; 

		return $errors;
	
	}
	
	// used by ajax
	public static function checkMime( $realtype=null , $senttype=null , $filename=null){
			/*
				file type supported
				jpg  png  gif pdf  doc  docx ppt  pptx  odt avi  ogg  m4a mov  mp3  mp4 mpg  wav  wmv
			*/
			//type checked by server
			$realtype = explode('/', $realtype);
			$realtype = (isset($realtype[1])) ? $realtype[1] : $realtype[0];
			//type sent by client
			$senttype = explode('/', $senttype);
			$senttype = (isset($senttype[1])) ? $senttype[1] : $senttype[0];

			switch ($realtype):
				case 'jpeg':
					$realtype = 'jpg';
				break;
				case 'vnd.adobe.photoshop':
					$realtype = 'psd';
				break;
				case 'quicktime':
					$realtype = 'mov';
					if($senttype == 'mp4'):
						$realtype = 'mp4';
					endif;
				break;
				case 'msword':
					$realtype = 'doc';
				break;
				case 'vnd.ms-office':
				case 'vnd.ms-powerpoint':
					$realtype = 'ppt';
				break;
				case 'mp4':
					if(in_array($senttype , array(
						'x-m4a',
						'm4a'
						))):
						$realtype = 'm4a';
					endif;
				break;
				case 'x-msvideo':
					$realtype = 'avi';
				break;
				case 'x-matroska':
					$realtype = 'mkv';
				break;
				case 'x-ms-asf':
					$realtype = 'wmv';
				break;
				case 'x-wav':
					$realtype = 'wav';
				break;
				case 'mpeg':
					if(in_array($senttype , array(
						'mp3'
						))):
						$realtype = 'mp3';
					endif;
					if(in_array($senttype , array(
						'mpeg'
						))):
						$realtype = 'mpg';
					endif;
				break;
				case 'zip':
					if( in_array($senttype, array(
						'vnd.openxmlformats-officedocument.wordprocessingml.document',
						'vnd.openxmlformats-officedocument.wordprocessingml.document'
						))):
						$realtype = 'docx';
					endif;
					if(in_array($senttype , array(
						'xml'
						))):
						$realtype = 'xml';
					endif;
					if(in_array($senttype , array(
						'vnd.openxmlformats-officedocument.presentationml.presentation',
						'vnd.openxmlformats-officedocument.presentationml.presentation'
						))):
						$realtype = 'pptx';
					endif;
				break;
				case 'vnd.openxmlformats-officedocument.wordprocessingml.document':
					$realtype = 'docx';
				break;
				case 'vnd.openxmlformats-officedocument.presentationml.presentation':
					$realtype = 'pptx';
				break;
				case 'html':
					if($senttype == 'svg+xml'):
						$realtype = 'svg';
					endif;
				break;
				case 'plain':
				case 'octet-stream':
					$realtype = $senttype;
					if(!empty($filename)):
						$filename = explode('.',$filename);
						if(count($filename) > 1):
							$filename = array_reverse($filename);
							$realtype = strtolower($filename[0]);
						endif;
					endif;
				break;
			endswitch;

			return $realtype;	
	}



}