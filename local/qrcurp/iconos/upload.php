<?php
require_once(__DIR__ . '/../../../config.php');
require_login();

if (!is_siteadmin()) {
    throw new \moodle_exception('accessdenied', 'admin');
}

$context = \context_system::instance();
require_capability('moodle/site:config', $context);

$maxbytes = 4 * 1024 * 1024;
$errors = [];
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && confirm_sesskey()) {
    $categoryname = trim((string)optional_param('categoryname', '', PARAM_TEXT));
    $categoryname = preg_replace('/[^A-Za-z0-9 _-]/', '', $categoryname);

    if ($categoryname === '') {
        $errors[] = 'Debes indicar el nombre de la categoría.';
    }

    if (!isset($_FILES['the_file']) || !is_uploaded_file($_FILES['the_file']['tmp_name'])) {
        $errors[] = 'Selecciona una imagen válida para subir.';
    }

    if (empty($errors)) {
        $file = $_FILES['the_file'];
        if ((int)$file['size'] > $maxbytes) {
            $errors[] = 'El archivo excede el tamaño máximo permitido (4 MB).';
        }

        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimetype = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);

        $allowed = [
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
        ];
        if (!isset($allowed[$mimetype])) {
            $errors[] = 'Solo se permiten imágenes JPG o PNG.';
        }

        $imginfo = @getimagesize($file['tmp_name']);
        if ($imginfo === false) {
            $errors[] = 'El archivo no es una imagen válida.';
        }

        if (empty($errors)) {
            $targetdir = __DIR__;
            $safe = trim(preg_replace('/\s+/', ' ', $categoryname));
            $filename = $safe . '.' . $allowed[$mimetype];
            $targetpath = $targetdir . DIRECTORY_SEPARATOR . $filename;

            if (!move_uploaded_file($file['tmp_name'], $targetpath)) {
                $errors[] = 'Ocurrió un error al guardar la imagen.';
            } else {
                @chmod($targetpath, 0644);
                $success = 'Imagen subida correctamente como: ' . s($filename);
            }
        }
    }
}

$PAGE->set_context($context);
$PAGE->set_url(new \moodle_url('/local/qrcurp/iconos/upload.php'));
$PAGE->set_title('Subir imagen de registro');
$PAGE->set_heading('Subir imagen de registro');

echo $OUTPUT->header();
echo html_writer::tag('h3', 'Subida segura de iconos para registro');
echo html_writer::tag('p', 'Solo administradores del sitio pueden usar esta página.');

foreach ($errors as $error) {
    echo $OUTPUT->notification($error, \core\output\notification::NOTIFY_ERROR);
}
if ($success !== '') {
    echo $OUTPUT->notification($success, \core\output\notification::NOTIFY_SUCCESS);
}

echo '<form method="post" enctype="multipart/form-data">';
echo '<input type="hidden" name="sesskey" value="' . sesskey() . '">';
echo '<div><label for="categoryname">Nombre de categoría:</label><br>';
echo '<input type="text" id="categoryname" name="categoryname" required></div><br>';
echo '<div><label for="the_file">Imagen (JPG o PNG, máximo 4 MB):</label><br>';
echo '<input type="file" id="the_file" name="the_file" accept=".jpg,.jpeg,.png,image/jpeg,image/png" required></div><br>';
echo '<button type="submit">Subir imagen</button>';
echo '</form>';

echo $OUTPUT->footer();
