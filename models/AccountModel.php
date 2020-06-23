<?php
namespace app\models;

use app\components\CommonUtils;
use app\models\database\Account;
use Yii;
use yii\web\IdentityInterface;

/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2020/6/16
 * Time: 16:34
 */
class AccountModel extends Account implements IdentityInterface
{
    const STATUS_DEL = 0, STATUS_INIT = 1, STATUS_ON = 2, STATUS_OFF = 3;

    public function beforeSave($insert)
    {
        $this->create_time = $this->create_time ? $this->create_time : time();
        return parent::beforeSave($insert);
    }

    /**
     * Finds an identity by the given ID.
     * @param string|int $id the ID to be looked for
     * @return IdentityInterface|null the identity object that matches the given ID.
     * Null should be returned if such an identity cannot be found
     * or the identity is not in an active state (disabled, deleted, etc.)
     */
    public static function findIdentity($id)
    {
        return AccountModel::findOne(['id' => $id]);
    }

    /**
     * Finds an identity by the given token.
     * @param mixed $token the token to be looked for
     * @param mixed $type the type of the token. The value of this parameter depends on the implementation.
     * For example, [[\yii\filters\auth\HttpBearerAuth]] will set this parameter to be `yii\filters\auth\HttpBearerAuth`.
     * @return array|\yii\db\ActiveRecord
     * Null should be returned if such an identity cannot be found
     * or the identity is not in an active state (disabled, deleted, etc.)
     */
    public static function findIdentityByAccessToken($token, $type = null)
    {
        $cacheKey = 'auth-'.$token;
        $account = Yii::$app->cache->get($cacheKey);
        if ($account) {
            return $account;
        }
        $account = AccountModel::find()->where(['token' => $token])->one();
        if ($account != null) {
            Yii::$app->cache->set($cacheKey, $account, Yii::$app->params['cache_expire']);
        }
        return $account;
    }

    /**
     * Returns an ID that can uniquely identify a user identity.
     * @return string|int an ID that uniquely identifies a user identity.
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Returns a key that can be used to check the validity of a given identity ID.
     *
     * The key should be unique for each individual user, and should be persistent
     * so that it can be used to check the validity of the user identity.
     *
     * The space of such keys should be big enough to defeat potential identity attacks.
     *
     * This is required if [[User::enableAutoLogin]] is enabled. The returned key will be stored on the
     * client side as a cookie and will be used to authenticate user even if PHP session has been expired.
     *
     * Make sure to invalidate earlier issued authKeys when you implement force user logout, password change and
     * other scenarios, that require forceful access revocation for old sessions.
     *
     * @return string a key that is used to check the validity of a given identity ID.
     * @see validateAuthKey()
     */
    public function getAuthKey()
    {
        $this->refreshAuthKey();
        return $this->token;
    }

    /**
     * Validates the given auth key.
     *
     * This is required if [[User::enableAutoLogin]] is enabled.
     * @param string $authKey the given auth key
     * @return bool whether the given auth key is valid.
     * @see getAuthKey()
     */
    public function validateAuthKey($authKey)
    {
        return true;
    }

    public function getStatusText()
    {
        $result = '未知错误';
        switch ($this->status) {
            case self::STATUS_DEL:
                $result = '不存在';
                break;
            case self::STATUS_ON:
                $result = '正常';
                break;
            case self::STATUS_OFF:
                $result = '已停用';
                break;
            case self::STATUS_INIT:
                $result = '未激活';
                break;
            default:
                break;
        }
        return $result;
    }

    public function refreshAuthKey()
    {
        if ($this->token != null) {
            Yii::$app->cache->delete('auth-' .$this->token);
        }
        $token = CommonUtils::createGuid();
        $this->token = $token;
        $this->save();
    }
}