<?php


$rawData = file_get_contents("php://input");
file_put_contents('webhook.log', $rawData);

// return;


function ipnVerification() {
    $secretKey="ALANCLAYPO";
    $pop = "";
    $ipnFields = array();

    foreach ($_POST as $key => $value) {
        if ($key == "cverify") {
            continue;
        }
        $ipnFields[] = $key;
    }

    sort($ipnFields);
    foreach ($ipnFields as $field) {
        // if Magic Quotes are enabled $_POST[$field] will need to be
        // un-escaped before being appended to $pop
        $pop = $pop . $_POST[$field] . "|";
    }

    $pop = $pop . $secretKey;

    $calcedVerify = sha1(mb_convert_encoding($pop, "UTF-8"));
    $calcedVerify = strtoupper(substr($calcedVerify,0,8));

    file_put_contents('webhookclick.log', json_encode($_POST), FILE_APPEND);
    
    return $calcedVerify == $_POST["cverify"];
    
}

$secretKey = "ALANCLAYPO"; // secret key from your ClickBank account
 
// get JSON from raw body...
$message = json_decode(file_get_contents('php://input'));
 
// Pull out the encrypted notification and the initialization vector for
// AES/CBC/PKCS5Padding decryption
$encrypted = $message->{'notification'};
$iv = $message->{'iv'};
error_log("IV: $iv");
 
// decrypt the body...
$decrypted = trim(
 openssl_decrypt(base64_decode($encrypted),
 'AES-256-CBC',
 substr(sha1($secretKey), 0, 32),
 OPENSSL_RAW_DATA,
 base64_decode($iv)), "\0..\32");
  
error_log("Decrypted: $decrypted");
 
////UTF8 Encoding, remove escape back slashes, and convert the decrypted string to a JSON object...
$sanitizedData = utf8_encode(stripslashes($decrypted));

$order = json_decode($decrypted);

 file_put_contents('webhook123.log', $decrypted);




// Ready to rock and roll - If the decoding of the JSON string wasn't
// successful, then you can assume the notification wasn't encrypted
// with your secret key.
//    ipnVerification();

if($order->transactionType == 'SALE'){

        $customer = $order->customer;
        
        // Shipping information
        $shippingInfo = $customer->shipping;
        $name = $shippingInfo->fullName;
        $email = $shippingInfo->email;  //'thelinearmedia@gmail.com'; //$shippingInfo->email;

        $webhookUrl = "your kajabi product activation link";

        // Data to be sent in the request
        $data = [
            "name" => $name,
            "email" => $email,
            "External_User_ID" => $email,
        ];

        // Initialize cURL session
        $ch = curl_init($webhookUrl);

        // Set cURL options
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
        ]);

        // Execute the cURL request
        $response = curl_exec($ch);
         
        // Check for cURL errors or handle the response as needed
        // if ($response === false) {
        //     return 0
        // } else {
        //     return 1;
        // }

        // Close cURL session
        curl_close($ch);
}

// return 1;
    // URL of the Kajabi webhook endpoint

?>
