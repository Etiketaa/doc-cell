const cartCountSpan = document.getElementById('cart-count');
const cartItemsContainer = document.getElementById('cart-items-container');
const cartTotalSpan = document.getElementById('cart-total');
const confirmOrderButton = document.getElementById('confirmOrderButton');

let cart = [];
let currentProductInModal = null;

window.showProductDetails = function(product) {
    currentProductInModal = product;
    
    document.getElementById('modalProductName').textContent = product.name;
    document.getElementById('modalProductDescription').textContent = product.description;
    document.getElementById('modalProductPrice').textContent = parseFloat(product.price).toFixed(2);

    const carouselInner = document.getElementById('carousel-inner');
    carouselInner.innerHTML = '';

    const mainImage = product.image ? '/static/img/products/' + product.image : null;

    if (mainImage) {
        carouselInner.innerHTML += `<div class="carousel-item active"><img src="${mainImage}" class="d-block w-100" alt="${product.name}"></div>`;
    }

    let images = [];
    try {
        images = product.images ? JSON.parse(product.images) : [];
    } catch (e) {
        console.error("Error parsing product images JSON:", e);
    }

    images.forEach(img => {
        const imgPath = '/static/img/products/' + img;
        carouselInner.innerHTML += `<div class="carousel-item"><img src="${imgPath}" class="d-block w-100" alt="${product.name}"></div>`;
    });

    if (carouselInner.innerHTML === '') {
        carouselInner.innerHTML = '<div class="carousel-item active"><p class="text-center">No hay imágenes disponibles.</p></div>';
    }
    
    const productModal = new bootstrap.Modal(document.getElementById('productModal'));
    productModal.show();
};

document.getElementById('modalAddToCartButton').addEventListener('click', function() {
    if (currentProductInModal) {
        window.addToCart(currentProductInModal);
    }
});

window.addToCart = function(product) {
    const imagePath = product.image ? '/static/img/products/' + product.image : null;

    const existingItem = cart.find(item => item.id === product.id);
    if (existingItem) {
        existingItem.quantity++;
    } else {
        cart.push({ id: product.id, name: product.name, price: product.price, image: imagePath, quantity: 1 });
    }
    updateCartDisplay();
};

function updateCartDisplay() {
    const totalItems = cart.reduce((sum, item) => sum + item.quantity, 0);
    cartCountSpan.textContent = totalItems;
    renderCart();
}

function renderCart() {
    cartItemsContainer.innerHTML = '';
    let total = 0;

    if (cart.length === 0) {
        cartItemsContainer.innerHTML = '<p class="text-center">El carrito está vacío.</p>';
    } else {
        cart.forEach(item => {
            const itemTotal = item.price * item.quantity;
            total += itemTotal;
            const cartItemHtml = `
                <div class="d-flex align-items-center mb-3 border-bottom pb-3">
                    <img src="${item.image}" alt="${item.name}" style="width: 60px; height: 60px; object-fit: cover; margin-right: 15px; border-radius: 5px;">
                    <div class="flex-grow-1">
                        <h5>${item.name}</h5>
                        <p class="mb-0">Cantidad: ${item.quantity} x $${parseFloat(item.price).toFixed(2)}</p>
                    </div>
                    <div class="text-end">
                        <h6>$${itemTotal.toFixed(2)}</h6>
                        <button class="btn btn-sm btn-danger" onclick="removeFromCart(${item.id})"><i class="bi bi-trash"></i></button>
                    </div>
                </div>`;
            cartItemsContainer.innerHTML += cartItemHtml;
        });
    }
    cartTotalSpan.textContent = total.toFixed(2);
}

window.removeFromCart = function(id) {
    cart = cart.filter(item => item.id !== id);
    updateCartDisplay();
};

confirmOrderButton.addEventListener('click', function() {
    if (cart.length === 0) {
        alert('Tu carrito está vacío.');
        return;
    }

    fetch('/api/whatsapp_order', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({ cart: cart }),
    })
    .then(response => response.json())
    .then(data => {
        if (data.whatsappUrl) {
            window.open(data.whatsappUrl, '_blank');
            cart = [];
            updateCartDisplay();
            const cartModal = bootstrap.Modal.getInstance(document.getElementById('cartModal'));
            if (cartModal) {
                cartModal.hide();
            }
        } else {
            alert('Hubo un error al procesar tu pedido.');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Hubo un error de conexión al procesar tu pedido.');
    });
});

document.addEventListener('DOMContentLoaded', () => {
    const typingTitle = document.getElementById('typing-title');
    const phrases = ["Doctor Cell"];
    let phraseIndex = 0;
    let charIndex = 0;
    let isDeleting = false;

    function typeWriter() {
        const currentPhrase = phrases[phraseIndex];
        typingTitle.textContent = currentPhrase.substring(0, charIndex);

        if (!isDeleting) {
            charIndex++;
            if (charIndex > currentPhrase.length) {
                isDeleting = true;
                setTimeout(typeWriter, 1500);
            } else {
                setTimeout(typeWriter, 100);
            }
        } else {
            charIndex--;
            if (charIndex < 0) {
                isDeleting = false;
                phraseIndex = (phraseIndex + 1) % phrases.length;
                setTimeout(typeWriter, 500);
            } else {
                setTimeout(typeWriter, 50);
            }
        }
    }
    typeWriter();

    const sidebarToggleMobile = document.getElementById('sidebarToggleMobile');
    const sidebar = document.getElementById('sidebar');
    const sidebarOverlay = document.getElementById('sidebarOverlay');
    const sidebarClose = document.getElementById('sidebarClose');

    function toggleSidebar() {
        sidebar.classList.toggle('active');
        sidebarOverlay.classList.toggle('active');
    }

    sidebarToggleMobile.addEventListener('click', toggleSidebar);
    sidebarClose.addEventListener('click', toggleSidebar);
    sidebarOverlay.addEventListener('click', toggleSidebar);
});