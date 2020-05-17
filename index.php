<?php
$filename = file_exists('config-dev.php') ? "config-dev.php":"config.php";
include($filename);


$sessionId   = $_POST["sessionId"];
$serviceCode = $_POST["serviceCode"];
$phoneNumber = $_POST["phoneNumber"];
$text        = $_POST["text"];
$userExists = user_exists($sessionId, "", "");
$doUserExists = $userExists[0];
$token = $userExists[1];

//CON to Allow Response
//END to end session

$message = "";
$response = "";
$check_session = check_session($sessionId, $phoneNumber);
if ( $text == "" && $check_session == "0") {
   $message = "CON Ikaze kuri Learning Reminder. \n";
   $response = $message."Hitamo ururimi: \n";
   $response .= "1. Kinyarwanda \n";
   $response .= "2. English \n";
}
else if ( $check_session != "0") {
  $phoneNumber = str_replace("+25", "", $phoneNumber);
  $response = get_menu($phoneNumber, $sessionId, $text, $doUserExists, $token);
}

// Print the response onto the page so that our gateway can read it
header('Content-type: text/plain');
echo $response;

// DONE!!!
function get_menu($phoneNumber, $session, $text, $userExists, $token)
{
  GLOBAL $server; GLOBAL $user; GLOBAL $pass; GLOBAL $db;
  $conn = new mysqli($server, $user, $pass, $db);
  //Check Input

  $stage_level = new_stage($session, $text);
  $response = "";
  
  //End Check Input
  $users_level = explode("*", $stage_level);
  $count_level = count($users_level);
  if ($count_level == 1) {
      if ( $text != "1" && $text != "2") {
          $response  = "END Muhitemo neza. \n";
      }
      else
      {
        //
          $lan = (($text == "1") ? "title_kinya":"title_en");
          $response  = "CON ".(($lan == "title_kinya") ? "Hitamo": "Choose")." \n";
          $serverRequest = sendAPIRequest(
            "POST",
            "/subscriptions/ussd-login",
            json_encode(array("phoneNumber" => $phoneNumber)),
            ""
          );
          $serverRequest = json_decode($serverRequest, true);
          $doUserExists = isset($serverRequest['error']) ? "no":"yes";
          user_exists($session, $doUserExists, $serverRequest['result']['token']);
          if($doUserExists == "no") {
            $response  = "CON ".(($lan == "title_kinya") ? "Iyandikishe": "Register")." \n";
            $response .= (($lan == "title_kinya") ? "Amazina": "Name")." \n";
          } else {
            $response  = "CON ".(($lan == "title_kinya") ? "Ikaze": "Welcome")." \n";
            $response  .= "1. ".(($lan == "title_kinya") ? get_element_translation("2", "kinya"):get_element_translation("2", "en") )." \n";
            $response  .= "2. ".(($lan == "title_kinya") ? get_element_translation("3", "kinya"):get_element_translation("3", "en") )." \n";
          }
        }
    }
    else
    {
        $lan = reset($users_level);
        $lan = (($lan == "1") ? "title_kinya":"title_en");
        if ($count_level == 2) {
          if($userExists == "no") {
            $names = end($users_level);
            $serverRequest = sendAPIRequest(
              "POST",
              "/subscriptions/ussd-create-subscription",
              json_encode(array("phoneNumber" => $phoneNumber, "name" => $names)),
              ""
            );
            $response = "END Successfully registered, You can now add student.";
          } else {
            if ($users_level[1] == "1") {
              $response  = "CON ".(($lan == "title_kinya") ? get_element_translation("4", "kinya"): get_element_translation("4", "en"))."\n";
            }
            if ($users_level[1] == "2") {
              $response  = "CON ".(($lan == "title_kinya") ? get_element_translation("3", "kinya"): get_element_translation("3", "en"))." \n";
              $serverRequest = sendAPIRequest(
                "POST",
                "/subscriptions/ussd-login",
                json_encode(array("phoneNumber" => $phoneNumber)),
                ""
              );
              $serverRequest = json_decode($serverRequest, true);
              $childrenList = $serverRequest['result']['information'][0]['parent'];
              $i = 1;
              foreach($childrenList as $childInfo) {
                $response .= $i.". ".$childInfo['name']." in ".$childInfo['class']['name']." at ".$childInfo['school']." \n";
                $i++;
              }
            }
          }
        }
        if ($count_level == 3) {
          if ($users_level[1] == "1") {
            $response  = "CON ".(($lan == "title_kinya") ? get_element_translation("5", "kinya"): get_element_translation("5", "en"))."\n";
          }
        }
        if ($count_level == 4) {
          if ($users_level[1] == "1") {
            $response  = "CON ".(($lan == "title_kinya") ? get_element_translation("6", "kinya"): get_element_translation("6", "en"))."\n";
          }
        }
        if ($count_level == 5) {
          if ($users_level[1] == "1") {
            $schoolName = end($users_level);
            $studentName = $users_level[count($users_level)-2];
            $studentClass = $users_level[count($users_level)-3];
            $serverRequest = sendAPIRequest(
              "GET",
              "/classes",
              json_encode(array("phoneNumber" => $phoneNumber)),
              "Authorization: Bearer ".$token
            );
            $serverRequest = json_decode($serverRequest, true);
            $classList = $serverRequest['result'];
            $classId = 0;
            $response  = "END ";
            foreach($classList as $class) {
              if(strtolower($class['name']) == strtolower($studentClass)) {
                $classId = $class['id'];
              }
            }
            if($classId == 0) {
              $response .= (($lan == "title_kinya") ? get_element_translation("8", "kinya"): get_element_translation("8", "en"))."\n";
            } else {
              $serverRequest = sendAPIRequest(
                "POST",
                "/students/create-student",
                json_encode(array("name" => $studentName, "school" => $schoolName, "classStudy" => $classId)),
                $token
              );
              // $serverRequest = json_decode($serverRequest, true);
              $response .= (($lan == "title_kinya") ? get_element_translation("7", "kinya"): get_element_translation("7", "en"))."\n";
            }
          }
        }
    }

  return (($response == "") ? "END Ibyo musabye ntibibashije kuboneka.": $response);
}
?>