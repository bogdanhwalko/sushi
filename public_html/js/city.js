document.addEventListener('DOMContentLoaded', () => {
    const cityModalContainer = $('#cityWelcomeModal');
    const cityModal =  bootstrap.Modal.getOrCreateInstance(cityModalContainer[0])
    const sideCitySelector = $('.city-dropdown');

    if (! localStorage.getItem('city')) {
        cityModal.show();
    }
    else {
        sideCitySelector.val(localStorage.getItem('city'));
    }

    sideCitySelector.on('change', function(){
        $('.city-dropdown').val($(this).val());
        localStorage.setItem('city', $(this).val());
    })
});