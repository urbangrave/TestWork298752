<?php
// Enqueue the parent theme's styles
function city_child_theme_enqueue_styles() {
    wp_enqueue_style('parent-theme-style', get_template_directory_uri() . '/style.css');
}
add_action('wp_enqueue_scripts', 'city_child_theme_enqueue_styles');


// Post Type "CITIES"
function create_cities_post_type() {
    $labels = array(
        'name'               => __('Cities'),
        'singular_name'      => __('City'),
        'menu_name'          => _x('Cities', 'admin menu'),
        'add_new'            => __('Add New'),
        'add_new_item'       => __('Add New City'),
        'new_item'           => __('New City'),
        'edit_item'          => __('Edit City'),
        'view_item'          => __('View City'),
        'all_items'          => __('All Cities'),
        'search_items'       => __('Search Cities'),
        'not_found'          => __('No cities found.'),
        'not_found_in_trash' => __('No cities found in Trash.')
    );

    $args = array(
        'labels'             => $labels,
        'public'             => true,
        'publicly_queryable' => true,
        'show_ui'            => true,
        'show_in_menu'       => true,
        'query_var'          => true,
        'rewrite'            => array('slug' => 'city'),
        'capability_type'    => 'post',
        'has_archive'        => true,
        'hierarchical'       => false,
        'menu_position'      => null,
        'supports'           => array('title', 'editor', 'author', 'thumbnail', 'excerpt', 'comments'),
        'show_in_rest'       => true, // Enables Gutenberg editor
    );

    register_post_type('city', $args);
}
add_action('init', 'create_cities_post_type');

// City location meta box
class WP_Skills_MetaBox_Locations {
    private $screen = array('post', 'city');

    private $meta_fields = array(
        array(
            'label'   => 'Longitude',
            'id'      => 'city_longitude',
            'type'    => 'text',
            'default' => '',
        ),
        array(
            'label'   => 'Latitude',
            'id'      => 'city_latitude',
            'type'    => 'text',
            'default' => '',
        )
    );

    public function __construct() {
        add_action('add_meta_boxes', array($this, 'add_meta_boxes'));
        add_action('admin_footer', array($this, 'media_fields'));
        add_action('save_post', array($this, 'save_fields'));
    }

    public function add_meta_boxes() {
        foreach ($this->screen as $single_screen) {
            add_meta_box(
                'Locations',
                __('Locations', ''),
                array($this, 'meta_box_callback'),
                $single_screen,
                'normal',
                'default'
            );
        }
    }

    public function meta_box_callback($post) {
        wp_nonce_field('Locations_data', 'Locations_nonce');
        $this->field_generator($post);
    }

    public function media_fields() { ?>
        <script>
            jQuery(document).ready(function($) {
                if (typeof wp.media !== 'undefined') {
                    var _custom_media = true,
                        _orig_send_attachment = wp.media.editor.send.attachment;
                    $('.new-media').click(function(e) {
                        var send_attachment_bkp = wp.media.editor.send.attachment;
                        var button = $(this);
                        var id = button.attr('id').replace('_button', '');
                        _custom_media = true;
                        wp.media.editor.send.attachment = function(props, attachment) {
                            if (_custom_media) {
                                if ($('input#' + id).data('return') == 'url') {
                                    $('input#' + id).val(attachment.url);
                                } else {
                                    $('input#' + id).val(attachment.id);
                                }
                                $('div#preview' + id).css('background-image', 'url(' + attachment.url + ')');
                            } else {
                                return _orig_send_attachment.apply(this, [props, attachment]);
                            };
                        }
                        wp.media.editor.open(button);
                        return false;
                    });
                    $('.add_media').on('click', function() {
                        _custom_media = false;
                    });
                    $('.remove-media').on('click', function() {
                        var parent = $(this).parents('td');
                        parent.find('input[type="text"]').val('');
                        parent.find('div').css('background-image', 'url()');
                    });
                }
            });
        </script>
    <?php 
    }

