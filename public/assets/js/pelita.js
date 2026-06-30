/**
 * PELITA - Custom JavaScript
 * @version 1.0.0
 */

document.addEventListener('DOMContentLoaded', function() {
    
    // Auto-hide alerts after 5 seconds
    const alerts = document.querySelectorAll('.alert-auto-hide');
    alerts.forEach(function(alert) {
        setTimeout(function() {
            alert.style.transition = 'opacity 0.5s ease';
            alert.style.opacity = '0';
            setTimeout(function() {
                alert.remove();
            }, 500);
        }, 5000);
    });

    // Form validation feedback
    const forms = document.querySelectorAll('form[data-validate]');
    forms.forEach(function(form) {
        form.addEventListener('submit', function(e) {
            const requiredFields = form.querySelectorAll('[required]');
            let isValid = true;
            
            requiredFields.forEach(function(field) {
                if (!field.value.trim()) {
                    isValid = false;
                    field.classList.add('border-red-500');
                    field.classList.remove('border-gray-300');
                } else {
                    field.classList.remove('border-red-500');
                    field.classList.add('border-gray-300');
                }
            });
            
            if (!isValid) {
                e.preventDefault();
                alert('Mohon lengkapi semua field yang wajib diisi');
            }
        });
    });

    // Confirm before delete
    const deleteButtons = document.querySelectorAll('[data-confirm]');
    deleteButtons.forEach(function(btn) {
        btn.addEventListener('click', function(e) {
            const message = this.dataset.confirm || 'Apakah Anda yakin?';
            if (!confirm(message)) {
                e.preventDefault();
            }
        });
    });

    // Phone number formatting
    const phoneInputs = document.querySelectorAll('input[type="tel"]');
    phoneInputs.forEach(function(input) {
        input.addEventListener('input', function() {
            this.value = this.value.replace(/[^0-9+]/g, '');
        });
    });

    // Email validation visual feedback
    const emailInputs = document.querySelectorAll('input[type="email"]');
    emailInputs.forEach(function(input) {
        input.addEventListener('blur', function() {
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (this.value && !emailRegex.test(this.value)) {
                this.classList.add('border-red-500');
            } else {
                this.classList.remove('border-red-500');
            }
        });
    });

    // Print functionality
    window.printPage = function() {
        window.print();
    };

    // Copy to clipboard
    window.copyToClipboard = function(text) {
        navigator.clipboard.writeText(text).then(function() {
            alert('Berhasil disalin!');
        }).catch(function(err) {
            console.error('Gagal menyalin:', err);
        });
    };

    // Toggle password visibility
    const togglePasswordButtons = document.querySelectorAll('[data-toggle-password]');
    togglePasswordButtons.forEach(function(btn) {
        btn.addEventListener('click', function() {
            const targetId = this.dataset.togglePassword;
            const input = document.getElementById(targetId);
            
            if (input.type === 'password') {
                input.type = 'text';
                this.textContent = '🙈';
            } else {
                input.type = 'password';
                this.textContent = '👁️';
            }
        });
    });

    // Auto-resize textarea
    const textareas = document.querySelectorAll('textarea[data-auto-resize]');
    textareas.forEach(function(textarea) {
        textarea.addEventListener('input', function() {
            this.style.height = 'auto';
            this.style.height = this.scrollHeight + 'px';
        });
    });

    // Number input - prevent negative
    const numberInputs = document.querySelectorAll('input[type="number"][min="0"], input[type="number"][min="1"]');
    numberInputs.forEach(function(input) {
        input.addEventListener('input', function() {
            if (this.value < 0) this.value = 0;
        });
    });

    console.log('🔥 PELITA v1.0.0 - Ready');
});

// Utility Functions
const PELITA = {
    
    // Format number with thousands separator
    formatNumber: function(num) {
        return num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
    },
    
    // Format date to Indonesian
    formatDate: function(dateStr) {
        const months = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 
                       'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
        const date = new Date(dateStr);
        return date.getDate() + ' ' + months[date.getMonth()] + ' ' + date.getFullYear();
    },
    
    // Show loading overlay
    showLoading: function() {
        const overlay = document.createElement('div');
        overlay.id = 'loadingOverlay';
        overlay.className = 'fixed inset-0 bg-black/50 flex items-center justify-center z-50';
        overlay.innerHTML = '<div class="spinner"></div>';
        document.body.appendChild(overlay);
    },
    
    // Hide loading overlay
    hideLoading: function() {
        const overlay = document.getElementById('loadingOverlay');
        if (overlay) overlay.remove();
    },
    
    // Show toast notification
    toast: function(message, type = 'info') {
        const colors = {
            success: 'bg-green-500',
            error: 'bg-red-500',
            warning: 'bg-yellow-500',
            info: 'bg-blue-500'
        };
        
        const toast = document.createElement('div');
        toast.className = `fixed bottom-4 right-4 ${colors[type]} text-white px-6 py-3 rounded-lg shadow-lg z-50 animate-fade-in`;
        toast.textContent = message;
        document.body.appendChild(toast);
        
        setTimeout(function() {
            toast.style.opacity = '0';
            toast.style.transition = 'opacity 0.3s ease';
            setTimeout(function() { toast.remove(); }, 300);
        }, 3000);
    }
};
