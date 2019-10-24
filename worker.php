<?php

require 'vendor/autoload.php';
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;
use Dotenv\Dotenv;


$dotenv = Dotenv::create(__DIR__);
$dotenv->load();
$notificationQueueUrl = getenv('EMAIL_QUEUE_URL');
$emailQueueName = getenv('EMAIL_QUEUE_NAME');


$connection = new AMQPStreamConnection($notificationQueueUrl , 5672, 'guest', 'guest');
$channel = $connection->channel();

$channel->queue_declare($emailQueueName, false, false, false, false);

echo " [*] Waiting for messages. To exit press CTRL+C\n";

$callback = function ($msg) {
    echo ' [x] Received ', $msg->body, "\n";

    // extracr JSON Message form MessageBody
    // Decode JSON data to PHP associative array
    $arr = json_decode($msg->body, true);
    $user_email = $arr["email"];
    $message = $arr["message"]; // Message Object
    
    $message_arr = json_decode($message,true);
    $url = $message_arr["url"];
    $urlId = $message_arr["urlId"];
    $statusMessage = $message_arr["statusMessage"];
    $statusCode = $message_arr["statusCode"];
    $appStatus = $message_arr["appStatus"];

    // Now Create Email Template from Message and extract user email and Send
    
    // Create the Transport
    $transport = (new Swift_SmtpTransport(getenv('SMTP_HOST'), 587, 'tls'))
    ->setUsername(getenv('SMTP_USERNAME'))
    ->setPassword(getenv('SMTP_PASSWORD'))
    ->setAuthMode('PLAIN');
    // Create the Mailer using your created Transport
    $mailer = new Swift_Mailer($transport);
    // Create a message
    $message = (new Swift_Message("Webmonere Critical Notification"))
    ->setFrom(['rajdeeponnet@gmail.com' => 'Webmonere Critical Notification'])
    ->setTo($user_email)
    ->setBody("<h1>Your Url ".$url." Status :".$statusMessage." "."Status Code: ".$statusCode." "."and your app is : ".$appStatus."</h1>","text/html");



    try {
        // Send the message
          $result = $mailer->send($message);
    } catch (\Swift_TransportException $Ste) {
        echo $Ste;
    }

};
$channel->basic_consume('main_email_queue', '', false, true, false, false, $callback);
while ($channel->is_consuming()) {
    $channel->wait();
}


$channel->close();
$connection->close();

?>