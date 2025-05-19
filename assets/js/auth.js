document.addEventListener('DOMContentLoaded', function() {
    initializeAuthForms();
    setupPasswordVisibility();
});

function initializeAuthForms() {
    const loginForm = document.getElementById('loginForm');
    const registerForm = document.getElementById('registerForm');

    if (loginForm) {
        loginForm.addEventListener('submit', function(e) {
            if (!validateForm(this)) {
                e.preventDefault();
            }
        });
    }

    if (registerForm) {
        registerForm.addEventListener('submit', function(e) {
            if (!validateForm(this)) {
                e.preventDefault();
            }
        });
    }
}

function validateForm(form) {
    let isValid = true;
    const inputs = form.querySelectorAll('input[required]');

    inputs.forEach(input => {
        if (!input.value.trim()) {
            showError(input, 'This field is required');
            isValid = false;
        } else {
            removeError(input);
        }

        if (input.type === 'password' && input.value.length < 6) {
            showError(input, 'Password must be at least 6 characters');
            isValid = false;
        }
    });

    return isValid;
}

function showError(input, message) {
    removeError(input);
    input.classList.add('error');
    const errorDiv = document.createElement('div');
    errorDiv.className = 'error-message';
    errorDiv.textContent = message;
    input.parentNode.appendChild(errorDiv);
}

function removeError(input) {
    input.classList.remove('error');
    const errorDiv = input.parentNode.querySelector('.error-message');
    if (errorDiv) {
        errorDiv.remove();
    }
}

function setupPasswordVisibility() {
    const passwordInputs = document.querySelectorAll('input[type="password"]');
    
    passwordInputs.forEach(input => {
        const toggleButton = document.createElement('button');
        toggleButton.type = 'button';
        toggleButton.className = 'password-toggle';
        toggleButton.innerHTML = '<i class="fas fa-eye"></i>';
        
        toggleButton.addEventListener('click', function() {
            const type = input.type === 'password' ? 'text' : 'password';
            input.type = type;
            toggleButton.innerHTML = type === 'password' ? 
                '<i class="fas fa-eye"></i>' : 
                '<i class="fas fa-eye-slash"></i>';
        });
        
        input.parentNode.style.position = 'relative';
        input.parentNode.appendChild(toggleButton);
    });
}

// Add smooth transitions for messages
function fadeOutMessage() {
    const messages = document.querySelectorAll('.auth-message');
    messages.forEach(message => {
        setTimeout(() => {
            message.style.opacity = '0';
            setTimeout(() => message.remove(), 300);
        }, 3000);
    });
}
