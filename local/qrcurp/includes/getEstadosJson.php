<?php
$iddelestado =  $_POST['iddelestado'];
$jsonEstados = '[
    {
        "id_estado": "1",
        "estado": "Aguascalientes",
        "estado_corto": "AS",
        "id_estatus": "1"
    },
    {
        "id_estado": "2",
        "estado": "Baja California",
        "estado_corto": "BC",
        "id_estatus": "1"
    },
    {
        "id_estado": "3",
        "estado": "Baja California Sur",
        "estado_corto": "BS",
        "id_estatus": "1"
    },
    {
        "id_estado": "4",
        "estado": "Campeche",
        "estado_corto": "CC",
        "id_estatus": "1"
    },
    {
        "id_estado": "5",
        "estado": "Coahuila de Zaragoza",
        "estado_corto": "CL",
        "id_estatus": "1"
    },
    {
        "id_estado": "6",
        "estado": "Colima",
        "estado_corto": "CM",
        "id_estatus": "1"
    },
    {
        "id_estado": "7",
        "estado": "Chiapas",
        "estado_corto": "CS",
        "id_estatus": "1"
    },
    {
        "id_estado": "8",
        "estado": "Chihuahua",
        "estado_corto": "CH",
        "id_estatus": "1"
    },
    {
        "id_estado": "9",
        "estado": "Ciudad de México",
        "estado_corto": "DF",
        "id_estatus": "1"
    },
    {
        "id_estado": "10",
        "estado": "Durango",
        "estado_corto": "DG",
        "id_estatus": "1"
    },
    {
        "id_estado": "11",
        "estado": "Guanajuato",
        "estado_corto": "GT",
        "id_estatus": "1"
    },
    {
        "id_estado": "12",
        "estado": "Guerrero",
        "estado_corto": "GR",
        "id_estatus": "1"
    },
    {
        "id_estado": "13",
        "estado": "Hidalgo",
        "estado_corto": "HG",
        "id_estatus": "1"
    },
    {
        "id_estado": "14",
        "estado": "Jalisco",
        "estado_corto": "JC",
        "id_estatus": "1"
    },
    {
        "id_estado": "15",
        "estado": "México",
        "estado_corto": "MC",
        "id_estatus": "1"
    },
    {
        "id_estado": "16",
        "estado": "Michoacán de Ocampo",
        "estado_corto": "MN",
        "id_estatus": "1"
    },
    {
        "id_estado": "17",
        "estado": "Morelos",
        "estado_corto": "MS",
        "id_estatus": "1"
    },
    {
        "id_estado": "18",
        "estado": "Nayarit",
        "estado_corto": "NT",
        "id_estatus": "1"
    },
    {
        "id_estado": "19",
        "estado": "Nuevo León",
        "estado_corto": "NL",
        "id_estatus": "1"
    },
    {
        "id_estado": "20",
        "estado": "Oaxaca",
        "estado_corto": "OC",
        "id_estatus": "1"
    },
    {
        "id_estado": "21",
        "estado": "Puebla",
        "estado_corto": "PL",
        "id_estatus": "1"
    },
    {
        "id_estado": "22",
        "estado": "Querétaro",
        "estado_corto": "QT",
        "id_estatus": "1"
    },
    {
        "id_estado": "23",
        "estado": "Quintana Roo",
        "estado_corto": "QR",
        "id_estatus": "1"
    },
    {
        "id_estado": "24",
        "estado": "San Luis Potosí",
        "estado_corto": "SP",
        "id_estatus": "1"
    },
    {
        "id_estado": "25",
        "estado": "Sinaloa",
        "estado_corto": "SL",
        "id_estatus": "1"
    },
    {
        "id_estado": "26",
        "estado": "Sonora",
        "estado_corto": "SR",
        "id_estatus": "1"
    },
    {
        "id_estado": "27",
        "estado": "Tabasco",
        "estado_corto": "TC",
        "id_estatus": "1"
    },
    {
        "id_estado": "28",
        "estado": "Tamaulipas",
        "estado_corto": "TS",
        "id_estatus": "1"
    },
    {
        "id_estado": "29",
        "estado": "Tlaxcala",
        "estado_corto": "TL",
        "id_estatus": "1"
    },
    {
        "id_estado": "30",
        "estado": "Veracruz de Ignacio de la Llave",
        "estado_corto": "VZ",
        "id_estatus": "1"
    },
    {
        "id_estado": "31",
        "estado": "Yucatán",
        "estado_corto": "YN",
        "id_estatus": "1"
    },
    {
        "id_estado": "32",
        "estado": "Zacatecas",
        "estado_corto": "ZS",
        "id_estatus": "1"
    },
    {
        "id_estado": "39",
        "estado": "En el extranjero",
        "estado_corto": "EN",
        "id_estatus": "1"
    }
]';

$jsonEstados = json_decode($jsonEstados);

foreach($jsonEstados as $estado)
{
    if($estado->id_estado == $iddelestado){
        echo strtoupper($estado->estado);
        break;
    }
}


