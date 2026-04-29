caja = document.getElementById("register-terms_of_service");
caja.addEventListener("click", () => {
    if(caja.checked == true){
        registrardbtwo = document.getElementById('verifica').disabled = false;
    }else{
        registrardbtwo = document.getElementById('verifica').disabled = true;
    }
});

var encuentradatos = document.getElementById("existeuserdb").innerHTML;
var activedate = document.getElementById("compruebaActivo").innerHTML;
var despachador = document.getElementById("despachador").innerHTML;
var externalinsert = document.getElementById("external").innerHTML;
var menssage = document.getElementById("message").innerHTML;
var inactivoToGeneral = document.getElementById("inactivotogeneral").innerHTML; //LFAS 26/01/23
var aceptaregistrospublico = document.getElementById("publicogeneral").innerHTML; //Para aceptar registros de publico en general
var publicogeneralmsg = document.getElementById("publicogeneralmsg") ? document.getElementById("publicogeneralmsg").innerHTML : "El registro no esta disponible para publico en general";
var nameExternalData =  localStorage.getItem('nameExternalData');
var namePlataform =  localStorage.getItem('namePlataform');
if(despachador == 3 && activedate == 0 && encuentradatos ==1 ){
    //redireccion index
    //alert("El usuario ya esta registrado en el portal de extención universitaria y en SIGE, pero se encuentra dado de baja en SIGE");
    swal("El usuario ya esta registrado en "+namePlataform+" y en "+nameExternalData+", pero se encuentra dado de baja en "+nameExternalData, {
        buttons: "Aceptar",
        timer: 10000,
    })
        .then((value) => {
            window.location.href = "index.php";
        });
}if(despachador == 3 && activedate == 1 && encuentradatos ==1 ){
    swal(menssage, {
        buttons: "Aceptar",
        timer: 10000,
    })
        .then((value) => {
            window.location.href = "index.php";
        });
}

if(despachador == 1 && activedate == 1 && encuentradatos ==1  ){
    //continua con el registro
    //alert("El usuario ya esta registrado en SIGE, da clic en Aceptar para continuar con tu registro en el portal de extención universitaria");
    swal(menssage, {
        buttons: "Aceptar",
        timer: 10000,
    });
    document.getElementById('pass').setAttribute("placeholder","Misma contraseña que en "+nameExternalData);
    setTimeout(function (){
        dato = document.getElementById("envia-info").querySelectorAll(".form-control");
        datoExcept = document.getElementById("envia-info").querySelectorAll(".notdisabled");
        tam = dato.length;
        //console.log(tam);
            for(i=0; i<= tam-1; i++){
                if(dato[i].value != '') {
                    dato[i].setAttribute("readonly", "");
                    dato[i].classList.add('control-data-form');
                }
            }
            for(i=0; i<= datoExcept.length -1; i++){
                if(datoExcept[i].value == '-' || datoExcept[i].value == '0' ) {
                    datoExcept[i].removeAttribute("readonly");
                    datoExcept[i].classList.remove('control-data-form');
                }
            }
            if(document.getElementById("categorias") != null ) {
                document.getElementById("categorias").removeAttribute("readonly");
                document.getElementById("categorias").classList.remove('control-data-form');
                if(document.getElementById("grupos") != null ) {
                document.getElementById("grupos").removeAttribute("readonly");
                document.getElementById("grupos").classList.remove('control-data-form');
                }
            }
            if (typeof window.applyEditableAutofilledOverrides === 'function') {
                window.applyEditableAutofilledOverrides();
            }
            if (typeof window.syncPasswordFromAlias === 'function') {
                window.syncPasswordFromAlias();
            }
    },5000);

}if(despachador == 4 && encuentradatos==0 && externalinsert == 1 ){
    //continua con el registro si esta activada la opcion de registar en la bd externa
    swal(menssage, {
        buttons: "Aceptar",
        timer: 10000,
    });
    //alert("Completa el formulario para registarte en el portal de extención universitaria");
}
if(despachador == 1 && activedate == 0 && encuentradatos ==1  ){
    const elurl = document.createElement('div')
    elurl.innerHTML = "<a href='https://gestionescolar.unadmexico.mx/servicios/recuperar/' target='_blank'>Clic aquí</a>"

    document.getElementById("envia-info").remove();
    //alert("El usuario ya esta registrado en SIGE, , pero se encuentra dado de baja en SIGE, revisa tu situación con el departamento correspondiente");
    menssage="El usuario está registrado en "+nameExternalData+", pero no está activo o no tiene todos sus datos registrados. \n" +
        "\n" +
        "Para registrarte a "+namePlataform+", debes asegurar la activación de tu cuenta en "+nameExternalData+". Entra a "+nameExternalData+" y registra correctamente tus datos. Si no recuerdas tus datos de acceso, puedes recuperarlos en la siguiente URL: "
    swal(menssage, {
        content: elurl,
        buttons: "Aceptar",
        timer: 19000,
    })
        .then((value) => {
            // redirect = document.getElementById("index-sesion-moodle").href;
            // window.location.href = redirect
            window.location.href = window.location.href = "index.php";
        });
}
else {
    if(despachador == 3){
        swal(menssage, {
            buttons: "Aceptar",
            timer: 10000,
        })
            .then((value) => {
                window.location.href = "index.php";
            });
    }
    if (encuentradatos == 0 ){
        if(despachador == 4){
            if(aceptaregistrospublico == 1){
                //CUANDO SE REGISTRAN PUBLICO EN GENERAL
                document.getElementById('pass').removeAttribute('readonly');
                setTimeout(function (){document.getElementById("id_country").removeAttribute("readonly");
                    document.getElementById("id_country").classList.remove('control-data-form');},5000);
                menssage = "Completa los campos del registro de "+namePlataform+" para continuar."
                swal(menssage, {
                    buttons: "Aceptar",
                    timer: 4000,
                });
            }else {
                document.getElementById("envia-info").remove();

                //cambia el envio de la información al registro de moodle
                menssage = publicogeneralmsg;
                swal(menssage, {
                    buttons: "Aceptar",
                    timer: 4000,
                }).then((value) => {
                    window.location.href = "index.php";
                });
            }
        }

    }

}
function viewPassword(){
    showPassword = document.querySelector('.show-password');
    password1 = document.querySelector('.password');
    if ( password1.type === "text" ) {
        password1.type = "password"
        showPassword.classList.remove('fa-eye-slash');
    } else {
        password1.type = "text"
        showPassword.classList.toggle("fa-eye-slash");
    }
}
