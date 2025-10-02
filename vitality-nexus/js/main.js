document.addEventListener('DOMContentLoaded', () => {

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