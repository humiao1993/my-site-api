<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2020/6/17
 * Time: 14:52
 */

namespace app\controllers\v1;

use app\components\auth\AuthAccessToken;
use app\models\AccountModel;
use Yii;
use yii\rest\ActiveController;
use yii\web\HttpException;

class AccountController extends ActiveController
{

    public $modelClass = 'app\models\AccountModel';
    public $serializer = ['class' => 'yii\rest\Serializer', 'collectionEnvelope' => 'items'];

    public function actions()
    {
        $actions = parent::actions();
        unset($actions['create']);
        unset($actions['view']);
        unset($actions['index']);
        unset($actions['update']);
        unset($actions['delete']);
    }

    public function behaviors()
    {
        $behaviors = parent::behaviors();
        unset($behaviors['authenticator']);
        $behaviors['authenticator'] = [
            'class' => AuthAccessToken::class,
            'permission' => false,
            'except' => ['login', 'create']
        ];
        return $behaviors;
    }

    public function actionCreate()
    {
        $params = json_decode(file_get_contents("php://input"), true);
        $this->verifyCreateParams($params);
        $account = new AccountModel();
        $account->setAttributes($params);
        $account->password = Yii::$app->getSecurity()->generatePasswordHash($account->password);
        $account->save();
        return [
            'account' => $account,
            'token' => $account->getAuthKey(),
            'error' => $account->getErrors()
        ];
    }

    public function actionUpdate($id)
    {
        $account = AccountModel::find()->where(['id' => Yii::$app->user->identity->id])->andWhere(['status' => AccountModel::STATUS_ON])->one();
        if ($account == null) {
            throw new HttpException(404, "账号不存在或者账号异常");
        }
        $params = json_decode(file_get_contents("php://input"), true);
        $params = $this->cleanSecurityFields($params);
        $params = $this->updatePassword($params, $account);
        $account->setAttributes($params);
        $account->save();
        return $account;
    }

    public function actionView($id)
    {
        $account = AccountModel::findOne(['id' => Yii::$app->user->identity->id]);
        if ($account != null) {
            return $account;
        }
        throw new HttpException(500, '系统出错');
    }

    public function actionLogin()
    {
        $params = json_decode(file_get_contents("php://input"), true);
        $account = $this->verifyLoginParams($params);
        return [
            'account' => $account,
            'token' => $account->getAuthKey(),
        ];
    }

    public function actionLogout()
    {
        $account = AccountModel::findOne(['id' => Yii::$app->user->identity->id]);
        if ($account) {
            $account->refreshAuthKey();
        }
        return ['result' => true];
    }


    private function verifyCreateParams($params)
    {
        if (!isset($params['name']) || !isset($params['identity']) || !isset($params['password'])) {
            throw new HttpException(412, "参数不完整", 412);
        }
        if (strlen($params['password']) < 6 || strlen($params['password']) > 20) {
            throw new HttpException(412, "密码不符合规则", 412);
        }
        if (AccountModel::find()->where(['identity' => $params['identity']])->andWhere(['!=', 'status', AccountModel::STATUS_DEL])->exists()) {
            throw new HttpException(409, "该账号已存在");
        }
    }

    private function cleanSecurityFields($params)
    {
        $result = [];
        if (isset($params['info'])) {
            $result['info'] = $params['info'];
        }
        if (isset($params['password'])) {
            $result['password'] = $params['password'];
        }
        if (isset($params['old-password'])) {
            $result['old-password'] = $params['old-password'];
        }
        return $result;
    }

    private function updatePassword($params, $user)
    {
        if (isset($params['password'])) {
            if (strlen($params['password']) < 6 || strlen($params['password']) > 20) {
                throw new HttpException(412, '密码不符合规则', 412);
            }
            if (!isset($params['old-password'])) {
                throw new HttpException(412, "请输入旧密码", 412);
            } else if (!Yii::$app->getSecurity()->validatePassword($params['old-password'], $user->password)) {
                throw new HttpException(406, "旧密码错误");
            }
            $params['password'] = Yii::$app->getSecurity()->generatePasswordHash($params['password']);
        }
    }

    private function verifyLoginParams($params)
    {
        if (!isset($params['identity']) || !isset($params['password'])) {
            throw new HttpException(412, "参数不完整");
        }
        $account = AccountModel::find()->where(['identity' => $params['identity']])->andWhere(['!=', 'status', AccountModel::STATUS_DEL])->one();
        if ($account == null) {
            throw new HttpException(406, "用户不存在");
        }
        if (!Yii::$app->getSecurity()->validatePassword($params['password'], $account->password)) {
            throw new HttpException(406, "密码错误");
        }
        if ($account->status == AccountModel::STATUS_OFF) {
            throw new HttpException(406, "该用户已被停用");
        }
        return $account;
    }

}