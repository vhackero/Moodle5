# local_segmentmenu

Plugin local para mostrar un menú superior con cursos o recursos recomendados según el segmento del usuario.

## Uso general

1. Crear un campo personalizado de perfil en Moodle.
   - Ruta sugerida: Administración del sitio > Usuarios > Cuentas > Campos de perfil de usuario.
   - Shortname recomendado: `segmento`.
   - El valor del campo debe coincidir con el segmento que se capture en los elementos del plugin.

2. Configurar el plugin.
   - Ruta: Administración del sitio > Plugins > Plugins locales > Configuración de menú por segmento.
   - Campo de perfil para segmento: `segmento` o el shortname que se haya definido.
   - Posición del menú:
     - Flotante del lado derecho.
     - Flotante del lado izquierdo.
     - Menú superior fijo.
   - Opcionalmente se puede usar el constructor rápido de menú.

3. Capturar elementos del menú.
   - Ruta: Administración del sitio > Plugins > Plugins locales > Menú por segmento.
   - Cada elemento requiere nombre, URL, destino del enlace y modo de visibilidad.
   - Los cursos se seleccionan desde un multiselect con los cursos existentes de Moodle para definir en qué cursos se muestra el elemento.
   - Si no se seleccionan cursos, el elemento se muestra en todo el sitio y en todos los cursos.
   - El modo de visibilidad puede ser por segmento, por rol de curso o por la combinación de ambos.
   - Si el segmento se deja vacío, no restringe por segmento.
   - Si los roles se dejan vacíos, no restringe por rol.
   - El destino permite abrir en la misma pestaña o en una nueva pestaña.

4. Validar como usuario segmentado.
   - Al iniciar sesión, el usuario ve el menú "Cursos y apoyos recomendados".
   - El menú muestra enlaces globales y enlaces cuyo segmento coincide con su campo de perfil.

## Ejemplo

- Campo de perfil: `segmento`
- Usuario A: segmento `ventas`
- Usuario B: segmento `soporte`
- Rol de curso para Usuario A: `student`
- Elemento "Curso de ventas": segmento `ventas`
- Elemento "Material para estudiantes": rol `student`
- Elemento "Ventas estudiantes": segmento `ventas`, rol `student`, modo `both`
- Elemento "Manual institucional": segmento vacío

Resultado:

- Usuario A ve "Curso de ventas", "Material para estudiantes", "Ventas estudiantes" y "Manual institucional".
- Usuario B ve "Manual institucional".

## Constructor rápido

En la configuración del plugin se puede capturar una lista de elementos con una sintaxis similar al menú personalizado de Moodle.

Formato:

`Nombre|URL|Segmento|Destino|Roles|Modo`

Ejemplos:

```text
Curso de ventas|/course/view.php?id=4|ventas|same||segment
Manual institucional|https://example.com/manual||new
Material estudiantes|/course/view.php?id=9||same|student|role
Ventas estudiantes|/course/view.php?id=10|ventas|same|student|both
Curso soporte|/course/view.php?id=8|soporte|same||segment
```

Notas:

- `Segmento` puede quedar vacío para no restringir por segmento.
- `Destino` acepta `same` para misma pestaña o `new` para nueva pestaña.
- En la pantalla de administración, los roles se seleccionan desde un multiselect con los roles existentes del sitio.
- `Roles` en el constructor rápido acepta shortnames de roles de curso separados por coma, por ejemplo `student,teacher`.
- `Modo` acepta `segment`, `role` o `both`.
- El formato anterior `Nombre|URL|Segmento|Destino` sigue funcionando y se interpreta como modo `segment`.
- Los elementos configurados en el constructor rápido se combinan con los elementos capturados desde la pantalla de administración.
