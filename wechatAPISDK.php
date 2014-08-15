<?php
/** 
 * 微信支付
 * @author funbox www.funboxpower.com
 * @copyright 2014
 */
require_once ('base/HttpClient.class.php');
class Wechat{
	
	var $wechat_config;
	var $wechat_time;
	var $wechat_noncestr;
	
	function __construct($wechat_config){
		$this->wechat_config = $wechat_config;
	}
	
    /**
     * Wechat::buildPackage()
     * 生成package
     * @param array $parameter
     * @return string
     */
    public function buildPackage($parameter) {
    	 	
        $filter = array('bank_type', 'body', 'partner', 'out_trade_no', 'total_fee', 'fee_type', 'notify_url', 'spbill_create_ip', 'input_charset');
        $base = array(
        	'notify_url' => $this->wechat_config['notify_url'],	
            'bank_type' => 'WX',
            'fee_type' => '1',
            'input_charset' => 'UTF-8',
            'partner' => $this->wechat_config['partner_id'],
        	 );
        $parameter = array_merge($parameter, $base);
        $array = array();
        foreach ($parameter as $k => $v) {
            if (in_array($k, $filter)) {
                $array[$k] = $v;
            }
        }
        ksort($array);
		reset($array);
        $signPars = ''; 
        foreach ($array as $k => $v) {
            $signPars .= $k."=".$v."&";
        }
        $sign = strtoupper(md5($signPars.'key='.$this->wechat_config['partner_key']));
        $signPars = '';
        foreach ($array as $k => $v) {
            $signPars .= strtolower($k) . "=" . urlencode($v) . "&";
        }        
        
        return $signPars . 'sign=' . $sign;
    }
    
    /**
     * Wechat::getXmlArray()
     * 从xml中获取数组
     * @return array
     */
    public function getXmlArray() {
    	$xmlData = file_get_contents("php://input");
		if ($xmlData) {
			$postObj = simplexml_load_string($xmlData, 'SimpleXMLElement', LIBXML_NOCDATA);		
            if (! is_object($postObj)) {
                return false;
            }
            $array = json_decode(json_encode($postObj), true); // xml对象转数组    
            return array_change_key_case($array, CASE_LOWER); // 所有键小写
        } else {
            return false;
        }        
    }
    
	/**
	 * Wechat::verifyNotify()
	 * 验证服务器通知
	 * @param array $data
	 * @return array
	 */
	public function verifyNotify($data) {
        $xml = $this->getXmlArray();
        if (! $xml || ! $data) {
            return false;
        }
        $AppSignature = $xml['appsignature'];
        unset($xml['signmethod'], $xml['appsignature']);
        $sign = $this->buildSign($xml);
        if ($AppSignature != $sign) {
            return false;
        } elseif ($data['trade_state'] != 0) {
            return false;
        }
        
        return $xml;
	}

	/**
	 * Wechat::buildSign()
	 * 生成sign值
	 * @param array $array
	 * @return string
	 */
	public function buildSign($array) {
		$signPars = "";
        $array['appkey'] = $this->wechat_config['pay_sign_key'];
		ksort($array);
		reset($array);
		foreach($array as $k => $v) {
				$signPars.=$k."=".$v."&";
		}
        $signPars = rtrim($signPars, '&'); // 去除最后一个&符号
        $sign = sha1($signPars);
		return $sign;

	}
    
    /**
     * wechat::getAccessToken()
     * 获取access_token
     * @return string
     */
    public function getAccessToken() {
    	$request = HttpClient::quickGet('https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=' . $this->wechat_config['app_id'] . '&secret=' . $this->wechat_config['app_secret'] , $this->wechat_config['cacert_url']);
        $requestArray = json_decode($request, true);
        if (isset($requestArray['errcode'])) {
            return false;
        }
        $accessToken = $requestArray['access_token'];
        return $accessToken;
    }
    
    /**
     * Wechat::createorder()
     * 生成预支付订单
     * @param array $access_token
     * @param array $parameter
     * @return array
     */
    public function createOrder($access_token , $parameter) {
    	$url = 'https://api.weixin.qq.com/pay/genprepay?access_token='.$access_token;
    	$params = array(
    			'appid' => $this->wechat_config['app_id'],
    			'traceid'=>'',
    			'noncestr' => uniqid(),
    			'package' => $this->buildPackage($parameter),
    			'timestamp' => time(), 			
    	);	
    	//用于之后的手机唤起 sign
    	$this->wechat_noncestr = $params['noncestr'];
    	$this->wechat_time = $params['timestamp'];
    	
    	$params['app_signature'] = $this->buildSign($params);
    	$params['sign_method'] = 'sha1';
    	$result = HttpClient::quickPost($url, json_encode($params));
    	return json_decode($result, true);
    }
}
