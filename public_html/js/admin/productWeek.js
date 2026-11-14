document.addEventListener('DOMContentLoaded', () => {
    const select2 = $('#select2-product-week');
    const content = $('#card-refresh-content');

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

    // поточний товар тижня — лише читання
    $.get('/admin-panel/ajax/product/product-week', function (html) {
        content.html(html);
    });

    // зміна товару тижня — POST з CSRF-токеном
    $('#product-week-button').on('click', function () {
        const productId = select2.val();
        if (!productId) {
            return;
        }

        const button = $(this).prop('disabled', true);

        $.ajax({
            url: '/admin-panel/ajax/product/set-product-week',
            method: 'POST',
            dataType: 'html',
            headers: {'X-CSRF-Token': $('meta[name="csrf-token"]').attr('content')},
            data: {product_id: productId},
            success: function (html) {
                content.html(html);
            },
            error: function (xhr) {
                alert(xhr.status === 400 ? 'Оберіть активний товар.' : 'Не вдалося зберегти товар тижня.');
            },
            complete: function () {
                button.prop('disabled', false);
            }
        });
    });
});
