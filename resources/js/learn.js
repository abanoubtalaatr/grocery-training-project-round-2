document.addEventListener('DOMContentLoaded', () => {
    const revealItems = document.querySelectorAll('.reveal, .flow-step');

    if ('IntersectionObserver' in window) {
        const observer = new IntersectionObserver(
            (entries) => {
                entries.forEach((entry) => {
                    if (!entry.isIntersecting) {
                        return;
                    }

                    const el = entry.target;
                    const siblings = el.parentElement?.querySelectorAll('.flow-step') ?? [];
                    const index = [...siblings].indexOf(el);

                    if (el.classList.contains('flow-step') && index >= 0) {
                        el.style.animationDelay = `${index * 90}ms`;
                    }

                    el.classList.add('is-visible');
                    observer.unobserve(el);
                });
            },
            { threshold: 0.18, rootMargin: '0px 0px -8% 0px' }
        );

        revealItems.forEach((item) => observer.observe(item));
    } else {
        revealItems.forEach((item) => item.classList.add('is-visible'));
    }

    document.querySelectorAll('[data-copy]').forEach((button) => {
        button.addEventListener('click', async () => {
            const target = document.querySelector(button.getAttribute('data-copy'));
            if (!target) {
                return;
            }

            const text = target.innerText.trim();

            try {
                await navigator.clipboard.writeText(text);
                const original = button.textContent;
                button.textContent = 'Copied';
                setTimeout(() => {
                    button.textContent = original;
                }, 1400);
            } catch (error) {
                button.textContent = 'Copy failed';
            }
        });
    });
});
