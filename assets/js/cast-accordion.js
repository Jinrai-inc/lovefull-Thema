/**
 * アコーディオン開閉
 *
 * @package KoiRiaPortal
 */

document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('.accordion__trigger, .accordion__sub-trigger').forEach(trigger => {
        trigger.addEventListener('click', () => {
            const content = trigger.nextElementSibling;
            if (!content) return;

            const isOpen = trigger.classList.toggle('is-open');
            content.classList.toggle('is-open');
            trigger.setAttribute('aria-expanded', isOpen);
        });
    });
});
