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
<article> 
  <!--banner begin-->
 <div class="picsbox"> 
  <div class="banner">
    <div id="banner" class="fader">
<?php $tag = yzm_base::load_sys_class('yzm_tag');if(method_exists($tag, 'banner')) {$data = $tag->banner(array('field'=>'title,image,url,typeid,status','limit'=>'10',));}?>
	<?php if(is_array($data)) foreach($data as $v) { ?>
	<li class="slide" ><a href="<?php echo $v['url'];?>" target="_blank"><img src="<?php echo $v['image'];?>" alt="<?php echo $v['title'];?>" title="<?php echo $v['title'];?>"><span class="imginfo"><?php echo $v['title'];?></span></a></li>
	<?php } ?>

      <div class="fader_controls">
        <div class="page prev" data-target="prev">&lsaquo;</div>
        <div class="page next" data-target="next">&rsaquo;</div>
        <ul class="pager_list">
        </ul>
      </div>
    </div>
  </div>
  <!--banner end-->
  <div class="toppic">
  <?php $tag = yzm_base::load_sys_class('yzm_tag');if(method_exists($tag, 'lists')) {$data = $tag->lists(array('field'=>'title,thumb,url,catid','modelid'=>'1','flag'=>'1','limit'=>'2',));}?>
        <?php if(is_array($data)) foreach($data as $v) { ?>
    <li> <a href="<?php echo $v['url'];?>" target="_blank" title="<?php echo $v['title'];?>"> <i><img src="<?php echo $v['thumb'];?>" alt="<?php echo $v['title'];?>"></i>
      <h2><?php echo str_cut($v['title'], 75);?></h2>
      <span><?php echo get_catname($v['catid']);?></span> </a> </li>
	<?php } ?>
  </div>
  </div>
  <div class="blank"></div>
  <!--blogsbox begin-->
  <div class="blogsbox">
<!--   <div class="pics">
    <ul>
	
      <li><i><?php echo adver(1);?></i><span>本站金主</span></li>
      <li><i><?php echo adver(2);?></i><span>支持本站</span></li>
      <li><i><?php echo adver(3);?></i><span>广而告之</span></li>
    </ul>
  </div> -->
<!-- 	<ul id="blogtab">
	 <li class="current">站长推荐</li>
	 <li><?php echo adver(4);?></li>
	 <li><?php echo adver(5);?></li>
	 <li><?php echo adver(6);?></li>
	 <li><?php echo adver(7);?></li>
	 <li><?php echo adver(8);?></li>
	</ul> -->
<?php $tag = yzm_base::load_sys_class('yzm_tag');if(method_exists($tag, 'lists')) {$data = $tag->lists(array('field'=>'title,url,thumb,catid,description,inputtime,nickname,click','modelid'=>'1','limit'=>'15',));}?>
   <?php if(is_array($data)) foreach($data as $v) { ?>
    <div class="blogs" data-scroll-reveal="enter bottom over 1s" >
      <h3 class="blogtitle"><a href="<?php echo $v['url'];?>" target="_blank"><?php echo $v['title'];?></a></h3>
      <span class="blogpic"><a href="<?php echo $v['url'];?>" title="<?php echo $v['title'];?>"><img src="<?php if(!empty($v['thumb'])) { ?><?php echo $v['thumb'];?><?php } else { ?><?php echo STATIC_URL;?>images/nopic.jpg<?php } ?>" alt="<?php echo $v['title'];?>" title="<?php echo $v['title'];?>"></a></span>
      <p class="blogtext"><?php echo $v['description'];?></p>
      <div class="bloginfo">
        <ul>
          <li class="author"><a href="javascript:void"><?php echo $v['nickname'];?></a></li>
          <li class="lmname"><a href="<?php echo get_category($v['catid'], 'pclink');?>"><?php echo get_catname($v['catid']);?></a></li>
          <li class="timer"><?php echo date('Y-m-d H:i:s',$v['inputtime']);?></li>
          <li class="view"><span><?php echo $v['click'];?></span>次阅读</li>
          <!--li class="like">9999</li-->
        </ul>
      </div>
    </div>
<?php } ?>
    

  </div>
  <!--blogsbox end-->
  <div class="sidebar">
    <div class="zhuanti">
      <h2 class="hometitle">特别推荐</h2>
      <ul>
        <?php $tag = yzm_base::load_sys_class('yzm_tag');if(method_exists($tag, 'lists')) {$data = $tag->lists(array('field'=>'title,thumb,catid,url','thumb'=>'1','modelid'=>'1','flag'=>'3','limit'=>'3',));}?>
    <?php if(is_array($data)) foreach($data as $v) { ?>	
            <li> <i><img src="<?php echo $v['thumb'];?>"></i>
              <p><?php echo str_cut($v['title'], 81);?> <span><a href="<?php echo $v['url'];?>">阅读</a></span> </p>
            </li>
    <?php } ?>
      </ul>
    </div>
