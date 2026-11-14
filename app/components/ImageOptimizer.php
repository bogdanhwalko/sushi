<?php

namespace app\components;

use Yii;
use yii\helpers\FileHelper;

/**
 * Зменшення і стискання завантажених зображень.
 *
 * Працює з тим, що є на сервері: Imagick → GD → без обробки (файл копіюється як є).
 * Результат — WebP, якщо сервер його вміє, інакше JPEG.
 */
final class ImageOptimizer
{
    /** більші зображення не обробляємо — забагато пам'яті (40 Мп ≈ 160 МБ у GD) */
    public const MAX_PIXELS = 40_000_000;

    /** @var string|null причина останньої помилки — для повідомлення адміну */
    public static ?string $lastError = null;


    /**
     * Зменшує зображення так, щоб воно вписалось у $maxWidth × $maxHeight (не збільшує),
     * стискає й зберігає у $dir під випадковим іменем.
     *
     * @return string|null ім'я збереженого файлу або null (причина — у self::$lastError)
     */
    public static function save(string $source, string $dir, int $maxWidth, int $maxHeight, int $quality = 80): ?string
    {
        self::$lastError = null;

        $info = @getimagesize($source);
        if ($info === false || !in_array($info[2], [IMAGETYPE_JPEG, IMAGETYPE_PNG, IMAGETYPE_WEBP], true)) {
            self::$lastError = 'Файл не є зображенням JPG, PNG або WebP.';
            return null;
        }
        if ($info[0] * $info[1] > self::MAX_PIXELS) {
            self::$lastError = 'Зображення завелике (понад 40 мегапікселів). Зменшіть його і спробуйте ще раз.';
            return null;
        }

        if (!FileHelper::createDirectory($dir)) {
            self::$lastError = 'Немає папки для зображень або прав на запис у неї.';
            return null;
        }

        $name = bin2hex(random_bytes(16));

        try {
            if (extension_loaded('imagick')) {
                return self::withImagick($source, $dir, $name, $maxWidth, $maxHeight, $quality);
            }
            if (extension_loaded('gd')) {
                return self::withGd($source, $info[2], $dir, $name, $maxWidth, $maxHeight, $quality);
            }
        } catch (\Throwable $e) {
            Yii::error('Не вдалося обробити зображення: ' . $e->getMessage(), __METHOD__);
            self::$lastError = 'Не вдалося обробити зображення. Спробуйте інший файл.';
            return null;
        }

        // обробити нічим — зберігаємо як є
        $ext = image_type_to_extension($info[2], false) === 'jpeg' ? 'jpg' : image_type_to_extension($info[2], false);
        if (!copy($source, "$dir/$name.$ext")) {
            self::$lastError = 'Не вдалося зберегти файл.';
            return null;
        }

        return "$name.$ext";
    }


    private static function withImagick(string $source, string $dir, string $name, int $maxW, int $maxH, int $quality): string
    {
        $im = new \Imagick($source);
        $im->setIteratorIndex(0); // з анімованих / багатосторінкових — лише перший кадр

        if (method_exists($im, 'autoOrient')) {
            $im->autoOrient(); // фото з телефона: повертаємо за EXIF
        }

        if ($im->getImageWidth() > $maxW || $im->getImageHeight() > $maxH) {
            $im->thumbnailImage($maxW, $maxH, true);
        }

        $webp = in_array('WEBP', \Imagick::queryFormats('WEBP'), true);
        if (!$webp) {
            // у JPEG немає прозорості — підкладаємо білий фон
            $im->setImageBackgroundColor('white');
            $im = $im->mergeImageLayers(\Imagick::LAYERMETHOD_FLATTEN);
            $im->setInterlaceScheme(\Imagick::INTERLACE_PLANE);
        }

        $im->stripImage(); // без EXIF (геолокація з телефона тощо)
        $im->setImageFormat($webp ? 'webp' : 'jpeg');
        $im->setImageCompressionQuality($quality);

        $file = $name . ($webp ? '.webp' : '.jpg');
        $im->writeImage("$dir/$file");
        $im->clear();

        return $file;
    }


    private static function withGd(string $source, int $type, string $dir, string $name, int $maxW, int $maxH, int $quality): string
    {
        $src = imagecreatefromstring((string)file_get_contents($source));
        if ($src === false) {
            throw new \RuntimeException('GD не прочитав файл');
        }

        // фото з телефона: повертаємо за EXIF
        if ($type === IMAGETYPE_JPEG && function_exists('exif_read_data')) {
            $orientation = @exif_read_data($source)['Orientation'] ?? 1;
            $angle = [3 => 180, 6 => -90, 8 => 90][$orientation] ?? 0;
            if ($angle) {
                $src = imagerotate($src, $angle, 0);
            }
        }

        $w = imagesx($src);
        $h = imagesy($src);
        $scale = min(1, $maxW / $w, $maxH / $h);
        $nw = max(1, (int)round($w * $scale));
        $nh = max(1, (int)round($h * $scale));

        $dst = imagecreatetruecolor($nw, $nh);
        $webp = function_exists('imagewebp');
        if ($webp) {
            imagealphablending($dst, false);
            imagesavealpha($dst, true);
        } else {
            imagefill($dst, 0, 0, imagecolorallocate($dst, 255, 255, 255));
        }
        imagecopyresampled($dst, $src, 0, 0, 0, 0, $nw, $nh, $w, $h);

        $file = $name . ($webp ? '.webp' : '.jpg');
        if ($webp) {
            imagewebp($dst, "$dir/$file", $quality);
        } else {
            imageinterlace($dst, true);
            imagejpeg($dst, "$dir/$file", $quality);
        }

        imagedestroy($src);
        imagedestroy($dst);

        return $file;
    }
}
