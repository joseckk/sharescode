<?php

namespace app\models;

use DateTime;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use Yii;

/**
 * This is the model class for table "users".
 *
 * @property int $id
 * @property bool $is_deleted
 * 
 * @property Portrait[] $portraits
 */
class Users extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'users';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['is_deleted'], 'boolean'],
        ];
    }

    public function attributes()
    {
        return array_merge(parent::attributes(), ['password_repeat']);
    }


    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'is_deleted' => 'Is Deleted',
        ];
    }

    public function beforeDelete()
    {
        if (!parent::beforeDelete()) {
            return false;
        }

        if ($this->getQueries()->exists() || $this->getAnswers()->exists()) {
            Yii::$app->session->setFlash('error', 'User is associated with some queries or answers.');
            return false;
        }

        return true;
    }


    /**
     * Gets query for [[Prestiges]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getPrestiges()
    {
        return $this->hasMany(Prestige::class, ['users_id' => 'id'])->inverseOf('users');
    }

    /**
     * Gets query for [[Queries]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getQueries()
    {
        return $this->hasMany(Query::class, ['users_id' => 'id'])->inverseOf('users');
    }

    /**
     * Gets query for [[Answers]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getAnswers()
    {
        return $this->hasMany(Answer::class, ['users_id' => 'id'])->inverseOf('users');
    }

    /**
     * Gets query for [[Reminders]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getReminders()
    {
        return $this->hasMany(Reminder::class, ['users_id' => 'id'])->inverseOf('users');
    }

    /**
     * Count notifications not read by user.
     *
     * @return int
     */
    public static function countReminders()
    {
        if (!Yii::$app->user->isGuest) {
            return Reminder::find()->where([
                    'users_id' => Yii::$app->user->id,
                    'is_read' => false
            ])->count();;
        } else {
            return 0;
        }
    }

    /**
     *  Calculate the time that has passed 
     * since you received the last notification
     *
     * @return string $day + $hours + $minutes + $seconds
     */
    public static function timeReminders()
    {
        if (!Yii::$app->user->isGuest) {
            $time = Reminder::find()->where([
                        'users_id' => Yii::$app->user->id,
                        'is_read' => false
                    ])->max('date_created');
            $today = new DateTime();
            $time = new DateTime($time);
            $time_reminder = $today->diff($time);
            if ($time_reminder->s === 0) {
                return '';
            } else {
                $time_reminder = $time_reminder->d . ' days '
                            . $time_reminder->h . ' hours '
                            . $time_reminder->i . ' minutes '
                            . $time_reminder->s . ' seconds ago';
                return $time_reminder;
            }
        } else {
            return null;
        }
    }
}