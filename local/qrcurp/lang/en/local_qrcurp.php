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

$string['pluginname'] = 'QRCURP';
$string['dbhost'] = "DB Host";
$string['dbhostinfo'] = "Remote Database host name (on which, we will be executing our SQL queries)";
$string['dbport'] = "DB Port";
$string['dbportinfo'] = "Remote Port to connect DB";
$string['dbname'] = "DB Name";
$string['dbnameinfo'] = "Remote Database name (on which, we will be executing our SQL queries)";
$string['dbuser'] = "DB Username";
$string['dbuserinfo'] = "Remote Database username (should have SELECT privileges on above DB)";
$string['dbpass'] = "DB Password";
$string['dbpassinfo'] = "Remote Database password (for above username)";
$string['dbtable'] = "DB Table";
$string['dbtableinfo'] = "Remote Table on Database(on which, we will be executing our SQL queries)";
$string['dbinsert'] = "Insert SQL query external DB";
$string['dbinsertinfo'] = "perform the query insertion in the external database";
$string['dateregistro'] = "Fecha Límite";
$string['dateregistroinfo'] = "Fecha límite en la que el regsitro estara activo. Formato : dd-mm-yyyy";
$string['textregistro'] = "Texto a mostrar";
$string['textregistroinfo'] = "Texto a mostrar pasado el límite de la fecha de registro";
$string['dateperiodos'] = "Fecha por peridos";
$string['dateperiodosinfo'] = "Si se requiere que el registro este abierto por periodos configurar el siguiente formato: <br> Nombre del periodo|fecha inicial(dd-mm-yyyy)|fechafinal(dd-mm-yyyy) Si se requiere mas de un período separar por ','<br> Ejemplo: Periodo A|03-01-2025|05-01-2025,Periodo B|08-04-2025|15-04-2025 ";
$string['dateduracionperidos'] = "Fecha de apertura y fin de acceso a cursos";
$string['dateduracionperidosinfo'] = "Si se requiere que los cursos esten abiertos por periodos configurar el siguiente formato: <br> Nombre del periodo|fecha inicial(dd-mm-yyyy)|fechafinal(dd-mm-yyyy)|id de curso(para más de un curso separar los id con '-') Si se requiere mas de un período separar por ','<br> Ejemplo: Periodo A|03-01-2025|05-01-2025|2-3,Periodo B|08-04-2025|15-04-2025|15-25 ";
$string['publicogeneral'] = "Registros externos";
$string['publicogeneralinfo'] = "Acepta registros a usuarios no pertenecientes a la BD externa, publico en general";
$string['onlypublicogeneral'] = "Solo aceptar Registros externos";
$string['onlypublicogeneralinfo'] = "Acepta registros a usuarios no pertenecientes a la BD externa, publico en general, aún realizando la validación para excluirlos";
$string['idcategories'] = "Ids de Categorías";
$string['idcategoriesinfo'] = "Id o Ids de la categorías que mostrarán combo con los cursos pertenecientes a esa categoría, cada categoría deberá estar separada por una ',' Formato : 1,2,4 (Modificar el archivo index.php de /login)";
$string['rolteacher'] = "Rol del usuario que impartirá el grupo";
$string['rolteacherinfo'] = "Id del rol que estará impartiendo el curso ejemplo: 4 = teacher";
$string['limitegroup'] = "Límite de participantes en grupo";
$string['limitegroupinfo'] = "Número de participantes, contando únicamente estudiantes ";
$string['rolstudent'] = "Rol de estudiantes";
$string['rolstudentinfo'] = "Id del rol que estará como estudiante en el curso ejemplo: 5 = student";
$string['haygroupespera'] = "Existe grupo de espera";
$string['haygroupesperainfo'] = "Cuando los grupos alcanzan el límite permitido se agregara un grupo de espera ";
$string['namegroupespera'] = "Nombre del grupo en espera";
$string['namegroupesperainfo'] = "Cuando los grupos alcanzan el límite permitido se agregaran a un grupo de espera ejemplo: 'lista de espera'";
$string['defaultnamecategory'] = "Nombre del registro sin categoría";
$string['defaultnamecategoryinfo'] = "Cuando el registro no lleva un id de categoría para determinar el nombre del registro, usará este nombre por defecto, colocar 'externo' si no tiene alguno";
$string['confirmemail'] = "Enviar correo de confirmación";
$string['confirmemailinfo'] = "Cuando un nuevo usuario se registra se enviará un correo para confirmar el registro(desmarcar esta opción si solo se requiere para un tipo de registro)";
$string['confirmemailgeneral'] = "Enviar correo de confirmación publico general";
$string['confirmemailinfogeneral'] = "Cuando un nuevo usuario se registra se enviará un correo para confirmar el registro(Marcar únicamente si solo aplicará para registro en general)";
$string['confirmemailexternos'] = "Enviar correo de confirmación externos";
$string['confirmemailinfoexternos'] = "Cuando un nuevo usuario se registra se enviará un correo para confirmar el registro(Marcar únicamente si solo aplicará para registro en externos)";
$string['emailexterno'] = "Usar servidor de correo externo";
$string['emailexternoinfo'] = "Hace uso de un servidor de correo smtp externo al de moodle.";
$string['studentxcategory'] = "Límite de estudiantes totales por categoría";
$string['studentxcategoryinfo'] = "Límites por categoría. Formatos válidos: 100 (límite global), 5:100,8:200 (por categoría), *:100,8:200 (global + excepción). Vacío = sin límite.";
$string['studentxcategorytext'] = "Texto a mostrar si se supera el límite de estudiantes en la categoría";
$string['studentxcategorytextinfo'] = "Texto a mostrar pasado el límite de los estudiantes";
$string['sampleregister'] = "Acepta registro sin matriculación";
$string['sampleregisterinfo'] = "Permite que el usuario pueda registrarse únicamente a la plataforma sin matricularlo a un curso en especifico.";
$string['defaultcategoryid'] = "Id de la categoría por defecto";
$string['defaultcategoryidinfo'] = "Cuando no se encuentra el categoryid se tomara la categoría para usarla por defecto cuando la URL no tenga ese dato.";
$string['defaultcourseid'] = "Id del curso por defecto";
$string['defaultcourseidinfo'] = "Cuando se quiere que únicamente se muestre un solo curso perteneciente a la categoría por defecto..";
$string['defaultgroupid'] = "Id del grupo por defecto";
$string['defaultgroupidinfo'] = "Para cuando no se pudiera seleccionar un grupo, este sería el grupo que tomaría el registro(debe pertenecer a la categoría y curso por defecto).";
$string['adminsite'] = "Nombre del administrador del sitio";
$string['adminsiteinfo'] = "Nombre del administrador del sitio para que este sea el que agregue a los usuarios que se registran";
$string['mailsupport'] = "Correo electrónico de soporte";
$string['mailsupportinfo'] = "Correo electrónico que atenderá a los usuarios en caso de algún problema.";
$string['dbcatalogoshost'] = "Host";
$string['dbcatalogoshostinfo'] = "Host donde se encuentra la base de datos de sepomex";
$string['dbcatalogosuser'] = "Nombre de usuario";
$string['dbcatalogosuserinfo'] = "usuario para datos de sepomex";
$string['dbcatalogospass'] = "Contraseña";
$string['dbcatalogospassinfo'] = "contraseña para datos de sepomex";
$string['dbcatalogos'] = "Nombre de la base de datos";
$string['dbcatalogosinfo'] = "nombre de la base de datos por defecto es sepomex";
$string['showuniquecourses'] = "Muestra cursos en específico en el registro:";
$string['showuniquecoursesinfo'] = "Cuando únicamente se quiere que aparezcan un o unos cursos en específico.";
$string['showuniquecourseslist'] = "Id(s) de curso(s):";
$string['showuniquecourseslistinfo'] = "id del curso(s) si es más de uno separar los id por una \",\" ejemplo: 2,3,4";
$string['coursealphaorder'] = "Ordenar cursos alfabéticamente:";
$string['coursealphaorderinfo'] = "Ordena la lista del combo de cursos en el registro.";
$string['onegroupattime'] = "Muestra un grupo a la vez:";
$string['onegroupattimeinfo'] = "Muestra un grupo a la vez en el registro y se muestra el siguiente una vez alcanzado el límite (los grupos deben estar previamente creados y con un usuario inical  de estudiante y otro el rol de quien impartirá el grupo, este usauario no se tomará en cuenta para el conteo).";
$string['groupsalredycreated'] = "Lista de grupos ya creados:";
$string['groupsalredycreatedinfo'] = "Muestra los grupos que ya estan creados dentro del curso, quien impartira el grupo debe estar asociado a ese grupo";
$string['creategroupenrol'] = "Crear grupo por patron:";
$string['creategroupenrolinfo'] = "Creará los grupos a partir de un id el cual se deberá asignar en el siguiente campo.";
$string['idcreategroup'] = "teclea el id del patron a seguir:";
$string['idcreategroupinfo'] = "Lista de grupos: <br> GROUPENTIDAD : 10001 = ENTIDAD_MES_ANIO ejemplo: CDMX_12_2022 ";
$string['nameplataform'] = "Nombre de la plataforma:";
$string['nameplataforminfo'] = "Este aparecerá en todos los textos, haciendo referencia al mismo, incluso en el envio de correos, ejemplo: Cursos autogestivos,CVL,etc.";
$string['nameexternal'] = "Nombre de la plataforma externa donde se verifican los usuarios registrados:";
$string['nameexternalinfo'] = "Este aparecerá los textos que hacen referencia a la base de datos externa";
$string['counterviews'] = "Contador de visitas:";
$string['counterviewsinfo'] = "Realiza un conteo de las visitas al registro.";
$string['validaterenapo'] = "Validar Curp con RENAPO:";
$string['validaterenapoinfo'] = "Al aceptar el registro se conectara con la RENAPO validando el CURP que se ingrese";
$string['acceptnotvalidaterenapo'] = "Permitir registros sin validación de RENAPO:";
$string['acceptnotvalidaterenapoinfo'] = "Al activar el registro permitirá a los usuarios que ingresen su CURP registrase aún si la CURP no es encontrada en RENAPO";
$string['acceptcoursesdbexternal'] = "Permitir registros a cursos específicos para base de datos externa:";
$string['acceptcoursesdbexternalinfo'] = "Al activar el registro permitirá a los usuarios de un rol es especifico registrarse a esos cursos, configurar en el siguiente apartado.";
$string['accessbyroltocourse'] = "id de curso y rol con acceso a registro a cursos en específico:";
$string['accessbyroltocourseinfo'] = "Agregar el id de rol seguido de ':' seguido del '{ id de curso }, si es mas de un id se puede separa por ',' si se requiere configurar mas de una regla separe por '|' ejemplo: 10:5|11:{6,7,8}";
$string['accessbyrolenrol'] = "id de curso y rol para matricular en otros cursos";
$string['accessbyrolenrolinfo'] = "Una vez que el usuario confirma su registro y si se requiere matricular a otros cursos, Se deberá agregar el id de rol en la base de datos externa, seguido de el id de curso en el que se desea matricular y finalmente el id de curso en el cual el usurio realizo su registro, si se requiere mas de un curso agregar '|', ejemplo: 10:5:11|6:5:8";
$string['registeruserinothersite'] = "Registro en otro sitio web:";
$string['registeruserinothersiteinfo'] = "Cuando se realiza el registro y se confirma el usuario registra en otra sitio web, se deberá colocar la url seguido de | y finalmente el id de curso en el que se registro el usuario,esto aplicará para todos los usuarios que confirmen ese curso, este campo requiere que el campo 'accessbyrolenrol' este configurado, ejemplo https://www.aula-practica.unadmexico.mx|5";



