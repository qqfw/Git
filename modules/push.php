<?php
/**
 * 有赞推送服务消息接收文件
 */
require( '../../../../wp-load.php' );
$client_id = git_get_option('git_yzclient_id');
$client_secret = git_get_option('git_yzclient_secret');
$kdt_id = git_get_option('git_yzkdt_id');

$json = file_get_contents('php://input');
$data = json_decode($json, true);
/**
 * 判断消息是否合法，若合法则返回成功标识
 */
$msg = $data['msg'];

$sign_string = $client_id."".$msg."".$client_secret;
$sign = md5($sign_string);
if($sign != $data['sign'] && $data['sendCount'] != 0 && $data['type'] != 'trade_TradePaid' ){
    exit();
}else{
    $result = array("code"=>0,"msg"=>"success") ;
    var_dump($result);
}

/**
 * msg内容经过 urlencode 编码，需进行解码
 */
	$msg = json_decode(urldecode($msg),true);//推送的信息
	$amount = $msg['full_order_info']['orders']['0']['payment']; 	//交易金额
	$userid = $msg['full_order_info']['orders']['0']['title']; 		//交易标题
	$YZid = $data['id']; //有赞支付ID
	//error_log('push number'.$amount);  //输出到错误日志
	$point_number = $amount * git_get_option('git_chongzhi_dh');

    /* 有赞SDK代码结束，开始WordPress代码*/
	$user = get_user_by( 'id', $userid  );
	$message = '<div class="emailcontent" style="width:100%;max-width:720px;text-align:left;margin:0 auto;padding-top:80px;padding-bottom:20px"><div class="emailtitle"><h1 style="color:#fff;background:#51a0e3;line-height:70px;font-size:24px;font-weight:400;padding-left:40px;margin:0">充值到账通知</h1><div class="emailtext" style="background:#fff;padding:20px 32px 40px"><div style="padding:0;font-weight:700;color:#6e6e6e;font-size:16px">尊敬的'.$user->display_name.',您好！</div><p style="color:#6e6e6e;font-size:13px;line-height:24px">您的金币充值已成功到账，请查收！</p><table cellpadding="0" cellspacing="0" border="0" style="width:100%;border-top:1px solid #eee;border-left:1px solid #eee;color:#6e6e6e;font-size:16px;font-weight:normal"><thead><tr><th colspan="2" style="padding:10px 0;border-right:1px solid #eee;border-bottom:1px solid #eee;text-align:center;background:#f8f8f8">您的金币详细情况</th></tr></thead><tbody><tr><td style="padding:10px 0;border-right:1px solid #eee;border-bottom:1px solid #eee;text-align:center;width:100px">用户名</td><td style="padding:10px 20px 10px 30px;border-right:1px solid #eee;border-bottom:1px solid #eee;line-height:30px">'.$user->display_name.'</td></tr><tr><td style="padding:10px 0;border-right:1px solid #eee;border-bottom:1px solid #eee;text-align:center">充值金币</td><td style="padding:10px 20px 10px 30px;border-right:1px solid #eee;border-bottom:1px solid #eee;line-height:30px">'.$point_number.'</td></tr><tr><td style="padding:10px 0;border-right:1px solid #eee;border-bottom:1px solid #eee;text-align:center">金币总额</td><td style="padding:10px 20px 10px 30px;border-right:1px solid #eee;border-bottom:1px solid #eee;line-height:30px">'.Points::get_user_total_points($userid, POINTS_STATUS_ACCEPTED ).'</td></tr></tbody></table><p style="color:#6e6e6e;font-size:13px;line-height:24px">如果您的金币金额有异常，请您在第一时间和我们取得联系哦，联系邮箱：'.get_bloginfo('admin_email').'</p></div><div class="emailad" style="margin-top:4px"><a href="'.home_url().'"><img src="http://reg.163.com/images/secmail/adv.png" alt="" style="margin:auto;width:100%;max-width:700px;height:auto"></a></div></div></div>';
	$headers = "Content-Type:text/html;charset=UTF-8\n";

    Points::set_points($point_number, $userid, array('description' => $YZid, 'status' => 'accepted'));//增加金币金币
	wp_mail( $user->user_email , 'Hi,'.$user->display_name.'，充值成功到账通知！', $message, $headers);
	wp_mail( get_bloginfo('admin_email') , '【收款成功】网站充值订单已完成','充值订单:用户ID：'.$userid.'/金额'.$amount.'元');
	//more

?>