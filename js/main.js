document.addEventListener("DOMContentLoaded", () => {
    "use strict";

    // =======================================================
    // 1. UI General (Scroll, Preloader, Hero)
    // =======================================================
    const btnTop = document.getElementById("myBtn");
    const windowH = window.innerHeight / 2;

    window.addEventListener('scroll', () => {
        if (btnTop) {
            btnTop.style.display = window.scrollY > windowH ? 'flex' : 'none';
        }
    });

    if (btnTop) {
        btnTop.addEventListener("click", () => {
            window.scrollTo({ top: 0, behavior: 'smooth' });
        });
    }

    window.addEventListener('load', () => {
        const preloader = document.getElementById('preloader');
        
        if (preloader) {
            preloader.classList.add('preloader-fade-out');
            setTimeout(() => { 
                preloader.style.display = 'none'; 
            }, 400);
        }
    });

    // Galería Hero
    const hero = document.querySelector('.hero');
    const activateGallery = (e) => {
        if (!e.target.matches('.secundaria') || !hero) return;
        [hero.src, e.target.src] = [e.target.src, hero.src];
    };

    if (window.innerWidth > 800) {
        window.addEventListener('mouseover', activateGallery);
    } else {
        window.addEventListener('click', activateGallery);
        const btnSearch = document.getElementById('btn-search');
        if(btnSearch) btnSearch.classList.remove('js-show-header-dropdown');
    }

    // =======================================================
    // 2. Dark Mode (Lógica Centralizada)
    // =======================================================
    const darkmodeSwitches = document.querySelectorAll(".switch__darkmode");
    const currentTheme = localStorage.getItem('darkmode');

    const setTheme = (theme) => {
        if (theme === 'dark') {
            document.body.classList.add('dark-theme');
            darkmodeSwitches.forEach(sw => sw.classList.replace('fa-sun', 'fa-moon'));
            darkmodeSwitches.forEach(sw => sw.checked = true);
        } else {
            document.body.classList.remove('dark-theme');
            darkmodeSwitches.forEach(sw => sw.classList.replace('fa-moon', 'fa-sun'));
            darkmodeSwitches.forEach(sw => sw.checked = false);
        }
    };

    // Inicializar tema
    if (currentTheme) setTheme(currentTheme);

    darkmodeSwitches.forEach(sw => {
        sw.addEventListener('click', (e) => {
            const newTheme = e.target.checked ? "dark" : "light";
            localStorage.setItem('darkmode', newTheme);
            setTheme(newTheme);
        });
    });

    // =======================================================
    // 3. Detección de Móvil (Feature Detection)
    // =======================================================
    const isMobile = window.matchMedia("(max-width: 768px)").matches;
    const bodyDark = document.getElementById("BodyDark"); 
    
    if (bodyDark) {
        bodyDark.classList.add(isMobile ? "mobiledevice" : "desktop");
    }

    const urlParams = new URLSearchParams(window.location.search);
    const moduloActual = urlParams.get('mod');

    if (moduloActual === 'catalogo') {
        if (isMobile) {
            document.querySelectorAll(".filtros__subtitle").forEach(el => {
                el.addEventListener("click", e => {
                    const objetivo = e.target.className.replace("filtros__subtitle name__", "").trim();
                    const desplegar = document.querySelector(".opciones__" + objetivo);
                    if(desplegar) desplegar.classList.toggle("dis-none");
                });
            });
        } else {
            document.querySelectorAll(".contenido__opciones, .filtros").forEach(el => {
                el.classList.remove("dis-none");
            });
        }
    }

    // =======================================================
    // 4. Modales (Delegación de Eventos y Clics Fuera)
    // =======================================================
    const domPage = document.querySelector(".Macrom_page");
    const contHeader = document.querySelector(".contenedor__header");
    const sidebarMenu = document.querySelector("#abrirModal6");

    const toggleModalOverflow = (isOpening) => {
        if (bodyDark) bodyDark.classList.toggle("no-overflow", isOpening);
        if (domPage) domPage.classList.toggle("no-overflow", isOpening);
        if (contHeader) isOpening ? contHeader.classList.remove("pd-1") : contHeader.classList.add("pd-1");
    };

    document.addEventListener("click", (e) => {
        // --- ABRIR MODAL ---
        if (e.target.matches('.click')) {
            const targetId = e.target.dataset.target || `ventanaModal${e.target.id.split('l')[1]}`;
            const modal = document.getElementById(targetId);
            if (modal) {
                modal.style.display = "block";
                toggleModalOverflow(true);
            }
        }

        // --- CERRAR MODAL CON LA "X" ---
        if (e.target.matches('.closem') || e.target.matches('[class^="cerrar"]')) {
            // Busca el contenedor padre del modal para cerrarlo
            const modal = e.target.closest('[id^="ventanaModal"]') || e.target.closest('.modal') || e.target.closest('.sidebar__mobil');
            if (modal) {
                modal.style.display = 'none';
                toggleModalOverflow(false);
                if (sidebarMenu) {
                    sidebarMenu.classList.add("fa-bars");
                    sidebarMenu.classList.remove("fa-reply");
                }
            }
        }

        // --- CERRAR MODAL AL HACER CLIC AFUERA (Fondo Oscuro) ---
        // Si el elemento que clickeamos ES directamente el fondo del modal
        if (e.target.id && e.target.id.startsWith('ventanaModal') || e.target.classList.contains('sidebar__mobil')) {
            e.target.style.display = 'none';
            toggleModalOverflow(false);
            if (sidebarMenu) {
                sidebarMenu.classList.add("fa-bars");
                sidebarMenu.classList.remove("fa-reply");
            }
        }
    });

    // =======================================================
    // 5. Enrutamiento de Menú Activo (Mapeo por URLParams)
    // =======================================================
    const opcActual = urlParams.get('opc');
    
    const routeMap = {
        'home': '#sidebar0',
        'nosotros': '#sidebar4',
        'catalogo': '#sidebar1',
        'Compras': '#sidebar2',
        'Blog': '#sidebar9'
    };

    const profileRouteMap = {
        'Session': '#sidebar5',
        'Mispedidos': '#sidebar6',
        'Mispedidos_view': '#sidebar6',
        'Facturacion': '#sidebar8',
        'Facturacion_add': '#sidebar8',
        'Facturacion_edit': '#sidebar8',
        'Direcciones': '#sidebar7',
        'Direcciones_add': '#sidebar7',
        'Direcciones_edit': '#sidebar7'
    };

    let activeSidebarSelector = null;

    if (!moduloActual) {
        activeSidebarSelector = '#sidebar0';
    } else if (moduloActual === 'Profile' && opcActual) {
        activeSidebarSelector = profileRouteMap[opcActual];
    } else {
        activeSidebarSelector = routeMap[moduloActual];
    }

    if (activeSidebarSelector) {
        const activeSidebar = document.querySelector(activeSidebarSelector);
        if (activeSidebar) activeSidebar.classList.add('sidebar__active');
    }

    // =======================================================
    // 6. Menús Desplegables de Cabecera (Usuario y Carrito)
    // =======================================================
    const dropdownTriggers = document.querySelectorAll('.js-show-header-dropdown');
    const usercba = document.getElementById('usercba');
    const divcarri = document.getElementById('divcarri');
    const carrisvg = document.getElementById('carrisvg');
    
    // Función para resetear los estilos de la cabecera al cerrar menús
    const resetHeaderStyles = () => {
        if (usercba) usercba.style.backgroundColor = "transparent";
        if (carrisvg) carrisvg.style.filter = "brightness(0) invert(1)";
        if (divcarri) divcarri.style.backgroundColor = "transparent";
        
        dropdownTriggers.forEach(el => el.style.color = "#fff");
        const btnSearch = document.getElementById('btn-search');
        if (btnSearch) btnSearch.style.color = "#000";
    };

    dropdownTriggers.forEach(trigger => {
        trigger.addEventListener('click', (e) => {
            e.stopPropagation(); // Evita que se cierre instantáneamente
            
            const parent = trigger.parentElement;
            const dropdown = parent.querySelector('.header-dropdown');
            const isAlreadyOpen = dropdown && dropdown.classList.contains('show-header-dropdown');
            const isDarkMode = localStorage.getItem('darkmode') === "dark";
            
            // 1. Cerramos todos los menús para evitar que se empalmen
            document.querySelectorAll('.header-dropdown').forEach(menu => {
                menu.classList.remove('show-header-dropdown');
            });
            resetHeaderStyles();

            // 2. Si el que clickeamos estaba cerrado, lo abrimos y aplicamos sus colores
            if (!isAlreadyOpen && dropdown) {
                dropdown.classList.add('show-header-dropdown');
                
                // Aplicamos colores oscuros para contraste visual
                dropdownTriggers.forEach(el => el.style.color = "#000");
                const btnSearch = document.getElementById('btn-search');
                if (btnSearch) btnSearch.style.color = "#000";

                // Estilos específicos dependiendo de qué menú se abrió
                if (parent.matches(".header-wrapicon1")) { 
                    // Se abrió "Mi Cuenta"
                    if (carrisvg) carrisvg.style.filter = "brightness(0) invert(1)";
                    if (divcarri) divcarri.style.backgroundColor = "transparent";
                    if (usercba) usercba.style.backgroundColor = isDarkMode ? "#7f7f7f" : "#fff";
                } else if (parent.matches(".header-wrapicon2")) { 
                    // Se abrió "Carrito"
                    if (usercba) usercba.style.backgroundColor = "transparent";
                    if (divcarri) divcarri.style.backgroundColor = isDarkMode ? "#7f7f7f" : "white";
                    if (carrisvg && !isDarkMode) carrisvg.style.filter = "brightness(2)";
                }
            }
        });
    });

    // Evitar que si haces clic adentro del cuadro blanco (el carrito), este se cierre
    document.querySelectorAll('.header-dropdown').forEach(dropdown => {
        dropdown.addEventListener('click', (e) => {
            e.stopPropagation();
        });
    });

    // Si haces clic en cualquier otra parte de la página, cerramos los menús abiertos
    window.addEventListener('click', () => {
        document.querySelectorAll('.header-dropdown').forEach(menu => {
            menu.classList.remove('show-header-dropdown');
        });
        resetHeaderStyles();
    });

});