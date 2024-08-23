<?php
/* Template Name: Cities List */

get_header(); 

// Custom action hook before the table
do_action('before_cities_table');

global $wpdb;

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

// Pagination parameters
$paged = (get_query_var('paged')) ? absint(get_query_var('paged')) : 1;
$limit = 2; // Number of rows per page
$offset = ($paged - 1) * $limit;

// Query to fetch cities
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
    GROUP BY p.ID
    LIMIT %d OFFSET %d
", $limit, $offset);

$results = $wpdb->get_results($query);

// Query to get the total number of results
$total_query = "
    SELECT COUNT(DISTINCT p.ID)
    FROM {$wpdb->posts} p
    INNER JOIN {$wpdb->postmeta} pm1 ON p.ID = pm1.post_id AND pm1.meta_key = '_city_longitude'
    INNER JOIN {$wpdb->postmeta} pm2 ON p.ID = pm2.post_id AND pm2.meta_key = '_city_latitude'
    INNER JOIN {$wpdb->term_relationships} tr ON p.ID = tr.object_id
    INNER JOIN {$wpdb->term_taxonomy} tt ON tr.term_taxonomy_id = tt.term_taxonomy_id
    INNER JOIN {$wpdb->terms} t ON tt.term_id = t.term_id
    WHERE p.post_type = 'city'
    AND p.post_status = 'publish'
";

$total_results = $wpdb->get_var($total_query);
$total_pages = ceil($total_results / $limit);
?>

<form id="city-search-form">
    <input type="text" id="city-search-input" name="search_term" placeholder="Search cities...">
    <button type="submit">Search</button>
</form>

<table id="city-table">
    <thead>
        <tr>
            <th>City</th>
            <th>Country</th>
            <th>Longitude</th>
            <th>Latitude</th>
            <th>Temperature (°C)</th>
        </tr>
    </thead>
    <tbody>
        <?php if ($results) : ?>
            <?php foreach ($results as $row) : ?>
                <tr>
                    <td>
                        <a href="<?php echo esc_url(get_permalink($row->ID)); ?>">
                            <?php echo esc_html($row->post_title); ?>
                        </a>
                    </td>
                    <td><?php echo esc_html($row->countries); ?></td>
                    <td><?php echo esc_html($row->longitude); ?></td>
                    <td><?php echo esc_html($row->latitude); ?></td>
                    <td>
                        <?php
                        $latitude = convert_to_decimal($row->latitude);
                        $longitude = convert_to_decimal($row->longitude);
                        $weather_data = get_weather_data($latitude, $longitude);
                        if ($weather_data) {
                            echo esc_html(number_format($weather_data['temperature'], 2) . ' °C');
                        } else {
                            echo 'Data not available';
                        }
                        ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        <?php else : ?>
            <tr>
                <td colspan="5">No cities found.</td>
            </tr>
        <?php endif; ?>
    </tbody>
</table>

<?php
// Display pagination links if there are more than one page
if ($total_pages > 1) {
    echo paginate_links(array(
        'base' => str_replace(999999999, '%#%', esc_url(get_pagenum_link(999999999))),
        'format' => '?paged=%#%',
        'current' => max(1, $paged),
        'total' => $total_pages,
        'prev_text' => __('« Prev'),
        'next_text' => __('Next »'),
        'end_size' => 1,
        'mid_size' => 2,
    ));
}

// Custom action hook after the table
do_action('after_cities_table');

get_footer();
?>
