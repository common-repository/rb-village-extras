<?php
/**
 * The Template for displaying all single posts.
 *
 */
get_header();
?>
<!--Start Content Grid-->
<div class="grid_24 content">
    <div  class="grid_16 alpha">
        <div class="content-wrap">
            <?php if (have_posts()) while (have_posts()) : the_post(); ?>

                <?php $post_meta = get_post_meta($post->ID); ?>
                <?php $facilities = wp_get_post_terms( $post->ID, 'rbve_tax_facilities_services' ); ?>

                <h1><?php the_title(); ?></h1>
                <?php print do_shortcode(wpautop( $post_meta['_rbve_body'][0] )); ?>

                <ul class="fa-ul">
                <?php
                $facility_icons = array(
                    'dog-friendly' => 'fa-paw',
                    'food-served' => 'fa-cutlery',
                    'alcohol-served' => 'fa-beer',
                    'hot-drinks-served' => 'fa-coffee',
                    'soft-drinks-served' => 'Soft Drinks Served', //Fontawesome - ???
                    'items-for-sale' => 'fa-shopping-bag',
                    'classes' => 'fa-graduation-cap',
                    'art-design-craft' => 'fa-paint-brush',
                    'historical-interest' => 'Historical Interest', //Fontawesome - ???
                    'financial-services' => 'fa-money',
                    'marketing-services' => 'Marketing Services', //Fontawesome - ???
                    'home-improvement' => 'fa-home',
                    'accommodation' => 'fa-bed',
                );
                foreach ($facilities as &$facility) {
                    print '<li><i class="fa-li fa fa-fw ' . $facility_icons[$facility->slug] . '" aria-hidden="true"></i>' . $facility->name . '</li>';
                }
                ?>
                </ul>

                <?php
                if ($post_meta['_rbve_addresspostcode'][0]) {
                    // MAP
                    $parsed_postcode = str_replace(' ', '+', $post_meta['_rbve_addresspostcode'][0]);
                    print '<a href="https://www.google.com/maps/place/ox10+7ra/"><img src="http://maps.googleapis.com/maps/api/staticmap?center=' . $parsed_postcode . '&zoom=17&scale=false&size=600x300&maptype=roadmap&format=png&visual_refresh=true&markers=size:small%7Ccolor:0x319310%7Clabel:1%7C' . $parsed_postcode . '" alt="Google Map of ' . $parsed_postcode . '"></a>';
                }
                ?>

            <?php endwhile; ?>
        </div>
    </div>

    <!-- Business specific sidebar -->
    <div class="grid_8 omega">
        <div class="sidebar">

            <style>
                .size-rbve_business_logo {
                    height: auto;
                }
            </style>
            <?php
            // LOGO
            $image = wp_get_attachment_image( $post_meta['_rbve_logo_id'][0], 'rbve_business_logo' );
            print $image;
            ?>

            <ul>
            <?php
            // CONTACT NAME/ROLE (if an individual
            (isset($post_meta['_rbve_contactname'][0]) ? print '<li><strong>Name:</strong></br> ' . $post_meta['_rbve_contactname'][0] . '</li>' : '');
            (isset($post_meta['_rbve_contactrole'][0]) ? print '<li><strong>Role:</strong></br> ' . $post_meta['_rbve_contactrole'][0] . '</li>' : '');

            // CONTACT DETAILS
            (isset($post_meta['_rbve_contactemail'][0]) ? print '<li><strong>Email:</strong></br> ' . make_clickable($post_meta['_rbve_contactemail'][0]) . '</li>' : '');
            (isset($post_meta['_rbve_contactphone'][0]) ? print '<li><strong>Phone:</strong></br> ' . $post_meta['_rbve_contactphone'][0] . '</li>' : '');
            (isset($post_meta['_rbve_contactwebsite'][0]) ? print '<li><strong>Web:</strong></br> ' . make_clickable($post_meta['_rbve_contactwebsite'][0]) . '</li>' : '');

            // SOCIAL MEDIA
            (isset($post_meta['_rbve_contacttwitter'][0]) ? print '<li><strong>Twitter:</strong></br> <a target="_blank" href="http://twitter.com/' . $post_meta['_rbve_contacttwitter'][0] . '">@' . $post_meta['_rbve_contacttwitter'][0] . '</a></li>' : '');
            (isset($post_meta['_rbve_contactfacebook'][0]) ? print '<li><strong>Facebook:</strong></br> ' . make_clickable($post_meta['_rbve_contactfacebook'][0]) . '</li>' : '');
            (isset($post_meta['_rbve_contactyoutube'][0]) ? print '<li><strong>YouTube:</strong></br> ' . make_clickable($post_meta['_rbve_contactyoutube'][0]) . '</li>' : '');
            (isset($post_meta['_rbve_contacttripadvisor'][0]) ? print '<li><strong>Trip Advisor:</strong></br> ' . make_clickable($post_meta['_rbve_contacttripadvisor'][0]) . '</li>' : '');
            ?>
            </ul>

            <?php
            if ($post_meta['_rbve_addresspostcode'][0]) {
                // ADDRESS
                print '<p><strong>Address:</strong><br />';
                print $post_meta['_rbve_addresshousenumber'][0] . ' ' . $post_meta['_rbve_addressstreet'][0] . '<br />';
                print $post_meta['_rbve_addresstownvillage'][0] . '<br />';
                print $post_meta['_rbve_addresspostcode'][0] . '<br />';
            }
            ?>

            <?php
            // OPENING HOURS
            print '<p><strong>Opening hours:</strong></p>';
            $days = array('monday' => 'Monday', 'tuesday' => 'Tuesday', 'wednesday' => 'Wednesday', 'thursday' => 'Thursday', 'friday' => 'Friday', 'saturday' => 'Saturday', 'sunday' => 'Sunday');
            foreach ($days as $key => &$day) {
                if ($post_meta['_rbve_opening' . $key][0]) {
                    print '<p><strong>' . $day . ':</strong></br>';
                    print $post_meta['_rbve_opening' . $key][0] . ' - ' . $post_meta['_rbve_opening' . $key . '2'][0];
                }
            }
            ?>
        </div>
    </div>

</div>
<div class="clear"></div>
<!--End Content Grid-->
</div>
<!--End Container Div-->
<?php get_footer(); ?>
