<?php
   $host = getenv('DB_HOST') ?: "localhost";
   $user = getenv('DB_USER') ?: "root";
   $password = getenv('DB_PASS') ?: "";
   $bd = getenv('DB_NAME') ?: "Camaras";

   // 1. Inicializar MySQLi
   $conectar = mysqli_init();

   // 2. Configurar SSL
   // Azure App Service en Linux suele tener los certificados aquí:
   mysqli_ssl_set($conectar, NULL, NULL, "/etc/ssl/certs/ca-certificates.crt", NULL, NULL);

   // 3. Conectar usando real_connect
   // Nota: Se añade el puerto 3306 y la bandera MYSQLI_CLIENT_SSL
   if (!mysqli_real_connect($conectar, $host, $user, $password, $bd, 3306, NULL, MYSQLI_CLIENT_SSL)) {
       die("Error de conexión (" . mysqli_connect_errno() . "): " . mysqli_connect_error());
   }
?>
