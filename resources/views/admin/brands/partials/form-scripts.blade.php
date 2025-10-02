@once
    <script src="https://cdn.ckeditor.com/ckeditor5/36.0.1/classic/ckeditor.js"></script>
@endonce
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const brandFormWrapper = document.querySelector('[data-brand-form]');
        if (!brandFormWrapper) {
            return;
        }

        const logoInput = document.getElementById('brandLogoFile');
        const previewWrapper = document.getElementById('brandLogoPreview');
        const previewImage = document.getElementById('brandLogoPreviewImg');

        const togglePreviewVisibility = (shouldShow) => {
            if (!previewWrapper) {
                return;
            }

            previewWrapper.classList.toggle('hidden', !shouldShow);
        };

        if (previewWrapper && previewImage && previewImage.getAttribute('src')) {
            togglePreviewVisibility(true);
        }

        if (logoInput && previewWrapper && previewImage) {
            logoInput.addEventListener('change', (event) => {
                const [file] = event.target.files || [];

                if (!file) {
                    togglePreviewVisibility(false);
                    previewImage.removeAttribute('src');
                    return;
                }

                const reader = new FileReader();
                reader.onload = (e) => {
                    previewImage.src = e.target?.result || '';
                    togglePreviewVisibility(true);
                };
                reader.readAsDataURL(file);
            });
        }

        const tabButtons = Array.from(brandFormWrapper.querySelectorAll('[data-tab-button]'));
        const tabPanels = Array.from(brandFormWrapper.querySelectorAll('[data-tab-panel]'));
        const activeTabInput = brandFormWrapper.querySelector('input[name="active_tab"]');

        if (tabButtons.length > 0 && tabPanels.length > 0) {
            const setActiveTab = (targetId) => {
                if (!targetId) {
                    return;
                }

                tabButtons.forEach((button) => {
                    const isActive = button.dataset.tabTarget === targetId;
                    button.classList.toggle('nav-tab-active', isActive);
                    button.classList.toggle('nav-tab-inactive', !isActive);
                    button.setAttribute('aria-selected', isActive ? 'true' : 'false');
                });

                tabPanels.forEach((panel) => {
                    const isActive = panel.id === targetId;
                    panel.classList.toggle('hidden', !isActive);
                    panel.setAttribute('aria-hidden', isActive ? 'false' : 'true');
                });

                if (activeTabInput) {
                    const activeButton = tabButtons.find((button) => button.dataset.tabTarget === targetId);
                    if (activeButton) {
                        activeTabInput.value = activeButton.dataset.tabValue || '';
                    }
                }
            };

            const showTabByValue = (value) => {
                if (!value) {
                    return false;
                }

                const matchingButton = tabButtons.find((button) => button.dataset.tabValue === value);
                if (!matchingButton) {
                    return false;
                }

                setActiveTab(matchingButton.dataset.tabTarget);
                return true;
            };

            tabButtons.forEach((button) => {
                button.addEventListener('click', () => {
                    setActiveTab(button.dataset.tabTarget);
                });
            });

            const initialTabValue = activeTabInput ? activeTabInput.value : undefined;

            if (!showTabByValue(initialTabValue) && tabButtons[0]) {
                setActiveTab(tabButtons[0].dataset.tabTarget);
            }

            const firstInvalidElement = brandFormWrapper.querySelector('.is-invalid');
            if (firstInvalidElement) {
                const tabPane = firstInvalidElement.closest('[data-tab-panel]');
                if (tabPane) {
                    setActiveTab(tabPane.id);
                }
            }
        }

        brandFormWrapper.querySelectorAll('.ck-editor-multi-languages').forEach((element) => {
            if (element.classList.contains('ck-editor-initialized')) {
                return;
            }

            ClassicEditor
                .create(element)
                .then(() => {
                    element.classList.add('ck-editor-initialized');
                })
                .catch((error) => {
                    console.error(error);
                });
        });
    });
</script>
