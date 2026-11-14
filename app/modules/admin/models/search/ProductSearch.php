<?php

namespace app\modules\admin\models\search;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\modules\admin\models\Product;

/**
 * ProductSearch represents the model behind the search form of `app\modules\admin\models\Product`.
 */
class ProductSearch extends Product
{
    /**
     * {@inheritdoc}
     */
    public function rules(): array
    {
        return [
            [['id', 'category_id', 'price', 'status', 'sort_order'], 'integer'],
            [['name', 'slug', 'description', 'short_description', 'currency', 'image', 'meta_title', 'meta_description', 'created_at', 'updated_at'], 'safe'],
            [['old_price', 'weight'], 'number'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function scenarios()
    {
        // bypass scenarios() implementation in the parent class
        return Model::scenarios();
    }

    /**
     * Creates data provider instance with search query applied
     *
     * @param array $params
     * @param string|null $formName Form name to be used into `->load()` method.
     *
     * @return ActiveDataProvider
     */
    public function search($params, $formName = null)
    {
        // у категорії теж є id, name, status… — тож колонки товару завжди з префіксом p.,
        // інакше фільтр падає з "Column ... is ambiguous"
        $query = Product::find()
            ->alias('p')
            ->joinWith(['category'])
            ->orderBy(['p.id' => SORT_DESC]);

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        $this->load($params, $formName);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        // grid filtering conditions
        $query->andFilterWhere([
            'p.id' => $this->id,
            'p.category_id' => $this->category_id,
            'p.price' => $this->price,
            'p.old_price' => $this->old_price,
            'p.weight' => $this->weight,
            'p.status' => $this->status,
            'p.sort_order' => $this->sort_order,
            'p.created_at' => $this->created_at,
            'p.updated_at' => $this->updated_at,
        ]);

        $query->andFilterWhere(['like', 'p.name', $this->name])
            ->andFilterWhere(['like', 'p.slug', $this->slug])
            ->andFilterWhere(['like', 'p.description', $this->description])
            ->andFilterWhere(['like', 'p.short_description', $this->short_description])
            ->andFilterWhere(['like', 'p.currency', $this->currency])
            ->andFilterWhere(['like', 'p.image', $this->image])
            ->andFilterWhere(['like', 'p.meta_title', $this->meta_title])
            ->andFilterWhere(['like', 'p.meta_description', $this->meta_description]);

        return $dataProvider;
    }
}
