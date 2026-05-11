const root = document.body;

if (root) {
    const mediaQuery = window.matchMedia('(prefers-reduced-motion: reduce)');
    const modalTriggers = document.querySelectorAll('[data-modal-target]');
    const modals = document.querySelectorAll('[data-modal]');
    let rafId = 0;
    let activeModal = null;

    const setBackgroundState = (clientX, clientY) => {
        const width = window.innerWidth || 1;
        const height = window.innerHeight || 1;
        const ratioX = Math.min(Math.max(clientX / width, 0), 1);
        const ratioY = Math.min(Math.max(clientY / height, 0), 1);
        const normalizedX = ratioX - 0.5;
        const normalizedY = ratioY - 0.5;

        root.style.setProperty('--pointer-x', `${ratioX * 100}%`);
        root.style.setProperty('--pointer-y', `${ratioY * 100}%`);
        root.style.setProperty('--parallax-x', `${normalizedX * 42}px`);
        root.style.setProperty('--parallax-y', `${normalizedY * 42}px`);
        root.style.setProperty('--grid-shift-x', `${normalizedX * 28}px`);
        root.style.setProperty('--grid-shift-y', `${normalizedY * 28}px`);
    };

    const moveBackground = (clientX, clientY) => {
        if (mediaQuery.matches) {
            return;
        }

        if (rafId) {
            cancelAnimationFrame(rafId);
        }

        rafId = requestAnimationFrame(() => {
            setBackgroundState(clientX, clientY);
            rafId = 0;
        });
    };

    const resetBackground = () => {
        if (mediaQuery.matches) {
            return;
        }

        setBackgroundState(window.innerWidth / 2, window.innerHeight / 2);
    };

    const closeModal = () => {
        if (!activeModal) {
            return;
        }

        activeModal.classList.remove('is-open');
        activeModal.setAttribute('aria-hidden', 'true');
        root.classList.remove('modal-open');
        activeModal = null;
    };

    const openModal = (modal) => {
        if (!modal) {
            return;
        }

        closeModal();
        modal.classList.add('is-open');
        modal.setAttribute('aria-hidden', 'false');
        root.classList.add('modal-open');
        activeModal = modal;
    };

    window.addEventListener('pointermove', (event) => {
        moveBackground(event.clientX, event.clientY);
    }, { passive: true });

    window.addEventListener('pointerleave', resetBackground);
    window.addEventListener('resize', resetBackground);
    mediaQuery.addEventListener('change', resetBackground);

    modalTriggers.forEach((trigger) => {
        trigger.addEventListener('click', () => {
            openModal(document.querySelector(trigger.dataset.modalTarget));
        });
    });

    modals.forEach((modal) => {
        modal.addEventListener('click', (event) => {
            if (event.target === modal || event.target.closest('[data-modal-close]')) {
                closeModal();
            }
        });
    });

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape') {
            closeModal();
        }
    });

    resetBackground();
}