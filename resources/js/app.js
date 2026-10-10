const pendingButtonsByComponent = new Map();

const escapeAttribute = (value) => {
    if (typeof CSS !== 'undefined' && typeof CSS.escape === 'function') {
        return CSS.escape(value);
    }

    return String(value).replace(/"/g, '\\"');
};

const defaultLoadingLabel = (button) => {
    if (button.dataset.loadingLabel) {
        return button.dataset.loadingLabel;
    }

    return button.type === 'submit' ? 'Menyimpan...' : 'Memproses...';
};

const storePendingButton = (componentId, button) => {
    if (!componentId) {
        return;
    }

    const currentButtons = pendingButtonsByComponent.get(componentId) ?? new Set();
    currentButtons.add(button);
    pendingButtonsByComponent.set(componentId, currentButtons);
};

const restoreButtonState = (button) => {
    if (!button || button.dataset.uiLoading !== 'true') {
        return;
    }

    button.disabled = false;
    button.classList.remove('is-loading');
    button.dataset.uiLoading = 'false';
    button.removeAttribute('aria-busy');

    if (button.tagName === 'INPUT') {
        if (button.dataset.originalValue) {
            button.value = button.dataset.originalValue;
        }

        return;
    }

    const labelNode = button.querySelector('[data-ui-submit-label]');

    if (labelNode && button.dataset.originalLabel) {
        labelNode.textContent = button.dataset.originalLabel;
        const spinnerNode = button.querySelector('[data-ui-spinner]');
        if (spinnerNode) {
            spinnerNode.remove();
        }
        return;
    }

    if (button.dataset.originalHtml) {
        button.innerHTML = button.dataset.originalHtml;
    }
};

const restorePendingButtonsForComponent = (componentId) => {
    const buttons = pendingButtonsByComponent.get(componentId);

    if (!buttons) {
        return;
    }

    buttons.forEach((button) => restoreButtonState(button));
    pendingButtonsByComponent.delete(componentId);
};

const applyLoadingState = (button, loadingLabel = null) => {
    if (!button || button.disabled || button.dataset.uiLoading === 'true') {
        return;
    }

    const currentLabel = loadingLabel || defaultLoadingLabel(button);

    button.disabled = true;
    button.classList.add('is-loading');
    button.dataset.uiLoading = 'true';
    button.setAttribute('aria-busy', 'true');

    if (button.tagName === 'INPUT') {
        if (!button.dataset.originalValue) {
            button.dataset.originalValue = button.value;
        }

        button.value = currentLabel;
        return;
    }

    const labelNode = button.querySelector('[data-ui-submit-label]');

    if (labelNode) {
        if (!button.dataset.originalLabel) {
            button.dataset.originalLabel = labelNode.textContent.trim();
        }

        if (!button.querySelector('[data-ui-spinner]')) {
            const spinner = document.createElement('span');
            spinner.dataset.uiSpinner = 'true';
            spinner.setAttribute('aria-hidden', 'true');
            spinner.className = 'inline-flex items-center justify-center';
            spinner.innerHTML = '<svg viewBox="0 0 24 24" fill="none" class="h-4 w-4 animate-spin"><circle cx="12" cy="12" r="9" class="opacity-25" stroke="currentColor" stroke-width="3"></circle><path d="M21 12a9 9 0 0 0-9-9" class="opacity-90" stroke="currentColor" stroke-width="3" stroke-linecap="round"></path></svg>';
            button.prepend(spinner);
        }

        labelNode.textContent = currentLabel;
        return;
    }

    if (!button.dataset.originalHtml) {
        button.dataset.originalHtml = button.innerHTML;
    }

    button.innerHTML = `<span data-ui-spinner="true" aria-hidden="true" class="inline-flex items-center justify-center"><svg viewBox="0 0 24 24" fill="none" class="h-4 w-4 animate-spin"><circle cx="12" cy="12" r="9" class="opacity-25" stroke="currentColor" stroke-width="3"></circle><path d="M21 12a9 9 0 0 0-9-9" class="opacity-90" stroke="currentColor" stroke-width="3" stroke-linecap="round"></path></svg></span><span>${currentLabel}</span>`;
};

const componentIdForElement = (element) => {
    const componentRoot = element.closest('[wire\\:id]');
    return componentRoot ? componentRoot.getAttribute('wire:id') : null;
};

const enhanceSubmitButtons = (root = document) => {
    const forms = Array.from(root.querySelectorAll('form'));

    forms.forEach((form) => {
        if (form.dataset.uiEnhanced === 'true') {
            return;
        }

        const submitButton = form.querySelector('[data-ui-submit]') ?? form.querySelector('button[type="submit"], input[type="submit"]');

        if (!submitButton) {
            return;
        }

        form.dataset.uiEnhanced = 'true';

        form.addEventListener('submit', () => {
            const componentId = componentIdForElement(form);
            if (componentId) {
                storePendingButton(componentId, submitButton);
            }

            applyLoadingState(submitButton);
        });
    });
};

const handleActionButtons = () => {
    document.addEventListener('click', (event) => {
        const button = event.target.closest('button[wire\\:click], button[data-ui-action]');

        if (!button) {
            return;
        }

        const componentId = componentIdForElement(button);

        if (componentId) {
            storePendingButton(componentId, button);
        }

        applyLoadingState(button);
    });
};

const restoreDetachedButtons = () => {
    pendingButtonsByComponent.forEach((buttons, componentId) => {
        buttons.forEach((button) => {
            if (!document.body.contains(button)) {
                restoreButtonState(button);
                buttons.delete(button);
            }
        });

        if (buttons.size === 0) {
            pendingButtonsByComponent.delete(componentId);
        }
    });
};

const registerLivewireHooks = () => {
    document.addEventListener('livewire:init', () => {
        if (!window.Livewire || typeof window.Livewire.hook !== 'function') {
            return;
        }

        window.Livewire.hook('commit', ({ component, succeed, fail }) => {
            const finish = () => restorePendingButtonsForComponent(component.id);

            succeed(() => finish());
            fail(() => finish());
        });
    });

    document.addEventListener('livewire:navigated', () => {
        enhanceSubmitButtons();
        restoreDetachedButtons();
    });

    document.addEventListener('livewire:navigate', () => {
        restoreDetachedButtons();
    });
};

document.addEventListener('DOMContentLoaded', () => {
    enhanceSubmitButtons();
    handleActionButtons();
    registerLivewireHooks();
    restoreDetachedButtons();
});

window.addEventListener('pageshow', () => {
    restoreDetachedButtons();
});

window.restorePlatformLoadingButtons = (componentId) => {
    if (!componentId) {
        pendingButtonsByComponent.forEach((_, id) => restorePendingButtonsForComponent(id));
        return;
    }

    restorePendingButtonsForComponent(componentId);
};

window.restorePlatformLoadingButtonBySelector = (selector) => {
    if (!selector) {
        return;
    }

    document.querySelectorAll(selector).forEach((button) => restoreButtonState(button));
};

window.markPlatformButtonLoading = (selector, loadingLabel = null) => {
    if (!selector) {
        return;
    }

    document.querySelectorAll(selector).forEach((button) => applyLoadingState(button, loadingLabel));
};

