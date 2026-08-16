// Cart quantity stepper - progressive enhancement over the plain
// number input, which still works fine with JS disabled.
document.addEventListener('click', function (e) {
    if (e.target.matches('.qty-incr, .qty-decr')) {
        const wrapper = e.target.closest('.qty-stepper');
        const input = wrapper.querySelector('input[type=number]');
        const min = parseInt(input.min || '1', 10);
        const max = parseInt(input.max || '9999', 10);
        let value = parseInt(input.value || '1', 10);

        value += e.target.classList.contains('qty-incr') ? 1 : -1;
        value = Math.max(min, Math.min(max, value));

        input.value = value;
    }
});
