<?php
$wechat_config['app_id'] = "wx61e9efc271be87f2";// 公众号身份标识

$wechat_config['app_secret'] = "8eb1bd96a60f8231e13e6f0874561309";// 权限获取所需密钥 Key

$wechat_config['pay_sign_key'] = "Qn5LM9fCDMDNNeFEqITN4cxCNyzKceB8szwWmUqT4laGqK5SapxD8r38gC3qM9BK8UEc2VeiDWs4OJcg8W6rtjLvHc0Jraq6fS7ph1YUzbPMHyQutwqgohHuSDifytCw";// 加密密钥 Key，也即appKey

$wechat_config['partner_id'] = '1218891802';// 财付通商户身份标识

$wechat_config['partner_key'] = 'c72fecdb206e8d7cee2b4f4da861693f';// 财付通商户权限密钥 Key

$wechat_config['notify_url'] = 'http:/www.funboxpower/wechat_notify.php';// 微信支付完成服务器通知页面地址

$wechat_config['cacert_url'] = dirname(__FILE__).'/1218891801_20140425185952.pfx';

?>