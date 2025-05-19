document.addEventListener('DOMContentLoaded', function() {
    initializeProfileForm();
    initializePasswordValidation();
});

function initializeProfileForm() {
    const form = document.getElementById('profileUpdateForm');
    if (!form) return;

    form.addEventListener('submit', async function(e) {
        e.preventDefault();
        
        if (!validateForm()) {
            return;
        }

        const formData = new FormData(form);
        try {
            const response = await fetch('../controllers/update_profile.php', {
                method: 'POST',
                body: formData
            });

            const data = await response.json();
            if (data.success) {
                showMessage('Profile updated successfully!', 'success');
                if (formData.get('new_password')) {
                    form.reset();
                }
            } else {
                showMessage(data.message || 'Error updating profile', 'error');
            }
        } catch (error) {
            showMessage('Error updating profile', 'error');
            console.error('Error:', error);
        }
    });
}

function validateForm() {
    const newPassword = document.getElementById('new_password');
    const confirmPassword = document.getElementById('confirm_password');
    const email = document.getElementById('email');
    
    let isValid = true;

    // Validate email
    if (email.value && !isValidEmail(email.value)) {
        showError(email, 'Please enter a valid email address');
        isValid = false;
    } else {
        removeError(email);
    }

    // Validate passwords if either field is filled
    if (newPassword.value || confirmPassword.value) {
        if (newPassword.value.length < 6) {
            showError(newPassword, 'Password must be at least 6 characters');
            isValid = false;
        } else {
            removeError(newPassword);
        }

        if (newPassword.value !== confirmPassword.value) {
            showError(confirmPassword, 'Passwords do not match');
            isValid = false;
        } else {
            removeError(confirmPassword);
        }
    }

    return isValid;
}

function initializePasswordValidation() {
    const newPassword = document.getElementById('new_password');
    const confirmPassword = document.getElementById('confirm_password');
    
    if (newPassword && confirmPassword) {
        confirmPassword.addEventListener('input', function() {
            if (newPassword.value !== this.value) {
                showError(this, 'Passwords do not match');
            } else {
                removeError(this);
            }
        });
    }
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

function showMessage(message, type = 'success') {
    const messageDiv = document.createElement('div');
    messageDiv.className = type === 'success' ? 'success-message' : 'error-message';
    messageDiv.textContent = message;
    
    const form = document.getElementById('profileUpdateForm');
    form.insertAdjacentElement('beforebegin', messageDiv);
    
    setTimeout(() => {
        messageDiv.remove();
    }, 5000);
}

function isValidEmail(email) {
    return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
}

// Update profile photo preview if file input exists
const photoInput = document.getElementById('profile_photo');
const photoPreview = document.getElementById('photoPreview');
if (photoInput && photoPreview) {
    photoInput.addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                photoPreview.style.backgroundImage = `url(${e.target.result})`;
            };
            reader.readAsDataURL(file);
        }
    });
}
