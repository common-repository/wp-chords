<?php

function wpchords_plugin_data()
{
  return get_plugin_data(plugin_dir_path(__FILE__).'wpchords.php');
}

function wpchords_initializer()
{
  // wp_enqueue_script('wpchords_app', '//localhost:8080/bundle.js', array(), '1.1.0', true);
   wp_enqueue_script('wpchords_app', plugin_dir_url( __FILE__ ) . 'dist/bundle.js', array(), '1.1.0', true );

  $passedValues = array(
    'font_size'   => get_option('wpchords_toggle_fontsize'),
    'transpose'   => get_option('wpchords_toggle_transpose'),
    'alternating' => get_option('wpchords_toggle_alternating'),
    'print'       => get_option('wpchords_toggle_print'),
    'printFooter' => get_option('wpchords_string_printfooter'),
    'ajax_url'    => admin_url('admin-ajax.php'));
  wp_localize_script( 'wpchords_app', 'wpchords_opt', $passedValues );

  // if (wpchords_amp_active()) add_action('amp_post_template_css','wpchords_amp_preload_scripts', 11);
  // if (wpchords_amp_active()) add_action('amp_post_template_head','wpchords_amp_preload_js', 11);

}

function wpchords_generator( $content ) {
  $chord = str_replace(' ', '&nbsp;', $content);
  $chord = preg_replace('/[ ](?=[^>]*(?:<|$))/', '&nbsp', $content);
  $chord = $content;
  $replacor = '/\[[A-Za-z1-9#]{1,6}\]/';
  // $chord = !wpchords_amp_active() ? preg_replace_callback($replacor, function($m) {
  //   return "<nota-component nota=\"".substr($m[0],1,-1)."\"></nota-component>";
  // }, $chord) : 
  //  preg_replace_callback($replacor, function($m) {
  //   return "<b>[".substr($m[0],1,-1)."]</b>";
  // }, $chord);
  $chord = !wpchords_amp_active() ? preg_replace_callback($replacor, function($m) {
    return "<wpchord data-nosnippet nota=\"".substr($m[0],1,-1)."\"></wpchord>";
  }, $chord) : 
   preg_replace_callback($replacor, function($m) {
    return "<b>[".substr($m[0],1,-1)."]</b>";
  }, $chord);
  $chord = "<wpchords>".$chord."</wpchords>";

  if (wpchords_amp_active()) $chord = "<p style=\"margin-bottom:0\"><a href=\"".preg_replace('/amp=1/', 'noamp', amp_get_permalink(get_the_ID()))."\">".(!get_option("wpchords_string_deamp") ? "To view full edition please click here" : get_option("wpchords_string_deamp"))."</a></p>".$chord;
  return "<div class=\"app-handler akorcu-app-handler bs-iso\"><div class=\"header-handler\"><wp-chords-tools/></div><div class=\"wpChordsBody".((boolean)get_option('wpchords_toggle_monospace') ? ' wpChordsBodyMonospace' : '')."\" ref=\"chordsbody\" title=\"".get_the_title()."\">".$chord."</div></div>";
}

function wpchords_shortcode( $atts, $content = null ) {
  wpchords_initializer();
	return wpchords_generator($content);
}
add_shortcode( 'wpchords', 'wpchords_shortcode' );

/*
* admin helper
*/
add_action( 'wp_ajax_get_wpchords', 'wpchords_get_wpchords' );

function wpchords_get_wpchords() {
	$www = sanitize_textarea_field($_POST['payload']);
  echo stripslashes(do_shortcode($www));
	wp_die();
}

/*
 * admin page
 */

add_action('admin_menu', 'wpchords_admin_add_page');
function wpchords_admin_add_page() {
  add_options_page('WP Chords', wpchords_plugin_data()['Name'], 'manage_options', 'wpchords', 'wpchords_options');
}

add_action("admin_init", "wpchords_admin_page_init");
function wpchords_admin_page_init() {
  // add_settings_section( $id, $title, $callback, $page );
  add_settings_section("wpchords-section", null, null, "wpchords");
  add_settings_section("wpchords-text-section", null, null, "wpchords");
  // add_settings_field( string $id, string $title, callable $callback, string $page, string $section = 'default', array $args = array() )
  add_settings_field("wpchords_toggle_fontsize", "Hide Font Size Changer", "wpchords_chk_font_size_display", "wpchords", "wpchords-section");
  add_settings_field("wpchords_toggle_transpose", "Hide Transpose", "wpchords_chk_chord_changer_display", "wpchords", "wpchords-section");
  add_settings_field("wpchords_toggle_alternating", "Display with Alternating Chords", "wpchords_chk_alternating_display", "wpchords", "wpchords-section");
  add_settings_field("wpchords_toggle_monospace", "Display with monospace font", "wpchords_chk_monospacefont", "wpchords", "wpchords-section");
  add_settings_field("wpchords_toggle_print", "Hide Print Button", "wpchords_chk_print", "wpchords", "wpchords-section");
  add_settings_field("wpchords_string_deamp", "De-Amp Link Text (if amp is enabled)", "wpchords_str_deamp", "wpchords", "wpchords-text-section");
  add_settings_field("wpchords_string_printfooter", "Footer Text for Printing", "wpchords_str_printfooter", "wpchords", "wpchords-text-section");
    // register_setting( string $option_group, string $option_name, array $args = array() )
  register_setting("wpchords-all", "wpchords_toggle_fontsize");
  register_setting("wpchords-all", "wpchords_toggle_transpose");
  register_setting("wpchords-all", "wpchords_toggle_alternating");
  register_setting("wpchords-all", "wpchords_toggle_monospace");
  register_setting("wpchords-all", "wpchords_toggle_print");
  register_setting("wpchords-all", "wpchords_string_printfooter");
  register_setting("wpchords-all", "wpchords_string_deamp");
}


function wpchords_chk_font_size_display()
{
   ?> <input type="checkbox" name="wpchords_toggle_fontsize" value="1" <?php checked(1, get_option('wpchords_toggle_fontsize'), true); ?> > <?php
}


function wpchords_chk_chord_changer_display()
{
   ?> <input type="checkbox" name="wpchords_toggle_transpose" value="1" <?php checked(1, get_option('wpchords_toggle_transpose'), true); ?> > <?php
}

function wpchords_chk_alternating_display()
{
   ?> <input type="checkbox" name="wpchords_toggle_alternating" value="1" <?php checked(1, get_option('wpchords_toggle_alternating'), true); ?> > <?php
}

function wpchords_chk_monospacefont()
{
   ?> <input type="checkbox" name="wpchords_toggle_monospace" value="1" <?php checked(1, get_option('wpchords_toggle_monospace'), true); ?> > <?php
}

function wpchords_chk_print()
{
   ?> <input type="checkbox" name="wpchords_toggle_print" value="1" <?php checked(1, get_option('wpchords_toggle_print'), true); ?> > <?php
}

function wpchords_str_printfooter()
{
  // $opt = get_option('wpchords_str_deamp');
   ?> <input type="text" name="wpchords_string_printfooter" value="<?php echo esc_attr( get_option('wpchords_string_printfooter') ); ?>"> <?php
}

function wpchords_str_deamp()
{
  // $opt = get_option('wpchords_str_deamp');
   ?> <input type="text" name="wpchords_string_deamp" value="<?php echo esc_attr( get_option('wpchords_string_deamp') ); ?>"> <?php
}


function wpchords_amp_active() {
  return ( function_exists( 'is_amp_endpoint' ) && is_amp_endpoint() );
}

?>