# local_modrules

Plugin local para administrar reglas centrales sobre actividades.

## Reglas incluidas

- Ocultar logs de actividades por tipo, texto en el nombre, roles y alcance de cursos.
- Mostrar actividades solo a roles especificados, respetando fechas comunes de apertura/cierre cuando el módulo las tiene.

## Administración

Después de instalar o actualizar Moodle, entra a:

`Administración del sitio > Plugins > Plugins locales > Reglas de actividades`

Cada regla puede aplicar a todos los cursos o a una selección de cursos. El campo "Tipo de actividad" usa el nombre técnico del módulo, por ejemplo `assign`, `quiz`, `forum` o `scorm`.

## Notas técnicas

La restricción de acceso directo a actividades se aplica en servidor para reglas de tipo "Mostrar actividad solo a roles". El ocultamiento en la página del curso y en reportes de logs se aplica desde hooks de salida, por lo que no modifica los registros históricos ni reemplaza los permisos nativos de reportes de Moodle.
