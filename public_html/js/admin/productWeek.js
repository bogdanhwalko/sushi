document.addEventListener('DOMContentLoaded', () => {
    const select2 = $('#select2-product-week');
    const refreshButton = $('#refresh-product-week-button');

    select2.select2({
        dropdownAutoWidth: true,
        width: '85%',
        ajax: {
            url: '/api/product/by-select',
            dataType: 'json',
            delay: 250,
            data: function (params) {
                return { term: params.term };
            },
            cache: true
        }
    });

    loadProductWeek();
    $('#product-week-button').on('click', loadProductWeek);

    function loadProductWeek(e) {

        let product_id = e ? select2.val() : null;

        $.get('/admin-panel/ajax/product/product-week', { product_id: product_id }, function (html) {
            $('#card-refresh-content').html(html);
        });
    }
});