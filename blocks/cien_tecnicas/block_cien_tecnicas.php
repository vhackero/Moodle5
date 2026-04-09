<?php
class block_cien_tecnicas extends block_base {
    function init() {
        $this->title = get_string('pluginname', 'block_cien_tecnicas');
    }
    function get_content() {
        global $CFG,$COURSE;
        if ($this->content !== NULL) {
            return $this->content;
        }

        $this->content = new stdClass;
        $this->content->text = '
        <style>
        .buscarpor {
          /*width: 15rem;*/
          height: 50px;
          margin-right: 1em;
          padding: 0rem;
          font-size: 1em;
        }
        .busqueda-inputs {
          border-top: none;
          border-left: none;
          border-right: none;
          background-color: white;
          outline: none;
          margin-top: 10px;
          border-bottom: solid 1.5px #8f959e;
          padding: 5px;
          /*padding-bottom: 15px;*/
        }
        .buscarpalabra {
          /*width: 15rem;*/
          height: 50px;
          margin-right: 1em;
          padding: 0rem 0.5rem;
          font-size: 1em;
          margin-bottom: 20px;
        }
        .searchNotValue{
          padding: 15px;
          background: #fded99ed;
          margin: 15px;
          border-radius: 5px;
        }
        .searchNotValue-info{
          background: #fded99ed;
        
        }
        .container{
        padding: 15px;
        }
        #vista .form-card{
          color: white;
          padding: 10px;
          margin-top: 5px;
          justify-content: center;
        }
        #vista .extra-card-info{
            color: black;
                font-size: 0.75em;
        }
        .searchbutton:hover, .resetElements:hover, .button-form-card:hover .button-form-card:hover{
            background: #97213b !important;
            color: white;
        }
        @media (max-width: 768px) {
            #vista .container-info{
                text-align: center;
            }
        }
        
        #vista .p-form-card{
          padding: 8px;
          margin-bottom: 2px;
        }
        
        .color-white{
        color:white;
        border: none !important;
        }
        #vista .img-container-form-card{
          height: 150px;
          background: white;
          border-radius: 2px;
          text-align: center;
          justify-content: center;
          display: flex;
          max-width: 100%;
          margin-top: 8px;
        }
        #searchbutton, #resetElements{
            background-color: #611232;
            border-color: #611232;
            border-radius: 6%;
        }
        #searchbutton:active, #resetElements:active, #searchbutton:hover, #resetElements:hover{
            background-color: #393c3e;
            border-color: #393c3e;
        }
        .btn:focus, .btn.focus{
            box-shadow: none;
        }
        .searching{
          width: 100%;
          display: flex;
          justify-content: center;
          margin-top: 30px;
          height: 100px;
          align-items: center;
          font-size: 1.3em;
        }
        .not-view{
            display: none;
        }
        #vista{
            display: flex;
            flex-wrap: wrap;
            justify-content: center; /* Centra los elementos */
            max-width: 1200px; /* Ancho máximo del contenedor */
            margin: auto; /* Centra el contenedor en la página */
        }
        #vista .form-card .p-form-card{
          margin-top: 10px;
          min-height: 50px;
          color: #393c3e;
        }
        #vista .form-card{
        flex: 1 1 calc(33.33% - 20px);
        margin: 5px;
        max-width: 33%;
        text-align: center;
        }
        #vista .button-form-card{
        border: none !important;
        padding: 0px;
        }
        @media  screen and (min-width: 768px ) and (max-width: 1074px) {
            #vista .form-card{
                flex: 33.33%;
                max-width: 100%;
            }
        }
        @media  screen and (max-width: 500px) {
            #vista .form-card{
                flex: auto;
                max-width: 100%;
            }
            .img-container-form-card{
                max-width: 100px !important;
            }
        }
        </style>
                    <div class="set-buscador">
                      <p>100 técnicas didácticas de enseñanza y aprendizaje es un buscador que te permite 
                          realizar consultas específicas utilizando (nombre de la técnica, nivel taxonómico básico, número de técnica y palabras claves</p>
                       <select name="filtro-avanzado" id="filtro-avanzado" class="busqueda-inputs form-select buscarpor col-md-4">
                          <option value="buscar_por">Selecciona un filtro</option>
                          <option value="resource_tecnica">Nombre de técnica</option>
                          <option value="resource_type_resource">Nivel taxonómico básico</option>
                          <option value="resource_no_tecnica">Número de técnica</option>
                          <option value="resource_keywords">Palabras clave</option>
                          <!--                  <option value="resource_nivel">Nivel de ejecución</option>-->
                       </select>
                       <input class="buscarpalabra busqueda-inputs col-md-4" name="filtro-avanzado-text" id="filtro-avanzado-text" placeholder="escribir una palabra o texto">
                       <button  title="Realizar busqueda" id="searchbutton" type="button" onclick="ValidateToSubmit(this)" class="btn btn-info btn-buscar button-search">
                          <i class="fa fa-search"></i>
                       </button>
                       <button  title="Borrar busqueda" id="resetElements" type="button"  onclick="ValidateToSubmit(this)" class="btn btn-danger btn-buscar button-search">
                             <img id="icon-delete-cien-tecnicas" src="" width="18px" alt="borrar" title="Borrar busqueda">
                       </button>
                    </div>
                    <div id="vista" class="container">
                    </div>
                    <div id="cargando" class="container not-view">
                       <div class="searching "><i class="fa fa-cog fa-spin fa-3x fa-fw"></i><span>Espere...</span></div>
                    </div>
                    
        <script>
        let serverurlmoodle = "'.$CFG->wwwroot.'";
        let courseid = '.$COURSE->id.';
        document.getElementById("icon-delete-cien-tecnicas").src = serverurlmoodle+"/blocks/cien_tecnicas/img/borrar.png";
        
        window.addEventListener("load", function() {
            document.getElementById("filtro-avanzado").value = "buscar_por";
            document.getElementById("filtro-avanzado-text").value = "";
        });
        
        
        function ValidateToSubmit(dato){
            dataContainer = document.getElementById("vista");   
            filtroAvanzadoConditional = document.getElementById("filtro-avanzado").value;         
            filtroAvanzadoSearch = document.getElementById("filtro-avanzado-text").value;    
            if(dato.id == "searchbutton") {
                       if(filtroAvanzadoConditional == "buscar_por" || filtroAvanzadoSearch == "" ){
                           dataContainer.innerHTML = "<p class=\'searchNotValue\'>Es necesario seleccionar un filtro y escribir por lo menos una palabra para realizar una búsqueda.</p>";
                       }else{
                           //  console.log("El filtro activo es: "+filtroAvanzadoConditional+"el texto a buscar es:"+filtroAvanzadoSearch);
                           dataContainer.innerHTML = "";
                           actualizarSeccionBusqueda(filtroAvanzadoConditional,filtroAvanzadoSearch,dataContainer);
                       }
            }else if(dato.id == "resetElements"){
                dataContainer.innerHTML = "";
                 $("#filtro-avanzado").val("buscar_por");
                 $("#filtro-avanzado-text").val("");
            }
        }
        function actualizarSeccionBusqueda(filtroAvanzadoConditional="",filtroAvanzadoSearch="",dataContainer){
              // Crear una nueva instancia de XMLHttpRequest
              var xhttp = new XMLHttpRequest();
              // Definir la función de devolución de llamada para manejar la respuesta del servidor
              xhttp.onreadystatechange = function() {
                  document.querySelector("#cargando").classList.remove("not-view");
                 if (this.readyState == 4 && this.status == 200) {
                     document.querySelector("#cargando").classList.add("not-view");
                     //Válida si la conexión fue exitosa
                     if(this.responseText.includes("error_connect_database100tecnicas")){
                         dataContainer.innerHTML = "<p class=\'searchNotValue\'>Error al realizar la búsqueda intentalo más tarde.</p>";
                     }else{
                         
                         // Actualizar el contenido de la sección con la respuesta del servidor
                         dataContainer.innerHTML =  xhttp.responseText;
                     }
                 }
              };
              // Enviar la solicitud al servidor
              xhttp.open("POST", serverurlmoodle+"/blocks/cien_tecnicas/wsConection.php", true);
              var datos = "filtro=" + encodeURIComponent(filtroAvanzadoConditional)+"&busqueda=" + encodeURIComponent(filtroAvanzadoSearch)+ "&courseid="+encodeURIComponent(courseid);
              xhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
              xhttp.send(datos);
        }
        </script>
                ';
        $this->content->footer = '';

        return $this->content;
    }
    function instance_allow_config() {
        return true;
    }

    function instance_allow_multiple() {
        return true;
    }
}
