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
        const tabTriggers = Array.from(document.querySelectorAll('[data-language-tab-target]'));
        const tabPanels = Array.from(document.querySelectorAll('[data-language-panel]'));
        const removeText = @json(__('cms.attributes.remove_value'));
        const valuePlaceholder = @json(__('cms.attributes.attribute_values'));
        const translationPlaceholder = @json(__('cms.attributes.translated_value'));

        if (!valueContainer) {
            return;
        }

        const generateRowId = () => `attribute-value-${Date.now()}-${Math.floor(Math.random() * 10000)}`;

        const updatePlaceholders = () => {
            const rows = Array.from(valueContainer.querySelectorAll('.attribute-value-row'));

            rows.forEach((row, index) => {
                const baseInput = row.querySelector('input[name="values[]"]');
                if (baseInput) {
                    baseInput.placeholder = `${valuePlaceholder} #${index + 1}`;
                }

                languages.forEach(({ code, name }) => {
                    const translationInput = row.querySelector(`input[name="translations[${code}][]"]`);
                    if (translationInput) {
                        translationInput.placeholder = `${translationPlaceholder} (${name}) #${index + 1}`;
                    }
                });
            });
        };

        const activateFirstTab = (row) => {
            const firstTabTrigger = row.querySelector('.attribute-language-tabs button');
            if (firstTabTrigger && typeof bootstrap !== 'undefined') {
                bootstrap.Tab.getOrCreateInstance(firstTabTrigger).show();
            }
        };

        const createRow = (value = '', translationValues = {}) => {
            const row = document.createElement('div');
            row.className = 'attribute-value-row space-y-4 rounded-lg border border-gray-200 p-4';

            const rowId = generateRowId();
            row.dataset.rowId = rowId;

            const header = document.createElement('div');
            header.className = 'flex flex-col gap-2 md:flex-row md:items-start md:gap-3';

            const inputWrapper = document.createElement('div');
            inputWrapper.className = 'flex-1';

            const input = document.createElement('input');
            input.type = 'text';
            input.name = 'values[]';
            input.value = value || '';
            input.className = 'form-control';

            inputWrapper.appendChild(input);
            header.appendChild(inputWrapper);

            const removeButton = document.createElement('button');
            removeButton.type = 'button';
            removeButton.className = 'btn btn-outline-danger attribute-value-remove self-start';
            removeButton.textContent = removeText;

            header.appendChild(removeButton);
            row.appendChild(header);

            const translationWrapper = document.createElement('div');
            translationWrapper.className = 'translation-section';

            const navTabs = document.createElement('ul');
            navTabs.className = 'nav nav-tabs attribute-language-tabs';
            navTabs.id = `${rowId}-tabs`;
            navTabs.setAttribute('role', 'tablist');

            const tabContent = document.createElement('div');
            tabContent.className = 'tab-content mt-3';
            tabContent.id = `${rowId}-tab-content`;

            languages.forEach(({ code, name }, index) => {
                const tabId = `${rowId}-${code}-tab`;
                const paneId = `${rowId}-${code}-panel`;

                const listItem = document.createElement('li');
                listItem.className = 'nav-item';
                listItem.setAttribute('role', 'presentation');

                const tabButton = document.createElement('button');
                tabButton.type = 'button';
                tabButton.className = `nav-link${index === 0 ? ' active' : ''}`;
                tabButton.id = tabId;
                tabButton.dataset.bsToggle = 'tab';
                tabButton.dataset.bsTarget = `#${paneId}`;
                tabButton.setAttribute('role', 'tab');
                tabButton.setAttribute('aria-controls', paneId);
                tabButton.setAttribute('aria-selected', index === 0 ? 'true' : 'false');
                tabButton.textContent = name;

                listItem.appendChild(tabButton);
                navTabs.appendChild(listItem);

                const tabPane = document.createElement('div');
                tabPane.className = `tab-pane fade show${index === 0 ? ' active' : ''}`;
                tabPane.id = paneId;
                tabPane.setAttribute('role', 'tabpanel');
                tabPane.setAttribute('aria-labelledby', tabId);

                const translationInput = document.createElement('input');
                translationInput.type = 'text';
                translationInput.name = `translations[${code}][]`;
                translationInput.value = translationValues[code] ?? '';
                translationInput.className = 'form-control';

                tabPane.appendChild(translationInput);
                tabContent.appendChild(tabPane);
            });

            translationWrapper.appendChild(navTabs);
            translationWrapper.appendChild(tabContent);
            row.appendChild(translationWrapper);

            return row;
        };

        const addValueRow = (value = '', translationValues = {}) => {
            const row = createRow(value, translationValues);
            valueContainer.appendChild(row);
            updatePlaceholders();
            activateFirstTab(row);
        };

        const clearSingleRow = (row) => {
            const valueInput = row.querySelector('input[name="values[]"]');
            if (valueInput) {
                valueInput.value = '';
            }

            languages.forEach(({ code }) => {
                const translationInput = row.querySelector(`input[name="translations[${code}][]"]`);
                if (translationInput) {
                    translationInput.value = '';
                }
            });

            activateFirstTab(row);
            updatePlaceholders();
        };

        const removeValueRow = (row) => {
            const rows = Array.from(valueContainer.querySelectorAll('.attribute-value-row'));
            if (rows.length <= 1) {
                clearSingleRow(row);
                return;
            }

            row.remove();
            updatePlaceholders();
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
            valueContainer.querySelectorAll('.attribute-value-row').forEach((row) => activateFirstTab(row));
        }

        @if ($errors->any())
            const firstInvalid = document.querySelector('.is-invalid');
            if (firstInvalid) {
                const tabPane = firstInvalid.closest('[data-language-panel]');
                if (tabPane) {
                    const code = tabPane.dataset.languagePanel;
                    const trigger = document.querySelector(`[data-language-tab-target="${code}"]`);
                    if (trigger) {
                        setActiveTab(code);
                    }
                }
            }
        @endif
    });
</script>
