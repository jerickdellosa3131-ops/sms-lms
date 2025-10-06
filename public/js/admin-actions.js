/**
 * Admin Actions JavaScript
 * Handles View, Edit, Delete, and Export functionality
 */

// View Record Modal
function viewRecord(id, type, data) {
    let content = '<div class="table-responsive"><table class="table table-borderless">';
    
    for (let key in data) {
        if (data.hasOwnProperty(key)) {
            let label = key.charAt(0).toUpperCase() + key.slice(1).replace(/_/g, ' ');
            content += `<tr>
                <td class="fw-bold" style="width: 150px;">${label}:</td>
                <td>${data[key]}</td>
            </tr>`;
        }
    }
    
    content += '</table></div>';
    
    // Create or update modal
    let modal = document.getElementById('viewRecordModal');
    if (!modal) {
        modal = document.createElement('div');
        modal.id = 'viewRecordModal';
        modal.className = 'modal fade';
        modal.innerHTML = `
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header bg-info text-white">
                        <h5 class="modal-title">
                            <i class="bi bi-eye me-2"></i>View ${type} Details
                        </h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body" id="viewRecordContent"></div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>`;
        document.body.appendChild(modal);
    }
    
    document.getElementById('viewRecordContent').innerHTML = content;
    new bootstrap.Modal(modal).show();
}

// Edit Record Modal
function editRecord(id, type, data) {
    let formFields = '';
    
    for (let key in data) {
        if (data.hasOwnProperty(key)) {
            let label = key.charAt(0).toUpperCase() + key.slice(1).replace(/_/g, ' ');
            formFields += `
                <div class="mb-3">
                    <label for="edit_${key}" class="form-label fw-bold">${label}</label>
                    <input type="text" class="form-control" id="edit_${key}" name="${key}" value="${data[key]}">
                </div>`;
        }
    }
    
    // Create or update modal
    let modal = document.getElementById('editRecordModal');
    if (!modal) {
        modal = document.createElement('div');
        modal.id = 'editRecordModal';
        modal.className = 'modal fade';
        modal.innerHTML = `
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header bg-warning">
                        <h5 class="modal-title">
                            <i class="bi bi-pencil me-2"></i>Edit ${type}
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <form id="editRecordForm">
                            <input type="hidden" id="edit_record_id" value="${id}">
                            <div id="editFormFields"></div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="button" class="btn btn-warning" onclick="saveEdit('${type}')">
                            <i class="bi bi-save me-2"></i>Save Changes
                        </button>
                    </div>
                </div>
            </div>`;
        document.body.appendChild(modal);
    }
    
    document.getElementById('editFormFields').innerHTML = formFields;
    new bootstrap.Modal(modal).show();
}

// Save Edit Function
function saveEdit(type) {
    const form = document.getElementById('editRecordForm');
    const formData = new FormData(form);
    const id = document.getElementById('edit_record_id').value;
    
    // Show loading
    Swal.fire({
        title: 'Saving...',
        text: 'Please wait while we save your changes',
        allowOutsideClick: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });
    
    // Simulate save (replace with actual AJAX call)
    setTimeout(() => {
        Swal.fire({
            icon: 'success',
            title: 'Saved!',
            text: `${type} has been updated successfully.`,
            timer: 2000,
            showConfirmButton: false
        }).then(() => {
            bootstrap.Modal.getInstance(document.getElementById('editRecordModal')).hide();
            // Reload page or update table
            location.reload();
        });
    }, 1000);
}

// Delete Record with Confirmation
function deleteRecord(id, type, name) {
    Swal.fire({
        title: 'Are you sure?',
        html: `You are about to delete <strong>${name}</strong>.<br>This action cannot be undone!`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#6c757d',
        confirmButtonText: '<i class="bi bi-trash me-2"></i>Yes, delete it!',
        cancelButtonText: '<i class="bi bi-x-circle me-2"></i>Cancel',
        reverseButtons: true
    }).then((result) => {
        if (result.isConfirmed) {
            // Show loading
            Swal.fire({
                title: 'Deleting...',
                text: 'Please wait',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });
            
            // Simulate delete (replace with actual AJAX call)
            setTimeout(() => {
                Swal.fire({
                    icon: 'success',
                    title: 'Deleted!',
                    text: `${type} has been deleted successfully.`,
                    timer: 2000,
                    showConfirmButton: false
                }).then(() => {
                    // Reload page or remove row
                    location.reload();
                });
            }, 1000);
        }
    });
}

// Export to Excel Function
function exportToExcel(data, filename = 'export') {
    if (typeof XLSX === 'undefined') {
        Swal.fire({
            icon: 'error',
            title: 'Export Error',
            text: 'Excel library not loaded. Please refresh the page.'
        });
        return;
    }
    
    const wb = XLSX.utils.book_new();
    const ws = XLSX.utils.json_to_sheet(data);
    
    // Auto-size columns
    const cols = [];
    const colWidths = {};
    
    data.forEach(row => {
        Object.keys(row).forEach(key => {
            const value = String(row[key] || '');
            colWidths[key] = Math.max(colWidths[key] || key.length, value.length);
        });
    });
    
    Object.keys(colWidths).forEach(key => {
        cols.push({ wch: Math.min(colWidths[key] + 2, 50) });
    });
    
    ws['!cols'] = cols;
    
    XLSX.utils.book_append_sheet(wb, ws, 'Data');
    XLSX.writeFile(wb, `${filename}_${new Date().toISOString().split('T')[0]}.xlsx`);
    
    Swal.fire({
        icon: 'success',
        title: 'Exported!',
        text: 'Data has been exported successfully.',
        timer: 2000,
        showConfirmButton: false
    });
}

// Export Current Table
function exportCurrentTable(tableId = 'table', filename = 'export') {
    const table = document.querySelector(`#${tableId}, table`);
    if (!table) {
        Swal.fire({
            icon: 'error',
            title: 'Export Error',
            text: 'Table not found'
        });
        return;
    }
    
    const data = [];
    const headers = [];
    
    // Get headers
    table.querySelectorAll('thead th').forEach(th => {
        const text = th.textContent.trim();
        if (text && text !== 'Actions' && text !== 'Action') {
            headers.push(text);
        }
    });
    
    // Get data rows
    table.querySelectorAll('tbody tr').forEach(tr => {
        const row = {};
        const cells = tr.querySelectorAll('td');
        
        headers.forEach((header, index) => {
            if (cells[index]) {
                row[header] = cells[index].textContent.trim();
            }
        });
        
        if (Object.keys(row).length > 0) {
            data.push(row);
        }
    });
    
    if (data.length === 0) {
        Swal.fire({
            icon: 'warning',
            title: 'No Data',
            text: 'No data available to export'
        });
        return;
    }
    
    exportToExcel(data, filename);
}

// Download File
function downloadFile(url, filename) {
    const link = document.createElement('a');
    link.href = url;
    link.download = filename || 'download';
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
    
    Swal.fire({
        icon: 'success',
        title: 'Download Started',
        text: 'Your file is being downloaded',
        timer: 2000,
        showConfirmButton: false
    });
}

// Initialize tooltips
document.addEventListener('DOMContentLoaded', function() {
    // Initialize Bootstrap tooltips
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[title]'));
    tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
});
