function bindMenuToggle() {
    const burger = document.getElementById('burgerBtn');
    const menu = document.getElementById('menu');
    const closeBtn = document.querySelector('.close-btn');

    if (!burger || !menu) return;

    // Nettoyage si déjà bindé (évite doublons)
    burger.replaceWith(burger.cloneNode(true));
    if (closeBtn) closeBtn.replaceWith(closeBtn.cloneNode(true));

    const newBurger = document.getElementById('burgerBtn');
    const newClose = document.querySelector('.close-btn');

    newBurger.addEventListener('click', () => {
        menu.classList.toggle('active');
    });

    if (newClose) {
        newClose.addEventListener('click', () => {
            menu.classList.remove('active');
        });
    }

    menu.querySelectorAll('a').forEach(link => {
        link.addEventListener('click', () => {
            menu.classList.remove('active');
        });
    });
}

// Appelle le binding sur chaque chargement Turbo ou classique
document.addEventListener('DOMContentLoaded', bindMenuToggle);
document.addEventListener('turbo:load', bindMenuToggle);
