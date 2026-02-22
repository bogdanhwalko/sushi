<?php

namespace app\components;


use yii\base\BaseObject;

class TelegramSender extends BaseObject
{
    public $token;

    public $chatId;

    /* Сповіщення не будуть надсилатись якщо false */
    public $sending = true;


    private $lastResponse;

    private $telegramUrl;



    public function init()
    {
        parent::init();

        $this->changeToken($this->token);
    }


    public function setChatId(int $chatId): self
    {
        $this->chatId = $chatId;

        return $this;
    }


    public function changeToken(string $token): self
    {
        $this->token = $token;
        $this->telegramUrl = 'https://api.telegram.org/bot' . $token . '/';

        return $this;
    }


    public function sendMessage($message, $chatId = null, array $keyboard = []): bool
    {
        $this->formatMessage($message);

        if ($this->sending) {
            $data = [
                'chat_id' => $chatId ?? $this->chatId,
                'text' => $message,
                'parse_mode' => 'html'
            ];

            if (! empty($keyboard)) {
                $data['reply_markup'] = json_encode($keyboard);
            }

            return $this->sendRequest('sendMessage', $data);
        }

        return true;
    }


    public function sendPhoto($message, string $imgLink, $chatId = null, array $keyboard = []): bool
    {
        $this->formatMessage($message);

        if ($this->sending) {
            $data = [
                'chat_id' => $chatId ?? $this->chatId,
                'caption' => $message,
                'parse_mode' => 'html',
                'photo' => new \CURLFile($imgLink)
            ];

            if (!empty($keyboard)) {
                $data['reply_markup'] = json_encode($keyboard);
            }

            return $this->sendRequestAsFormMultipart('sendPhoto', $data);
        }

        return true;
    }


    public function updateMessage($message, $oldId, $chatId = null, array $keyboard = []): bool
    {
        $this->formatMessage($message);

        if ($this->sending) {
            $data = [
                'chat_id' => $chatId ?? $this->chatId,
                'text' => $message,
                'parse_mode' => 'html',
                'message_id' => $oldId
            ];

            if (! empty($keyboard)) {
                $data['reply_markup'] = json_encode($keyboard);
            }

            return $this->sendRequest('editMessageText', $data);
        }

        return true;
    }


    public function editMessageCaption($message, $oldId, $chatId = null, array $keyboard = []): bool
    {
        $this->formatMessage($message);

        if ($this->sending) {
            $data = [
                'chat_id' => $chatId ?? $this->chatId,
                'caption' => $message,
                'parse_mode' => 'html',
                'message_id' => $oldId
            ];

            if (! empty($keyboard)) {
                $data['reply_markup'] = json_encode($keyboard);
            }

            return $this->sendRequest('editMessageCaption', $data);
        }

        return true;
    }


    public function setHook($url)
    {
        return $this->sendRequest('setWebhook', ['url' => $url]);
    }


    public function deleteHook($url)
    {
        return $this->sendRequest('deleteWebhook', ['url' => $url]);
    }


    public function getLastResponse()
    {
        return $this->lastResponse;
    }


    private function formatMessage(mixed &$message): void
    {
        if (is_array($message) || is_object($message)) {
            $message = json_encode($message, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        }
    }


    private function sendRequestAsFormMultipart(string $method, array $params)
    {
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $this->telegramUrl . $method);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
        curl_setopt($ch, CURLOPT_HEADER, 0);

        $result = curl_exec($ch);
        curl_close($ch);

        $this->lastResponse = json_decode($result);

        return $this->lastResponse->ok ?? false;
    }


    private function sendRequest(string $method, array $params = []): bool
    {
        $url = $this->telegramUrl . $method . (!empty($params)? '?' . http_build_query($params) : '');

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, 0);

        $result = curl_exec($ch);
        curl_close($ch);

        $this->lastResponse = json_decode($result);

        return $this->lastResponse->ok ?? false;
    }
}
