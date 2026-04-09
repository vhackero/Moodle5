function decodeOnce(codeReader, selectedDeviceId) {
    codeReader.decodeFromInputVideoDevice(selectedDeviceId, 'video').then((result) => {
        //console.log(result)
        qrs = document.getElementById('result').textContent = result.text
        a = document.getElementById("result").innerHTML;
        verifica = a.split("|",1)
        verificar = verifica[0];
        compruebaCurp(verificar);


    }).catch((err) => {
        console.error(err)
        document.getElementById('result').textContent = err
    })
}

function decodeContinuously(codeReader, selectedDeviceId) {
    codeReader.decodeFromInputVideoDeviceContinuously(selectedDeviceId, 'video', (result, err) => {
        if (result) {
            // properly decoded qr code
            //console.log('Found QR code!', result)
            //console.log('Found QR code!')
            document.getElementById('result').textContent = result.text
            a = document.getElementById("result").innerHTML;
            verifica = a.split("|",1)
            verificar = verifica[0];
            //console.log(verificar);
            compruebaCurp(verificar);
        }

        if (err) {
            // As long as this error belongs into one of the following categories
            // the code reader is going to continue as excepted. Any other error
            // will stop the decoding loop.
            //
            // Excepted Exceptions:
            //
            //  - NotFoundException
            //  - ChecksumException
            //  - FormatException

            if (err instanceof ZXing.NotFoundException) {
                console.log('No QR code found.')
            }

            if (err instanceof ZXing.ChecksumException) {
                console.log('A code was found, but it\'s read value was not valid.')
            }

            if (err instanceof ZXing.FormatException) {
                console.log('A code was found, but it was in a invalid format.')
            }
        }
    })
}

window.addEventListener('load', function () {
    let selectedDeviceId;
    let selectedDEviceIDCheck;
    const codeReader = new ZXing.BrowserQRCodeReader()
    //console.log('ZXing code reader initialized')

    codeReader.getVideoInputDevices()
        .then((videoInputDevices) => {
            const sourceSelect = document.getElementById('sourceSelect')
            selectedDeviceId = videoInputDevices[0].deviceId
            selectedDEviceIDCheck = videoInputDevices[0].deviceId;
            const idcheck2 = document.getElementById("cam-secondary");

            const idcheck = document.getElementById("cam-principal");

            idcheck.onchange = () =>{
                if(idcheck.checked == true) {
                    idcheck2.checked = false;
                    selectedDEviceIDCheck = videoInputDevices[0].deviceId;
                    //decodeOnce(codeReader, selectedDEviceIDCheck);
                    decodeContinuously(codeReader, selectedDEviceIDCheck);
                    //console.log("Camara 1")
                }else{
                    codeReader.reset()
                    document.getElementById('result').textContent = '';
                }
            };
            idcheck2.onchange = () =>{
                if(idcheck2.checked == true) {
                    idcheck.checked = false;
                    selectedDEviceIDCheck = videoInputDevices[1].deviceId;
                    //decodeOnce(codeReader, selectedDEviceIDCheck);
                    decodeContinuously(codeReader, selectedDEviceIDCheck);
                    //console.log("Camara 2")

                }else{
                    codeReader.reset()
                    document.getElementById('result').textContent = '';
                }
            };


            if (videoInputDevices.length > 1) {
                document.getElementById("secondary").style.display = "inline";



            }
            //decodeOnce(codeReader, selectedDeviceId);

            /*document.getElementById('startButton').addEventListener('click', () => {

                const decodingStyle = document.getElementById('decoding-style').value;

                if (decodingStyle == "once") {
                   // decodeOnce(codeReader, selectedDeviceId);
                   decodeOnce(codeReader, selectedDEviceIDCheck);
                } else {
                    decodeContinuously(codeReader, selectedDeviceId);

                }
               // console.log(`Started decode from camera with id ${selectedDeviceId}`)
            })

            document.getElementById('resetButton').addEventListener('click', () => {
                codeReader.reset()
                document.getElementById('result').textContent = '';
                console.log('Reset.')
            })
            */

        })
        .catch((err) => {
            console.error(err)
        })
})

