/**
 * Teacher Actions JavaScript Library
 * Teacher-specific functionality for SMS3 LMS
 */

// Grade Assignment Modal
function gradeAssignment(submissionId, studentName, assignmentName) {
  const modalHTML = `
    <div class="modal fade" id="gradeAssignmentModal" tabindex="-1">
      <div class="modal-dialog modal-lg">
        <div class="modal-content">
          <div class="modal-header bg-success text-white">
            <h5 class="modal-title"><i class="bi bi-pencil-square me-2"></i>Grade Assignment</h5>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
          </div>
          <div class="modal-body">
            <div class="alert alert-info">
              <strong>Student:</strong> ${studentName}<br>
              <strong>Assignment:</strong> ${assignmentName}
            </div>
            <form id="gradeForm">
              <div class="row mb-3">
                <div class="col-md-6">
                  <label class="form-label fw-bold">Grade/Score <span class="text-danger">*</span></label>
                  <input type="number" class="form-control" id="gradeScore" min="0" max="100" placeholder="0-100" required>
                </div>
                <div class="col-md-6">
                  <label class="form-label fw-bold">Status</label>
                  <select class="form-select" id="gradeStatus">
                    <option value="passed">Passed</option>
                    <option value="failed">Failed</option>
                    <option value="incomplete">Incomplete</option>
                  </select>
                </div>
              </div>
              <div class="mb-3">
                <label class="form-label fw-bold">Feedback/Comments</label>
                <textarea class="form-control" id="gradeFeedback" rows="4" placeholder="Provide feedback to the student..."></textarea>
              </div>
              <div class="form-check">
                <input class="form-check-input" type="checkbox" id="notifyStudent" checked>
                <label class="form-check-label" for="notifyStudent">
                  Send email notification to student
                </label>
              </div>
            </form>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
            <button type="button" class="btn btn-success" onclick="handleGradeSubmission(${submissionId})">
              <i class="bi bi-check-lg me-2"></i>Submit Grade
            </button>
          </div>
        </div>
      </div>
    </div>
  `;
  
  const existingModal = document.getElementById('gradeAssignmentModal');
  if (existingModal) existingModal.remove();
  
  document.body.insertAdjacentHTML('beforeend', modalHTML);
  const modal = new bootstrap.Modal(document.getElementById('gradeAssignmentModal'));
  modal.show();
}

// Handle Grade Submission
function handleGradeSubmission(submissionId) {
  const score = document.getElementById('gradeScore').value;
  const feedback = document.getElementById('gradeFeedback').value;
  
  if (!score) {
    alert('Please enter a grade score');
    return;
  }
  
  alert('Grade submitted successfully! The student has been notified.');
  bootstrap.Modal.getInstance(document.getElementById('gradeAssignmentModal')).hide();
  showNotification('Grade recorded successfully!', 'success');
}

// View Student Progress
function viewStudentProgress(studentId, studentName, data) {
  const modal = document.getElementById('viewModal') || createViewModal();
  const title = document.getElementById('viewModalTitle');
  const body = document.getElementById('viewModalBody');
  
  title.textContent = studentName + ' - Progress Report';
  body.innerHTML = generateProgressView(data);
  
  const bootstrapModal = new bootstrap.Modal(modal);
  bootstrapModal.show();
}

// Generate Progress View
function generateProgressView(data) {
  let html = '<div class="row">';
  html += '<div class="col-md-6 mb-3">';
  html += '<div class="card text-center">';
  html += '<div class="card-body">';
  html += '<h6 class="text-muted">Quiz Average</h6>';
  html += '<h3 class="text-primary">' + (data.quizAvg || 'N/A') + '</h3>';
  html += '</div></div></div>';
  
  html += '<div class="col-md-6 mb-3">';
  html += '<div class="card text-center">';
  html += '<div class="card-body">';
  html += '<h6 class="text-muted">Assignment Average</h6>';
  html += '<h3 class="text-success">' + (data.assignmentAvg || 'N/A') + '</h3>';
  html += '</div></div></div>';
  
  html += '<div class="col-md-6 mb-3">';
  html += '<div class="card text-center">';
  html += '<div class="card-body">';
  html += '<h6 class="text-muted">Attendance</h6>';
  html += '<h3 class="text-info">' + (data.attendance || 'N/A') + '</h3>';
  html += '</div></div></div>';
  
  html += '<div class="col-md-6 mb-3">';
  html += '<div class="card text-center">';
  html += '<div class="card-body">';
  html += '<h6 class="text-muted">Final Grade</h6>';
  html += '<h3 class="text-warning">' + (data.finalGrade || 'N/A') + '</h3>';
  html += '</div></div></div>';
  
  html += '</div>';
  return html;
}

