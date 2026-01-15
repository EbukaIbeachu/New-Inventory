// Main JS file
$(document).ready(function() {
    // Initialize DataTables if table exists
    if ($('.datatable').length > 0) {
        $('.datatable').DataTable();
    }

    // Debounced search for DataTables
    if ($('.datatable').length > 0) {
        var dt = $('.datatable').DataTable();
        var searchTimeout;
        $('.datatable_filter input').off('keyup').on('keyup', function() {
            clearTimeout(searchTimeout);
            var val = this.value;
            searchTimeout = setTimeout(function() {
                dt.search(val).draw();
            }, 250);
        });
    }
    // Character counters for inputs with maxlength
    $(document).on('input', 'input[maxlength], textarea[maxlength]', function() {
        var $el = $(this);
        var max = $el.attr('maxlength');
        var $cc = $el.siblings('.char-count');
        if ($cc.length === 0) {
            $cc = $('<div class="char-count"></div>').insertAfter($el);
        }
        $cc.text($el.val().length + ' / ' + max + ' characters');
    });

    // UI preferences: table density, column visibility
    $('.datatable').each(function() {
        var tableId = $(this).attr('id') || 'datatable';
        // Density toggle
        var $densityBtn = $('<button type="button" class="btn btn-sm btn-outline-secondary ms-2">Density</button>');
        $(this).closest('.dataTables_wrapper').find('.dataTables_length').append($densityBtn);
        var dense = localStorage.getItem(tableId + '_dense') === '1';
        if (dense) $(this).addClass('table-sm');
        $densityBtn.on('click', function() {
            $(this).closest('table').toggleClass('table-sm');
            var isDense = $(this).closest('table').hasClass('table-sm');
            localStorage.setItem(tableId + '_dense', isDense ? '1' : '0');
        });
        // Column visibility
        var $colBtn = $('<button type="button" class="btn btn-sm btn-outline-secondary ms-2">Columns</button>');
        $(this).closest('.dataTables_wrapper').find('.dataTables_length').append($colBtn);
        $colBtn.on('click', function() {
            var $table = $(this).closest('.dataTables_wrapper').find('table');
            var $thead = $table.find('thead th');
            $thead.each(function(i) {
                if (i === 0) return; // always show first col
                var $th = $(this);
                var visible = !$table.find('tbody td:nth-child(' + (i+1) + ')').is(':hidden');
                var label = $th.text();
                var checked = visible ? 'checked' : '';
                var $cb = $('<div class="form-check"><input class="form-check-input" type="checkbox" '+checked+' data-col="'+i+'"><label class="form-check-label">'+label+'</label></div>');
                $cb.find('input').on('change', function() {
                    var colIdx = $(this).data('col');
                    $table.find('tr').each(function(){ $(this).find('th,td').eq(colIdx).toggle(); });
                });
                $cb.appendTo('#colvis-popover');
            });
            // Show popover
            var $popover = $('#colvis-popover');
            if ($popover.length === 0) {
                $popover = $('<div id="colvis-popover" class="popover bs-popover-bottom show" style="position:absolute;z-index:9999;"></div>').appendTo('body');
            }
            $popover.html('').append($cb);
            $popover.css({top: $colBtn.offset().top + $colBtn.outerHeight(), left: $colBtn.offset().left});
            $(document).one('click', function(){ $popover.remove(); });
        });
    });

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
    // Date helpers: min/max and mask
    $(document).on('input', 'input[type="date"]', function() {
        var $el = $(this);
        var min = $el.attr('min');
        var max = $el.attr('max');
        var val = $el.val();
        if (min && val < min) $el.val(min);
        if (max && val > max) $el.val(max);
    });

    // Keyboard shortcuts
    $(document).on('keydown', function(e) {
        if (e.target.tagName === 'INPUT' || e.target.tagName === 'TEXTAREA') return;
        if (e.key === 'n' || e.key === 'N') {
            var $add = $("a[href*='add_item.php']:visible").first();
            if ($add.length) { $add[0].click(); e.preventDefault(); }
        }
        if (e.key === '/') {
            var $search = $('.dataTables_filter input:visible').first();
            if ($search.length) { $search.focus(); e.preventDefault(); }
        }
    });

    // Async feedback: disable submit, show spinner
    $('form').on('submit', function() {
        var $btn = $(this).find('button[type="submit"]:visible').first();
        if ($btn.length) {
            $btn.prop('disabled', true);
            var $spinner = $('<span class="spinner-border spinner-border-sm ms-2" role="status" aria-hidden="true"></span>');
            $btn.append($spinner);
        }
    });

    // Draft autosave for receipt form
    var $receiptForm = $('#receiptForm');
    if ($receiptForm.length) {
        var key = 'draft_receipt';
        // Restore
        var saved = localStorage.getItem(key);
        if (saved) {
            try {
                var data = JSON.parse(saved);
                Object.keys(data).forEach(function(k){
                    $receiptForm.find('[name="'+k+'"], [name="'+k+'[]"]').each(function(i,el){
                        if (Array.isArray(data[k])) {
                            $(el).val(data[k][i] || '');
                        } else {
                            $(el).val(data[k]);
                        }
                    });
                });
            } catch(e){}
        }
        // Save on change
        $receiptForm.on('input change', function() {
            var formData = {};
            $receiptForm.serializeArray().forEach(function(f){
                if (formData[f.name]) {
                    if (!Array.isArray(formData[f.name])) formData[f.name] = [formData[f.name]];
                    formData[f.name].push(f.value);
                } else {
                    formData[f.name] = f.value;
                }
            });
            localStorage.setItem(key, JSON.stringify(formData));
        });
        // Clear on submit
        $receiptForm.on('submit', function(){ localStorage.removeItem(key); });
    }

    // Row expanders (for tables with .expand-row)
    $(document).on('click', '.expand-row', function() {
        var $row = $(this).closest('tr');
        $row.next('.row-details').toggle();
    });

    // Copy actions
    $(document).on('click', '.copy-btn', function() {
        var val = $(this).data('copy');
        if (val) {
            navigator.clipboard.writeText(val);
            var $btn = $(this);
            $btn.tooltip({title:'Copied!',trigger:'manual'}).tooltip('show');
            setTimeout(function(){ $btn.tooltip('hide'); }, 1000);
        }
    });

    // Print preview toggles
    $(document).on('click', '.toggle-print', function() {
        var target = $(this).data('target');
        $(target).toggleClass('no-print');
    });

    // Guided tour (Intro.js)
    function buildTourSteps() {
        var steps = [];
        if ($('#sidebar-wrapper').length) {
            steps.push({
                element: document.getElementById('sidebar-wrapper'),
                intro: 'Use this sidebar to navigate between Dashboard, Inventory, Receipts, Users and more.',
                position: 'right'
            });
        }
        if ($('#page-content-wrapper').length) {
            steps.push({
                element: document.getElementById('page-content-wrapper'),
                intro: 'This area shows the main content for the page you are on.',
                position: 'bottom'
            });
        }
        var $datatable = $('.datatable').first();
        if ($datatable.length) {
            steps.push({
                element: $datatable.get(0),
                intro: 'Tables support search, sorting and pagination. Use the toolbar above to filter and change density.',
                position: 'top'
            });
        }
        var $createReceipt = $("a[href*='create_receipt.php']").first();
        if ($createReceipt.length) {
            steps.push({
                element: $createReceipt.get(0),
                intro: 'Create a new receipt from here. Items and customers are recorded against each receipt.',
                position: 'bottom'
            });
        }
        var $viewReceipt = $("a[href*='view_receipt.php?id=']").first();
        if ($viewReceipt.length) {
            steps.push({
                element: $viewReceipt.get(0),
                intro: 'Click on a receipt to see full details, update status, and print.',
                position: 'bottom'
            });
        }
        return steps;
    }

    function startAppTour(force) {
        if (typeof introJs === 'undefined') return; // library not loaded
        if (!force && localStorage.getItem('inventory_tour_seen') === '1') return;
        var steps = buildTourSteps();
        if (!steps.length) return;
        var intro = introJs();
        intro.setOptions({
            steps: steps,
            showProgress: true,
            exitOnOverlayClick: true,
            nextLabel: 'Next',
            prevLabel: 'Back',
            skipLabel: 'Skip',
            doneLabel: 'Done'
        });
        intro.oncomplete(function(){ localStorage.setItem('inventory_tour_seen', '1'); });
        intro.onexit(function(){ localStorage.setItem('inventory_tour_seen', '1'); });
        intro.start();
    }

    // Start tour when user clicks the Tour button
    $(document).on('click', '#startTour', function() {
        // allow replay: ignore stored flag when user explicitly clicks
        startAppTour(true);
    });

    // Optionally run once for new users on dashboard
    if (window.location.pathname.indexOf('index.php') !== -1) {
        startAppTour(false);
    }
});
