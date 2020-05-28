<?php
/**
 * The template for displaying the footer.
 *
 * Contains the closing of the #content div and all content after.
 *
 * @link https://developer.wordpress.org/themes/basics/template-files/#template-partials
 *
 * @package styledstore
 */

?>
<footer id="footer">
	<!-- get sidebar footer top for  -->
	<?php get_sidebar( 'footer-top');?>	
	<!-- add payment links  -->	
	<div class="footer-bottom">
		<?php do_action( 'styledstore_add_payment_links' ); ?>
	</div>	
		<div class="footer-bottombar">
			<div class="container">
<!--                diy begin-->
                <div>
                    <style>
                        .foot-item{
                            width: 18%;
                            float: left;
                            /*height: 180px;*/
                            margin: 0 5px;
                        }
                        .foot-item .foot-title{
                            color: black;
                            font-weight: bold;
                        }
                        .foot-item .foot-content{
                            color: #00b9eb;
                        }
                    </style>
                    <div class="foot-item">
                        <div>
                            <?php styledstore_the_custom_logo();?>
                            <span style="font-size: 24px;font-weight: bold">chumi</span>
                        </div>
                        <div style="margin: 5px 0 0 15px;">Available countries</div>
                    </div>
                    <div class="foot-item">
                        <a href=""><div class="foot-title">Features</div></a>
                        <a href=""><div class="foot-content">Ticketing</div></a>
                        <a href=""><div class="foot-content">Promoting</div></a>
                        <a href=""><div class="foot-content">Managing</div></a>
                        <a href=""><div class="foot-content">Reporting</div></a>
                    </div>
                    <div class="foot-item">
                        <a href=""><div class="foot-title">How it works</div></a>
                        <a href=""><div class="foot-content">Large-scale enterainment event</div></a>
                        <a href=""><div class="foot-content">Medium enterainment event</div></a>
                        <a href=""><div class="foot-content">Venue box office</div></a>
                        <a href=""><div class="foot-content">Sport venues</div></a>
                    </div>
                    <div class="foot-item">
                        <a href=""><div class="foot-title">Follow</div></a>
                        <a href=""><div class="foot-content">Blog</div></a>
                        <a href=""><div class="foot-content">About</div></a>
                        <a href=""><div class="foot-content">Contact us</div></a>
                        <a href=""><div class="foot-content">Partnership</div></a>
                    </div>
                    <div class="foot-item">
                        <a href=""><div class="foot-title">Resources</div></a>
                        <a href=""><div class="foot-content">Product Tours</div></a>
                        <a href=""><div class="foot-content">Help Docs</div></a>
                    </div>
                </div>
<!--                diy end-->
				<?php
				if  ( get_theme_mod( 'styledstore_show_footer_text' ) != '1' ) {?>
					<div class="copyright">
	                    <?php esc_html_e( 'Styled Store WordPress Theme by', 'styled-store' ); ?>
						<a href="<?php echo esc_url('https://www.styledthemes.com/'); ?>" target="_blank"><?php esc_html_e( 'StyledThemes', 'styled-store' ); ?></a>
					</div>
				<?php } ?>

				<div class="footer-menu">
						
					<?php $styledstore_primary_nav = array(
						'theme_location'	=> 'footer',
						'container'	=> false,
						'menu_class'	=> 'sm',
						'menu_id'	=> 'footer-menu',
						'depth'	=> 1,
						'fallback_cb' => false
					);
					wp_nav_menu( $styledstore_primary_nav ); ?>
					
				</div>
			</div>
		</div>
    <div style="clear:both;text-align: center;border-top: grey 1px solid;">© Chumi technologies,inc.Terms Privacy</div>
</footer>
<?php wp_footer(); ?>
</body>
</html>
