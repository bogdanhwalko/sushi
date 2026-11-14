/**
 * Список товарів в адмінці: ціна і вага змінюються подвійним кліком по значенню.
 * Поруч із полем — кнопки ✓ (зберегти, або Enter) і ✕ (скасувати, або Esc).
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
        const saveBtn = $('<button type="button" class="btn btn-success" title="Зберегти (Enter)"><i class="fas fa-check"></i></button>');
        const cancelBtn = $('<button type="button" class="btn btn-outline-secondary" title="Скасувати (Esc)"><i class="fas fa-times"></i></button>');
        const group = $('<div class="input-group input-group-sm inline-edit-group"></div>')
            .append(input)
            .append($('<div class="input-group-append"></div>').append(saveBtn, cancelBtn));

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
            group.find('input, button').prop('disabled', true);

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
                group.find('input, button').prop('disabled', false);
                input.addClass('is-invalid').attr('title', res.error).trigger('focus');
                alert(res.error);
            }).fail(function (xhr) {
                busy = false;
                group.find('input, button').prop('disabled', false);
                input.addClass('is-invalid').trigger('focus');
                alert(xhr.status === 404 ? 'Товар не знайдено — оновіть сторінку.' : 'Не вдалося зберегти. Спробуйте ще раз.');
            });
        };

        const cancel = () => {
            busy = true;
            close(oldHtml);
        };

        cell.addClass('is-editing').empty().append(group);
        input.trigger('focus').trigger('select');

        // зберігається лише явно: кнопкою ✓ або Enter; ✕ або Esc — скасувати
        saveBtn.on('click', save);
        cancelBtn.on('click', cancel);
        group.on('dblclick click', (e) => e.stopPropagation()); // кліки в полі не відкривають перегляд/редагування

        input.on('keydown', function (e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                save();
            } else if (e.key === 'Escape') {
                cancel();
            }
        });
        input.on('input', function () {
            input.removeClass('is-invalid');
        });
    });
});
