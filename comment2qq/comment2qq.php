<?php
/**
Plugin Name: Comment2QQ
Plugin URI: https://krunk.cn/kblog1624.html
Description: 将你的评论实时推送至 QQ Webhook
Version: 1.0
Author: Krunk Design
Author URI: https://krunk.cn
*/

function krunk_comment_qq_notify($comment_id) {
	$comment = get_comment($comment_id);
	$content=$comment->comment_content;
		$match_count=preg_match_all('/<a href="#comment-([0-9]+)?" rel="nofollow">/si',$content,$matchs);
	if($match_count>0){
			foreach($matchs[1] as $parent_id){
					krunk_qq_webhook($parent_id,$comment,1);
			}
	}elseif($comment->comment_parent!='0'){
			$parent_id=$comment->comment_parent;
			krunk_qq_webhook($parent_id,$comment,1);
	}else {krunk_qq_webhook($parent_id,$comment,0);return;}
}
add_action('comment_post', 'krunk_comment_qq_notify');

function krunk_qq_webhook($parent_id,$comment,$floor){
 	//QQ Webhook
	$endpoint = get_option('krunk_comment_qq_notify_url');
	if ($floor==1){
		$body = '{"content": [ {"type":0,"data":"《' . get_the_title($comment->comment_post_ID) . '》速来围观大佬水评\\n\\n' . trim($comment->comment_author) . ' 评论 ' . trim(get_comment($parent_id)->comment_author) . ': '. str_replace('"', '\'', strip_tags(htmlspecialchars(do_shortcode(trim($comment->comment_content))))) .'"}]}';
	}else{
		$body = '{"content": [ {"type":0,"data":"《' . get_the_title($comment->comment_post_ID) . '》速来围观大佬水评\\n\\n' . strip_tags(htmlspecialchars(trim($comment->comment_author))) . '说: '. str_replace('"', '\'', strip_tags(htmlspecialchars(do_shortcode(trim($comment->comment_content))))) .'"}]}';
	}
	$options = [
		'body'        => $body,
		'headers'     => [
			'Content-Type' => 'application/json',
		],
		'timeout'     => 10,
		'redirection' => 5,
		'blocking'    => true,
		'httpversion' => '1.0',
		'sslverify'   => false,
	    'data_format' => 'body',
	];
	wp_remote_post( $endpoint, $options );
}

function krunk_comment_qq_notify_add_management_page(){
	if( isset( $_POST['krunk_comment_qq_notify_url'] ) ){
		$endpoint1 = $_POST['krunk_comment_qq_notify_url'];
		if (FALSE === update_option('krunk_comment_qq_notify_url',$endpoint1)) add_option('krunk_comment_qq_notify_url',$endpoint1);
		echo '<div id="message" class="updated fade"><p><strong>更新成功</p></strong><p>尝试来个评论吧</p></div>';
	}
	?><div class="wrap">
		<h2>Comment2QQ 设置</h2>
		<h3>这个插件能把WP上所有评论实时发送到QQ群</h3><br>
		<form method="post" action="tools.php?page=<?php echo basename(__FILE__); ?>">
			<h3 style="margin-bottom:5px;">
				输入 Webhook 链接：<br><br>
				<textarea rows = "5" cols = "60" name="krunk_comment_qq_notify_url" type="text" id="krunk_comment_qq_notify_url"/><?php echo get_option('krunk_comment_qq_notify_url'); ?></textarea>
			</h3>

			<p>链接样式：https://app.qun.qq.com/cgi-bin/api/hookrobot_send?key=xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx</p>
			
			<p>
				<input class="button-primary" name="krunk_comment_qq_notify_submit" value="提交" type="submit" />
			</p>
		</form>

<br>
		<p>
			联系我：<br>
			<a href="mailto: webmaster@krunk.cn">webmaster@krunk.cn</a><br>
			<a href="https://krunk.cn">KRUNK DESIGN</a><br><br>
			<a href="https://krunk.cn/kblog1624.html">了解插件使用方法</a>
		</p>
		<?php
}

function krunk_comment_qq_notify_management_page(){
	add_management_page("Comment2QQ", "Comment2QQ", "manage_options", basename(__FILE__), "krunk_comment_qq_notify_add_management_page");
}
add_action('admin_menu', 'krunk_comment_qq_notify_management_page');

function krunk_plugin_comment2qq_action_links( $links, $file ) {
    if ( plugin_basename( "comment2qq/comment2qq.php" ) !== $file ) {
        return $links;
    }
    $settings_link = '<a href="tools.php?page=comment2qq.php">' . esc_html__( 'Settings', 'Krunk Design' ) . '</a>';
    array_unshift( $links, $settings_link );
    return $links;
}
add_filter( 'plugin_action_links', 'krunk_plugin_comment2qq_action_links', 10, 2 );

?>