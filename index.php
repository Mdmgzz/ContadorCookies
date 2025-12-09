<?php

// si la cookie no existe 
if (!isset($_COOKIE['contador'])) {

    setcookie('Contador','1');
}else{
    $_COOKIE['contador']++;
}

//otro metodo

/*

Nuevo operador de fusión null coalescente (??) disponible desde PHP 7
$contador = $_COOKIE['Contador'] ?? 0;
$contador++;

*/


// si la cookie no existe 
$contador = $_COOKIE['contador'] ?? 0;
$contador++;
setcookie('contador', (string)$contador, [
    'expires'  => time() + (60 * 60 * 24 * 365),
    'path'     => '/',
    'httponly' => true,
    //'secure'   => true, // activa en producción con HTTPS
    'samesite' => 'Lax'
]);



?>

<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <title>Contador de visitas</title>
</head>
<body>
  <h1>Contador de visitas</h1>
<p><?$contador; ?></p>
</body>
</html>
