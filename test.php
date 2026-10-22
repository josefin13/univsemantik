<?php
session_start();
$_SESSION['hitung'] = ($_SESSION['hitung'] ?? 0) + 1;
echo $_SESSION['hitung'];