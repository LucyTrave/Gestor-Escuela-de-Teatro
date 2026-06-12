# LSM_M26_GS004_Gest_asistencia-main

Aplicación web en PHP para la gestión de una escuela de teatro. El proyecto organiza la experiencia por roles (`admin`, `profesor`, `alumno`) y cubre operaciones como acceso autenticado, gestión de alumnado, grupos, eventos y recuperación de clases mediante tokens.

## Qué incluye

- Login con redirección por rol.
- Panel de administración con:
  - alumnos posibles
  - alumnos matriculados
  - grupos
  - eventos especiales
- Panel de profesorado con gestión de alumnos, grupos, clases y asistencia.
- Panel de alumnado con:
  - próximas clases
  - aviso de ausencias
  - generación y uso de tokens
  - solicitud de recuperaciones
  - calendario mensual

## Stack

- PHP
- MySQL
- HTML, CSS y JavaScript
- Entorno pensado para XAMPP

No usa Composer ni framework; el enrutado y la carga de controladores se hacen de forma manual.

## Estructura del proyecto

```
LSM_M26_GS004_Gest_asistencia-main/
├── app/
│   ├── controllers/
│   ├── models/
│   └── views/
├── config/
│   └── database.php
├── public/
│   ├── css/
│   └── js/
├── routes/
│   └── web.php
├── index.php
└── setup.php
```

## Requisitos

- XAMPP con Apache y MySQL activos
- PHP con extensión `mysqli`
- Acceso local al directorio `htdocs`

## Puesta en marcha

1. Coloca el proyecto en:
   - `/Applications/XAMPP/xamppfiles/htdocs/LSM_M26_GS004_Gest_asistencia-main`
2. Inicia Apache y MySQL desde XAMPP.
3. Abre en el navegador:
   - [`http://localhost/LSM_M26_GS004_Gest_asistencia-main/setup.php`](http://localhost/LSM_M26_GS004_Gest_asistencia-main/setup.php)
4. El script `setup.php` crea:
   - la base de datos `punto_de_partida`
   - las tablas necesarias
   - salas y grupos iniciales
   - usuarios de demostración
5. Entra en la aplicación desde:
   - [`http://localhost/LSM_M26_GS004_Gest_asistencia-main/`](http://localhost/LSM_M26_GS004_Gest_asistencia-main/)
6. (Opcional) Para datos de prueba:
   - Importa `sql/datos_prueba_alumno.sql` en phpMyAdmin
   - Importa `sql/datos_invitado.sql` en phpMyAdmin

## Configuración de base de datos

La conexión está definida en [config/database.php](/Applications/XAMPP/xamppfiles/htdocs/LSM_M26_GS004_Gest_asistencia-main/config/database.php) con valores por defecto de XAMPP:

- servidor: `localhost`
- usuario: `root`
- contraseña: vacía
- base de datos: `punto_de_partida`

Si tu entorno usa otras credenciales, cambia esos valores antes de arrancar.

## Usuarios demo

Después de ejecutar `setup.php`, quedan disponibles estos accesos:

| Rol | Email | Contraseña |
| --- | --- | --- |
| Admin | `lucia@mail.com` | `1234` |
| Profesor | `luis@mail.com` | `1234` |
| Alumno | `juan@mail.com` | `1234` |

## Rutas principales

### Autenticación

- `/`
- `/login`
- `/logout`
- `/dashboard`

### Admin

- `/admin`
- `/admin/posibles`
- `/admin/matriculados`
- `/admin/grupos`
- `/admin/especiales`
- `/admin/especiales/gestionar`
- `/admin/alumnos/crear`
- `/admin/alumnos/detalle`
- `/admin/alumnos/editar`

### Profesor

- `/profesor`
- `/profesor/alumnos`
- `/profesor/grupos`
- `/profesor/clases`
- `/profesor/asistencia`

### Alumno

- `/alumno`
- `/alumno/tokens`
- `/alumno/recuperar`
- `/alumno/calendario`

## Modelo de datos

El script de instalación crea, entre otras, estas tablas:

- `usuario`
- `admin`
- `profesor`
- `alumno`
- `grupo`
- `alumno_grupo`
- `clase`
- `asistencia`
- `token`
- `recuperacion`
- `bloque_pago`
- `evento_grupal`
- `inscripcion_evento`
- `sala`
- `horario_posible`

La lógica de negocio principal gira alrededor de:

- usuarios y roles
- matrícula y asignación de grupos
- clases programadas
- control de asistencia
- tokens por ausencias avisadas con antelación
- recuperación de clases
- eventos grupales

## Arquitectura

- [index.php](/Applications/XAMPP/xamppfiles/htdocs/LSM_M26_GS004_Gest_asistencia-main/index.php) define `ROOT` y `BASE_URL`, carga la conexión y el enrutador.
- [routes/web.php](/Applications/XAMPP/xamppfiles/htdocs/LSM_M26_GS004_Gest_asistencia-main/routes/web.php) resuelve las rutas mediante condicionales.
- Los controladores están en [app/controllers](/Applications/XAMPP/xamppfiles/htdocs/LSM_M26_GS004_Gest_asistencia-main/app/controllers).
- Las vistas están separadas por rol dentro de [app/views](/Applications/XAMPP/xamppfiles/htdocs/LSM_M26_GS004_Gest_asistencia-main/app/views).

## Notas de funcionamiento

- `BASE_URL` se calcula automáticamente a partir del directorio donde vive el proyecto.
- El acceso al panel cambia según el rol autenticado.
- En alumnado, avisar una ausencia con al menos 24 horas de antelación puede generar un token, con un máximo de 4 tokens disponibles.
- La caducidad del token demo está fijada actualmente en `30-06-2026`.

## Estado del proyecto

En desarrollo.
