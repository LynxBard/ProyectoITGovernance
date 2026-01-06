<?php
   // Si existen variables de entorno (Nube), úsalas. Si no, usa localhost (Local)
   $host = getenv('DB_HOST') ?: "localhost";
   $user = getenv('DB_USER') ?: "root";
   $password = getenv('DB_PASS') ?: "";
   $bd = getenv('DB_NAME') ?: "Camaras"; // O "ti", según corresponda

   $conectar = mysqli_connect($host, $user, $password, $bd);

   if (!$conectar) {
       die("Error de conexión: " . mysqli_connect_error());
   }
?>