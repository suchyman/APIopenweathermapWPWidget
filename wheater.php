<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              localhost
 * @since             1.0.0
 * @package           Wheater
 *
 * @wordpress-plugin
 * Plugin Name:       SimpleWeather
 * Plugin URI:        localhost
 * Description:       This is a short description of what the plugin does. It's displayed in the WordPress admin area.
 * Version:           1.0.0
 * Author:            suchyman
 * Author URI:        localhost
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       wheater
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define( 'WHEATER_VERSION', '1.0.0' );

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-wheater-activator.php
 */
function activate_wheater() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-wheater-activator.php';
	Wheater_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-wheater-deactivator.php
 */
function deactivate_wheater() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-wheater-deactivator.php';
	Wheater_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_wheater' );
register_deactivation_hook( __FILE__, 'deactivate_wheater' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-wheater.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_wheater() {

	$plugin = new Wheater();
	$plugin->run();

}
run_wheater();


// Register and load the widget
function wpb_load_widget() {
    register_widget( 'wpb_widget' );
}
add_action( 'widgets_init', 'wpb_load_widget' );

// Creating the widget
class wpb_widget extends WP_Widget {

function __construct() {
parent::__construct(

// Base ID of your widget
'wpb_widget',

// Widget name will appear in UI
__('WPWheater Widget', 'wpb_widget_domain'),

// Widget description
array( 'description' => __( 'Simple Weather widget', 'wpb_widget_domain' ), )
);
}

// Creating widget front-end

public function widget( $args, $instance ) {
$title = apply_filters( 'widget_title', $instance['title'] );

// before and after widget arguments are defined by themes
echo $args['before_widget'];
if ( ! empty( $title ) )
echo $args['before_title'] . $title . $args['after_title'];

///////////////////////////////////// This is where you run the code and display the output

////widget start



/////////////////////////////////////////Localization start/////////////////////////////////////////////////////////////////////
$ip=$_SERVER['REMOTE_ADDR'];

//  Initiate curl
$ch = curl_init();
// Will return the response, if false it print the response
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
// Set the url


curl_setopt($ch, CURLOPT_URL,'http://ip-api.com/json/'.$ip.'?fields=status,country,countryCode,city,lat,lon,timezone,query');
// Execute
$resultLoc=curl_exec($ch);
// Closing
curl_close($ch);


$loc = json_decode($resultLoc);
$locCity = $loc->{'city'};
$locCode = $loc->{'countryCode'};

// echo 'Your ip: ' . $ip . '<br>';

////////////////////////////////////////Localization end//////////////////////////////////////////////////////////////////

$ch2 = curl_init();

curl_setopt($ch2, CURLOPT_HEADER, 0);
curl_setopt($ch2, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch2, CURLOPT_URL, $googleApiUrl);
curl_setopt($ch2, CURLOPT_FOLLOWLOCATION, 1);
curl_setopt($ch2, CURLOPT_VERBOSE, 0);
curl_setopt($ch2, CURLOPT_SSL_VERIFYPEER, false);

curl_setopt($ch2, CURLOPT_URL,'http://api.openweathermap.org/data/2.5/weather?q='.$locCity.','.$locCode.'&units=metric&appid=<<YOUR API KEY FROM OPENWEATHERMAP,ORG>>&lang=pl');
$resultWheater=curl_exec($ch2);
curl_close($ch2);
$wheater = json_decode($resultWheater);


// http://api.openweathermap.org/data/2.5/weather?q=Pulawy,pl&units=metric&appid=<<YOUR API ID FROM OWM>>
//{"coord":{"lon":21.97,"lat":51.42},
// "weather":[{"id":804,"main":"Clouds","description":"overcast clouds","icon":"04d"}],
// "base":"stations","main":{"temp":16.09,"pressure":992,"humidity":87,"temp_min":15,"temp_max":16.67},"visibility":10000,
// "wind":{"speed":5.1,"deg":180},"clouds":{"all":94},"dt":1572862540,"sys":{"type":1,"id":1699,"country":"PL",
// "sunrise":1572845389,"sunset":1572879689},"timezone":3600,"id":760924,"name":"Pulawy","cod":200}

// http://ip-api.com/json/82.177.226.40?fields=status,country,countryCode,city,lat,lon,timezone,query
//{"status":"success","country":"Poland","countryCode":"PL","city":"Puławy","lat":51.4114,"lon":21.977,"timezone":"Europe/Warsaw","query":"82.177.226.40"}


echo 'Wheater for: '.$locCity . ' (from your IP)<br>';
echo 'Actual temp.: ' . $wheater->main->{'temp'};
echo '<br>

<img src="http://openweathermap.org/img/w/'. $wheater->weather[0]->icon.'.png"
                class="weather-icon" /><p>max temp. '.$wheater->main->temp_max .'°C<p><span
				class="min-temperature"> min temp. '.$wheater->main->temp_min.'°C</span>
				<p>wind: '.$wheater->wind->speed.' km/h </p>
				<p> clouds: '.$wheater->clouds->all.' %</p>
';


/////////////////////////////////////widget end
echo $args['after_widget'];
}

// Widget Backend
public function form( $instance ) {
if ( isset( $instance[ 'title' ] ) ) {
$title = $instance[ 'title' ];
}
else {
$title = __( 'New title', 'wpb_widget_domain' );
}
// Widget admin form
?>
<p>
<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:' ); ?></label>
<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>" />
</p>
<?php
}

// Updating widget replacing old instances with new
public function update( $new_instance, $old_instance ) {
$instance = array();
$instance['title'] = ( ! empty( $new_instance['title'] ) ) ? strip_tags( $new_instance['title'] ) : '';
return $instance;
}
} // Class wpb_widget ends here
