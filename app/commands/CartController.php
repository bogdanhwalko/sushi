<?php

namespace app\commands;

use app\models\CartItems;
use yii\console\Controller;
use yii\console\ExitCode;

/**
 * Обслуговування кошиків.
 *
 * Запуск щодня через cron (від користувача сайту):
 *     15 4 * * * php /шлях/до/проєкту/app/yii cart/cleanup >/dev/null 2>&1
 */
class CartController extends Controller
{
    /**
     * Видаляє гостьові кошики, які не змінювались вказану кількість днів (за замовчуванням 14).
     * Після закінчення сесії гість до такого кошика вже не повернеться.
     * Кошики зареєстрованих клієнтів не видаляються.
     *
     * @param int $days скільки днів без змін вважати кошик покинутим
     */
    public function actionCleanup(int $days = 14): int
    {
        if ($days < 1) {
            $this->stderr("Кількість днів має бути не менше 1.\n");
            return ExitCode::USAGE;
        }

        $deleted = CartItems::deleteAbandonedGuestItems($days);
        $this->stdout("Видалено позицій з покинутих гостьових кошиків (старших за {$days} дн.): {$deleted}\n");

        return ExitCode::OK;
    }
}
