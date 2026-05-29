# local_segmentmenu

Plugin local para mostrar un menu superior con cursos o recursos recomendados segun el segmento del usuario.

## Uso general

1. Crear un campo personalizado de perfil en Moodle.
   - Ruta sugerida: Administracion del sitio > Usuarios > Cuentas > Campos de perfil de usuario.
   - Shortname recomendado: `segmento`.
   - El valor del campo debe coincidir con el segmento que se capture en los elementos del plugin.

2. Configurar el plugin.
   - Ruta: Administracion del sitio > Plugins > Plugins locales > Configuracion de menu por segmento.
   - Campo de perfil para segmento: `segmento` o el shortname que se haya definido.

3. Capturar elementos del menu.
   - Ruta: Administracion del sitio > Plugins > Plugins locales > Menu por segmento.
   - Cada elemento requiere nombre, URL y segmento.
   - Si el segmento se deja vacio, el elemento aparece para todos los usuarios.

4. Validar como usuario segmentado.
   - Al iniciar sesion, el usuario ve el menu "Cursos y apoyos recomendados".
   - El menu muestra enlaces globales y enlaces cuyo segmento coincide con su campo de perfil.

## Ejemplo

- Campo de perfil: `segmento`
- Usuario A: segmento `ventas`
- Usuario B: segmento `soporte`
- Elemento "Curso de ventas": segmento `ventas`
- Elemento "Manual institucional": segmento vacio

Resultado:

- Usuario A ve "Curso de ventas" y "Manual institucional".
- Usuario B ve "Manual institucional".
