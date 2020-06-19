<?php

namespace app\models\database;

use Yii;

/**
 * This is the model class for table "account".
 *
 * @property int $id
 * @property string $name
 * @property string $identity
 * @property string $password
 * @property string|null $token
 * @property string|null $info
 * @property int|null $status
 * @property int|null $create_time
 */
class Account extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'account';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['name', 'identity', 'password'], 'required'],
            [['info'], 'safe'],
            [['status', 'create_time'], 'integer'],
            [['name'], 'string', 'max' => 45],
            [['identity', 'password'], 'string', 'max' => 125],
            [['token'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => 'Name',
            'identity' => 'Identity',
            'password' => 'Password',
            'token' => 'Token',
            'info' => 'Info',
            'status' => 'Status',
            'create_time' => 'Create Time',
        ];
    }
}
