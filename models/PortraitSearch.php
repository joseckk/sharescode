<?php

namespace app\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\models\Portrait;

/**
 * PortraitSearch represents the model behind the search form of `\app\models\Portrait`.
 */
class PortraitSearch extends Portrait
{
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['nickname', 'date_register', 'email', 'repository', 'prestige_port', 'sex'], 'safe'],
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
     *
     * @return ActiveDataProvider
     */
    public function search($params)
    {
        $query = Portrait::find();

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        // grid filtering conditions
        $query->andFilterWhere([
            'date_register' => $this->date_register,
        ]);

        $query->andFilterWhere(['ilike', 'nickname', $this->nickname])
            ->andFilterWhere(['ilike', 'email', $this->email])
            ->andFilterWhere(['ilike', 'repository', $this->repository])
            ->andFilterWhere(['ilike', 'prestige_port', $this->prestige_port])
            ->andFilterWhere(['ilike', 'sex', $this->sex]);

        return $dataProvider;
    }
}
