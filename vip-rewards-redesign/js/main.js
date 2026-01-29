document.addEventListener('DOMContentLoaded', () => {
    //Hero image animation
    const slider = document.querySelector('.hero_img-carousel');
    const viewport = slider.querySelector('.hero_img-carousel-viewport');
    const imageWrapper = viewport.querySelector('.custom-slider__image');

    let direction = -1;
    let position = 0;
    const speed = 0.5;
    let maxScroll = 0;
    let paused = false;

    function updateMaxScroll() {
        const sliderHeight = slider.offsetHeight;
        const imageHeight = imageWrapper.offsetHeight;
        maxScroll = imageHeight - sliderHeight;

        if (maxScroll <= 0) {
            setTimeout(updateMaxScroll, 100);
        }
    }

    function animate() {
        if (!paused) {
            position += direction * speed;

            if (position < -maxScroll) {
                position = -maxScroll;
                direction = 1;
            } else if (position > 0) {
                position = 0;
                direction = -1;
            }

            imageWrapper.style.transform = `translateY(${position}px)`;
        }

        requestAnimationFrame(animate);
    }

    function initScroll() {
        updateMaxScroll();
        requestAnimationFrame(animate);
    }

    slider.addEventListener('mouseenter', () => {
        paused = false;
    });

    slider.addEventListener('mouseleave', () => {
        paused = false;
    });

    window.addEventListener('load', () => {
        initScroll();
    });

    window.addEventListener('resize', updateMaxScroll);

    //Slider
    let networkSlider = null;
    function initNetworkSwiper() {
        if (window.innerWidth < 1280) {
            if (!networkSlider) {
                networkSlider = new Swiper('.why-swiper', {
                    spaceBetween: 20,
                    pagination: {
                        el: ".swiper-pagination",
                        clickable: true,
                    },
                    breakpoints: {
                        768: {
                            slidesPerView: 2,
                        },
                        1024: {
                            slidesPerView: 3,
                        },
                    },
                });
            }
        } else {
            if (networkSlider) {
                networkSlider.destroy(true, true);
                networkSlider = null;
            }
        }
    }
    window.addEventListener('DOMContentLoaded', initNetworkSwiper);
    window.addEventListener('resize', initNetworkSwiper);

    let infoSlider = null;
    function initInfoSwiper() {
        if (window.innerWidth < 1024) {
            if (!infoSlider) {
                infoSlider = new Swiper('.info-three-in-row__slider', {
                    spaceBetween: 20,
                    pagination: {
                        el: ".swiper-pagination",
                        clickable: true,
                    },
                    breakpoints: {
                        768: {
                            slidesPerView: 2,
                        },
                    },
                });
            }
        } else {
            if (infoSlider) {
                infoSlider.destroy(true, true);
                infoSlider = null;
            }
        }
    }
    window.addEventListener('DOMContentLoaded', initInfoSwiper);
    window.addEventListener('resize', initInfoSwiper);
});
