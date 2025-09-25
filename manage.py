import click
from werkzeug.security import generate_password_hash
from app import create_app, db
from models import AdminUser
from sqlalchemy import text

app = create_app()

@click.command()
def init_db():
    """Crea las tablas de la base de datos y un usuario admin por defecto."""
    with app.app_context():
        db.create_all()

        # Add is_available column to products table if it doesn't exist
        try:
            db.session.execute(text('ALTER TABLE products ADD COLUMN is_available BOOLEAN DEFAULT 1'))
            db.session.commit()
            click.echo("Columna 'is_available' agregada a la tabla de productos.")
        except Exception as e:
            # A more specific check for mysql connector error might be needed
            if "Duplicate column name" in str(e):
                click.echo("Columna 'is_available' ya existe.")
            else:
                click.echo(f"Error al agregar la columna: {e}")
            db.session.rollback()
        
        user = AdminUser.query.filter_by(username='admin').first()
        hashed_password = generate_password_hash('admin123', method='pbkdf2:sha256')

        if user is None:
            new_admin = AdminUser(username='admin', password=hashed_password)
            db.session.add(new_admin)
            click.echo('Usuario administrador por defecto creado (admin/admin123).')
        else:
            user.password = hashed_password
            click.echo('La contraseña del usuario administrador ha sido actualizada.')
        
        db.session.commit()
        click.echo('Base de datos inicializada y usuario admin configurado.')

if __name__ == "__main__":
    init_db()
