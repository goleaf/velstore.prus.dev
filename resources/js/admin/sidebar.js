const sidebarToggleButton = document.getElementById('sidebarToggle');
if (sidebarToggleButton) {
    sidebarToggleButton.addEventListener('click', function () {
        const sidebarEl = document.getElementById('sidebar');
        const contentEl = document.getElementById('content');
        if (sidebarEl) {
            sidebarEl.classList.toggle('collapsed');
        }
        if (contentEl) {
            contentEl.classList.toggle('collapsed');
        }
    });
}

const languageSelectItems = document.querySelectorAll('.language-select');
if (languageSelectItems && languageSelectItems.length > 0) {
    languageSelectItems.forEach(item => {
        item.addEventListener('click', function (e) {
            e.preventDefault();
            const selectedLang = this.getAttribute('data-lang');

            const modalEl = document.getElementById('languageChangeModal');
            if (!modalEl || typeof bootstrap === 'undefined' || !bootstrap.Modal) {
                return;
            }
            const modal = new bootstrap.Modal(modalEl);
            modal.show();

            const confirmBtn = document.getElementById('confirmChange');
            if (!confirmBtn) {
                return;
            }
            confirmBtn.onclick = function () {
                modal.hide();

                if (typeof axios === 'undefined') {
                    return;
                }
                axios.post('/admin/change-language', {
                    _token: (document.querySelector('meta[name="csrf-token"]') || {}).content,
                    lang: selectedLang
                })
                    .then(response => {
                        window.location.reload();
                    })
                    .catch(error => {
                        console.error(error);
                    });
            };
        });
    });
}


