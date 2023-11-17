<?php
/*
	Copyright (c) 2019-2023 Mark J Crane <markjcrane@fusionpbx.com>
	
	Redistribution and use in source and binary forms, with or without
	modification, are permitted provided that the following conditions
	are met:

		1. Redistributions of source code must retain the above copyright
		notice, this list of conditions and the following disclaimer.
	
		2. Redistributions in binary form must reproduce the above copyright
		notice, this list of conditions and the following disclaimer in the
		documentation and/or other materials provided with the distribution.

	THIS SOFTWARE IS PROVIDED BY THE AUTHOR AND CONTRIBUTORS "AS IS" AND
	ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE
	IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE
	ARE DISCLAIMED.  IN NO EVENT SHALL THE AUTHOR OR CONTRIBUTORS BE LIABLE
	FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL
	DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS
	OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION)
	HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT
	LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY
	OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF
	SUCH DAMAGE.
*/

//includes files
	require_once dirname(__DIR__, 2) . "/resources/require.php";
	require_once "resources/check_auth.php";

//check permissions
	if (permission_exists('device_log_add') || permission_exists('device_log_edit')) {
		//access granted
	}
	else {
		echo "access denied";
		exit;
	}

//add multi-lingual support
	$language = new text;
	$text = $language->get();

//action add or update
	if (!empty($_REQUEST["id"]) && is_uuid($_REQUEST["id"])) {
		$action = "update";
		$device_log_uuid = $_REQUEST["id"];
		$id = $_REQUEST["id"];
	}
	else {
		$action = "add";
	}

//get http post variables and set them to php variables
	if (is_array($_POST)) {
		$device_log_uuid = $_POST["device_log_uuid"] ?? null;
		$device_uuid = $_POST["device_uuid"] ?? null;
		$timestamp = $_POST["timestamp"] ?? '';
		$device_address = $_POST["device_address"] ?? '';
		$request_scheme = $_POST["request_scheme"] ?? '';
		$http_host = $_POST["http_host"] ?? '';
		$server_port = $_POST["server_port"] ?? '';
		$server_protocol = $_POST["server_protocol"] ?? '';
		$query_string = $_POST["query_string"] ?? '';
		$remote_address = $_POST["remote_address"] ?? '';
		$http_user_agent = $_POST["http_user_agent"] ?? '';
		$http_status = $_POST["http_status"] ?? '';
		$http_status_code = $_POST["http_status_code"] ?? '';
		$http_content_body = $_POST["http_content_body"] ?? '';
	}

