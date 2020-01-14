<?php

/**
 * Copyright (c) 2020. Nikolaj Rudakov
 */

declare(strict_types=1);

namespace app\models\db;

use Yii;
use yii\db\{ActiveRecord, Expression};
use yii\behaviors\TimestampBehavior;
use app\components\behaviors\CreatePermissionsBehavior;
use app\components\validators\PhoneValidator;

/**
 * @OA\Schema(
 *     schema="inn",
 *     title="ИНН",
 *     type="string"
 * )
 * @OA\Schema(
 *     schema="ogrn",
 *     title="ОГРН",
 *     type="string"
 * )
 * @OA\Schema(
 *     schema="address",
 *     title="Адрес",
 *     type="string"
 * )
 * @OA\Schema(
 *     schema="phone",
 *     title="Телефон",
 *     type="string",
 *     description="Формат номера `+7XXXXXXXXXX`."
 * )
 * @OA\Schema(
 *     schema="person",
 *     title="Ответственное лицо",
 *     description="ФИО ответственного лица.",
 *     type="string",
 * )
 * @OA\Schema(
 *     schema="contact",
 *     title="Контакт ответственного лица",
 *     type="string",
 * )
 * @OA\Schema(
 *     schema="enabled",
 *     title="Активность",
 *     description="Является ли организация не заблокированной.",
 *     type="boolean",
 * )
 * @OA\Schema(
 *     schema="scada_host",
 *     title="Хост БД SCADA",
 *     type="string"
 * )
 * @OA\Schema(
 *     schema="scada_port",
 *     title="Порт БД SCADA",
 *     type="integer",
 *     format="int32"
 * )
 * @OA\Schema(
 *     schema="scada_db",
 *     title="Имя БД SCADA",
 *     type="string"
 * )
 * @OA\Schema(
 *     schema="scada_user",
 *     title="Пользователь БД SCADA",
 *     type="string"
 * )
 * @OA\Schema(
 *     schema="scada_password",
 *     title="Пароль БД SCADA",
 *     type="string",
 *     format="password"
 * )
 *
 * @OA\Schema(
 *     schema="CompanyShort",
 *     title="Организация",
 *     @OA\Property(
 *          property="id",
 *          ref="#/components/schemas/id"
 *     ),
 *     @OA\Property(
 *          property="title",
 *          ref="#/components/schemas/title"
 *     ),
 *     @OA\Property(
 *          property="inn",
 *          ref="#/components/schemas/inn"
 *     ),
 *     @OA\Property(
 *          property="address",
 *          ref="#/components/schemas/address"
 *     ),
 *     @OA\Property(
 *          property="phone",
 *          ref="#/components/schemas/phone"
 *     ),
 *     @OA\Property(
 *          property="person",
 *          ref="#/components/schemas/person"
 *     ),
 *     @OA\Property(
 *          property="contact",
 *          ref="#/components/schemas/contact"
 *     ),
 *     @OA\Property(
 *          property="enabled",
 *          ref="#/components/schemas/enabled"
 *     ),
 * )
 * @OA\Schema(
 *     schema="CompanyFull",
 *     title="Организация",
 *     @OA\Property(
 *          property="id",
 *          ref="#/components/schemas/id"
 *     ),
 *     @OA\Property(
 *          property="title",
 *          ref="#/components/schemas/title"
 *     ),
 *     @OA\Property(
 *          property="inn",
 *          ref="#/components/schemas/inn"
 *     ),
 *     @OA\Property(
 *          property="ogrn",
 *          ref="#/components/schemas/ogrn"
 *     ),
 *     @OA\Property(
 *          property="address",
 *          ref="#/components/schemas/address"
 *     ),
 *     @OA\Property(
 *          property="phone",
 *          ref="#/components/schemas/phone"
 *     ),
 *     @OA\Property(
 *          property="person",
 *          ref="#/components/schemas/person"
 *     ),
 *     @OA\Property(
 *          property="contact",
 *          ref="#/components/schemas/contact"
 *     ),
 *     @OA\Property(
 *          property="enabled",
 *          ref="#/components/schemas/enabled"
 *     ),
 *     @OA\Property(
 *          property="scada_host",
 *          ref="#/components/schemas/scada_host"
 *     ),
 *     @OA\Property(
 *          property="scada_port",
 *          ref="#/components/schemas/scada_port"
 *     ),
 *     @OA\Property(
 *          property="scada_db",
 *          ref="#/components/schemas/scada_db"
 *     ),
 *     @OA\Property(
 *          property="scada_user",
 *          ref="#/components/schemas/scada_user"
 *     ),
 *     @OA\Property(
 *          property="scada_password",
 *          ref="#/components/schemas/scada_password"
 *     ),
 *     @OA\Property(
 *          property="comment",
 *          ref="#/components/schemas/comment"
 *     ),
 * )
 */
