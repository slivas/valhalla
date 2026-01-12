document.addEventListener('click', function (e) {
    const btn = e.target.closest('.video-play');
    if (!btn) return;

    const block = btn.closest('.meet-affiliates__list-item');
    const src = block?.dataset.src;
    const poster = block?.dataset.poster || '';

    if (!src) return;

    const existingModal = document.querySelector('.video-modal');
    if (existingModal) existingModal.remove();

    const modal = document.createElement('div');
    modal.className = 'video-modal';
    modal.innerHTML = `
    <div class="video-modal__overlay"></div>
    <div class="video-modal__content">
      <video src="${src}" poster="${poster}" controls autoplay playsinline preload="metadata"></video>
      <button class="video-modal__close" aria-label="Закрити">
        <svg width="24" height="24" viewBox="0 0 24 24">
          <path d="M18 6L6 18M6 6l12 12" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
        </svg>
      </button>
    </div>
  `;
    document.body.appendChild(modal);

    const video = modal.querySelector('video');
    const closeBtn = modal.querySelector('.video-modal__close');
    const overlay = modal.querySelector('.video-modal__overlay');

    setTimeout(() => modal.classList.add('open'), 10);

    function closeModal() {
        modal.classList.remove('open');
        video.pause();
        setTimeout(() => modal.remove(), 300);
    }

    closeBtn.addEventListener('click', closeModal);
    overlay.addEventListener('click', closeModal);
    document.addEventListener('keydown', e => e.key === 'Escape' && closeModal(), { once: true });

    video.muted = false;
});
