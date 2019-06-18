<?php

require_once('./logoutClass.php');

$logout = new logoutClass();
$logout->execute();

header('Location: ./login.php');
exit;