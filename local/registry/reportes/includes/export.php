<?php
require_once(__DIR__.'/../../../../config.php');
require_once ("graph.php");


function createExport($datasql="",$nameReport='',$hayGrafica=0)
{
        global $CFG, $OUTPUT, $USER,$DB;
        require_once($CFG->libdir . '/excellib.class.php');
        //TEMPORALES
//        $nameReport = "Reporte";
        // Recuperar los datos de la base de datos de Moodle
//            $datasql = $DB->get_records_sql('SELECT * FROM mdl_user');
        $nombreCompleto = $USER->firstname . " " . $USER->lastname;
        $items = [];
        $fechaExtraccion = date('d-m-Y H:m');
        $filename = 'Reporte_' . $nameReport . '_' . date('d_m_Y');
        //Obtenemos el nombre de los header
        foreach ($datasql as $lista){
            $items = $lista;
            break;
        }
        $data = get_object_vars($items);
        $ArrayHeaders = (array_keys($data));

        //limpia el nombre del archivo
        $downloadfilename = clean_filename($filename);
        //Creamos el libro de excel mediante la librería
            $workbook = new MoodleExcelWorkbook("-");
        // Sending HTTP headers.
            $workbook->send($downloadfilename);
        //Agregamos una nueva hoja con el nome del documento.
            $worksheet = $workbook->add_worksheet($filename);
        //DATOS adicionales
        $worksheet->write_string(0,0 ,"Institución: UnADM");
        $worksheet->write_string(1,0 ,"Usuario que realizo la extracción: $nombreCompleto");
        $worksheet->write_string(2,0 ,"Fecha de extracción: $fechaExtraccion");
        //Valida si inserta la gráfica
        $exitsImage = "";
        if($hayGrafica==1){
            $file = "$CFG->dirroot/local/registry/reportes/img/grafico.png";
            if(file_exists($file)){
                $worksheet->insert_bitmap(2,6,$file);
            }else{
                $exitsImage =  "image-not-exist";
            }
        }
        if($exitsImage != ''){
            return $exitsImage;
        }else {
            //Agregamos los encabezados del cocumento
            $i = 0;
            foreach ($ArrayHeaders as $headers) {
                $worksheet->write_string(5, $i, $headers);
                $i++;
            }

            // Agregar los datos a la hoja de cálculo
            $row = 6;
            $col = 0;
            foreach ($datasql as $item) {
                //acedemos a el primero dato encontrado
                foreach ($item as $dataitem) {
                    //recorremos el primer dato en todas sus columnas
                    $worksheet->write_string($row, $col, $dataitem);
                    $col++;//aumentamos el valor de la columna para ir recorriendo el dato
                }
                $col = 0;//regresamos al inicio de la columna
                $row++;//pasamos a la siguiente fila
            }
             $workbook->close();
//            unlink("$CFG->dirroot/local/registry/reportes/img/grafico.png");
            exit;
        }
}
