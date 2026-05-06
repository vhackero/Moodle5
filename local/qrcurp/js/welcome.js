var nameRegsitro = localStorage.getItem('nameCategoria');
const urlPrincipal = window.location.href.split("qrcurp");
const UrlIconos = urlPrincipal[0]+"qrcurp/iconos/";
// alert(nameRegsitro);
var inicial = document.createElement("span");
const showUploadLink = (nameRegsitro === "not-image" || nameRegsitro === "not-image-site");

if (showUploadLink) {
    inicial.innerHTML = "No hay imagen asociada al registro.<br><a href='" + urlPrincipal[0] + "qrcurp/iconos/upload.php' target='_blank' rel='noopener noreferrer'>Subir imagen</a>";
} else {
    inicial.innerHTML = " Elige una opción para registrar tus datos:";
}

const tituloRegistro = showUploadLink ? "Registrarme" : "Registrarme a " + nameRegsitro;
swal({
    content: {
        element: inicial,
    },
    title: tituloRegistro,
    //background: '#91203e',
    //text: "Registrarme a la comunidad de practica del club virtual de lenguas",
    icon: UrlIconos+nameRegsitro+".jpg?v=1",

    buttons: {
        cancel: "Teclea CURP",
        catch: {
            text: "Escaneo QR de CURP",
            value: "qrcurp",
        },

    },

}).then((value) => {
        switch (value) {
            case "qrcurp":
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
