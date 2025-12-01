<?php
// index.php - Contador de visitas simples (cookie + bloqueo de archivo)

// -------- CONFIGURACIÓN --------
$contador = __DIR__ . '/contador.txt';   // archivo donde se almacena el contador ya que el contador es global y no de usuario
$cookieName  = 'visited';                  // nombre de la cookie
$cookieTTL   = 24 * 60 * 60;               // tiempo de vida de la cookie en segundos (24h)
// 'secure' => true  si usasemos un sitio con https pondriamos el secure para solo permitir las cookies en conexiones seguras


if (!file_exists($contador)) {
    // crea con valor 0 si no existe
    file_put_contents($contador, "0");
    // intenta ajustar permisos . los permisos 0664 permiten lectura y escritura al propietario y grupo, y solo lectura a otros
    @chmod($contador, 0664);
}

// verificamos si el usuario ya tiene la cookie
$shouldCount = false;
if (!isset($_COOKIE[$cookieName])) {
    // No tiene cookie => contamos esta visita única
    $shouldCount = true;

    // Poner la cookie antes de cualquier salida al navegador
    setcookie($cookieName, '1', [
        'expires'  => time() + $cookieTTL,
        'path'     => '/',
        'httponly' => true,
        //'secure'   => true, // activa en producción con HTTPS
        'samesite' => 'Lax'
    ]);
}

// si hay que contar, abrimos el archivo y actualizamos el contador
if ($shouldCount) {
    // Abrimos en modo c+ (lectura/escritura, crea si no existe)
    $fp = fopen($contador, 'c+');
    if ($fp === false) {
        error_log("ERROR: no se pudo abrir el archivo del contador.");
    } else {
        // bloqueo exclusivo para evitar condiciones de carrera
        if (flock($fp, LOCK_EX)) {
            // leer valor actual desde el inicio
            rewind($fp);
            $raw = stream_get_contents($fp);
            $count = intval(trim($raw === '' ? '0' : $raw));

            $count++; // incrementamos

            // escribir nuevo valor: truncar y escribir desde el inicio
            rewind($fp);
            ftruncate($fp, 0);
            fwrite($fp, (string)$count);
            fflush($fp);        // asegurar que se escriba en disco
            flock($fp, LOCK_UN);
        } else {
            error_log("WARNING: no se pudo bloquear el archivo del contador.");
        }
        fclose($fp);
    }
}

// leemos el valor actual de la cookie para mostrarlo en la pantalla
$current = 0;
if (is_readable($contador)) {
    $current = intval(file_get_contents($contador));
}
?>

<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <title>Contador de visitas</title>
</head>
<body>
  <h1>Contador de visitas</h1>
  <p>Visitas únicas (por cookie, <?= ($cookieTTL/3600) ?>h): <strong><?= htmlspecialchars($current, ENT_QUOTES) ?></strong></p>
  <p style="color:gray;font-size:0.9em">Refresca la página: no sumará. Borra las cookies o abre en incógnito para simular otro visitante.</p>
</body>
</html>
