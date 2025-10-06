/**
 * Admin Actions JavaScript Library
 * Provides view, edit, and delete functionality for all admin pages
 */

// View Record Details
function viewRecord(id, type, data) {
  const modal = document.getElementById('viewModal') || createViewModal();
  const title = document.getElementById('viewModalTitle');
  const body = document.getElementById('viewModalBody');
  
  title.textContent = `View ${type} Details`;
  body.innerHTML = generateViewContent(data);
  
  const bootstrapModal = new bootstrap.Modal(modal);
  bootstrapModal.show();
}

// Edit Record
function editRecord(id, type, data) {
  const modal = document.getElementById('editModal') || createEditModal();
  const title = document.getElementById('editModalTitle');
  const body = document.getElementById('editModalBody');
  
  title.textContent = `Edit ${type}`;
  body.innerHTML = generateEditForm(type, data);
  
  const bootstrapModal = new bootstrap.Modal(modal);
  bootstrapModal.show();
}

// Delete Record with Confirmation
function deleteRecord(id, type, name) {
  if (confirm(`Are you sure you want to delete this ${type}?\n\n"${name}"\n\nThis action cannot be undone.`)) {
    // In production, this would make an API call
    alert(`${type} "${name}" deleted successfully! (Demo mode)`);
    // Optionally remove the row from table
    // removeTableRow(id);
  }
}

// Create View Modal if doesn't exist
function createViewModal() {
  const modalHTML = `
    <div class="modal fade" id="viewModal" tabindex="-1">
      <div class="modal-dialog modal-lg">
        <div class="modal-content">
          <div class="modal-header bg-primary text-white">
            <h5 class="modal-title" id="viewModalTitle">View Details</h5>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
          </div>
          <div class="modal-body" id="viewModalBody">
            <!-- Content will be injected here -->
          </div>
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

// Create Edit Modal if doesn't exist
function createEditModal() {
  const modalHTML = `
    <div class="modal fade" id="editModal" tabindex="-1">
      <div class="modal-dialog modal-lg">
        <div class="modal-content">
          <div class="modal-header bg-warning">
            <h5 class="modal-title" id="editModalTitle">Edit Record</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
          </div>
          <div class="modal-body" id="editModalBody">
            <!-- Content will be injected here -->
          </div>
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

// Generate view content based on data
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

// Generate edit form based on type and data
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

// Save edit (to be implemented with backend)
function saveEdit() {
  const form = document.getElementById('editForm');
  if (form.checkValidity()) {
    alert('Changes saved successfully! (Demo mode)');
    bootstrap.Modal.getInstance(document.getElementById('editModal')).hide();
  } else {
    alert('Please fill in all required fields');
  }
}

// Show success notification
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