//process the user data and save it to the database
	if (count($_POST) > 0 && strlen($_POST["persistformvar"]) == 0) {

		//delete the bridge
			if (permission_exists('bridge_delete')) {
				if ($_POST['action'] == 'delete' && is_uuid($device_log_uuid)) {
					//prepare
						$array[0]['checked'] = 'true';
						$array[0]['uuid'] = $device_log_uuid;
					//delete
						$obj = new device_logs;
						$obj->delete($array);
					//redirect
						header('Location: device_logs.php');
						exit;
				}
			}

		//get the uuid from the POST
			if ($action == "update") {
				$device_log_uuid = $_POST["device_log_uuid"];
			}

		//validate the token
			$token = new token;
			if (!$token->validate($_SERVER['PHP_SELF'])) {
				message::add($text['message-invalid_token'],'negative');
				header('Location: device_logs.php');
				exit;
			}

		//check for all required data
			$msg = '';
			//if (strlen($device_uuid) == 0) { $msg .= $text['message-required']." ".$text['label-device_uuid']."<br>\n"; }
			if (strlen($timestamp) == 0) { $msg .= $text['message-required']." ".$text['label-timestamp']."<br>\n"; }
			if (strlen($device_address) == 0) { $msg .= $text['message-required']." ".$text['label-device_address']."<br>\n"; }
			if (strlen($request_scheme) == 0) { $msg .= $text['message-required']." ".$text['label-request_scheme']."<br>\n"; }
			if (strlen($http_host) == 0) { $msg .= $text['message-required']." ".$text['label-http_host']."<br>\n"; }
			if (strlen($server_port) == 0) { $msg .= $text['message-required']." ".$text['label-server_port']."<br>\n"; }
			if (strlen($server_protocol) == 0) { $msg .= $text['message-required']." ".$text['label-server_protocol']."<br>\n"; }
			if (strlen($query_string) == 0) { $msg .= $text['message-required']." ".$text['label-query_string']."<br>\n"; }
			if (strlen($remote_address) == 0) { $msg .= $text['message-required']." ".$text['label-remote_address']."<br>\n"; }
			if (strlen($http_user_agent) == 0) { $msg .= $text['message-required']." ".$text['label-http_user_agent']."<br>\n"; }
			if (strlen($http_status) == 0) { $msg .= $text['message-required']." ".$text['label-http_status']."<br>\n"; }
			if (strlen($http_status_code) == 0) { $msg .= $text['message-required']." ".$text['label-http_status_code']."<br>\n"; }
			//if (strlen($http_content_body) == 0) { $msg .= $text['message-required']." ".$text['label-http_content_body']."<br>\n"; }
			if (strlen($msg) > 0 && strlen($_POST["persistformvar"]) == 0) {
				require_once "resources/header.php";
				require_once "resources/persist_form_var.php";
				echo "<div align='center'>\n";
				echo "<table><tr><td>\n";
				echo $msg."<br />";
				echo "</td></tr></table>\n";
				persistformvar($_POST);
				echo "</div>\n";
				require_once "resources/footer.php";
				return;
			}

		//add the device_log_uuid
			if (!is_uuid($_POST["device_log_uuid"])) {
				$device_log_uuid = uuid();
			}

		//prepare the array
			$array['device_logs'][0]['device_log_uuid'] = $device_log_uuid;
			$array['device_logs'][0]['domain_uuid'] = $_SESSION['domain_uuid'];
			$array['device_logs'][0]['device_uuid'] = $device_uuid;
			$array['device_logs'][0]['timestamp'] = $timestamp;
			$array['device_logs'][0]['device_address'] = $device_address;
			$array['device_logs'][0]['request_scheme'] = $request_scheme;
			$array['device_logs'][0]['http_host'] = $http_host;
			$array['device_logs'][0]['server_port'] = $server_port;
			$array['device_logs'][0]['server_protocol'] = $server_protocol;
			$array['device_logs'][0]['query_string'] = $query_string;
			$array['device_logs'][0]['remote_address'] = $remote_address;
			$array['device_logs'][0]['http_user_agent'] = $http_user_agent;
			$array['device_logs'][0]['http_status'] = $http_status;
			$array['device_logs'][0]['http_status_code'] = $http_status_code;
			$array['device_logs'][0]['http_content_body'] = $http_content_body;

		//save the data
			$database = new database;
			$database->app_name = 'device logs';
			$database->app_uuid = '78b1e5c7-5028-43e7-a05b-a36b44f87087';
			$database->save($array);
			//$message = $database->message;

		//redirect the user
			if (isset($action)) {
				if ($action == "add") {
					$_SESSION["message"] = $text['message-add'];
				}
				if ($action == "update") {
					$_SESSION["message"] = $text['message-update'];
				}
				header('Location: device_logs.php');
				//header('Location: device_log_edit.php?id='.urlencode($device_log_uuid));
				return;
			}
	}

//pre-populate the form
	if (is_array($_GET) && empty($_POST["persistformvar"])) {
		$device_log_uuid = $_GET["id"] ?? '';
		$sql = "select * from v_device_logs ";
		$sql .= "where device_log_uuid = :device_log_uuid ";
		//$sql .= "and domain_uuid = :domain_uuid ";
		//$parameters['domain_uuid'] = $_SESSION['domain_uuid'];
		$parameters['device_log_uuid'] = $device_log_uuid;
		$database = new database;
		$row = $database->select($sql, $parameters, 'row');
		if (is_array($row) && sizeof($row) != 0) {
			$device_uuid = $row["device_uuid"];
			$timestamp = $row["timestamp"];
			$device_address = $row["device_address"];
			$request_scheme = $row["request_scheme"];
			$http_host = $row["http_host"];
			$server_port = $row["server_port"];
			$server_protocol = $row["server_protocol"];
			$query_string = $row["query_string"];
			$remote_address = $row["remote_address"];
			$http_user_agent = $row["http_user_agent"];
			$http_status = $row["http_status"];
			$http_status_code = $row["http_status_code"];
			$http_content_body = $row["http_content_body"];
		}
		unset($sql, $parameters, $row);
	}

//create token
	$object = new token;
	$token = $object->create($_SERVER['PHP_SELF']);

//show the header
	$document['title'] = $text['title-device_log'];
	require_once "resources/header.php";

