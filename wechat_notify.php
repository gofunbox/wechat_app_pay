<?php
/**
 * 服务器异步通知
 * @author funbox www.funboxpower.com
 * @copyright 2014
 */
require 'Wechatconfig.php';
require 'WechatAPISDK.php';

$notify_info = $_REQUEST; // 官方文档所说的post是接收不到参数的，实际上要用get才行
		
$wechat = new Wechat($wechat_config);
$verify_info = $wechat->verifyNotify($notify_info); // 验证通知
if ($verify_info !== false) {	
	echo 'success';
} else {
	echo 'fail';
}
?>
