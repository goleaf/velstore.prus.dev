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

        if (previewWrapper && previewImage && previewImage.getAttribute('src')) {
            previewWrapper.classList.remove('d-none');
        }

        if (logoInput && previewWrapper && previewImage) {
            logoInput.addEventListener('change', (event) => {
                const [file] = event.target.files || [];

                if (!file) {
                    previewWrapper.classList.add('d-none');
                    previewImage.removeAttribute('src');
                    return;
                }

                const reader = new FileReader();
                reader.onload = (e) => {
                    previewWrapper.classList.remove('d-none');
                    previewImage.src = e.target?.result || '';
                };
                reader.readAsDataURL(file);
            });
        }

        const firstInvalidElement = brandFormWrapper.querySelector('.is-invalid');
        if (firstInvalidElement) {
            const tabPane = firstInvalidElement.closest('.tab-pane');
            if (tabPane) {
                const trigger = document.querySelector(`[data-bs-target="#${tabPane.id}"]`);
                if (trigger) {
                    bootstrap.Tab.getOrCreateInstance(trigger).show();
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
