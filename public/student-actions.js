/**
 * Student Actions JavaScript Library
 * Student-specific functionality for SMS3 LMS
 */

// View Grade Details
function viewGrade(id, data) {
  const modal = document.getElementById('viewModal') || createViewModal();
  const title = document.getElementById('viewModalTitle');
  const body = document.getElementById('viewModalBody');
  
  title.textContent = 'Grade Details';
  body.innerHTML = generateGradeView(data);
  
  const bootstrapModal = new bootstrap.Modal(modal);
  bootstrapModal.show();
}

// Generate Grade View
function generateGradeView(data) {
  let html = '<div class="card">';
  html += '<div class="card-body">';
  html += '<h5 class="card-title">' + (data.subject || 'Subject') + '</h5>';
  html += '<div class="row mt-3">';
  
  for (const [key, value] of Object.entries(data)) {
    const label = key.charAt(0).toUpperCase() + key.slice(1).replace(/([A-Z])/g, ' $1');
    html += `
      <div class="col-md-6 mb-3">
        <strong class="text-muted d-block">${label}</strong>
        <span class="fs-5">${value || 'N/A'}</span>
      </div>
    `;
  }
  
  html += '</div></div></div>';
  return html;
}

// Submit Assignment Modal
function submitAssignment(assignmentId, assignmentName) {
  const modalHTML = `
    <div class="modal fade" id="submitAssignmentModal" tabindex="-1">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header bg-primary text-white">
            <h5 class="modal-title"><i class="bi bi-upload me-2"></i>Submit Assignment</h5>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
          </div>
          <div class="modal-body">
            <h6>${assignmentName}</h6>
            <form id="submitAssignmentForm">
              <div class="mb-3">
                <label class="form-label fw-bold">Upload File <span class="text-danger">*</span></label>
                <input type="file" class="form-control" id="assignmentFile" required>
                <small class="text-muted">Accepted: PDF, DOCX, ZIP (Max: 10MB)</small>
              </div>
              <div class="mb-3">
                <label class="form-label fw-bold">Comments (Optional)</label>
                <textarea class="form-control" id="assignmentComments" rows="3" placeholder="Add any notes for your teacher..."></textarea>
              </div>
            </form>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
            <button type="button" class="btn btn-primary" onclick="handleSubmitAssignment(${assignmentId})">
              <i class="bi bi-check-lg me-2"></i>Submit
            </button>
          </div>
        </div>
      </div>
    </div>
  `;
  
  // Remove existing modal if any
  const existingModal = document.getElementById('submitAssignmentModal');
  if (existingModal) existingModal.remove();
  
  document.body.insertAdjacentHTML('beforeend', modalHTML);
  const modal = new bootstrap.Modal(document.getElementById('submitAssignmentModal'));
  modal.show();
}

// Handle Assignment Submission
function handleSubmitAssignment(assignmentId) {
  const file = document.getElementById('assignmentFile').files[0];
  
  if (!file) {
    alert('Please select a file to submit');
    return;
  }
  
  // Demo mode - in production, this would upload to server
  alert('Assignment submitted successfully! Your teacher will review it soon.');
  bootstrap.Modal.getInstance(document.getElementById('submitAssignmentModal')).hide();
  
  // Optionally refresh or update UI
  showNotification('Assignment submitted successfully!', 'success');
}

// Download Material
function downloadMaterial(materialId, materialName) {
  // In production, this would trigger actual download
  alert('Downloading: ' + materialName + '\n\n(Demo mode - file download would start)');
  showNotification('Download started: ' + materialName, 'info');
}

// Join Virtual Class
function joinVirtualClass(classId, meetingLink, className) {
  if (confirm('Join virtual class: ' + className + '?')) {
    window.open(meetingLink, '_blank');
    showNotification('Joining ' + className, 'info');
  }
}

// Submit Feedback
function submitFeedback(subject) {
  const modalHTML = `
    <div class="modal fade" id="submitFeedbackModal" tabindex="-1">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header bg-info text-white">
            <h5 class="modal-title"><i class="bi bi-chat-dots me-2"></i>Submit Feedback</h5>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
          </div>
          <div class="modal-body">
            <form id="feedbackForm">
              <div class="mb-3">
                <label class="form-label fw-bold">Subject</label>
                <input type="text" class="form-control" value="${subject || ''}" readonly>
              </div>
              <div class="mb-3">
                <label class="form-label fw-bold">Your Feedback <span class="text-danger">*</span></label>
                <textarea class="form-control" id="feedbackText" rows="4" placeholder="Share your thoughts..." required></textarea>
              </div>
              <div class="mb-3">
                <label class="form-label fw-bold">Rating</label>
                <select class="form-select" id="feedbackRating">
                  <option value="5">⭐⭐⭐⭐⭐ Excellent</option>
                  <option value="4">⭐⭐⭐⭐ Good</option>
                  <option value="3" selected>⭐⭐⭐ Average</option>
                  <option value="2">⭐⭐ Below Average</option>
                  <option value="1">⭐ Poor</option>
                </select>
              </div>
            </form>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
            <button type="button" class="btn btn-info text-white" onclick="handleSubmitFeedback()">
              <i class="bi bi-send me-2"></i>Submit
            </button>
          </div>
        </div>
      </div>
    </div>
  `;
  
  const existingModal = document.getElementById('submitFeedbackModal');
  if (existingModal) existingModal.remove();
  
  document.body.insertAdjacentHTML('beforeend', modalHTML);
  const modal = new bootstrap.Modal(document.getElementById('submitFeedbackModal'));
  modal.show();
}

// Handle Feedback Submission
function handleSubmitFeedback() {
  const feedbackText = document.getElementById('feedbackText').value;
  
  if (!feedbackText) {
    alert('Please enter your feedback');
    return;
  }
  
  alert('Thank you for your feedback! It has been sent to your teacher.');
  bootstrap.Modal.getInstance(document.getElementById('submitFeedbackModal')).hide();
  showNotification('Feedback submitted successfully!', 'success');
}

// Export Grades
function exportStudentGrades() {
  if (typeof XLSX === 'undefined') {
    alert('Export feature requires SheetJS library');
    return;
  }
  
  const wb = XLSX.utils.book_new();
  const data = [
    ['My Grade Report'],
    [''],
    ['Subject', 'Quiz Average', 'Assignment Average', 'Exam Score', 'Final Grade'],
    // Sample data - in production, fetch from actual grades
    ['Mathematics', '85%', '88%', '82%', '85%'],
    ['Science', '90%', '92%', '88%', '90%'],
    ['English', '78%', '80%', '75%', '78%']
  ];
  
  const ws = XLSX.utils.aoa_to_sheet(data);
  ws['!cols'] = [{ wch: 20 }, { wch: 15 }, { wch: 20 }, { wch: 12 }, { wch: 12 }];
  
  XLSX.utils.book_append_sheet(wb, ws, 'My Grades');
  XLSX.writeFile(wb, 'my-grades.xlsx');
  
  showNotification('Grade report exported successfully!', 'success');
}

// Show Notification (reuse from admin)
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

// Create View Modal (reuse)
function createViewModal() {
  const modalHTML = `
    <div class="modal fade" id="viewModal" tabindex="-1">
      <div class="modal-dialog modal-lg">
        <div class="modal-content">
          <div class="modal-header bg-primary text-white">
            <h5 class="modal-title" id="viewModalTitle">Details</h5>
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
