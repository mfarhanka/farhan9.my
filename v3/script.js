const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
const sections = document.querySelectorAll('.reveal');

if (reducedMotion || !('IntersectionObserver' in window)) {
    sections.forEach((section) => section.classList.add('is-visible'));
} else {
    const observer = new IntersectionObserver((entries) => {
        entries.forEach((entry) => {
            if (entry.isIntersecting) {
                entry.target.classList.add('is-visible');
                observer.unobserve(entry.target);
            }
        });
    }, { threshold: 0.12 });

    sections.forEach((section) => observer.observe(section));
}

document.getElementById('year').textContent = new Date().getFullYear();
