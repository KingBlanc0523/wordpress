<?php
/**
 * The header for our theme.
 * This is the template that displays all of the <head> section and everything up until <div id="content">
 * @link https://developer.wordpress.org/themes/basics/template-files/#template-partials
 * @package styledstore
 */

?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
<meta charset="<?php bloginfo( 'charset' ); ?>">
<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
<link rel="profile" href="http://gmpg.org/xfn/11">
<link rel="pingback" href="<?php bloginfo( 'pingback_url' ); ?>">
<?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>
<!-- add scroll top feature -->
<?php do_action( 'styledstore_move_to_top' ); ?>
<header id="header">
	<div class="row">
		<div class="header-topbar" style="background: #fff;">
			<div class="container" style="width: 100%;">

                <div class="logo-container sameheight col-xs-8 col-sm-6 col-md-4" style="margin-left: 0;">
                    <?php if( has_custom_logo() ) {
                        styledstore_the_custom_logo();
                    } else { ?>
                        <a href="<?php echo esc_url( home_url('/') ); ?>">
                            <div class="logo">
                                <div class="site-title"> <?php echo get_bloginfo('name'); ?> </div>
                                <div class="site-desc">	<?php echo get_bloginfo('description'); ?> </div>
                            </div>
                        </a>
                    <?php } ?>
                    <span style="font-size: 24px;font-weight: bold;">chumi</span>
                </div>
			<?php
				/**
				 * @author StyledThemes 
				 * @action_hook header_top_bar_social_links
				 * @uses social media links with icon
				 * @package styledstore-function
				*/

				do_action( 'styledstore_header_top_bar_social_links' );
			?>
				<?php if( styledstore_check_woocommerce_activation() ) { ?>
					<div class="lgn-ct" style="height: 72px;line-height: 72px;">
						<div class="acc-login" style="border-right: none;line-height: 30px;line-height: 30px;text-align: center;">
                            <a href="">
                                <span style="margin: auto 10px;">    Refer & Get Paid    </span>
                            </a>
                            <a href="">
                                <span style="margin: auto 10px;">   My Tickets   </span>
                            </a>
							<?php if ( is_user_logged_in() ) { ?>
							 	<a href="<?php echo esc_url( wc_get_endpoint_url( 'customer-logout', '', wc_get_page_permalink( 'myaccount' ) ) ); ?>" class="logout">
			                        <?php esc_html_e(' Logout', 'styled-store'); ?>
			                    </a>
							 <?php } 
							 else { ?>
							 	<a href="<?php echo esc_url( get_permalink( get_option('woocommerce_myaccount_page_id') ) ); ?>" title="<?php esc_attr_e('Login','styled-store'); ?>"><?php esc_html_e('Login','styled-store'); ?></a>
							 <?php } ?>
                            <a href="">
                                <span style="width: 100px;height: 30px;line-height: 30px;display: inline-block;background: blue;color: #fff;margin: auto 10px;">  Create Event  </span>
                            </a>
						</div>

						<div class="header-top-cart mini-cart" style="display: none;">
							<a class="st-cart-contents" href="<?php echo esc_url( wc_get_cart_url() ); ?>" title="<?php esc_attr_e( 'View your shopping cart', 'styled-store' ); ?>"><?php esc_html_e( 'cart /', 'styled-store' ); echo WC()->cart->get_cart_total(); ?><i class="fa fa-shopping-cart fa-lg" aria-hidden="true"></i>
								<span class="mini-cart-count-item"><?php echo sprintf ( _n( '%d', '%d', WC()->cart->get_cart_contents_count(), 'styled-store' ), WC()->cart->get_cart_contents_count() ); ?>
								</span>
							</a>
							<?php the_widget( 'WC_Widget_Cart', '' ); ?>
						</div>
					</div>
				<?php } ?>	
			</div>
		</div>

		<div class="header">
			<div class="container" style="width: 100%;padding: 0;background: black !important;">

			<!-- </div> -->
			
			<?php
				/**
				 * @author StyledThemes 
				 * @action_hook styledstore_header_navigation_menu
				 * @uses Naviagation Menu
				*/

				do_action( 'styledstore_header_navigation_menu' );
			?>

		<!-- </div> -->
	</div>
</header>

<?php if ( has_header_image() && is_front_page() ) : ?>
	<div class="custom-header-image">
		<a href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home">
			<img src="<?php header_image(); ?>" height="<?php echo esc_attr( get_custom_header()->height ); ?>" width="<?php echo esc_attr( get_custom_header()->width ); ?>" alt="<?php esc_attr_e( 'custom header image', 'styled-store' ); ?>" />
		</a>	
	</div>
<?php endif;
