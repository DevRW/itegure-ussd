<?php 
function check_session($session_id, $phone_number)
{
  GLOBAL $server; GLOBAL $user; GLOBAL $pass; GLOBAL $db;
  $conn = new mysqli($server, $user, $pass, $db);
  $query="SELECT id FROM sessions WHERE session = '$session_id' AND phone = '$phone_number'";
  $query = $conn->query($query);
  $nums = $query->num_rows;
  $row = $query->fetch_array();
  $registered_session = (($nums == 0) ? new_session($session_id, $phone_number): $row['id']);
  return $registered_session;
}
function new_session($session, $phone)
{
  if ($session == "" || $phone == "") {
    return "0";
  }
  GLOBAL $server; GLOBAL $user; GLOBAL $pass; GLOBAL $db;
  $conn = new mysqli($server, $user, $pass, $db);
  $query = "INSERT INTO sessions(session, phone) VALUES('$session', '$phone')";
  $query = $conn->query($query);
  return "0";
}
function new_stage($session, $text)
{
  GLOBAL $server; GLOBAL $user; GLOBAL $pass; GLOBAL $db;
  $conn = new mysqli($server, $user, $pass, $db);
  $query="SELECT id, stage FROM stage WHERE session = '$session'";
  $query = $conn->query($query);
  $row = $query->fetch_array();
  $nums = $query->num_rows;
  if ($nums == 0) {
    $query="INSERT INTO stage(session, stage) VALUES('$session', '$text')";
    $query = $conn->query($query);
  }
  else{
    $query="UPDATE stage SET stage = '$text' WHERE session = '$session'";
    $query = $conn->query($query);
  }
  $query="SELECT stage FROM stage WHERE session = '$session'";
  $query = $conn->query($query);
  $row = $query->fetch_array();
  return $row['stage'];
}

function get_element_translation($element, $lang)
{
  GLOBAL $server; GLOBAL $user; GLOBAL $pass; GLOBAL $db;
  $conn = new mysqli($server, $user, $pass, $db);
  $lang = (($lang == "en") ? "title_en": "title_kinya");
  $query="SELECT $lang as value FROM menus WHERE id ='$element'";
  $query = $conn->query($query);
  $rowSingle = $query->fetch_array();
  $response = $rowSingle['value'];
  return $response;
}

function user_exists($session, $exist, $token)
{
  GLOBAL $server; GLOBAL $user; GLOBAL $pass; GLOBAL $db;
  $conn = new mysqli($server, $user, $pass, $db);
  $query="SELECT id FROM user_exists WHERE session = '$session'";
  $query = $conn->query($query);
  $row = $query->fetch_array();
  $nums = $query->num_rows;
  if ($nums == 0) {
    $query="INSERT INTO user_exists(session, exist, token) VALUES('$session', 'n/a', '')";
    $query = $conn->query($query);
  }
  if($exist != "") {
    $query="UPDATE user_exists SET exist = '$exist' WHERE session = '$session'";
    $query = $conn->query($query);
  }
  if($token != "") {
    $query="UPDATE user_exists SET token = '$token' WHERE session = '$session'";
    $query = $conn->query($query);
  }
  $query="SELECT exist, token FROM user_exists WHERE session = '$session'";
  $query = $conn->query($query);
  $row = $query->fetch_array();
  return array($row['exist'], $row['token']);
}

function sendAPIRequest($method, $url, $data, $token){
  $url = $GLOBALS['backend_url'].$url;
  $curl = curl_init();
  switch ($method){
     case "POST":
        curl_setopt($curl, CURLOPT_POST, 1);
        if ($data)
           curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
        break;
     case "PUT":
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "PUT");
        if ($data)
           curl_setopt($curl, CURLOPT_POSTFIELDS, $data);			 					
        break;
     default:
        if ($data)
           $url = sprintf("%s?%s", $url, http_build_query($data));
  }
  // OPTIONS:
  curl_setopt($curl, CURLOPT_URL, $url);
  $header = array(
    'Content-Type: application/json',
  );
  if ($token != "") {
    $header = array(
      "Authorization: Bearer $token",
      "Content-Type: application/json",
    );
  }
  curl_setopt($curl, CURLOPT_HTTPHEADER, $header);
  curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
  curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
  // EXECUTE:
  $result = curl_exec($curl);
  if(!$result){die("Connection Failure");}
  curl_close($curl);
  return $result;
}
?>