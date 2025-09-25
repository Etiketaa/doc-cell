document.addEventListener('DOMContentLoaded', () => {
    const dropZone = document.getElementById('image-drop-zone');
    const fileInput = document.getElementById('file-input');
    const previewContainer = document.getElementById('image-preview-container');
    const productForm = document.querySelector('form.card');

    let uploadedFiles = []; // Array to store File objects
    let mainImageIndex = 0; // Index of the main image in the uploadedFiles array

    if (!dropZone || !fileInput || !previewContainer || !productForm) {
        // Elements not found, do not proceed
        return;
    }

    // --- Event Listeners ---
    dropZone.addEventListener('click', () => fileInput.click());
    fileInput.addEventListener('change', (e) => handleFiles(e.target.files));

    dropZone.addEventListener('dragover', (e) => {
        e.preventDefault();
        dropZone.classList.add('border-primary', 'text-primary');
    });

    dropZone.addEventListener('dragleave', (e) => {
        e.preventDefault();
        dropZone.classList.remove('border-primary', 'text-primary');
    });

    dropZone.addEventListener('drop', (e) => {
        e.preventDefault();
        dropZone.classList.remove('border-primary', 'text-primary');
        handleFiles(e.dataTransfer.files);
    });

    productForm.addEventListener('submit', handleFormSubmit);

    // --- Core Functions ---
    function handleFiles(files) {
        for (const file of files) {
            if (file.type.startsWith('image/')) {
                uploadedFiles.push(file);
                createPreview(file, uploadedFiles.length - 1);
            }
        }
        updatePreviews();
    }

    function createPreview(file, index) {
        const reader = new FileReader();
        reader.onload = (e) => {
            const previewWrapper = document.createElement('div');
            previewWrapper.className = 'image-preview-wrapper border rounded p-2';
            previewWrapper.setAttribute('data-index', index);

            const img = document.createElement('img');
            img.src = e.target.result;
            img.className = 'img-thumbnail';

            const controls = document.createElement('div');
            controls.className = 'image-preview-controls mt-2 d-flex justify-content-between';

            const setMainBtn = document.createElement('button');
            setMainBtn.type = 'button';
            setMainBtn.className = 'btn btn-sm btn-outline-primary set-main-btn';
            setMainBtn.innerHTML = '<i class="fas fa-star"></i> Principal';
            setMainBtn.addEventListener('click', () => setMainImage(index));

            const removeBtn = document.createElement('button');
            removeBtn.type = 'button';
            removeBtn.className = 'btn btn-sm btn-outline-danger';
            removeBtn.innerHTML = '<i class="fas fa-trash"></i>';
            removeBtn.addEventListener('click', () => removeImage(index));

            controls.appendChild(setMainBtn);
            controls.appendChild(removeBtn);
            previewWrapper.appendChild(img);
            previewWrapper.appendChild(controls);
            previewContainer.appendChild(previewWrapper);
        };
        reader.readAsDataURL(file);
    }

    function updatePreviews() {
        const wrappers = previewContainer.querySelectorAll('.image-preview-wrapper');
        wrappers.forEach(wrapper => {
            const index = parseInt(wrapper.getAttribute('data-index'), 10);
            if (index === mainImageIndex) {
                wrapper.classList.add('border-success', 'border-3');
                wrapper.querySelector('.set-main-btn').classList.add('active');
            } else {
                wrapper.classList.remove('border-success', 'border-3');
                wrapper.querySelector('.set-main-btn').classList.remove('active');
            }
        });
    }

    function setMainImage(index) {
        mainImageIndex = index;
        updatePreviews();
    }

    function removeImage(index) {
        // This is a simplified removal. For a more robust solution, we'd need to handle index shifts.
        // For now, we'll just nullify the entry and filter it out during submission.
        uploadedFiles[index] = null;
        const wrapper = previewContainer.querySelector(`[data-index="${index}"]`);
        if (wrapper) {
            wrapper.remove();
        }
        // If the main image was deleted, set the new main image to the first available one.
        if (mainImageIndex === index) {
            const newMainIndex = uploadedFiles.findIndex(f => f !== null);
            mainImageIndex = newMainIndex !== -1 ? newMainIndex : 0;
            updatePreviews();
        }
    }

    async function handleFormSubmit(e) {
        e.preventDefault();

        const formData = new FormData(productForm);
        const validFiles = uploadedFiles.filter(f => f !== null);

        if (validFiles.length === 0) {
            alert('Por favor, sube al menos una imagen.');
            return;
        }

        // Clear existing image fields to avoid conflicts
        formData.delete('image');
        formData.delete('additional_images');

        // Append files
        validFiles.forEach((file, index) => {
            if (index === mainImageIndex) {
                formData.append('image', file, file.name);
            } else {
                formData.append('additional_images', file, file.name);
            }
        });

        // Optional: Show a loading indicator
        const submitButton = productForm.querySelector('button[type="submit"]');
        submitButton.disabled = true;
        submitButton.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Guardando...';

        try {
            const response = await fetch(productForm.action, {
                method: 'POST',
                body: formData,
            });

            if (response.ok) {
                // The redirect will be handled by the server response
                window.location.href = response.url;
            } else {
                alert('Hubo un error al guardar el producto.');
                submitButton.disabled = false;
                submitButton.innerHTML = 'Guardar Producto';
            }
        } catch (error) {
            console.error('Error en la subida:', error);
            alert('Hubo un error de red.');
            submitButton.disabled = false;
            submitButton.innerHTML = 'Guardar Producto';
        }
    }
});
