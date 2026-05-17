# 🔐 Sistema de Autenticación PHP + MySQL

Sistema web que implementa autenticación de usuarios con manejo seguro de sesiones, zona privada de perfil y cambio de contraseña, desarrollado con PHP y MySQL usando XAMPP como entorno local.

---

## 📋 Descripción

Esta aplicación permite a los usuarios registrarse, iniciar sesión y gestionar su perfil de forma segura. Fue desarrollada como práctica de los conceptos de autenticación, manejo de sesiones y actualización segura de datos en PHP.

**Funcionalidades principales:**
- Registro de usuarios con validación de datos
- Login con verificación segura de credenciales
- Zona privada de perfil (solo accesible con sesión activa)
- Actualización de nombre y correo electrónico
- Cambio de contraseña verificando la contraseña actual
- Cierre de sesión que destruye completamente la sesión

---

## 🛠️ Requisitos

| Herramienta | Versión recomendada |
|---|---|
| XAMPP | 8.x o superior |
| PHP | 8.0 o superior |
| MySQL | 5.7 o superior |
| Navegador | Cualquier navegador moderno |

> No se requieren librerías externas. Todo el sistema usa PHP nativo y PDO.

---

## 📁 Estructura de archivos

```
sistema_auth/
├── index.php                  ← Redirección automática (login o perfil)
├── registro.php               ← Formulario de registro de usuarios
├── login.php                  ← Formulario de inicio de sesión
├── perfil.php                 ← Zona privada: ver y actualizar perfil
├── cambiar_password.php       ← Cambio seguro de contraseña
├── logout.php                 ← Cierre de sesión
├── database.sql               ← Script SQL para crear la base de datos
├── css/
│   └── estilo.css             ← Estilos del sistema (tema oscuro)
└── includes/
    ├── config.php             ← Conexión PDO a MySQL
    └── auth.php               ← Funciones de autenticación y sesión
```

---

## 🚀 Instalación y prueba local

### 1. Clonar o descargar el repositorio

```bash
git clone https://github.com/tu-usuario/sistema_auth.git
```

O descarga el ZIP desde GitHub y descomprímelo.

### 2. Copiar a XAMPP

Mueve la carpeta `sistema_auth` dentro de:

```
C:\xampp\htdocs\sistema_auth\
```

### 3. Iniciar XAMPP

- Abre **XAMPP Control Panel**
- Inicia los servicios **Apache** y **MySQL**

### 4. Crear la base de datos

- Abre el navegador y ve a `http://localhost/phpmyadmin`
- Haz clic en la pestaña **SQL**
- Pega el contenido del archivo `database.sql` y haz clic en **Continuar**

Esto creará automáticamente la base de datos `sistema_auth` con la tabla `usuarios`.

### 5. Verificar la configuración

Abre `includes/config.php` y confirma que los datos coincidan con tu entorno:

```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'sistema_auth');
define('DB_USER', 'root');
define('DB_PASS', '');        // En XAMPP por defecto está vacío
```

### 6. Abrir en el navegador

```
http://localhost/sistema_auth/
```

Serás redirigido al login automáticamente.

---

## 🧪 Prueba rápida

Puedes registrar tu propio usuario desde el formulario, o usar el usuario de demostración que se crea con el script SQL:

| Campo | Valor |
|---|---|
| Correo | demo@correo.com |
| Contraseña | password |

---

## 🔒 Seguridad implementada

| Mecanismo | Implementación |
|---|---|
| Hash de contraseñas | `password_hash()` con BCRYPT, cost=12 |
| Verificación de contraseña | `password_verify()` — nunca se compara en texto plano |
| Protección CSRF | Token único por sesión en todos los formularios POST |
| Prevención de SQL Injection | PDO con prepared statements en todas las consultas |
| Prevención de XSS | `htmlspecialchars()` en todas las salidas a HTML |
| Session Fixation | `session_regenerate_id(true)` al autenticarse |
| Cookies seguras | HttpOnly y SameSite=Strict |
| Acceso restringido | Verificación de sesión activa en páginas privadas |

---

## 📄 Descripción de archivos clave

**`includes/config.php`** — Configura la conexión PDO a MySQL con manejo de errores. Usa el patrón singleton para no abrir múltiples conexiones.

**`includes/auth.php`** — Centraliza toda la lógica de autenticación: iniciar sesión, verificar si está autenticado, crear/destruir sesión, generar tokens CSRF y escapar salidas HTML.

**`registro.php`** — Valida cédula, nombre, correo y contraseña. Verifica que el correo y la cédula no estén duplicados antes de insertar.

**`login.php`** — Busca al usuario por correo y verifica la contraseña con `password_verify()`. Crea la sesión solo si las credenciales son correctas.

**`perfil.php`** — Página privada que redirige al login si no hay sesión. Permite actualizar nombre y correo con validación en servidor.

**`cambiar_password.php`** — Pide la contraseña actual, la verifica con `password_verify()`, y guarda la nueva con `password_hash()`.

**`logout.php`** — Vacía `$_SESSION`, elimina la cookie de sesión y destruye la sesión del servidor.
