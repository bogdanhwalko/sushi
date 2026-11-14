document.addEventListener('DOMContentLoaded', () => {
    const cityModalContainer = $('#cityWelcomeModal');
    const cityModal =  bootstrap.Modal.getOrCreateInstance(cityModalContainer[0])
    const sideCitySelector = $('.city-dropdown');

    let city = localStorage.getItem('city');

    // залогінений клієнт: якщо на цьому пристрої місто ще не обирали — беремо з профілю
    const profileCity = document.body.dataset.userCity;
    if ((city === null || city === 'undefined' || city === '') && profileCity) {
        city = profileCity;
        localStorage.setItem('city', city);
    }

    if (city === null || city === 'undefined' || city === '') {
        cityModal.show();
    }
    else {
        sideCitySelector.val(city);
    }

    sideCitySelector.on('change', function(){
        $('.city-dropdown').val($(this).val());
        localStorage.setItem('city', $(this).val());
    })
});