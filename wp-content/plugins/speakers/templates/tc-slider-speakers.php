<div class="tc-speakers-slider flexslider">

    <ul class="slides">
        <?php
        $tc_show_in_popup = get_option('tc_speakers_settings');
        $args = array(
            'post_type' => 'tc_speakers',
            'posts_per_page' => -1,
            'post__in' => $tc_event_speakers_list
        );
        $tc_speakers_query = new WP_Query($args);
        while ($tc_speakers_query->have_posts()) : $tc_speakers_query->the_post();
        $speaker_title = get_post_meta(get_the_ID(), 'speaker_title', true);

                ?>

                <li>
                    <div class="tc-speaker-image-wrap">
                        <?php
                        if ($tc_show_in_popup['show_popup'] == 'yes') {
                            echo '<a class="tc-magnific-popup-ajax tc-image-link" data-post-id="' . get_the_ID() . '" href="#tc-speaker-popup">';
                            if (has_post_thumbnail()) {
                                the_post_thumbnail('tc-speakers-slider');
                            } else {?>
                              <img src="<?php plugin_dir_url( __DIR__ ) . '/images/default-slider.png' ?>"/>   
                            <?php }
                            echo '</a>';
                        } else {
                            echo '<a href="' . get_the_permalink(get_the_ID()) . '" class="tc-image-link">';
                            if (has_post_thumbnail()) {
                                the_post_thumbnail('tc-speakers-slider');
                            } else { ?>
                              <img src="<?php echo plugin_dir_url(  __DIR__  ) . 'images/default-slider.png' ?>"/>   
                            <?php }
                            echo '</a>';
                        }
                        ?> 

                    </div>
                    <div class="tc-speaker-description-wrap">
                        <?php if ($tc_show_in_popup['show_popup'] == 'yes') { ?>
                                    <h3> 
                                        <a class="tc-magnific-popup-ajax tc-speaker-name" data-post-id="<?php echo get_the_ID(); ?>" href="#tc-speaker-popup"><?php the_title(); ?>
                                        </a>
                                    </h3>
                                <?php if( !empty( $speaker_title)) { ?>
                                    <span class="tc_speakers_title"><?php echo $speaker_title; ?></span> 
                                <?php } ?>

                        <?php } else { ?>
                                    <h3>
                                        <a class="tc-speaker-name" href="<?php echo get_the_permalink(get_the_ID()); ?>"><?php the_title(); ?>
                                        </a>
                                    </h3>
                                <?php if( !empty( $speaker_title)) { ?>
                                    <span class="tc_speakers_title"><?php echo $speaker_title; ?></span> 
                                <?php } ?>
                                <?php } ?>
                        <div class="tc-speaker-excerpt">
                            <?php the_excerpt(); ?>
                        </div><!-- .tc-speaker-excerpt -->
                    </div><!-- .tc-speaker-description-wrap --> 
                    
                    
                </li>
<?php endwhile; ?>
    </ul>

</div><!-- .tc-speakers-slider -->
<?php if ($tc_show_in_popup['show_popup'] == 'yes') { ?>
    <div id="tc-speaker-popup" class="white-popup mfp-hide"></div>
<?php } ?>