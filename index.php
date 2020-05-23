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
   $message = "CON Ikaze kuri ITEGURE. \n";
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
            $serverRequest = sendAPIRequest(
              "POST",
              "/subscriptions/ussd-create-subscription",
              json_encode(array("phoneNumber" => $phoneNumber, "name" => "Parent")),
              ""
            );
          }
          $response = "CON ".(($lan == "title_kinya") ? get_element_translation("2", "kinya"):get_element_translation("2", "en") )." \n";
        }
    }
    else
    {
        $lan = reset($users_level);
        $lan = (($lan == "1") ? "title_kinya":"title_en");
        if ($count_level == 2) {
          $studentClass = preg_replace('/\s+/', '', end($users_level));
          $serverRequest = sendAPIRequest(
            "GET",
            "/classes",
            "",
            "Authorization: Bearer ".$token
          );
          $serverRequest = json_decode($serverRequest, true);
          $classList = $serverRequest['result'];
          $classId = 0;
          foreach($classList as $class) {
            if(strtolower($class['name']) == strtolower($studentClass)) {
              $classId = $class['id'];
            }
          }
          if($classId == 0) {
            $response = "END ".(($lan == "title_kinya") ? get_element_translation("8", "kinya"): get_element_translation("8", "en"))."\n";
          } else {
            //
            $serverRequest = sendAPIRequest(
              "GET",
              "/timetable/upcoming-lessons/".$classId,
              "",
              "Authorization: Bearer ".$token
            );
            $serverRequest = json_decode($serverRequest, true);
            $upcomingList = $serverRequest['result'];
            //
            $response = "CON ";
            if(count($upcomingList) != 0) {
              $response .= (($lan == "title_kinya") ?"Gahunda:":"Upcoming lesson:");
              $response .= "\n";
              foreach($upcomingList as $upcoming) {
                $startDate = $upcoming['timeFrom'];
                $subjectName = $upcoming['subjectKeyId']['name'];
                $stationName = $upcoming['stationKeyId']['name'];
                $response .= "- ".$subjectName." ".(($lan == "title_kinya") ?"kuri":"on")." ".$stationName. ", ".$startDate." \n";
              }
            }
            $serverRequest = sendAPIRequest(
              "POST",
              "/subscriptions/ussd-login",
              json_encode(array("phoneNumber" => $phoneNumber)),
              ""
            );
            $serverRequest = json_decode($serverRequest, true);
            $childrenList = $serverRequest['result']['information'][0]['parent'];
            $classFound = 0;
            foreach($childrenList as $child) {
              if($child['class']['id'] == $classId ) {
                $classFound = $classId;
              }
            }
            //
            $add_where = ($classFound == 0) ? "id=3":"id=4";
            $query="SELECT * FROM menus WHERE parent_id = 'MENU' AND $add_where ORDER BY id ASC";
            $query = $conn->query($query);
            while ($rowSingle = $query->fetch_assoc()) {
                $response .= $rowSingle['choice'].". ".$rowSingle[$lan]." \n";
            }
            $response .= "00. ".(($lan == "title_kinya") ? get_element_translation("5", "kinya"):get_element_translation("5", "en") )." \n";
            
          }
          //
        }
        if ($count_level == 3) {
          if(end($users_level) == "1") {
            //
            $studentClass = preg_replace('/\s+/', '', $users_level[1]);
            $serverRequest = sendAPIRequest(
              "GET",
              "/classes",
              "",
              "Authorization: Bearer ".$token
            );
            $serverRequest = json_decode($serverRequest, true);
            $classList = $serverRequest['result'];
            $classId = 0;
            foreach($classList as $class) {
              if(strtolower($class['name']) == strtolower($studentClass)) {
                $classId = $class['id'];
              }
            }

            $serverRequest = sendAPIRequest(
              "POST",
              "/subscriptions/ussd-login",
              json_encode(array("phoneNumber" => $phoneNumber)),
              ""
            );
            $serverRequest = json_decode($serverRequest, true);
            $childrenList = $serverRequest['result']['information'][0]['parent'];
            $classFound = 0;
            $studentId = 0;
            foreach($childrenList as $child) {
              if($child['class']['id'] == $classId ) {
                $classFound = $classId;
                $studentId = $child['studentId'];
              }
            }

            if($classFound == 0) {
              $serverRequest = sendAPIRequest(
                "POST",
                "/students/create-student",
                json_encode(array("name" => "Student Name", "school" => "School Name", "classStudy" => $classId)),
                $token
              );
              $response = "END ".(($lan == "title_kinya") ? get_element_translation("6", "kinya"): get_element_translation("6", "en"))."\n";
            } else {
              $serverRequest = sendAPIRequest(
                "DELETE",
                "/students/delete-student/".$studentId,
                json_encode(array()),
                $token
              );
              $response = "END ".(($lan == "title_kinya") ? get_element_translation("7", "kinya"): get_element_translation("7", "en"))."\n";
            }
          } else {
            $response .= "END ".(($lan == "title_kinya") ? get_element_translation("9", "kinya"):get_element_translation("9", "en") )." \n";
          }
        }
    }

  return (($response == "") ? "END Ibyo musabye ntibibashije kuboneka.": $response);
}
?>