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
 * Form for editing HTML block instances.
 *
 * @package   block_Colmex
 * @copyright 1999 onwards Martin Dougiamas (http://dougiamas.com)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

class block_colmex extends block_base {

    function init() {
        $this->title = get_string('pluginname', 'block_colmex');
    }

    function get_content() {
        if ($this->content !== NULL) {
            return $this->content;
        }

        $this->content = new stdClass;

            $this->content->text = '
            <!doctype html>
            <html lang="en">
              <head>
                <meta charset="utf-8">
                <meta http-equiv="X-UA-Compatible" content="IE=edge">
                <meta name="viewport" content="width=device-width, initial-scale=1">
                <link rel="icon" href="../../favicon.ico">
              </head>

              <script>
                    function buscar(){
                    var palabra = document.getElementById(\'id_palabra\').value;
                    var url="/blocks/colmex/get_ws.php";
                    //alert(palabra);

                    if(palabra === ""){
                      $(\'#div_contenido\').html("Es necesario escribir una palabra para realizar la consulta");
                    }else{

                      $.ajax({
                        type: "POST",
                        url:url,
                        data:{palabra:palabra,accion:1},
                        afterSend: $(\'#div_contenido\').html(\'<img src="/blocks/colmex/image/loading3.gif" width="60" height="50">\'),
                        success: function(datos){
                        $(\'#div_contenido\').html(datos);
                        $(\'#id_palabra\').val("");
                            }
                          });
                        }
                    }


              </script>

              <body>
                  <h1>DEM</h1>
                  <p>Diccionario Español de México (DEM) es un sistema que permite la
                  interconexión entre la infraestructura del diccionario (base de datos, buscador, procesos) y otras aplicaciones</p>
            <div class="container">
                  <div class="container">
                      <div class="navbar-form navbar-right">
                          <div class="form-group">
                              <input type="text" placeholder="" onkeydown="if(event.keyCode === 13) buscar()" class="form-control" id="id_palabra">
                          </div>
                          <button type="button" onclick="buscar()"  class="btn btn-success">Buscar</button>
                      </div>
                  </div>
                      <hr>
                        <div id="div_contenido" style="">

                        </div>
                      <hr>
            </div>
              </body>
            </html>';
          $this->content->footer='';
        return $this->content;
    }
}
