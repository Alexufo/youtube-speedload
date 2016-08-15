<?php


if (!function_exists( 'curl_version' )) {
	add_action( 'admin_notices', 'ytsl_curl' );
	function ytsl_curl() {
		echo	"<div class=\"notice notice-error\"><p>".__('Youtube-speedload require CURL extension', 'ytsl-textdomain') ."</p></div>";
	}
	
}




add_filter('plugin_action_links', 'ytsl_plugin_action_links', 10, 2);
function ytsl_plugin_action_links($links, $file) {

	if(function_exists('curl_version')){
		$curl = '<span class="catt"></span>';
	}

	if(basename(dirname($file)) == 'youtube-speedload') {
		$links[] = $curl.'<a href="' . add_query_arg(array('page' => 'youtube-speedload'), admin_url('options-general.php')) . '">' . __('Settings') . '</a>';
	}

	return $links;
}

add_action( 'admin_menu', 'ytsl_plugin_menu' );
function ytsl_plugin_menu() {
	add_options_page( 'Youtube Speedload Options', 'Youtube Speedload', 'manage_options', 'youtube-speedload', 'ytsl_settings_page' );
}

function ytsl_settings_page() {

    //must check that the user has the required capability 
    if (!current_user_can('manage_options')){
		wp_die( __('You do not have sufficient permissions to access this page.','ytsl-textdomain') );
    }

    // variables for the field and option names 
    $opt_name = 'ytsl-responive';
    $hidden_field_name = 'ytsl_submit_hidden';
    // Read in existing option value from database
    $opt_val = get_option( $opt_name );

    // See if the user has posted us some information
    // If they did, this hidden field will be set to 'Y'
    if( isset($_POST[ $hidden_field_name ]) && $_POST[ $hidden_field_name ] == 'Y' ) {
        // Read their posted value
        $opt_val = $_POST[ 'doResponsive' ];
		
		if($_POST[ 'oembedcache' ]=='on'){
            global $wpdb;
            $wpdb->query("DELETE FROM $wpdb->postmeta WHERE meta_key LIKE '%_oembed_%'");
		}
        

        // Save the posted value in the database
        update_option( $opt_name, $opt_val );
		
		// Echo saved message
		?><div class="updated"><p><strong><?php _e('settings saved.','youtube-speedload') ?></strong></p></div><?php
	};

?>

<div class="wrap">
	<?php  echo "<h2>" . __( 'Youtube SpeedLoad settings','ytsl-textdomain') . "</h2>";  ?>
	<form name="form1" method="post" action="">
	<table class="form-table">
		<tbody>
			<tr>
				<th scope="row"><?php _e('Responsive embeds','ytsl-textdomain'); ?></th>
				<td>
					<fieldset>
						<label for="doResponsive">
						<?php //echo $opt_val?>
							<input type="checkbox" <?php checked( $opt_val, 'on' ); ?> id="doResponsive"  name="doResponsive"><?php _e('Yes. I want all youtube embeds have 100% width','ytsl-textdomain'); ?></label>
					</fieldset>
					
				</td>
			</tr>
			<tr>
				<th scope="row"><?php _e('Clear oembed cache','youtube-speedload'); ?></th>
				<td>
					<fieldset>
						<label for="oembedcache">
						<?php //echo $opt_val?>
							<input type="checkbox"  id="oembedcache"  name="oembedcache"><?php _e('Clear oembed cache','ytsl-textdomain'); ?></label>
					</fieldset>
				</td>
			</tr>

		</tbody>
	</table>
	<img style="position: absolute; right: 0; top: 25px;" src="<?php echo  plugins_url(). "/youtube-speedload/cat.png" ?>">
	<input type="hidden" name="<?php echo $hidden_field_name; ?>" value="Y">
	<hr />

	<p class="submit">
	<input type="submit" name="Submit" class="button-primary" value="<?php esc_attr_e('Save Changes','ytsl-textdomain') ?>" />
	</p>

	</form>
</div>

<?php } ?>