// Main Application JavaScript

// Utility Functions
const App = {
    // Show toast notification
    showToast(message, type = 'info', duration = 3000) {
        const toast = document.createElement('div');
        toast.className = `toast ${type}`;
        toast.innerHTML = `
            <div class="flex items-center">
                <i class="fas fa-${this.getToastIcon(type)} mr-3"></i>
                <span>${message}</span>
            </div>
        `;
        
        document.body.appendChild(toast);
        
        setTimeout(() => {
            toast.style.animation = 'slideOutRight 0.3s ease';
            setTimeout(() => toast.remove(), 300);
        }, duration);
    },
    
    getToastIcon(type) {
        const icons = {
            success: 'check-circle',
            error: 'exclamation-circle',
            warning: 'exclamation-triangle',
            info: 'info-circle'
        };
        return icons[type] || 'info-circle';
    },
    
    // Format currency
    formatCurrency(amount, currency = 'IDR') {
        if (currency === 'IDR') {
            return 'Rp ' + new Intl.NumberFormat('id-ID').format(amount);
        }
        return new Intl.NumberFormat('en-US', {
            style: 'currency',
            currency: currency
        }).format(amount);
    },
    
    // Format date
    formatDate(dateString) {
        const date = new Date(dateString);
        return date.toLocaleDateString('id-ID', {
            day: 'numeric',
            month: 'long',
            year: 'numeric'
        });
    },
    
    // Confirm dialog
    confirm(message, callback) {
        if (window.confirm(message)) {
            callback();
        }
    },
    
    // Loading state
    setLoading(element, loading = true) {
        if (loading) {
            element.disabled = true;
            element.dataset.originalText = element.innerHTML;
            element.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Loading...';
        } else {
            element.disabled = false;
            element.innerHTML = element.dataset.originalText;
        }
    },
    
    // API request wrapper
    async request(url, options = {}) {
        try {
            const response = await fetch(url, {
                ...options,
                headers: {
                    'Content-Type': 'application/json',
                    ...options.headers
                }
            });
            
            const data = await response.json();
            
            if (!response.ok) {
                throw new Error(data.error || 'Request failed');
            }
            
            return data;
        } catch (error) {
            this.showToast(error.message, 'error');
            throw error;
        }
    }
};

// Form Validation
class FormValidator {
    constructor(formId) {
        this.form = document.getElementById(formId);
        if (this.form) {
            this.init();
        }
    }
    
    init() {
        this.form.addEventListener('submit', (e) => {
            if (!this.validate()) {
                e.preventDefault();
            }
        });
        
        // Real-time validation
        this.form.querySelectorAll('input, select, textarea').forEach(field => {
            field.addEventListener('blur', () => this.validateField(field));
        });
    }
    
    validate() {
        let isValid = true;
        const fields = this.form.querySelectorAll('[required]');
        
        fields.forEach(field => {
            if (!this.validateField(field)) {
                isValid = false;
            }
        });
        
        return isValid;
    }
    
    validateField(field) {
        const value = field.value.trim();
        const type = field.type;
        let isValid = true;
        let message = '';
        
        // Required check
        if (field.hasAttribute('required') && !value) {
            isValid = false;
            message = 'Field ini wajib diisi';
        }
        
        // Email validation
        if (type === 'email' && value) {
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(value)) {
                isValid = false;
                message = 'Format email tidak valid';
            }
        }
        
        // Min length
        if (field.hasAttribute('minlength') && value.length < field.minLength) {
            isValid = false;
            message = `Minimal ${field.minLength} karakter`;
        }
        
        // Number validation
        if (type === 'number' && value) {
            if (field.hasAttribute('min') && parseFloat(value) < parseFloat(field.min)) {
                isValid = false;
                message = `Nilai minimal ${field.min}`;
            }
        }
        
        this.showFieldError(field, isValid, message);
        return isValid;
    }
    
    showFieldError(field, isValid, message) {
        const errorEl = field.nextElementSibling;
        
        if (!isValid) {
            field.classList.add('border-red-500');
            field.classList.remove('border-gray-300');
            
            if (!errorEl || !errorEl.classList.contains('error-message')) {
                const error = document.createElement('p');
                error.className = 'error-message text-red-500 text-sm mt-1';
                error.textContent = message;
                field.parentNode.insertBefore(error, field.nextSibling);
            } else {
                errorEl.textContent = message;
            }
        } else {
            field.classList.remove('border-red-500');
            field.classList.add('border-gray-300');
            
            if (errorEl && errorEl.classList.contains('error-message')) {
                errorEl.remove();
            }
        }
    }
}

