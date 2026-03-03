<?php

require __DIR__ . '/../vendor/autoload.php';

try {
    // Obtener el HTML de la página
    $url = 'https://datosmacro.expansion.com/divisas';
    $html = file_get_contents($url);

    file_put_contents('contenido_datosmacro.html', $html);

    echo "El contenido HTML se ha guardado en contenido_datosmacro.html". "<br>";

    // Cargar el HTML en DOMDocument
    $dom = new DOMDocument();
    @$dom->loadHTML($html);  // @ evita warnings por HTML malformado

    // Inicializar DOMXPath para hacer consultas XPath
    $xpath = new DOMXPath($dom);

    // Lista de monedas con sus respectivos XPath completos
    $monedas_xpaths = [
        'Peso argentino' => '//*[@id="tb1_287"]/tbody/tr[9]/td[3]',
        'Real brasilero' => '//*[@id="tb1_287"]/tbody/tr[20]/td[3]',
        'Peso chileno' => '//*//*[@id="tb1_287"]/tbody/tr[27]/td[3]',
        'Peso colombiano' => '//*[@id="tb1_287"]/tbody/tr[28]/td[3]',
        'Peso mexicano' => '//*[@id="tb1_287"]/tbody/tr[60]/td[3]',
        'Sol peruano' => '//*[@id="tb1_287"]/tbody/tr[75]/td[3]',
        'Bolívar venezolano' => '//*[@id="tb1_287"]/tbody/tr[98]/td[3]',
        'Bolíviano de Bolivia' => '//*[@id="tb1_287"]/tbody/tr[98]/td[3]',
        'Peso uruguayo' => '//*[@id="tb1_287"]/tbody/tr[96]/td[3]',
        'Guarani' => '//*[@id="tb1_287"]/tbody/tr[74]/td[3]',
        'Euro' => '//*[@id="tb1_287"]/tbody/tr[2]/td[3]',   
    ];

    function loadEnv($file) {
        if (file_exists($file)) {
            $lines = file($file);
            foreach ($lines as $line) {
                $line = trim($line);
                if ($line && $line[0] !== '#' && strpos($line, '=') !== false) {
                    list($key, $value) = explode('=', $line, 2);
                    $key = trim($key);
                    $value = trim($value);
                    if (!empty($key)) {
                        $_ENV[$key] = $value;
                    }
                }
            }
        }
    }
    
    // Cargar el archivo .env
    loadEnv(__DIR__ . '/../.env');
    //print_r($_ENV);
    try {
        // Crear una instancia de PDO usando las variables de entorno
        $dsn = "mysql:host=" . env('DB_HOST') . ";dbname=" . env('DB_NAME');
        $pdo = new PDO($dsn, env('DB_USERNAME'), env('DB_PASSWORD'));
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
        echo "<br>"."Acceso correcto a la BBDD"."<br>";
    } catch (PDOException $e) {
        die('Error de conexión: ' . $e->getMessage());
    }
    
    echo "<br>"."Listado de actualizacion:"."<br>";
    // Recorremos cada moneda y su XPath para obtener su valor de cambio
    foreach ($monedas_xpaths as $nombre => $xpath_query) {
        // Ejecutar la consulta XPath
        $nodes = $xpath->query($xpath_query);

        if ($nodes->length > 0) {
            // Extraer el valor del cambio
            $cambio = trim($nodes->item(0)->textContent);
            $cambio = preg_replace('/(\,\d{2})\d*/', '$1', $cambio);

            // Actualizar el valor en la base de datos
            $stmt = $pdo->prepare('UPDATE tipocambio SET cambio = :cambio WHERE moneda = :moneda');
            $stmt->execute([
                ':cambio' => $cambio,
                ':moneda' => $nombre
            ]);
            
            echo "Moneda: $nombre, Cambio: $cambio actualizado."."<br>";
        } else {
            echo "No se encontró el valor de cambio para $nombre."."<br>";
        }
    }

    echo "<br>".'Tasas de cambio actualizadas correctamente.';

} catch (Exception $e) {
    echo 'Error: ' . $e->getMessage();
}
?>
