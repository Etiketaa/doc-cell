from flask_sqlalchemy import SQLAlchemy
import json

db = SQLAlchemy()



class Product(db.Model):
    __tablename__ = 'products'
    id = db.Column(db.Integer, primary_key=True)
    name = db.Column(db.String(255), nullable=False)
    price = db.Column(db.Float, nullable=False)
    is_available = db.Column(db.Boolean, nullable=True, default=True)
    description = db.Column(db.Text, nullable=False)
    image = db.Column(db.String(255), nullable=True)
    images = db.Column(db.Text, nullable=True)  # Storing as JSON string
    category = db.Column(db.String(100), nullable=True)

    @property
    def normalized_image(self):
        if self.image and 'img/products/' in self.image:
            return self.image.split('img/products/')[-1]
        return self.image

    @property
    def normalized_images(self):
        if not self.images:
            return []
        try:
            image_list = json.loads(self.images)
            return [img.split('img/products/')[-1] for img in image_list]
        except (json.JSONDecodeError, TypeError):
            return []

    def to_dict(self):
        return {
            'id': self.id,
            'name': self.name,
            'price': self.price,
            'is_available': self.is_available,
            'description': self.description,
            'image': self.normalized_image,
            'images': json.dumps(self.normalized_images),
            'category': self.category
        }
