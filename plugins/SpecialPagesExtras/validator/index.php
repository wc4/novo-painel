<?php
//async validation entry point

include('../../../admin/inc/common.php');
$loggedin = cookie_check();
if (!$loggedin) die('not logged!');

require_once('../SPEValidator.php');
require_once('../SPESettings.php');

$res = SPEValidator::editValidateData(SPESettings::load(@$_POST['post-special']));

echo json_encode($res); 