    //meta box field generator
    public function field_generator($post) {
        $output = '';
        $screen = get_current_screen();
        
        if ($screen->action == 'add') {
            foreach ($this->meta_fields as $meta_field) {
                $label = '<label for="' . $meta_field['id'] . '">' . $meta_field['label'] . '</label>';
                $meta_value = $meta_field['default'];
                $input = sprintf(
                    '<input %s id="%s" name="%s" type="%s" value="%s">',
                    $meta_field['type'] !== 'color' ? 'style="width: 100%"' : '',
                    $meta_field['id'],
                    $meta_field['id'],
                    $meta_field['type'],
                    $meta_value
                );
                $output .= $this->format_rows($label, $input);
            }
        } else {
            foreach ($this->meta_fields as $meta_field) {
                $label = '<label for="' . $meta_field['id'] . '">' . $meta_field['label'] . '</label>';
                $meta_value = get_post_meta($post->ID, '_' . $meta_field['id'], true);
                if (empty($meta_value)) {
                    $meta_value = $meta_field['default'];
                }
                $input = sprintf(
                    '<input %s id="%s" name="%s" type="%s" value="%s">',
                    $meta_field['type'] !== 'color' ? 'style="width: 100%"' : '',
                    $meta_field['id'],
                    $meta_field['id'],
                    $meta_field['type'],
                    $meta_value
                );
                $output .= $this->format_rows($label, $input);
            }
        }
        echo '<table class="form-table"><tbody>' . $output . '</tbody></table>';
    }

    public function format_rows($label, $input) {
        return '<tr><th>' . $label . '</th><td>' . $input . '</td></tr>';
    }

    //save field
    public function save_fields($post_id) {
        if (!isset($_POST['Locations_nonce'])) {
            return;
        }
        $nonce = $_POST['Locations_nonce'];
        if (!wp_verify_nonce($nonce, 'Locations_data')) {
            return;
        }
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }
        if (!current_user_can('edit_post', $post_id)) {
            return;
        }

        foreach ($this->meta_fields as $meta_field) {
            if (isset($_POST[$meta_field['id']])) {
                switch ($meta_field['type']) {
                    case 'email':
                        $_POST[$meta_field['id']] = sanitize_email($_POST[$meta_field['id']]);
                        break;
                    case 'text':
                        $_POST[$meta_field['id']] = sanitize_text_field($_POST[$meta_field['id']]);
                        break;
                }
                $meta_key = '_' . $meta_field['id'];
                if (!metadata_exists('post', $post_id, $meta_key)) {
                    add_post_meta($post_id, $meta_key, $_POST[$meta_field['id']], true);
                } else {
                    update_post_meta($post_id, $meta_key, $_POST[$meta_field['id']]);
                }
            } else if ($meta_field['type'] === 'checkbox') {
                $meta_key = '_' . $meta_field['id'];
                if (!metadata_exists('post', $post_id, $meta_key)) {
                    add_post_meta($post_id, $meta_key, '0', true);
                } else {
                    update_post_meta($post_id, $meta_key, '0');
                }
            }
        }
    }
}

//callback metalocation
if (class_exists('WP_Skills_MetaBox_Locations')) {
    new WP_Skills_MetaBox_Locations;
}

// Cities Taxonomy
function create_countries_taxonomy() {
    $labels = array(
        'name'              => _x('Countries', 'taxonomy general name', 'text-domain'),
        'singular_name'     => _x('Country', 'taxonomy singular name', 'text-domain'),
        'search_items'      => __('Search Countries', 'text-domain'),
        'all_items'         => __('All Countries', 'text-domain'),
        'parent_item'       => __('Parent Country', 'text-domain'),
        'parent_item_colon' => __('Parent Country:', 'text-domain'),
        'edit_item'         => __('Edit Country', 'text-domain'),
        'update_item'       => __('Update Country', 'text-domain'),
        'add_new_item'      => __('Add New Country', 'text-domain'),
        'new_item_name'     => __('New Country Name', 'text-domain'),
        'menu_name'         => __('Countries', 'text-domain'),
    );

    $args = array(
        'hierarchical'      => true, // Set to true for a category-like taxonomy, false for tags
        'labels'            => $labels,
        'show_ui'           => true,
        'show_admin_column' => true,
        'query_var'         => true,
        'rewrite'           => array('slug' => 'country'),
        'show_in_rest'      => true, // Enables Gutenberg support
    );

    register_taxonomy('country', array('city'), $args);
}
add_action('init', 'create_countries_taxonomy');

// Display latitude and longitude to city
function display_location_coordinates($post_id) {
    $longitude = get_post_meta($post_id, '_city_longitude', true);
    $latitude = get_post_meta($post_id, '_city_latitude', true);
    $output = '';
    if (!empty($longitude) && !empty($latitude)) {
        $output .= '<p>Longitude: ' . esc_html($longitude) . '</p>';
        $output .= '<p>Latitude: ' . esc_html($latitude) . '</p>';
    } else {
        $output .= '<p>' . esc_html('No Value Yet!') . '</p>';
    }
    return $output;
}
add_shortcode('location_coordinates', 'display_location_coordinates_callback');

