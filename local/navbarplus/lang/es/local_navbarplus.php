<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Local plugin "Navbar Plus" - Spanish language pack
 *
 * @package    local_navbarplus
 * @copyright  2026
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['pluginname'] = 'Navbar Plus';
$string['privacy:metadata'] = 'El plugin Navbar Plus ofrece funcionalidades extendidas para los usuarios de Moodle, pero no almacena ningún dato personal.';
$string['resetusertours_hint'] = '(Puede tardar un momento)';
$string['setting_inserticonswithlinks'] = 'Iconos con enlaces';
$string['setting_inserticonswithlinks_desc'] = 'Con esta configuración puedes agregar iconos con enlace en la barra de navegación superior, a la izquierda de los iconos de "mensajes" y "notificaciones".<br/>
Cada línea consiste en un icono, una URL, un texto, idioma(s) soportado(s) (opcional), abrir en ventana nueva (opcional), clases adicionales (opcional), ID del elemento (opcional) y alcance de visibilidad (opcional), separados por el carácter de barra vertical (pipe). Cada icono debe escribirse en una línea nueva.<br/>
Por ejemplo:<br/>
fa-question|http://moodle.org|Moodle|en,de|true|d-none d-sm-flex||all<br/>
fa-sign-out|/login/logout.php|Cerrar sesión||false|||loggedin<br/>
fa-home|/|Inicio|||||public<br/><br/>
Más información sobre los parámetros:
<ul>
<li><b>Icono:</b> Puedes agregar identificadores de iconos Font Awesome (<a href="https://fontawesome.com/v6/icons">Ver la lista de iconos en fontawesome.com/v6/icons</a>). Font Awesome 6 está incluido en Moodle core.</li>
<li><b>Enlace:</b> El destino puede ser una URL completa (por ejemplo, https://moodle.org) o una ruta relativa dentro de tu instancia Moodle (por ejemplo, /login/logout.php).</li>
<li><b>Título:</b> Este texto se usará en los atributos title y alt del icono.</li>
<li><b>Idioma(s) soportado(s) (opcional):</b> Este ajuste permite mostrar el enlace solo a usuarios de los idiomas especificados. Separa varios idiomas con comas. Si el enlace debe mostrarse en todos los idiomas, deja este campo vacío.</li>
<li><b>Ventana nueva (opcional):</b> De forma predeterminada el enlace se abre en la misma ventana y el valor de este ajuste es false. Si deseas abrir el enlace en una nueva ventana, establece el valor en true.</li>
<li><b>Clases adicionales (opcional):</b> Puedes agregar clases CSS personalizadas con este parámetro opcional. Un caso común es usar clases responsivas de Bootstrap para ocultar un icono en ciertos tamaños de pantalla. <br/> Puedes consultar las clases de visualización responsiva para <a href="https://getbootstrap.com/docs/5.2/utilities/display/">Bootstrap versión 5</a> para todos los temas basados en Boost.<br/>
Las clases más importantes para temas basados en Boost podrían ser "d-none d-sm-flex" para ocultar un icono en dispositivos pequeños o "d-sm-none" para mostrarlo solo en pantallas pequeñas.</li>
<li><b>ID (opcional):</b> Puedes agregar un ID individual al elemento del icono. Esto permite dirigirte fácilmente a ese icono específico con CSS (por ejemplo, para recorridos de usuario de Moodle). El texto que ingreses siempre será prefijado con "localnavbarplus-".</li>
<li><b>Alcance de visibilidad (opcional):</b> Puedes decidir cuándo se muestra el icono: "all" (predeterminado), "loggedin" (solo usuarios autenticados) o "public" (solo usuarios sin sesión autenticada).</li>
</ul>
Ten en cuenta:
<ul>
<li>La separación por pipes para parámetros opcionales siempre es necesaria cuando están ubicados entre otras opciones. Esto significa que debes separar los parámetros con el carácter pipe aunque estén vacíos. Consulta también el ejemplo del icono Font Awesome anterior.</li>
<li>Si el icono no aparece en la barra de navegación, verifica que todos los parámetros obligatorios estén correctamente definidos y que el ajuste opcional de idioma coincida con el idioma actual del usuario.</li>
</ul>';
$string['setting_resetusertours'] = 'Enlace para reiniciar recorrido de usuario';
$string['setting_resetusertours_desc'] = 'Con esta configuración puedes colocar un icono de mapa de Font Awesome en la barra de navegación para que el usuario pueda reiniciar el recorrido de usuario de la página actual. De forma predeterminada, Boost coloca el enlace para reiniciar el recorrido en el pie de página. Esto puede no ser tan visible. Con esta configuración puedes ubicar el enlace en la barra de navegación para que sea más visible.<br/> Si deseas cambiar este icono, revisa el archivo README.md.';
