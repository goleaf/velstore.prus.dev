@php
    $languageMeta = $languages->map(fn ($language) => [
        'code' => $language->code,
        'name' => ucwords($language->name),
    ])->values();
@endphp

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const languages = @json($languageMeta);
        const container = document.getElementById('attribute-values-container');
        const addButton = document.getElementById('add-attribute-value');

        if (!container || !addButton) {
            return;
        }

        const removeText = @json(__('cms.attributes.remove_value'));
        const valueLabelTemplate = @json(__('cms.attributes.value_label', ['number' => ':number']));
        const valuePlaceholderTemplate = @json(__('cms.attributes.value_placeholder', ['number' => ':number']));
        const translationLabelTemplate = @json(__('cms.attributes.translation_label', ['language' => ':language']));
        const translationPlaceholderTemplate = @json(__('cms.attributes.translation_placeholder', ['language' => ':language']));

        const escapeHtml = (value) => {
            if (typeof value !== 'string') {
                return '';
            }

            return value
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#39;');
        };

        const createRow = (value = '', translationValues = {}) => {
            const row = document.createElement('div');
            row.className = 'attribute-value-row rounded border border-gray-200 p-4';

            const translationsMarkup = languages.map(({ code }) => `
                <div class="col-12 col-md-6 col-lg-4">
                    <label class="form-label" data-translation-label data-language="${code}"></label>
                    <input
                        type="text"
                        name="translations[${code}][]"
                        value="${escapeHtml(translationValues[code] ?? '')}"
                        class="form-control"
                    >
                </div>
            `).join('');

            row.innerHTML = `
                <div class="row g-3 align-items-end">
                    <div class="col-12 col-md-6 col-lg-5">
                        <label class="form-label" data-value-label></label>
                        <input type="text" name="values[]" value="${escapeHtml(value)}" class="form-control">
                    </div>
                    <div class="col-12 col-md-6 col-lg-4 d-flex gap-2">
                        <button type="button" class="btn btn-outline-danger attribute-value-remove">${removeText}</button>
                    </div>
                </div>
                <div class="row g-3 mt-1">
                    ${translationsMarkup}
                </div>
            `;

            return row;
        };

        const updateRowLabels = () => {
            const rows = Array.from(container.querySelectorAll('.attribute-value-row'));

            rows.forEach((row, index) => {
                const valueLabel = row.querySelector('[data-value-label]');
                const valueInput = row.querySelector('input[name="values[]"]');

                if (valueLabel) {
                    valueLabel.textContent = valueLabelTemplate.replace(':number', index + 1);
                }

                if (valueInput) {
                    valueInput.placeholder = valuePlaceholderTemplate.replace(':number', index + 1);
                }

                row.querySelectorAll('[data-translation-label]').forEach((label) => {
                    const languageCode = label.getAttribute('data-language');
                    const languageName = languages.find((language) => language.code === languageCode)?.name ?? languageCode.toUpperCase();

                    label.textContent = translationLabelTemplate.replace(':language', languageName);

                    const input = label.nextElementSibling;
                    if (input && input.tagName === 'INPUT') {
                        input.placeholder = translationPlaceholderTemplate.replace(':language', languageName);
                    }
                });
            });
        };

        const addValueRow = (value = '', translationValues = {}) => {
            const row = createRow(value, translationValues);
            container.appendChild(row);
            updateRowLabels();
        };

        const clearRowValues = (row) => {
            row.querySelectorAll('input').forEach((input) => {
                input.value = '';
            });
        };

        const removeValueRow = (row) => {
            const rows = container.querySelectorAll('.attribute-value-row');

            if (rows.length <= 1) {
                clearRowValues(row);
                return;
            }

            row.remove();
            updateRowLabels();
        };

        container.addEventListener('click', (event) => {
            const trigger = event.target.closest('.attribute-value-remove');
            if (!trigger) {
                return;
            }

            event.preventDefault();
            const row = trigger.closest('.attribute-value-row');
            if (row) {
                removeValueRow(row);
            }
        });

        addButton.addEventListener('click', () => {
            addValueRow();
        });

        if (container.querySelectorAll('.attribute-value-row').length === 0) {
            addValueRow();
        } else {
            updateRowLabels();
        }
    });
</script>
