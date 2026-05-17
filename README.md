# SecureApp — Sistema de Autenticación PHP

Sistema web completo de autenticación con PHP, MySQL y XAMPP.

---

## 📁 Estructura de archivos

```
sistema_auth/
├── index.php                  ← Redirección automática
├── registro.php               ← Registro de usuarios
├── login.php                  ← Inicio de sesión
├── perfil.php                 ← Zona privada (requiere sesión)
├── cambiar_password.php       ← Cambio de contraseña
├── logout.php                 ← Cierre de sesión
├── database.sql               ← Script para crear la BD
├── css/
│   └── estilo.css             ← Estilos del sistema
└── includes/
    ├── config.php             ← Configuración de BD y conexión PDO
    └── auth.php               ← Funciones de autenticación
```

---

## 🚀 Pasos de instalación

### 1. Preparar XAMPP
- Abre **XAMPP Control Panel**
- Inicia **Apache** y **MySQL**

### 2. Crear la base de datos
- Abre tu navegador y ve a: `http://localhost/phpmyadmin`
- Haz clic en **"SQL"** (pestaña superior)
- Pega el contenido del archivo **`database.sql`** y haz clic en **"Continuar"**
- Esto creará la base de datos `sistema_auth` con la tabla `usuarios`

### 3. Copiar los archivos
- Copia la carpeta **`sistema_auth`** dentro de:
  ```
  C:\xampp\htdocs\sistema_auth\
  ```

### 4. Verificar configuración
- Abre `includes/config.php`
- Confirma que los valores sean correctos:
  ```php
  define('DB_HOST', 'localhost');
  define('DB_NAME', 'sistema_auth');
  define('DB_USER', 'root');
  define('DB_PASS', '');   // Vacío por defecto en XAMPP
  ```

### 5. Probar el sistema
- Abre el navegador y ve a: `http://localhost/sistema_auth/`
- Serás redirigido al **login**

---

## 🧪 Usuario de prueba

| Campo    | Valor            |
|----------|------------------|
| Correo   | demo@correo.com  |
| Contraseña | password       |

> ⚠️ Este usuario es solo para pruebas. Regístralo correctamente desde el formulario en producción.

---

## 🔒 Características de seguridad implementadas

| Característica | Detalle |
|---|---|
| `password_hash` | Algoritmo BCRYPT con cost=12 |
| `password_verify` | Verificación segura en login y cambio de contraseña |
| Protección CSRF | Token en todos los formularios POST |
| Sesiones seguras | `session_regenerate_id` al hacer login |
| Cookies HttpOnly | Protección contra XSS en cookies de sesión |
| Protección XSS | `htmlspecialchars` en todas las salidas |
| Consultas seguras | PDO con prepared statements (evita SQL Injection) |
| Validación servidor | Todos los inputs validados en PHP |

---

## 📄 Descripción de cada archivo

### `includes/config.php`
Configura la conexión PDO a MySQL. Contiene la función `conectar()` que usa el patrón singleton para reutilizar la conexión.

### `includes/auth.php`
Funciones auxiliares:
- `iniciarSesion()` — Inicia sesión PHP con parámetros seguros
- `estaAutenticado()` — Verifica si hay sesión activa
- `requiereAutenticacion()` — Redirige si no hay sesión
- `crearSesion($usuario)` — Registra al usuario en sesión
- `destruirSesion()` — Elimina completamente la sesión
- `e($valor)` — Escapa HTML (anti-XSS)
- `esCorreoValido($correo)` — Valida formato de correo
- `generarCsrf()` / `verificarCsrf()` — Tokens CSRF

### `registro.php`
- Valida cédula, nombre, correo y contraseña
- Verifica correo/cédula no duplicados
- Guarda contraseña con `password_hash()`

### `login.php`
- Valida credenciales contra la BD
- Usa `password_verify()` para comparar
- Crea sesión segura con `session_regenerate_id()`

### `perfil.php`
- Requiere sesión activa (`requiereAutenticacion()`)
- Muestra y permite actualizar nombre y correo
- Valida que el nuevo correo no esté en uso

### `cambiar_password.php`
- Requiere sesión activa
- Verifica contraseña actual con `password_verify()`
- Guarda nueva contraseña con `password_hash()`

### `logout.php`
- Destruye la sesión completamente
- Redirige al login
