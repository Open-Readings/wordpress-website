document.addEventListener("DOMContentLoaded", function () {
    const scrollContainer = document.querySelector(".image-scroll-container");
    const scrollLeftBtn = document.querySelector(".scroll-left");
    const scrollRightBtn = document.querySelector(".scroll-right");
    const newsPost = document.querySelector(".news-post"); // Get one post to calculate size
    let autoScroll;
    let direction = 1;
    let autoScrollTimeout;

    function getScrollAmount() {
        const postWidth = newsPost.offsetWidth; // Get full width including padding/borders
        const gap = parseInt(getComputedStyle(scrollContainer).columnGap) || 0; // Check for CSS gap
        const containerPadding = parseInt(getComputedStyle(scrollContainer).paddingLeft) || 0;
        return postWidth + gap + containerPadding; // Adjusted for accurate scrolling
    }

    function startAutoScroll() {
        clearInterval(autoScroll);
        autoScroll = setInterval(() => {
            scrollImages();
        }, 8000);
    }

    function scrollImages() {
        const scrollAmount = getScrollAmount();
        scrollContainer.scrollBy({ left: scrollAmount * direction, behavior: "smooth" });

        // Loop back when reaching the end
        if (scrollContainer.scrollLeft + scrollContainer.clientWidth >= scrollContainer.scrollWidth) {
            scrollContainer.scrollTo({ left: 0, behavior: "smooth" });
        }
    }

    function stopAndRestartAutoScroll() {
        clearInterval(autoScroll);
        clearTimeout(autoScrollTimeout);
        autoScrollTimeout = setTimeout(startAutoScroll, 4000);
    }

    // Adjusted click events for dynamic scroll amount
    scrollLeftBtn.addEventListener("click", () => {
        scrollContainer.scrollBy({ left: -getScrollAmount(), behavior: "smooth" });
        stopAndRestartAutoScroll();
    });

    scrollRightBtn.addEventListener("click", () => {
        scrollContainer.scrollBy({ left: getScrollAmount(), behavior: "smooth" });
        stopAndRestartAutoScroll();
    });

    // Hide scrollbar
    scrollContainer.style.overflowX = "hidden"; //to show the scrollbar

    startAutoScroll();
});




document.addEventListener("DOMContentLoaded", function () {
    const scrollContainer = document.querySelector(".image-scroll-container");
    const scrollLeft = document.querySelector(".scroll-left");
    const scrollRight = document.querySelector(".scroll-right");

    scrollLeft.addEventListener("click", function () {
        scrollContainer.scrollBy({ left: -255, behavior: "smooth" });
    });

    scrollRight.addEventListener("click", function () {
        scrollContainer.scrollBy({ left: 255, behavior: "smooth" });
    });
});
