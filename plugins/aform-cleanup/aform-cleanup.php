<?php
/*
Plugin Name: Clean Aforms
Description: Clear out Aforms Uploads Once Per Week
*/

// Scheduled Action Hook
function delete_aforms_uploads( ) {
// get upload directory
$upload_dir = wp_upload_dir();

// Aforms DIR
$aform_dir = $upload_dir['basedir'].'/aform/uploads';

error_log($aform_dir);

$dir = $aform_dir;
$di = new RecursiveDirectoryIterator($dir, FilesystemIterator::SKIP_DOTS);
$ri = new RecursiveIteratorIterator($di, RecursiveIteratorIterator::CHILD_FIRST);
foreach ( $ri as $file ) {
    $file->isDir() ?  rmdir($file) : unlink($file);
    error_log("Delete Aform File " .$file);
    usleep('250000');
}
return true;

}
add_action( 'delete_aforms_uploads', 'delete_aforms_uploads' );

// Custom Cron Recurrences
function clear_aforms_data_recurrence( $schedules ) {
	$schedules['weekly'] = array(
		'display' => __( 'Once Per Week', 'textdomain' ),
		'interval' => 604800,
	);
	return $schedules;
}
add_filter( 'cron_schedules', 'clear_aforms_data_recurrence' );

// Schedule Cron Job Event
function clear_aforms_data() {
	if ( ! wp_next_scheduled( 'delete_aforms_uploads' ) ) {
		wp_schedule_event( current_time( 'timestamp' ), 'weekly', 'delete_aforms_uploads' );
	}
}
add_action( 'wp', 'clear_aforms_data' );

 ?>