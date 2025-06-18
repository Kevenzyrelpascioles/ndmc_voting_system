<?php
// This file contains scripts that should only be loaded on pages with DataTables.
?>
<script type="text/javascript" charset="utf-8">
    jQuery(document).ready(function() {
        // Set error handling mode to avoid popup errors
        $.fn.dataTableExt.sErrMode = 'throw';
        
        // Initialize all tables with proper settings
        try {
            if ($.fn.dataTable.isDataTable('#log')) {
                // Destroy existing initialization if present
                $('#log').DataTable().destroy();
            }
            oTable = jQuery('#log').dataTable({
                "bJQueryUI": true,
                "sPaginationType": "full_numbers",
                "bAutoWidth": false,
                "aaSorting": []
            });
            
            oTable = jQuery('#attendance').dataTable({
                "bJQueryUI": true,
                "sPaginationType": "full_numbers"
            });
            oTable = jQuery('#record').dataTable({
                "bJQueryUI": true,
                "sPaginationType": "full_numbers"
            });
            oTable = jQuery('#cadet_list').dataTable({
                "bJQueryUI": true,
                "sPaginationType": "full_numbers"
            });
            oTable = jQuery('#passed').dataTable({
                "bJQueryUI": true,
                "sPaginationType": "full_numbers"
            });								
        } catch(e) {
            console.log('DataTable error handled: ' + e);
        }
    });		
</script> 