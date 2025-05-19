document.addEventListener('DOMContentLoaded', function() {
    initializeBookForms();
    setupTableInteractions();
    initializeSearch();
});

function initializeBookForms() {
    const addBookForm = document.getElementById('addBookForm');
    
    if (addBookForm) {
        addBookForm.addEventListener('submit', function(e) {
            if (!validateBookForm(this)) {
                e.preventDefault();
            }
        });
    }
}

function validateBookForm(form) {
    let isValid = true;
    const inputs = form.querySelectorAll('input[required]');
    
    inputs.forEach(input => {
        if (!input.value.trim()) {
            showError(input, 'This field is required');
            isValid = false;
        } else {
            removeError(input);
            
            if (input.name === 'price') {
                const price = parseFloat(input.value);
                if (isNaN(price) || price <= 0) {
                    showError(input, 'Please enter a valid price');
                    isValid = false;
                }
            }
        }
    });
    
    return isValid;
}

function setupTableInteractions() {
    const table = document.querySelector('.books-table');
    if (!table) return;

    // Add row hover effect
    const rows = table.querySelectorAll('tbody tr');
    rows.forEach(row => {
        row.addEventListener('mouseenter', function() {
            this.style.transform = 'translateX(5px)';
            this.style.transition = 'transform 0.3s ease';
        });
        
        row.addEventListener('mouseleave', function() {
            this.style.transform = 'none';
        });
    });

    // Setup edit buttons
    const editButtons = table.querySelectorAll('.edit-btn');
    editButtons.forEach(btn => {
        btn.addEventListener('click', function() {
            const row = this.closest('tr');
            const id = row.querySelector('td:first-child').textContent;
            handleEdit(id);
        });
    });

    // Setup delete buttons
    const deleteButtons = table.querySelectorAll('.delete-btn');
    deleteButtons.forEach(btn => {
        btn.addEventListener('click', function() {
            const row = this.closest('tr');
            const id = row.querySelector('td:first-child').textContent;
            handleDelete(id, row);
        });
    });
}

function handleEdit(id) {
    // Create edit form
    const row = document.querySelector(`tr[data-id="${id}"]`);
    const cells = row.querySelectorAll('td');
    
    cells.forEach((cell, index) => {
        if (index > 0 && index < cells.length - 1) {
            const text = cell.textContent;
            const input = document.createElement('input');
            input.type = index === 3 ? 'number' : 'text';
            input.value = text;
            input.step = index === 3 ? '0.01' : null;
            cell.textContent = '';
            cell.appendChild(input);
        }
    });
}

function handleDelete(id, row) {
    if (confirm('Are you sure you want to delete this book?')) {
        // Add animation before removal
        row.style.transition = 'all 0.3s ease';
        row.style.opacity = '0';
        row.style.transform = 'translateX(-100%)';
        
        setTimeout(() => {
            row.remove();
        }, 300);
        
        // Here you would typically make an AJAX call to delete the book
        fetch(`delete_book.php?id=${id}`, {
            method: 'DELETE'
        }).then(response => {
            if (!response.ok) {
                showToast('Error deleting book', 'error');
            } else {
                showToast('Book deleted successfully', 'success');
            }
        });
    }
}

function initializeSearch() {
    const searchInput = document.querySelector('.search-input');
    if (!searchInput) return;

    let timeout = null;
    searchInput.addEventListener('input', function() {
        clearTimeout(timeout);
        timeout = setTimeout(() => {
            filterBooks(this.value.toLowerCase());
        }, 300);
    });
}

function filterBooks(query) {
    const rows = document.querySelectorAll('.books-table tbody tr');
    rows.forEach(row => {
        const text = row.textContent.toLowerCase();
        row.style.display = text.includes(query) ? '' : 'none';
    });
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

function showToast(message, type = 'info') {
    const toast = document.createElement('div');
    toast.className = `toast toast-${type}`;
    toast.textContent = message;
    
    document.body.appendChild(toast);
    
    setTimeout(() => {
        toast.classList.add('show');
    }, 100);

    setTimeout(() => {
        toast.classList.remove('show');
        setTimeout(() => toast.remove(), 300);
    }, 3000);
}
