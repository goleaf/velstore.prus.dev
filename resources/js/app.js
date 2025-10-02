import './bootstrap';
import './admin/sidebar';

document.addEventListener('click', function (event) {
    const target = event.target;
    if (!target) {
        return;
    }
    const button = target.closest && target.closest('button[data-url]');
    if (button && button.dataset && button.dataset.url) {
        event.preventDefault();
        const url = button.dataset.url;
        if (url && typeof url === 'string') {
            window.location.href = url;
        }
    }
}, true);
