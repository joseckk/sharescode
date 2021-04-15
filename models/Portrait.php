<?php

namespace app\models;

use Yii;
use yii\bootstrap4\Html;
use yii\web\IdentityInterface;

/**
 * This is the model class for table "portrait".
 *
 * @property int $id
 * @property bool $is_admin
 * @property string $nickname
 * @property string $password
 * @property string $date_register
 * @property string $email
 * @property string $repository
 * @property string $prestige_port
 * @property string $sex
 * @property int $us_id
 *
 * @property Users $us
 * @property Prestige[] $prestiges
 * @property Query[] $queries
 */
class Portrait extends \yii\db\ActiveRecord implements IdentityInterface
{
    const SCENARIO_CREATE = 'create';
    const SCENARIO_UPDATE = 'update';
    const SCENARIO_REGISTER = 'register';

    public $password_repeat;
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'portrait';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['is_admin'], 'boolean'],
            [['nickname', 'password', 'date_register', 'email', 'repository', 'sex', 'us_id'], 'required'],
            [['date_register'], 'safe'],
            [['us_id'], 'default', 'value' => null],
            [['us_id'], 'integer'],
            [['nickname', 'password', 'email', 'repository', 'prestige_port'], 'string', 'max' => 255],
            [['sex'], 'string'],
            [['email'], 'unique'],
            [['nickname'], 'unique'],
            [['repository'], 'unique'],
            [['us_id'], 'exist', 'skipOnError' => true, 'targetClass' => Users::class, 'targetAttribute' => ['us_id' => 'id']],
            [['password', 'password_repeat'], 'required', 'on' => [self::SCENARIO_CREATE, self::SCENARIO_REGISTER]],
            [['password'], 'compare', 'on' => [self::SCENARIO_CREATE, self::SCENARIO_UPDATE, self::SCENARIO_REGISTER]],
            [['password_repeat'], 'safe', 'on' => [self::SCENARIO_UPDATE, self::SCENARIO_REGISTER]],
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
            'is_admin' => 'Is Admin',
            'nickname' => 'Nickname',
            'password' => 'Password',
            'date_register' => 'Date Register',
            'email' => 'Email',
            'repository' => 'Repository',
            'prestige_port' => 'Prestige Port',
            'sex' => 'Sex',
            'us_id' => 'User',
        ];
    }

    public function beforeSave($insert)
    {
        if (!parent::beforeSave($insert)) {
            return false;
        }

        if ($insert) {
            if ($this->scenario === self::SCENARIO_CREATE || self::SCENARIO_REGISTER) {
                goto salto;
            }
        } else {
            if ($this->scenario === self::SCENARIO_UPDATE) {
                if ($this->password === '') {
                    $this->password = $this->getOldAttribute('password');
                } else {
                    salto:
                    $this->password = Yii::$app->security
                        ->generatePasswordHash($this->password);
                }
            }
        }
        return true;
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

    public static function findIdentity($id)
    {
        return static::findOne($id);
    }

    public static function findIdentityByAccessToken($token, $type = null)
    {
    }

    public function getAuthKey()
    {
    }

    public function getId()
    {
        return $this->id;
    }

    public function validateAuthKey($authKey)
    {
    }

    public static function findByNickName($nickname)
    {
        return static::findOne(['nickname' => $nickname]);
    }

    public function validatePassword($password)
    {
        return Yii::$app->security
            ->validatePassword($password, $this->password);
    }

    public function devolverImg($model) {
        $sexo = $model->sex;
        if ($sexo !== null) {
            if ($sexo === 'Men') {
                $img = Html::img('@web/img/men.svg');
            } else {
                $img = Html::img('@web/img/woman.svg');
            }
        };
        return $img;
    }

    /**
     * Gets query for [[Us]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getUs()
    {
        return $this->hasOne(Users::class, ['id' => 'us_id'])->inverseOf('portraits');
    }

    /**
     * Gets query for [[Prestiges]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getPrestiges()
    {
        return $this->hasMany(Prestige::class, ['portrait_id' => 'id'])->inverseOf('portrait');
    }

    /**
     * Gets query for [[Queries]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getQueries()
    {
        return $this->hasMany(Query::class, ['portrait_id' => 'id'])->inverseOf('portrait');
    }

    /**
     * Gets query for [[Answers]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getAnswers()
    {
        return $this->hasMany(Answer::class, ['portrait_id' => 'id'])->inverseOf('portrait');
    }
}
