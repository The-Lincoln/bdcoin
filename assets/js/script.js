document.addEventListener('DOMContentLoaded', function() {
    const forms = document.querySelectorAll('.needs-validation');
    Array.from(forms).forEach(form => {
        form.addEventListener('submit', event => {
            if (!form.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
            }
            form.classList.add('was-validated');
        }, false);
    });

    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function(el) {
        return new bootstrap.Tooltip(el);
    });

    const toastElList = [].slice.call(document.querySelectorAll('.toast'));
    toastElList.map(function(el) {
        return new bootstrap.Toast(el);
    });

    const amountInputs = document.querySelectorAll('input[name="amount"]');
    amountInputs.forEach(input => {
        input.addEventListener('input', function() {
            const parent = this.closest('.input-group') || this.parentElement;
            const display = parent.parentElement.querySelector('.amount-display');
            if (display) {
                display.textContent = '$' + parseFloat(this.value || 0).toFixed(2);
            }
        });
    });

    console.log('%c BDPay International ', 'background: #667eea; color: white; font-size: 14px; padding: 10px; border-radius: 4px;');
    console.log('%c Secure Payment Gateway v1.0', 'color: #764ba2; font-size: 12px;');
});
