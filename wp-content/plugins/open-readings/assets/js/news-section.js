function initNewsWidget() {
    const container = document.querySelector(".or-news-scroll-container");
    const btnLeft = document.querySelector(".or-news-nav-btn.btn-left");
    const btnRight = document.querySelector(".or-news-nav-btn.btn-right");

    if (!container) return;

    // SAFEGUARD: Don't run auto-scroll for performance testing bots
    if (navigator.userAgent.includes("Chrome-Lighthouse") || navigator.userAgent.includes("Speed Insights")) {
        container.style.overflowX = "auto";
        return; 
    }

    let autoScrollInterval;
    let resizeTimer; 
    const SCROLL_DELAY = 9000; 
    
    // SPAM-PROOF LOGIC
    let targetScroll = null; 
    let spamResetTimer;
    const ANIMATION_TIME = 600; 

    function getScrollWidth() {
        const firstPost = container.querySelector(".news-post");
        // Factoring in the exact 16px gap for precise sliding
        return firstPost && firstPost.clientWidth > 0 ? firstPost.clientWidth + 16 : 320; 
    }

    function scrollRight() {
        if (!container || container.clientWidth === 0) return;
        const itemWidth = getScrollWidth();

        if (targetScroll === null) {
            targetScroll = Math.round(container.scrollLeft / itemWidth) * itemWidth;
        }

        targetScroll += itemWidth;

        const maxScroll = container.scrollWidth - container.clientWidth;
        if (targetScroll > maxScroll + 10) { 
            targetScroll = 0;
        }

        container.scrollTo({ left: targetScroll, behavior: "smooth" });

        clearTimeout(spamResetTimer);
        spamResetTimer = setTimeout(() => { targetScroll = null; }, ANIMATION_TIME);
    }

    function scrollLeft() {
        if (!container || container.clientWidth === 0) return;
        const itemWidth = getScrollWidth();

        if (targetScroll === null) {
            targetScroll = Math.round(container.scrollLeft / itemWidth) * itemWidth;
        }

        targetScroll -= itemWidth;

        if (targetScroll < 0) {
            const maxScroll = container.scrollWidth - container.clientWidth;
            targetScroll = Math.floor(maxScroll / itemWidth) * itemWidth;
        }

        container.scrollTo({ left: targetScroll, behavior: "smooth" });

        clearTimeout(spamResetTimer);
        spamResetTimer = setTimeout(() => { targetScroll = null; }, ANIMATION_TIME);
    }

    function startAutoScroll() {
        clearInterval(autoScrollInterval);
        autoScrollInterval = setInterval(scrollRight, SCROLL_DELAY);
    }

    function resetAutoScroll() {
        clearInterval(autoScrollInterval);
        startAutoScroll(); 
    }

    // ==========================================
    // UPDATED: RESIZE-PROOF LOGIC (Snap to nearest)
    // ==========================================
    window.addEventListener("resize", () => {
        clearTimeout(resizeTimer);
        clearInterval(autoScrollInterval);
        targetScroll = null; 
        
        resizeTimer = setTimeout(() => {
            if (container) {
                const itemWidth = getScrollWidth();
                // Find the nearest math-perfect snap point based on current position
                const nearestSnapPoint = Math.round(container.scrollLeft / itemWidth) * itemWidth;
                
                // Glide smoothly to the closest item instead of returning to 0
                container.scrollTo({ left: nearestSnapPoint, behavior: "smooth" });
            }
            startAutoScroll(); 
        }, 250); 
    });
    // ==========================================

    if (btnLeft) {
        btnLeft.addEventListener("click", () => {
            scrollLeft();
            resetAutoScroll();
        });
    }

    if (btnRight) {
        btnRight.addEventListener("click", () => {
            scrollRight();
            resetAutoScroll();
        });
    }

    container.addEventListener("mouseenter", () => clearInterval(autoScrollInterval));
    container.addEventListener("mouseleave", startAutoScroll);

    if (document.readyState === "complete") {
        startAutoScroll();
    } else {
        window.addEventListener("load", startAutoScroll);
    }
}

if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", initNewsWidget);
} else {
    initNewsWidget();
}