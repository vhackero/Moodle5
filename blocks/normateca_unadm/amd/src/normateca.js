define(['jquery'], function ($) {

    return {
        init: function (courseid, wwwlocation) {

            const serverurlmoodle_normateca = wwwlocation;
            const courseid_normateca = courseid;

            // console.log('Normateca AMD iniciado', courseid_normateca, serverurlmoodle_normateca);

            /* ================= ELEMENTOS ================= */
            const SelectDivision = document.getElementById("filtro-division");
            const SelectCarrera = document.getElementById("filtro-carrera");
            const SelectDependencia = document.getElementById("filtro-dependencia");
            const SelectRecurso = document.getElementById("filtro-recurso");
            const dataContainer_normateca = document.getElementById("vista-normateca");
            const paginadorContainer_normateca = document.getElementById("paginador-normateca");
            const scrollToTopBtn_normateca = document.getElementById("scrollToTopBtn-normateca");
            var num_paginas_normateca = 0;
            const limitShowNumbers_normateca = 10;
            var num_resultados_normateca = 0;


            if (!SelectDivision || !SelectCarrera || !dataContainer_normateca) {
                console.warn('Normateca: elementos principales no encontrados');
                return;
            }

            /* ================= INICIALIZACIÓN ================= */
            SelectDivision.value = "0";
            SelectCarrera.value = "0";
            SelectDependencia.value = "0";
            SelectRecurso.value = "0";

            const deleteIcon = document.getElementById("icon-delete-cien-tecnicas-normateca");
            if (deleteIcon) {
                deleteIcon.src = serverurlmoodle_normateca + "/blocks/normateca_unadm/img/borrar.png";
            }

            setDivisiones();

            /* ================= EVENTOS ================= */
            SelectDivision.addEventListener('change', function () {
                const id_division = this.value;
                SelectCarrera.value = 0;
                if (id_division !== "0") {
                    setCarreras(id_division);
                }
            });

            SelectCarrera.addEventListener('change', function () {
                setDependencias();
                setRecursos();
            });

            if (scrollToTopBtn_normateca) {
                scrollToTopBtn_normateca.addEventListener('click', function () {
                    window.location.hash = '#buscador-normateca';
                });
            }

            /* ================= FUNCIONES ================= */

            function actualizarSeccionBusquedaNormateca(filtro, filtroDivision, filtroCarrera,filtroDependencia) {
                document.querySelector("#cargando-normateca").classList.remove("not-view");
                const xhttp = new XMLHttpRequest();
                xhttp.onreadystatechange = function () {
                    if (this.readyState === 4 && this.status === 200) {
                        document.querySelector("#cargando-normateca").classList.add("not-view");
                        if (this.responseText.includes("error_connect_database100tecnicas")) {
                            dataContainer_normateca.innerHTML = "<p class=\'searchNotValue-normateca\'>Error al realizar la búsqueda intentalo más tarde.</p>";
                        } else {
                            dataContainer_normateca.innerHTML = this.responseText;
                            num_paginas_normateca = document.getElementsByClassName("resultados-normateca").length
                            num_resultados_normateca = document.getElementsByClassName("container-info-normateca").length
                            document.getElementById("num_resultados_normateca").innerHTML = num_resultados_normateca
                            document.getElementById("num_resultados_container-normateca").classList.remove("not-view");
                            createPaginatorNormateca(num_paginas_normateca);
                            document
                                .querySelectorAll('.urlelements')
                                .forEach(function (element) {
                                    element.addEventListener('click', function () {
                                        showpdf(this);
                                    });
                                });
                        }
                    }
                };

                const datos =
                    "filtro=" + encodeURIComponent(filtro) +
                    "&filtro2=" + encodeURIComponent(filtroDependencia) +
                    "&filtro3=" + encodeURIComponent(filtroDivision) +
                    "&filtro4=" + encodeURIComponent(filtroCarrera) +
                    "&courseid_normateca=" + encodeURIComponent(courseid_normateca);

                xhttp.open("POST", serverurlmoodle_normateca + "/blocks/normateca_unadm/wsConection.php", true);
                xhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
                xhttp.send(datos);
            }

            function setDivisiones() {
                ajaxSimple("getDivisiones", SelectDivision);
            }

            function setCarreras(id_division) {
                ajaxSimple("getCarreras&id_division=" + encodeURIComponent(id_division), SelectCarrera);
            }

            function setDependencias() {
                ajaxSimple("getDependencias", SelectDependencia);
            }

            function setRecursos() {
                ajaxSimple("getRecurso", SelectRecurso);
            }

            function ajaxSimple(funcion, selectElement) {
                const xhttp = new XMLHttpRequest();
                xhttp.onreadystatechange = function () {
                    if (this.readyState === 4 && this.status === 200) {
                        selectElement.innerHTML = this.responseText;
                    }
                };
                xhttp.open("POST", serverurlmoodle_normateca + "/blocks/normateca_unadm/wsConection.php", true);
                xhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
                xhttp.send("funtion=" + funcion);
            }

            function ValidateToSubmitNormateca(dato) {

                const filtroDivision = document.getElementById("filtro-division").value;
                const filtroCarrera = document.getElementById("filtro-carrera").value;
                const filtroDependencia = document.getElementById("filtro-dependencia").value;
                const filtroRecurso = document.getElementById("filtro-recurso").value;

                if (dato.id === "searchbutton-normateca") {

                    if (filtroDivision === "0" || filtroCarrera === "0") {
                        dataContainer_normateca.innerHTML =
                            "<p class='searchNotValue-normateca'>Es necesario seleccionar los filtros.</p>";
                        return;
                    }

                    dataContainer_normateca.innerHTML = "";
                    actualizarSeccionBusquedaNormateca(
                        filtroRecurso,
                        filtroDivision,
                        filtroCarrera,
                        filtroDependencia
                    );

                } else if (dato.id === "resetElements-normateca") {

                    dataContainer_normateca.innerHTML = "";
                    paginadorContainer_normateca.innerHTML = "";

                    document.getElementById("filtro-division").value = "0";
                    document.getElementById("filtro-carrera").value = "0";
                    document.getElementById("filtro-dependencia").value = "0";
                    document.getElementById("filtro-recurso").value = "0";
                    document.getElementById("num_resultados_container-normateca").classList.add("not-view");

                }
            }
            function createPaginatorNormateca(length) {
                padrePaginador = document.getElementById("paginador-normateca");
                padrePaginador.innerHTML = "";
                if (num_paginas_normateca > 0) {
                    previousElement = document.createElement("button");
                    previousElement.setAttribute("class", "caroucel-previous-normateca");
                    previousElement.setAttribute("id", "caroucel-previous-normateca");
                    previousElement.onclick = () => actionPaginatorNormateca("previous");
                    previousElement.innerHTML = "<i class=\"fa fa-caret-left\"></i>";
                    padrePaginador.appendChild(previousElement);
                    for (var i = 1; i <= length; i++) {
                        (function (num) {
                            var itempag = document.createElement("button");
                            itempag.setAttribute("class", "caroucel-nums-normateca");
                            itempag.setAttribute("id", "item-active-" + num);
                            itempag.onclick = () => {
                                activeElementNormateca(num)
                            };
                            if (num == 1) {
                                itempag.setAttribute("class", "active-num-normateca caroucel-nums-normateca");
                            }
                            itempag.innerHTML = num
                            if (i > limitShowNumbers_normateca) {
                                itempag.setAttribute("class", "not_visible-num-normateca caroucel-nums-normateca");
                            }
                            padrePaginador.appendChild(itempag);
                        })(i);
                    }
                    nextElement = document.createElement("button");
                    nextElement.setAttribute("class", "caroucel-next-normateca");
                    nextElement.setAttribute("id", "caroucel-next-normateca");
                    nextElement.onclick = () => actionPaginatorNormateca("next");
                    nextElement.innerHTML = "<i class=\"fa fa-caret-right\"></i>";
                    padrePaginador.appendChild(nextElement);
                }
            }
            function actionPaginatorNormateca(action) {
                activeelement = document.getElementsByClassName("active-num-normateca")[0];
                if (activeelement != undefined) {
                    activenum = activeelement.id.split("-")[2];
                    if (action == "next") {
                        if (activenum < num_paginas_normateca) {
                            activeElementNormateca(Number(activenum) + (1));
                            var caroucel_nums = document.getElementsByClassName("caroucel-nums-normateca");
                            elementToactive = Number(activenum) + (1);
                            if (elementToactive > limitShowNumbers_normateca) {
                                caroucel_nums[elementToactive - 1].classList.remove("not_visible-num-normateca");
                                //console.log("Elemento activo era" + activenum)
                                var numsnotvisble = document.getElementsByClassName("not_visible-num-normateca");
                                var itemoculto = caroucel_nums[caroucel_nums.length - elementToactive].id;
                                var iditemoculto = itemoculto.split("-")[2];
                                dato1 = Number(num_paginas_normateca) - Number(limitShowNumbers_normateca); /*console.log("num_paginas_normateca "+num_paginas_normateca); console.log("limitShowNumbers_normateca "+limitShowNumbers_normateca); console.log("dato "+dato1); console.log("iditemoculto "+iditemoculto);*/
                                dato2 = dato1 - iditemoculto;
                                if (dato2 > 0) {
                                    dato2 - 1;
                                }
                                caroucel_nums[dato2].classList.add("not_visible-num-normateca");
                            }
                        }
                    } else if (action == "previous") {
                        if (activenum > 1) {
                            activeElementNormateca(activenum - 1);
                            elementToactive = (Number(activenum) - (1));
                            if (elementToactive < limitShowNumbers_normateca) {
                                //console.log("item activo era" + activenum);
                                var caroucel_nums = document.getElementsByClassName("caroucel-nums-normateca");
                                var itemoculto = caroucel_nums[caroucel_nums.length - elementToactive].id;
                                var iditemoculto = itemoculto.split("-")[2];
                                dato1 = Number(num_paginas_normateca) - Number(limitShowNumbers_normateca);
                                dato2 = Number(elementToactive) + Number(dato1);
                                prueba = (activenum - dato1) - 3;
                                if (caroucel_nums[prueba] != undefined) {
                                    caroucel_nums[prueba].classList.remove("not_visible-num-normateca");
                                }
                                if (dato2 > limitShowNumbers_normateca) {
                                    caroucel_nums[dato2].classList.add("not_visible-num-normateca");
                                }
                                if (elementToactive == 1 & caroucel_nums[limitShowNumbers_normateca] != undefined) {
                                    caroucel_nums[limitShowNumbers_normateca].classList.add("not_visible-num-normateca");
                                }
                            }
                        }
                    }
                }
            }
            function activeElementNormateca(elementToactive, action = "") {
                if (elementToactive != undefined) {
                    //console.log("El elemento a activar es" + elementToactive);
                    var boxElements = document.getElementsByClassName("resultados-normateca");
                    var caroucel_nums = document.getElementsByClassName("caroucel-nums-normateca");
                    for (var i = 0; i < boxElements.length; i++) {
                        boxElements[i].classList.remove("active-item-normateca");
                        caroucel_nums[i].classList.remove("active-num-normateca");
                        boxElements[elementToactive - 1].classList.add("active-item-normateca");
                        caroucel_nums[elementToactive - 1].classList.add("active-num-normateca");
                    }
                }
            }

            function showpdf(datosProcesar) {
                dataProcesarLimpia = datosProcesar.getAttribute('data-item').split('|');
                pdfUrl = dataProcesarLimpia[0].trim();
                namedocument = dataProcesarLimpia[1].trim();
                indicaciones = dataProcesarLimpia[2].trim();
                document.getElementById("pdfViewer").src = "";
                document.getElementById("modal-footer-normateca").innerHTML = "";
                document.getElementById("pdfModalLabel").textContent = namedocument;
                document.getElementById("pdfViewer").src = pdfUrl;
                butonAlternative = document.createElement("a");
                butonAlternative.setAttribute("class", "btn btn-success");
                butonAlternative.setAttribute("target", "_blank");
                butonAlternative.href = pdfUrl
                butonAlternative.innerHTML = "Abrir en otra ventana"
                textoinformativo = document.createElement("p");
                textoinformativo.textContent = "Si no puedes visualizar el recurso dentro de la pantalla, puedes dar clic en el siguiente botón:"
                document.getElementById("modal-footer-normateca").appendChild(textoinformativo)
                document.getElementById("modal-footer-normateca").appendChild(butonAlternative)
                document.getElementById("loadElement-normateca").classList.remove("not-view");
                window.location.href = "#load-item"
                document.getElementById("pdfViewer").onload = () => {
                    document.getElementById("loadElement-normateca").classList.add("not-view");
                    $("#pdfModal").modal("show");
                    if (indicaciones != "") {
                        indicacionesdiv = document.createElement("div");
                        if (document.getElementsByClassName("indicaciones-normateca").length == 0) {
                            indicacionesdiv.classList.add("indicaciones-normateca");
                            indicacionesdiv.innerHTML = "<p>" + indicaciones + "</p>";
                            document.querySelectorAll("#pdfModal #pdfModalLabel")[0].appendChild(indicacionesdiv);
                        } else {
                            indicacionesdiv.innerHTML = "";
                        }
                    }
                    if (document.getElementById("loadElement-normateca").classList[0] != "not-view") {
                        document.getElementById("loadElement-normateca").classList.add("not-view");
                    }
                }
                setTimeout(() => {
                    if (document.getElementById("loadElement-normateca").classList[0] != "not-view") {
                        document.getElementById("loadElement-normateca").classList.add("not-view");
                        $("#pdfModal").modal("show");
                        document.getElementById("messageViewer").innerHTML = "El recurso que intentas acceder no está disponible en el sitio origen,intenta dando clic " + "<a target=\"_blank\" href=\"" + pdfUrl + "\" >aquí</a>" + " o prueba más tarde.";
                        $("#pdfModalmessages-normateca").modal("show");
                    }
                }, 19000)
            }

            document
                .getElementById('searchbutton-normateca')
                ?.addEventListener('click', function () {
                    ValidateToSubmitNormateca(this);
                });

            document
                .getElementById('resetElements-normateca')
                ?.addEventListener('click', function () {
                    ValidateToSubmitNormateca(this);
                });
            var contenedorbloque = document.querySelector(".items-busqueda-normateca");
            var anchoActual = contenedorbloque.offsetWidth;
            if (anchoActual < "1250") {
                contenedorbloque.style.flexDirection = "column";
            } else {
                contenedorbloque.style.flexDirection = "row"
            }
        }
    };
});
