<?php
$servername = "localhost";
$username = "root";          // padrão local, altere se for outro
$password = "";              // geralmente vazio no XAMPP
$dbname = "my_website";  // o banco que você criou/importou

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Conexão falhou: " . $conn->connect_error);
}
$conn->set_charset("utf8mb4");
?>
