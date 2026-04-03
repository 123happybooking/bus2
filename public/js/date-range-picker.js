function initDateRangePicker(fromSelector, toSelector) {
    const dateFromInput = document.querySelector(fromSelector);
    const dateToInput = document.querySelector(toSelector);
    
    if (!dateFromInput || !dateToInput) return;
    
    let selectedStartDate = dateFromInput.value ? new Date(dateFromInput.value) : null;
    
    function highlightInput(input, isActive) {
        if (isActive) {
            input.classList.add('date-input-active');
            input.style.borderColor = '#2563eb';
            input.style.boxShadow = '0 0 0 2px rgba(37, 99, 235, 0.2)';
        } else {
            input.classList.remove('date-input-active');
            input.style.borderColor = '';
            input.style.boxShadow = '';
        }
    }
    
    const fromPicker = flatpickr(dateFromInput, {
        locale: 'ja',
        dateFormat: 'Y-m-d',
        showMonths: 3,
        allowInput: true,
        clickOpens: true,
        mode: 'single',
        disableMobile: true,
        onOpen: function(selectedDates, dateStr, instance) {
            highlightInput(dateFromInput, true);
        },
        onClose: function(selectedDates, dateStr, instance) {
            highlightInput(dateFromInput, false);
            dateFromInput.blur();
        },
        onChange: function(selectedDates, dateStr, instance) {
            if (selectedDates.length > 0) {
                selectedStartDate = selectedDates[0];
                
                if (dateToInput.value && new Date(dateToInput.value) < selectedDates[0]) {
                    dateToInput.value = '';
                    if (toPicker) {
                        toPicker.clear();
                    }
                }
                
                if (toPicker) {
                    toPicker.set('minDate', selectedDates[0]);
                }
                instance.close();
                
                setTimeout(function() {
                    if (toPicker) {
                        toPicker.open();
                    }
                }, 100);
            }
        },
        onReady: function(selectedDates, dateStr, instance) {
            const daysContainer = instance.daysContainer;
            if (daysContainer) {
                const dayContainers = daysContainer.querySelectorAll('.dayContainer');
                dayContainers.forEach(function(dayContainer) {
                    const wrapper = document.createElement('div');
                    wrapper.className = 'month-wrapper';
                    dayContainer.parentNode.insertBefore(wrapper, dayContainer);
                    wrapper.appendChild(dayContainer);
                });
            }
        }
    });
    
    const toPicker = flatpickr(dateToInput, {
        locale: 'ja',
        dateFormat: 'Y-m-d',
        showMonths: 3,
        allowInput: true,
        clickOpens: true,
        mode: 'single',
        disableMobile: true,
        minDate: dateFromInput.value || new Date(),
        onOpen: function(selectedDates, dateStr, instance) {
            highlightInput(dateToInput, true);
        },
        onClose: function(selectedDates, dateStr, instance) {
            highlightInput(dateToInput, false);
            dateToInput.blur();
        },
        onDayCreate: function(dObj, dStr, fp, dayElem) {
            const date = dayElem.dateObj;
            if (selectedStartDate && date.toDateString() === selectedStartDate.toDateString()) {
                dayElem.classList.add('selected');
            }
        },
        onChange: function(selectedDates, dateStr, instance) {
            if (selectedDates.length > 0) {
                instance.close();
            }
        },
        onReady: function(selectedDates, dateStr, instance) {
            const daysContainer = instance.daysContainer;
            if (daysContainer) {
                const dayContainers = daysContainer.querySelectorAll('.dayContainer');
                dayContainers.forEach(function(dayContainer) {
                    const wrapper = document.createElement('div');
                    wrapper.className = 'month-wrapper';
                    dayContainer.parentNode.insertBefore(wrapper, dayContainer);
                    wrapper.appendChild(dayContainer);
                });
            }
            if (selectedStartDate) {
                instance.redraw();
            }
        }
    });
    
    if (dateFromInput.value) {
        toPicker.set('minDate', dateFromInput.value);
        selectedStartDate = new Date(dateFromInput.value);
    }
    
    return { fromPicker, toPicker };
}