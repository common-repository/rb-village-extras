<?php

function _rbve_helper_atts($post_type, $original_atts = []) {
    $attsOutput = array();
    
    $attsOutput['orderby'] = 'title';
    $attsOutput['order'] = 'ASC';
    
    $attsOutput['posts_per_page'] = (empty($original_atts['posts_per_page']) ? -1 : $original_atts['posts_per_page']);

    $attsOutput['post_type'] = $post_type; // What post type we are going to list

    return $attsOutput;
}

function _rbve_helper_proximity($direction, $proximity, $atts, $fieldname = "_rbve_sortdate") {
    // var_dump("_rbve_helper_proximity");

    if (isset($proximity)) {

        // Get the targetdate based on proximity
        if ($proximity === "month") {
            $targetdate = strtotime($direction . "1 month");
        }
        elseif ($proximity === "week") {
            $targetdate = strtotime($direction . "1 week");
        }
        elseif ($proximity === "fortnight") {
            $targetdate = strtotime($direction . "2 weeks");
        }
        elseif ($proximity === "year") {
            $targetdate = strtotime($direction . "1 year");
        }

        // UNIX timestamp (1433116800)
        if ($fieldname === "_rbve_sortdate") {
            
            $targetdatestr = date("j", $targetdate) . ' ' . date("M", $targetdate) . ' ' . date("Y", $targetdate);

            $todaystr = date('j') . ' ' . date('M') . ' ' . date('Y');

            if ($direction === "+") {
                $dateArray = [
                    strtotime($todaystr),
                    strtotime($targetdatestr)
                ];
            }
            else {
                $dateArray = [
                    strtotime($targetdatestr),
                    strtotime($todaystr)
                ];
            }

            $atts['meta_key'] = $fieldname;
            $atts['meta_query'] = array(
                array(
                'key' => $fieldname,
                'value' => $dateArray,
                'compare' => 'BETWEEN',
                'type' => 'DECIMAL',
                ),
            );
        }
        // Date string (2024-02-20)
        else if ($fieldname === "_event_start_date") {
            $todaystr = date('Y') . '-' . date('m') . '-' . date('d');
            $targetdatestr = date("Y", $targetdate) . '-' . date("m", $targetdate) . '-' . date("d", $targetdate);

            $atts['meta_key'] = $fieldname;
            $atts['meta_query'] = array(
                'relation' => 'AND',
                array(
                    'key' => $fieldname,
                    'value' => $todaystr,
                    'compare' => '>=',
                    'type' => 'DATE',
                ),
                array(
                    'key' => $fieldname,
                    'value' => $targetdatestr,
                    'compare' => '<=',
                    'type' => 'DATE',
                ),
            );
        }
    }

    return $atts;
}

/**
 * @param $atts
 * @param $content
 * @return string
 *
 * Examples:
 *     [rbve_events proximity="month"]
 */
function rbve_events_shortcode_handler($original_atts = [], $content = null, $tag = '') {

    global $post;
    $output = '';

    $atts = _rbve_helper_atts([
        'event',
        'event-recurring',
    ]);

    $atts['order'] = 'ASC';
    $atts['orderby'] = '_event_start_date';

    // Filter by proximity (from sortdate field)
    if (array_key_exists('proximity', $original_atts)) {
        $atts = _rbve_helper_proximity("+", $original_atts['proximity'], $atts, "_event_start_date");
    }

    // Unsetting these, not sure why
    // unset($atts['meta_key']);
    // unset($atts['meta_query']);

    if (array_key_exists('categories', $original_atts)) {
        $categories = $original_atts['categories'];
        $atts['tax_query'] = array(
            [
                'taxonomy' => EM_TAXONOMY_CATEGORY,
                // 'field' => 'slug',
                'terms' => $categories,
            ]
        );
    }
    // $atts['tag__not_in'] = [100];

    // Run the query using the attributes set above
    $query = new WP_Query($atts);

    // If the query returns results, then we will act on them
    if ($query->have_posts()){
        $output .= '<div class="rbve_events">';
        $output .= '<ul>';

        // Loop through the posts
        while ($query->have_posts()){
            // Load the post for the current loop iterator
            $query->the_post();

            // Load the metadata for this post
            $post_meta = get_post_meta($post->ID);
            
            $date_start = strtotime($post_meta['_event_start_date'][0]);

            // Create the document link
            $event_link = '<li class="rbve_event_item"><div class="rbve_date"><span class="rbve_date_month">' . date("M", $date_start) . '</span><span class="rbve_date_day">' . date("j", $date_start) . '</span></div><a href="' . get_permalink() . '">' . get_the_title() . '</a></li>';

            // Add the item to the output list
            $output .= $event_link;
        }
        $output .= '</ul>';
        $output .= '</div>';
    }

    // Reset the query ready for future checks
    wp_reset_query();

    // Return the output string
    return $output;
}

