/**
 * Список товарів в адмінці: ціна і вага змінюються подвійним кліком по значенню.
 * Enter або клік поза полем — зберегти, Esc — скасувати.
 */
$(function () {
    const csrf = () => $('meta[name="csrf-token"]').attr('content');

    $(document).on('dblclick', '.js-inline-edit', function () {
        const cell = $(this);
        if (cell.hasClass('is-editing')) {
            return;
        }

        const oldHtml = cell.html();
        const oldValue = String(cell.data('value') ?? '');
        const input = $('<input type="number" min="0" step="1" class="form-control form-control-sm">').val(oldValue);

        let busy = false;
        const close = (html) => {
            cell.removeClass('is-editing').html(html);
        };

        const save = () => {
            if (busy) {
                return;
            }
            const value = String(input.val()).trim();
            if (value === oldValue) {
                close(oldHtml);
                return;
            }

            busy = true;
            input.prop('disabled', true);

            $.ajax({
                url: '/admin-panel/ajax/product/update-field',
                method: 'POST',
                dataType: 'json',
                headers: {'X-CSRF-Token': csrf()},
                data: {id: cell.data('id'), field: cell.data('field'), value: value},
            }).done(function (res) {
                if (res.success) {
                    cell.data('value', res.value).attr('data-value', res.value);
                    close($('<span>').text(res.text).html());
                    cell.addClass('is-saved');
                    setTimeout(() => cell.removeClass('is-saved'), 800);
                    return;
                }
                busy = false;
                input.prop('disabled', false).addClass('is-invalid').attr('title', res.error).trigger('focus');
                alert(res.error);
            }).fail(function (xhr) {
                busy = false;
                input.prop('disabled', false).addClass('is-invalid').trigger('focus');
                alert(xhr.status === 404 ? 'Товар не знайдено — оновіть сторінку.' : 'Не вдалося зберегти. Спробуйте ще раз.');
            });
        };

        cell.addClass('is-editing').empty().append(input);
        input.trigger('focus').trigger('select');

        input.on('keydown', function (e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                save();
            } else if (e.key === 'Escape') {
                busy = true; // щоб blur після закриття не зберіг
                close(oldHtml);
            }
        });
        // alert забирає фокус — після помилки не зберігаємо повторно автоматично
        input.on('blur', function () {
            if (!input.hasClass('is-invalid')) {
                save();
            }
        });
        input.on('input', function () {
            input.removeClass('is-invalid');
        });
    });
});
