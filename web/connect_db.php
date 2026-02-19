<?php 
$hostname = "localhost";
$username = "root";
$password = "";
$bd = "travel";

$conectare = mysqli_connect($hostname, $username, $password, $bd)
or die("Eroare! Conexiunea la baza de date a eșuat. Verifică parametrii.");

if (!$conectare) {
    die("Connection failed: " . mysqli_connect_error());
}
?>