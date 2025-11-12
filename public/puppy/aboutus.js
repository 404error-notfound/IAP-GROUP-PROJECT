let slideIndex = 1; // Tracks the current slide number (1, 2, 3, 4...)
showSlides(slideIndex);
// --- Initialization and Manual Click Logic ---

// Function to handle showing a specific slide (used for dot clicks)
function currentSlide(n) {
    showSlides(slideIndex = n);
}

// Function that handles the logic for displaying the correct slide and dot
function showSlides(n) {
    const slides = document.querySelectorAll(".slide");
    const dots = document.querySelectorAll(".dot");
    let i;

    // Handle wrap-around (if n is greater than total slides, reset to 1)
    if (n > slides.length) {
        slideIndex = 1;
    }
    // Handle wrap-around (if n is less than 1, go to the last slide)
    if (n < 1) {
        slideIndex = slides.length;
    }

    // Hide all slides
    for (i = 0; i < slides.length; i++) {
        slides[i].style.display = "none";
    }

    // Remove 'active' class from all dots
    for (i = 0; i < dots.length; i++) {
        dots[i].classList.remove("active");
    }

    // Display the current slide (using slideIndex - 1 for zero-based array access)
    if (slides.length > 0) {
        slides[slideIndex - 1].style.display = "block";
    }
    
    // Add 'active' class to the current dot
    if (dots.length > 0) {
        dots[slideIndex - 1].classList.add("active");
    }
}


// --- Automatic Rotation Logic ---

// Start the automatic rotation
autoShowSlides();

function autoShowSlides() {
    // Increment slideIndex and call the display logic (showSlides)
    slideIndex++;
    
    // Check for wrap-around BEFORE calling showSlides to manage the index correctly
    const slides = document.querySelectorAll(".slide");
    if (slideIndex > slides.length) {
        slideIndex = 1;
    }

    showSlides(slideIndex);
    
    // Schedule the next slide change
    setTimeout(autoShowSlides, 5000);
}