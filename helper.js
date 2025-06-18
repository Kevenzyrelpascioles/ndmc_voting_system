// Helper JS for NDMC Voting System Mobile Responsiveness

document.addEventListener('DOMContentLoaded', function() {
    // Adjust modals for mobile view
    const adjustModals = function() {
        const modals = document.querySelectorAll('.modal');
        const windowWidth = window.innerWidth;
        
        if (windowWidth < 768) {
            modals.forEach(function(modal) {
                // Center modals on small screens
                if (modal.style.marginLeft !== 'auto') {
                    modal.style.width = '90%';
                    modal.style.marginLeft = 'auto';
                    modal.style.marginRight = 'auto';
                    modal.style.left = '0';
                    modal.style.right = '0';
                }
            });
        }
    };

    // Adjust table layouts for mobile
    const adjustTables = function() {
        const tables = document.querySelectorAll('table');
        const windowWidth = window.innerWidth;
        
        if (windowWidth < 768) {
            tables.forEach(function(table) {
                table.classList.add('responsive-table');
                
                // Add data attributes to cells for responsive display
                const headerCells = table.querySelectorAll('thead th');
                const headerTexts = Array.from(headerCells).map(th => th.textContent.trim());
                
                const rows = table.querySelectorAll('tbody tr');
                rows.forEach(function(row) {
                    const cells = row.querySelectorAll('td');
                    cells.forEach(function(cell, index) {
                        if (headerTexts[index]) {
                            cell.setAttribute('data-label', headerTexts[index]);
                        }
                    });
                });
            });
        }
    };

    // Fix navbar for mobile
    const adjustNavbar = function() {
        const navbar = document.querySelector('.navbar-fixed-top');
        const windowWidth = window.innerWidth;
        
        if (navbar) {
            if (windowWidth < 768) {
                // On small screens, ensure navbar is visible but doesn't take up too much space
                navbar.style.position = 'fixed';
                navbar.style.top = '0';
                navbar.style.left = '0';
                navbar.style.right = '0';
                navbar.style.zIndex = '1030';
                document.body.style.paddingTop = (navbar.offsetHeight + 10) + 'px';
            } else {
                navbar.style.position = 'fixed';
                document.body.style.paddingTop = navbar.offsetHeight + 'px';
            }
        }
    };

    // Adjust ballot display for mobile
    const adjustBallot = function() {
        const ballot = document.querySelector('.ballot');
        const windowWidth = window.innerWidth;
        
        if (windowWidth < 768 && ballot) {
            ballot.style.width = '95%';
            ballot.style.margin = '0 auto';
            ballot.style.padding = '10px';
            
            const centElements = ballot.querySelectorAll('.cent');
            centElements.forEach(function(element) {
                element.style.display = 'flex';
                element.style.flexDirection = 'column';
                element.style.alignItems = 'center';
                element.style.textAlign = 'center';
                element.style.marginBottom = '15px';
            });
        }
    };
    
    // Create responsive candidate grid
    const createCandidateGrid = function() {
        const candidateSections = document.querySelectorAll('.row');
        const windowWidth = window.innerWidth;
        
        candidateSections.forEach(function(section) {
            if (section.querySelectorAll('img').length > 0) {
                // This is likely a candidate section
                section.classList.add('candidates-grid');
                
                const candidateItems = section.querySelectorAll('[class*="col-"]');
                candidateItems.forEach(function(item) {
                    item.classList.add('candidate-item');
                    
                    if (windowWidth < 480) {
                        // Make images smaller on very small screens
                        const img = item.querySelector('img');
                        if (img) {
                            img.style.maxWidth = '60px';
                            img.style.maxHeight = '60px';
                        }
                    }
                });
            }
        });
    };
    
    // Make buttons stack on mobile
    const adjustButtons = function() {
        const buttonGroups = document.querySelectorAll('.d-flex');
        const windowWidth = window.innerWidth;
        
        if (windowWidth < 768) {
            buttonGroups.forEach(function(group) {
                if (group.querySelectorAll('button, .btn').length > 1) {
                    group.classList.add('button-group');
                    group.classList.add('flex-column');
                    
                    const buttons = group.querySelectorAll('button, .btn');
                    buttons.forEach(function(button) {
                        button.style.marginBottom = '10px';
                        button.style.width = '100%';
                    });
                }
            });
        }
    };
    
    // Handle form elements responsively
    const adjustFormElements = function() {
        const inputs = document.querySelectorAll('input[type="text"], input[type="password"], select, textarea, .form-control');
        const windowWidth = window.innerWidth;
        
        if (windowWidth < 768) {
            inputs.forEach(function(input) {
                input.style.width = '100%';
                input.style.boxSizing = 'border-box';
                input.style.marginBottom = '10px';
            });
        }
    };

    // Set up responsive images
    const makeImagesResponsive = function() {
        const images = document.querySelectorAll('img');
        images.forEach(function(img) {
            if (!img.classList.contains('circle-logo')) {
                img.style.maxWidth = '100%';
                img.style.height = 'auto';
            }
        });
    };
    
    // Add touch-friendly interactions for mobile
    const addTouchInteractions = function() {
        // Increase touch target size for better mobile usability
        const smallButtons = document.querySelectorAll('.btn-sm, .btn-xs');
        smallButtons.forEach(function(button) {
            button.style.minHeight = '44px';
            button.style.minWidth = '44px';
            button.style.padding = '10px 15px';
        });
    };

    // Run all adjustments
    const runResponsiveAdjustments = function() {
        adjustModals();
        adjustTables();
        adjustNavbar();
        adjustBallot();
        createCandidateGrid();
        adjustButtons();
        adjustFormElements();
        makeImagesResponsive();
        addTouchInteractions();
    };

    // Run on load and resize
    runResponsiveAdjustments();
    
    // Debounce resize events for better performance
    let resizeTimer;
    window.addEventListener('resize', function() {
        clearTimeout(resizeTimer);
        resizeTimer = setTimeout(runResponsiveAdjustments, 250);
    });
    
    // Re-run if page content changes (for dynamic content)
    const observer = new MutationObserver(function(mutations) {
        runResponsiveAdjustments();
    });
    
    observer.observe(document.body, { 
        childList: true, 
        subtree: true 
    });
}); 