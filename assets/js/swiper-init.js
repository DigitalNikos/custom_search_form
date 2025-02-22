document.addEventListener("DOMContentLoaded", function () {
    var swiperInstance = null;

    function initSwiper() {
        if (window.innerWidth < 768) {
            if (!swiperInstance) {
                console.log("📱 Initializing Swiper for mobile.");
                swiperInstance = new Swiper('.random-suggested-properties', {
                    slidesPerView: 1,
                    spaceBetween: 10,
                    pagination: {
                        el: '.swiper-pagination',
                        clickable: true,
                    },
                    speed: 500
                });
            }
        } else {
            if (swiperInstance) {
                console.log("💻 Destroying Swiper for desktop.");
                swiperInstance.destroy(true, true);
                swiperInstance = null;
            }
        }
    }

    // Initialize on page load
    initSwiper();

    // Reinitialize on window resize
    window.addEventListener("resize", initSwiper);
});
