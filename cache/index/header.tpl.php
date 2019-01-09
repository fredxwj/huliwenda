<?php defined('IN_YZMPHP') or exit('No permission resources.'); ?><header> 
  <!--menu begin-->
  <div class="menu">
    <nav class="nav" id="topnav">
      <li><a href="<?php echo SITE_URL;?>"><?php echo $site['site_name'];?></a> </li>
<?php $tag = yzm_base::load_sys_class('yzm_tag');if(method_exists($tag, 'nav')) {$data = $tag->nav(array('field'=>'catid,catname,arrchildid,pclink,type','where'=>"parentid=0",'limit'=>'20',));}?>
	<?php if(is_array($data)) foreach($data as $v) { ?>
      <li><a<?php if(isset($catid) && $v['catid']==$catid) { ?> class="current" <?php } ?> href="<?php echo $v['pclink'];?>" <?php if($v['type']==2) { ?> target="_blank" <?php } ?>><?php echo $v['catname'];?></a>
			<!-- 这里是二级栏目的循环，不需要的可以删除，代码开始 -->
			<?php if($v['arrchildid']!=$v['catid']) { ?> 
			<?php $r = get_childcat($v['catid']);?>
        <ul class="sub-nav">
          <?php if(is_array($r)) foreach($r as $v) { ?>
				<li><a href="<?php echo $v['pclink'];?>"><?php echo $v['catname'];?></a></li>
				<?php } ?>	
        </ul>
     <?php } ?>
			<!-- 这里是二级栏目的循环，不需要的可以删除，代码结束 -->
		</li>		
	<?php } ?>	
      <!--search begin-->
      <!-- <div id="search_bar" class="search_bar">
        <form  id="searchform" action="<?php echo SITE_URL;?>index.php" method="get" name="searchform">
          <input class="input" placeholder="想搜点什么呢..." type="text" name="q" required="required" id="keyboard">
          <input type="hidden" name="m" value="search" />
          <input type="hidden" name="c" value="index" />
          <input type="hidden" name="a" value="init" />
          <input type="hidden" name="modelid" value="1" id="modelid" />
          <span class="search_ico"></span>
        </form>
  		  <i class="fa"></i>
      </div> -->
      <!--search end-->  <!--mnav end-->
        <!--mini登陆条-->
        <div id="head_login">
            <div class="w1000">
                <div id="mini">
                    <?php if(intval(get_cookie('_userid'))==0) { ?>
                    <a href="<?php echo U('member/index/register');?>" target="_blank">注册</a> <a href="<?php echo U('member/index/login');?>"  target="_blank">登录</a>
                    <?php } else { ?>
                    你好：<?php echo safe_replace(get_cookie('_username'));?>，<a href="<?php echo U('member/index/init');?>">会员中心</a> <a href="<?php echo U('member/index/logout');?>">退出</a>
                    <?php } ?>
                </div>
            </div>
        </div>
    </nav>
  </div>
  <!--menu end--> 
  <!--mnav begin-->
  <div id="mnav">
    <h2><a href="<?php echo SITE_URL;?>" class="mlogo"><?php echo $site['site_name'];?></a><span class="navicon"></span></h2>
    <dl class="list_dl">
      <dt class="list_dt"> <a class="current" href="<?php echo SITE_URL;?>">网站首页</a> </dt>
<?php $tag = yzm_base::load_sys_class('yzm_tag');if(method_exists($tag, 'nav')) {$data = $tag->nav(array('field'=>'catid,catname,arrchildid,pclink,type','where'=>"parentid=0",'limit'=>'20',));}?>
	<?php if(is_array($data)) foreach($data as $v) { ?>
      <dt class="list_dt"> <a href="<?php echo $v['pclink'];?>" <?php if($v['type']==2) { ?> target="_blank" <?php } ?>><?php echo $v['catname'];?></a> 
	  </dt>
<!-- 这里是二级栏目的循环，不需要的可以删除，代码开始 -->
			<?php if($v['arrchildid']!=$v['catid']) { ?> 
      			<?php $r = get_childcat($v['catid']);?>

            <dd class="list_dd">
              <ul>
      		    <?php if(is_array($r)) foreach($r as $v) { ?>
      				<li><a href="<?php echo $v['pclink'];?>"><?php echo $v['catname'];?></a></li>
      				<?php } ?>
              </ul>
            </dd>
      <?php } ?>
			<!-- 这里是二级栏目的循环，不需要的可以删除，代码结束 -->
<?php } ?>	

    </dl>
  </div>

</header>