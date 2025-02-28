document.addEventListener("DOMContentLoaded", function () {
    const scrollContainer = document.querySelector(".image-scroll-container");
    const scrollLeftBtn = document.querySelector(".scroll-left");
    const scrollRightBtn = document.querySelector(".scroll-right");
    const imageWidth = scrollContainer.querySelector("img").clientWidth + 10; // Adjust for margin
    let autoScroll; // Stores interval ID
    let direction = 1; // 1 = right, -1 = left
    let autoScrollTimeout; // Timeout to restart auto-scroll

    function startAutoScroll() {
        clearInterval(autoScroll); // Clear previous auto-scroll
        autoScroll = setInterval(() => {
            scrollImages();
        }, 7000); // Adjust timing for wave effect
    }

    function scrollImages() {
        scrollContainer.scrollBy({ left: imageWidth * direction, behavior: "smooth" });

        // If reached the end, loop back to start
        if (scrollContainer.scrollLeft + scrollContainer.clientWidth >= scrollContainer.scrollWidth) {
            scrollContainer.scrollTo({ left: 0, behavior: "smooth" });
        }
    }

    function stopAndRestartAutoScroll() {
        clearInterval(autoScroll); // Stop auto-scroll
        clearTimeout(autoScrollTimeout); // Clear existing restart timeout
        autoScrollTimeout = setTimeout(startAutoScroll, 5000); // Restart auto-scroll after delay
    }

    // Scroll left on button click, then stop auto-scroll and restart later
    scrollLeftBtn.addEventListener("click", () => {
        scrollContainer.scrollBy({ left: -imageWidth, behavior: "smooth" });
        stopAndRestartAutoScroll();
    });

    // Scroll right on button click, then stop auto-scroll and restart later
    scrollRightBtn.addEventListener("click", () => {
        scrollContainer.scrollBy({ left: imageWidth, behavior: "smooth" });
        stopAndRestartAutoScroll();
    });

    // Hide scrollbar with CSS (modify styles dynamically if needed)
    scrollContainer.style.overflowX = "shown"; // To hide scrollbar

    /* To unhide the scrollbar, comment the line above and enable the one below:
    scrollContainer.style.overflowX = "auto";
    */

    startAutoScroll(); // Start auto-scrolling initially
});



document.addEventListener("DOMContentLoaded", function () {
    const scrollContainer = document.querySelector(".image-scroll-container");
    const scrollLeft = document.querySelector(".scroll-left");
    const scrollRight = document.querySelector(".scroll-right");

    scrollLeft.addEventListener("click", function () {
        scrollContainer.scrollBy({ left: -300, behavior: "smooth" });
    });

    scrollRight.addEventListener("click", function () {
        scrollContainer.scrollBy({ left: 300, behavior: "smooth" });
    });
});
