document.addEventListener('DOMContentLoaded', function () {
    const photoInput = document.getElementById('candidatePhoto');
    const photoPreview = document.getElementById('photoPreview');
    const candidateForm = document.getElementById('candidateForm');
    const candidateIdInput = document.getElementById('candidateId');

    // Preview candidate photo
    if (photoInput) {
        photoInput.addEventListener('change', function (e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function (event) {
                    photoPreview.src = event.target.result;
                    photoPreview.style.opacity = '0';
                    setTimeout(() => {
                        photoPreview.style.opacity = '1';
                    }, 10);
                };
                reader.readAsDataURL(file);
            } else {
                photoPreview.src = '';
            }
        });
    }

    // Handle form submission (add/update)
    if (candidateForm) {
        candidateForm.addEventListener('submit', function (e) {
            e.preventDefault();

            const id = candidateIdInput.value;
            const name = document.getElementById('candidateName').value.trim();
            const party = document.getElementById('partyList').value.trim();
            const studentId = document.getElementById('studentId').value.trim();
            const position = document.getElementById('candidatePosition').value;
            const photoFile = document.getElementById('candidatePhoto').files[0];

            if (!name || !party || !studentId || !position || (!id && !photoFile)) {
                alert('Please fill in all required fields.');
                return;
            }

            // Check if student ID exists before saving
            fetch('../back_end/check_student_id.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: new URLSearchParams({ student_id: studentId })
            })
            .then(response => response.json())
            .then(data => {
                if (!data.exists) {
                    // Show error toast or modal
                    alert('Student ID does not exist in the student records. Cannot save candidate.');
                    return;
                }

                // Proceed with candidate saving
                const formData = new FormData();
                formData.append('id', id);
                formData.append('name', name);
                formData.append('partylist', party);
                formData.append('student_id', studentId);
                formData.append('position', position);
                if (photoFile) {
                    formData.append('photo', photoFile);
                }

                fetch('../back_end/save_candidate.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(result => {
                    if (result.success) {
                        const modal = bootstrap.Modal.getInstance(document.getElementById('addCandidateModal'));
                        if (modal) modal.hide();

                        candidateForm.reset();
                        photoPreview.src = '';
                        candidateIdInput.value = '';
                        loadCandidates();
                    } else {
                        alert(result.error || 'An error occurred while saving.');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Failed to save candidate. Check console for details.');
                });
            })
            .catch(err => {
                console.error('Student ID check failed:', err);
                alert('Failed to verify student ID. Try again later.');
            });
        });
    }

    // Fetch and render candidates
    function loadCandidates() {
        fetch('../back_end/get_candidates.php')
            .then(response => response.text())
            .then(data => {
                const tbody = document.querySelector('table tbody');
                if (tbody) {
                    tbody.innerHTML = data;
                } else {
                    console.warn("Candidate table body not found.");
                }
            })
            .catch(error => {
                console.error('Error loading candidates:', error);
            });
    }

    // Pre-fill modal form for editing
    document.body.addEventListener('click', function (e) {
        const btn = e.target.closest('.edit-btn');
        if (btn) {
            candidateIdInput.value = btn.getAttribute('data-id') || '';
            document.getElementById('candidateName').value = btn.getAttribute('data-name') || '';
            document.getElementById('partyList').value = btn.getAttribute('data-party') || '';
            document.getElementById('candidatePosition').value = btn.getAttribute('data-position') || '';
            const photoUrl = btn.getAttribute('data-photo');
            photoPreview.src = photoUrl || '';
        }
    });

    // Load candidates on first load
    loadCandidates();
});
