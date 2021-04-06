<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="">
    <meta name="author" content="">
    <link rel="icon" href="../../favicon.ico">

    <title>G(e)mails</title>

    <!-- Bootstrap core CSS -->
    <link href="bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap theme -->
    <link href="bootstrap-theme.min.css" rel="stylesheet">

    <!-- Custom styles for this template -->
    <link href="theme.css" rel="stylesheet">
  </head>
  <body role="document">
<?php
ini_set('display_errors', 1);
session_start();
session_start();

$mysqli3=mysqli_connect('127.0.0.1', 'root', 'shani3003', 'moodle1', '3306');


//connect to mysql
$mysqli  = mysqli_connect('127.0.0.1', 'root', 'shani3003', 'stam', '3306');
//connect to mysql for students details
//$mysqli2  = mysqli_connect('127.0.0.1', 'root', 'shani3003', 'stam', '3306');

//connect to moodle1


//Your gmail email address and password
$username = $_SESSION["username"];
$password = $_SESSION["password"];

//Select messagestatus as ALL or UNSEEN which is the unread email
$messagestatus = "ALL";

//-------------------------------------------------------------------

//Gmail host with folder
$hostname = $_POST["folder"];

//Open the connection
$connection = imap_open($hostname,$username,$password) or die('Cannot connect to Gmail: ' . imap_last_error());


//Grab all the emails inside the inbox
$emails = imap_search($connection,$messagestatus);

//number of emails in the inbox
$totalemails = imap_num_msg($connection);
  
echo "<div class='container'>
	
	<div class='col-md-6'><h1 class='bg-primary'>Total Emails: " . $totalemails . "</h1></div></div>";

$result=mysqli_query($mysqli3,
    "CREATE TABLE  IF NOT EXISTS mdl_emoodles_user_details (
uniqueid TEXT, userid TEXT, firstname TEXT, lastname TEXT, email TEXT, enrolled TEXT, courseid TEXT, coursename TEXT);");

$result=mysqli_query($mysqli3,
    "    INSERT INTO mdl_emoodles_user_details
    SELECT CONCAT(u.id, '_', c.id) AS uniqueid,
        u.id AS userid,
        u.firstname,
        u.lastname,
        u.email,
        MAX(CASE WHEN ue.id IS NULL THEN '' ELSE 'X' END) AS enrolled,
        c.id AS courseid,
        c.fullname AS coursename
    FROM mdl_user u
    CROSS JOIN mdl_course c
    LEFT JOIN mdl_enrol e ON e.courseid = c.id
    LEFT JOIN mdl_user_enrolments ue ON u.id = ue.userid AND ue.enrolid = e.id
    WHERE u.deleted = 0
    GROUP BY u.id, c.id");
  
if($emails) {

  //sort emails by newest first
  rsort($emails);

  //loop through every email in the inbox
  foreach($emails as $email_number) {

      //get some header info for subject, from, and date.. imap_fetch_overview (which was in the example I used for this) just returns true or false
    $headerinfo = imap_headerinfo($connection, $email_number);

    //Because attachments can be problematic this logic will default to skipping the attachments    
    $message = imap_fetchbody($connection,$email_number,1.1);
         if ($message == "") { // no attachments is the usual cause of this
          $message = imap_fetchbody($connection, $email_number, 1);
    }
    //shani shalel add :
    $tomess=$headerinfo->{'toaddress'};
    //
	$from = $headerinfo->{'fromaddress'};
	$subject = $headerinfo->{'subject'};
	$date = $headerinfo->{'date'};
	$fmessage = quoted_printable_decode($message);


//if the emails is in the students details table
	$result=mysqli_query($mysqli, "select * from students_details where email='$from'");

	if ($result->num_rows>0){

        echo <<<END
        <div class='container'>
        <div class='col-md-6'>
        <h4>Inserting:<br><br>
        <h4>Sender:</h4>  $from <br><br>
        <h4>Subject:</h4> $subject <br><br>
        <h4>Date:</h4> $date <br><br>
        <h4>Message:</h4> $fmessage <br><br>
        <h4>To:</h4> $tomess <br><br>
        </div></div>
END;

        if (!($stmt = $mysqli->prepare("INSERT INTO emails (sender, subject, date, message, recive) VALUES (?,?,?,?,?)"))) {
            echo "Prepare failed: (" . $mysqli->errno . ") " . $mysqli->error;
        }

        if (!$stmt->bind_param("sssss", $from, $subject, $date, $fmessage, $tomess)) {
            echo "Binding parameters failed: (" . $stmt->errno . ") " . $stmt->error;
        }

        if (!$stmt->execute()) {
            echo "Execute failed: (" . $stmt->errno . ") " . $stmt->error;
        }

    }
      //shani shalel add this
      imap_delete($connection, $email_number);
      $check = imap_mailboxmsginfo($connection);
      echo "Messages after  delete: " . $check->Nmsgs . "<br />\n";
  }


}


imap_expunge($connection);

$check = imap_mailboxmsginfo($connection);
echo "Messages after expunge: " . $check->Nmsgs . "<br />\n";


// close the connection
imap_close($connection);

?>
