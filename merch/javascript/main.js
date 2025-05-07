(function ($) {
    "use strict";
    
    // Dropdown on mouse hover
    $(document).ready(function () {
        function toggleNavbarMethod() {
            if ($(window).width() > 992) {
                $('.navbar .dropdown').on('mouseover', function () {
                    $('.dropdown-toggle', this).trigger('click');
                }).on('mouseout', function () {
                    $('.dropdown-toggle', this).trigger('click').blur();
                });
            } else {
                $('.navbar .dropdown').off('mouseover').off('mouseout');
            }
        }
        toggleNavbarMethod();
        $(window).resize(toggleNavbarMethod);
    });
    
    
    // Back to top button
    $(window).scroll(function () {
        if ($(this).scrollTop() > 100) {
            $('.back-to-top').fadeIn('slow');
        } else {
            $('.back-to-top').fadeOut('slow');
        }
    });
    $('.back-to-top').click(function () {
        $('html, body').animate({scrollTop: 0}, 1500, 'easeInOutExpo');
        return false;
    });


    // Vendor carousel
    $('.vendor-carousel').owlCarousel({
        loop: true,
        margin: 29,
        nav: false,
        autoplay: true,
        smartSpeed: 1000,
        responsive: {
            0:{
                items:2
            }
        }
    });


    // Related carousel
    $('.related-carousel').owlCarousel({
        loop: false,
        margin: 29,
        nav: false,
        autoplay: false,
        smartSpeed: 1000000,
        responsive: {
            0:{
                items:1
            },
            576:{
                items:2
            },
            768:{
                items:3
            },
            992:{
                items:4
            }
        }
    });


    // Product Quantity
    $('.quantity button').on('click', function () {
        var button = $(this);
        var oldValue = button.parent().parent().find('input').val();
        if (button.hasClass('btn-plus')) {
            var newVal = parseFloat(oldValue) + 1;
        } else {
            if (oldValue > 0) {
                var newVal = parseFloat(oldValue) - 1;
            } else {
                newVal = 0;
            }
        }
        button.parent().parent().find('input').val(newVal);
    });
    
})(jQuery);