// Export Class Gradebook
function exportClassGradebook(className) {
  if (typeof XLSX === 'undefined') {
    alert('Export feature requires SheetJS library');
    return;
  }
  
  const wb = XLSX.utils.book_new();
  const data = [
    ['Class Gradebook - ' + (className || 'All Classes')],
    [''],
    ['Student Name', 'Quiz Avg', 'Assignment Avg', 'Exam Score', 'Final Grade', 'Status'],
    // Sample data
    ['Juan Dela Cruz', '88%', '90%', '85%', '88%', 'Passed'],
    ['Maria Santos', '75%', '78%', '70%', '74%', 'Failed'],
    ['Pedro Garcia', '92%', '95%', '90%', '92%', 'Passed']
  ];
  
  const ws = XLSX.utils.aoa_to_sheet(data);
  ws['!cols'] = [{ wch: 20 }, { wch: 10 }, { wch: 15 }, { wch: 12 }, { wch: 12 }, { wch: 10 }];
  
  XLSX.utils.book_append_sheet(wb, ws, 'Gradebook');
  XLSX.writeFile(wb, 'class-gradebook.xlsx');
  
  showNotification('Gradebook exported successfully!', 'success');
}

// Export Attendance Report
function exportAttendance(className) {
  if (typeof XLSX === 'undefined') {
    alert('Export feature requires SheetJS library');
    return;
  }
  
  const wb = XLSX.utils.book_new();
  const data = [
    ['Attendance Report - ' + (className || 'All Classes')],
    [''],
    ['Student Name', 'Present', 'Absent', 'Late', 'Attendance Rate'],
    ['Juan Dela Cruz', '45', '2', '3', '90%'],
    ['Maria Santos', '40', '7', '3', '80%'],
    ['Pedro Garcia', '48', '1', '1', '96%']
  ];
  
  const ws = XLSX.utils.aoa_to_sheet(data);
  ws['!cols'] = [{ wch: 20 }, { wch: 10 }, { wch: 10 }, { wch: 10 }, { wch: 15 }];
  
  XLSX.utils.book_append_sheet(wb, ws, 'Attendance');
  XLSX.writeFile(wb, 'attendance-report.xlsx');
  
  showNotification('Attendance report exported successfully!', 'success');
}

// Quick Create Quiz
function quickCreateQuiz() {
  const modalHTML = `
    <div class="modal fade" id="quickQuizModal" tabindex="-1">
      <div class="modal-dialog modal-lg">
        <div class="modal-content">
          <div class="modal-header bg-primary text-white">
            <h5 class="modal-title"><i class="bi bi-plus-circle me-2"></i>Quick Create Quiz</h5>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
          </div>
          <div class="modal-body">
            <form id="quickQuizForm">
              <div class="mb-3">
                <label class="form-label fw-bold">Quiz Title <span class="text-danger">*</span></label>
                <input type="text" class="form-control" id="quizTitle" placeholder="e.g., Math Quiz 1" required>
              </div>
              <div class="row mb-3">
                <div class="col-md-6">
                  <label class="form-label fw-bold">Duration (minutes)</label>
                  <input type="number" class="form-control" id="quizDuration" value="30" min="5">
                </div>
                <div class="col-md-6">
                  <label class="form-label fw-bold">Total Points</label>
                  <input type="number" class="form-control" id="quizPoints" value="100" min="1">
                </div>
              </div>
              <div class="mb-3">
                <label class="form-label fw-bold">Due Date</label>
                <input type="date" class="form-control" id="quizDueDate">
              </div>
            </form>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
            <button type="button" class="btn btn-primary" onclick="handleQuickQuizCreate()">
              <i class="bi bi-check-lg me-2"></i>Create Quiz
            </button>
          </div>
        </div>
      </div>
    </div>
  `;
  
  const existingModal = document.getElementById('quickQuizModal');
  if (existingModal) existingModal.remove();
  
  document.body.insertAdjacentHTML('beforeend', modalHTML);
  const modal = new bootstrap.Modal(document.getElementById('quickQuizModal'));
  modal.show();
}

// Handle Quick Quiz Creation
function handleQuickQuizCreate() {
  const title = document.getElementById('quizTitle').value;
  
  if (!title) {
    alert('Please enter a quiz title');
    return;
  }
  
  alert('Quiz "' + title + '" created successfully! You can now add questions.');
  bootstrap.Modal.getInstance(document.getElementById('quickQuizModal')).hide();
  showNotification('Quiz created successfully!', 'success');
}