/**
 * Модель таблицы "companies".
 *
 * @property integer $id             Идентификатор.
 * @property string  $title          Наименование.
 * @property string  $inn            ИНН.
 * @property string  $ogrn           ОГРН.
 * @property string  $address        Адрес.
 * @property string  $phone          Телефон.
 * @property string  $person         Ответственное лицо.
 * @property string  $contact        Контакт ответственного лица.
 * @property boolean $enabled        Активна.
 * @property string  $comment        Комментарий.
 * @property string  $scada_host     Хост БД SCADA.
 * @property integer $scada_port     Порт БД SCADA.
 * @property string  $scada_db       Имя БД SCADA.
 * @property string  $scada_user     Пользователь БД SCADA.
 * @property string  $scada_password Пароль БД SCADA.
 * @property string  $created_at     Дата создания.
 * @property string  $updated_at     Дата обновления.
 *
 * @package    app\models\db
 * @author     Nikolaj Rudakov <nnrudakov@gmail.com>
 * @copyright  2020 Nikolaj Rudakov
 */
class Company extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName(): string
    {
        return 'companies';
    }

    /**
     * {@inheritdoc}
     */
    public function behaviors(): array
    {
        return [
            [
                'class' => TimestampBehavior::class,
                'value' => new Expression('NOW()'),
            ],
            [
                'class' => CreatePermissionsBehavior::class,
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function rules(): array
    {
        return [
            [
                [
                    'title', 'inn', 'ogrn', 'address', 'phone', 'person', 'contact', 'scada_host', 'scada_port',
                    'scada_db', 'scada_user', 'scada_password'
                ],
                'required'
            ],
            [['inn'], 'unique'],
            [['ogrn'], 'unique'],
            [['enabled'], 'boolean'],
            [['scada_port'], 'integer'],
            [['title', 'person'], 'string', 'max' => 100],
            [['inn'], 'string', 'max' => 10],
            [['ogrn'], 'string', 'max' => 13],
            [['address', 'contact'], 'string', 'max' => 256],
            [['phone'], PhoneValidator::class],
            [['comment'], 'string', 'max' => 512],
            [['title', 'inn', 'ogrn', 'address', 'person', 'contact', 'comment'], 'trim'],
            [['scada_host', 'scada_db', 'scada_user', 'scada_password'], 'string', 'max' => 50],
            [['created_at', 'updated_at'], 'safe'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels(): array
    {
        return [
            'title' => 'Наименование',
            'inn' => 'ИНН',
            'ogrn' => 'ОГРН',
            'address' => 'Адрес',
            'phone' => 'Телефон',
            'person' => 'Ответственное лицо',
            'contact' => 'Контакт ответственного лица',
            'scada_host' => 'Хост БД SCADA',
            'scada_port' => 'Порт БД SCADA',
            'scada_db' => 'Имя БД SCADA',
            'scada_user' => 'Пользователь БД SCADA',
            'scada_password' => 'Пароль БД SCADA',
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function fields(): array
    {
        return ['id', 'title', 'inn', 'address', 'phone', 'person', 'contact', 'enabled'];
    }

    /**
     * {@inheritdoc}
     */
    public function extraFields(): array
    {
        /** @noinspection PhpUndefinedMethodInspection */
        return Yii::$app->user->getIdentity()->isSuperAdmin()
            ? ['ogrn', 'comment', 'scada_host', 'scada_port', 'scada_db', 'scada_user', 'scada_password']
            : ['ogrn', 'comment'];
    }
}