const openFilterButton = document.getElementById('open-filter-button');
        const closeFilterButton = document.getElementById('close-filter-button');
        const filterPanelContainer = document.getElementById('filter-panel-container');
        const filterOverlay = document.getElementById('filter-overlay');

        function openFilterPanel() {
            if (filterPanelContainer && filterOverlay) {
                filterPanelContainer.classList.add('open');
                filterOverlay.classList.add('open');
                document.body.style.overflow = 'hidden'; // Prevent scrolling background
            }
        }

        function closeFilterPanel() {
             if (filterPanelContainer && filterOverlay) {
                filterPanelContainer.classList.remove('open');
                filterOverlay.classList.remove('open');
                document.body.style.overflow = ''; // Restore scrolling
            }
        }

        if (openFilterButton) {
            openFilterButton.addEventListener('click', openFilterPanel);
        }
        if (closeFilterButton) {
            closeFilterButton.addEventListener('click', closeFilterPanel);
        }
        if (filterOverlay) {
            // Close panel if overlay is clicked
            filterOverlay.addEventListener('click', closeFilterPanel);
        }

        // --- Filter Component JavaScript (Adapted for Panel) ---
        const priceSliderElement = document.getElementById('price-slider');
        const priceFromInput = document.getElementById('price-from');
        const priceToInput = document.getElementById('price-to');
        const resetPriceLink = document.getElementById('reset-price');

        // --- noUiSlider Initialization ---
        const priceMin = 0; // Adjust if needed
        const priceMax = 100; // Adjust based on actual max price or image ($79.92)

        const initialFrom = 15; // Match image example ($15.39)
        const initialTo = 80; // Match image example ($79.92)

        if (priceSliderElement) {
            noUiSlider.create(priceSliderElement, {
                start: [initialFrom, initialTo],
                connect: true,
                range: { 'min': priceMin, 'max': priceMax },
                step: 1, // Or use decimals like 0.01 for cents
                format: { // Updated format for potential decimals
                    to: value => '$' + value.toFixed(2), // Show two decimal places
                    from: value => Number(value.replace('$', ''))
                }
            });

            // --- Sync Slider with Inputs ---
            priceSliderElement.noUiSlider.on('update', function (values, handle) {
                if(priceFromInput) priceFromInput.value = values[0];
                if(priceToInput) priceToInput.value = values[1];
            });

            // --- Sync Inputs with Slider ---
            function setSliderHandle(i, value) {
                var arr = [null, null];
                arr[i] = value;
                const numericValue = Number(String(value).replace(/[^0-9.]/g, '')); // Allow dot
                if (!isNaN(numericValue)) {
                     priceSliderElement.noUiSlider.set(arr);
                }
            }

            if(priceFromInput) {
                priceFromInput.addEventListener('change', function () { setSliderHandle(0, this.value); });
            }
            if(priceToInput) {
                priceToInput.addEventListener('change', function () { setSliderHandle(1, this.value); });
            }

             // --- Reset Price ---
             if(resetPriceLink) {
                 resetPriceLink.addEventListener('click', (event) => {
                    event.preventDefault();
                    priceSliderElement.noUiSlider.set([priceMin, priceMax]);
                 });
             }
        }

        // --- Reset/Clear All Logic ---
        function clearAllFiltersAction() {
             const allCheckboxes = filterPanelContainer ? filterPanelContainer.querySelectorAll('input[type="checkbox"]') : [];
             allCheckboxes.forEach(cb => cb.checked = false);
             if (priceSliderElement && priceSliderElement.noUiSlider) {
                priceSliderElement.noUiSlider.set([priceMin, priceMax]);
             }
             console.log('Clearing all filters');
             // Add logic here to actually re-apply filters (e.g., update product grid)
             // Optionally close the panel after clearing
             // closeFilterPanel();
        }

        // Clear button inside the panel
        const clearAllButtonPanel = document.getElementById('clear-all-filters-panel');
        if (clearAllButtonPanel) {
            clearAllButtonPanel.addEventListener('click', clearAllFiltersAction);
        }
        // Clear button on the filter bar
        const clearAllButtonBar = document.getElementById('clear-all-filters-bar');
         if (clearAllButtonBar) {
            clearAllButtonBar.addEventListener('click', clearAllFiltersAction);
        }


        const resetLinks = document.querySelectorAll('#filter-panel-container .reset-link:not(#reset-price)');
        resetLinks.forEach(link => {
            link.addEventListener('click', (event) => {
                event.preventDefault();
                const section = link.closest('.filter-section');
                if (section) {
                    const checkboxes = section.querySelectorAll('input[type="checkbox"]');
                    checkboxes.forEach(cb => cb.checked = false);
                    console.log(`Resetting section filters`);
                    // Add logic here to actually re-apply filters
                }
            });
        });

        // Make labels click checkboxes
        const labels = document.querySelectorAll('#filter-panel-container .filter-option-label');
        labels.forEach(label => {
            label.addEventListener('click', () => {
                const checkboxId = label.getAttribute('for');
                if (checkboxId) {
                    const checkbox = document.getElementById(checkboxId);
                    if (checkbox) {
                        checkbox.checked = !checkbox.checked;
                        checkbox.dispatchEvent(new Event('change'));
                    }
                }
            });
        });

        // --- Placeholder: Add actual filtering logic here ---
        function applyFilters() {
            console.log("Applying filters...");
            // 1. Read filter values
            // 2. Update product grid
            // 3. Update active filter tags display in the filter bar
            // 4. Optionally close the panel: closeFilterPanel();
        }

        // Trigger filtering when filters change
        const filterInputs = filterPanelContainer ? filterPanelContainer.querySelectorAll('input[type="checkbox"]') : [];
        filterInputs.forEach(input => input.addEventListener('change', applyFilters));
        if (priceSliderElement && priceSliderElement.noUiSlider) {
             priceSliderElement.noUiSlider.on('change', applyFilters); // Filter when slider interaction ends
        }
        // Also trigger applyFilters when 'Show Results' is clicked
        const showResultsButton = document.querySelector('.show-results-button');
        if(showResultsButton) {
            showResultsButton.addEventListener('click', applyFilters);
        }

        const imageElements = document.querySelectorAll('.hover-effect-image');

        // Function to handle mouseover
        function handleMouseOver(event) {
            const image = event.target; // Get the specific image being hovered
            const hoverSrc = image.dataset.hoverSrc; // Get its hover source
            if (hoverSrc) { // Check if hover source exists
                 image.src = hoverSrc;
            }
        }

        // Function to handle mouseout
        function handleMouseOut(event) {
            const image = event.target; // Get the specific image
            const originalSrc = image.dataset.originalSrc; // Get its original source
             if (originalSrc) { // Check if original source exists
                image.src = originalSrc;
             }
        }

        // Preload images and add event listeners to each image
        imageElements.forEach(imageElement => {
            // Preload the hover image for this specific element
            const hoverSrc = imageElement.dataset.hoverSrc;
            if (hoverSrc) {
                const preloadImage = new Image();
                preloadImage.src = hoverSrc;
            }

            // Add event listener for mouse entering the image area
            imageElement.addEventListener('mouseover', handleMouseOver);

            // Add event listener for mouse leaving the image area
            imageElement.addEventListener('mouseout', handleMouseOut);
        });
