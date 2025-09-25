import os
import getpass
from app import create_app, db
from models import AdminUser
from werkzeug.security import generate_password_hash

# This script is used to create the first admin user.
# It should be run once after creating the database tables.
# IMPORTANT: If you are running this for a production database, 
# ensure the DATABASE_URL environment variable is set correctly before execution.

app = create_app()

with app.app_context():
    print("--- Creación de Usuario Administrador ---")
    
    while True:
        username = input("Ingresa el nombre de usuario para el nuevo administrador: ").strip()
        if not username:
            print("El nombre de usuario no puede estar vacío.")
            continue

        existing_user = AdminUser.query.filter_by(username=username).first()
        if existing_user:
            print(f"El usuario ''{username}'' ya existe. Por favor, elige otro nombre.")
            continue
        
        break

    while True:
        password = getpass.getpass("Ingresa la contraseña para el nuevo administrador: ").strip()
        if not password:
            print("La contraseña no puede estar vacía.")
            continue
        
        password_confirm = getpass.getpass("Confirma la contraseña: ").strip()
        if password != password_confirm:
            print("Las contraseñas no coinciden. Inténtalo de nuevo.")
            continue
        
        break

    try:
        hashed_password = generate_password_hash(password)
        new_user = AdminUser(username=username, password=hashed_password)
        db.session.add(new_user)
        db.session.commit()
        print("\n¡Usuario administrador creado exitosamente!")
        print(f"Ahora puedes iniciar sesión con el usuario ''{username}'' en el panel de administración.")
    except Exception as e:
        print(f"\nOcurrió un error al crear el usuario: {e}")