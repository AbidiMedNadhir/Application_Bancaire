document.addEventListener('DOMContentLoaded', function() {
    // Add animation to cards
    const cards = document.querySelectorAll('.card');
    
    if (cards.length) {
        cards.forEach(card => {
            card.addEventListener('mouseover', function() {
                this.style.transform = 'translateY(-10px)';
                this.style.boxShadow = '0 15px 30px rgba(0, 0, 0, 0.1)';
            });
            
            card.addEventListener('mouseout', function() {
                this.style.transform = 'translateY(0)';
                this.style.boxShadow = '0 5px 15px rgba(0, 0, 0, 0.1)';
            });
        });
    }
    
    // Add sticky navigation effect
    const nav = document.querySelector('nav');
    
    if (nav) {
        window.addEventListener('scroll', function() {
            if (window.scrollY > 100) {
                nav.classList.add('sticky');
                nav.style.boxShadow = '0 2px 10px rgba(0, 0, 0, 0.1)';
            } else {
                nav.classList.remove('sticky');
                nav.style.boxShadow = '0 2px 5px rgba(0, 0, 0, 0.1)';
            }
        });
    }
});


// Disparition automatique du message de succès
const alert = document.querySelector('.alert');
if (alert) {
    setTimeout(() => {
        alert.classList.add('fade-out');
    }, 2500); // Attendre 2.5 secondes

    setTimeout(() => {
        alert.remove(); // Supprimer l’élément du DOM
    }, 2000); // Total 2 secondes
}
