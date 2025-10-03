<script src="https://cdn.ckeditor.com/ckeditor5/36.0.1/classic/ckeditor.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const form = document.getElementById('pageForm');
        if (!form) {
            return;
        }

        const LANG_CODES = @json($activeLanguages->pluck('code'));
        const DEFAULT_LOCALE = "{{ config('app.locale') }}";
        const ORIGINAL_SLUG = "{{ optional($page ?? null)->slug }}";
        const CKEDITORS = {};

        document.querySelectorAll('.ck-editor-multi-languages').forEach((element) => {
            const id = element.id;
            ClassicEditor.create(element)
                .then((editor) => {
                    CKEDITORS[id] = editor;
                })
                .catch((error) => {
                    console.error('CKEditor init error', error);
                });
        });

        window.previewImage = function (input, langCode) {
            const file = input.files[0];
            const previewElement = document.getElementById('image_preview_' + langCode);
            const previewImage = document.getElementById('image_preview_img_' + langCode);
            const hiddenInput = document.getElementById('image_base64_' + langCode);

            if (file) {
                const reader = new FileReader();
                reader.onload = function (event) {
                    previewElement.style.display = 'block';
                    previewImage.src = event.target.result;
                    if (hiddenInput) {
                        hiddenInput.value = event.target.result;
                    }
                };
                reader.readAsDataURL(file);
            } else {
                previewElement.style.display = 'none';
                previewImage.src = '';
                if (hiddenInput) {
                    hiddenInput.value = '';
                }
            }
        };

        function base64ToFile(dataUrl, baseName) {
            if (!dataUrl || dataUrl.indexOf(',') === -1) return null;
            const [header, data] = dataUrl.split(',');
            const mimeMatch = header.match(/data:(.*);base64/);
            if (!mimeMatch) return null;
            const mime = mimeMatch[1];
            const extPart = mime.split('/')[1] || 'png';
            const ext = extPart.split('+')[0];
            const binary = atob(data);
            const len = binary.length;
            const bytes = new Uint8Array(len);
            for (let i = 0; i < len; i++) {
                bytes[i] = binary.charCodeAt(i);
            }
            const extension = ext === 'jpeg' ? 'jpg' : ext;
            const filename = baseName + '.' + extension;
            return new File([bytes], filename, { type: mime });
        }

        form.addEventListener('submit', function () {
            for (const code of LANG_CODES) {
                const textareaId = 'content_' + code;
                const editor = CKEDITORS[textareaId];
                if (editor) {
                    const textarea = document.getElementById(textareaId);
                    if (textarea) {
                        textarea.value = editor.getData();
                    }
                }
            }

            for (const code of LANG_CODES) {
                const fileInput = document.getElementById('image_file_' + code);
                const base64Input = document.getElementById('image_base64_' + code);
                if (fileInput && fileInput.files.length === 0 && base64Input && base64Input.value) {
                    const generatedFile = base64ToFile(base64Input.value, 'image_' + code);
                    if (generatedFile) {
                        const dataTransfer = new DataTransfer();
                        dataTransfer.items.add(generatedFile);
                        fileInput.files = dataTransfer.files;
                    }
                }
            }
        });

        const statusToggle = document.getElementById('page-status-toggle');
        const statusInput = document.getElementById('page-status-input');
        const statusHelp = document.getElementById('page-status-help');

        if (statusToggle && statusInput) {
            statusToggle.addEventListener('change', function () {
                const isActive = statusToggle.checked;
                statusInput.value = isActive ? 1 : 0;
                if (statusHelp) {
                    statusHelp.textContent = isActive
                        ? "{{ __('cms.pages.form_status_help_active') }}"
                        : "{{ __('cms.pages.form_status_help_inactive') }}";
                }
            });
        }

        function slugify(text) {
            return text
                .toString()
                .normalize('NFD').replace(/[\u0300-\u036f]/g, '')
                .toLowerCase()
                .trim()
                .replace(/[^a-z0-9\s-]/g, '')
                .replace(/\s+/g, '-')
                .replace(/-+/g, '-');
        }

        const slugInput = document.getElementById('page-slug');
        const defaultTitleInput = document.querySelector(`[name="translations[${DEFAULT_LOCALE}][title]"]`);
        const generateSlugButton = document.querySelector('[data-generate-slug]');

        if (slugInput) {
            if (!slugInput.value) {
                slugInput.dataset.autogenerated = 'true';
            }

            slugInput.addEventListener('input', () => {
                const autoSlug = slugify(defaultTitleInput ? defaultTitleInput.value : '');
                slugInput.dataset.autogenerated = slugInput.value === autoSlug ? 'true' : 'false';
            });
        }

        function updateSlugFromTitle(force = false) {
            if (!slugInput || !defaultTitleInput) {
                return;
            }

            const generated = slugify(defaultTitleInput.value || '');
            if (force || slugInput.dataset.autogenerated === 'true' || (!slugInput.value && !ORIGINAL_SLUG)) {
                slugInput.value = generated;
                slugInput.dataset.autogenerated = 'true';
            }
        }

        if (defaultTitleInput) {
            defaultTitleInput.addEventListener('input', () => updateSlugFromTitle(false));
        }

        if (generateSlugButton) {
            generateSlugButton.addEventListener('click', (event) => {
                event.preventDefault();
                updateSlugFromTitle(true);
            });
        }

        @if ($errors->any())
            const firstErrorElement = document.querySelector('.is-invalid');
            if (firstErrorElement) {
                const tabPane = firstErrorElement.closest('.tab-pane');
                if (tabPane) {
                    const tabId = tabPane.getAttribute('id');
                    const triggerEl = document.querySelector(`button[data-bs-target="#${tabId}"]`);
                    if (triggerEl) {
                        const tab = new bootstrap.Tab(triggerEl);
                        tab.show();
                    }
                }
            }
        @endif
    });
</script>
