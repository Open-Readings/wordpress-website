function initNewsWidget() {
    // 1. Grab the container and buttons (supports both sets of class names you used)
    const scrollContainer = document.querySelector(".image-scroll-container");
    const scrollLeftBtn = document.querySelector(".left-button") || document.querySelector(".scroll-left");
    const scrollRightBtn = document.querySelector(".or-right") || document.querySelector(".scroll-right");

    // 2. Safety Check: If the container isn't loaded yet, wait 100ms and try again
    if (!scrollContainer) {
        setTimeout(initNewsWidget, 100);
        return;
    }

    // 3. Centralized Sizing Logic (Runs on load AND on resize)
    function updateSizes() {
        let viewportWidth = window.innerWidth;
        let width;

        // Your exact size math
        if (viewportWidth < 768) {
            width = scrollContainer.clientWidth - 10;
        } else if (viewportWidth >= 768 && viewportWidth < 1024) {
            width = (scrollContainer.clientWidth - 20) / 2;
        } else {
            width = (scrollContainer.clientWidth - 30) / 3;
        }

        // Apply to posts
        document.querySelectorAll(".news-post").forEach((post) => {
            post.style.width = `${width}px`;
            post.style.height = `${width / 2 + 220}px`;
        });

        // Apply to image backgrounds
        document.querySelectorAll(".news-image-background").forEach((bg) => {
            bg.style.height = `${width / 1.92}px`;
        });
    }

    // Run the sizing logic immediately, and attach it to the window resize event
    updateSizes();
    window.addEventListener("resize", () => {
        updateSizes();
        scrollContainer.scrollTo({ left: 0, behavior: "smooth" }); // Reset scroll position on resize
    });

    // 4. Auto-Scroll Logic
    let autoScroll;
    let autoScrollTimeout;

    function getScrollAmount() {
        const newsPost = document.querySelector(".news-post");
        if (!newsPost) return 255; // Safe fallback if no posts exist yet
        const postWidth = newsPost.offsetWidth;
        const gap = parseInt(window.getComputedStyle(scrollContainer).columnGap) || 0;
        const containerPadding = parseInt(window.getComputedStyle(scrollContainer).paddingLeft) || 0;
        return postWidth + gap + containerPadding + 10;
    }

    function scrollImages() {
        scrollContainer.scrollBy({ left: getScrollAmount(), behavior: "smooth" });

        // Loop back when reaching the end
        if (scrollContainer.scrollLeft + scrollContainer.clientWidth >= scrollContainer.scrollWidth - 10) {
            scrollContainer.scrollTo({ left: 0, behavior: "smooth" });
        }
    }

    function startAutoScroll() {
        clearInterval(autoScroll);
        autoScroll = setInterval(scrollImages, 25000);
    }

    function stopAndRestartAutoScroll() {
        clearInterval(autoScroll);
        clearTimeout(autoScrollTimeout);
        autoScrollTimeout = setTimeout(startAutoScroll, 4000); // Pause for 4 seconds after user clicks
    }

    // 5. Button Click Events (Only attaches if the buttons actually exist)
    if (scrollLeftBtn) {
        scrollLeftBtn.addEventListener("click", () => {
            scrollContainer.scrollBy({ left: -getScrollAmount(), behavior: "smooth" });
            stopAndRestartAutoScroll();
        });
    }

    if (scrollRightBtn) {
        scrollRightBtn.addEventListener("click", () => {
            scrollContainer.scrollBy({ left: getScrollAmount(), behavior: "smooth" });
            stopAndRestartAutoScroll();
        });
    }

    // Hide scrollbar and start the timer
    scrollContainer.style.overflowX = "hidden";
    startAutoScroll();
}

// Start the entire process as soon as the DOM is ready
document.addEventListener("DOMContentLoaded", initNewsWidget);