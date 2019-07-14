<?php
/**
 * 会员中心内容操作类
 * @author           袁志蒙  
 * @license          http://www.yzmcms.com
 * @lastmodify       2018-03-23
 */
 
defined('IN_YZMPHP') or exit('Access Denied'); 
yzm_base::load_controller('common', 'member', 0);
yzm_base::load_sys_class('page','',0);

require_once 'third/PHPExcel-1.8/PHPExcel.php';
require_once 'third/baixingzhidao.php';

class member_content extends common{
	
	function __construct() {
		parent::__construct();
	}

	
	/**
	 * 在线投稿
	 */	
	public function init(){ 
		$memberinfo = $this->memberinfo;
		extract($memberinfo);
		
		$this->_check_group_auth($groupid); 
		yzm_base::load_sys_class('form','',0);

		$catid = isset($_GET['catid']) ? intval($_GET['catid']) : 0;
		$modelid = !$catid ? 1 : get_category($catid, 'modelid');
		if(!$modelid)  showmsg(L('illegal_operation'), 'stop');
		
		$category_data = select_category('catid', $catid, '≡ 请选择栏目 ≡', 1, 'onchange="javascript:change_model(this.value);"');
		$fieldstr = $this->_get_model_str($modelid);
		$field_check = $this->_get_model_str($modelid, true);

		include template('member', 'publish');
	}

    /**
     * 批量投稿初始化
     */
    public function initbatch(){
        $memberinfo = $this->memberinfo;
        extract($memberinfo);

        $this->_check_group_auth($groupid);
        yzm_base::load_sys_class('form','',0);

        $catid = isset($_GET['catid']) ? intval($_GET['catid']) : 0;
        $modelid = !$catid ? 1 : get_category($catid, 'modelid');
        if(!$modelid)  showmsg(L('illegal_operation'), 'stop');

        $category_data = select_category('catid', $catid, '≡ 请选择栏目 ≡', 1, 'onchange="javascript:change_model(this.value);"');
        $fieldstr = $this->_get_model_str($modelid);
        $field_check = $this->_get_model_str($modelid, true);

        include template('member', 'batch');
    }

    /**
     * 批量投稿执行
     */
    public function batch(){
        $memberinfo = $this->memberinfo;
        extract($memberinfo);

        $this->_check_group_auth($groupid);
        yzm_base::load_sys_class('form','',0);

        $catid = isset($_POST['catid']) ? intval($_POST['catid']) : 0;
        $inputfile = $_POST['attach'];
        $modelid = !$catid ? 1 : get_category($catid, 'modelid');
        if(!$modelid)  showmsg(L('illegal_operation'), 'stop');

        $res = $this->publish_batch($catid,YZMPHP_PATH.$inputfile);
        $total = $res['total'];
        $succ = $res['succ'];

        $download = SITE_URL.$inputfile;

        //逻辑一 读取excel； 预览（页面显示，数据放在json中）后提交，最后批量写入
        //逻辑二 读取excel, 写入到草稿，然后到草稿去发布
        include template('member', 'batch_items');
    }

