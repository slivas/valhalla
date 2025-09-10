document.addEventListener('DOMContentLoaded', () => {
    //Hero image animation
    const slider = document.querySelector('.glp-hero_img-carousel');
    const viewport = slider.querySelector('.glp-hero_img-carousel-viewport');
    const imageWrapper = viewport.querySelector('.custom-slider__image');

    let direction = -1;
    let position = 0;
    const speed = 0.75;
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

    //Element in viewport
    const targets = document.querySelectorAll('.animate');

    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('in-view');
                observer.unobserve(entry.target);
            }
        });
    }, {
        threshold: .9
    });

    targets.forEach(el => observer.observe(el));

    //Slider
    let networkSlider = null;
    function initNetworkSwiper() {
        if (window.innerWidth < 1024) {
            if (!networkSlider) {
                networkSlider = new Swiper('.your-way__swiper', {
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

});