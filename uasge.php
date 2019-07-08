<?

include 'httpsocket.php';

// Notification settings
$notify_email='mail@email.address';
$notify_threshold=70;

//Directadmin API access
$server_login='resellername';
$server_pass='resellerpassword';
$server_host='directadmin.server.address';
$server_ssl='Y';
$server_port=2222;

$sock = new HTTPSocket;

if ($server_ssl == 'Y'){
	$sock->connect('ssl://'.$server_host, $server_port);
} else{ 
	$sock->connect($server_host, $server_port);
}
$sock->set_login($server_login,$server_pass);

$sock->query('/CMD_API_SHOW_USERS',array('reseller'=>$server_login));
$users = $sock->fetch_parsed_body();

if (is_array($users['list']) && !empty($users['list'])){
	foreach ($users['list'] as $user) {
		$sock->query('/CMD_API_SHOW_USER_CONFIG',array('user'=>$user));
		$user_config = $sock->fetch_parsed_body();

		$sock->query('/CMD_API_SHOW_USER_USAGE',array('user'=>$user));
		$user_usage = $sock->fetch_parsed_body();
		
		if (isset($user_config['quota']) && floatval($user_config['quota'])>0 && isset($user_usage['quota']) && floatval($user_usage['quota'])>0) {
			$usage_percent = (100/floatval($user_config['quota']))*floatval($user_usage['quota']);
			if ($usage_percent>$notify_threshold) {
				echo 'User: '.$user.' is using over '.$usage_percent.'% if his quota';
				if (mail($notify_email, 'User: '.$user.' is using over '.$usage_percent.'% if his quota', 'User: '.$user.' is using over '.$usage_percent.'% if his quota')) {
				  echo 'Mail sent';
				} else {
					echo 'Mail not sent';
				}
			}
		}
	}
}
