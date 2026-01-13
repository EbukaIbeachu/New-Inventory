// Main JS file
$(document).ready(function() {
    // Initialize DataTables if table exists
    if ($('.datatable').length > 0) {
        $('.datatable').DataTable();
    }
});
