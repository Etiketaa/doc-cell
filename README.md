# Doctor Cell

Un catálogo de productos y sistema de pedidos para una tienda de accesorios de celulares, con un panel de administración completo para la gestión de productos y usuarios.

**Repositorio del Proyecto:** [https://github.com/Etiketaa/doc-cell.git](https://github.com/Etiketaa/doc-cell.git)

---

## Tecnologías Utilizadas

*   **Backend:** Python, Flask, SQLAlchemy
*   **Base de Datos:** MySQL
*   **Frontend:** HTML, CSS, JavaScript, Bootstrap 5
*   **Despliegue:** Vercel (Aplicación) y Clever Cloud (Base de Datos)

---

## Configuración para Desarrollo Local

Sigue estos pasos para ejecutar el proyecto en tu máquina local.

### 1. Prerrequisitos

*   Tener instalado Python 3.x.
*   Tener instalado un servidor de MySQL y acceso al mismo.

### 2. Instalación

1.  **Clona el repositorio:**
    ```bash
    git clone https://github.com/Etiketaa/doc-cell.git
    cd doc-cell
    ```

2.  **Crea y activa un entorno virtual:**
    ```bash
    # Para Windows
    python -m venv venv
    .\venv\Scripts\activate

    # Para macOS/Linux
    python3 -m venv venv
    source venv/bin/activate
    ```

3.  **Instala las dependencias:**
    ```bash
    pip install -r requirements.txt
    ```

4.  **Configura tu base de datos local:**
    *   Abre tu cliente de MySQL y crea una nueva base de datos (puedes usar el nombre que prefieras, ej: `doc_cell_db`).
    *   A continuación, ve al archivo `config.py` y asegúrate de que la línea `SQLALCHEMY_DATABASE_URI` coincida con tu configuración (tu usuario, tu contraseña y el nombre de la base de datos que creaste).

### 3. Inicialización de la Base de Datos

Estos scripts solo se ejecutan la primera vez que configuras el proyecto.

1.  **Crea las tablas:**
    ```bash
    python create_tables.py
    ```

2.  **Crea tu primer usuario administrador:**
    ```bash
    python create_admin.py
    ```
    El script te pedirá de forma interactiva un nombre de usuario y una contraseña.

### 4. Ejecuta la Aplicación

```bash
python app.py
```
La aplicación estará disponible en `http://127.0.0.1:5000`.

---

## Despliegue

El proyecto está configurado para un despliegue sencillo en Vercel.

1.  **Base de Datos en la Nube:** Crea una base de datos MySQL en un servicio como [Clever Cloud](https://www.clever-cloud.com/) o [PlanetScale](https://planetscale.com/).

2.  **Configuración en Vercel:**
    *   Importa tu repositorio de GitHub a Vercel.
    *   En la configuración del proyecto en Vercel, ve a **Settings -> Environment Variables**.
    *   Añade la variable de entorno `DATABASE_URL` con la URL de conexión de tu base de datos en la nube.

3.  **Inicialización de la Base de Datos de Producción:**
    *   Antes de que el sitio desplegado funcione, necesitas inicializar la base de datos remota.
    *   En tu terminal local, configura la variable de entorno `DATABASE_URL` para que apunte a tu base de datos de producción y ejecuta los scripts de inicialización:
        ```bash
        # Ejemplo para Windows (cmd.exe)
        set DATABASE_URL=tu-url-de-base-de-datos-remota
        python create_tables.py
        python create_admin.py
        ```

---

## Autor

Desarrollado por **Franco Paredes**.
*   **Portfolio:** [https://francoparedes.vercel.app/](https://francoparedes.vercel.app/)
*   **LinkedIn:** [https://www.linkedin.com/in/francoparedes1992/](https://www.linkedin.com/in/francoparedes1992/)
