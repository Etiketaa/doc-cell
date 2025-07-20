# Bit House - Sistema de Gestión de Productos

## Descripción

Bit House es una aplicación web diseñada para la gestión de productos. Permite a los administradores agregar, editar y eliminar productos, así como gestionar usuarios. Los usuarios pueden ver los productos disponibles. La aplicación está construida con PHP y utiliza Firebase para la autenticación de usuarios.

## Características

- **Autenticación de Usuarios:** Sistema de inicio y cierre de sesión para usuarios.
- **Gestión de Productos (Administrador):**
  - Agregar nuevos productos.
  - Editar la información de productos existentes.
  - Eliminar productos.
- **Visualización de Productos:** Los usuarios pueden navegar y ver la lista de productos.
- **Panel de Administración:** Interfaz separada para que los administradores gestionen el contenido.

## Tecnologías Utilizadas

- **Frontend:** HTML, CSS, JavaScript
- **Backend:** PHP
- **Base de Datos/Autenticación:** Firebase (Realtime Database y Authentication)
- **Servidor:** Apache (a través de XAMPP)

## Instalación y Configuración

1.  **Clonar el repositorio:**
    ```bash
    git clone <url-del-repositorio>
    ```
2.  **Configurar el entorno:**
    -   Asegúrate de tener un servidor web como XAMPP o WAMP instalado.
    -   Copia los archivos del proyecto en el directorio `htdocs` (para XAMPP) o `www` (para WAMP).
3.  **Configurar Firebase:**
    -   Crea un proyecto en la [consola de Firebase](https://console.firebase.google.com/).
    -   Ve a la configuración de tu proyecto y copia la configuración de tu SDK web.
    -   Pega esta configuración en el archivo `firebase-config.js`.
    -   Asegúrate de habilitar la Autenticación por correo electrónico/contraseña en la consola de Firebase.
4.  **Base de Datos:**
    -   El proyecto utiliza tablas que se pueden crear ejecutando los scripts en la carpeta `includes/`:
        -   `create_users_table.php`
        -   `create_products_table.php`
        -   `create_compradores_table.php`
    -   Asegúrate de que tu archivo `includes/config.php` tenga las credenciales correctas de la base de datos si se utiliza una base de datos SQL además de Firebase.

## Uso

1.  **Iniciar el servidor:** Inicia tu servidor Apache y MySQL desde el panel de control de XAMPP/WAMP.
2.  **Acceder a la aplicación:** Abre tu navegador y ve a `http://localhost/bit-house/`.
3.  **Iniciar Sesión:**
    -   Usa el formulario de `login.html` para iniciar sesión.
    -   Si eres un administrador, serás redirigido al panel de administración.
4.  **Panel de Administración:**
    -   En `admin/`, puedes gestionar productos y usuarios.

## Estructura del Proyecto

```
bit-house/
├── admin/                # Panel de administración
│   ├── add_product.php
│   ├── delete_producto.php
│   ├── edit_product.php
│   ├── index.php
│   └── users/
├── img/                  # Imágenes de productos y de la web
├── includes/             # Archivos de configuración y funciones
│   ├── config.php
│   ├── functions.php
│   └── ...
├── vendor/               # Dependencias de Composer (Firebase PHP)
├── auth.js               # Lógica de autenticación con Firebase
├── firebase-config.js    # Configuración de Firebase
├── index.php             # Página principal
├── login.html            # Formulario de inicio de sesión
├── logout.php            # Script para cerrar sesión
├── perfil.php            # Perfil de usuario
├── products.csv          # Archivo CSV de productos (posiblemente para importación)
├── script.js             # Scripts generales de JavaScript
├── style.css             # Estilos CSS principales
└── ...
```
