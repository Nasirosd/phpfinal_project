document.addEventListener('DOMContentLoaded', () => {
    const toggle = document.querySelector('.menu-toggle');
    const navigation = document.querySelector('.main-nav');
    if (toggle && navigation) {
        toggle.addEventListener('click', () => {
            const expanded = toggle.getAttribute('aria-expanded') === 'true';
            toggle.setAttribute('aria-expanded', String(!expanded));
            navigation.classList.toggle('is-open', !expanded);
        });
    }

    document.querySelectorAll('input[type="date"][name="check_in"]').forEach((checkIn) => {
        const checkOut = checkIn.form?.querySelector('input[type="date"][name="check_out"]');
        if (!checkOut) return;
        checkIn.addEventListener('change', () => {
            if (checkIn.value) {
                const nextDay = new Date(`${checkIn.value}T00:00:00`);
                nextDay.setDate(nextDay.getDate() + 1);
                checkOut.min = nextDay.toISOString().slice(0, 10);
                if (checkOut.value && checkOut.value <= checkIn.value) checkOut.value = '';
            }
        });
    });
});
