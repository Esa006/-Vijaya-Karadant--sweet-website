/**
 * Sweets Website - Checkout Validation
 * Real-time client-side validation for the checkout form.
 */

document.addEventListener('DOMContentLoaded', function () {
    const form = document.querySelector('.needs-validation');
    if (!form) return;

    const inputs = form.querySelectorAll('input[name], select[name]');

    const validationRules = {
        email: {
            pattern: /^[^\s@]+@[^\s@]+\.[^\s@]+$/,
            message: 'Please enter a valid email address'
        },
        first_name: {
            min: 2,
            max: 50,
            pattern: /^[a-zA-Z\s\'-]+$/,
            message: 'First name must be 2-50 characters (letters only)'
        },
        last_name: {
            min: 2,
            max: 50,
            pattern: /^[a-zA-Z\s\'-]+$/,
            message: 'Last name must be 2-50 characters (letters only)'
        },
        phone: {
            pattern: /^[6-9]\d{9}$/,
            message: 'Enter a valid 10-digit Indian phone number starting with 6-9'
        },
        address: {
            min: 5,
            max: 100,
            message: 'Address must be between 5 and 100 characters'
        },
        city: {
            min: 2,
            pattern: /^[a-zA-Z\s\'-]+$/,
            message: 'Enter a valid city name'
        },
        pin_code: {
            pattern: /^\d{6}$/,
            message: 'Enter a valid 6-digit PIN code'
        },
        state: {
            required: true,
            message: 'State is required'
        }
    };

    const validateField = (input) => {
        const name = input.name;
        const value = input.value.trim();
        const rule = validationRules[name];
        let isValid = true;
        let message = '';

        if (!rule) return true;

        if (!value) {
            isValid = false;
            message = 'This field is required';
        } else if (rule.pattern && !rule.pattern.test(value)) {
            isValid = false;
            message = rule.message;
        } else if (rule.min && value.length < rule.min) {
            isValid = false;
            message = rule.message;
        } else if (rule.max && value.length > rule.max) {
            isValid = false;
            message = rule.message;
        }

        if (isValid) {
            input.classList.remove('is-invalid');
            input.classList.add('is-valid');
            const feedback = input.nextElementSibling;
            if (feedback && feedback.classList.contains('invalid-feedback')) {
                feedback.style.display = 'none';
            }
        } else {
            input.classList.remove('is-valid');
            input.classList.add('is-invalid');
            
            let feedback = input.nextElementSibling;
            if (!feedback || !feedback.classList.contains('invalid-feedback')) {
                feedback = document.createElement('div');
                feedback.className = 'invalid-feedback';
                input.parentNode.insertBefore(feedback, input.nextSibling);
            }
            feedback.textContent = message;
            feedback.style.display = 'block';
        }

        return isValid;
    };

    inputs.forEach(input => {
        input.addEventListener('blur', () => validateField(input));
        input.addEventListener('input', () => {
            if (input.classList.contains('is-invalid') || input.classList.contains('is-valid')) {
                validateField(input);
            }
        });
    });

    form.addEventListener('submit', function (event) {
        let isFormValid = true;
        inputs.forEach(input => {
            if (!validateField(input)) {
                isFormValid = false;
            }
        });

        if (!isFormValid) {
            event.preventDefault();
            event.stopPropagation();
            
            const firstError = form.querySelector('.is-invalid');
            if (firstError) {
                firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }
        }
    });
});