// View/Edit/Delete from admin-actions.js (reuse)
function viewRecord(id, type, data) {
  const modal = document.getElementById('viewModal') || createViewModal();
  const title = document.getElementById('viewModalTitle');
  const body = document.getElementById('viewModalBody');
  
  title.textContent = `View ${type} Details`;
  body.innerHTML = generateViewContent(data);
  
  const bootstrapModal = new bootstrap.Modal(modal);
  bootstrapModal.show();
}

function editRecord(id, type, data) {
  const modal = document.getElementById('editModal') || createEditModal();
  const title = document.getElementById('editModalTitle');
  const body = document.getElementById('editModalBody');
  
  title.textContent = `Edit ${type}`;
  body.innerHTML = generateEditForm(type, data);
  
  const bootstrapModal = new bootstrap.Modal(modal);
  bootstrapModal.show();
}

function deleteRecord(id, type, name) {
  if (confirm(`Are you sure you want to delete this ${type}?\n\n"${name}"\n\nThis action cannot be undone.`)) {
    alert(`${type} "${name}" deleted successfully! (Demo mode)`);
    showNotification(`${type} deleted successfully!`, 'success');
  }
}

// Helper functions (reuse from admin)
function generateViewContent(data) {
  let html = '<div class="row">';
  for (const [key, value] of Object.entries(data)) {
    const label = key.charAt(0).toUpperCase() + key.slice(1).replace(/([A-Z])/g, ' $1');
    html += `
      <div class="col-md-6 mb-3">
        <strong class="text-muted d-block">${label}</strong>
        <span>${value || 'N/A'}</span>
      </div>
    `;
  }
  html += '</div>';
  return html;
}

function generateEditForm(type, data) {
  let html = '<form id="editForm">';
  for (const [key, value] of Object.entries(data)) {
    if (key === 'id') continue;
    const label = key.charAt(0).toUpperCase() + key.slice(1).replace(/([A-Z])/g, ' $1');
    html += `
      <div class="mb-3">
        <label class="form-label fw-bold">${label}</label>
        <input type="text" class="form-control" name="${key}" value="${value || ''}" required>
      </div>
    `;
  }
  html += '</form>';
  return html;
}

function createViewModal() {
  const modalHTML = `
    <div class="modal fade" id="viewModal" tabindex="-1">
      <div class="modal-dialog modal-lg">
        <div class="modal-content">
          <div class="modal-header bg-primary text-white">
            <h5 class="modal-title" id="viewModalTitle">View Details</h5>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
          </div>
          <div class="modal-body" id="viewModalBody"></div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
          </div>
        </div>
      </div>
    </div>
  `;
  document.body.insertAdjacentHTML('beforeend', modalHTML);
  return document.getElementById('viewModal');
}

function createEditModal() {
  const modalHTML = `
    <div class="modal fade" id="editModal" tabindex="-1">
      <div class="modal-dialog modal-lg">
        <div class="modal-content">
          <div class="modal-header bg-warning">
            <h5 class="modal-title" id="editModalTitle">Edit Record</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
          </div>
          <div class="modal-body" id="editModalBody"></div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
            <button type="button" class="btn btn-warning" onclick="saveEdit()">
              <i class="bi bi-save me-2"></i>Save Changes
            </button>
          </div>
        </div>
      </div>
    </div>
  `;
  document.body.insertAdjacentHTML('beforeend', modalHTML);
  return document.getElementById('editModal');
}

function saveEdit() {
  const form = document.getElementById('editForm');
  if (form.checkValidity()) {
    alert('Changes saved successfully! (Demo mode)');
    bootstrap.Modal.getInstance(document.getElementById('editModal')).hide();
    showNotification('Changes saved successfully!', 'success');
  } else {
    alert('Please fill in all required fields');
  }
}

function showNotification(message, type = 'success') {
  const alertClass = type === 'success' ? 'alert-success' : type === 'danger' ? 'alert-danger' : 'alert-info';
  const icon = type === 'success' ? 'check-circle-fill' : type === 'danger' ? 'exclamation-triangle-fill' : 'info-circle-fill';
  
  const notification = document.createElement('div');
  notification.className = `alert ${alertClass} alert-dismissible fade show position-fixed top-0 start-50 translate-middle-x mt-3`;
  notification.style.zIndex = '9999';
  notification.innerHTML = `
    <i class="bi bi-${icon} me-2"></i>${message}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
  `;
  
  document.body.appendChild(notification);
  setTimeout(() => notification.remove(), 3000);
}
