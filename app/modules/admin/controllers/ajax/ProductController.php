<?php

namespace app\modules\admin\controllers\ajax;

use app\modules\admin\controllers\AbstractController;
use app\modules\admin\models\Config;
use app\modules\admin\models\Product;
use Yii;
use yii\filters\VerbFilter;
use yii\web\BadRequestHttpException;
use yii\web\NotFoundHttpException;
use yii\web\Response;

class ProductController extends AbstractController
{
    public function behaviors()
    {
        return array_merge(parent::behaviors(), [
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'product-week' => ['GET'],
                    // зміна лише POST-запитом: для нього Yii перевіряє CSRF-токен
                    'set-product-week' => ['POST'],
                    'update-field' => ['POST'],
                ],
            ],
        ]);
    }


    /**
     * Зміна ціни або ваги прямо зі списку товарів (подвійний клік по значенню).
     * POST id, field (price|weight), value → {success, value, text} або {success: false, error}.
     */
    public function actionUpdateField(): array
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $request = Yii::$app->request;

        $field = $request->post('field');
        if (!in_array($field, Product::INLINE_FIELDS, true)) {
            throw new BadRequestHttpException('Це поле не можна змінювати зі списку.');
        }

        $id = filter_var($request->post('id'), FILTER_VALIDATE_INT);
        $product = $id ? Product::findOne($id) : null;
        if ($product === null) {
            throw new NotFoundHttpException('Товар не знайдено.');
        }

        if (!$product->saveInlineField($field, (string)$request->post('value', ''))) {
            return [
                'success' => false,
                'error' => $product->getFirstError($field) ?: 'Не вдалося зберегти.',
            ];
        }

        return [
            'success' => true,
            'value' => $product->$field === null ? '' : (string)(int)$product->$field,
            'text' => $product->inlineText($field),
        ];
    }


    /** Картка поточного товару тижня (лише читання) */
    public function actionProductWeek()
    {
        $productWeekId = Config::get('product_week_id');

        return $this->renderProductWeek($productWeekId ? Product::findOne($productWeekId) : null);
    }


    public function actionSetProductWeek()
    {
        $productId = filter_var(Yii::$app->request->post('product_id'), FILTER_VALIDATE_INT);

        $product = $productId ? Product::findOne(['id' => $productId, 'status' => 1]) : null;
        if ($product === null) {
            throw new BadRequestHttpException('Оберіть активний товар.');
        }

        Config::set('product_week_id', (string)$product->id);

        return $this->renderProductWeek($product);
    }


    private function renderProductWeek(?Product $productWeek): string
    {
        return $this->renderPartial('product-week', [
            'productWeek' => $productWeek,
        ]);
    }
}
