document.addEventListener('DOMContentLoaded', function() {
    const el = document.getElementById("wrapper");
    const toggleButton = document.getElementById("menu-toggle");

    if (toggleButton) {
        toggleButton.onclick = function () {
            el.classList.toggle("toggled");
        };
    }

    // Lógica para el sidebar en diferentes tamaños de pantalla
    function setSidebarState() {
        if (window.innerWidth >= 768) {
            if (localStorage.getItem('sidebarToggled') === 'true') {
                el.classList.add('toggled');
            } else {
                el.classList.remove('toggled');
            }
        } else {
            el.classList.add('toggled');
        }
    }

    // Guardar el estado del sidebar en desktop
    const desktopToggleButton = document.getElementById('desktop-menu-toggle'); // Asumiendo que tienes un botón para desktop
    if(desktopToggleButton) {
        desktopToggleButton.addEventListener('click', function() {
            if (window.innerWidth >= 768) {
                if (localStorage.getItem('sidebarToggled') === 'true') {
                    localStorage.setItem('sidebarToggled', 'false');
                } else {
                    localStorage.setItem('sidebarToggled', 'true');
                }
            }
        });
    }

    setSidebarState();
    window.addEventListener('resize', setSidebarState);
});