//show the content
	echo "<form name='frm' id='frm' method='post' action=''>\n";

	echo "<div class='action_bar' id='action_bar'>\n";
	echo "	<div class='heading'><b>".$text['title-device_log']."</b></div>\n";
	echo "	<div class='actions'>\n";
	echo button::create(['type'=>'button','label'=>$text['button-back'],'icon'=>$_SESSION['theme']['button_icon_back'],'id'=>'btn_back','style'=>'margin-right: 15px;','link'=>'device_logs.php']);
	if ($action == 'update' && permission_exists('device_log_delete')) {
		echo button::create(['type'=>'button','label'=>$text['button-delete'],'icon'=>$_SESSION['theme']['button_icon_delete'],'name'=>'btn_delete','style'=>'margin-right: 15px;','onclick'=>"modal_open('modal-delete','btn_delete');"]);
	}
	if ($action == 'update' && permission_exists('device_log_copy')) {
		echo button::create(['type'=>'button','label'=>$text['button-copy'],'icon'=>$_SESSION['theme']['button_icon_copy'],'name'=>'btn_copy','style'=>'margin-right: 15px;','link'=>'device_log_copy.php']);
	}
	echo button::create(['type'=>'submit','label'=>$text['button-save'],'icon'=>$_SESSION['theme']['button_icon_save'],'id'=>'btn_save','name'=>'action','value'=>'save']);
	echo "	</div>\n";
	echo "	<div style='clear: both;'>".$text['description-device_logs']."</div>\n";
	echo "</div>\n";
	if ($action == 'update' && permission_exists('device_log_delete')) {
		echo modal::create(['id'=>'modal-delete','type'=>'delete','actions'=>button::create(['type'=>'submit','label'=>$text['button-continue'],'icon'=>'check','id'=>'btn_delete','style'=>'float: right; margin-left: 15px;','collapse'=>'never','name'=>'action','value'=>'delete','onclick'=>"modal_close();"])]);
	}

	echo "<table width='100%'  border='0' cellpadding='0' cellspacing='0'>\n";


	echo "<tr>\n";
	echo "<td class='vncell' valign='top' align='left' nowrap='nowrap'>\n";
	echo "	".$text['label-device_uuid']."\n";
	echo "</td>\n";
	echo "<td class='vtable' style='position: relative;' align='left'>\n";
	echo "  <input class='formfld' type='text' name='device_uuid' maxlength='255' value='".escape($device_uuid)."'>\n";
	echo "<br />\n";
	echo $text['description-device_uuid']."\n";
	echo "</td>\n";
	echo "</tr>\n";

	echo "<tr>\n";
	echo "<td class='vncellreq' valign='top' align='left' nowrap='nowrap'>\n";
	echo "	".$text['label-timestamp']."\n";
	echo "</td>\n";
	echo "<td class='vtable' style='position: relative;' align='left'>\n";
	echo "  <input class='formfld' type='text' name='timestamp' maxlength='255' value='".escape($timestamp)."'>\n";
	echo "<br />\n";
	echo $text['description-timestamp']."\n";
	echo "</td>\n";
	echo "</tr>\n";

	echo "<tr>\n";
	echo "<td class='vncellreq' valign='top' align='left' nowrap='nowrap'>\n";
	echo "	".$text['label-device_address']."\n";
	echo "</td>\n";
	echo "<td class='vtable' style='position: relative;' align='left'>\n";
	echo "	<input class='formfld' type='text' name='device_address' maxlength='255' value='".escape($device_address)."'>\n";
	echo "<br />\n";
	echo $text['description-device_address']."\n";
	echo "</td>\n";
	echo "</tr>\n";

	echo "<tr>\n";
	echo "<td class='vncellreq' valign='top' align='left' nowrap='nowrap'>\n";
	echo "	".$text['label-request_scheme']."\n";
	echo "</td>\n";
	echo "<td class='vtable' style='position: relative;' align='left'>\n";
	echo "	<input class='formfld' type='text' name='request_scheme' maxlength='255' value='".escape($request_scheme)."'>\n";
	echo "<br />\n";
	echo $text['description-request_scheme']."\n";
	echo "</td>\n";
	echo "</tr>\n";

	echo "<tr>\n";
	echo "<td class='vncellreq' valign='top' align='left' nowrap='nowrap'>\n";
	echo "	".$text['label-http_host']."\n";
	echo "</td>\n";
	echo "<td class='vtable' style='position: relative;' align='left'>\n";
	echo "	<input class='formfld' type='text' name='http_host' maxlength='255' value='".escape($http_host)."'>\n";
	echo "<br />\n";
	echo $text['description-http_host']."\n";
	echo "</td>\n";
	echo "</tr>\n";

	echo "<tr>\n";
	echo "<td class='vncellreq' valign='top' align='left' nowrap='nowrap'>\n";
	echo "	".$text['label-server_port']."\n";
	echo "</td>\n";
	echo "<td class='vtable' style='position: relative;' align='left'>\n";
	echo "	<input class='formfld' type='text' name='server_port' maxlength='255' value='".escape($server_port)."'>\n";
	echo "<br />\n";
	echo $text['description-server_port']."\n";
	echo "</td>\n";
	echo "</tr>\n";

	echo "<tr>\n";
	echo "<td class='vncellreq' valign='top' align='left' nowrap='nowrap'>\n";
	echo "	".$text['label-server_protocol']."\n";
	echo "</td>\n";
	echo "<td class='vtable' style='position: relative;' align='left'>\n";
	echo "	<input class='formfld' type='text' name='server_protocol' maxlength='255' value='".escape($server_protocol)."'>\n";
	echo "<br />\n";
	echo $text['description-server_protocol']."\n";
	echo "</td>\n";
	echo "</tr>\n";

	echo "<tr>\n";
	echo "<td class='vncellreq' valign='top' align='left' nowrap='nowrap'>\n";
	echo "	".$text['label-query_string']."\n";
	echo "</td>\n";
	echo "<td class='vtable' style='position: relative;' align='left'>\n";
	echo "	<input class='formfld' type='text' name='query_string' maxlength='255' value='".escape($query_string)."'>\n";
	echo "<br />\n";
	echo $text['description-query_string']."\n";
	echo "</td>\n";
	echo "</tr>\n";

	echo "<tr>\n";
	echo "<td class='vncellreq' valign='top' align='left' nowrap='nowrap'>\n";
	echo "	".$text['label-remote_address']."\n";
	echo "</td>\n";
	echo "<td class='vtable' style='position: relative;' align='left'>\n";
	echo "	<input class='formfld' type='text' name='remote_address' maxlength='255' value='".escape($remote_address)."'>\n";
	echo "<br />\n";
	echo $text['description-remote_address']."\n";
	echo "</td>\n";
	echo "</tr>\n";

	echo "<tr>\n";
	echo "<td class='vncellreq' valign='top' align='left' nowrap='nowrap'>\n";
	echo "	".$text['label-http_user_agent']."\n";
	echo "</td>\n";
	echo "<td class='vtable' style='position: relative;' align='left'>\n";
	echo "	<input class='formfld' type='text' name='http_user_agent' maxlength='255' value='".escape($http_user_agent)."'>\n";
	echo "<br />\n";
	echo $text['description-http_user_agent']."\n";
	echo "</td>\n";
	echo "</tr>\n";

	echo "<tr>\n";
	echo "<td class='vncellreq' valign='top' align='left' nowrap='nowrap'>\n";
	echo "	".$text['label-http_status']."\n";
	echo "</td>\n";
	echo "<td class='vtable' style='position: relative;' align='left'>\n";
	echo "	<input class='formfld' type='text' name='http_status' maxlength='255' value='".escape($http_status)."'>\n";
	echo "<br />\n";
	echo $text['description-http_status']."\n";
	echo "</td>\n";
	echo "</tr>\n";

	echo "<tr>\n";
	echo "<td class='vncellreq' valign='top' align='left' nowrap='nowrap'>\n";
	echo "	".$text['label-http_status_code']."\n";
	echo "</td>\n";
	echo "<td class='vtable' style='position: relative;' align='left'>\n";
	echo "	<input class='formfld' type='text' name='http_status_code' maxlength='255' value='".escape($http_status_code)."'>\n";
	echo "<br />\n";
	echo $text['description-http_status_code']."\n";
	echo "</td>\n";
	echo "</tr>\n";

	echo "<tr>\n";
	echo "<td class='vncell' valign='top' align='left' nowrap='nowrap'>\n";
	echo "	".$text['label-http_content_body']."\n";
	echo "</td>\n";
	echo "<td class='vtable' style='position: relative;' align='left'>\n";
	echo "	<textarea class='formfld' name='http_content_body' style='width: 100%; height: 300px; max-width: 5000px;'>".$http_content_body."</textarea>\n";
	echo "<br />\n";
	echo $text['description-http_content_body']."\n";
	echo "</td>\n";
	echo "</tr>\n";

	echo "</table>";
	echo "<br /><br />";

	if ($action == "update") {
		echo "<input type='hidden' name='device_log_uuid' value='".escape($device_log_uuid)."'>\n";
	}
	echo "<input type='hidden' name='".$token['name']."' value='".$token['hash']."'>\n";

	echo "</form>";

//include the footer
	require_once "resources/footer.php";

?>
