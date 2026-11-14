<?php

namespace app\components;

use Yii;

/**
 * Простий обмежувач частоти дій з однієї IP (замовлення, заявки, реєстрація).
 * Час кожної дії зберігається в кеші; рахуються лише дії за останні $window секунд.
 *
 *     if (RateLimiter::tooMany('order', 3, 600)) { ...відмова... }
 *     ...дія вдалася...
 *     RateLimiter::hit('order', 600);
 */
final class RateLimiter
{
    /** Чи досягнуто ліміту $limit дій за $window секунд */
    public static function tooMany(string $action, int $limit, int $window): bool
    {
        return count(self::recent($action, $window)) >= $limit;
    }


    /** Зафіксувати дію */
    public static function hit(string $action, int $window): void
    {
        $times = self::recent($action, $window);
        $times[] = time();

        Yii::$app->cache->set(self::key($action), $times, $window);
    }


    private static function recent(string $action, int $window): array
    {
        $since = time() - $window;
        $times = Yii::$app->cache->get(self::key($action));

        return array_values(array_filter(is_array($times) ? $times : [], fn($t) => $t > $since));
    }


    private static function key(string $action): string
    {
        return 'rate:' . $action . ':' . md5((string)Yii::$app->request->userIP);
    }
}
