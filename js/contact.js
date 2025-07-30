// ===== CONTACT FORM JAVASCRIPT =====

class ContactForm {
    constructor() {
        this.form = document.getElementById('contact-form');
        this.submitBtn = null;
        this.originalBtnText = '';
        this.isSubmitting = false;
        
        this.init();
    }

    init() {
        if (this.form) {
            this.submitBtn = this.form.querySelector('button[type="submit"]');
            this.originalBtnText = this.submitBtn.textContent;
            this.bindEvents();
            this.setupValidation();
        }
    }

    bindEvents() {
        // Form submission
        this.form.addEventListener('submit', (e) => {
            e.preventDefault();
            this.handleSubmit();
        });

        // Real-time validation
        const inputs = this.form.querySelectorAll('input, textarea');
        inputs.forEach(input => {
            input.addEventListener('blur', () => {
                this.validateField(input);
            });
            
            input.addEventListener('input', () => {
                this.clearFieldError(input);
            });
        });

        // Character counter for message
        const messageField = this.form.querySelector('#message');
        if (messageField) {
            messageField.addEventListener('input', () => {
                this.updateCharacterCount(messageField);
            });
        }
    }

    setupValidation() {
        // Add validation attributes
        const nameField = this.form.querySelector('#name');
        const emailField = this.form.querySelector('#email');
        const subjectField = this.form.querySelector('#subject');
        const messageField = this.form.querySelector('#message');

        if (nameField) {
            nameField.setAttribute('minlength', '2');
            nameField.setAttribute('maxlength', '50');
        }

        if (emailField) {
            emailField.setAttribute('type', 'email');
        }

        if (subjectField) {
            subjectField.setAttribute('minlength', '5');
            subjectField.setAttribute('maxlength', '100');
        }

        if (messageField) {
            messageField.setAttribute('minlength', '10');
            messageField.setAttribute('maxlength', '1000');
        }
    }

    validateField(field) {
        const value = field.value.trim();
        const fieldName = field.name;
        let isValid = true;
        let errorMessage = '';

        // Clear previous error
        this.clearFieldError(field);

        // Required field validation
        if (field.hasAttribute('required') && !value) {
            isValid = false;
            errorMessage = `${this.getFieldLabel(fieldName)} is required.`;
        }

        // Specific field validations
        if (isValid && value) {
            switch (fieldName) {
                case 'name':
                    if (value.length < 2) {
                        isValid = false;
                        errorMessage = 'Name must be at least 2 characters long.';
                    } else if (!/^[a-zA-Z\s]+$/.test(value)) {
                        isValid = false;
                        errorMessage = 'Name can only contain letters and spaces.';
                    }
                    break;

                case 'email':
                    if (!this.isValidEmail(value)) {
                        isValid = false;
                        errorMessage = 'Please enter a valid email address.';
                    }
                    break;

                case 'subject':
                    if (value.length < 5) {
                        isValid = false;
                        errorMessage = 'Subject must be at least 5 characters long.';
                    }
                    break;

                case 'message':
                    if (value.length < 10) {
                        isValid = false;
                        errorMessage = 'Message must be at least 10 characters long.';
                    }
                    break;
            }
        }

        // Show error if validation failed
        if (!isValid) {
            this.showFieldError(field, errorMessage);
        }

        return isValid;
    }

    validateForm() {
        const fields = this.form.querySelectorAll('input, textarea');
        let isValid = true;

        fields.forEach(field => {
            if (!this.validateField(field)) {
                isValid = false;
            }
        });

        return isValid;
    }

    showFieldError(field, message) {
        // Remove existing error
        this.clearFieldError(field);

        // Add error class
        field.classList.add('error');

        // Create error message element
        const errorDiv = document.createElement('div');
        errorDiv.className = 'field-error';
        errorDiv.textContent = message;
        errorDiv.style.cssText = `
            color: var(--error-color);
            font-size: 0.875rem;
            margin-top: 0.25rem;
            display: flex;
            align-items: center;
            gap: 0.25rem;
        `;

        // Add error icon
        errorDiv.innerHTML = `<i class="fas fa-exclamation-circle"></i> ${message}`;

        // Insert error message after field
        field.parentNode.appendChild(errorDiv);
    }

    clearFieldError(field) {
        field.classList.remove('error');
        const errorDiv = field.parentNode.querySelector('.field-error');
        if (errorDiv) {
            errorDiv.remove();
        }
    }

    updateCharacterCount(field) {
        const maxLength = field.getAttribute('maxlength');
        const currentLength = field.value.length;
        
        // Remove existing counter
        let counter = field.parentNode.querySelector('.char-counter');
        if (!counter) {
            counter = document.createElement('div');
            counter.className = 'char-counter';
            counter.style.cssText = `
                font-size: 0.75rem;
                color: var(--text-light);
                text-align: right;
                margin-top: 0.25rem;
            `;
            field.parentNode.appendChild(counter);
        }

        // Update counter
        counter.textContent = `${currentLength}/${maxLength}`;
        
        // Change color based on usage
        const usage = currentLength / maxLength;
        if (usage > 0.9) {
            counter.style.color = 'var(--error-color)';
        } else if (usage > 0.7) {
            counter.style.color = 'var(--warning-color)';
        } else {
            counter.style.color = 'var(--text-light)';
        }
    }

