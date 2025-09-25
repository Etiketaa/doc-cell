import os
from app import create_app, db

# IMPORTANT: Set the DATABASE_URL environment variable before running this script.
# Example for Windows (Command Prompt):
# set DATABASE_URL=mysql+mysqlconnector://user:password@host:port/dbname
# Example for Linux/macOS:
# export DATABASE_URL=mysql+mysqlconnector://user:password@host:port/dbname

app = create_app()

with app.app_context():
    print("Conectando a la base de datos...")
    try:
        # This command creates all tables based on the models defined in your application
        db.create_all()
        print("¡Tablas creadas exitosamente en la base de datos!")
        print("Por favor, verifica en tu panel de Clever Cloud o cliente de base de datos que las tablas 'admin_users' y 'products' existen.")
    except Exception as e:
        print(f"Ocurrió un error al intentar crear las tablas: {e}")
