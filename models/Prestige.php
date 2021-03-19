<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "prestige".
 *
 * @property int $id
 * @property string $type_prestige
 * @property int $score
 * @property int|null $portrait_id
 *
 * @property Portrait $portrait
 */
class Prestige extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'prestige';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['type_prestige'], 'required'],
            [['score', 'portrait_id'], 'default', 'value' => null],
            [['score', 'portrait_id'], 'integer'],
            [['type_prestige'], 'string', 'max' => 255],
            [['portrait_id'], 'exist', 'skipOnError' => true, 'targetClass' => Portrait::class, 'targetAttribute' => ['portrait_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'type_prestige' => 'Type Prestige',
            'score' => 'Score',
            'portrait_id' => 'Portrait ID',
        ];
    }

    /**
     * Gets query for [[Portrait]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getPortrait()
    {
        return $this->hasOne(Portrait::className(), ['id' => 'portrait_id'])->inverseOf('prestiges');
    }
}
