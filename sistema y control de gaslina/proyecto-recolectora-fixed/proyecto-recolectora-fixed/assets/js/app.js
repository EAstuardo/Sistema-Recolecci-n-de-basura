/**
 * assets/js/app.js
 * JavaScript unificado - Sistema Recolectora GT
 */

// ============================================
// TOAST NOTIFICATIONS
// ============================================
function showToast(message, type = 'success') {
    const container = document.getElementById('toastContainer');
    if (!container) return;
    
    const toast = document.createElement('div');
    toast.className = `toast ${type}`;
    toast.textContent = message;
    container.appendChild(toast);
    
    setTimeout(() => {
        toast.style.opacity = '0';
        toast.style.transform = 'translateX(100px)';
        setTimeout(() => toast.remove(), 300);
    }, 3500);
}

// ============================================
// TEMA OSCURO / CLARO
// ============================================
function initTheme() {
    const savedTheme = localStorage.getItem('theme') || 'dark';
    document.documentElement.setAttribute('data-theme', savedTheme);
    updateThemeButton(savedTheme);
}

function toggleTheme() {
    const currentTheme = document.documentElement.getAttribute('data-theme');
    const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
    document.documentElement.setAttribute('data-theme', newTheme);
    localStorage.setItem('theme', newTheme);
    updateThemeButton(newTheme);
    showToast(`Modo ${newTheme === 'dark' ? 'oscuro' : 'claro'} activado`, 'success');
}

function updateThemeButton(theme) {
    const btn = document.getElementById('themeToggle');
    if (!btn) return;
    
    const icon = btn.querySelector('.theme-icon');
    const text = btn.querySelector('.theme-text');
    
    if (theme === 'dark') {
        icon.textContent = '🌙';
        text.textContent = 'Modo Oscuro';
    } else {
        icon.textContent = '☀️';
        text.textContent = 'Modo Claro';
    }
}

// ============================================
// SIDEBAR TOGGLE (MÓVIL)
// ============================================
function toggleSidebar() {
    const sidebar = document.getElementById('sidebar');
    const overlay = document.getElementById('sidebarOverlay');
    if (sidebar) {
        sidebar.classList.toggle('open');
    }
    if (overlay) {
        overlay.classList.toggle('active');
    }
}

// Cerrar sidebar al hacer clic en un nav-item en móvil
document.addEventListener('DOMContentLoaded', function () {
    const navItems = document.querySelectorAll('.nav-item');
    navItems.forEach(function (item) {
        item.addEventListener('click', function () {
            if (window.innerWidth <= 768) {
                const sidebar = document.getElementById('sidebar');
                const overlay = document.getElementById('sidebarOverlay');
                if (sidebar) sidebar.classList.remove('open');
                if (overlay) overlay.classList.remove('active');
            }
        });
    });
});

// ============================================
// MODAL
// ============================================
function openModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.classList.add('active');
    }
}

function closeModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.classList.remove('active');
    }
}

// Cerrar modal al hacer clic fuera
document.addEventListener('click', function(e) {
    if (e.target.classList && e.target.classList.contains('modal-overlay')) {
        e.target.classList.remove('active');
    }
});

// ============================================
// FORM VALIDATION
// ============================================
function validateForm(formId) {
    const form = document.getElementById(formId);
    if (!form) return true;
    
    let isValid = true;
    const requiredFields = form.querySelectorAll('[required]');
    
    requiredFields.forEach(field => {
        if (!field.value.trim()) {
            field.classList.add('error');
            isValid = false;
        } else {
            field.classList.remove('error');
        }
    });
    
    return isValid;
}

// ============================================
// TELEFONO (solo números, 8 dígitos)
// ============================================
function initTelefonoInputs() {
    const inputs = document.querySelectorAll('input[type="tel"], input[data-telefono]');
    inputs.forEach(input => {
        input.addEventListener('input', function() {
            this.value = this.value.replace(/\D/g, '').slice(0, 8);
        });
    });
}

// ============================================
// CONFIRMACIÓN DE ELIMINACIÓN
// ============================================
function confirmDelete(message) {
    return confirm(message || '¿Estás seguro de eliminar este registro? Esta acción no se puede deshacer.');
}

// ============================================
// FETCH HELPER
// ============================================
async function fetchAPI(url, options = {}) {
    try {
        const response = await fetch(url, {
            headers: {
                'Content-Type': 'application/json',
                ...options.headers
            },
            ...options
        });
        
        if (!response.ok) {
            const error = await response.json();
            throw new Error(error.error || 'Error en la petición');
        }
        
        return await response.json();
    } catch (error) {
        showToast(error.message, 'error');
        throw error;
    }
}

// ============================================
// INICIALIZACIÓN
// ============================================
document.addEventListener('DOMContentLoaded', function() {
    // Inicializar tema
    initTheme();
    
    // Theme toggle button
    const themeBtn = document.getElementById('themeToggle');
    if (themeBtn) {
        themeBtn.addEventListener('click', toggleTheme);
    }
    
    // Inicializar inputs de teléfono
    initTelefonoInputs();
    
    // Mostrar mensajes flash PHP
    const flashMessage = document.getElementById('flashMessage');
    if (flashMessage) {
        const type = flashMessage.classList.contains('success') ? 'success' : 'error';
        showToast(flashMessage.textContent, type);
    }
    
    // Auto cerrar alertas después de 5s
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(alert => {
        setTimeout(() => {
            alert.style.opacity = '0';
            setTimeout(() => alert.remove(), 500);
        }, 5000);
    });
});