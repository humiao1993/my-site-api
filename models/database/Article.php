<?php

namespace app\models\database;

use Yii;

/**
 * This is the model class for table "article".
 *
 * @property int $id
 * @property string $name
 * @property string|null $author
 * @property string|null $abstract
 * @property string|null $content
 * @property int|null $status
 * @property int|null $update_time
 * @property int|null $create_time
 */
class Article extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'article';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['name'], 'required'],
            [['content'], 'string'],
            [['status', 'update_time', 'create_time'], 'integer'],
            [['name', 'abstract'], 'string', 'max' => 255],
            [['author'], 'string', 'max' => 45],
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
            'author' => 'Author',
            'abstract' => 'Abstract',
            'content' => 'Content',
            'status' => 'Status',
            'update_time' => 'Update Time',
            'create_time' => 'Create Time',
        ];
    }
}