//Conexión con DB ----
$string['dbconn'] = "Exito";
$string['dbconnerr'] = "Asegurarse de haber configurado el puerto y el nombre de la base de datos";

//Mensajes emergentes
$string['noregismoodle'] = 'Tus datos no han sido registrados previamente, por favor da clic en aceptar para registrarte en el sitio.';
$string['noregisexdb'] = 'Tus datos no han sido registrados previamente, por favor da clic en aceptar para registrarte en el sitio.';
$string['regismoodleyexdb'] = 'Ya existe una cuenta registrada en la base de datos externa y en este sitio, inicia sesión.';
$string['regismoodlenotexdb'] = 'No existe una cuenta registrada ni en este sitio ni en la base de datos externa, regístrate para continuar.';
$string['regismoodle'] = 'Ya existe una cuenta registrada en este sitio, inicia sesión.';
$string['regismoodlenotexdb0'] = 'No existe una cuenta en la base de datos externa pero sí en este sitio, inicia sesión.';
$string['regismoodlenotexdb1'] = 'No existe una cuenta en la base de datos externa pero sí en este sitio, regístrate en la base de datos externa.';
$string['errorconsulta'] = 'No existe una cuenta en la base de datos externa pero sí en este sitio, regístrate en la base de datos externa.';
$string['correoregimoodle'] = 'Existe una cuenta registrada con ese correo en este sitio, inténtalo con otro.';
//$string['regisexdbnotmoodle'] = "Tus datos han sido registrados previamente en la base de datos de la SIGE(UnADM), da clic en continuar para proceder con el registro en el sitio de reforma laboral, (Verifica que los datos sean correctos)";

