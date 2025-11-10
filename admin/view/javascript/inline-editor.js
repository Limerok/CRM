(function () {
    'use strict';

    const ACTIVE_CLASS = 'inline-editable-active';
    const BUSY_CLASS = 'inline-editable-busy';
    const FLASH_CLASS = 'inline-editable-flash';

    let activePopover = null;
    let activeTarget = null;

    const closePopover = () => {
        if (activePopover) {
            activePopover.remove();
            activePopover = null;
        }
        if (activeTarget) {
            activeTarget.classList.remove(ACTIVE_CLASS);
            activeTarget = null;
        }
    };

    const parseOptions = (target) => {
        const optionsString = target.dataset.options;
        if (!optionsString) {
            return [];
        }
        try {
            const parsed = JSON.parse(optionsString);
            if (Array.isArray(parsed)) {
                return parsed;
            }
        } catch (error) {
            console.error('Failed to parse inline editor options', error);
        }
        return [];
    };

    const buildPopover = (target) => {
        const popover = document.createElement('div');
        popover.className = 'inline-editor-popover card shadow-sm';

        const body = document.createElement('div');
        body.className = 'card-body py-2 px-2 d-flex gap-2 align-items-center';

        const type = target.dataset.type || 'text';
        const currentValue = target.dataset.value || '';
        let input;

        if (type === 'select') {
            input = document.createElement('select');
            input.className = 'form-select form-select-sm';
            const options = parseOptions(target);
            const uniqueOptions = Array.isArray(options) ? options.slice() : [];
            if (currentValue && !uniqueOptions.includes(currentValue)) {
                uniqueOptions.unshift(currentValue);
            }
            uniqueOptions.forEach((option) => {
                const optionElement = document.createElement('option');
                optionElement.value = option;
                optionElement.textContent = option;
                if (option === currentValue) {
                    optionElement.selected = true;
                }
                input.appendChild(optionElement);
            });
        } else {
            input = document.createElement('input');
            input.type = type === 'number' ? 'number' : 'text';
            input.className = 'form-control form-control-sm';
            if (type === 'number') {
                if (target.dataset.step) {
                    input.step = target.dataset.step;
                }
                if (target.dataset.min) {
                    input.min = target.dataset.min;
                }
                if (target.dataset.max) {
                    input.max = target.dataset.max;
                }
            }
            input.value = currentValue;
        }

        const buttonsWrapper = document.createElement('div');
        buttonsWrapper.className = 'inline-editor-buttons d-flex gap-1';

        const saveButton = document.createElement('button');
        saveButton.type = 'button';
        saveButton.className = 'btn btn-success btn-sm';
        saveButton.innerHTML = '<i class="bi bi-check"></i>';

        const cancelButton = document.createElement('button');
        cancelButton.type = 'button';
        cancelButton.className = 'btn btn-outline-secondary btn-sm';
        cancelButton.innerHTML = '<i class="bi bi-x"></i>';

        const errorContainer = document.createElement('div');
        errorContainer.className = 'inline-editor-error text-danger small mt-2';
        errorContainer.style.display = 'none';

        buttonsWrapper.appendChild(saveButton);
        buttonsWrapper.appendChild(cancelButton);

        body.appendChild(input);
        body.appendChild(buttonsWrapper);
        popover.appendChild(body);
        popover.appendChild(errorContainer);

        const setError = (message) => {
            if (!message) {
                errorContainer.textContent = '';
                errorContainer.style.display = 'none';
                return;
            }
            errorContainer.textContent = message;
            errorContainer.style.display = 'block';
        };

        const submit = async () => {
            if (!activeTarget) {
                return;
            }
            const url = activeTarget.dataset.updateUrl;
            const field = activeTarget.dataset.field;
            const id = activeTarget.dataset.id;
            if (!url || !field || !id) {
                setError('Некорректная конфигурация.');
                return;
            }

            const value = input.value;
            const formData = new FormData();
            formData.append('id', id);
            formData.append('field', field);
            formData.append('value', value);

            setError('');
            activeTarget.dataset.inlineBusy = '1';
            activeTarget.classList.add(BUSY_CLASS);
            saveButton.disabled = true;
            cancelButton.disabled = true;
            input.disabled = true;

            try {
                const response = await fetch(url, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });

                if (!response.ok) {
                    throw new Error('Ошибка сохранения');
                }

                const data = await response.json();
                if (!data || !data.success) {
                    throw new Error(data && data.error ? data.error : 'Не удалось сохранить значение');
                }

                const newValue = data.value !== undefined ? data.value : value;
                const formatted = data.formatted_value !== undefined ? data.formatted_value : newValue;
                activeTarget.dataset.value = newValue;
                activeTarget.textContent = formatted;
                activeTarget.classList.add(FLASH_CLASS);
                setTimeout(() => {
                    if (activeTarget) {
                        activeTarget.classList.remove(FLASH_CLASS);
                    }
                }, 1500);
                closePopover();
            } catch (error) {
                console.error(error);
                setError(error.message || 'Не удалось сохранить значение');
            } finally {
                if (activeTarget) {
                    delete activeTarget.dataset.inlineBusy;
                    activeTarget.classList.remove(BUSY_CLASS);
                }
                saveButton.disabled = false;
                cancelButton.disabled = false;
                input.disabled = false;
            }
        };

        saveButton.addEventListener('click', submit);
        cancelButton.addEventListener('click', () => {
            closePopover();
        });

        input.addEventListener('keydown', (event) => {
            if (event.key === 'Enter') {
                event.preventDefault();
                submit();
            }
            if (event.key === 'Escape') {
                event.preventDefault();
                closePopover();
            }
        });

        setTimeout(() => {
            input.focus();
            if (input instanceof HTMLInputElement) {
                input.select();
            }
        }, 0);

        return popover;
    };

    const positionPopover = (popover, target) => {
        const rect = target.getBoundingClientRect();
        const popRect = popover.getBoundingClientRect();

        let top = window.scrollY + rect.bottom + 8;
        let left = window.scrollX + rect.left;

        if (left + popRect.width > window.scrollX + window.innerWidth - 12) {
            left = window.scrollX + window.innerWidth - popRect.width - 12;
        }

        if (left < window.scrollX + 12) {
            left = window.scrollX + 12;
        }

        if (top + popRect.height > window.scrollY + window.innerHeight - 12) {
            top = window.scrollY + rect.top - popRect.height - 8;
        }

        if (top < window.scrollY + 12) {
            top = window.scrollY + 12;
        }

        popover.style.top = `${top}px`;
        popover.style.left = `${left}px`;
    };

    const openPopover = (target) => {
        if (target === activeTarget) {
            return;
        }

        closePopover();
        activeTarget = target;
        activeTarget.classList.add(ACTIVE_CLASS);

        activePopover = buildPopover(target);
        document.body.appendChild(activePopover);
        positionPopover(activePopover, target);
    };

    document.addEventListener('click', (event) => {
        const editTarget = event.target.closest('[data-inline-edit]');
        if (editTarget) {
            event.preventDefault();
            if (editTarget.dataset.inlineBusy === '1') {
                return;
            }
            openPopover(editTarget);
            return;
        }

        if (activePopover && !event.target.closest('.inline-editor-popover')) {
            closePopover();
        }
    });
})();
