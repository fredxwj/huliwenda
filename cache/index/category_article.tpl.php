<?php defined('IN_YZMPHP') or exit('No permission resources.'); ?><!doctype html>
<html>
<head>
<meta charset="utf-8">
<title><?php echo $seo_title;?></title>
<meta name="keywords" content="<?php echo $keywords;?>" />
<meta name="description" content="<?php echo $description;?>" />
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link href="<?php echo SITE_PATH;?>application/index/view/<?php echo C('site_theme');?>/css/base.css" rel="stylesheet">
<link href="<?php echo SITE_PATH;?>application/index/view/<?php echo C('site_theme');?>/css/index.css" rel="stylesheet">
<link href="<?php echo SITE_PATH;?>application/index/view/<?php echo C('site_theme');?>/css/m.css" rel="stylesheet">
<script src="<?php echo SITE_PATH;?>application/index/view/<?php echo C('site_theme');?>/js/jquery.min.js" type="text/javascript"></script>
<script src="<?php echo SITE_PATH;?>application/index/view/<?php echo C('site_theme');?>/js/jquery.easyfader.min.js"></script>
<script src="<?php echo SITE_PATH;?>application/index/view/<?php echo C('site_theme');?>/js/scrollReveal.js"></script>
<script src="<?php echo SITE_PATH;?>application/index/view/<?php echo C('site_theme');?>/js/common.js"></script>
<!--[if lt IE 9]>
<script src="<?php echo SITE_PATH;?>application/index/view/<?php echo C('site_theme');?>/js/modernizr.js"></script>
<![endif]-->
</head>
<body>
<?php include template("index","header"); ?> 
<!--div class="pagebg sh">sss</div-->
<div class="container">
<?php $data = get_childcat($catid);?>
			 <?php if(is_array($data)) foreach($data as $k=>$v) { ?>
				<div class="sub_class_title">
					<h1 class="mt80"><?php echo $v['catname'];?> / <?php echo $v['catdir'];?></h1>
					<span><?php echo $v['subtitle'];?></span>
				</div>
				<div class="sub_class_more">
				     <a href="<?php echo $v['pclink'];?>" class="moreBtn" target="_blank">查看更多</a>
				</div>
				<div class="share">

				<ul>
				<?php $tag = yzm_base::load_sys_class('yzm_tag');if(method_exists($tag, 'lists')) {$data = $tag->lists(array('field'=>'title,inputtime,url,click,thumb','catid'=>$v['catid'],'limit'=>'4',));}?>
			  	<?php if(is_array($data)) foreach($data as $v) { ?>	
				 <li> <div class="shareli"><a href="<?php echo $v['url'];?>" target="_blank"> <i><img src="<?php if(!empty($v['thumb'])) { ?><?php echo $v['thumb'];?><?php } else { ?><?php echo STATIC_URL;?>images/nopic.jpg<?php } ?>"></i>
				      <h2><b><?php echo $v['title'];?></b></h2>
				      <span>浏览 | <?php echo $v['click'];?></span> </a></div> </li>
				<?php } ?> 
				</ul>

</div>

<?php } ?>	

<div class="pagelist" id="page"></div>


</div>
<?php include template("index","footer"); ?> 
