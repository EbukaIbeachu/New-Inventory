// Main JS file
$(document).ready(function() {
    // Initialize DataTables if table exists
    if ($('.datatable').length > 0) {
        $('.datatable').DataTable();
    }

    // Money helpers
    function sanitizeMoney(val) {
        if (typeof val !== 'string') val = String(val || '');
        return val.replace(/[^0-9.]/g, '');
    }
    function formatMoney(val) {
        const num = parseFloat(sanitizeMoney(val));
        if (isNaN(num)) return '';
        return num.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }

    // Format on blur; keep clean numeric while submitting
    $(document).on('blur', 'input.money', function() {
        const v = $(this).val();
        const f = formatMoney(v);
        if (f !== '') $(this).val(f);
    });
    $(document).on('input', 'input.money', function() {
        // strip invalid characters live
        const v = sanitizeMoney($(this).val());
        $(this).val(v);
    });

    // Before submit: strip formatting so backend receives plain number
    $('form').on('submit', function() {
        $(this).find('input.money').each(function() {
            const clean = sanitizeMoney($(this).val());
            $(this).val(clean);
        });
    });

    // Inline required validation
    function validateRequired($el) {
        const v = ($el.val() || '').toString().trim();
        if (v === '') {
            $el.addClass('is-invalid');
            const fb = $el.siblings('.invalid-feedback');
            if (fb.length) fb.text('This field is required.');
            return false;
        } else {
            $el.removeClass('is-invalid');
            return true;
        }
    }
    $(document).on('blur', '[data-validate="required"]', function() {
        validateRequired($(this));
    });
    $('form').on('submit', function(e) {
        let ok = true;
        $(this).find('[data-validate="required"]').each(function(){ if(!validateRequired($(this))) ok = false; });
        if (!ok) {
            // prevent submission and focus first invalid
            e.preventDefault();
            $(this).find('.is-invalid').first().focus();
        }
    });
});
