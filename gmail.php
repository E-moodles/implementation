
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

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP;

require 'vendor/autoload.php';


ini_set('display_errors', 1);
session_start();
session_start();

//connect to mysql
//connect to mysql
$mysqli  = mysqli_connect('127.0.0.1', 'root', 'shani3003', 'stam', '3306');
//connect to mysql for students details
$mysqli2  = mysqli_connect('127.0.0.1', 'root', 'shani3003', 'stam', '3306');
$mysqli3  = mysqli_connect('127.0.0.1', 'root', 'shani3003', 'moodle1', '3306');
$mysqli4  = mysqli_connect('127.0.0.1', 'root', 'shani3003', 'moodle1', '3306');
$mysqli5  = mysqli_connect('127.0.0.1', 'root', 'shani3003', 'moodle1', '3306');


//Your gmail email address and password
$username = isset($_SESSION["username"]);
$password = isset($_SESSION["password"]);

//Select messagestatus as ALL or UNSEEN which is the unread email
$messagestatus = "ALL";

//-------------------------------------------------------------------

//Gmail host with folder
$hostname = isset($_POST["folder"]);

//Open the connection
#$connection = imap_open($hostname,$username,$password) or die('Cannot connect to Gmail: ' . imap_last_error());
$connection = imap_open('{imap.gmail.com:993/imap/ssl}INBOX' ,'emoodlesmessage@gmail.com','moodle1234') or die('Cannot connect to Gmail: ' . imap_last_error());


//Grab all the emails inside the inbox
$emails = imap_search($connection,$messagestatus);

//number of emails in the inbox
$totalemails = imap_num_msg($connection);

echo "<div class='container'>
	
	<div class='col-md-6'><h1 class='bg-primary'>Total Emails: " . $totalemails . "</h1></div></div>";


$result=mysqli_query($mysqli3,
    "DROP TABLE if exists mdl_emoodles_user_details;");

//if the emails is in the students details table
$result=mysqli_query($mysqli3,
    "CREATE TABLE mdl_emoodles_user_details (
        uniqueid TEXT not null , userid TEXT not null, firstname TEXT not null , lastname TEXT not null, email TEXT not null,
         enrolled TEXT not null, courseid TEXT not null, coursename TEXT not null);");

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

//      $from = $overview[0]->from;
//
//      $header = imap_headerinfo($inbox, $x);
//      $fromaddress = $header->from[0]->mailbox . "@" . $header->from[0]->host;


        $fromaddr = $headerinfo->from[0]->mailbox . "@" . $headerinfo->from[0]->host;
        $tomess=$headerinfo->{'toaddress'};
        $from = $headerinfo->{'fromaddress'};
        //$from = $headerinfo->{'from[2]'};
        $subject = $headerinfo->{'subject'};
        $date = $headerinfo->{'date'};
        $fmessage = quoted_printable_decode($message);

