document.addEventListener('DOMContentLoaded', () => {
  // Signup role wizard visualization
  const roleRadios = document.querySelectorAll('input[name="role"]');
  const rolePreview = document.querySelector('#role-preview');
  const slides = document.querySelectorAll('.role-slide');
  const updateRole = (value) => {
    if (rolePreview) rolePreview.textContent = value[0].toUpperCase() + value.slice(1);
    slides.forEach(s => s.style.display = (s.dataset.role === value) ? 'block' : 'none');
  };
  roleRadios.forEach(r => r.addEventListener('change', () => updateRole(r.value)));
  const checked = document.querySelector('input[name="role"]:checked');
  if (checked) updateRole(checked.value);

  // Simple client preview of selected file name
  const fileInput = document.querySelector('input[type="file"][name="file"]');
  const fileLabel = document.querySelector('#file-name');
  if (fileInput && fileLabel) {
    fileInput.addEventListener('change', () => {
      fileLabel.textContent = fileInput.files?.[0]?.name || 'No file selected';
    });
  }

  // AJAX message sending (progressive enhancement)
  const msgForm = document.querySelector('#message-form');
  if (msgForm) {
    msgForm.addEventListener('submit', async (e) => {
      e.preventDefault();
      const fd = new FormData(msgForm);
      const res = await fetch('backend/messages.php', { method: 'POST', body: fd });
      if (res.ok) {
        msgForm.reset();
        location.reload(); // simple refresh to reflect new messages
      } else {
        alert('Failed to send message.');
      }
    });
  }
});