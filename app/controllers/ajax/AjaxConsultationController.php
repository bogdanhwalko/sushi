<?php

namespace app\controllers\ajax;

use app\forms\ConsultationForm;
use Yii;
use yii\filters\ContentNegotiator;
use yii\rest\Controller;
use yii\web\Response;

/**
 * Заявка на консультацію з блоку контактів: POST /ajax/ajax-consultation/send
 */
class AjaxConsultationController extends Controller
{
    /** форма надсилається з сайту — перевіряємо CSRF-токен */
    public $enableCsrfValidation = true;


    protected function verbs(): array
    {
        return [
            'send' => ['POST'],
        ];
    }


    public function behaviors(): array
    {
        $behaviors = parent::behaviors();

        $behaviors['contentNegotiator'] = [
            'class' => ContentNegotiator::class,
            'formats' => [
                'application/json' => Response::FORMAT_JSON,
            ],
        ];

        return $behaviors;
    }


    public function actionSend(): array
    {
        $form = new ConsultationForm();
        $form->load(Yii::$app->request->post(), '');

        if (!$form->send()) {
            return ['success' => false, 'errors' => $form->getErrorSummary(false)];
        }

        return ['success' => true];
    }
}
