document.addEventListener("DOMContentLoaded", function () {
    let viewportWidth = window.innerWidth;
    let scrollContainer = document.querySelector(".image-scroll-container");
    const scrollLeftBtn = document.querySelector(".left-button");
    const scrollRightBtn = document.querySelector(".or-right");
    let newsPosts = document.querySelectorAll(".news-post"); // Get one post to calculate size
    newsPosts.forEach((post) => {
        if (viewportWidth < 768) {
            post.style.width = `${(scrollContainer.clientWidth - 20)}px`; // Set width to match container
        } else if (viewportWidth >= 768 && viewportWidth < 1024) {
            post.style.width = `${(scrollContainer.clientWidth - 30)/2}px`; // Set width to match container
        } else {
            post.style.width = `${(scrollContainer.clientWidth - 40)/3}px`; // Set width to match container       
        }
    });
    let newsPost = document.querySelector(".news-post");

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

window.addEventListener("resize", function () {
    viewportWidth = window.innerWidth;
    scrollContainer = document.querySelector(".image-scroll-container");
    newsPosts = document.querySelectorAll(".news-post");
    newsPosts.forEach((post) => {
        if (viewportWidth < 768) {
            post.style.width = `${(scrollContainer.clientWidth - 20)}px`; // Set width to match container
        } else if (viewportWidth >= 768 && viewportWidth < 1024) {
            post.style.width = `${(scrollContainer.clientWidth - 30)/2}px`; // Set width to match container
        } else {
            post.style.width = `${(scrollContainer.clientWidth - 40)/3}px`; // Set width to match container       
        }
    });
    scrollContainer.scrollTo({ left: 0, behavior: "smooth" }); // Reset scroll position
});
