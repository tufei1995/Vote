<?php
/**
 * 湖北省高校网络文化建设成果评选自动投票程序基于PHP
 * @author 随风飘扬
 * @var
 */
$vote = new Vote();
$vote->vote();

Class Vote {

    /**
     * 年份，用于设置当前投票年份
     * @var
     */
	protected static $year = date('Y')-1;

    /**
     * 投票对象
     * @var
     */
	protected static $id = 185;

    /**
     * 操作网址列表
     * @var array
     */
	protected static $urls = array(
		'getCookie' => 'http://218.199.196.196/2014culture/Services/Users/UserLogin.ashx',
		'register' => 'http://218.199.196.196/' . static::$year . 'culture/Services/Users/UserReg.ashx';
		'login' => 'http://218.199.196.196/' . static::$year . 'culture/Services/Users/UserLogin.ashx';
		'comment' => 'http://218.199.196.196/' . static::$year . 'culture/Services/Scores/ScoreAdd.ashx';
	);

    /**
     * 评论列表.
     * 需要添加评论即修改此数组.
     * @var array
     */
	protected static $content = array(
		'评论1',
		'评论2',
		'评论3',
		'评论4',
		'评论5',
		'评论6'
	);

	/**
     * 姓列表，用于生成名字.
     * @var array
     */
	protected static $first = array(
		'赵','钱','孙','李','周','吴','郑','王'
	);

    /**
     * 名列表，用于生成名字.
     * @var array
     */
	protected static $last = array(
		'伟','飞','敏','冰','彦磊','嘉伟','诚','振','震'
	);

    /**
     * cookie，用于后续评论.
     *
     * @var
     */
	protected static $cookie;

	public function vote(){
		$rel = $this->request(static::$urls['getCookie'], '', true);
		preg_match_all("/ASP.NET_SessionId=(.*); pa/i", $rel, $session);
		$this->cookie = $session[1][0];
		$data = $this->register();
		$this->login($data);
		$this->comment();
	}

    /**
     * 随机生成账号信息进行注册
     * @return array
     */
	private function register(){
		$data['cardid'] = $this->createID();
		$data['realname'] = $this->createName();
		$data['email'] = $this->createEmail();
		$data['name'] = substr(md5(rand(2222,9999)), rand(0,20), rand(5,8));
		$data['password0'] = rand(111111111,2000000000);
		$data['password1'] = $data['password0'];
		$rel = $this->request(static::$urls['register'], $data);
		$rel = json_decode($rel, true);
		if ($rel['code'] == 1) {
			return $data;
		}
		else {
			exit('注册失败');
		}
	}

    /**
     *进行登录，使cookie可用于评论。
     */
	private function login($data){
		$login['username'] = $data['name'];
		$login['userpassword'] = $data['password0'];
		$rel = $this->request(static::$urls['login'], $login);
		$rel = json_decode($rel, true);
		if ($rel['code'] != 1) exit('登陆失败');
	}

    /**
     *进行评论操作。
     */
	private function comment(){
		$data = 'applyid=' . static::$id . '&score=5&comment='.urlencode(static::$content[rand(0, count($content)-1)]);
		$rel = $this->request(static::$urls['comment'], $data , $header);
		$rel = json_decode($rel, true);
		if ($rel['code'] != 1) exit('投票失败');
		echo '投票成功';
	}

    /**
     * 随机生成身份证
     * @return array
     */
	private function createID(){
		$province = array('11','12','13','14','15','21','22','23','31','32','33','34','35','36','37','41','42','43','44','45','46','50','51','52','53','54','61','62','63','64','65');
		$shi = array('01','02','03','04','05','06','07','08','09','10','11','12','13','14','15','16','17','18','19','20','21','22','23','24','25','26','27','28');
		return $province[rand(0,30)].$shi[rand(0,20)].$shi[rand(0,20)].'199'.rand(0,8).$shi[rand(0,11)].$shi[rand(0,27)].rand(1000,9999);
	}

    /**
     * 随机生成用户名
     * @return array
     */
	private function createName(){
		return static::$first[rand(0, count(static::$first)-1)].static::$last[rand(0, count(static::$last)-1)];
	}

    /**
     * 随机生成电子邮箱
     * @return array
     */
	private function createEmail(){
		return rand(111111111,2000000000).'@qq.com';
	}

    /**
     * curl发送请求
     * @return string
     */
	private function request($url, $data = '', $return_header = false) {
		if (is_array($data)) {
			foreach ($data as $k => $v) {
				$test[] = $k.'='.urlencode($v);
			}
			$data = implode('&', $test);
		}
		$header = array(
			'User-Agent: Mozilla/5.0 (Linux; Android 5.1.1; Redmi Note 3 Build/LMY47V) AppleWebKit/537.36 (KHTML, like Gecko) Version/4.0 Chrome/37.0.0.0 Mobile MQQBrowser/6.8 TBS/036872 Safari/537.36 MicroMessenger/6.3.32.960 NetType/WIFI Language/zh_CN sfpy',
			'Cookie: ASP.NET_SessionId='.static::$cookie,
		);
		$curl = curl_init();
		curl_setopt($curl, CURLOPT_URL, $url);
		curl_setopt($curl, CURLOPT_POST, 1);
		curl_setopt($curl, CURLOPT_HTTPHEADER, $header);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
		curl_setopt($curl, CURLOPT_HEADER, $return_header);
		curl_setopt($curl, CURLOPT_NOBODY, $return_header);
		$content = curl_exec($curl);
		curl_close($curl);
		
		return $content;
	}
}