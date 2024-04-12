<?php
session_start();
unset($_SESSION["user_id"]);
unset($_SESSION["username"]);
unset($_SESSION["usertype"]);
session_unset();
session_destroy();
header("Location: ../index.php");