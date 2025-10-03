<script>
    (function () {
        'use strict';

        const container = document.getElementById('gateway-configurations');
        const template = document.getElementById('config-row-template');
        const addButton = document.getElementById('add-configuration');

        if (!container || !template || !addButton) {
            return;
        }

        const extractHighestIndex = () => {
            const rows = Array.from(container.querySelectorAll('.config-row'));

            if (rows.length === 0) {
                return 0;
            }

            return rows.reduce((max, row) => {
                const value = Number.parseInt(row.getAttribute('data-config-index'), 10);

                if (Number.isNaN(value)) {
                    return max;
                }

                return Math.max(max, value);
            }, 0) + 1;
        };

        let nextIndex = extractHighestIndex();

        addButton.addEventListener('click', () => {
            const html = template.innerHTML.replace(/__INDEX__/g, String(nextIndex));
            container.insertAdjacentHTML('beforeend', html);
            nextIndex += 1;
        });

        container.addEventListener('click', (event) => {
            const target = event.target;

            if (target && target.classList.contains('btn-remove-config')) {
                const row = target.closest('.config-row');

                if (row) {
                    row.remove();
                }
            }
        });
    })();
</script>