<!--     <div class="tuijian">
      <h2 class="hometitle">推荐文章</h2>
      <ul class="tjpic">
        <?php $tag = yzm_base::load_sys_class('yzm_tag');if(method_exists($tag, 'lists')) {$data = $tag->lists(array('field'=>'title,url,thumb,inputtime','modelid'=>'1','flag'=>'4','limit'=>'1',));}?>
			<?php if(is_array($data)) foreach($data as $v) { ?>	
        <i><img src="<?php if(!empty($v['thumb'])) { ?><?php echo $v['thumb'];?><?php } else { ?><?php echo STATIC_URL;?>images/nopic.jpg<?php } ?>" alt="<?php echo $v['title'];?>"></i>
        <p><a href="<?php echo $v['url'];?>"><?php echo str_cut($v['title'], 81);?></a></p>
		<?php } ?>
      </ul>
      <ul class="sidenews">
        <?php $tag = yzm_base::load_sys_class('yzm_tag');if(method_exists($tag, 'lists')) {$data = $tag->lists(array('field'=>'title,url,thumb,inputtime','modelid'=>'1','order'=>'RAND()','limit'=>'4',));}?>
			<?php if(is_array($data)) foreach($data as $v) { ?>	
        <li> <i><img src="<?php if(!empty($v['thumb'])) { ?><?php echo $v['thumb'];?><?php } else { ?><?php echo STATIC_URL;?>images/nopic.jpg<?php } ?>" alt="<?php echo $v['title'];?>"></i>
          <p><a href="<?php echo $v['url'];?>"><?php echo str_cut($v['title'], 81);?></a></p>
          <span><?php echo date('Y-m-d H:i:s',$v['inputtime']);?></span> </li>
      <?php } ?>
      </ul>
    </div> -->
    <div class="tuijian">
      <h2 class="hometitle">点击排行</h2>
      <ul class="tjpic">
       <?php $tag = yzm_base::load_sys_class('yzm_tag');if(method_exists($tag, 'lists')) {$data = $tag->lists(array('field'=>'title,url,thumb,inputtime','modelid'=>'1','order'=>'RAND()','limit'=>'1',));}?>
			<?php if(is_array($data)) foreach($data as $v) { ?>	
        <i><img src="<?php if(!empty($v['thumb'])) { ?><?php echo $v['thumb'];?><?php } else { ?><?php echo STATIC_URL;?>images/nopic.jpg<?php } ?>" alt="<?php echo $v['title'];?>"></i>
        <p><a href="<?php echo $v['url'];?>"><?php echo str_cut($v['title'], 81);?></a></p>
		<?php } ?>
      </ul>
      <ul class="sidenews">
        <?php $tag = yzm_base::load_sys_class('yzm_tag');if(method_exists($tag, 'hits')) {$data = $tag->hits(array('field'=>'title,thumb,inputtime,nickname,url,description,catid','modelid'=>'1','limit'=>'4',));}?>
		<?php if(is_array($data)) foreach($data as $v) { ?>	
        <li> <i><img src="<?php if(!empty($v['thumb'])) { ?><?php echo $v['thumb'];?><?php } else { ?><?php echo STATIC_URL;?>images/nopic.jpg<?php } ?>" alt="<?php echo $v['title'];?>"></i>
          <p><a href="<?php echo $v['url'];?>"><?php echo str_cut($v['title'], 81);?></a></p>
          <span><?php echo date('Y-m-d H:i:s',$v['inputtime']);?></span> </li>
		<?php } ?>
      </ul>
    </div>
    <div class="cloud">
      <h2 class="hometitle">标签云</h2>
      <ul>
       <?php $tag = yzm_base::load_sys_class('yzm_tag');if(method_exists($tag, 'tag')) {$data = $tag->tag(array('field'=>'id,tag,total','limit'=>'20',));}?>
    	  <?php if(is_array($data)) foreach($data as $v) { ?>
            <a href="<?php echo U('search/index/tag',array('id'=>$v['id']));?>" target="_blank"><?php echo $v['tag'];?></a>
        <?php } ?> 
      </ul>
    </div>
    <div class="links">
      <h2 class="hometitle">友情链接</h2>
	  
      <ul>
      <?php $tag = yzm_base::load_sys_class('yzm_tag');if(method_exists($tag, 'link')) {$data = $tag->link(array('field'=>'url,logo,name','limit'=>'12',));}?>
      <?php if(is_array($data)) foreach($data as $v) { ?>	
      		<li><a href="<?php echo $v['url'];?>" target="_blank"><?php echo $v['name'];?></a></li>
      <?php } ?>
      </ul>
    </div>
  </div>
</article>
<?php include template("index","footer"); ?> 