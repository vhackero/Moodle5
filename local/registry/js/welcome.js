var nameRegsitro = localStorage.getItem('nameCategoria');
const urlPrincipal = window.location.href.split("registry");
const UrlIconos = urlPrincipal[0]+"registry/iconos/";
// alert(nameRegsitro);
var inicial = document.createElement("span");
inicial.innerHTML = " Elige una opción para registrar tus datos:";
swal({
    content: {
        element: inicial,
    },
    title: "Registrarme a "+nameRegsitro,
    //background: '#91203e',
    //text: "Registrarme a la comunidad de practica del club virtual de lenguas",
    icon: UrlIconos+nameRegsitro+".jpg?v=1",

    buttons: {
        cancel: "Teclea CURP",
        catch: {
            text: "Escaneo QR de CURP",
            value: "registry",
        },

    },

})
    .then((value) => {
        switch (value) {
            case "registry":
                $(document).ready(function(){
                    $("#envia-info").css("display","none");
                    $("#dos_form").css("display","block");
                    $("#texto-a-mostrar").html("<h1 id='.'texto-a-mostrar'.'>Por favor, escanea tu CURP. Si no la tienes genérala aquí: <a target='.'_blank'.' href=https://www.gob.mx/curp/ >Generar CURP.</a></h1>");
                });
                break;
            default:
                // $(document).ready(function(){
                //     $("#envia-info").css("display","none");
                //     $("#dos_form").css("display","block");
                //     $("#texto-a-mostrar").html("<h1 id='.'texto-a-mostrar'.'>Por favor, escanea tu CURP. Si no la tienes genérala aquí: <a target='.'_blank'.' href=https://www.gob.mx/curp/ >Generar CURP.</a></h1>");
                //     showModalWelcome();
                // });
            $(document).ready(function(){
                $("#muestra-curp").css("display","none");
                $("#controler-curp").css("display","none");
                $("#dos_form").css("display","block");
            });
        }
    });
