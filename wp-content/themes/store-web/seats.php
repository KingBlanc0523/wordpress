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
<html lang="en" style="margin: 0 auto; height:100%;">

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
    <link rel="stylesheet" href="<?php bloginfo('template_directory'); ?>/css/events_frame.css">
    <!-- HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
    <script src="https://oss.maxcdn.com/html5shiv/3.7.3/html5shiv.min.js"></script>
    <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
    <![endif]-->
</head>
<body class="menu-is-closed" style="height:100%">
<div class="animsition">
    <div class="platform-modal">
        <div class="platform-modal-body"><!---->
            <div class="platform-layout">
                <div class="platform-layout-content">
                    <div class="layout-container"><!---->
                        <header class="layout-container-head">
                            <div class="layout-head-wrapper">
                                <button class="btn layout-close"><i class="fe fe-x"></i></button>
                                <div class="layout-head-title">
                                    <h2><?php echo $events['post_title']; ?> </h2>
                                    <p>Event starts on <?php echo $events_detail['event_date_time']?></p><!---->
                                </div>
                                <div class="layout-head-language" style="display: none">
                                    <div class="field"><!---->
                                        <select class="sm-size ng-untouched ng-pristine ng-valid" name="currentLanguage">
                                            <option disabled="">En</option><!---->
                                            <option value="en">En</option>
                                            <option value="cn">中文</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </header><!---->
                        <main class="layout-container-main" id="top">
                            <div class="layout-main-content"><!----><!----><!---->
                                <div class="overly-loading" style="background: white;">
                                    <div class="layout-main-content-body">
                                        <div class="layout-content-body-wrapper">
                                            <div class="layout-content-section"><h3 class="lcs-title">This event has
                                                    ended</h3></div>
                                        </div>
                                    </div>
                                </div>
                                <div class="layout-main-content-body">
                                    <div class="layout-content-body-wrapper"><!---->
                                        <div class="steppers">
                                            <div class="step-root active">
                                                <div class="step-root-symbol"><span class="step-root-num">1</span></div>
                                                <span class="step-root-name">Ticket List</span></div><!---->
                                            <div class="step-contact"></div>
                                            <div class="step-root">
                                                <div class="step-root-symbol"><span class="step-root-num">2</span></div>
                                                <span class="step-root-name">Payment</span></div>
                                            <div class="step-contact last"></div>
                                            <div class="step-root last">
                                                <div class="step-root-symbol"><span class="step-root-num"><svg
                                                            height="18" viewBox="0 0 18 18" width="18"><path
                                                                d="M0,0h18v18H0V0z" fill="none"></path><polygon fill="#ccc"
                                                                                                                points="14.4,3.8 7.5,10.7 3.8,7 2,8.7 7.5,14.2 16.2,5.5 "></polygon></svg></span>
                                                </div>
                                            </div>
                                        </div>
                                        <router-outlet></router-outlet>
                                        <app-ticket-list><!---->
                                            <div class="layout-content-section"><h3 class="lcs-title">Event
                                                    information</h3>
                                                <div class="lcs-intro"><p></p></div>
                                            </div><!---->
                                            <div class="layout-content-section"><!----><h3 class="lcs-title">
                                                    Tickets</h3>
                                                <ul class="lcs-tickets-list"><!----><!----><!----><!----></ul>
                                            </div>
                                        </app-ticket-list>
                                    </div>
                                </div><!----><!---->
                                <div class="layout-main-content-foot">
                                    <div class="layout-content-foot-wrapper">
                                        <div class="foot-checkout">
                                            <button class="btn shop-bag">
                                                <svg height="24" viewBox="0 0 24 24" width="24">
                                                    <path d="M0 0h24v24H0z" fill="none"></path>
                                                    <path d="M4 6.414L.757 3.172l1.415-1.415L5.414 5h15.242a1 1 0 0 1 .958 1.287l-2.4 8a1 1 0 0 1-.958.713H6v2h11v2H5a1 1 0 0 1-1-1V6.414zM6 7v6h11.512l1.8-6H6zm-.5 16a1.5 1.5 0 1 1 0-3 1.5 1.5 0 0 1 0 3zm12 0a1.5 1.5 0 1 1 0-3 1.5 1.5 0 0 1 0 3z"></path>
                                                </svg>
                                                <svg class="shop-layout-arrow" height="8px" viewBox="0 0 12 8"
                                                     width="12px">
                                                    <polyline clip-rule="evenodd" fill="none" fill-rule="evenodd"
                                                              points="10.6,1.5 6,6.5 1.4,1.5 " stroke="#1c1c1c"
                                                              stroke-linecap="round" stroke-miterlimit="10"
                                                              stroke-width="1.5"></polyline>
                                                </svg>
                                                <span class="badge badge-red">0</span></button>
                                            <div class="foot-total-price"><span>CA$0.00</span></div>
                                        </div>
                                        <button class="btn btn-dark" disabled="">Next</button>
                                    </div>
                                </div>
                            </div>
                        </main>
                    </div>
                </div>
                <div class="platform-layout-pane">
                    <div class="layout-aside">
                        <div class="layout-aside-head"><!---->
                            <img  src="<?php echo $events_detail['event_logo_file_url']; ?>">
                            <div class="padding-open"></div>
                        </div><!----><!---->
                        <div class="layout-aside-body"><h3 class="layout-aside-body-title">Order Summary</h3>
                            <div class="aside-order-list">
                                <div class="aside-order-item total">
                                    <div class="aoi-title"><span>Your Tickets</span></div>
                                </div><!----><!----><!---->
                                <hr><!----><!---->
                                <div class="aside-order-item">
                                    <div class="aoi-title"><span>Subtotal</span></div>
                                    <div class="aoi-price">CA$0.00</div>
                                </div><!---->
                                <div class="aside-order-item">
                                    <div class="aoi-title"><span>Facility Fee</span></div>
                                    <div class="aoi-price"></div>
                                </div>
                                <div class="aside-order-item">
                                    <div class="aoi-title"><span>GST</span></div>
                                    <div class="aoi-price"></div>
                                </div><!----><!---->
                                <div class="aside-order-item">
                                    <div class="aoi-title"><span>Processing fee</span></div>
                                    <div class="aoi-price">CA$0.00</div>
                                </div>
                                <hr>
                                <div class="aside-order-item total">
                                    <div class="aoi-title"><span>Total</span></div>
                                    <div class="aoi-price">CA$0.00</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="close-platform-modal"><img alt="" src="<?php bloginfo('template_directory'); ?>/img/close-player.svg"></div>
    </div>
</div>
</body>
</html>
<script type="text/javascript" src="<?php bloginfo('template_directory'); ?>/js/jquery.min.js"></script>
<script type="text/javascript" src="<?php bloginfo('template_directory'); ?>/js/bootstrap.min.js"></script>
<script type="text/javascript" src="<?php bloginfo('template_directory'); ?>/js/owl.carousel.min.js"></script>
<script type="text/javascript" src="<?php bloginfo('template_directory'); ?>/js/jquery.magnific-popup.js"></script>
<script type="text/javascript" src="<?php bloginfo('template_directory'); ?>/js/main.js"></script>
<script>
    $('.close-platform-modal').click(function(){
        parent.closeFrame();
    })
</script>