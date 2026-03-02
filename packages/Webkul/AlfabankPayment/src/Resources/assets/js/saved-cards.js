/**
 * Alfa-Bank Saved Cards Handler
 */
(function() {
    'use strict';

    let selectedCardBindingId = null;

    /**
     * Load saved cards for customer
     */
    function loadSavedCards() {
        const paymentMethod = document.querySelector('input[name="payment[method]"]:checked');
        
        if (!paymentMethod || paymentMethod.value !== 'alfabank') {
            resetCardSelection();
            return;
        }

        // Check if customer is logged in (you may need to adjust this check)
        const isLoggedIn = document.body.classList.contains('customer-logged-in') || 
                          window.customerId !== undefined;

        if (!isLoggedIn) {
            return;
        }

        // Make AJAX request to get saved cards
        const route = window.alfabankRoutes?.getCards || '/alfabank/saved-cards';
        fetch(route, {
            method: 'GET',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
            },
            credentials: 'same-origin',
        })
        .then(response => response.json())
        .then(data => {
            displaySavedCards(data.cards || []);
        })
        .catch(error => {
            console.error('Error loading saved cards:', error);
        });
    }

    /**
     * Display saved cards in the payment method area
     */
    function displaySavedCards(cards) {
        // Remove existing saved cards display
        const existingDisplay = document.querySelector('.alfabank-saved-cards-wrapper');
        if (existingDisplay) {
            existingDisplay.remove();
        }

        if (!cards || cards.length === 0) {
            return;
        }

        // Find the payment method container
        const paymentMethod = document.querySelector('input[name="payment[method]"][value="alfabank"]');
        if (!paymentMethod) {
            return;
        }

        // Find parent container (adjust selector based on your checkout structure)
        const paymentContainer = paymentMethod.closest('.payment-method-item') || 
                                 paymentMethod.closest('label')?.parentElement ||
                                 paymentMethod.closest('[class*="payment"]');

        if (!paymentContainer) {
            return;
        }

        // Create wrapper
        const wrapper = document.createElement('div');
        wrapper.className = 'alfabank-saved-cards-wrapper mt-4 p-4 bg-gray-50 rounded-lg';

        // Create title
        const title = document.createElement('h4');
        title.className = 'text-sm font-semibold mb-3';
        title.textContent = 'Выберите карту для оплаты:';
        wrapper.appendChild(title);

        // Create cards list
        const cardsList = document.createElement('div');
        cardsList.className = 'space-y-2';

        // Add "New card" option
        const newCardOption = createCardOption(null, 'Новая карта', true);
        cardsList.appendChild(newCardOption);

        // Add saved cards
        cards.forEach(card => {
            const cardOption = createCardOption(card.binding_id, card.card_mask + ' — ' + (card.card_type || ''), false);
            cardsList.appendChild(cardOption);
        });

        wrapper.appendChild(cardsList);
        paymentContainer.appendChild(wrapper);
    }

    /**
     * Create card option element
     */
    function createCardOption(bindingId, label, isNewCard) {
        const labelEl = document.createElement('label');
        labelEl.className = 'saved-card flex items-center p-2 border rounded cursor-pointer hover:bg-gray-100';
        
        const radio = document.createElement('input');
        radio.type = 'radio';
        radio.name = 'alfabank_saved_card';
        radio.value = bindingId || '';
        radio.className = 'mr-2';
        
        if (isNewCard || !bindingId) {
            radio.checked = true;
            selectedCardBindingId = null;
        }

        radio.addEventListener('change', function() {
            if (this.checked) {
                selectedCardBindingId = bindingId;
                setSelectedCard(bindingId);
            }
        });

        labelEl.appendChild(radio);
        
        const text = document.createElement('span');
        text.textContent = label;
        labelEl.appendChild(text);

        return labelEl;
    }

    /**
     * Set selected card via AJAX
     */
    function setSelectedCard(bindingId) {
        const formData = new FormData();
        formData.append('binding_id', bindingId || '');

        const selectRoute = window.alfabankRoutes?.selectCard || '/alfabank/saved-cards/select';
        fetch(selectRoute, {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
            },
            body: formData,
            credentials: 'same-origin',
        })
        .catch(error => {
            console.error('Error setting selected card:', error);
        });
    }

    /**
     * Reset card selection
     */
    function resetCardSelection() {
        selectedCardBindingId = null;
        const existingDisplay = document.querySelector('.alfabank-saved-cards-wrapper');
        if (existingDisplay) {
            existingDisplay.remove();
        }
        setSelectedCard(null);
    }

    /**
     * Initialize when DOM is ready
     */
    function init() {
        // Listen for payment method changes
        document.addEventListener('change', function(e) {
            if (e.target.matches('input[name="payment[method]"]')) {
                loadSavedCards();
            }
        });

        // Load cards on page load if alfabank is already selected
        if (document.querySelector('input[name="payment[method]"][value="alfabank"]:checked')) {
            loadSavedCards();
        }

        // Also listen for Vue.js events if using Vue components
        if (window.app && window.app.config) {
            window.addEventListener('payment-method-selected', function(e) {
                if (e.detail && e.detail.method === 'alfabank') {
                    setTimeout(loadSavedCards, 100);
                } else {
                    resetCardSelection();
                }
            });
        }
    }

    // Initialize when DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