    async handleSubmit() {
        if (this.isSubmitting) {
            return;
        }

        // Validate form
        if (!this.validateForm()) {
            this.showNotification('Please fix the errors in the form.', 'error');
            return;
        }

        // Show loading state
        this.setSubmittingState(true);

        try {
            // Get form data
            const formData = new FormData(this.form);
            const data = Object.fromEntries(formData);

            // Simulate API call (replace with actual backend endpoint)
            await this.submitForm(data);

            // Success
            this.showNotification('Message sent successfully! We\'ll get back to you soon.', 'success');
            this.form.reset();
            this.clearAllErrors();

        } catch (error) {
            console.error('Form submission error:', error);
            this.showNotification('Failed to send message. Please try again.', 'error');
        } finally {
            this.setSubmittingState(false);
        }
    }

    async submitForm(data) {
        // Simulate API call delay
        return new Promise((resolve, reject) => {
            setTimeout(() => {
                // Simulate random success/failure for demo
                const success = Math.random() > 0.1; // 90% success rate
                
                if (success) {
                    resolve({ success: true, message: 'Message sent successfully' });
                } else {
                    reject(new Error('Network error'));
                }
            }, 2000);
        });
    }

    setSubmittingState(submitting) {
        this.isSubmitting = submitting;
        
        if (submitting) {
            this.submitBtn.innerHTML = '<span class="loading-spinner"></span> Sending...';
            this.submitBtn.disabled = true;
        } else {
            this.submitBtn.textContent = this.originalBtnText;
            this.submitBtn.disabled = false;
        }
    }

    clearAllErrors() {
        const fields = this.form.querySelectorAll('input, textarea');
        fields.forEach(field => {
            this.clearFieldError(field);
        });
    }

    getFieldLabel(fieldName) {
        const labels = {
            name: 'Name',
            email: 'Email',
            subject: 'Subject',
            message: 'Message'
        };
        return labels[fieldName] || fieldName;
    }

    isValidEmail(email) {
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return emailRegex.test(email);
    }

    showNotification(message, type = 'info') {
        if (window.MainJS && window.MainJS.showNotification) {
            window.MainJS.showNotification(message, type);
        } else {
            // Fallback notification
            alert(message);
        }
    }

    // Public methods for external use
    reset() {
        this.form.reset();
        this.clearAllErrors();
        this.setSubmittingState(false);
    }

    setFieldValue(fieldName, value) {
        const field = this.form.querySelector(`[name="${fieldName}"]`);
        if (field) {
            field.value = value;
        }
    }

    getFieldValue(fieldName) {
        const field = this.form.querySelector(`[name="${fieldName}"]`);
        return field ? field.value : '';
    }

    validateFieldByName(fieldName) {
        const field = this.form.querySelector(`[name="${fieldName}"]`);
        return field ? this.validateField(field) : false;
    }

    // Auto-fill form with user data (if available)
    autoFill(data) {
        Object.keys(data).forEach(key => {
            this.setFieldValue(key, data[key]);
        });
    }

    // Export form data
    getFormData() {
        const formData = new FormData(this.form);
        return Object.fromEntries(formData);
    }

    // Check if form is valid
    isValid() {
        return this.validateForm();
    }

    // Submit form programmatically
    submit() {
        this.handleSubmit();
    }
}

// Initialize contact form when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    // Check if contact form exists
    const contactForm = document.getElementById('contact-form');
    if (contactForm) {
        window.contactForm = new ContactForm();
    }
});

// Add CSS for form-specific styles
const contactStyles = `
    .form-group input.error,
    .form-group textarea.error {
        border-color: var(--error-color);
        box-shadow: 0 0 0 3px rgba(239, 68, 68, 0.1);
    }

    .field-error {
        animation: slideIn 0.3s ease-out;
    }

    @keyframes slideIn {
        from {
            opacity: 0;
            transform: translateY(-10px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .char-counter {
        transition: color 0.3s ease;
    }

    .form-group input:focus,
    .form-group textarea:focus {
        border-color: var(--primary-color);
        box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1);
    }

    .form-group input.error:focus,
    .form-group textarea.error:focus {
        border-color: var(--error-color);
        box-shadow: 0 0 0 3px rgba(239, 68, 68, 0.1);
    }

    .loading-spinner {
        margin-right: 0.5rem;
    }
`;

// Inject styles if not already present
if (!document.querySelector('#contact-styles')) {
    const styleSheet = document.createElement('style');
    styleSheet.id = 'contact-styles';
    styleSheet.textContent = contactStyles;
    document.head.appendChild(styleSheet);
}

// Export for use in other modules
window.ContactForm = ContactForm; 