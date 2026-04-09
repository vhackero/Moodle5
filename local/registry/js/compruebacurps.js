//Función para validar una CURP
function curpValida(curp) {
    var re = /^([A-Z][AEIOUX][A-Z]{2}\d{2}(?:0[1-9]|1[0-2])(?:0[1-9]|[12]\d|3[01])[HM](?:AS|B[CS]|C[CLMSH]|D[FG]|G[TR]|HG|JC|M[CNS]|N[ETL]|OC|PL|Q[TR]|S[PLR]|T[CSL]|VZ|YN|ZS)[B-DF-HJ-NP-TV-Z]{3}[A-Z\d])(\d)$/,
        validado = curp.match(re);

    if (!validado)  //Coincide con el formato general?
        return false;

    //Validar que coincida el dígito verificador
    function digitoVerificador(curp17) {
        //Fuente https://consultas.curp.gob.mx/CurpSP/
        var diccionario  = "0123456789ABCDEFGHIJKLMNÑOPQRSTUVWXYZ",
            lngSuma      = 0.0,
            lngDigito    = 0.0;
        for(var i=0; i<17; i++)
            lngSuma = lngSuma + diccionario.indexOf(curp17.charAt(i)) * (18 - i);
        lngDigito = 10 - lngSuma % 10;
        if (lngDigito == 10) return 0;
        return lngDigito;
    }

    if (validado[2] != digitoVerificador(validado[1]))
        return false;

    return true; //Validado
}


//Handler para el evento cuando cambia el input
//Lleva la CURP a mayúsculas para validarlo
function validarInput(input) {
    var curp = input.value.toUpperCase(),
        resultado = document.getElementById("resultado"),
        valido = "No válido";

    if (curpValida(curp, true)) { // ⬅️ Acá se comprueba
        valido = "Válido";
        resultado.classList.add("ok");
        document.getElementById("continuar").disabled = false;

    } else {
        resultado.classList.remove("ok");
        document.getElementById("continuar").disabled = true;
    }

    resultado.innerText = "CURP: " + curp + "\nFormato: " + valido;
}
//Valida CURP ESCANEADO
function compruebaCurp(verificar){
    //console.log(this.verificar);
    tmpcurp =this.verificar
    formato = /^([A-Z][AEIOUX][A-Z]{2}\d{2}(?:0[1-9]|1[0-2])(?:0[1-9]|[12]\d|3[01])[HM](?:AS|B[CS]|C[CLMSH]|D[FG]|G[TR]|HG|JC|M[CNS]|N[ETL]|OC|PL|Q[TR]|S[PLR]|T[CSL]|VZ|YN|ZS)[B-DF-HJ-NP-TV-Z]{3}[A-Z\d])(\d)$/;
    comprueba = formato.test(tmpcurp);
    tam = tmpcurp.length;
    //console.log(tam)
    if(tam == 18 && comprueba == true){
        cp = document.getElementById("curp");
        cp.value = a;
        //console.log(cp);
        swal("¡CURP válida!", "Obteniendo datos...", "success");
        //swal("CURP VÁLIDO", "success");
        document.getElementById("controler-curp").submit();
        codeReader.stopContinuousDecode();
    }else{
        swal("¡CURP no válida!", "¡Intentalo nuevamente!", "error");
        //swal("INGRESA UNA CURP VÁLIDA");
        //location.reload();
    }
}