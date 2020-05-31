<?php
$post_id = $_GET['id'];
if (is_null($post_id)) {
    exit;
}

global $wpdb;
//获取events信息
$events = $wpdb->get_results("select wp.* from w_posts wp where ID = {$post_id}");

if (!$events) {
    exit;
}
$events = (array)$events[0];

//获取events详情
$events_metas = $wpdb->get_results("select meta_key,meta_value from w_postmeta where post_id = {$post_id}");

$events_detail = [];
foreach ($events_metas as $val) {
    $events_detail[$val->meta_key] = $val->meta_value;
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- The above 3 meta tags *must* come first in the head; any other head content must come *after* these tags -->

    <link rel="icon" href="<?php bloginfo('template_directory'); ?>/img/page_logo.jpg">
    <title>chumi</title>

    <!-- Google font -->
    <link href="https://fonts.googleapis.com/css?family=Montserrat:400,700%7CVarela+Round" rel="stylesheet">

    <!-- Bootstrap -->
    <!--<link type="text/css" rel="stylesheet" href="<?php bloginfo('template_directory'); ?>/css/bootstrap.min.css" />-->

    <!-- Owl Carousel -->
    <link type="text/css" rel="stylesheet" href="<?php bloginfo('template_directory'); ?>/css/owl.carousel.css" />
    <link type="text/css" rel="stylesheet" href="<?php bloginfo('template_directory'); ?>/css/owl.theme.default.css" />

    <!-- Magnific Popup -->
    <link type="text/css" rel="stylesheet" href="<?php bloginfo('template_directory'); ?>/css/magnific-popup.css" />

    <!-- Font Awesome Icon -->
    <link rel="stylesheet" href="<?php bloginfo('template_directory'); ?>/css/font-awesome.min.css">

    <!-- Custom stlylesheet -->
    <!--<link type="text/css" rel="stylesheet" href="<?php bloginfo('template_directory'); ?>/css/style.css" />-->
    <link type="text/css" rel="stylesheet" href="<?php bloginfo('template_directory'); ?>/fonts/font.css" />
    <link type="text/css" rel="stylesheet" href="<?php bloginfo('template_directory'); ?>/fonts/feather.min.css" />
    <link type="text/css" rel="stylesheet" href="<?php bloginfo('template_directory'); ?>/css/template.css" />
    <!-- HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
    <script src="https://oss.maxcdn.com/html5shiv/3.7.3/html5shiv.min.js"></script>
    <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
    <![endif]-->
</head>

<body  class="menu-is-closed">
<div  class="">
    <header  id="navbar" class="header navbar-down">
        <nav>
            <a routerlink="/" class="header-logo"  href="/">
                <img alt="logo" src="https://dhjjgq45wu4ho.cloudfront.net/cover-5bef2491ca840587368fce2b-1547058591092.jpeg">
            </a>
            <div class="nav-list">
                <div class="nav-list flex-end">
                    <ul>
                        <li style="cursor: pointer;">
                            <a href="mailto: admin@chumi.co">Support</a>
                        </li>
                    </ul>
                </div>
            </div>
            <div class="menu-btn navbar-toggle">
                <i class="fe fe-menu"></i>
            </div>
        </nav>
    </header>
    <div  class="click-capture"></div>
    <div  class="menu">
        <span class="close-menu fe fe-x right-boxed"></span>
        <ul  class="menu-list right-boxed">
            <li >
                <a  href="mailto: admin@chumi.co">Support</a>
            </li>
        </ul>
        <div  class="menu-footer right-boxed"></div>
    </div>
    <main>
        <div  id="poster" class="event-banner" style="z-index: 0;">
            <svg xmlns="http://www.w3.org/2000/svg" version="1.1" width="781.2" height="500" id="blurred_4vrqq0q80"
                 class="bg-blur" viewBox="0 0 781.2 500" preserveAspectRatio="none" style="opacity: 1;">
                <filter id="blur_4vrqq0q80">
                    <feGaussianBlur in="SourceGraphic" stdDeviation="10"></feGaussianBlur>
                </filter>
                <image x="0" y="0" width="781.2" height="500" externalResourcesRequired="true"
                       xlink:href="<?php echo $events_detail['event_logo_file_url']; ?>"
                       style="filter:url(#blur_4vrqq0q80)" preserveAspectRatio="none"></image>
            </svg>
            <div class="bg-blur-overlay"></div>
            <div  class="wrapper">
                <div  class="post-content">
                    <div  class="post-content-inner">
                        <div  class="post-content-main">
                            <div  class="post-cover" style="background-image: url(&quot;<?php echo $events_detail['event_logo_file_url']; ?>&quot;);"></div>
                            <div  class="post-sidebar">
                                <small ></small>
                                <h2 ><?php echo $events['post_title']; ?></h2>
                                <p  class="by-organizer">By chumi</p>
                                <div  class="layout-info-content">
                                    <div  class="layout-info-item">
                                        <i class="fe fe-clock"></i>
                                        <div  class="layout-info-item-body">
                                            <h3>Date &amp; Time</h3>
                                            <p ><?php echo $events_detail['event_date_time']?></p>
                                        </div>
                                    </div>
                                    <div  class="layout-info-item"><i
                                            class="fe fe-map-pin"></i>
                                        <div  class="layout-info-item-body">
                                            <h3>Location</h3>
                                            <p ><?php echo $events_detail['detail_address']?></p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div  class="event-main">
            <div  class="wrapper">
                <div  class="post-content-foot">
                    <div  class="post-content-foot-left">
                        <div  class="pcf-time">
                            Doors Open<span ><?php echo explode(' ',$events_detail['event_date_time'])[1]?></span>
                        </div>
                        <div class="pcf-price">
                            Ticket from<span><span class="pcf-symbol">$</span><?php echo $events_detail['price'] ?></span>
                        </div>
                    </div>
                    <div  class="post-content-foot-right">
                        <button  class="btn btn-primary btn-block"> Buy Tickets</button>
                    </div>
                </div>
                <div  class="event-main-wrapper">
                    <div  class="event-main-content">
                        <div  class="post-sidebar">
                            <small ></small>
                            <h2 >MC热狗 HOTDOG w/Kenzy MJ116 03/27/20《Young Losers》北美巡演-温哥华站 </h2>
                            <p  class="by-organizer">By chumi</p>
                            <div  class="layout-info-content">
                                <div  class="layout-info-item">
                                    <i class="fe fe-clock"></i>
                                    <div  class="layout-info-item-body">
                                        <h3>Date &amp; Time</h3>
                                        <p >Fri, Mar 27 2020 7:00 PM</p>
                                    </div>
                                </div>
                                <div  class="layout-info-item">
                                    <i class="fe fe-map-pin"></i>
                                    <div  class="layout-info-item-body">
                                        <h3>Location</h3>
                                        <p >vogue</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div  class="layout-content-section">
                            <div  id="content">
                                <?php echo $events['post_content']?>
                            </div>
                        </div>
                    </div>
                </div>
                <div  class="event-main-foot">
                    <div  class="organizer">
                        <h2>Organizer</h2>
                        <a  href="#" class="organizer-item">
                            <img alt="" width="85" src="https://dhjjgq45wu4ho.cloudfront.net/">
                            <p>chumi</p>
                        </a>
                    </div>
                </div>
            </div>
        </div>
        <div  class="event-foot">
            <div  class="event-foot-wrap">
                <div >
                    <div  class="efw-desc">Ticket from</div>
                    <div  class="pcf-price">
                        <span>$</span><span >43</span>
                    </div>
                </div>
                <button  class="btn btn-primary"> Buy Tickets</button>
            </div>
        </div>
    </main>
    <footer >
        <div  class="footer-grid index-footer">
            <div  class="wrapper">
                <div  class="footer-single">
                    <img src="server/images/logo-white.svg" alt="">
                    <div  class="cp">
                        <a  href="https://www.chumi.co">All right reserved.©2020 Chumi Technologies, Inc</a>
                    </div>
                </div>
                <div  class="footer-content">
                    <div  class="footer-content-left">
                        <div  class="fcl-conditions">
                            Terms &amp; conditions | Privacy policy
                        </div>
                    </div>
                    <div  class="footer-content-right">
                        <div  class="copyright">
                            <a href="https://www.chumi.co">All right reserved. ©2020 Chumi Technologies Inc</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </footer>
</div>

<iframe id="chumi-iframe" src="<?php echo home_url().'/?page=seats&id='. $events['ID']?>" style="display:none;position: fixed; top: 0px; left: 0px; right: 0px; bottom: 0px; margin: 0px; border: 0px; width: 100%; height: 100%; z-index: 99999;">

</iframe>
</body>

</html>
<script type="text/javascript" src="<?php bloginfo('template_directory'); ?>/js/jquery.min.js"></script>
<script type="text/javascript" src="<?php bloginfo('template_directory'); ?>/js/bootstrap.min.js"></script>
<script type="text/javascript" src="<?php bloginfo('template_directory'); ?>/js/owl.carousel.min.js"></script>
<script type="text/javascript" src="<?php bloginfo('template_directory'); ?>/js/jquery.magnific-popup.js"></script>
<script type="text/javascript" src="<?php bloginfo('template_directory'); ?>/js/main.js"></script>
<script>
    function closeFrame(){
        $("#chumi-iframe").hide();
    }
</script>