//        $check="not send";
//




        //getting the number of the course em@gmail.

        $index_strudel=strpos($tomess,'@');//location of @
        $index_plus=strpos($tomess,'+');//location of +
        $index_point=strpos($tomess,'.');//location of .

        if ($index_plus==false || $index_point>$index_strudel){//there isn't plus in the mail

            //send mail to the address that we gets and tells the format isn't right
            $mail=new PHPMailer();
            $mail->IsSMTP(); // enable SMTP
            $mail->SMTPDebug = 1;  //Enable verbose debug output
            //$mail->isSMTP();                                            //Send using SMTP
            $mail->Host = 'smtp.gmail.com';                     //Set the SMTP server to send through
            $mail->SMTPAuth = true;                                   //Enable SMTP authentication
            $mail->Username = 'emoodlesmessage@gmail.com';                     //SMTP username
            $mail->Password = 'moodle1234';                               //SMTP password
            $mail->Port = 465;
            $mail->SMTPSecure = 'ssl';

            $mail->AddAddress($fromaddr);

            $mail->SetFrom("emoodlesmessage@gmail.com");
            $mail->Subject = 'E-Moodle Syntax error mail ';
            $mail->Body = 'The mail you send is not in the right format, please look at the right format again. ';
            $mail->isHTML(true);

            try {
                if ($mail->Send()) {
                    $check = "email send";
                } else {
                    $check = "email isn't send";
                }
            } catch (Exception $e) {

            }

        }
        else{

            $index_plus++;
            $space_between_1=$index_strudel-$index_plus;
            $space_between_2=$index_point-$index_plus;
            $index_point++;
            $space_between_3=$index_strudel-$index_point;

            $temp=substr($tomess,$index_plus,$space_between_1);
            $num_course=substr($tomess,$index_plus,$space_between_2 );
            $num_forum=substr($tomess,$index_point,$space_between_3);


            /**check if the email is participate in the course , according to num_course and $fromaddr
             * if the user is participate in the course he will have X in enrolled in this course_number
             **/

            $is_part=mysqli_query($mysqli5,"select  enrolled from mdl_emoodles_user_details 
            where email='$fromaddr' and courseid='$num_course'");

            $row=mysqli_fetch_array($is_part);
            $enrolled=$row[0]; //is it is participate it will be =='X'

            if($enrolled!='X'){ // isn't participate

                //send mail to the address that we gets and tells the format isn't right
                $mail=new PHPMailer();
                $mail->IsSMTP(); // enable SMTP
                $mail->SMTPDebug = 1;  //Enable verbose debug output
                // $mail->isSMTP();                                            //Send using SMTP
                $mail->Host = 'smtp.gmail.com';                     //Set the SMTP server to send through
                $mail->SMTPAuth = true;                                   //Enable SMTP authentication
                $mail->Username = 'emoodlesmessage@gmail.com';                     //SMTP username
                $mail->Password = 'moodle1234';                               //SMTP password
                $mail->Port = 465;
                $mail->SMTPSecure = 'ssl';

                $mail->AddAddress($fromaddr);

                $mail->SetFrom("emoodlesmessage@gmail.com");
                $mail->Subject = "E-Moodle incorrect course number  ";
                $mail->Body = "You are not participate in course number {$num_course} , please check your courses number again ";
                $mail->isHTML(true);
                try {
                    if ($mail->Send()) {
                        $check = "email send";
                    } else {
                        $check = "email isn't send";
                    }
                } catch (Exception $e) {

                }

            }
            else{

                /*checking if the num_forum is exist in the num_course we gets if not we will return email error*/

                $is_exist=mysqli_query($mysqli5,"select id from mdl_forum where course='$num_course' and id='$num_forum'");

                $row=mysqli_fetch_array($is_exist);
                $enrolled=$row[0]; //is it is exist it will be =='$num_forum'

                //this course don't have this forum number
                if(strcmp($enrolled,$num_forum)!=0){//the strings are not equal!
                    //we will send an error mail

                    //send mail to the address that we gets and tells the format isn't right
                    $mail=new PHPMailer();
                    $mail->IsSMTP(); // enable SMTP
                    $mail->SMTPDebug = 1;  //Enable verbose debug output
                    // $mail->isSMTP();                                            //Send using SMTP
                    $mail->Host = 'smtp.gmail.com';                     //Set the SMTP server to send through
                    $mail->SMTPAuth = true;                                   //Enable SMTP authentication
                    $mail->Username = 'emoodlesmessage@gmail.com';                     //SMTP username
                    $mail->Password = 'moodle1234';                               //SMTP password
                    $mail->Port = 465;
                    $mail->SMTPSecure = 'ssl';

                    $mail->AddAddress($fromaddr);

                    $mail->SetFrom("emoodlesmessage@gmail.com");
                    $mail->Subject = "E-Moodle incorrect course number  ";
                    $mail->Body = "The number of forum {$num_forum}  you enter isn't exist in course number : {$num_course} , please check your courses number and 
                    forum number again ";
                    $mail->isHTML(true);
                    try {
                        if ($mail->Send()) {
                            $check = "email send";
                        } else {
                            $check = "email isn't send";
                        }
                    } catch (Exception $e) {

                    }

                }
                else {

                    $var = html_entity_decode($message);

                    $resultt = mysqli_query($mysqli4, "select userid from mdl_emoodles_user_details where email='$fromaddr' and courseid = '$num_course'");
                    $row = mysqli_fetch_array($resultt);
                    $userid = $row[0];

                    echo <<<END
                    <div class='container'>
                    <div class='col-md-6'>
                    <h4>Inserting:<br><br>
                    <h4>Subject:</h4> $subject <br><br>
                    <h4>Message:</h4> $message <br><br>
                    <h4>Date:</h4> $date <br><br>
                    <h4>Sender:</h4> $fromaddr <br><br>
                    <h4>ID:</h4> $userid <br><br>
                    <h4>num of course:</h4> $num_course <br><br>
                    <h4>num of forum:</h4> $num_forum <br><br>

                    </div></div>
END;

                    $id1 = mysqli_query($mysqli4, "SELECT MAX(id) AS MAX FROM mdl_forum_discussions");
                    $d2 = mysqli_fetch_array($id1);
                    $id = $d2['MAX'] + 1;

                    $timestamp = strtotime($date);


                    if (!($stmt = $mysqli3->prepare("INSERT INTO mdl_forum_discussions (id, course, forum, name, firstpost, userid, assessed, timemodified, usermodified)
            VALUES ($id, $num_course, $num_forum, ?, $id, $userid, 0, $timestamp, 2)"))) {
                        echo "Prepare failed: (" . $mysqli3->errno . ") " . $mysqli3->error;
                    }


                    if (!$stmt->bind_param("s", $subject)) {
                        echo "Binding parameters failed: (" . $stmt->errno . ") " . $stmt->error;
                    }

                    if (!$stmt->execute()) {
                        echo "Execute failed: (" . $stmt->errno . ") " . $stmt->error;
                    }

                    $var1 = $fmessage;
                    //var2 for hebrew


                    if (!($stmt = $mysqli3->prepare("INSERT INTO mdl_forum_posts (id, discussion, parent, userid, created, modified, subject, message, messageformat)
                VALUES ($id, $id, 0, $userid, $timestamp, $timestamp, ?,?, 1)"))) {
                        echo "Prepare failed: (" . $mysqli3->errno . ") " . $mysqli3->error;
                    }


                    if (!$stmt->bind_param("ss", $subject, $fmessage)) {
                        echo "Binding parameters failed: (" . $stmt->errno . ") " . $stmt->error;
                    }

                    if (!$stmt->execute()) {
                        echo "Execute failed: (" . $stmt->errno . ") " . $stmt->error;
                    }

                    //gets the emaill of all of the students
                    $emails_students = mysqli_query($mysqli4, "select email from mdl_emoodles_user_details where  courseid = '$num_course' and enrolled='X';");
                    $result1=mysqli_query($mysqli4, "select coursename from mdl_emoodles_user_details where  courseid = '5';");
                    $row = mysqli_fetch_array($result1);
                    $cousre_name = $row[0];

                    while($ans = $emails_students->fetch_array())
                    {
                        $list_of_emails[] = $ans;
                    }

                    foreach($list_of_emails as $ans){

                        $mail=new PHPMailer();
                        $mail->IsSMTP(); // enable SMTP
                        $mail->SMTPDebug = 1;  //Enable verbose debug output
                        //$mail->isSMTP();                                            //Send using SMTP
                        $mail->Host = 'smtp.gmail.com';                     //Set the SMTP server to send through
                        $mail->SMTPAuth = true;                                   //Enable SMTP authentication
                        $mail->Username = 'emoodlesmessage@gmail.com';                     //SMTP username
                        $mail->Password = 'moodle1234';                               //SMTP password
                        $mail->Port = 465;
                        $mail->SMTPSecure = 'ssl';

                        $mail->AddAddress($ans['email']);

                        $mail->SetFrom("emoodlesmessage@gmail.com");
                        $mail->Subject = "E-Moodle :: You have new message from +{$num_course}.{$num_forum} , course name :{$cousre_name},email : {$fromaddr}, subject :{$subject}";
                        $mail->Body = $fmessage;
                        $mail->isHTML(true);

                        try {
                            if ($mail->Send()) {
                                $check = "email send";
                            } else {
                                $check = "email isn't send";
                            }
                        } catch (Exception $e) {

                        }




                    }









                }


            }

        }







        // }
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
