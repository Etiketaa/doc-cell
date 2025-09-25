document.addEventListener('DOMContentLoaded', function () {
    var editProductModal = document.getElementById('editProductModal');
    if (editProductModal) {
        editProductModal.addEventListener('show.bs.modal', function (event) {
            var button = event.relatedTarget;
            var productId = button.getAttribute('data-product-id');
            
            var form = document.getElementById('editProductForm');
            form.action = '/admin/edit_product/' + productId;

            // Limpiar el formulario y contenedores de imágenes
            form.reset();
            document.getElementById('current-main-image-container').innerHTML = '';
            document.getElementById('current-additional-images-container').innerHTML = '';

            // Fetch product data from the API
            fetch('/api/product/' + productId)
                .then(response => response.json())
                .then(data => {
                    if (data) {
                        document.getElementById('edit-name').value = data.name || '';
                        document.getElementById('edit-price').value = data.price || '';
                        document.getElementById('edit-category').value = data.category || '';
                        document.getElementById('edit-description').value = data.description || '';
                        document.getElementById('edit-is_available').checked = data.is_available;

                        // Display main image
                        if (data.image) {
                            var mainImg = document.createElement('img');
                            mainImg.src = '/static/img/products/' + data.image;
                            mainImg.className = 'img-thumbnail';
                            mainImg.style.maxWidth = '150px';
                            document.getElementById('current-main-image-container').appendChild(mainImg);
                        }

                        // Display additional images
                        var additionalImagesContainer = document.getElementById('current-additional-images-container');
                        var images = data.images;
                        if (images && images.length > 0) {
                            images.forEach(function(imageName) {
                                var imgContainer = document.createElement('div');
                                imgContainer.className = 'position-relative';

                                var img = document.createElement('img');
                                img.src = '/static/img/products/' + imageName;
                                img.className = 'img-thumbnail';
                                img.style.width = '100px';
                                img.style.height = '100px';
                                img.style.objectFit = 'cover';

                                var checkbox = document.createElement('input');
                                checkbox.type = 'checkbox';
                                checkbox.name = 'delete_images';
                                checkbox.value = imageName;
                                checkbox.className = 'form-check-input position-absolute top-0 start-0 m-1';
                                checkbox.title = 'Marcar para eliminar';

                                imgContainer.appendChild(img);
                                imgContainer.appendChild(checkbox);
                                additionalImagesContainer.appendChild(imgContainer);
                            });
                        }
                    }
                })
                .catch(error => console.error('Error fetching product data:', error));
        });
    }
});