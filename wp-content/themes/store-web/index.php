<?php
global $wpdb;
//获取events信息
$events = $wpdb->get_results("select wp.* from w_posts wp where wp.post_type = 'tc_events' and wp.post_status='publish'");
$events_show = [];
foreach ($events as $val) {
    $value = (array)$val;
    $events_metas = $wpdb->get_results("select meta_key,meta_value from w_postmeta where post_id = {$value['ID']}");
    foreach($events_metas as $k => $v){
        $v = (array)$v;
        $value['events_metas'][$v['meta_key']] = $v['meta_value'];
    }
    $events_show[] = $value;
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
	<title>ChuMi</title>

	<!-- Google font -->
	<link href="https://fonts.googleapis.com/css?family=Montserrat:400,700%7CVarela+Round" rel="stylesheet">

	<!-- Bootstrap -->
	<link type="text/css" rel="stylesheet" href="<?php bloginfo('template_directory'); ?>/css/bootstrap.min.css" />

	<!-- Owl Carousel -->
	<link type="text/css" rel="stylesheet" href="<?php bloginfo('template_directory'); ?>/css/owl.carousel.css" />
	<link type="text/css" rel="stylesheet" href="<?php bloginfo('template_directory'); ?>/css/owl.theme.default.css" />

	<!-- Magnific Popup -->
	<link type="text/css" rel="stylesheet" href="<?php bloginfo('template_directory'); ?>/css/magnific-popup.css" />

	<!-- Font Awesome Icon -->
	<link rel="stylesheet" href="<?php bloginfo('template_directory'); ?>/css/font-awesome.min.css">

	<!-- Custom stlylesheet -->
	<link type="text/css" rel="stylesheet" href="<?php bloginfo('template_directory'); ?>/css/style.css" />

	<!-- HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries -->
	<!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
	<!--[if lt IE 9]>
		<script src="https://oss.maxcdn.com/html5shiv/3.7.3/html5shiv.min.js"></script>
		<script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
	<![endif]-->
</head>

<body>
	<!-- Header -->
	<header id="home">
		<!-- Background Image -->
		<div class="bg-img" style="background-image: url('<?php bloginfo('template_directory'); ?>/img/Mchotdog.jpeg');">
			<div class="overlay"></div>
		</div>
		<!-- /Background Image -->

		<!-- Nav -->
		<div>
			<nav id="nav" class="navbar nav-transparent">
				<div class="container">

					<div class="navbar-header">
						<!-- Logo -->
						<div class="navbar-brand">
							<a>
								<img class="logo" src="<?php bloginfo('template_directory'); ?>/img/juicymusiclogo3.jpg" alt="logo">
								<img class="logo-alt" src="<?php bloginfo('template_directory'); ?>/img/juicymusiclogo3.jpg" alt="logo">
							</a>
						</div>
						<!-- /Logo -->

						<!-- Collapse nav button -->
						<div class="nav-collapse">
							<span></span>
						</div>
						<!-- /Collapse nav button -->
					</div>

					<!--  Main navigation  -->
					<ul class="main-nav nav navbar-nav navbar-right">
						<li><a href="#home">chumi主页</a></li>
						<li><a href="#main-event">主办活动</a></li>
						<li><a href="#portfolio">近期活动</a></li>
						<li><a href="#about">关于chumi</a></li>
						<li><a href="#service">战略伙伴</a></li>
						<li><a href="#media">传媒合作</a></li>
						<li><a href="#contact">联系我们</a></li>
					</ul>
					<!-- /Main navigation -->

				</div>
			</nav>
		</div>
		<!-- /Nav -->

		<!-- home wrapper -->
		<div class="home-wrapper">
			<div class="container">
				<div class="row">

					<!-- home content -->
					<div class="col-md-10 col-md-offset-1">
						<div class="home-content">
							<h1 class="white-text">ChuMi 娱乐制作</h1>
							<p class="white-text">“潜心十六年 演艺界的巨型航母 浮出水面”
							</p>
							<h2 class="red-text"></h2>
<!-- 							<p class="white-text">杨千嬅&ensp;《My Beautiful Live》温哥华站</p> -->
<!-- 							<div class="home-logo">
								<iframe class="home-video" src="" frameborder="0" allow="accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
							</div> -->
<!-- 							<button class="white-btn" onclick="window.location.href='https://tickets.juicymusic.ca/post/yang-qian-12-02-my-beautiful-live-wen-ge-hua-zhan-5d5f62ceb02760977bc9f265'" download>线上购票</button> -->

						</div>
					</div>
					<!-- /home content -->

				</div>
			</div>
		</div>
		<!-- /home wrapper -->

	</header>
	<!-- /Header -->

	<!-- Main Event -->
	<div id="main-event" class="section md-padding bg-grey">

		<!-- Container -->
		<div class="container">

			<!-- Row -->
			<div class="row">

				<!-- Section header -->
				<div class="section-header text-center">
					<h2 class="title">主办活动</h2>
<!-- 					<h2>杨千嬅&ensp;《My Beautiful Live》温哥华站</h2> -->
				</div>
				<!-- /Section header -->

				<!-- Picture -->
				<div class="col-md-5 col-xs-6 work">
					<img class="img-responsive" src="<?php echo $events_show[0]['events_metas']['event_logo_file_url'] ?>" alt="">
				</div>
				<!-- /Picture -->

				<!-- why choose us content -->
				<div class="col-md-7">
					<div class="feature">
						</br>
						<p class="model-text">	<?php  echo $events_show[0]['events_metas']['event_synopsis'];?></br>
						</p>
					</div>
					<button class="black-btn" onclick="window.location.href='https://tickets.juicymusic.ca/post/mc-re-gou-hotdog-w-kenzy-mj116-03-27-20-young-losers-bei-mei-xun-yan-wen-ge-hua-zhan--5dfbf6b9cd1fa0578ed42e08'">线上购票</button>
					<!-- <a href="/forms/XinShuoChangRegisterForm.pdf" class="black-btn btn btn-xl" download></a> -->
				</div>
				<!-- /why choose us content -->

			</div>
			<!-- /Row -->

		</div>
		<!-- /Container -->

	</div>
	<!-- /Main Event -->

	<!-- Portfolio -->
	<div id="portfolio" class="section md-padding bg-grey">

		<!-- Container -->
		<div class="container">

			<!-- Row -->
			<div class="row">

				<!-- Section header -->
				<div class="section-header text-center">
					<h2 class="title">近期活动</h2>
				</div>
				<!-- /Section header -->

				<!-- Work Template-->
<!-- 				<div class ="col-md-4 col-xs-6">
					<div class="work work-date">
						<img class="img-responsive img-work" src="./<?php bloginfo('template_directory'); ?>/img/yangqianhua_square.jpg" alt="">
						<div class="overlay"></div>
						<div class="work-content">
							<span>杨千嬅</span>
							<h3>《My Beautiful Live》</br>温哥华站</h3>
							<div class="work-link">
								<a data-toggle="modal" data-target="#modal4"><i class="fa fa-external-link"></i></a>
							</div>
						</div>
					</div>
					<div>
						<p>时间：12月2日 7:30pm-10:30pm
						</br>地点：The Orpheum
						</br>地址：<a href ="https://goo.gl/maps/jmTcQuBi66GsLkWH7">601 Smithe St, Vancouver</a>
						</br>票价：CA$98.56-$781.76
						</br>
						</br><button class="black-btn" onclick="window.location.href='https://tickets.juicymusic.ca/post/yang-qian-12-02-my-beautiful-live-wen-ge-hua-zhan-5d5f62ceb02760977bc9f265'">线上购票</button>
						</p>
					</div>
				</div> -->
				<!-- /Work Template-->

                <?php foreach ($events_show as $key => $val) { ?>
                    <div class ="col-md-4 col-xs-6">
                        <div class="work work-date">
                            <img class="img-responsive img-work" src="<?php echo $val['events_metas']['event_logo_file_url']?>" alt="">
                            <div class="overlay"></div>
                            <div class="work-content">
                                <span><?php echo $val['events_metas']['star_name']?></span>
                                <h3>《<?php echo $val['events_metas']['event_short_name']?>》</br><?php echo $val['events_metas']['city_name']?>站</h3>
                                <div class="work-link">
                                    <a data-toggle="modal" data-target="#modal<?php echo $key;?>"><i class="fa fa-external-link"></i></a>
                                </div>
                            </div>
                        </div>
                        <div>
                            <p>时间：<?php echo $val['events_metas']['event_date_time'].'-'.$val['events_metas']['event_end_date_time']?>
                                </br>地点：<?php echo $val['events_metas']['detail_address']?>
                                </br>地址：<a href ="https://goo.gl/maps/r2TsEgpMaaUavABn6"><?php echo $val['events_metas']['event_location'] ?></a>
                                </br>票价：CA$<?php echo $val['events_metas']['price'] ?>
                                </br>
                                </br>
                                </br><button class="black-btn" onclick="window.location.href='https://tickets.juicymusic.ca/post/mc-re-gou-hotdog-w-kenzy-mj116-03-27-20-young-losers-bei-mei-xun-yan-wen-ge-hua-zhan--5dfbf6b9cd1fa0578ed42e08'">线上购票</button>
                            </p>
                        </div>
                    </div>
                <?php } ?>
			</div>
			<!-- /Row -->


            <?php foreach($events_show as $key => $val){?>
                <div class="modal fade" id="modal<?php echo $key?>" role="dialog">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <button type="button" class="close" data-dismiss="modal">&times;</button>
                                <h4 class="modal-title">《<?php echo $val['events_metas']['event_short_name']?>》&ensp;<?php echo $val['events_metas']['city_name']?>站</h4>
                            </div>
                            <div class="modal-body">
                                <p class="model-text"><?php echo $val['events_metas']['event_synopsis']; ?></br>
                                    <button class="black-btn" onclick="window.location.href='https://tickets.juicymusic.ca/post/mc-re-gou-hotdog-w-kenzy-mj116-03-27-20-young-losers-bei-mei-xun-yan-wen-ge-hua-zhan--5dfbf6b9cd1fa0578ed42e08'">线上购票</button>
                                </p>
                            </div>
                            <div>
                                <img class="img-responsive" src="<?php echo $val['events_metas']['event_logo_file_url']?>" alt="">
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                            </div>
                        </div>
                    </div>
                </div>
            <?php } ?>
  			<!-- Modal1 -->

			<!-- /Modal1 -->

			<!-- Modal2 -->
  			<div class="modal fade" id="modal2" role="dialog">
    			<div class="modal-dialog">
      				<div class="modal-content">
        				<div class="modal-header">
    						<button type="button" class="close" data-dismiss="modal">&times;</button>
      						<h4 class="modal-title">《失恋博物馆》&ensp;温哥华站</h4>
   						</div>
        				<div class="modal-body">
          					<p class="model-text">失恋博物馆 是由哔哩哔哩许捷许仙僧主办的以失恋物品为主题的创意文化展览。展览主要内容为各地粉丝网友所捐赠的和自己失恋或前任相关的失恋物品，每件失恋物品背后都有对应的感人故事 。在成都、西安、天津、重庆、永川、海口、苏州、杭州、昆明、呼和浩特、广州、哈尔滨等地同步开展。其他城市的展览也在陆续筹备即将在温哥华的2020年举办。
								<br>
								许捷许仙僧失恋博物馆首站展览于2018年12月份在成都举办。开展20天累计观展人数2.4万人，抖音平台相关话题浏览量破1亿次。
								<br>
								2019年5月1日起成都复展，全国12个城市同步开启巡展。许捷许仙僧失恋博物馆邀请并收藏来自各地的人们提供的恋爱纪念品及其背后的故事，希望用此方式让失恋者们——放下治愈，重新开始。
								<br>
								许捷许仙僧失恋博物馆共设6大主题内容区域，分别是失恋展品、扎心语录、互动寄语、粉红少女、快乐柠黄与海蓝梦想区。许捷许仙僧失恋博物馆还为年轻群体打造了色彩治愈区
								<button class="black-btn" onclick="window.location.href='https://www.chumi.co/post/xu-jie-xu-xian-seng-zhi-shi-lian-bo-wu-guan-02-15-2020-wen-ge-hua-zhan-5e001598cd1fa0578ed42e26'">线上购票</button>
							</p>
        				</div>
        				<div>
        					<img class="img-responsive" src="<?php bloginfo('template_directory'); ?>/img/BrokenHeartMuseum.jpeg" alt="">
        				</div>
        				<div class="modal-footer">
         					<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
        				</div>
      				</div>
    			</div>
  			</div>
  			<!-- /Modal2 -->

		</div>
		<!-- /Container -->

	</div>
	<!-- /Portfolio -->

	<!-- About -->
	<div id="about" class="section md-padding">

		<!-- Container -->
		<div class="container">

			<!-- Row -->
			<div class="row">

				<!-- Section header -->
				<div class="section-header text-center">
					<h2 class="title">关于chumi音乐</h2>
				</div>
				<!-- /Section header -->

				<!-- about -->
				<div class="col-md-4">
					<div class="about">
						<i class="fa fa-calendar"></i>
						<h3>大型演出活动</h3>
						<p>大型商业演出活动, 大型展示会开幕式, 闭幕式文艺演出, 博览会、交易会期间的文艺演出, 慈善文艺晚会, 个人大型演唱会等</p></br></br>
						<!-- <a data-toggle="modal" data-target="#myModal">Read more</a> -->
					</div>
					<!-- Modal -->
				</div>
				<!-- /about -->

				<!-- about -->
				<div class="col-md-4">
					<div class="about">
						<i class="fa fa-support"></i>
						<h3>经纪代理</h3>
						<p>中国移动, 诺基亚, 摩托罗拉, 巴黎欧莱雅, 苏宁电器, 创维, 爱玛, 雀巢, 可口可乐, 乐天, 蒙牛,
						伊利, 喜之郎, 美特斯邦威, 特步, 德尔惠, 康师傅, 达利食品, 雪碧, 唯品会, 百雀羚, 英雄联盟等广告代言, 涉及各个领</p>
					</div>
				</div>
				<!-- /about -->

				<!-- about -->
				<div class="col-md-4">
					<div class="about">
						<i class="fa fa-music"></i>
						<h3>唱片宣传, 策划</h3>
						<p>周杰伦《跨时代》《魔杰座》《我很忙》《依然范特西》《惊叹号》《十二新作》《哎哟，不错哦》
						《周杰伦的床边故事》内地宣传；潘玮柏《零零七》《玩酷》《反转地球》等全部七张专辑内地宣传等</p>
					</div>
				</div>
				<!-- /about -->

			</div>
			<!-- /Row -->

		</div>
		<!-- /Container -->

	</div>
	<!-- /About -->

	<!-- Why Choose Us -->
	<div id="features" class="section md-padding bg-grey">

		<!-- Container -->
		<div class="container">

			<!-- Row -->
			<div class="row">

				<!-- why choose us content -->
				<div class="col-md-6">
					<div class="section-header">
						<h2 class="title">代售各式演出门票</h2>
					</div>
					<p>温哥华线下最强售票团队</p>
					<div class="feature">
						<i class="fa fa-check"></i>
						<p>陈奕迅 11/14/2017 Orpheum Theatre</p>
					</div>
					<div class="feature">
						<i class="fa fa-check"></i>
						<p>理查德克莱德曼 9/30/2017 Orpheum Theatre</p>
					</div>
					<div class="feature">
						<i class="fa fa-check"></i>
						<p>张惠妹 11/29/2016 Thunderbird Sports Center</p>
					</div>
					<div class="feature">
						<i class="fa fa-check"></i>
						<p>Hebe 12/10/2015 Thunderbird Sports Center</p>
					</div>
					<div class="feature">
						<i class="fa fa-check"></i>
						<p>苏打绿 9/24/2015 Thunderbird Sports Center</p>
					</div>
				</div>
				<!-- /why choose us content -->

				<!-- About slider -->
				<div class="col-md-6">
					<div id="about-slider" class="owl-carousel owl-theme">
						<img class="img-responsive" src="<?php bloginfo('template_directory'); ?>/img/poster_dengziqi2.jpg" alt="">
						<img class="img-responsive" src="<?php bloginfo('template_directory'); ?>/img/poster_jay2.jpg" alt="">
						<img class="img-responsive" src="<?php bloginfo('template_directory'); ?>/img/about5.jpg" alt="">
						<img class="img-responsive" src="<?php bloginfo('template_directory'); ?>/img/about2.jpg" alt="">
						<img class="img-responsive" src="<?php bloginfo('template_directory'); ?>/img/about3.jpg" alt="">
						<img class="img-responsive" src="<?php bloginfo('template_directory'); ?>/img/poster_zhang2.jpg" alt="">
						<img class="img-responsive" src="<?php bloginfo('template_directory'); ?>/img/about4.jpg" alt="">
						<img class="img-responsive" src="<?php bloginfo('template_directory'); ?>/img/feiyuqing.jpg" alt="">
					</div>
				</div>
				<!-- /About slider -->

			</div>
			<!-- /Row -->

		</div>
		<!-- /Container -->

	</div>
	<!-- /Why Choose Us -->


	<!-- Numbers -->
	<div id="numbers" class="section sm-padding">

		<!-- Background Image -->
		<div class="bg-img" style="background-image: url('<?php bloginfo('template_directory'); ?>/img/background_zhang2.jpg');">
			<div class="overlay"></div>
		</div>
		<!-- /Background Image -->

		<!-- Container -->
		<div class="container">

			<!-- Row -->
			<div class="row">

				<div class="section-header text-center">
					<h2 class="title-sales title">销售记录</h2>
				</div>

				<!-- number -->
				<div class="col-sm-3 col-xs-6">
					<div class="number">
						<i class="fa fa-money"></i>
						<h3 class="white-text"><span class="counter">20万+</span></h3>
						<span class="white-text">张惠妹</span>
					</div>
				</div>
				<!-- /number -->

				<!-- number -->
				<div class="col-sm-3 col-xs-6">
					<div class="number">
						<i class="fa fa-trophy"></i>
						<h3 class="white-text"><span class="counter">10万+</span></h3>
						<span class="white-text">陈奕迅</span>
					</div>
				</div>
				<!-- /number -->

				<!-- number -->
				<div class="col-sm-3 col-xs-6">
					<div class="number">
						<i class="fa fa-money"></i>
						<h3 class="white-text"><span class="counter">4万+</span></h3>
						<span class="white-text">最高当日销售</span>
					</div>
				</div>
				<!-- /number -->

				<!-- number -->
				<div class="col-sm-3 col-xs-6">
					<div class="number">
						<i class="fa fa-file"></i>
						<h3 class="white-text"><span class="counter">40+</span></h3>
						<span class="white-text">代售场次</span>
					</div>
				</div>
				<!-- /number -->

			</div>
			<!-- /Row -->

		</div>
		<!-- /Container -->

	</div>
	<!-- /Numbers -->

	<!-- Service -->
	<div id="service" class="section md-padding">

		<!-- Container -->
		<div class="container">

			<!-- Row -->
			<div class="row">

				<!-- Section header -->
				<div class="section-header text-center">
					<h2 class="title">战略伙伴</h2>
				</div>
				<!-- /Section header -->
				<div class ="row" style="margin-left:0px;margin-right:0px;">
					<!-- service -->
					<div class="col-md-4 col-sm-4">
						<img class="img-responsive img-sponsor" src="<?php bloginfo('template_directory'); ?>/img/sponsor/jiaheng_logo.jpg" alt=""
						style ="margin-top:60px">
					</div>
					<!-- /service -->

					<!-- service -->
					<div class="col-md-4 col-sm-4">
						<img class="img-responsive img-sponsor" src="<?php bloginfo('template_directory'); ?>/img/sponsor/travel_logo.jpg" alt=""
						style ="margin-top:60px">
					</div>
					<!-- /service -->

					<!-- service -->
					<div class="col-md-4 col-sm-4">
						<img class="img-responsive img-sponsor" src="<?php bloginfo('template_directory'); ?>/img/sponsor/v_logo.jpg" alt=""
						style ="margin-top:60px">
					</div>
					<!-- /service -->
				</div>
				<div class ="row" style="margin-left:0px;margin-right:0px;">
					<!-- service -->
					<div class="col-md-4 col-sm-4">
						<img class="img-responsive img-sponsor" src="<?php bloginfo('template_directory'); ?>/img/sponsor/nder_logo.jpg" alt=""
						style ="margin-top:84px">
					</div>
					<!-- /service -->

					<!-- service -->
					<div class="col-md-4 col-sm-6">
						<img class="img-responsive img-sponsor" src="<?php bloginfo('template_directory'); ?>/img/sponsor/eat_logo.jpg" alt=""
						style ="margin-top:45px">
					</div>
					<!-- /service -->

					<!-- service -->
					<div class="col-md-4 col-sm-6">
						<img class="img-responsive img-sponsor" src="<?php bloginfo('template_directory'); ?>/img/sponsor/mei_logo.jpg" alt="">
					</div>
					<!-- /service -->
				</div>

				<div class ="row" style="margin-left:0px;margin-right:0px;">
					<!-- service -->
					<div class="col-md-4 col-sm-6">
						<div class="sponsor">
							<img class="img-responsive img-sponsor" src="<?php bloginfo('template_directory'); ?>/img/sponsor/zenq_logo.jpg" alt=""
							style ="margin-top:110px">
						</div>
					</div>
					<!-- /service -->

					<!-- service -->
					<div class="col-md-4 col-sm-6">
						<div class="sponsor">
							<img class="img-responsive img-sponsor" src="<?php bloginfo('template_directory'); ?>/img/sponsor/logo_xuebeier.jpg" alt="">
						</div>
					</div>
					<!-- /service -->

					<!-- service -->
					<div class="col-md-4 col-sm-6">
						<div class="sponsor">
							<img class="img-responsive img-sponsor" src="<?php bloginfo('template_directory'); ?>/img/sponsor/liuyishou_logo.jpg" alt=""
							style ="margin-top:20px">
						</div>
					</div>
					<!-- /service -->
				</div>

				<div class ="row" style="margin-left:0px;margin-right:0px;">
					<!-- service -->
					<div class="col-md-4 col-sm-6">
						<div class="sponsor">
							<img class="img-responsive img-sponsor" src="<?php bloginfo('template_directory'); ?>/img/sponsor/imaginarium_logo.jpg" alt=""
							style ="margin-top:105px">
						</div>
					</div>
					<!-- /service -->

					<!-- service -->
					<div class="col-md-4 col-sm-6">
						<div class="sponsor">
							<img class="img-responsive img-sponsor" src="<?php bloginfo('template_directory'); ?>/img/sponsor/momosnail_logo.jpg" alt=""
							style ="margin-top:85px">
						</div>
					</div>
					<!-- /service -->

					<!-- service -->
					<div class="col-md-4 col-sm-6">
						<div class="sponsor">
							<img class="img-responsive img-sponsor" src="<?php bloginfo('template_directory'); ?>/img/sponsor/zhouji_logo.jpg" alt=""
							style ="margin-top:45px">
						</div>
					</div>
					<!-- /service -->
				</div>

				<div class ="row" style="margin-left:0px;margin-right:0px;">
					<!-- service -->
					<div class="col-md-4 col-sm-6">
						<div class="sponsor">
							<img class="img-responsive img-sponsor" src="<?php bloginfo('template_directory'); ?>/img/sponsor/fruit_logo.jpg" alt=""
							style ="margin-top:55px">
						</div>
					</div>
					<!-- /service -->
				</div>

			</div>
			<!-- /Row -->

		</div>
		<!-- /Container -->

	</div>
	<!-- /Service -->

	<!-- Media -->
	<div id="media" class="section md-padding">

		<!-- Container -->
		<div class="container">

			<!-- Row -->
			<div class="row">

				<!-- Section header -->
				<div class="section-header text-center">
					<h2 class="title">传媒合作</h2>
				</div>
				<!-- /Section header -->
				<div class ="row" style="margin-left:0px;margin-right:0px;">
					<!-- service -->
					<div class="col-md-4 col-sm-6">
						<div class="sponsor">
							<img class="img-responsive img-sponsor" src="<?php bloginfo('template_directory'); ?>/img/media/logo_shiji.jpg" alt="">
						</div>
					</div>
					<!-- /service -->

					<!-- service -->
					<div class="col-md-4 col-sm-6">
						<div class="sponsor">
							<img class="img-responsive img-sponsor" src="<?php bloginfo('template_directory'); ?>/img/media/logo_youpinhui.jpg" alt=""
							style ="margin-top:45px">
						</div>
					</div>
					<!-- /service -->

					<!-- service -->
					<div class="col-md-4 col-sm-6">
						<div class="sponsor">
							<img class="img-responsive img-sponsor" src="<?php bloginfo('template_directory'); ?>/img/media/logo_jiaxi.jpg" alt=""
							style ="margin-top:90px">
						</div>
					</div>
					<!-- /service -->
				</div>


				<div class ="row" style="margin-left:0px;margin-right:0px;">
					<!-- service -->
					<div class="col-md-4 col-sm-6">
						<div class="sponsor">
							<img class="img-responsive img-sponsor" src="<?php bloginfo('template_directory'); ?>/img/media/logo_cityknows.jpg" alt=""
							style ="margin-top:90px">
						</div>
					</div>
					<!-- /service -->

					<!-- service -->
					<div class="col-md-4 col-sm-6">
						<div class="sponsor">
							<img class="img-responsive img-sponsor" src="<?php bloginfo('template_directory'); ?>/img/media/vanpeople_logo.jpg" alt=""
							style ="margin-top:80px">
						</div>
					</div>
					<!-- /service -->

					<!-- service -->
					<div class="col-md-4 col-sm-6">
						<div class="sponsor">
							<img class="img-responsive img-sponsor" src="<?php bloginfo('template_directory'); ?>/img/media/kabu_logo.jpg" alt="">
						</div>
					</div>
					<!-- /service -->
				</div>

			</div>
			<!-- /Row -->

		</div>
		<!-- /Container -->

	</div>
	<!-- /Media -->

	<!-- Contact -->
	<div id="contact" class="section md-padding">

		<!-- Container -->
		<div class="container">

			<!-- Row -->
			<div class="row">

				<!-- Section-header -->
				<div class="section-header text-center">
					<h2 class="title">联系我们</h2>
				</div>
				<!-- /Section-header -->

				<!-- contact -->
				<div class="col-sm-4">
					<div class="contact">
						<i class="fa fa-phone"></i>
						<h3>联络电话</h3>
						<a href="tel:778-251-9839">778-251-9839</a>
					</div>
				</div>
				<!-- /contact -->

				<!-- contact -->
				<div class="col-sm-4">
					<div class="contact">
						<i class="fa fa-envelope"></i>
						<h3>Email</h3>
						<a href = "mailto:juicymusicna@gmail.com">juicymusicna@gmail.com</a>
					</div>
				</div>
				<!-- /contact -->

				<!-- contact -->
				<div class="col-sm-4">
					<div class="contact">
						<i class="fa fa-map-marker"></i>
						<h3>通讯地址</h3>
						<a href="https://goo.gl/maps/MhLcHhuBnKo">1115-4731 McClelland Rd.</br>Richmond, BC, V6X0M5</a>
					</div>
				</div>
				<!-- /contact -->

				<!-- google map -->
<!--    			<div class="col-md-12">-->
<!--    				<div id="map">-->
<!--    					<script>-->
<!--						function initMap() {-->
<!--  							var juicymusic = {lat: 49.178518, lng: -123.122391};-->
<!--  							var map = new google.maps.Map(-->
<!--      						document.getElementById('map'), {zoom: 13, center: juicymusic});-->
<!--  							var marker = new google.maps.Marker({position: juicymusic, map: map});-->
<!--  						}-->
<!--    					</script>-->
<!--    					<script async defer-->
<!--    						src="https://maps.googleapis.com/maps/api/js?key=AIzaSyBwGtyukZeJRwoaVqPCf06MbweJOBs9lYM&callback=initMap">-->
<!--    					</script>-->
<!--    				</div>-->
<!--				</div>-->
				<!-- /google map -->

			</div>
			<!-- Row -->

		</div>
		<!-- /Container -->

	</div>
	<!-- /Contact -->


	<!-- Footer -->
	<footer id="footer" class="sm-padding bg-dark">

		<!-- Container -->
		<div class="container">

			<!-- Row -->
			<div class="row">

				<div class="col-md-12">

					<!-- footer logo -->
					<div class="footer-logo">
						<img class = "footer-img" src="<?php bloginfo('template_directory'); ?>/img/footer_logo.png" alt="logo">
					</div>
					<!-- /footer logo -->

					<!-- footer follow -->
					<ul class="footer-follow">
						<li><a href="https://www.facebook.com/%E5%B7%A8%E5%AE%A4%E9%9F%B3%E4%B9%90%E5%8C%97%E7%BE%8E-641690209548782/"><i class="fa fa-facebook"></i></a></li>
						<li><a href="https://www.instagram.com/juicymusic_na/"><i class="fa fa-instagram"></i></a></li>
					</ul>
					<!-- /footer follow -->

					<!-- footer copyright -->
					<div class="footer-copyright">
						<p>Copyright © 2018. All Rights Reserved. Designed by <a href="https://colorlib.com" target="_blank">Colorlib</a></p>
					</div>
					<!-- /footer copyright -->

				</div>

			</div>
			<!-- /Row -->

		</div>
		<!-- /Container -->

	</footer>
	<!-- /Footer -->

	<!-- Back to top -->
	<div id="back-to-top"></div>
	<!-- /Back to top -->

	<!-- Preloader -->
	<div id="preloader">
		<div class="preloader">
			<span></span>
			<span></span>
			<span></span>
			<span></span>
		</div>
	</div>
	<!-- /Preloader -->

	<!-- jQuery Plugins -->
	<script type="text/javascript" src="<?php bloginfo('template_directory'); ?>/js/jquery.min.js"></script>
	<script type="text/javascript" src="<?php bloginfo('template_directory'); ?>/js/bootstrap.min.js"></script>
	<script type="text/javascript" src="<?php bloginfo('template_directory'); ?>/js/owl.carousel.min.js"></script>
	<script type="text/javascript" src="<?php bloginfo('template_directory'); ?>/js/jquery.magnific-popup.js"></script>
	<script type="text/javascript" src="<?php bloginfo('template_directory'); ?>/js/main.js"></script>

</body>

</html>