// Currency Converter
class CurrencyConverter {
    constructor() {
        this.rates = {};
        this.init();
    }
    
    async init() {
        const currencySelect = document.getElementById('currency-select');
        const amountInput = document.getElementById('amount-input');
        const idrAmount = document.getElementById('idr-amount');
        
        if (!currencySelect || !amountInput) return;
        
        await this.fetchRates();
        
        const updateConversion = () => {
            const currency = currencySelect.value;
            const amount = parseFloat(amountInput.value) || 0;
            
            if (currency === 'IDR') {
                idrAmount.value = amount;
            } else {
                const rate = this.rates[currency] || 1;
                idrAmount.value = (amount * rate).toFixed(2);
            }
        };
        
        currencySelect.addEventListener('change', updateConversion);
        amountInput.addEventListener('input', updateConversion);
    }
    
    async fetchRates() {
        try {
            const response = await fetch('/api/exchange-rate.php?action=all');
            const data = await response.json();
            
            if (data.success) {
                this.rates = data.rates;
            }
        } catch (error) {
            console.error('Failed to fetch exchange rates:', error);
        }
    }
}

// Delete Confirmation
function confirmDelete(message, url) {
    if (confirm(message || 'Apakah Anda yakin ingin menghapus?')) {
        window.location.href = url;
    }
}

// AJAX Form Submit
async function submitAjaxForm(formId, successCallback) {
    const form = document.getElementById(formId);
    if (!form) return;
    
    form.addEventListener('submit', async (e) => {
        e.preventDefault();
        
        const formData = new FormData(form);
        const data = Object.fromEntries(formData);
        const submitBtn = form.querySelector('button[type="submit"]');
        
        App.setLoading(submitBtn, true);
        
        try {
            const response = await App.request(form.action, {
                method: form.method,
                body: JSON.stringify(data)
            });
            
            if (response.success) {
                App.showToast(response.message || 'Berhasil!', 'success');
                if (successCallback) successCallback(response);
            }
        } catch (error) {
            App.showToast(error.message, 'error');
        } finally {
            App.setLoading(submitBtn, false);
        }
    });
}

// Debounce function
function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

// Search functionality
function initSearch(inputId, targetClass) {
    const searchInput = document.getElementById(inputId);
    if (!searchInput) return;
    
    const searchHandler = debounce((e) => {
        const searchTerm = e.target.value.toLowerCase();
        const items = document.querySelectorAll(`.${targetClass}`);
        
        items.forEach(item => {
            const text = item.textContent.toLowerCase();
            if (text.includes(searchTerm)) {
                item.style.display = '';
            } else {
                item.style.display = 'none';
            }
        });
    }, 300);
    
    searchInput.addEventListener('input', searchHandler);
}

// Auto-save draft
class AutoSave {
    constructor(formId, storageKey) {
        this.form = document.getElementById(formId);
        this.storageKey = storageKey;
        
        if (this.form) {
            this.init();
        }
    }
    
    init() {
        // Load saved data
        this.loadDraft();
        
        // Save on input change
        this.form.querySelectorAll('input, select, textarea').forEach(field => {
            field.addEventListener('input', debounce(() => this.saveDraft(), 500));
        });
        
        // Clear on submit
        this.form.addEventListener('submit', () => this.clearDraft());
    }
    
    saveDraft() {
        const formData = new FormData(this.form);
        const data = Object.fromEntries(formData);
        localStorage.setItem(this.storageKey, JSON.stringify(data));
    }
    
    loadDraft() {
        const saved = localStorage.getItem(this.storageKey);
        if (!saved) return;
        
        const data = JSON.parse(saved);
        Object.keys(data).forEach(key => {
            const field = this.form.querySelector(`[name="${key}"]`);
            if (field) field.value = data[key];
        });
    }
    
    clearDraft() {
        localStorage.removeItem(this.storageKey);
    }
}

// Initialize on DOM ready
document.addEventListener('DOMContentLoaded', () => {
    // Initialize form validators
    ['transaksi-form', 'kategori-form', 'profile-form'].forEach(formId => {
        new FormValidator(formId);
    });
    
    // Initialize currency converter
    new CurrencyConverter();
    
    // Initialize search
    initSearch('search-input', 'searchable-item');
    
    // Add fade-in animation to main content
    const mainContent = document.querySelector('main');
    if (mainContent) {
        mainContent.classList.add('fade-in');
    }
});

// Export for use in other scripts
window.App = App;
window.FormValidator = FormValidator;
window.CurrencyConverter = CurrencyConverter;
window.AutoSave = AutoSave;