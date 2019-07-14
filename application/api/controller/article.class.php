<?php
/**
 * 系统API接口类
 * @author           邢皖甲  
 * @license          http://www.yzmcms.com
 * @lastmodify       2018-12-29
 */

class article{

	public function qalist(){
		$catid = isset($_GET['catid']) ? intval($_GET['catid']) : 0;
		if(!$catid) return_json(array('errno' => -1, 'errmsg' => '缺少catid参数'));
		$catinfo = get_category($catid);
		if(!$catinfo) return_json(array('errno' => -1, 'errmsg' => 'catid不存在'));
		extract($catinfo);

		//单页面
        $type = 0;
		if($type == 0){
			$db = D('article');
			$result = $db->field('id,title,url,thumb,flag,description')->select();
	
			return_json(array('errno' => 0, 'errmsg' => '', 'data' => $result));
		}
		return_json(array('errno' => -1, 'errmsg' => '内容错误', 'type' => $type));
	}

	public function post(){

        $fields = array('title','copyfrom','catid','thumb','description','content');
        $req = json_decode(file_get_contents("php://input"), true);
        if($req) {
            //$member_content = new member_content();
            $catid = intval($req['catid']);
            $userid = intval($req['userid']);

            //判断栏目是否禁止投稿
            $data = D('category')->field('member_publish')->where(array('catid' => $catid))->find();
            if (!$data['member_publish']) return_json(array('errno' => -1, 'errmsg' => '内容错误', 'type' => "栏目禁止投稿"));

            //支持不同栏目自动实例化不同的model
            $modelid = get_category($catid, 'modelid');

            //会员权限-投稿免审核
            $is_adopt = 1;

            $data['title'] = $req['title'];
            $data['seo_title'] = $req['title'] . '_' . get_config('site_name');
            $data['system'] = '0';
            $data['status'] = $is_adopt;
            $data['listorder'] = '10';        //为内容置顶做准备
            $data['description'] = empty($req['description']) ? str_cut(strip_tags($req['content']), 200) : $req['description'];
            $data['inputtime'] = SYS_TIME;
            $data['updatetime'] = SYS_TIME;
            $data['catid'] = $catid;
            $data['content'] = $catid;
            $data['userid'] = $userid;

            $db = D('member');
            $result = $db->field('userid, username')->where(array("userid"=>$userid))->select();
            $data['username'] = $result[0]['username'];

            $db = D('member_detail');
            $result = $db->field('userid, nickname')->where(array("userid"=>$userid))->select();
            $data['nickname'] = $result[0]['nickname'];

            $content_tabname = D(get_model($modelid));
            $id = $content_tabname->insert($data);

            //发布到用户内容列表中
            $data['checkid'] = $modelid . '_' . $id;
            $data['docid'] = $id;
            D('member_content')->insert($data);

            $url = $this->get_url(true, $data['catid'], $id);
            $db = D('article');
            $result = $db->update(array('url'=>$url),array('id'=>$id));

            if (!$is_adopt) {
                return_json(array('errno' => 0, 'errmsg' => '发布成功,等待管理员审核！', 'data' => $id));
            } else {
                //$this->_adopt($content_tabname, $catid, $id);
                return_json(array('errno' => 0, 'errmsg' => '发布成功，内容已通过审核！', 'data' => $id));
            }
        }
        return_json(array('errno' => 0, 'errmsg' => '参数错误，非json格式', 'data' => ""));
    }

    public function postanwsers(){
        $fields = array('title','copyfrom','catid','thumb','description','content');
        $req = json_decode(file_get_contents("php://input"), true);
        if($req) {
            $userid = $req['userid'];
            $data['userid'] = $req['userid'];
            $data['id'] = isset($req['id']) ? intval($req['id']) : 0;

            $db = D('member_detail');
            $result = $db->field('userid, nickname')->where(array("userid"=>$userid))->select();
            $data['username'] = $result[0]['nickname'];

            $db = D('article');
            $result = $db->field('id, catid')->where(array("id"=>$data['id']))->select();
            $data['catid'] = $result[0]['catid'];

            $db = D('category');
            $result = $db->field('catid, modelid')->where(array("catid"=>$data['catid']))->select();
            $data['modelid'] = $result[0]['modelid'];

            $data['content'] = $req['content'];
            $data['userpic'] = $userid ? get_memberavatar($userid) : '';
            $data['inputtime'] = SYS_TIME;
            $data['ip'] = "127.0.0.1";
            $data['reply'] = isset($req['reply']) ? intval($req['reply']) : 0;
            $data['commentid'] = $data['modelid'] . '_' . $data['catid'] . '_' . $data['id'];
            $data['status'] = 1;
            $data['total'] = 1;
            $id = D('comment')->insert($data); //评论表
            $comment_data = D('comment_data');
            if($comment_data->where(array('commentid' => $data['commentid']))->find()){
                $comment_data->update('`total`=`total`+1', array('commentid' => $data['commentid']));
            }else{
                $comment_data->insert($_POST, false, false);
            }

            return_json(array('errno' => 0, 'errmsg' => '发布成功', 'data' => $data));
        }
        return_json(array('errno' => 0, 'errmsg' => '参数错误，非json格式', 'data' => ""));
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