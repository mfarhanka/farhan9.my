document.querySelectorAll('[data-copy]').forEach((button) => {
    button.addEventListener('click', async () => {
        const target = document.querySelector(button.dataset.copy);
        if (!target) return;

        try {
            await navigator.clipboard.writeText(target.textContent.trim());
            const original = button.textContent;
            button.textContent = 'Copied';
            setTimeout(() => { button.textContent = original; }, 1500);
        } catch (error) {
            window.prompt('Copy this link:', target.textContent.trim());
        }
    });
});

document.querySelectorAll('[data-remove-form]').forEach((form) => {
    form.addEventListener('submit', (event) => {
        if (!window.confirm('Remove this tracked link? Its click history will also be deleted.')) {
            event.preventDefault();
        }
    });
});
