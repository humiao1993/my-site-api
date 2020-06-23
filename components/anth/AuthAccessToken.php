<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2020/6/23
 * Time: 14:24
 */
namespace app\components\auth;

use app\models\AccountModel;
use Yii;
use yii\filters\auth\AuthMethod;
use yii\web\HttpException;

class AuthAccessToken extends AuthMethod
{
    public $permission = true;
    public $permissionExcept = [];

    /**
     * @inheritdoc
     */
    public function authenticate($user, $request, $response)
    {
        $header = Yii::$app->request->headers;
        $accessToken = $header->get('token');
        if ($accessToken == null) {
            throw new HttpException(412, "必须包含token信息");
        }
        $identity = AccountModel::findIdentityByAccessToken($accessToken);
        if ($identity) {
            if ($identity->status != AccountModel::STATUS_ON) {
                throw new HttpException(406, $identity->getStatusText());
            }
            $user->setIdentity($identity);
            return $identity;
        } else {
            throw new HttpException(401, "无效的授权信息");
        }
        return null;
    }
}