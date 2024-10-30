<?php
/* 
Plugin Name: Lightweight Social Sharing Buttons
Plugin URI: http://marcelvanderhoek.nl/lightweight-social-sharing-buttons
Description: Adds social sharing buttons to single posts and pages. Doesn't load external scripts or images. 
Version: 0.4
Author: Marcel van der Hoek
Author URI: http://marcelvanderhoek.nl

Copyright 2014  Marcel van der Hoek  (email : marcel@marcelvanderhoek.nl)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as 
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

// Settings class
class LSSB_settings {

	public $options;
	
	// Construct
	public function __construct() 
	{		
		$this->options = get_option('lssb_options');
		$this->register_settings_and_fields();
	}
	
	// Add options page
	public static function add_menu_page() 
	{	
		add_options_page( __( 'LSSB Options', 'lssb' ), __( 'LSSB Options', 'lssb' ), 'administrator', __FILE__, array('LSSB_settings', 'display_options_page') ); 
	}
	
	// HTML for the options page
	public static function display_options_page() 
	{
		?>
		<div class="wrap">
			<h2><?php _e('LSSB Options','lssb'); ?></h2>
			<form method="post" action="options.php" enctype="multipart/form-data">
				<?php 
					settings_fields('lssb_options'); 
					do_settings_sections(__FILE__);
				?>				
				<p class="submit">
					<input name="submit" type="submit" class="button-primary" value="<?php _e('Save Changes','lssb'); ?>">
				</p>
			</form>
		</div>
		<?php
	}
	
	// Set default settings, register setting, add section and fields
	public function register_settings_and_fields() 
	{
		// Set defaults
   		add_option( 'lssb_options', array(
			'description' => '',
			'display_twitter' => 1,
			'display_facebook' => 1,
			'display_googleplus' => 1,
			'display_pinterest' => 1,
			'link_target' => '_self'
		) ); 
		
		register_setting('lssb_options', 'lssb_options');
		add_settings_section('main_section', __( 'Main Settings', 'lssb' ), array($this, 'main_section_cb'), __FILE__); // id, section title, callback, which page
		add_settings_field('description', __( 'Description:', 'lssb' ), array($this, 'description_setting'), __FILE__, 'main_section');
		add_settings_field('display_twitter', __( 'Display Twitter Button:', 'lssb' ), array($this, 'display_twitter_setting'), __FILE__, 'main_section');
		add_settings_field('display_facebook', __( 'Display Facebook Button:', 'lssb' ), array($this, 'display_facebook_setting'), __FILE__, 'main_section');
		add_settings_field('display_googleplus', __( 'Display Google+ Button:', 'lssb' ), array($this, 'display_googleplus_setting'), __FILE__, 'main_section');
		add_settings_field('display_pinterest', __( 'Display Pinterest Button:', 'lssb' ), array($this, 'display_pinterest_setting'), __FILE__, 'main_section');
		add_settings_field('link_target', __( 'Link Target:', 'lssb' ), array($this, 'link_target_setting'), __FILE__, 'main_section');
	}
	
	// Placeholder for add_settings_section callback function
	public function main_section_cb() 
	{
	}
	
	// Description setting
	public function description_setting()
	{
		echo "<input name='lssb_options[description]' type='text'"; 
		if (isset($this->options['description'])) echo "value='" . $this->options['description'] . "'"; // Echo out saved value for description
		echo " />";	
		
	}

	// Twitter Button setting
	public function display_twitter_setting()
	{
		echo "<input name='lssb_options[display_twitter]' type='checkbox' value='1' "; 
		if (isset($this->options['display_twitter'])) checked( $this->options['display_twitter'], 1 ); // WordPress function checked() returns HTML attribute (checked='checked') or empty string
		echo " />";	
		
	}
	
	// Facebook Button setting
	public function display_facebook_setting()
	{
		echo "<input name='lssb_options[display_facebook]' type='checkbox' value='1' "; 
		if (isset($this->options['display_facebook'])) checked( $this->options['display_facebook'], 1 ); 
		echo " />";	
	}
	
	// Google+ Button setting
	public function display_googleplus_setting()
	{
		echo "<input name='lssb_options[display_googleplus]' type='checkbox' value='1' "; 
		if (isset($this->options['display_googleplus'])) checked( $this->options['display_googleplus'], 1 ); 
		echo " />";	
	}	
	
	// Pinterest Button setting
	public function display_pinterest_setting()
	{
		echo "<input name='lssb_options[display_pinterest]' type='checkbox' value='1' "; 
		if (isset($this->options['display_pinterest'])) checked( $this->options['display_pinterest'], 1 ); 
		echo " />";	
	}
	
	// Link target setting
	public function link_target_setting()
	{
		$options = array('_blank', '_self');
		echo "<select name='lssb_options[link_target]'>";
		foreach($options as $option) {
			$selected = ( $this->options['link_target'] === $option ) ? 'selected="selected"' : '';
			echo "<option value='$option' $selected>$option</option>";
		}
		echo "</select>";
	}
}

function LSSB_init_settings() {
	new LSSB_settings();
}

function LSSB_add_menu() {
	LSSB_settings::add_menu_page();
}

function LSSB_load_textdomain() {
  load_plugin_textdomain( 'lssb', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' ); 
}

add_filter( 'the_content', 'add_lssb_buttons' ); // Add filter to the_content, call add_lssbb_buttons function
add_action('admin_init', 'LSSB_init_settings'); // Anonymous function would look better, using named function for compatibility reasons
add_action('admin_menu', 'LSSB_add_menu');
add_action( 'plugins_loaded', 'LSSB_load_textdomain' );

if(!function_exists('add_lssb_buttons')) {
	function add_lssb_buttons( $content ) {
		$title = str_replace( " ", "+", get_the_title() ); // Retrieve the current page's title and change spaces to + with str_replace so we can send it through the URL to Twitter
		$permalink = urlencode( get_permalink() ); // Retrieve the permalink and urlencode() it so we can send it through the URL
		$lssb_images_path = plugins_url( 'images' , __FILE__ ); // Retrieve plugin directory for images
		$options = get_option('lssb_options');
		$linktarget = "target='" . $options['link_target'] . "'"; // Retrieve link target setting 
		
		$lssb_buttons_markup = "<div class='lssb_buttons'>" . $options['description']; // Open div with description
		// Add buttons if display setting is set
		if ($options['display_twitter']) { $lssb_buttons_markup .= "<a href='http://twitter.com/home?status=$title:+$permalink' $linktarget><img src='$lssb_images_path/twitter.png' alt='" . __( 'Twitter Button', 'lssb' ) . "' class='lssb_button' /></a>"; }
		if ($options['display_googleplus']) { $lssb_buttons_markup .= "<a href='https://plus.google.com/share?url=$permalink' $linktarget><img src='$lssb_images_path/googleplus.png' alt='" . __( 'Google+ Button', 'lssb' ) . "' class='lssb_button' /></a>"; }
		if ($options['display_facebook']) { $lssb_buttons_markup .= "<a href='https://www.facebook.com/sharer/sharer.php?u=$permalink' $linktarget><img src='$lssb_images_path/facebook.png' alt='" . __( 'Facebook Button', 'lssb' ) . "' class='lssb_button' /></a>"; }
		if ($options['display_pinterest']) { $lssb_buttons_markup .= "<a href='http://pinterest.com/pin/create/link/?url=$permalink' $linktarget><img src='$lssb_images_path/pinterest.png' alt='" . __( 'Pinterest Button', 'lssb' ) . "' class='lssb_button' /></a>"; }
		$lssb_buttons_markup .= "</div>\n"; // Close lssb_buttons div

		if ( is_singular('post') || is_page() ) { // If we're on a single post OR on a single page
			return $lssb_buttons_markup . $content; // Add our markup before the content
		}
		else {
			return $content; // Executing this means we're not on a single post or page, return content as it is
		}
	}
}

// Add settings link to plugins.php in admin section
if(!function_exists('lssb_add_settings_link')) {
	function lssb_add_settings_link( $links ) {
		$settings_link = '<a href="options-general.php?page=lightweight-social-sharing-buttons/lightweight-social-sharing-buttons.php">' . __( 'Settings', 'lssb' ) . '</a>';
		array_push( $links, $settings_link );
		return $links;
	}

	$plugin = plugin_basename( __FILE__ );
	add_filter( "plugin_action_links_$plugin", 'lssb_add_settings_link' );
}
?>