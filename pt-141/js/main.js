document.addEventListener('DOMContentLoaded', () => {
    //Hero image animation
    const slider = document.querySelector('.hero_img-carousel');
    const viewport = slider.querySelector('.hero_img-carousel-viewport');
    const imageWrapper = viewport.querySelector('.custom-slider__image');

    let direction = -1;
    let position = 0;
    const speed = 0.25;
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

    //Sliders
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

    //Accordion
    const accordion = document.querySelector('.accordion');

    function open(panel, item){
        item.classList.add('is-open');
        panel.style.height = panel.getBoundingClientRect().height + 'px';
        requestAnimationFrame(() => {
            panel.style.height = panel.scrollHeight + 'px';
        });
        const onEnd = (e) => {
            if (e.propertyName !== 'height') return;
            panel.removeEventListener('transitionend', onEnd);
            panel.style.height = 'auto';
        };
        panel.addEventListener('transitionend', onEnd);
    }

    function close(panel, item){
        item.classList.remove('is-open');
        panel.style.height = panel.scrollHeight + 'px';
        requestAnimationFrame(() => {
            panel.style.height = '0px';
        });
    }

    accordion.addEventListener('click', (e) => {
        const btn = e.target.closest('.acc-trigger');
        if (!btn) return;

        const item = btn.parentElement;
        const panel = btn.nextElementSibling;
        const isOpen = item.classList.contains('is-open');

        accordion.querySelectorAll('.acc-item.is-open').forEach(other => {
            const otherPanel = other.querySelector('.acc-panel');
            if (other !== item) close(otherPanel, other);
        });

        if (!isOpen) {
            open(panel, item);
        } else {
            close(panel, item);
        }
    });

    document.querySelectorAll('.acc-item.is-open .acc-panel').forEach(p => p.style.height = 'auto');

    //Tabs
    function openTab(tabName, buttonElement) {
        document.querySelectorAll('.tab-content').forEach(content => {
            content.classList.remove('active');
        });
        document.querySelectorAll('.tab-btn').forEach(btn => {
            btn.classList.remove('active');
        });
        document.querySelectorAll('.tab-content[data-tab="' + tabName + '"]').forEach(content => {
            content.classList.add('active');
        });
        buttonElement.classList.add('active');

    }

    const tabButtons = document.querySelectorAll('.tab-btn');
    tabButtons.forEach(button => {
        button.addEventListener('click', function() {
            const tabName = this.getAttribute('data-tab');
            openTab(tabName, this);
        });
    });

    // Auto switcher
    function initAutoSwitcher(selector, interval = 2000) {
        const container = document.querySelector(selector);
        if (!container) return;

        const items = container.querySelectorAll('.switch-item');
        if (items.length === 0) return;

        let currentIndex = 0;
        let intervalId = null;

        function toggleActive() {
            items.forEach(item => item.classList.remove('active'));
            items[currentIndex].classList.add('active');
            currentIndex = (currentIndex + 1) % items.length;
        }

        function startSwitcher() {
            if (window.innerWidth >= 768 && !intervalId) {
                toggleActive();
                intervalId = setInterval(toggleActive, interval);
            }
        }

        function stopSwitcher() {
            if (intervalId) {
                clearInterval(intervalId);
                intervalId = null;
                items.forEach(item => item.classList.remove('active'));
            }
        }

        function handleResize() {
            if (window.innerWidth >= 1279) {
                startSwitcher();
            } else {
                stopSwitcher();
            }
        }

        handleResize();
        window.addEventListener('resize', handleResize);
    }

    initAutoSwitcher('.how-it-works__advantages-list', 2000);
    initAutoSwitcher('.progress__list', 2000);

});

//Slider therapies
let therapiesSlider = null;

function initTherapiesSwiper() {
    const isLargeScreen = window.innerWidth >= 1280;

    if (therapiesSlider) {
        therapiesSlider.destroy(true, true);
        therapiesSlider = null;
    }

    therapiesSlider = new Swiper('.therapies__swiper', {
        slidesPerView: 'auto',
        spaceBetween: 20,
        ...(isLargeScreen
            ? {
                navigation: {
                    nextEl: '.swiper-button-next',
                    prevEl: '.swiper-button-prev',
                },
                freeMode: true,
                mousewheel: {
                    forceToAxis: true,
                    sensitivity: 1,
                    releaseOnEdges: true,
                },
            }
            : {
                pagination: {
                    el: '.swiper-pagination',
                    clickable: true,
                },
            }),
        on: {
            init: function () {
                initVideoPreviewBehavior(this);
            },
            slideChangeTransitionEnd: function () {
                handleVideoSlideChange(this);
            }
        }
    });
}

let resizeTimeout;
window.addEventListener('resize', () => {
    clearTimeout(resizeTimeout);
    resizeTimeout = setTimeout(initTherapiesSwiper, 300);
});

window.addEventListener('DOMContentLoaded', initTherapiesSwiper);

// Therapy video

function initVideoPreviewBehavior(swiper) {
    const isMobile = window.innerWidth < 1024;
    const videoItems = document.querySelectorAll('.therapy__video-item');

    if (isMobile) {
        handleVideoSlideChange(swiper); // Инициализировать первый раз
    } else {
        videoItems.forEach(item => {
            const video = item.querySelector('video');
            if (!video) return;

            video.pause();

            item.addEventListener('mouseenter', () => {
                video.play();
                item.classList.add('active');
            });

            item.addEventListener('mouseleave', () => {
                video.pause();
            });
        });
    }
}

function handleVideoSlideChange(swiper) {
    const isMobile = window.innerWidth < 1024;
    if (!isMobile) return;

    swiper.slides.forEach(slide => {
        const video = slide.querySelector('video');
        if (!video) return;

        if (slide.classList.contains('swiper-slide-active')) {
            video.play();
            slide.classList.add('active');
        } else {
            video.pause();
            slide.classList.remove('active');
        }
    });
}
