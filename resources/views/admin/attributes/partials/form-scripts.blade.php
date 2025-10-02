@php
    $languageMeta = $languages->map(fn ($language) => [
        'code' => $language->code,
        'name' => ucwords($language->name),
    ])->values();
@endphp

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const languages = @json($languageMeta);
        const valueContainer = document.getElementById('attribute-values-container');
        const addButton = document.getElementById('add-attribute-value');
        const removeText = @json(__('cms.attributes.remove_value'));
        const valuePlaceholder = @json(__('cms.attributes.attribute_values'));
        const translationPlaceholder = @json(__('cms.attributes.translated_value'));

        if (!valueContainer) {
            return;
        }

        const updatePlaceholders = () => {
            const rows = Array.from(valueContainer.querySelectorAll('.attribute-value-row'));
            rows.forEach((row, index) => {
                const input = row.querySelector('input[name="values[]"]');
                if (input) {
                    input.placeholder = `${valuePlaceholder} #${index + 1}`;
                }
            });

            languages.forEach(({ code, name }) => {
                const container = document.getElementById(`translation-container-${code}`);
                if (!container) {
                    return;
                }

                const inputs = container.querySelectorAll(`input[name="translations[${code}][]"]`);
                inputs.forEach((input, index) => {
                    input.placeholder = `${translationPlaceholder} (${name}) #${index + 1}`;
                });
            });
        };

        const addTranslationGroup = (code) => {
            const container = document.getElementById(`translation-container-${code}`);
            if (!container) {
                return null;
            }

            const group = document.createElement('div');
            group.className = 'translation-group';

            const input = document.createElement('input');
            input.type = 'text';
            input.name = `translations[${code}][]`;
            input.className = 'form-control';

            group.appendChild(input);
            container.appendChild(group);

            return group;
        };

        const addValueRow = () => {
            const row = document.createElement('div');
            row.className = 'attribute-value-row flex flex-col gap-2 md:flex-row md:items-start md:gap-3';

            const inputWrapper = document.createElement('div');
            inputWrapper.className = 'flex-1';

            const input = document.createElement('input');
            input.type = 'text';
            input.name = 'values[]';
            input.className = 'form-control';

            inputWrapper.appendChild(input);
            row.appendChild(inputWrapper);

            const removeButton = document.createElement('button');
            removeButton.type = 'button';
            removeButton.className = 'btn btn-outline-danger attribute-value-remove self-start';
            removeButton.textContent = removeText;

            row.appendChild(removeButton);
            valueContainer.appendChild(row);

            languages.forEach(({ code }) => addTranslationGroup(code));
            updatePlaceholders();
        };

        const clearSingleRow = (row) => {
            const valueInput = row.querySelector('input[name="values[]"]');
            if (valueInput) {
                valueInput.value = '';
            }

            languages.forEach(({ code }) => {
                const container = document.getElementById(`translation-container-${code}`);
                if (!container) {
                    return;
                }

                const groups = Array.from(container.querySelectorAll('.translation-group'));
                groups.forEach((group, index) => {
                    const input = group.querySelector(`input[name="translations[${code}][]"]`);
                    if (index === 0) {
                        if (input) {
                            input.value = '';
                        }
                    } else {
                        group.remove();
                    }
                });

                if (groups.length === 0) {
                    addTranslationGroup(code);
                }
            });

            updatePlaceholders();
        };

        const removeValueRow = (row) => {
            const rows = Array.from(valueContainer.querySelectorAll('.attribute-value-row'));
            if (rows.length <= 1) {
                clearSingleRow(row);
                return;
            }

            const index = rows.indexOf(row);
            if (index === -1) {
                return;
            }

            row.remove();

            languages.forEach(({ code }) => {
                const container = document.getElementById(`translation-container-${code}`);
                if (!container) {
                    return;
                }

                const groups = Array.from(container.querySelectorAll('.translation-group'));
                if (groups[index]) {
                    groups[index].remove();
                }
            });

            if (valueContainer.querySelectorAll('.attribute-value-row').length === 0) {
                addValueRow();
            } else {
                updatePlaceholders();
            }
        };

        valueContainer.addEventListener('click', (event) => {
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

        if (addButton) {
            addButton.addEventListener('click', () => {
                addValueRow();
            });
        }

        if (valueContainer.querySelectorAll('.attribute-value-row').length === 0) {
            addValueRow();
        } else {
            updatePlaceholders();
        }

        @if ($errors->any())
            const firstInvalid = document.querySelector('.is-invalid');
            if (firstInvalid) {
                const tabPane = firstInvalid.closest('.tab-pane');
                if (tabPane && typeof bootstrap !== 'undefined') {
                    const trigger = document.querySelector(`[data-bs-target="#${tabPane.id}"]`);
                    if (trigger) {
                        bootstrap.Tab.getOrCreateInstance(trigger).show();
                    }
                }
            }
        @endif
    });
</script>
