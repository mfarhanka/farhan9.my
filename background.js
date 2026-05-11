const root = document.body;

if (root) {
    const mediaQuery = window.matchMedia('(prefers-reduced-motion: reduce)');
    let rafId = 0;

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

    window.addEventListener('pointermove', (event) => {
        moveBackground(event.clientX, event.clientY);
    }, { passive: true });

    window.addEventListener('pointerleave', resetBackground);
    window.addEventListener('resize', resetBackground);
    mediaQuery.addEventListener('change', resetBackground);

    resetBackground();
}