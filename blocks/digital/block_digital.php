<?php
/**
 * Bloque para mostrar el enlace de eLibro para usuarios autenticados.
 *
 * @package   block_digital
 * @copyright 1999 onwards Martin Dougiamas (http://dougiamas.com)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

class block_digital extends block_base {

    function init() {
        $this->title = get_string('pluginname', 'block_digital');
    }

    /**
     * Verifica si el usuario puede agregar una nueva instancia del bloque.
     *
     * @return bool
     */
    function instance_allow_multiple() {
        return true;
    }

    /**
     * Capacidad requerida para agregar una instancia del bloque.
     *
     * @return array
     */
    function get_required_capabilities() {
        return array('block/digital:myaddinstance');
    }

    /**
     * Devuelve las acciones permitidas en el bloque.
     *
     * @return array
     */
    function applicable_formats() {
        return array('all' => true);
    }

    function write_debug_log($message) {
        global $CFG;
        if (isset($CFG->debug_block_digital)) {

            $logpath = $CFG->debug_block_digital;

            if ($file = fopen($logpath, 'a')) {
                // Verificar el tipo de dato y formatear el mensaje en consecuencia
                if (is_array($message) || is_object($message)) {
                    $formatted_message = var_export($message, true);
                } else {
                    $formatted_message = $message;
                }

                if (fwrite($file, date('Y-m-d H:i:s') . ' - ' . $formatted_message . PHP_EOL) === false) {
                    echo 'Error al escribir en el archivo de registro.';
                }

                fclose($file);
            } else {
                echo 'Error al abrir el archivo de registro.';
            }
        }
    }

    function get_elibro_sso_data() {
        global $USER, $CFG;
        if(isset($CFG->resetcache_block_digital)) {
            $reiniciarCache_urlApi = $CFG->resetcache_block_digital;
        }else{
            $reiniciarCache_urlApi = false;
        }
        // Inicia proceso para registro/autenticación en eLibro...
        $this->write_debug_log('Inicia proceso para registro/autenticación en eLibro...');

        if (!isset($CFG->url_block_digital)) {
            $this->write_debug_log('Parámetro faltante en config.php: $CFG->url_block_digital');
            throw new Exception('Parámetro faltante en config.php: $CFG->url_block_digital');
            return false;
        }
        $api_url =  $CFG->url_block_digital.'auth/sso/?next=https://elibro.net/es/lc/unadmexico/titulos/11508';

        // Intenta obtener los resultados de la caché.
        $cache_key = 'elibro_sso_' . $USER->id;
        $this->write_debug_log('Cache_key = ' . $cache_key);

        $max_retries = 1;
        $retry_count = 0;
        $ch = null;

        try {
            if ($cached_data = get_user_preferences($cache_key, false)) {
                $this->write_debug_log('¡Datos en caché encontrados!');
                $cached_data = json_decode($cached_data, true);

                // Verificar si la respuesta contiene el campo 'url'.
                if (isset($cached_data['url']) && !empty($cached_data['url'])) {
                    $debug_message = 'Datos en caché:';
                    $debug_message .= PHP_EOL . 'data: ' . print_r($cached_data, true);
                    $this->write_debug_log($debug_message);
                    if ($reiniciarCache_urlApi) {
                        $debug_message = PHP_EOL . 'Se reiniciará la URL de API eLibro por la especificada en config.php|$CFG->url_block_digital: ' . $CFG->url_block_digital;
                        $debug_message .= PHP_EOL . 'Se eliminará la Cache_key(' . $cache_key . ') de la caché...';
                        set_user_preference($cache_key, false);
                        $this->write_debug_log($debug_message);
                        // Limpia la caché en caso de un error de formato.
                        $cache_key = 'elibro_sso_' . $USER->id;
                        $this->write_debug_log('Asignar nuevamente Cache_key = ' . $cache_key);
                    } else {
                        $debug_message = 'URL de API eLibro: ' . $CFG->url_block_digital;
                        $debug_message .= PHP_EOL . 'Envía datos a autenticarse sin eliminar caché y sin renovar sesión. ';
                        $this->write_debug_log($debug_message);
                        return $cached_data;
                    }
                } else {
                    $this->write_debug_log('El usuario aún no está registrado en eLibro.');
                }
            }
            $this->write_debug_log('Inicia codificación de datos para registro...');
            $data = array(
                'secret' => '53daQhTwd0mroC2EhMj8Po1H',
                'channel_id' => 'f87be48c-907d-43c8-aecc-20af4bac9258',
                'user' => $USER->email,
                'url_api' => $api_url
            );

            $this->write_debug_log('data = ' . print_r($data, true));
            $json_data = json_encode($data);

            // Reemplazar 'YOUR_AUTH_TOKEN' con el valor real del token.
            $token = '63a5d82fb86114c8cd6cbbd6645389f9506245a5';

            $this->write_debug_log('Inicializar la sesión cURL....');
            // Inicializar la sesión cURL.
            $ch = curl_init($api_url);

            // Configurar opciones de cURL.
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $json_data);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                'Content-Type: application/json',
                'Authorization: Token ' . $token, // Incluir el token en la cabecera de Autorización.
            ));
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 5); // Tiempo máximo de ejecución en segundos
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 2); // Tiempo máximo de espera para la conexión en segundos
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true); // Seguir redirecciones

            while ($retry_count < $max_retries) {
                $response = curl_exec($ch);

                // Verificar si la respuesta contiene un error
                if (curl_errno($ch)) {
                    $error_message = 'Error cURL: ' . curl_error($ch);
                    $error_message .= PHP_EOL . 'Intento  '.($retry_count+1). ' fallido por error en cURL.';
                    $this->write_debug_log($error_message);
                    // Incrementar el contador de reintento
                    $retry_count++;
                    continue;  // Volver al inicio del bucle para el próximo intento
                }

                // Decodificar la respuesta JSON
                $response_data = json_decode($response, true);
                $data_message = 'Datos de espuesta:';
                $data_message .= PHP_EOL . 'Datos: ' . print_r($response_data, true);
                $this->write_debug_log($data_message);

                // Verificar si la respuesta contiene la URL
                if (isset($response_data['url']) && !empty($response_data['url'])) {
                    $success_message = 'Datos de respuesta devueltos exitosamente.';
                    $success_message .= PHP_EOL . 'Se almacena el resultados en caché para futuras solicitudes: ' . print_r($response_data, true);
                    // Almacena los resultados en caché para futuras solicitudes.
                    set_user_preference($cache_key, json_encode($response_data));
                    $this->write_debug_log($success_message);
                    return $response_data;
                } else {
                    $error_message = 'Formato de respuesta inesperado - Error al conectar con la API eLibro:';
                    //$error_message .= PHP_EOL . 'Datos: ' . print_r($data, true);
                    //$error_message .= PHP_EOL . 'Respuesta: ' . $response;
                    $this->write_debug_log($error_message);
                }

                $this->write_debug_log('Intento  '.($retry_count+1).', fallido por respuesta inesperada de API eLibro.');
                // Limpia la caché en caso de un error de formato.
                set_user_preference($cache_key, false);
                $this->write_debug_log('Limpia la caché y realiza un nuevo intento.');
                // Incrementar el contador de reintento si la respuesta no es válida
                $retry_count++;
            }

            // Verificar si se agotaron los intentos y mostrar un mensaje de error
            if ($retry_count >= $max_retries) {
                $this->write_debug_log('Intentos de registro/autenticación superados.');
            }
        } catch (Exception $e) {
            $this->write_debug_log('Excepción capturada: ' . $e->getMessage());
            return false;
        } finally {
            // Cierra la sesión cURL.
            if ($ch) {
                curl_close($ch);
            }
        }
    }
    
    function get_content() {
        require_once('classes/event/connect_digital_elibro.php');

        global $USER;

        $content = '';
        $this->content = new stdClass;
        // Verificar si el usuario está autenticado.
        if (isloggedin()) {
            $this->content->text = '<style>
              .bilioteca-btn-acceder{
                  padding: 10px;
                  background: var(--color_primary);
                  color: white;
                  border-radius: 5px;
              }
              .bilioteca-btn-acceder:hover{
                color: white;
              }
             
            </style>';
            $fullname = fullname($USER);

            // Verificar si el correo electrónico está configurado.
            if (!empty($USER->email)) {
                try {
                    // Utiliza la función para obtener datos del servicio SSO de eLibro.
                    $response_data =  $this->get_elibro_sso_data();

                    // Verificar si la respuesta contiene el campo 'url'.
                    if (isset($response_data['url']) && !empty($response_data['url'])) {
                        // Agregar enlace al servicio eLibro.
                        $this->content->text .= '<label > Bienvenida(o): </label> ' . $fullname . '<br><br>';
//                        $this->content->text =  $fullname . '<br>';
                        $this->content->text .= '<a class="bilioteca-btn-acceder" href="' . $response_data['url'] . '" target="_blank"><b>Acceder eLibro</b></a>';
                        // Inicia proceso para registro/autenticación en eLibro...
                        $this->write_debug_log('Finaliza proceso exitoso para registro/autenticación en eLibro.');
                        $message = 'Finaliza proceso exitoso para registro/autenticación en eLibro';
                        //Creación de log de bitácoras
                        global $USER,$COURSE;

                        $event =  \block_digital\event\connect_digital_elibro::create(array(
                            'context' => context_course::instance($COURSE->id),
                            'other' => array('message'=>$message,
                            ),
                            'userid'  => $USER->id,
                        ));
                        $event->trigger();
                    } else {
                        //Creación de log de bitácoras
                        global $USER,$COURSE;
                        $message = 'Finaliza proceso fallido para registro/autenticación en eLibro';
                        $event =  \block_digital\event\connect_digital_elibro::create(array(
                            'context' => context_course::instance($COURSE->id),
                            'other' => array('message'=>$message,
                            ),
                            'userid'  => $USER->id,
                        ));
                        $event->trigger();
                        /*$message = 'Finaliza proceso exitoso para registro/autenticación en eLibro.';
                        //Creación de log de bitácoras
                        global $USER,$COURSE;
                        $event =  \block_digital\event\connect_digital_elibro::create(array(
                            'context' => context_course::instance($COURSE),
                            'other' => array('message'=>$message,
                            ),
                            'userid'  => $USER->id,
                        ));
                        $event->trigger();*/
                        $error_message = 'Servicio temporalmente no disponible, intente más tarde!';
                        $this->write_debug_log('Finaliza proceso fallido para registro/autenticación en eLibro.');
                        $this->write_debug_log($error_message);
                        throw new Exception($error_message);
                    }
                } catch (Exception $e) {
                    // Capturar excepciones y mostrar mensaje de error.
                    $this->content->text = '<br>' . $e->getMessage();
                    //Creación de log de bitácoras
                    $message = $e->getMessage();
                    global $USER,$COURSE;

                    $event =  \block_digital\event\connect_digital_elibro::create(array(
                        'context' => context_course::instance($COURSE->id),
                        'other' => array('message'=>$message,
                        ),
                        'userid'  => $USER->id,
                    ));
                    $event->trigger();
                    return $this->content;
                }
            } else {
                $error_message = 'El usuario, ' . $fullname . '<br>No tiene configurado un valor de correo electrónico.';
                $this->write_debug_log($error_message);
            }
        } else {
            $error_message = 'El usuario debe estar autenticado para acceder a la biblioteca digital.';
            $this->write_debug_log($error_message);
        }

        return $this->content;
    }

    function get_original_content() {
        global $USER;

        $this->content = new stdClass;

        // Verificar si el usuario está autenticado.
        if (isloggedin()) {
            $fullname = fullname($USER);

            // Verificar si el correo electrónico está configurado.
            if (!empty($USER->email)) {
                try {
                    // Utiliza la función para obtener datos del servicio SSO de eLibro.
                    $response_data = $this->get_elibro_sso_data();

                    // Verificar si la respuesta contiene el campo 'url'.
                    if (!empty($response_data['url'])) {
                        // Agregar enlace al servicio eLibro.
                        $this->content->text = '<label> Bienvenido: </label> ' . $fullname . '<br>';
                        $this->content->text .= '<a href="' . $response_data['url'] . '" target="_blank">Acceder a la Biblioteca digital.</a>';
                    } else {
                        throw new Exception('Unexpected response format: Missing URL');
                    }
                } catch (Exception $e) {
                    // Capturar excepciones y mostrar mensaje de error.
                    $this->content->text = '<br>Error: ' . $e->getMessage();
                    return $this->content;
                }
            } else {
                // El usuario no tiene configurado un valor de correo electrónico.
                $this->content->text = 'El usuario, ' . $fullname . '<br>No tiene configurado un valor de correo electrónico.';
            }
        } else {
            // El usuario debe estar autenticado para acceder a la biblioteca digital.
            $this->content->text = 'El usuario debe estar autenticado para acceder a la biblioteca digital';
        }

        return $this->content;
    }
}
?>
