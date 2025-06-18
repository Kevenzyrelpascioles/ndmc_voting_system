// Fix for DataTables warning error
// This overrides the default DataTable initialization for tables
$.fn.dataTableExt.sErrMode = 'throw';

$(document).ready(function() {
    // Re-initialize the datatable with proper settings
    try {
        if ($.fn.dataTable.isDataTable('#log')) {
            // Destroy existing initialization if present
            $('#log').DataTable().destroy();
        }
        
        // Initialize with correct settings and improved styling
        $('#log').dataTable({
            "bJQueryUI": true,
            "sPaginationType": "full_numbers",
            "bAutoWidth": false,
            "aaSorting": [],
            "bProcessing": true,
            "oLanguage": {
                "sEmptyTable": "No data available",
                "sInfo": "Showing _START_ to _END_ of _TOTAL_ entries",
                "sInfoEmpty": "Showing 0 to 0 of 0 entries",
                "sSearch": "Search:"
            },
            "fnDrawCallback": function() {
                // Apply custom styling after each table draw
                $('.dataTables_wrapper thead th').css({
                    'background-color': '#0066cc',
                    'color': 'white',
                    'border': '1px solid #004080',
                    'font-weight': 'bold'
                });
                
                $('.dataTables_wrapper tbody td').css({
                    'color': 'black',
                    'background-color': 'white',
                    'border': '1px solid #ddd'
                });
                
                $('.dataTables_wrapper tbody tr:nth-child(even) td').css({
                    'background-color': '#f2f2f2'
                });
                
                // Style row hover
                $('.dataTables_wrapper tbody tr').hover(
                    function() { 
                        $(this).find('td').css('background-color', '#e6f0ff');
                    },
                    function() {
                        const isEven = $(this).index() % 2 === 1;
                        const bgColor = isEven ? '#f2f2f2' : 'white';
                        $(this).find('td').css('background-color', bgColor);
                    }
                );
                
                // Fix position headers background
                $('.position-header td').css({
                    'background-color': '#4a86e8',
                    'color': 'white',
                    'font-weight': 'bold'
                });
                
                // Fix pagination controls
                $('.dataTables_paginate .ui-button').css({
                    'background-color': '#f9f9f9',
                    'color': '#333',
                    'border': '1px solid #ddd'
                });
            }
        });
        
        // Ensure the table header info is readable
        $('.dataTables_filter, .dataTables_length').css('color', '#333');
    } catch(e) {
        console.log('DataTable error handled: ' + e);
    }
});

// Add the script to the header.php to ensure it's loaded
if (typeof includeDataTablesFix === 'undefined') {
    includeDataTablesFix = true;
    $('head').append('<script type="text/javascript" src="js/dataTables_fix.js"></script>');
} 