/**
 * @param $atts
 * @param $content
 * @return string
 *
 * Examples:
 *     [rbve_docs]
 *     [rbve_docs labeltype="monthyear"]
 *     [rbve_docs doctypes="council-minutes"]
 *     [rbve_docs doctypes="council-minutes" labeltype="monthyear"]
 *     [rbve_docs labeltype="docname" proximity="month"]
 */
function rbve_docs_shortcode_handler($original_atts = [], $content = null, $tag = '') {
    if (!is_array($original_atts)) {
        $original_atts = [];
    }
    
    global $post;
    $output = '';

    $atts = [
        'post_type' => 'rbve_doc',
        'posts_per_page' => 25,
    ];
    $atts = _rbve_helper_atts("rbve_doc", $original_atts);

    // Don't order by title, instead order by date
    $atts['order'] = 'DESC';
    $atts['orderby'] = 'meta_value_num';
    $atts['meta_key'] = '_rbve_sortdate';

    // Filter by month (from sortdate field)
    if (array_key_exists('proximity', $original_atts)) {
        $atts = _rbve_helper_proximity("-", $original_atts['proximity'], $atts);
    }
    else {

        // Filter by year (from sortdate field)
        if (isset($original_atts['limit-year'])) {
            $atts['meta_query'] = array(
                array(
                'key' => '_rbve_sortdate',
                'value' => array(
                    strtotime('1 Jan ' . $original_atts['limit-year']),
                    strtotime('31 Dec ' . $original_atts['limit-year'] . ' 23:59:59')
                ),
                'compare' => 'BETWEEN',
                'type' => 'DECIMAL',
                ),
            );
        }
    }

    // Get the taxonomy query, and filter using it
    if (is_array($original_atts) && array_key_exists('doctypes', $original_atts)) {
        $doc_types = explode(',', $original_atts['doctypes']);
        if(isset($original_atts['doctypes'])) {
            $atts['tax_query'] = array(
                array(
                    'taxonomy' => 'rbve_tax_doctypes',
                    'field' => 'slug',
                    'terms' => $doc_types,
                )
            );
        }
    }

    // Run the query using the attributes set above
    $query = new WP_Query($atts);

    // If the query returns results, then we will act on them
    if ($query->have_posts()){
        $current_year = '';
        $output .= '<div>';

        // Loop through the posts
        while ($query->have_posts()){
            // Load the post for the current loop iterator
            $query->the_post();

            // Load the metadata for this post
            $post_meta = get_post_meta($post->ID);

            if (array_key_exists('_rbve_sortdate', $post_meta) && (!array_key_exists('grouptitle', $atts) || $atts['grouptitle'] !== "disabled") && !array_key_exists('proximity', $original_atts)) {
                $year = date('Y', date($post_meta['_rbve_sortdate'][0]));
                if ($current_year === '') {
                    $current_year = date('Y', date($post_meta['_rbve_sortdate'][0]));
                    $output .= '<h2>' . $current_year . '</h2>';
                    $output .= '<ul>';
                } else if ($current_year !== $year) {
                    $current_year = date('Y', date($post_meta['_rbve_sortdate'][0]));
                    $output .= '</ul>';
                    $output .= '<h2>' . $current_year . '</h2>';
                    $output .= '<ul>';
                }
            }

            // If a document has been uploaded for this type, then we will list it, otherwise, we do not display anything
            if (!empty($post_meta['_rbve_doc'][0])) {
                // Look at labeltype if set, and set the label variable
                if (array_key_exists('labeltype', $atts)) {
                    switch ($atts['labeltype']) {
                        case 'monthyear':
                            $link_label = date('F Y', $post_meta['_rbve_sortdate'][0]);
                            break;
                        case 'month':
                            $link_label = date('F', $post_meta['_rbve_sortdate'][0]);
                            break;
                        case 'year':
                            $link_label = date('Y', strtotime($post->post_date));
                            break;
                        case 'docname':
                        default:
                            $link_label = get_the_title();
                    }
                }
                else {
                    $link_label = get_the_title();
                }
            

                // Append the filetype to the label
                if (array_key_exists('showtype', $atts) && $atts['showtype'] !== "disabled") {
                    $filetype = substr(strrchr($post_meta['_rbve_doc'][0], '.'), 1);
                    $link_label .= ' [' . $filetype . ']';
                }

                // Create the document link
                $doc_link = '<li><a href="' . $post_meta['_rbve_doc'][0] . '">' . $link_label . '</a></li>';

                // Add the item to the output list
                $output .= $doc_link;
            }
        }
        $output .= '</ul>';
        $output .= '</div>';
    }
    else {
        // Note that this can happen if you have done an export/import as the metadata doesn't come with it, which is what is tagging the posts at present.
        $output .= "<ul><li>we have no correctly tagged posts yet for this type.</li></ul>";
    }

    // Reset the query ready for future checks
    wp_reset_query();

    // Return the output string
    return $output;
}


