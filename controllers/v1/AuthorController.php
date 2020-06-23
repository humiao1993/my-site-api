<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2020/6/16
 * Time: 16:40
 */

namespace app\controllers\v1;

use app\components\auth\AuthAccessToken;
use app\models\AuthorModel;
use yii\data\ActiveDataProvider;
use yii\rest\ActiveController;
use yii\web\HttpException;

class AuthorController extends ActiveController
{
    public $modelClass = 'app\models\AuthorModel';
    public $serializer = ['class' => 'yii\rest\Serializer', 'collectionEnvelope' => 'items'];

    public function actions()
    {
        $actions = parent::actions();
        unset($actions['create']);
        unset($actions['view']);
        unset($actions['index']);
        unset($actions['update']);
        unset($actions['delete']);
        return $actions;
    }

    public function behaviors()
    {
        $behaviors = parent::behaviors();
        unset($behaviors['authenticator']);
        $behaviors['authenticator'] = [
            'class' => AuthAccessToken::class,
            'permission' => false,
        ];
        return $behaviors;
    }

    public function actionIndex($keyword = null, $order_by = 'id DESC')
    {
        $query = AuthorModel::find()->andFilterWhere(['name' => $keyword])->orderBy($order_by);
        return new ActiveDataProvider([
            'query' => $query
        ]);
    }

    public function actionCreate()
    {
        $params = json_decode(file_get_contents("php://input"), true);
        $author = new AuthorModel();
        $author->setAttributes($params);
        $author->save();
        return $author;
    }


    public function actionView($id)
    {
        $author = AuthorModel::findOne(['id' => $id]);
        if ($author != null) {
            return $author;
        }
        throw new HttpException(404, '数据不存在');
    }

    public function actionUpdate($id)
    {
        $params = json_decode(file_get_contents("php://input"), true);
        $author = AuthorModel::find()->where(['id' => $id])->one();
        if ($author == null) {
            throw new HttpException(404, '数据不存在');
        }
        $author->setAttributes($params);
        $author->save();
        return $author;
    }

    public function actionDelete($id)
    {
        $author = AuthorModel::find()->where(['id' => $id])->one();
        if ($author == null) {
            throw new HttpException(404, "数据不存在");
        }
        $author->delete();
        return ['result' => true];
    }

}