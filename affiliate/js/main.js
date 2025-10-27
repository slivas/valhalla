document.addEventListener('click', function (e) {
    const btn = e.target.closest('.video-play');
    if (!btn) return;

    const block = btn.closest('.meet-affiliates__list-item');
    const shell = block.querySelector('.video-shell');
    const src = block.dataset.src;
    const poster = block.dataset.poster || '';

    if (!src) return;

    const wrapper = document.createElement('div');
    wrapper.className = 'video-wrapper';

    const video = document.createElement('video');
    video.src = src;
    video.poster = poster;
    video.controls = true;
    video.autoplay = true;
    video.muted = true;
    video.playsInline = true;
    video.preload = 'metadata';
    video.style.width = '100%';
    video.style.height = '100%';
    video.style.objectFit = 'cover';
    video.style.display = 'block';

    wrapper.appendChild(video);

    shell.replaceWith(wrapper);

    video.muted = false;

    video.addEventListener('canplay', () => video.play().catch(() => {}), { once: true });
});