function display_location_coordinates_callback() {
    global $post;
    return display_location_coordinates($post->ID);
}


//custom_city_Temp_widget
function register_city_weather_widget() {
  register_widget('City_Weather_Widget');
}
add_action('widgets_init', 'register_city_weather_widget');

// Define the custom widget class
class City_Weather_Widget extends WP_Widget {
    public function __construct() {
        parent::__construct(
            'city_weather_widget',
            __('City Weather Widget', 'text_domain'),
            array('description' => __('Displays a city name and current temperature.', 'text_domain'))
        );
    }

    public function widget($args, $instance) {
        $title = isset($instance['title']) ? apply_filters('widget_title', $instance['title']) : '';
        $city_id = isset($instance['city_id']) ? $instance['city_id'] : '';
    
        // Fetch city data
        $city = get_post($city_id);
        if (!$city) return;
    
        // Fetch latitude and longitude
        $latitude = get_post_meta($city_id, '_city_latitude', true);
        $longitude = get_post_meta($city_id, '_city_longitude', true);
    
        // Convert latitude and longitude to decimal
        $latitude = $this->convert_to_decimal($latitude);
        $longitude = $this->convert_to_decimal($longitude);
    
        // Validate latitude and longitude
        if (!is_numeric($latitude) || !is_numeric($longitude)) {
            echo $args['before_widget'];
            if (!empty($title)) {
                echo $args['before_title'] . $title . $args['after_title'];
            }
            echo '<p><strong>' . esc_html($city->post_title) . '</strong></p>';
            echo '<p>Latitude and/or longitude not valid.</p>';
            echo $args['after_widget'];
            return;
        }
    
        // Fetch weather data
        $weather_data = $this->get_weather_data($latitude, $longitude);
    
        echo $args['before_widget'];
        if (!empty($title)) {
            echo $args['before_title'] . $title . $args['after_title'];
        }
    
        echo '<p><strong>' . esc_html($city->post_title) . '</strong></p>';
        echo '<p>Temperature: ' . ($weather_data ? number_format($weather_data['temperature'], 2) . ' °C' : 'Data not available') . '</p>';
        echo $args['after_widget'];
    }
    

    function get_weather_data($latitude, $longitude) {
        $api_key = 'cf890c585155870200e5913a501b3613'; // Your API key
        $api_url = 'https://api.openweathermap.org/data/2.5/weather';
        $response = wp_remote_get($api_url . '?lat=' . urlencode($latitude) . '&lon=' . urlencode($longitude) . '&appid=' . $api_key . '&units=metric');

        if (is_wp_error($response)) {
            error_log('Weather API request error: ' . $response->get_error_message());
            return false;
        }

        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);

        // Log the API response for debugging
        error_log('Widget API Response: ' . print_r($data, true));

        if (isset($data['main']['temp'])) {
            return array(
                'temperature' => $data['main']['temp']
            );
        } else {
            error_log('Weather data not found for lat: ' . $latitude . ', lon: ' . $longitude);
            return false;
        }
    }

    function convert_to_decimal($coordinate) {
        // Remove any non-numeric characters except for the minus sign and decimal point
        $coordinate = preg_replace('/[^0-9.-]/', '', $coordinate);

        // Convert to float
        $decimal = floatval($coordinate);

        // Ensure that the conversion resulted in a numeric value
        return is_numeric($decimal) ? $decimal : false;
    }
}






function city_weather_widget_shortcode($atts) {
  ob_start();
  the_widget('City_Weather_Widget');
  return ob_get_clean();
}
add_shortcode('city_weather', 'city_weather_widget_shortcode');


//table shortcode
function country_temperature_listing_shortcode() {
    ob_start();
    // Include the template file or directly output the content here
    include get_stylesheet_directory() . '/country-temperature.php';
    return ob_get_clean();
}
add_shortcode('country_temperature_listing', 'country_temperature_listing_shortcode');

//table search
function enqueue_city_search_script() {
    wp_enqueue_script('city-search', get_stylesheet_directory_uri() . '/js/city-search.js', array('jquery'), null, true);
    
    // Localize the script with AJAX URL and nonce
    wp_localize_script('city-search', 'ajax_obj', array(
        'ajax_url' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('city_search_nonce')
    ));
}
add_action('wp_enqueue_scripts', 'enqueue_city_search_script');

