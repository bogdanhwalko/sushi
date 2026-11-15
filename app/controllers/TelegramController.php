<?php

namespace app\controllers;

use app\components\OrderTelegram;
use Yii;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\Response;

/**
 * Вебхук Telegram-ботів: сюди Telegram надсилає натискання кнопки «Прийняте» під замовленням.
 * Адреса: /telegram/webhook/<ключ бота> (див. OrderTelegram::webhookUrl()).
 */
class TelegramController extends Controller
{
    /** запити йдуть від серверів Telegram, а не з форм сайту — CSRF-токена в них немає */
    public $enableCsrfValidation = false;


    public function actionWebhook(string $key): Response
    {
        $token = OrderTelegram::tokenByKey($key);
        $secret = (string)Yii::$app->request->headers->get('X-Telegram-Bot-Api-Secret-Token', '');

        // невідомий бот або запит не від Telegram (немає нашого секрету)
        if ($token === null || !hash_equals(OrderTelegram::webhookSecret($token), $secret)) {
            throw new NotFoundHttpException();
        }

        $update = json_decode(Yii::$app->request->getRawBody(), true);
        if (is_array($update)) {
            try {
                OrderTelegram::handleUpdate($update, $token);
            } catch (\Throwable $e) {
                // відповідаємо 200 у будь-якому разі, інакше Telegram повторюватиме те саме оновлення
                Yii::error($e, __METHOD__);
            }
        }

        Yii::$app->response->format = Response::FORMAT_JSON;
        Yii::$app->response->data = ['ok' => true];

        return Yii::$app->response;
    }
}
