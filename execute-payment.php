<?php
ob_start();
include_once "./bkashconfig.php";

$curl = curl_init();

$data = json_encode([
  "paymentID" => $_GET['paymentID'],
]);

if(!isset($_GET['paymentID']) || empty($_GET['paymentID']) ){
  $_SESSION['Error'] = "ভুল রিকুয়েস্ট।";
  header("Location: ../../recharge.php?statusMessage=error&id=0");
  die();
}

curl_setopt_array($curl, [
  CURLOPT_URL => BASEURL . "/tokenized/checkout/execute",
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_ENCODING => "",
  CURLOPT_MAXREDIRS => 10,
  CURLOPT_TIMEOUT => 30,
  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
  CURLOPT_CUSTOMREQUEST => "POST",
  CURLOPT_POSTFIELDS => $data,
  CURLOPT_HTTPHEADER => [
    "Authorization: ".$_SESSION['id_token'],
    "X-APP-Key: ".APPKEY,
    "accept: application/json",
    "content-type: application/json"
  ],
]);

$response = curl_exec($curl);
$err = curl_error($curl);

curl_close($curl);

if ($err) {

  $_SESSION['Error'] = json_encode($err);

  header("Location: ../../recharge.php?statusMessage=error&id=1");
  die();

} else {

  $response = json_decode($response);
  //add config file for db connection
  include('../../includes/config.php');
  // validate payment
  if($response->statusMessage !== 'Successful' || $response->transactionStatus !== 'Completed'){
    $_SESSION['Error'] = "পেমেন্ট হয়নি।";
    header("Location: ../../recharge.php?statusMessage=error&id=2");
    die();
  }else if($response->statusMessage === 'Successful' && $response->transactionStatus === 'Completed'){
    //update user balance
    // Set the PDO error mode to exception
    $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    // Prepare the SQL statement
    $sql = "UPDATE users SET balance = balance + :amount WHERE email = :email";
    $stmt = $dbh->prepare($sql);
    // Bind the parameters
    $stmt->bindParam(':amount', $response->amount);
    $stmt->bindParam(':email', $_SESSION['alogin']);
    // Execute the query for balance add
    $stmt->execute();

    //save to database
    $amountAdd = "INSERT INTO payment (paymentID,trxID, paymentExecuteTime, customerMsisdn, amount, email) 
        VALUES (:paymentID, :trxID, :paymentExecuteTime, :customerMsisdn, :amount, :email)";
    $amountAddStmt = $dbh->prepare($amountAdd);

    $amountAddStmt->bindParam(':paymentID', $response->paymentID);
    $amountAddStmt->bindParam(':trxID', $response->trxID);
    $amountAddStmt->bindParam(':paymentExecuteTime', $response->paymentExecuteTime);
    $amountAddStmt->bindParam(':customerMsisdn', $response->customerMsisdn);
    $amountAddStmt->bindParam(':amount', $response->amount);
    $amountAddStmt->bindParam(':email', $_SESSION['alogin']);
    $amountAddStmt->execute();
    $_SESSION['Success'] = $response->amount." টাকা সফলভাবে রিচার্জ হয়েছে।";
    header("Location: ../../recharge.php?statusMessage=success&id=1");
    die();
  }else{
    $_SESSION['Error'] = "পেমেন্ট এরোর।";
    header("Location: ../../recharge.php?statusMessage=error&id=3");
    die();
  }
}