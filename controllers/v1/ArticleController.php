<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2020/6/16
 * Time: 16:40
 */

namespace app\controllers\v1;

use app\components\auth\AuthAccessToken;
use app\models\ArticleModel;
use yii\data\ActiveDataProvider;
use yii\rest\ActiveController;
use yii\web\HttpException;

class ArticleController extends ActiveController
{
    public $modelClass = 'app\models\ArticleModel';
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
        $query = ArticleModel::find()->andFilterWhere(['name' => $keyword])->orderBy($order_by);
        return new ActiveDataProvider([
            'query' => $query
        ]);
    }

    public function actionCreate()
    {
        $params = json_decode(file_get_contents("php://input"), true);
        $article = new ArticleModel();
        $article->setAttributes($params);
        $article->save();
        return $article;
    }


    public function actionView($id)
    {
        $article = ArticleModel::findOne(['id' => $id]);
        if ($article != null) {
            return $article;
        }
        throw new HttpException(404, '数据不存在');
    }

    public function actionUpdate($id)
    {
        $params = json_decode(file_get_contents("php://input"), true);
        $articles = ArticleModel::find()->where(['id' => $id])->one();
        if ($articles == null) {
            throw new HttpException(404, '数据不存在');
        }
        $articles->setAttributes($params);
        $articles->save();
        return $articles;
    }

    public function actionDelete($id)
    {
        $article = ArticleModel::find()->where(['id' => $id])->one();
        if ($article == null) {
            throw new HttpException(404, "数据不存在");
        }
        $article->delete();
        return ['result' => true];
    }

}