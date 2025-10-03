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

        const rowTabControllers = new WeakMap();

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

        const createTabController = (row) => {
            const tabButtons = Array.from(row.querySelectorAll('[data-language-tab-target]'));
            const tabPanels = Array.from(row.querySelectorAll('[data-language-panel]'));

            if (!tabButtons.length || !tabPanels.length) {
                return null;
            }

            const setActiveTab = (targetCode) => {
                if (!targetCode) {
                    return;
                }

                tabButtons.forEach((button) => {
                    const isActive = button.dataset.languageTabTarget === targetCode;
                    button.classList.toggle('nav-tab-active', isActive);
                    button.classList.toggle('nav-tab-inactive', !isActive);
                    button.setAttribute('aria-selected', isActive ? 'true' : 'false');
                });

                tabPanels.forEach((panel) => {
                    const isActive = panel.dataset.languagePanel === targetCode;
                    panel.classList.toggle('hidden', !isActive);
                    panel.setAttribute('aria-hidden', isActive ? 'false' : 'true');
                });
            };

            tabButtons.forEach((button) => {
                button.addEventListener('click', () => {
                    setActiveTab(button.dataset.languageTabTarget);
                });
            });

            const initiallySelected = tabButtons.find((button) => button.getAttribute('aria-selected') === 'true');
            const initialCode = initiallySelected?.dataset.languageTabTarget || tabButtons[0]?.dataset.languageTabTarget;
            if (initialCode) {
                setActiveTab(initialCode);
            }

            return { setActiveTab };
        };

        const registerRow = (row) => {
            const controller = createTabController(row);
            if (controller) {
                rowTabControllers.set(row, controller);
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
            translationWrapper.className = 'translation-section space-y-3';
            translationWrapper.dataset.languageTabs = 'true';

            const tabList = document.createElement('div');
            tabList.className = 'flex flex-wrap gap-2';
            tabList.setAttribute('role', 'tablist');
            translationWrapper.appendChild(tabList);

            languages.forEach(({ code, name }, index) => {
                const tabId = `${rowId}-${code}-tab`;
                const panelId = `${rowId}-${code}-panel`;

                const tabButton = document.createElement('button');
                tabButton.type = 'button';
                tabButton.id = tabId;
                tabButton.dataset.languageTabTarget = code;
                tabButton.className = `nav-tab ${index === 0 ? 'nav-tab-active' : 'nav-tab-inactive'}`;
                tabButton.setAttribute('aria-controls', panelId);
                tabButton.setAttribute('aria-selected', index === 0 ? 'true' : 'false');

                const tabLabel = document.createElement('span');
                tabLabel.className = 'inline-flex items-center gap-2';

                const tabCode = document.createElement('span');
                tabCode.className = 'text-xs font-semibold uppercase text-gray-500';
                tabCode.textContent = code.toUpperCase();

                const tabName = document.createElement('span');
                tabName.className = 'text-sm font-medium text-gray-700';
                tabName.textContent = name;

                tabLabel.append(tabCode, tabName);
                tabButton.appendChild(tabLabel);
                tabList.appendChild(tabButton);

                const panel = document.createElement('div');
                panel.className = `space-y-2${index === 0 ? '' : ' hidden'}`;
                panel.id = panelId;
                panel.dataset.languagePanel = code;
                panel.setAttribute('role', 'tabpanel');
                panel.setAttribute('aria-labelledby', tabId);
                panel.setAttribute('aria-hidden', index === 0 ? 'false' : 'true');

                const label = document.createElement('label');
                label.className = 'form-label sr-only';
                label.setAttribute('for', `${rowId}-${code}-input`);
                label.textContent = `${translationPlaceholder} (${name})`;
                panel.appendChild(label);

                const translationInput = document.createElement('input');
                translationInput.type = 'text';
                translationInput.name = `translations[${code}][]`;
                translationInput.id = `${rowId}-${code}-input`;
                translationInput.value = translationValues[code] ?? '';
                translationInput.className = 'form-control';
                panel.appendChild(translationInput);

                translationWrapper.appendChild(panel);
            });

            row.appendChild(translationWrapper);

            return row;
        };

        const addValueRow = (value = '', translationValues = {}) => {
            const row = createRow(value, translationValues);
            valueContainer.appendChild(row);
            registerRow(row);
            updatePlaceholders();
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

            const controller = rowTabControllers.get(row);
            if (controller) {
                const firstButton = row.querySelector('[data-language-tab-target]');
                if (firstButton) {
                    controller.setActiveTab(firstButton.dataset.languageTabTarget);
                }
            }

            updatePlaceholders();
        };

        const removeValueRow = (row) => {
            const rows = Array.from(valueContainer.querySelectorAll('.attribute-value-row'));
            if (rows.length <= 1) {
                clearSingleRow(row);
                return;
            }

            rowTabControllers.delete(row);
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

        const existingRows = Array.from(valueContainer.querySelectorAll('.attribute-value-row'));
        if (!existingRows.length) {
            addValueRow();
        } else {
            existingRows.forEach((row) => registerRow(row));
            updatePlaceholders();
        }

        @if ($errors->any())
            const firstInvalid = document.querySelector('.is-invalid');
            if (firstInvalid) {
                const row = firstInvalid.closest('.attribute-value-row');
                const tabPane = firstInvalid.closest('[data-language-panel]');
                if (row && tabPane) {
                    const controller = rowTabControllers.get(row);
                    if (controller) {
                        controller.setActiveTab(tabPane.dataset.languagePanel);
                    }
                }
            }
        @endif
    });
</script>
