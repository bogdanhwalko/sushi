<?php

namespace app\models;

use app\components\HeroSettings;
use Yii;
use yii\db\ActiveRecord;

/**
 * Історія змін шапки сайту: кожне збереження в адмінці — знімок усіх налаштувань.
 *
 * Фото, на які посилаються записи історії, не видаляються з диска, доки записи існують,
 * тож відновлення повертає і тексти, і фото. Зберігаємо останні KEEP записів.
 *
 * @property int $id
 * @property string $settings JSON
 * @property int|null $user_id
 * @property string $created_at
 *
 * @property User|null $user
 */
class HeroHistory extends ActiveRecord
{
    /** скільки останніх збережень пам'ятаємо */
    public const KEEP = 30;


    public static function tableName(): string
    {
        return '{{%hero_history}}';
    }


    public function getUser()
    {
        return $this->hasOne(User::class, ['id' => 'user_id']);
    }


    /** @return array<string, string> налаштування цього знімка */
    public function getValues(): array
    {
        $data = json_decode($this->settings, true);

        return HeroSettings::normalize(is_array($data) ? $data : []);
    }


    /** чи цей знімок збігається з тим, що зараз на сайті */
    public function isCurrent(): bool
    {
        return $this->getValues() === HeroSettings::stored();
    }


    /** Записує поточні налаштування, якщо вони відрізняються від останнього запису */
    public static function snapshot(): void
    {
        $values = HeroSettings::stored();

        $last = static::find()->orderBy(['id' => SORT_DESC])->one();
        if ($last !== null && $last->getValues() === $values) {
            return;
        }

        $row = new static();
        $row->settings = json_encode($values, JSON_UNESCAPED_UNICODE);
        $row->user_id = Yii::$app->has('user') && !Yii::$app->user->isGuest ? Yii::$app->user->id : null;
        $row->save(false);
    }


    /**
     * Зберігає нові налаштування з історією: стан «до» (якщо його ще немає в історії)
     * і стан «після», потім прибирає старі записи й непотрібні фото.
     *
     * @param array<string, string|null> $values
     */
    public static function apply(array $values): void
    {
        static::snapshot();      // стан до зміни — щоб до нього можна було повернутись
        HeroSettings::save($values);
        static::snapshot();      // новий стан
        static::prune();
    }


    /**
     * Залишає KEEP останніх записів і видаляє з диска фото, на які вже не посилаються
     * ні налаштування сайту, ні записи історії.
     */
    public static function prune(): void
    {
        $keepFrom = static::find()->select('id')->orderBy(['id' => SORT_DESC])->offset(self::KEEP - 1)->limit(1)->scalar();
        if ($keepFrom) {
            static::deleteAll(['<', 'id', $keepFrom]);
        }

        $used = [];
        foreach (array_merge([HeroSettings::stored()], array_map(
            static fn (self $row) => $row->getValues(),
            static::find()->all()
        )) as $values) {
            foreach (HeroSettings::FILE_KEYS as $key) {
                $used[$values[$key]] = true;
            }
        }

        $dir = HeroSettings::uploadPath();
        foreach (is_dir($dir) ? scandir($dir) : [] as $file) {
            if ($file[0] !== '.' && !isset($used[$file]) && is_file("$dir/$file")) {
                HeroSettings::deleteImage($file);
            }
        }
    }
}
