/**
 * Modal Handlers - Database Integration
 * Handles all Create/Add/Upload modal submissions with database connections
 */

// Get CSRF Token
function getCSRFToken() {
    return document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
}

/**
 * Create Quiz Handler
 */
function handleCreateQuiz() {
    const title = document.getElementById('quizTitle')?.value;
    const sectionId = document.getElementById('quizSection')?.value;
    const duration = document.getElementById('quizDuration')?.value || 30;
    const totalPoints = document.getElementById('quizPoints')?.value || 100;
    const deadline = document.getElementById('quizDeadline')?.value;

    if (!title || !sectionId) {
        Swal.fire({
            icon: 'error',
            title: 'Missing Fields',
            text: 'Please fill in all required fields'
        });
        return;
    }

    Swal.fire({
        title: 'Creating Quiz...',
        allowOutsideClick: false,
        didOpen: () => Swal.showLoading()
    });

    fetch('/admin/quizzes/store', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': getCSRFToken(),
            'Accept': 'application/json'
        },
        body: JSON.stringify({
            title,
            section_id: sectionId,
            duration,
            total_points: totalPoints,
            deadline
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            Swal.fire({
                icon: 'success',
                title: 'Quiz Created!',
                text: data.message,
                showConfirmButton: true
            }).then(() => {
                // Close modal
                const modalElement = document.getElementById('createQuizModal');
                if (modalElement) {
                    bootstrap.Modal.getInstance(modalElement)?.hide();
                }
                // Reload page
                location.reload();
            });
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: data.message
            });
        }
    })
    .catch(error => {
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'Failed to create quiz. Please try again.'
        });
        console.error('Error:', error);
    });
}

/**
 * Create Assignment Handler
 */
function handleCreateAssignment() {
    const title = document.getElementById('assignmentTitle')?.value;
    const classId = document.getElementById('assignmentClass')?.value;
    const description = document.getElementById('assignmentDescription')?.value;
    const dueDate = document.getElementById('assignmentDueDate')?.value;
    const maxPoints = document.getElementById('assignmentPoints')?.value || 100;

    if (!title || !classId || !dueDate) {
        Swal.fire({
            icon: 'error',
            title: 'Missing Fields',
            text: 'Please fill in all required fields'
        });
        return;
    }

    Swal.fire({
        title: 'Creating Assignment...',
        allowOutsideClick: false,
        didOpen: () => Swal.showLoading()
    });

    fetch('/admin/assignments/store', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': getCSRFToken(),
            'Accept': 'application/json'
        },
        body: JSON.stringify({
            title,
            class_id: classId,
            description,
            due_date: dueDate,
            max_points: maxPoints
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            Swal.fire({
                icon: 'success',
                title: 'Assignment Created!',
                text: data.message,
                showConfirmButton: true
            }).then(() => {
                const modalElement = document.getElementById('createAssignmentModal');
                if (modalElement) {
                    bootstrap.Modal.getInstance(modalElement)?.hide();
                }
                location.reload();
            });
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: data.message
            });
        }
    })
    .catch(error => {
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'Failed to create assignment. Please try again.'
        });
        console.error('Error:', error);
    });
}

/**
 * Upload Material Handler
 */
function handleUploadMaterial() {
    const title = document.getElementById('materialTitle')?.value;
    const classId = document.getElementById('materialClass')?.value;
    const materialType = document.getElementById('materialType')?.value;
    const fileInput = document.getElementById('materialFile');
    const file = fileInput?.files[0];

    if (!title || !classId || !materialType) {
        Swal.fire({
            icon: 'error',
            title: 'Missing Fields',
            text: 'Please fill in all required fields'
        });
        return;
    }

    Swal.fire({
        title: 'Uploading Material...',
        allowOutsideClick: false,
        didOpen: () => Swal.showLoading()
    });

    const formData = new FormData();
    formData.append('title', title);
    formData.append('class_id', classId);
    formData.append('material_type', materialType);
    if (file) {
        formData.append('file', file);
    }

    fetch('/admin/materials/upload', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': getCSRFToken(),
            'Accept': 'application/json'
        },
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            Swal.fire({
                icon: 'success',
                title: 'Material Uploaded!',
                text: data.message,
                showConfirmButton: true
            }).then(() => {
                const modalElement = document.getElementById('uploadMaterialModal');
                if (modalElement) {
                    bootstrap.Modal.getInstance(modalElement)?.hide();
                }
                location.reload();
            });
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: data.message
            });
        }
    })
    .catch(error => {
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'Failed to upload material. Please try again.'
        });
        console.error('Error:', error);
    });
}

/**
 * Teacher: Quick Create Quiz
 */
function handleQuickQuizCreate() {
    const title = document.getElementById('quizTitle')?.value;
    const duration = document.getElementById('quizDuration')?.value || 30;
    const points = document.getElementById('quizPoints')?.value || 100;
    const dueDate = document.getElementById('quizDueDate')?.value;

    if (!title) {
        Swal.fire({
            icon: 'error',
            title: 'Missing Title',
            text: 'Please enter a quiz title'
        });
        return;
    }

    Swal.fire({
        title: 'Creating Quiz...',
        allowOutsideClick: false,
        didOpen: () => Swal.showLoading()
    });

    // For teacher, we'll use the same endpoint but with teacher prefix
    fetch('/teacher/quizzes/store', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': getCSRFToken(),
            'Accept': 'application/json'
        },
        body: JSON.stringify({
            title,
            duration,
            total_points: points,
            deadline: dueDate
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            Swal.fire({
                icon: 'success',
                title: 'Quiz Created!',
                text: 'Your quiz has been created successfully',
                showConfirmButton: true
            }).then(() => {
                location.reload();
            });
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: data.message
            });
        }
    })
    .catch(error => {
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'Failed to create quiz. Please try again.'
        });
        console.error('Error:', error);
    });
}

/**
 * Teacher: Upload Material
 */
function handleTeacherUploadMaterial() {
    const title = document.getElementById('materialTitle')?.value;
    const materialType = document.getElementById('materialType')?.value;
    const fileInput = document.getElementById('materialFile');
    const file = fileInput?.files[0];

    if (!title || !materialType) {
        Swal.fire({
            icon: 'error',
            title: 'Missing Fields',
            text: 'Please fill in all required fields'
        });
        return;
    }

    Swal.fire({
        title: 'Uploading Material...',
        allowOutsideClick: false,
        didOpen: () => Swal.showLoading()
    });

    const formData = new FormData();
    formData.append('title', title);
    formData.append('material_type', materialType);
    if (file) {
        formData.append('file', file);
    }

    fetch('/teacher/materials/upload', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': getCSRFToken(),
            'Accept': 'application/json'
        },
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            Swal.fire({
                icon: 'success',
                title: 'Material Uploaded!',
                text: data.message,
                showConfirmButton: true
            }).then(() => {
                const modalElement = document.getElementById('uploadMaterialModal');
                if (modalElement) {
                    bootstrap.Modal.getInstance(modalElement)?.hide();
                }
                location.reload();
            });
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: data.message
            });
        }
    })
    .catch(error => {
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'Failed to upload material. Please try again.'
        });
        console.error('Error:', error);
    });
}

// Make functions globally available
window.handleCreateQuiz = handleCreateQuiz;
window.handleCreateAssignment = handleCreateAssignment;
window.handleUploadMaterial = handleUploadMaterial;
window.handleTeacherUploadMaterial = handleTeacherUploadMaterial;
window.handleQuickQuizCreate = handleQuickQuizCreate;
