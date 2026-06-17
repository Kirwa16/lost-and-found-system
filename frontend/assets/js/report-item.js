document.addEventListener('DOMContentLoaded', () => {
    // Set mode based on URL query param
    const params = new URLSearchParams(window.location.search);
    const mode = params.get('mode') || 'lost';
    setMode(mode);

    // Image Preview
    const imageInput = document.getElementById('image');
    const imagePreview = document.getElementById('imagePreview');
    if(imageInput) {
        imageInput.addEventListener('change', function() {
            const file = this.files[0];
            if(file) {
                const reader = new FileReader();
                reader.onload = function(e) { imagePreview.src = e.target.result; imagePreview.style.display = 'block'; }
                reader.readAsDataURL(file);
            }
        });
    }

    // Form Submission
    const reportForm = document.getElementById('reportForm');
    if(reportForm) {
        reportForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            const btn = reportForm.querySelector('button[type="submit"]');
            btn.disabled = true; btn.textContent = 'Submitting...';

            const formData = new FormData(reportForm);
            // Note: For FormData, we don't set Content-Type to application/json, the browser sets it to multipart/form-data
            
            try {
                const res = await fetch('api/submit_item.php', {
                    method: 'POST',
                    body: formData // Francis's backend must read $_POST and $_FILES
                });
                const data = await res.json();

                if(res.ok && data.success) {
                    showToast('Item reported successfully!', 'success');
                    setTimeout(() => window.location.href = 'dashboard.html', 1500);
                } else {
                    showToast(data.message || 'Failed to submit report', 'error');
                }
            } catch (err) {
                showToast('Network error. Try again.', 'error');
            } finally {
                btn.disabled = false; btn.textContent = 'Submit Report';
            }
        });
    }
});

function setMode(mode) {
    const typeInput = document.getElementById('reportType');
    const btnLost = document.getElementById('btnLost');
    const btnFound = document.getElementById('btnFound');
    
    if(mode === 'found') {
        typeInput.value = 'found';
        btnLost.classList.remove('active');
        btnFound.classList.add('active', 'found-mode');
    } else {
        typeInput.value = 'lost';
        btnFound.classList.remove('active', 'found-mode');
        btnLost.classList.add('active');
    }
}