// Handle AJAX request
add_action('wp_ajax_city_search', 'handle_city_search');
add_action('wp_ajax_nopriv_city_search', 'handle_city_search');

function handle_city_search() {
    // Verify nonce
    check_ajax_referer('city_search_nonce', 'nonce');

    global $wpdb;

    $search_term = sanitize_text_field($_POST['search']);

    // Query to get the cities based on search term
    $query = $wpdb->prepare("
        SELECT p.ID, p.post_title, pm1.meta_value AS longitude, pm2.meta_value AS latitude, GROUP_CONCAT(t.name) AS countries
        FROM {$wpdb->posts} p
        INNER JOIN {$wpdb->postmeta} pm1 ON p.ID = pm1.post_id AND pm1.meta_key = '_city_longitude'
        INNER JOIN {$wpdb->postmeta} pm2 ON p.ID = pm2.post_id AND pm2.meta_key = '_city_latitude'
        INNER JOIN {$wpdb->term_relationships} tr ON p.ID = tr.object_id
        INNER JOIN {$wpdb->term_taxonomy} tt ON tr.term_taxonomy_id = tt.term_taxonomy_id
        INNER JOIN {$wpdb->terms} t ON tt.term_id = t.term_id
        WHERE p.post_type = 'city'
        AND p.post_status = 'publish'
        AND p.post_title LIKE %s
        GROUP BY p.ID
    ", '%' . $wpdb->esc_like($search_term) . '%');

    $results = $wpdb->get_results($query);

    // Function to fetch weather data
    function get_weather_data($latitude, $longitude) {
        $api_key = 'cf890c585155870200e5913a501b3613'; // Your OpenWeatherMap API key
        $api_url = 'https://api.openweathermap.org/data/2.5/weather';
        $response = wp_remote_get($api_url . '?lat=' . urlencode($latitude) . '&lon=' . urlencode($longitude) . '&appid=' . $api_key . '&units=metric');
        
        if (is_wp_error($response)) {
            return false;
        }

        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);

        if (isset($data['main']['temp'])) {
            return array(
                'temperature' => $data['main']['temp']
            );
        } else {
            return false;
        }
    }
    // Function to convert formatted coordinates to decimal
    function convert_to_decimal($coordinate) {
        $coordinate = preg_replace('/[^0-9.,-]/', '', $coordinate);
        if (!is_numeric($coordinate)) {
            return false;
        }
        return (float) $coordinate;
    }

    ob_start(); // Start output buffering
    if ($results) {
        foreach ($results as $row) {
            echo '<tr>';
            echo '<td>' . esc_html($row->post_title) . '</td>';
            echo '<td>' . esc_html($row->countries) . '</td>';
            echo '<td>' . esc_html($row->longitude) . '</td>';
            echo '<td>' . esc_html($row->latitude) . '</td>';
            echo '<td>';

            // Convert coordinates to decimal
            $latitude = convert_to_decimal($row->latitude);
            $longitude = convert_to_decimal($row->longitude);

            // Fetch temperature
            $weather_data = get_weather_data($latitude, $longitude);
            if ($weather_data && isset($weather_data['temperature'])) {
                echo esc_html(number_format($weather_data['temperature'], 2) . ' °C');
            } else {
                echo 'Data not available';
            }

            echo '</td>';
            echo '</tr>';
        }
    } else {
        echo '<tr><td colspan="5">No cities found.</td></tr>';
    }
    $output = ob_get_clean(); // Get output buffer contents and clean buffer

    wp_send_json_success($output);
}

function remove_dashboard_menu_items() {
    global $menu;
    // Uncomment the lines below to exclude specific menu items
    // unset($menu[5]);    // Removes "Posts"
    unset($menu[10]);   // Removes "Media"
    unset($menu[20]);   // Removes "Pages"
    unset($menu[25]);   // Removes "Comments"
    //unset($menu[60]);   // Removes "Appearance"
    //unset($menu[65]);   // Removes "Plugins"
    unset($menu[70]);   // Removes "Users"
    unset($menu[75]);   // Removes "Tools"
    unset($menu[80]);   // Removes "Settings"
    remove_menu_page('index.php'); // Removes the Dashboard home page
}
add_action('admin_menu', 'remove_dashboard_menu_items');




?>