    public function publish_batch($catid, $inputFileName){
        try {
            $inputFileType = PHPExcel_IOFactory::identify($inputFileName);
            $objReader = PHPExcel_IOFactory::createReader($inputFileType);
            $objPHPExcel = $objReader->load($inputFileName);
        } catch(Exception $e) {
            die("加载文件发生错误：".pathinfo($inputFileName,PATHINFO_BASENAME).":".$e->getMessage());
        }

        // 确定要读取的sheet，什么是sheet，看excel的右下角，真的不懂去百度吧
        $sheet = $objPHPExcel->getSheet(0);
        $highestRow = $sheet->getHighestRow();
        $highestColumn = $sheet->getHighestColumn();
        $urlRow = hex2bin(bin2hex($highestColumn) + 1);
        $bxurlRow = hex2bin(bin2hex($highestColumn) + 2);

        $memberinfo = $this->memberinfo;
        if ($memberinfo['point'] < $highestRow ){
            showmsg('积分不足, 请移步到充值中心！', U('/member/member_pay/pay'));
            return;
        }

        // 获取一行的数据, 这里是标题
        $rowData = $sheet->rangeToArray('A1' . ':' . $highestColumn .'1', NULL, TRUE, FALSE);
        for ($idx = 1; $idx <= $highestColumn; $idx++){

        }

        //略过第一条, 然后计算发布成功的条目数
        $modelid = get_category($catid, 'modelid');
        $content_tabname = D(get_model($modelid));
        $succ = 0;
        $max_tmp_user_id = get_config('tmpuser');
        for ($row = 2; $row <= $highestRow; $row++){
            // Read a row of data into an array
            $rowData = $sheet->rangeToArray('A' . $row . ':' . $highestColumn . $row, NULL, TRUE, FALSE);
            //这里得到的rowData都是一行的数据，得到数据后提交
            $data = [];
            $data['title'] = $rowData[0][0];
            $data['description'] = $rowData[0][1];
            $data['content'] = $rowData[0][1];
            $data['catid'] = $catid;
            $data['copyfrom'] = '原创';
            $data['thumb'] = '';

            $bxid = postBaixingQA($data['title'], $data['description'], array_slice($rowData[0],2),'');
            $bxinfo = "发布到百姓知道失败，存在违禁词".$bxid;
            $bxurl = '';
            $charge = False;

            if ($bxid) {
                $bxinfo = "发布到百姓知道成功，id为" . $bxid;
                $bxurl = 'http://zhidao.baixing.com/question/' . $bxid . '.html';
                $charge = True;
            }

            $data['urls'] = $bxurl;
            $id = $this->publish_item($data);
            $this->_adopt($content_tabname, $catid, $id, $charge);
            $url = $this->get_url(true, $catid, $id);

            for($i=2; $i<sizeof($rowData[0]); $i++){
                if ( strlen($rowData[0][$i]) > 10 ) { //判断评论长度要超过5个字
                    $cid = $this->post_comment($id, $catid, $modelid, rand(1,$max_tmp_user_id), $rowData[0][$i]);
                }
            }

            if ($id >0) {
                $succ = $succ + 1;
            }

            //写入狐狸到excel中
            $cell = $urlRow.$row;
            $sheet->setCellValue($cell, $url);

            //写入bx知道到excel中
            $bxcell = $bxurlRow.$row;
            $sheet->setCellValue($bxcell, $bxurl);
        }

        $arr = [];
        $arr['total'] = $highestRow - 1;
        $arr['succ'] = $succ;
        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, $inputFileType);
        $objWriter->save($inputFileName);
        return $arr;
    }

    public function baidu_push($urls){
        if(!empty($urls)){
            $baidu_push_token = get_config('baidu_push_token');
            if(!$baidu_push_token) showmsg('token值为空，请到系统设置中配置！', 'stop');
            $api_url = 'http://data.zz.baidu.com/urls?site='.HTTP_HOST.'&token='.$baidu_push_token;
            $ch = curl_init();
            $options =  array(
                CURLOPT_URL => $api_url,
                CURLOPT_POST => true,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_POSTFIELDS => implode("\n", $urls),
                CURLOPT_HTTPHEADER => array('Content-Type: text/plain'),
            );
            curl_setopt_array($ch, $options);
            $result = curl_exec($ch);
            curl_close($ch);
            $result = json_decode($result, true);
            if(isset($result['success'])){
                showmsg('发布并且成功推送'.$result['success'].'条URL地址到百度！', '', 2);
            }else{
                showmsg('推送失败，错误码：'.$result['error'], 'stop');
            }
        }
    }

	/**
	 * 在线投稿-发布稿件
	 */	
	public function publish(){
		$memberinfo = $this->memberinfo;
		extract($memberinfo);

		if ($memberinfo['point'] <=0 ){
            showmsg('积分不足, 请移步到充值中心！', U('/member/member_pay/pay'));
            return;
        }

		$groupinfo = $this->_check_group_auth($groupid);
        $catid = intval($_POST['catid']);
        $modelid = get_category($catid, 'modelid');
        $content_tabname = D(get_model($modelid));

		//会员中心可发布的字段
		$fields = array('title','copyfrom','catid','thumb','description','content');

		if(isset($_POST['dosubmit'])) {
		    //在问答的时候，没有content数据
            $_POST['content'] = isset($_POST['content']) ? $_POST['content'] : "";

            //会员权限-投稿免审核
            $is_adopt = strpos($groupinfo['authority'], '4') === false ? 0 : 1;

            $bxid = postBaixingQA($_POST['title'], $_POST['description'], array($_POST['content']),'');
            $bxinfo = "发布到百姓知道失败，存在违禁词".$bxid;
            $bxurl ='';
            $charge = False;

            if ($bxid) {
                $bxinfo = "发布到百姓知道成功，id为".$bxid;
                $bxurl = 'http://zhidao.baixing.com/question/'.$bxid.'.html';
                $charge = True;
            }

            $_POST['urls'] = $bxurl;

            $id = $this->publish_item($_POST);

            if (!$is_adopt) {
                showmsg('发布成功，等待管理员审核！'.$bxinfo, U('not_pass'));
            } else {
                $this->_adopt($content_tabname, $catid, $id);
                showmsg('发布成功，内容已通过审核！'.$bxinfo, U('pass'));
            }
        }
	}
	
	
	/**
	 * 编辑稿件
	 */	
	public function edit_through(){
		$memberinfo = $this->memberinfo;
		extract($memberinfo);
		
		$groupinfo = $this->_check_group_auth($groupid);	
		yzm_base::load_sys_class('form','',0);
		
		//会员中心可发布的字段
		$fields = array('title','copyfrom','catid','thumb','description','content');
		
		//可以根据catid获取model模型，来加载不同模板
		$catid = isset($_GET['catid']) ? intval($_GET['catid']) : showmsg(L('lose_parameters'), 'stop');  
		$id = isset($_GET['id']) ? intval($_GET['id']) : showmsg(L('lose_parameters'), 'stop');
	
		if(isset($_POST['dosubmit'])) {
			
			$_POST['catid'] = intval($_POST['catid']);
			
			//判断栏目是否禁止投稿
			$data = D('category')->field('member_publish')->where(array('catid'=>$_POST['catid']))->find();
			if(!$data['member_publish']) showmsg('该栏目不允许在线投稿！', 'stop');
			
			//根据POST传回的参数再次判断一下modelid（必须）
			$modelid = get_category($_POST['catid'], 'modelid');
			if(!$modelid){
				showmsg(L('operation_failure'), 'stop');
			}
			$content_tabname = D(get_model($modelid));			
			
			$member_content = D('member_content');
			$data = $member_content->field('username,status')->where(array('checkid' =>$modelid.'_'.$id))->find();
			//只能编辑自己发布的内容
			if(!$data || $data['username'] != $username){
				showmsg(L('illegal_operation'), 'stop');
			}
			
			$field_check = $this->_get_model_str($modelid, true);
			foreach($field_check as $k => $v){
				if($v['isrequired']){
					if(empty($_POST[$k])) showmsg($v['errortips']);
				}
			}
			$fields = array_merge($fields, array_keys($field_check));
			$notfilter_field = $this->_get_notfilter_field($modelid);
			
			foreach($_POST as $_k=>$_v) {
				if(!in_array($_k, $fields)){
					unset($_POST[$_k]);
					continue;
				}
				if(in_array($_k, $notfilter_field)) {
					$_POST[$_k] = remove_xss(strip_tags($_v, '<p><a><br><img><ul><li><div>'));
				}else{
					$_POST[$_k] = !is_array($_POST[$_k]) ? new_html_special_chars(trim_script($_v)) : $this->_content_dispose($_v);
				}
			}
			
			//会员权限-投稿免审核
			$is_adopt = strpos($groupinfo['authority'], '4') === false ? 0 : 1;

			$_POST['seo_title'] = $_POST['title'].'_'.get_config('site_name');
			$_POST['description'] = empty($_POST['description']) ? str_cut(strip_tags($_POST['content']),200) : $_POST['description'];
			$_POST['updatetime'] = SYS_TIME;
			$_POST['status'] = $is_adopt;	
			
			if($content_tabname->update($_POST, array('id' => $id))){
				$member_content->update($_POST, array('checkid' =>$modelid.'_'.$id, 'docid' =>$id));	//更新会员内容表
				if(!$is_adopt){
					showmsg('操作成功，等待管理员审核！', U('not_pass'));
				}else{
					showmsg('操作成功，内容已通过审核！', U('pass'));
				}
			}

		}
		
		$modelid = get_category($catid, 'modelid');
		if(!$modelid)  showmsg(L('lose_parameters'), 'stop');  
		$content_tabname = D(get_model($modelid));
		
		$data = $content_tabname->where(array('id' => $id))->find(); 
		
		$fieldstr = $this->_get_model_str($modelid, false, $data);
		$field_check = $this->_get_model_str($modelid, true);
		include template('member', 'edit_through');
	}

	
	
	//已通过的稿件
	public function pass(){
		$memberinfo = $this->memberinfo;
		extract($memberinfo);
		
		$member_content = D('member_content');
		$total = $member_content->where(array('userid' =>$userid,'status' =>1))->total();
		$page = new page($total, 10);
		$res = $member_content->alias('a')->field('a.checkid,a.catid,a.title,a.inputtime,a.updatetime,b.urls')->join('yzmcms_article b ON a.docid=b.id')->where(array('a.userid' =>$userid,'a.status' =>1))->order('updatetime DESC')->limit($page->limit())->select();
		$data = array();
		foreach($res as $val) {
			list($val['modelid'], $val['id']) = explode('_', $val['checkid']);
			$val['url'] = SITE_URL.'index.php?m=index&c=index&a=show&catid='.$val['catid'].'&id='.$val['id'];
			$data[] = $val;
		}
		$pages = '<span class="pageinfo">共'.$total.'条记录</span>'.$page->getfull();
		include template('member', 'publish_through');
	}



	//未通过的稿件
	public function not_pass(){
		$memberinfo = $this->memberinfo;
		extract($memberinfo);
		
		$member_content = D('member_content');
		$total = $member_content->where(array('userid' =>$userid,'status' =>0))->total();
		$page = new page($total, 10);
		$res = $member_content->field('checkid,catid,title,inputtime,updatetime,status')->where(array('userid' =>$userid,'status!=' =>1))->order('updatetime DESC')->limit($page->limit())->select();
		$data = array();
		foreach($res as $val) {
			list($val['modelid'], $val['id']) = explode('_', $val['checkid']);
			$data[] = $val;
		}
		$pages = '<span class="pageinfo">共'.$total.'条记录</span>'.$page->getfull();
		include template('member', 'publish_not_through');
	}
	
	
	
	//删除未通过的稿件
	public function del(){
		$memberinfo = $this->memberinfo;
		extract($memberinfo);
		
		$catid = isset($_GET['catid']) ? intval($_GET['catid']) : showmsg(L('lose_parameters'), 'stop');
		$id = isset($_GET['id']) ? intval($_GET['id']) : showmsg(L('lose_parameters'), 'stop');
		
		$modelid = get_category($catid, 'modelid');
		if(!$modelid){
			showmsg(L('operation_failure'), 'stop');
		}
		$content_tabname = D(get_model($modelid));
		$member_content = D('member_content');
		$data = $member_content->field('username,status')->where(array('checkid' =>$modelid.'_'.$id))->find();
		//只能删除自己的 且 未通过审核的
		if($data && $data['username'] == $username && $data['status'] != 1){
			$member_content->delete(array('checkid' =>$modelid.'_'.$id));	//删除会员内容表
			$content_tabname->delete(array('id' => $id));	 //删除model内容表
		}
		showmsg(L('operation_success'));
	}
	
	
	
	//收藏夹
	public function favorite(){
		$memberinfo = $this->memberinfo;
		extract($memberinfo);
		
		$favorite = D('favorite');
		$total = $favorite->where(array('userid' =>$userid))->total();
		$page = new page($total, 10);
		$data = $favorite->where(array('userid' =>$userid))->order('id DESC')->limit($page->limit())->select();
		$pages = '<span class="pageinfo">共'.$total.'条记录</span>'.$page->getfull();
		include template('member', 'favorite');
	}
	
	
	
	//删除收藏夹
	public function favorite_del(){
		$memberinfo = $this->memberinfo;
		extract($memberinfo);
		
		if(!isset($_POST['fx'])) showmsg('您没有选择项目！');
		if(!is_array($_POST['fx'])) showmsg(L('illegal_operation'), 'stop');
		$favorite = D('favorite');
		foreach($_POST['fx'] as $v){
			$favorite->delete(array('id' => intval($v), 'userid' => $userid));
		}
		showmsg(L('operation_success'));
	}

	
	//检查会员组权限
	private function _check_group_auth($groupid){
		$groupinfo = get_groupinfo($groupid);
		if(strpos($groupinfo['authority'], '3') === false) 
		showmsg('你没有权限投稿，请升级会员组！', 'stop'); 
		return $groupinfo;
	}
	
	
	//获取不同模型获取HTML表单
	private function _get_model_str($modelid, $field = false, $data = array()) {
		$modelinfo = getcache($modelid.'_model');
		if($modelinfo === false){
			$modelinfo = D('model_field')->where(array('modelid' => $modelid, 'disabled' => 0))->order('listorder ASC')->select();
			setcache($modelid.'_model', $modelinfo);
		}
		
		$fields = $fieldstr = array();
		foreach($modelinfo as $val){
			if($val['isadd'] == 0) continue;
			$fieldtype = $val['fieldtype'];
			if($data){
				$val['defaultvalue'] = isset($data[$val['field']]) ? $data[$val['field']] : '';
			}
			$setting = $val['setting'] ? string2array($val['setting']) : 0;
			$required = $val['isrequired'] ? '<span class="red">*</span>' : '';
			$fieldstr[] = '<td>'.$val['name'].'：</td><td>'.form::$fieldtype($val['field'], $val['defaultvalue'], $setting).$required.'</td>';	
			$fields[$val['field']] = $val['isrequired'] ? array('isrequired'=>1, 'fieldtype'=>$fieldtype, 'errortips'=>$val['errortips'] ? $val['errortips'] : $val['name'].'不能为空！') : array('isrequired'=>0);
		}
		
		return $field ? $fields : $fieldstr;
	}
	

	/**
	 * 获取模型非过滤字段
	 */	
	private function _get_notfilter_field($modelid) {
		$arr = array('content');
		$data = D('model_field')->field('field,fieldtype')->where(array('modelid' => $modelid))->select();
		foreach($data as $val){
			if($val['fieldtype'] == 'editor' || $val['fieldtype'] == 'editor_mini') $arr[] = $val['field'];
		}

		return $arr;
	} 
	
	
	/**
	 * 内容处理
	 * @param $content 
	 */	
	private function _content_dispose($content) {
		$is_array = false;
		foreach($content as $val){
			if(is_array($val)) $is_array = true;
			break;
		}
		if(!$is_array) return safe_replace(implode(',', $content));
		
		//这里认为是多文件上传
		$arr = array();
		foreach($content['url'] as $key => $val){
			$arr[$key]['url'] = safe_replace($val);
			$arr[$key]['alt'] = safe_replace($content['alt'][$key]);
		}		
		return array2string($arr);
	}

    public function publish_item($data){
        $memberinfo = $this->memberinfo;
        extract($memberinfo);

        $groupinfo = $this->_check_group_auth($groupid);
        //会员中心可发布的字段
        $fields = array('title','copyfrom','catid','thumb','description','content', 'urls');

        $catid = intval($data['catid']);

        //判断栏目是否禁止投稿
        $opendata = D('category')->field('member_publish')->where(array('catid'=>$catid))->find();
        if(!$opendata['member_publish']) showmsg(L('illegal_operation'), 'stop');

        //支持不同栏目自动实例化不同的model
        $modelid = get_category($catid, 'modelid');

        yzm_base::load_sys_class('form','',0);
        $field_check = $this->_get_model_str($modelid, true);
        foreach($field_check as $k => $v){
            if($v['isrequired']){
                if(empty($data[$k])) showmsg($v['errortips']);
            }
        }

        $fields = array_merge($fields, array_keys($field_check));
        $notfilter_field = $this->_get_notfilter_field($modelid);

        foreach($data as $_k=>$_v) {
            if(!in_array($_k, $fields)){
                unset($data[$_k]);
                continue;
            }
            if(in_array($_k, $notfilter_field)) {
                $data[$_k] = remove_xss(strip_tags($_v, '<p><a><br><img><ul><li><div><strong>'));
            }else{
                $data[$_k] = !is_array($data[$_k]) ? new_html_special_chars(trim_script($_v)) : $this->_content_dispose($_v);
            }
        }

        //会员权限-投稿免审核
        $is_adopt = strpos($groupinfo['authority'], '4') === false ? 0 : 1;

        $data['seo_title'] = $data['title'].'_'.get_config('site_name');
        $data['system'] = '0';
        $data['status'] = $is_adopt;
        $data['listorder'] = '10';		//为内容置顶做准备
        $data['description'] = empty($data['description']) ? str_cut(strip_tags($data['content']),200) : $data['description'];
        $data['inputtime'] = SYS_TIME;
        $data['updatetime'] = SYS_TIME;
        $data['catid'] = $catid;
        $data['userid'] = $userid;
        $data['username'] = $username;
        $data['nickname'] = $nickname;

        $content_tabname = D(get_model($modelid));
        $id = $content_tabname->insert($data);

        //发布到用户内容列表中
        $data['checkid'] = $modelid.'_'.$id;
        $data['docid'] = $id;
        D('member_content')->insert($data);

        return $id;
    }

    public function post_comment($docid, $catid, $modelid, $userid, $content){
        $fields = array('title','copyfrom','catid','thumb','description','content');

        $data['userid'] = $userid;
        $data['id'] = $docid;

        $db = D('member_detail');
        $result = $db->field('userid, nickname')->where(array("userid"=>$userid))->select();
        $data['username'] = $result[0]['nickname'];

        $data['catid'] = $catid;
        $data['modelid'] = $modelid;
        $data['content'] = $content;
        $data['userpic'] = $userid ? get_memberavatar($userid) : '';
        $data['inputtime'] = SYS_TIME;
        $data['ip'] = "127.0.0.1";
        $data['reply'] = 0;
        $data['commentid'] = $modelid. '_' . $catid . '_' . $docid;
        $data['status'] = 1;
        $data['total'] = 1;
        $id = D('comment')->insert($data); //评论表
        $comment_data = D('comment_data');
        if($comment_data->where(array('commentid' => $data['commentid']))->find()){
            $comment_data->update('`total`=`total`+1', array('commentid' => $data['commentid']));
        }else{
            $comment_data->insert($data, false, false);
        }

        return $id;
    }
	
	
	/**
	 * 内容通过审核
	 * @param $content_tabname 
	 * @param $catid 
	 * @param $id 
	 */		
	private function _adopt($content_tabname, $catid, $id, $charge=True){
		if(get_config('url_rule')){
			$catinfo = get_category($catid);
			$url = URL_MODEL == 1 ? SITE_URL.'index.php?s=/'.$catinfo['catdir'].'/'.$id.C('url_html_suffix') : SITE_URL.$catinfo['catdir'].'/'.$id.C('url_html_suffix');
		}else{  
			$url = U('index/index/show',array('catid'=>$catid,'id'=>$id));
		}

		$content_tabname->update(array('url' => $url), array('id' => $id));
        //$this->baidu_push(array($url));
		
		//投稿奖励积分和经验
        if($charge) {
            $publish_point = get_config('publish_point');
		    if ($publish_point > 0) {
                M('point')->point_add(1, $publish_point, 2, $this->memberinfo['userid'], $this->memberinfo['username'], $this->memberinfo['experience'], $catid . '_' . $id);
            }
        }
	}

    /**
     * 获取内容页URL
     */
    private function get_url($url_rule, $catid, $id){

        //如果系统设置是伪静态模式
        if($url_rule){
            $catinfo = get_category($catid);
            $url = URL_MODEL == 1 ? SITE_URL.'index.php?s=/'.$catinfo['catdir'].'/'.$id.C('url_html_suffix') : SITE_URL.$catinfo['catdir'].'/'.$id.C('url_html_suffix');
        }else{
            $url = U('index/index/show',array('catid'=>$catid,'id'=>$id));
        }
        return $url;
    }
}