/**
 * @param $atts
 * @param $content
 * @return string
 *
 * Examples:
 *     [rbve_business_directory]
 */
function rbve_business_directory_shortcode_handler($atts, $content) {
    global $post;
    $output = '';

    // Get a list of all the business types that have been used
    $business_types = get_terms('rbve_tax_businesstype', array(
      'hide_empty' => true,
    ));

    // Loop through the types creating our directory groupings
    foreach ($business_types as $business_type) {
        $output .= '<h2>' . $business_type->name . '</h2>';
        $output .= '<ul>';

        $atts = _rbve_helper_atts("rbve_business");

        $atts['tax_query'] = array(
            array(
            'taxonomy' => 'rbve_tax_businesstype',
            'field' => 'slug',
            'terms' => $business_type->slug,
            )
        );

        // Run the query using the attributes set above
        $posts = new WP_Query($atts);

        // If the query returns results, then we will act on them
        if ($posts->have_posts()) {
            while ($posts->have_posts()) {
                // Load the post for the current loop iterator
                $posts->the_post();

                $post_meta = get_post_meta($post->ID);

                // Create the business link
                $doc_link = '<li><a href="' . get_permalink() . '">' . get_the_title() . '</a><br />' . $post_meta['_rbve_teaser'][0] . '</li>';
                $output .= $doc_link;
            }

        }
        $output .= '</ul>';
    }

    return $output;
}


/**
 * @param $atts
 * @param $content
 * @return string
 *
 * Examples:
 *     [rbve_business]
 *     [rbve_business businesstypes="public-house-restaurant"]
 */
function rbve_business_shortcode_handler($atts, $content) {
    global $post;
    $output = '';

    $output .= '<ul>';
    
    $atts = _rbve_helper_atts("rbve_business", $atts);

    // Get all the business' within this business type
    if (isset($atts['businesstypes'])) {
        $atts['tax_query'] = array(
          array(
            'taxonomy' => 'rbve_tax_businesstype',
            'field' => 'slug',
            'terms' => $atts['businesstypes'],
          )
        );
    }

    // Run the query using the attributes set above
    $posts = new WP_Query($atts);

    // If the query returns results, then we will act on them
    if ($posts->have_posts()) {
        while ($posts->have_posts()) {
            // Load the post for the current loop iterator
            $posts->the_post();

            $post_meta = get_post_meta($post->ID);

            // Create the business link
            $doc_link = '<li><a href="' . get_permalink() . '">' . get_the_title() . '</a><br />' .  $post_meta['_rbve_teaser'][0] . '</li>';
            $output .= $doc_link;
        }

    }
    $output .= '</ul>';

    return $output;
}


/**
 * @param $atts
 * @param $content
 * @return string
 *
 * Examples:
 *     [rbve_clubs_directory]
 */
function rbve_clubs_directory_shortcode_handler($atts, $content) {
    global $post;
    $output = '';

    $atts = _rbve_helper_atts("rbve_clubs_societies");

    // Run the query using the attributes set above
    $posts = new WP_Query($atts);

    // If the query returns results, then we will act on them
    if ($posts->have_posts()) {
        while ($posts->have_posts()) {
            // Load the post for the current loop iterator
            $posts->the_post();

            $post_meta = get_post_meta($post->ID);

            // Create the business link
            $doc_link = '<div class="rbve_club"><h2><a href="' . get_permalink() . '">' . get_the_title() . '</a></h2>';
            $doc_link .= (array_key_exists('_rbve_teaser', $post_meta) && $post_meta['_rbve_teaser'][0] ? '<p>' . $post_meta['_rbve_teaser'][0] . '</p>' : '');
            $doc_link .= (array_key_exists('_rbve_venue', $post_meta) && $post_meta['_rbve_venue'][0] ? '<p><strong>Venue:</strong> ' . $post_meta['_rbve_venue'][0] . '</p>' : '');
            $doc_link .= (array_key_exists('_rbve_datestimes', $post_meta) && $post_meta['_rbve_datestimes'][0] ? '<p><strong>Date/Time:</strong> ' . $post_meta['_rbve_datestimes'][0] . '</p>' : '');
            $doc_link .= '</div>';
            $output .= $doc_link;
        }

    }
//    $output .= '</ul>';

    return $output;
}

add_shortcode('rbve_docs','rbve_docs_shortcode_handler');
add_shortcode('rbve_business_directory','rbve_business_directory_shortcode_handler');
add_shortcode('rbve_business','rbve_business_shortcode_handler');
add_shortcode('rbve_clubs_directory','rbve_clubs_directory_shortcode_handler');
add_shortcode('rbve_events','rbve_events_shortcode_handler');
