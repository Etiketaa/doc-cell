from flask import Flask, render_template, request, session, redirect, url_for, flash, jsonify
from urllib.parse import quote_plus
from werkzeug.security import check_password_hash, generate_password_hash
from werkzeug.utils import secure_filename
from functools import wraps
import os
import uuid
import json
from config import Config
from models import db, Product, AdminUser


def create_app():
    app = Flask(__name__)
    app.config.from_object(Config)
    app.config['UPLOAD_FOLDER'] = 'static/img/products'
    os.makedirs(app.config['UPLOAD_FOLDER'], exist_ok=True)
    
    db.init_app(app)

    @app.template_filter('fromjson')
    def fromjson_filter(value):
        if value is None:
            return []
        try:
            return json.loads(value)
        except (json.JSONDecodeError, TypeError):
            return []

    # Decorador para proteger rutas de admin
    def admin_required(f):
        @wraps(f)
        def decorated_function(*args, **kwargs):
            if 'admin_loggedin' not in session:
                flash('Por favor, inicia sesión para acceder a esta página.', 'danger')
                return redirect(url_for('admin_login'))
            return f(*args, **kwargs)
        return decorated_function

    def upload_image(file):
        if file and file.filename != '':
            filename = secure_filename(file.filename)
            unique_filename = f"{uuid.uuid4().hex}-{filename}"
            file_path = os.path.join(app.config['UPLOAD_FOLDER'], unique_filename)
            file.save(file_path)
            return unique_filename
        return None

    @app.route('/')
    def index():
        page = request.args.get('page', 1, type=int)
        search_term = request.args.get('search', '')
        category_filter = request.args.get('category', 'all')

        products_query = Product.query

        if search_term:
            products_query = products_query.filter(Product.name.contains(search_term) | Product.description.contains(search_term))

        if category_filter != 'all':
            products_query = products_query.filter_by(category=category_filter)

        pagination = products_query.order_by(Product.id.desc()).paginate(page=page, per_page=18, error_out=False)
        all_products = pagination.items

        latest_products = Product.query.order_by(Product.id.desc()).limit(12).all()
        categories = db.session.query(Product.category).distinct().all()
        categories = [c[0] for c in categories if c[0]]

        products_dicts = [p.to_dict() for p in all_products]
        latest_products_dicts = [p.to_dict() for p in latest_products]

        return render_template('index.html', 
                                 products=products_dicts, 
                                 latest_products=latest_products_dicts,
                                 categories=categories,
                                 pagination=pagination,
                                 search_term=search_term,
                                 active_category=category_filter)

    @app.route('/admin/login', methods=['GET', 'POST'])
    def admin_login():
        if 'admin_loggedin' in session:
            return redirect(url_for('admin_dashboard'))

        if request.method == 'POST':
            username = request.form['username']
            password = request.form['password']
            
            user = AdminUser.query.filter_by(username=username).first()
            
            if user and check_password_hash(user.password, password):
                session['admin_loggedin'] = True
                session['admin_username'] = user.username
                return redirect(url_for('admin_dashboard'))
            else:
                flash('Nombre de usuario o contraseña incorrectos.', 'danger')

        return render_template('admin/login.html')

    @app.route('/admin/logout')
    def admin_logout():
        session.clear()
        flash('Has cerrado sesión exitosamente.', 'success')
        return redirect(url_for('admin_login'))

    @app.route('/admin/dashboard')
    @admin_required
    def admin_dashboard():
        page = request.args.get('page', 1, type=int)
        search_term = request.args.get('search', '')
        category_filter = request.args.get('category', 'all')

        products_query = Product.query

        if search_term:
            products_query = products_query.filter(Product.name.contains(search_term))

        if category_filter != 'all':
            products_query = products_query.filter_by(category=category_filter)

        pagination = products_query.order_by(Product.id.desc()).paginate(page=page, per_page=15, error_out=False)
        products = pagination.items

        categories = db.session.query(Product.category).distinct().all()
        categories = [c[0] for c in categories if c[0]]
        
        total_products = pagination.total

        return render_template('admin/dashboard.html', 
                                 products=products, 
                                 categories=categories,
                                 pagination=pagination,
                                 total_products=total_products,
                                 search_term=search_term,
                                 active_category=category_filter)

    @app.route('/admin/add_product', methods=['GET', 'POST'])
    @admin_required
    def add_product():
        if request.method == 'POST':
            name = request.form['name']
            price = request.form['price']
            is_available = request.form.get('is_available') == '1'
            description = request.form['description']
            category = request.form['category']

            image_path = None
            if 'image' in request.files:
                image_path = upload_image(request.files['image'])

            additional_images = []
            if 'additional_images' in request.files:
                for file in request.files.getlist('additional_images'):
                    img_path = upload_image(file)
                    if img_path:
                        additional_images.append(img_path)

            new_product = Product(
                name=name,
                price=price,
                is_available=is_available,
                description=description,
                category=category,
                image=image_path,
                images=json.dumps(additional_images)
            )
            db.session.add(new_product)
            db.session.commit()
            flash('Producto agregado exitosamente!', 'success')
            return redirect(url_for('admin_dashboard'))

        categories = db.session.query(Product.category).distinct().all()
        categories = [c[0] for c in categories if c[0]]
        return render_template('admin/add_product.html', categories=categories)


    @app.route('/admin/edit_product/<int:product_id>', methods=['GET', 'POST'])
    @admin_required
    def edit_product(product_id):
        product = Product.query.get_or_404(product_id)

        if request.method == 'POST':
            product.name = request.form['name']
            product.price = request.form['price']
            product.is_available = request.form.get('is_available') == '1'
            product.description = request.form['description']
            product.category = request.form['category']

            if 'image' in request.files and request.files['image'].filename != '':
                # Delete old image if it exists
                if product.image and os.path.exists(os.path.join(app.config['UPLOAD_FOLDER'], product.image)):
                    os.remove(os.path.join(app.config['UPLOAD_FOLDER'], product.image))
                product.image = upload_image(request.files['image'])

            # Handle deletion of additional images
            additional_images = json.loads(product.images) if product.images else []
            images_to_delete = request.form.getlist('delete_images')
            for img in images_to_delete:
                if img in additional_images:
                    additional_images.remove(img)
                    if os.path.exists(os.path.join(app.config['UPLOAD_FOLDER'], img)):
                        os.remove(os.path.join(app.config['UPLOAD_FOLDER'], img))
            
            # Handle upload of new additional images
            if 'additional_images' in request.files:
                for file in request.files.getlist('additional_images'):
                    img_path = upload_image(file)
                    if img_path:
                        additional_images.append(img_path)

            product.images = json.dumps(additional_images)

            db.session.commit()
            flash('Producto actualizado exitosamente!', 'success')
            return redirect(url_for('admin_dashboard'))

        categories = db.session.query(Product.category).distinct().all()
        categories = [c[0] for c in categories if c[0]]
        return render_template('admin/edit_product.html', product=product, categories=categories)


    @app.route('/admin/delete_product/<int:product_id>', methods=['POST'])
    @admin_required
    def delete_product(product_id):
        product = Product.query.get_or_404(product_id)

        # Delete images from filesystem
        if product.image and os.path.exists(os.path.join(app.config['UPLOAD_FOLDER'], product.image)):
            os.remove(os.path.join(app.config['UPLOAD_FOLDER'], product.image))
        
        if product.images:
            additional_images = json.loads(product.images)
            for img in additional_images:
                if os.path.exists(os.path.join(app.config['UPLOAD_FOLDER'], img)):
                        os.remove(os.path.join(app.config['UPLOAD_FOLDER'], img))

        db.session.delete(product)
        db.session.commit()
        flash('Producto eliminado exitosamente!', 'success')
        return redirect(url_for('admin_dashboard'))

    # --- User Management Routes ---
    @app.route('/admin/users')
    @admin_required
    def list_users():
        users = AdminUser.query.all()
        return render_template('admin/users.html', users=users)

    @app.route('/admin/add_user', methods=['GET', 'POST'])
    @admin_required
    def add_user():
        if request.method == 'POST':
            username = request.form['username']
            password = request.form['password']

            existing_user = AdminUser.query.filter_by(username=username).first()
            if existing_user:
                flash('Ese nombre de usuario ya existe.', 'danger')
                return redirect(url_for('add_user'))

            hashed_password = generate_password_hash(password)
            new_user = AdminUser(username=username, password=hashed_password)
            db.session.add(new_user)
            db.session.commit()
            flash('Usuario administrador agregado exitosamente.', 'success')
            return redirect(url_for('list_users'))
        return render_template('admin/add_user.html')

    @app.route('/admin/edit_user/<int:user_id>', methods=['GET', 'POST'])
    @admin_required
    def edit_user(user_id):
        user = AdminUser.query.get_or_404(user_id)
        if request.method == 'POST':
            new_username = request.form['username']
            new_password = request.form['password']

            # Check if username is being changed and if it already exists
            if new_username != user.username:
                existing_user = AdminUser.query.filter_by(username=new_username).first()
                if existing_user:
                    flash('Ese nombre de usuario ya está en uso.', 'danger')
                    return redirect(url_for('edit_user', user_id=user_id))
            
            user.username = new_username

            if new_password:
                user.password = generate_password_hash(new_password)
            
            db.session.commit()
            flash('Usuario actualizado exitosamente.', 'success')
            return redirect(url_for('list_users'))

        return render_template('admin/edit_user.html', user=user)

    @app.route('/admin/delete_user/<int:user_id>', methods=['POST'])
    @admin_required
    def delete_user(user_id):
        user_to_delete = AdminUser.query.get_or_404(user_id)
        
        # Safety check: prevent user from deleting themselves
        current_user = AdminUser.query.filter_by(username=session['admin_username']).first()
        if current_user and current_user.id == user_to_delete.id:
            flash('No puedes eliminar tu propia cuenta.', 'danger')
            return redirect(url_for('list_users'))

        db.session.delete(user_to_delete)
        db.session.commit()
        flash('Usuario eliminado exitosamente.', 'success')
        return redirect(url_for('list_users'))

    # --- API Routes ---
    @app.route('/api/whatsapp_order', methods=['POST'])
    def whatsapp_order():
        cart = request.json.get('cart', [])
        if not cart:
            return jsonify({'error': 'El carrito está vacío'}), 400

        message = 'Hola, me gustaría encargar los siguientes productos:\n\n'
        total = 0
        for item in cart:
            item_total = item.get('price', 0) * item.get('quantity', 0)
            total += item_total
            message += f"{item.get('quantity', 0)}x - {item.get('name', '')} - ${item_total:.2f}\n"
        
        message += f"\n*Total: ${total:.2f}*"
        
        # Remember to configure this phone number
        phone_number = "5492914621490"
        whatsapp_url = f"https://wa.me/{phone_number}?text={quote_plus(message)}"
        
        return jsonify({'whatsappUrl': whatsapp_url})

    @app.route('/api/product/<int:product_id>')
    @admin_required
    def get_product_data(product_id):
        product = Product.query.get_or_404(product_id)
        return jsonify(product.to_dict())

    return app

if __name__ == '__main__':
    app = create_app()
    app.run(debug=True)
