<?php
use App\Services\FirebasePushService;
use Illuminate\Support\Facades\Cache;


if(isset($_GET['send_notification'])){
   send_notification ();
}

function send_notification()
{

    // SEND PUSHUP NOTIFICATION
    if($_REQUEST['token']!=null && !empty($_REQUEST['token'])){
        $firebaseService = new FirebasePushService();
        $firebaseService->sendToToken($_REQUEST['token'], $_REQUEST['title'], $_REQUEST['body']);
    }
	
	//echo 'Aramisc Edu';
//define( 'API_ACCESS_KEY', 'AAAAFyQhhks:APA91bGJqDLCpuPgjodspo7Wvp1S4yl3jYwzzSxet_sYQH9Q6t13CtdB_EiwD6xlVhNBa6RcHQbBKCHJ2vE452bMAbmdABsdPriJy_Pr9YvaM90yEeOCQ6VF7JEQ501Prhnu_2bGCPNp');
    define('API_ACCESS_KEY', Cache::get('firebase_access_token'));
 //   $registrationIds = ;
#prep the bundle
     $msg = array
          (
		'body' 	=> $_REQUEST['body'],
		'title'	=> $_REQUEST['title'],
             	
          );
	$fields = array
			(
				'to'		=> $_REQUEST['token'],
				'notification'	=> $msg
			);
	
	
	$headers = array
			(
				'Authorization: key=' . API_ACCESS_KEY,
				'Content-Type: application/json'
			);
#Send Reponse To FireBase Server	
		$ch = curl_init();
		curl_setopt( $ch,CURLOPT_URL, 'https://fcm.googleapis.com/fcm/send' );
		curl_setopt( $ch,CURLOPT_POST, true );
		curl_setopt( $ch,CURLOPT_HTTPHEADER, $headers );
		curl_setopt( $ch,CURLOPT_RETURNTRANSFER, true );
		curl_setopt( $ch,CURLOPT_SSL_VERIFYPEER, false );
		curl_setopt( $ch,CURLOPT_POSTFIELDS, json_encode( $fields ) );
		$result = curl_exec($ch );
		echo $result;
		curl_close( $ch );
}
?>