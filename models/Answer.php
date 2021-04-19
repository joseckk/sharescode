<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "answer".
 *
 * @property int $id
 * @property string $content
 * @property string $date_created
 * @property int $query_id
 * @property int|null $users_id
 *
 * @property Users $users
 * @property Query $query
 */
class Answer extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'answer';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['content', 'date_created', 'query_id'], 'required'],
            [['date_created'], 'safe'],
            [['query_id', 'users_id'], 'default', 'value' => null],
            [['query_id', 'users_id'], 'integer'],
            [['content'], 'string', 'max' => 255],
            [['users_id'], 'exist', 'skipOnError' => true, 'targetClass' => Users::class, 'targetAttribute' => ['users_id' => 'id']],
            [['query_id'], 'exist', 'skipOnError' => true, 'targetClass' => Query::class, 'targetAttribute' => ['query_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'content' => 'Content',
            'date_created' => 'Date Created',
            'query_id' => 'Query ID',
            'users_id' => 'User ID',
        ];
    }

    /**
     * Gets query for [[Portrait]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne(Users::class, ['id' => 'users_id'])->inverseOf('answers');
    }

    /**
     * Gets query for [[Query]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getQuery()
    {
        return $this->hasOne(Query::class, ['id' => 'query_id'])->inverseOf('answers');
    }
}
