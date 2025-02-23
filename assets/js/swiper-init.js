(function($, window, document, undefined) {
    'use strict';

    console.log("Swiper init script loaded!!!!!!!!!!!!!!!!!.");
    var swiperInstance = null;

    function initSwiper() {
        // Check if the Swiper container exists
        var $container = $('.random-suggested-properties');
        if ($container.length === 0) {
            // Container not found, so do nothing.
            console.log("Swiper container not found.");
            return;
        }

        var windowWidth = $(window).width();

        if (windowWidth < 768) {
            if (!swiperInstance) {
                console.log("Initializing Swiper for mobile.");
                swiperInstance = new Swiper($container[0], {
                    slidesPerView: 1,
                    spaceBetween: 20,
                    pagination: {
                        el: '.swiper-pagination',
                        clickable: true,
                    },
                    speed: 500
                });
            }
        } else {
            if (swiperInstance && typeof swiperInstance.destroy === 'function') {
                console.log("Destroying Swiper for desktop.");
                try {
                    swiperInstance.destroy();
                } catch (e) {
                    console.error("Error destroying Swiper:", e);
                }
                swiperInstance = null;
            }
        }
    }

    
    $(document).on('click', '.random-property-result-item', function() {
        var link = $(this).data('link');
        if (link) {
          window.location.href = link;
        }
      });
    

    // Initialize on load.
    initSwiper();

    // Re-check on window resize.
    $(window).on('resize', initSwiper);
})(jQuery, window, document);
