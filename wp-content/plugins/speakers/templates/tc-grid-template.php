<div class="tc-grid-wrap tc-masonry-wrap">

    <?php


    if ($show_categories == 'yes') {
        $tc_speakers_tax = array();

        foreach ($tc_event_speakers_list as $tc_single_post_id) {
            $tc_speakers_terms = wp_get_post_terms($tc_single_post_id, 'tc_speakers_taxonomy', '');
            foreach ($tc_speakers_terms as $tc_speakers_single_term)
                $tc_speakers_tax[] = $tc_speakers_single_term->term_id;
        }

        $tc_speakers_tax = array_unique($tc_speakers_tax);
        ?>

        <div class="tc-speakers-taxonomies" id="tc-speakers-tax">
            <ul>
                
                <?php if(!empty($tc_speakers_tax)){ ?>
                    <li class="tc-sort-button" data-filter="*"><?php _e('All', 'tc'); ?></li>
                <?php } ?>

                <?php
                foreach ($tc_speakers_tax as $tc_speakers_single_tax) {
                    $tc_speakers_terms = get_term_by('id', $tc_speakers_single_tax, 'tc_speakers_taxonomy');
                    ?>
                    <li  class="tc-sort-button" data-filter="<?php echo '.tc-speaker-taxonomy-' . $tc_speakers_terms->term_id; ?>"><?php echo $tc_speakers_terms->name; ?></li>

                <?php } ?>
            </ul>
        </div><!-- .tc-speakers-taxonomies -->

    <?php } ?>

    <div class="tc-grid-wrap tc-masonry-cat-wrap">
        <?php
        $i = 1;

        $tc_show_in_popup = get_option('tc_speakers_settings');

        $args = array(
            'post_type' => 'tc_speakers',
            'posts_per_page' => -1,
            'post__in' => $tc_event_speakers_list
        );
        $tc_speakers_query = new WP_Query($args);
        while ($tc_speakers_query->have_posts()) : $tc_speakers_query->the_post();

            $tc_speakers_terms = wp_get_post_terms(get_the_ID(), 'tc_speakers_taxonomy');

            $tc_div_classes = '';
            foreach ($tc_speakers_terms as $tc_single_speaker_term) {
                $tc_div_classes .= 'tc-speaker-taxonomy-' . $tc_single_speaker_term->term_id . ' ';
            }
            ?>


            <div class="tc-speakers-grid tc-speakers-grid-num-<?php echo $grid_count . ' ' . $tc_div_classes ?>">
                <?php
                //speakers info meta
                $speaker_website = get_post_meta(get_the_ID(), 'speaker_website', true);
                $speaker_facebook = get_post_meta(get_the_ID(), 'speaker_facebook', true);
                $speaker_twitter = get_post_meta(get_the_ID(), 'speaker_twitter', true);
                $speaker_linkedin = get_post_meta(get_the_ID(), 'speaker_linkedin', true);
                $speaker_youtube = get_post_meta(get_the_ID(), 'speaker_youtube', true);
                $speaker_vimeo = get_post_meta(get_the_ID(), 'speaker_vimeo', true);
                $speaker_instagram = get_post_meta(get_the_ID(), 'speaker_instagram', true);
                $speaker_pinterest = get_post_meta(get_the_ID(), 'speaker_pinterest', true);
                $speaker_title = get_post_meta(get_the_ID(), 'speaker_title', true);

                if (has_post_thumbnail()) {
                    ?>
                    <div class="tc-speaker-image">
                        <?php
                        if ($tc_show_in_popup['show_popup'] == 'yes') {
                            echo '<a class="tc-magnific-popup-ajax tc-image-link" data-post-id="' . get_the_ID() . '" href="#tc-speaker-popup">';
                            the_post_thumbnail('tc-speakers-size');
                            echo '</a>';
                        } else {
                            echo '<a href="' . get_the_permalink(get_the_ID()) . '" class="tc-image-link">';
                            the_post_thumbnail('tc-speakers-size');
                            echo '</a>';
                        }
                        ?> 
                   
                    </div>
                
                    <div class="tc-speakers-title-wrap">

                        <?php if ($tc_show_in_popup['show_popup'] == 'yes') { ?>
                            <h4>
                                <a class="tc-magnific-popup-ajax" data-post-id="<?php echo get_the_ID(); ?>" href="#tc-speaker-popup">
                                    <?php the_title(); ?>
                                </a>
                            </h4>
                        <?php } else { ?>
                            <h4><a href="<?php echo get_the_permalink(get_the_ID()); ?>"><?php the_title(); ?></a></h4>
                        <?php } ?>
                            <?php if( !empty( $speaker_title)) { ?>
                                <span class="tc_speakers_title"><?php echo $speaker_title; ?></span> 
                            <?php } ?>
                        <a href="<?php echo $speaker_website; ?>" target="_blank" class="tc-speaker-website"><i class="fa fa-external-link" aria-hidden="true"></i></a>
                    </div><!-- .tc-speakers-title-wrap -->
                    
                    
                <?php } else {
                    ?>
                    <div class="tc-speaker-image">
                        <img src="<?php echo plugins_url() . '/speakers/'; ?>/images/default.png" />
                        <div class="tc-speakers-title-wrap">
                            <?php if ($tc_show_in_popup['show_popup'] == 'yes') { ?>
                                <h4>
                                    <a class="tc-magnific-popup-ajax" data-post-id="<?php echo get_the_ID(); ?>" href="#tc-speaker-popup">
                                        <?php the_title(); ?>
                                    </a>
                                </h4>
                            <?php } else { ?>
                                <h4><a href="<?php echo get_the_permalink(get_the_ID()); ?>"><?php the_title(); ?></a></h4>
                            <?php } ?>

                            <?php
                            if (!empty($speaker_title)) {
                                echo '<span>' . $speaker_title . '</span>';
                            }
                            if (!empty($speaker_website)) {
                                ?>
                                <a href="<?php echo $speaker_website; ?>"  target="_blank" class="tc-speaker-website"><i class="fa fa-external-link" aria-hidden="true"></i></a>
                            <?php } ?>
                        </div><!-- .tc-speakers-title-wrap -->
                    </div>
                <?php }
                ?>


                
            </div><!-- .tc-speakers-grid -->
    <?php if ($grid_count == $i) { ?>

                <div class="tc-clear"></div>

            <?php } ?>
            <?php
            $i++;
        endwhile;
        ?>

    </div>
</div>

<?php if ($tc_show_in_popup['show_popup'] == 'yes') { ?>
    <div id="tc-speaker-popup" class="white-popup mfp-hide"></div>
<?php } ?>
