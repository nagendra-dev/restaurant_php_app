// Form validation functions
function validateForm(formId) {
    const form = document.getElementById(formId);
    const inputs = form.querySelectorAll('input[required]');
    let isValid = true;

    inputs.forEach(input => {
        if (!input.value.trim()) {
            isValid = false;
            input.classList.add('error');
        } else {
            input.classList.remove('error');
        }
    });

    return isValid;
}

// Reservation management
class ReservationManager {
    constructor() {
        this.reservations = [];
        this.loadReservations();
    }

    addReservation(reservation) {
        this.reservations.push(reservation);
        this.saveReservations();
    }

    deleteReservation(id) {
        this.reservations = this.reservations.filter(res => res.id !== id);
        this.saveReservations();
    }

    loadReservations() {
        const stored = localStorage.getItem('reservations');
        if (stored) {
            this.reservations = JSON.parse(stored);
        }
    }

    saveReservations() {
        localStorage.setItem('reservations', JSON.stringify(this.reservations));
    }
}

// Menu management
class MenuManager {
    constructor() {
        this.menuItems = [];
        this.loadMenu();
    }

    addMenuItem(item) {
        this.menuItems.push(item);
        this.saveMenu();
    }

    deleteMenuItem(id) {
        this.menuItems = this.menuItems.filter(item => item.id !== id);
        this.saveMenu();
    }

    loadMenu() {
        const stored = localStorage.getItem('menu');
        if (stored) {
            this.menuItems = JSON.parse(stored);
        }
    }

    saveMenu() {
        localStorage.setItem('menu', JSON.stringify(this.menuItems));
    }
}

// Initialize managers
const reservationManager = new ReservationManager();
const menuManager = new MenuManager();

// DOM Events
document.addEventListener('DOMContentLoaded', () => {
    // Form submissions
    const forms = document.querySelectorAll('form');
    forms.forEach(form => {
        form.addEventListener('submit', (e) => {
            e.preventDefault();
            if (validateForm(form.id)) {
                form.submit();
            }
        });
    });

    // Delete buttons
    const deleteButtons = document.querySelectorAll('.delete-btn');
    deleteButtons.forEach(button => {
        button.addEventListener('click', (e) => {
            if (confirm('Are you sure you want to delete this item?')) {
                const id = e.target.dataset.id;
                if (e.target.classList.contains('reservation-delete')) {
                    reservationManager.deleteReservation(id);
                } else if (e.target.classList.contains('menu-delete')) {
                    menuManager.deleteMenuItem(id);
                }
            }
        });
    });
});