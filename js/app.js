// Fonction pour ajouter des effets visuels aux formulaires
function enhanceForms() {
    // Mettre en évidence les champs de saisie lors du focus
    const inputs = document.querySelectorAll('input, select, textarea');
    inputs.forEach(input => {
        // Effet quand on clique sur un champ
        input.addEventListener('focus', function() {
            this.style.backgroundColor = '#f0f8ff'; // Bleu très clair
            this.style.boxShadow = '0 0 5px rgba(0, 123, 255, 0.5)';
        });
        
        // Retour à la normale quand on quitte le champ
        input.addEventListener('blur', function() {
            this.style.backgroundColor = '';
            this.style.boxShadow = '';
        });
    });
    
    // Animation des boutons
    const buttons = document.querySelectorAll('button, input[type="submit"]');
    buttons.forEach(button => {
        button.addEventListener('mouseenter', function() {
            this.style.transform = 'scale(1.05)';
        });
        
        button.addEventListener('mouseleave', function() {
            this.style.transform = 'scale(1)';
        });
        
        // Effet visuel au clic
        button.addEventListener('click', function() {
            this.style.transform = 'scale(0.95)';
            setTimeout(() => {
                this.style.transform = 'scale(1)';
            }, 100);
        });
    });
}

// Fonction pour ajouter des messages de confirmation
function addConfirmationMessages() {
    const forms = document.querySelectorAll('form');
    forms.forEach(form => {
        form.addEventListener('submit', function(event) {
            // Trouver le type d'opération basé sur l'URL ou l'ID du formulaire
            let operationType = '';
            if (window.location.href.includes('deposit')) {
                operationType = 'dépôt';
            } else if (window.location.href.includes('withdraw')) {
                operationType = 'retrait';
            } else if (window.location.href.includes('transfer')) {
                operationType = 'transfert';
            } else {
                operationType = 'cette opération';
            }
            
            // Afficher une confirmation
            const confirmed = confirm(`Êtes-vous sûr de vouloir effectuer ${operationType} ?`);
            if (!confirmed) {
                event.preventDefault();
            }
        });
    });
}

// Fonction pour ajouter des effets visuels aux messages de succès/erreur
function enhanceMessages() {
    const successMessages = document.querySelectorAll('.success, .alert-success');
    const errorMessages = document.querySelectorAll('.error, .alert-error');
    
    // Animation pour les messages de succès
    successMessages.forEach(message => {
        message.style.opacity = '0';
        message.style.transition = 'opacity 0.5s ease-in-out';
        
        setTimeout(() => {
            message.style.opacity = '1';
            message.style.backgroundColor = '#d4edda';
            message.style.border = '1px solid #c3e6cb';
            message.style.borderRadius = '5px';
            message.style.padding = '10px';
            message.style.marginBottom = '15px';
        }, 100);
    });
    
    // Animation pour les messages d'erreur
    errorMessages.forEach(message => {
        message.style.opacity = '0';
        message.style.transition = 'opacity 0.5s ease-in-out';
        
        setTimeout(() => {
            message.style.opacity = '1';
            message.style.backgroundColor = '#f8d7da';
            message.style.border = '1px solid #f5c6cb';
            message.style.borderRadius = '5px';
            message.style.padding = '10px';
            message.style.marginBottom = '15px';
        }, 100);
    });
}

// Exécuter toutes les améliorations quand la page est chargée
document.addEventListener('DOMContentLoaded', function() {
    enhanceForms();
    addConfirmationMessages();
    enhanceMessages();
    
    console.log('Améliorations JavaScript appliquées !');
});
