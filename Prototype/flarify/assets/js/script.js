// Role selection sync
document.querySelectorAll('.option').forEach(option => {
    option.addEventListener('click', () => {
        const role = option.querySelector('h3').textContent;
        document.querySelector('select[name="role"]').value = role;
        // Highlight selected
        document.querySelectorAll('.option').forEach(o => o.classList.remove('active'));
        option.classList.add('active');
    });
});

// Form validation example (add to forms)
function validateForm(form) {
    // Basic check
    return true;
}

// For messages: Poll for new messages (simplified)
setInterval(() => {
    // AJAX to fetch new messages
}, 5000);