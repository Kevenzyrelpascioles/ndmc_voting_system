<?php
include('session.php');
include('header.php');
?>
</head>
<body>
<?php include('nav_top.php'); ?>

<div class="wrapper">
    <div class="container">
        <div class="row">
            <div class="span12">
                <h2>Reset All Voter Passwords</h2>
                <p class="alert alert-warning">
                    <strong>Warning:</strong> This will reset ALL voter passwords and set ALL voters to Unvoted status.
                    This operation cannot be undone.
                </p>
                
                <div id="progress-container" style="margin: 20px 0; display: none;">
                    <h4>Processing Passwords Reset:</h4>
                    <div class="progress progress-striped active">
                        <div id="progress-bar" class="bar" style="width: 0%;"></div>
                    </div>
                    <p id="progress-text">0% complete (0 of 0 voters processed)</p>
                </div>
                
                <div id="result-container" style="margin: 20px 0; display: none;">
                    <div id="result-message" class="alert alert-success"></div>
                    <a href="voter_list.php" class="btn btn-primary">Return to Voter List</a>
                </div>
                
                <div id="error-container" style="margin: 20px 0; display: none;">
                    <div id="error-message" class="alert alert-error"></div>
                    <a href="voter_list.php" class="btn btn-primary">Return to Voter List</a>
                </div>
                
                <div id="start-container">
                    <button id="start-reset" class="btn btn-large btn-warning">
                        <i class="icon-refresh icon-white"></i> Start Password Reset Process
                    </button>
                    <a href="voter_list.php" class="btn btn-large">Cancel</a>
                </div>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
$(document).ready(function() {
    const batchSize = 10; // Process 10 voters at a time
    let currentBatch = 0;
    let totalProcessed = 0;
    let totalVoters = 0;
    
    // Start batch processing
    $('#start-reset').click(function() {
        $('#start-container').hide();
        $('#progress-container').show();
        processBatch(0);
    });
    
    // Process a single batch of voters
    function processBatch(batch) {
        $.ajax({
            url: 'reset_single_batch.php',
            type: 'GET',
            data: {
                batch: batch,
                size: batchSize
            },
            dataType: 'json',
            success: function(response) {
                if (response.error) {
                    showError(response.error);
                    return;
                }
                
                // Update progress
                currentBatch = response.batch;
                totalProcessed = response.total_processed;
                totalVoters = response.total;
                
                updateProgress(response.progress, totalProcessed, totalVoters);
                
                // Process next batch or finish
                if (response.is_complete) {
                    processComplete(response.total_processed);
                } else {
                    // Process next batch with a small delay to avoid overloading the server
                    setTimeout(function() {
                        processBatch(currentBatch + 1);
                    }, 300);
                }
            },
            error: function(xhr, status, error) {
                showError('AJAX error: ' + error);
            }
        });
    }
    
    // Update progress bar and text
    function updateProgress(percentage, processed, total) {
        $('#progress-bar').css('width', percentage + '%');
        $('#progress-text').text(percentage + '% complete (' + processed + ' of ' + total + ' voters processed)');
    }
    
    // Show completion message
    function processComplete(total) {
        $('#progress-container').hide();
        $('#result-container').show();
        $('#result-message').html('<strong>Success!</strong> All ' + total + ' voter passwords have been reset and voters set to Unvoted status.');
    }
    
    // Show error message
    function showError(message) {
        $('#progress-container').hide();
        $('#error-container').show();
        $('#error-message').html('<strong>Error:</strong> ' + message);
    }
});
</script>

<?php include('footer.php'); ?>
</body>
</html> 