document.addEventListener('DOMContentLoaded', () => {
    //Hero image animation
    const slider = document.querySelector('.hero-slider');
    const viewport = slider.querySelector('.hero-slider__viewport');
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
        paused = true;
    });

    slider.addEventListener('mouseleave', () => {
        paused = false;
    });

    window.addEventListener('load', () => {
        initScroll();
    });

    window.addEventListener('resize', updateMaxScroll);


    //Video play
    document.querySelectorAll('.video-item').forEach(preview => {
        const therapyVideo = preview.querySelector('video');

        preview.addEventListener('mouseenter', () => {
            therapyVideo.play();
            preview.classList.add('active');
        });

        preview.addEventListener('mouseleave', () => {
            therapyVideo.pause();
        });
    });

    //Anchor scroll
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            const targetId = this.getAttribute('href');
            const targetElement = document.querySelector(targetId);

            if (targetElement) {
                e.preventDefault();
                targetElement.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        });
    });

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

    if (tabButtons.length > 0) {
        tabButtons[0].click();
    }

    //Open modal in nexus section
    document.querySelectorAll('.nexus__list-item').forEach(item => {
        const openBtn = item.querySelector('.btn-secondary__inverse');
        const modal = item.querySelector('.nexus__list-modal');
        const closeBtn = item.querySelector('.nexus__list-modal-close');

        openBtn?.addEventListener('click', () => {
            modal?.classList.add('active');
        });

        closeBtn?.addEventListener('click', () => {
            modal?.classList.remove('active');
        });
    });

    //Cycle switcher
    function cycleActiveClass(selector, delay = 500) {
        let elements = [];
        let index = 0;
        let timeoutId = null;

        function clearClasses() {
            elements.forEach(el => el.classList.remove('active'));
        }

        function activateNext() {
            if (index >= elements.length) {
                clearClasses();
                index = 0;
            }

            elements[index].classList.add('active');
            index++;

            timeoutId = setTimeout(activateNext, delay);
        }

        function startCycle() {
            if (window.innerWidth >= 768 && !timeoutId) {
                elements = document.querySelectorAll(selector);
                if (elements.length === 0) return;
                activateNext();
            }
        }

        function stopCycle() {
            if (timeoutId) {
                clearTimeout(timeoutId);
                timeoutId = null;
                clearClasses();
                index = 0;
            }
        }

        function handleResize() {
            if (window.innerWidth >= 768) {
                startCycle();
            } else {
                stopCycle();
            }
        }

        window.addEventListener('resize', handleResize);
        handleResize();
    }

    cycleActiveClass('.cta-blueprint__list-item', 1000);



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



//Slider hero
let heroSlider = null;
function initHeroSwiper() {
    if (window.innerWidth < 1400) {
        if (!heroSlider) {
            heroSlider = new Swiper('.hero__services-swiper', {
                slidesPerView: 'auto',
                spaceBetween: 20,
                pagination: {
                    el: ".swiper-pagination",
                    clickable: true,
                },
            });
        }
    } else {
        if (heroSlider) {
            heroSlider.destroy(true, true);
            heroSlider = null;
        }
    }
}
window.addEventListener('DOMContentLoaded', initHeroSwiper);
window.addEventListener('resize', initHeroSwiper);

//Slider care network
let networkSlider = null;
function initNetworkSwiper() {
    if (window.innerWidth < 1024) {
        if (!networkSlider) {
            networkSlider = new Swiper('.care-network__swiper', {
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
        if (networkSlider) {
            networkSlider.destroy(true, true);
            networkSlider = null;
        }
    }
}
window.addEventListener('DOMContentLoaded', initNetworkSwiper);
window.addEventListener('resize', initNetworkSwiper);

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

//Slider cta blueprint
let ctaBlueprintSlider = null;
function initCtaBlueprintSlider() {
    if (window.innerWidth < 768) {
        if (!ctaBlueprintSlider) {
            ctaBlueprintSlider = new Swiper('.cta-blueprint__list-wrapper', {
                slidesPerView: 'auto',
                spaceBetween: 20,
                watchSlidesProgress: true,
                navigation: {
                    nextEl: '.swiper-button-next',
                    prevEl: '.swiper-button-prev',
                },
                breakpoints: {
                    768: {
                        slidesPerView: 2,
                    },
                },
            });
        }
    } else {
        if (ctaBlueprintSlider) {
            ctaBlueprintSlider.destroy(true, true);
            ctaBlueprintSlider = null;
        }
    }
}

window.addEventListener('DOMContentLoaded', initCtaBlueprintSlider);
window.addEventListener('resize', initCtaBlueprintSlider);


//Trigger chat button
const newChatButton = document.querySelector('.new-chat-button');

newChatButton.addEventListener('click', function() {
    const chatButton = document.querySelector('.chatbot-icon-button');

    chatButton.click();
});