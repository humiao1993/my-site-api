<?php
namespace app\models;

use app\models\database\Article;

/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2020/6/16
 * Time: 16:34
 */
class ArticleModel extends Article
{
    public function beforeSave($insert)
    {
        $this->create_time = $this->create_time ? $this->create_time : time();
        $this->update_time = time();
        return parent::beforeSave($insert);
    }
}