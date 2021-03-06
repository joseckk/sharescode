<?php

namespace app\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\models\Votes;

/**
 * VotesSearch represents the model behind the search form of `\app\models\Votes`.
 */
class VotesSearch extends Votes
{
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'puntuation', 'users_id'], 'integer'],
            [['typ', 'suggesting'], 'safe'],
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
        $query = Votes::find();

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
            'id' => $this->id,
            'puntuation' => $this->puntuation,
            'users_id' => $this->users_id,
        ]);

        $query->andFilterWhere(['ilike', 'typ', $this->typ])
            ->andFilterWhere(['ilike', 'suggesting', $this->suggesting]);

        return $dataProvider;
    }
}
