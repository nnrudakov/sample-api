<?php

/**
 * Copyright (c) 2020. Nikolaj Rudakov
 */

declare(strict_types=1);

namespace app\models\db;

use Yii;
use yii\db\{ActiveRecord, ArrayExpression, Expression};
use yii\base\{Exception, InvalidArgumentException};
use yii\behaviors\TimestampBehavior;
use app\components\behaviors\{AssignRoleBehavior, PasswordSendBehavior, ResetPermissionsBehavior};
use app\components\validators\UserCompaniesValidator;

/**
 * @OA\Schema(
 *     schema="email",
 *     title="E-mail",
 *     type="string"
 * )
 * @OA\Schema(
 *     schema="name",
 *     title="Имя",
 *     type="string"
 * )
 * @OA\Schema(
 *     schema="User",
 *     title="Пользователь",
 *     @OA\Property(
 *          property="id",
 *          ref="#/components/schemas/id"
 *     ),
 *     @OA\Property(
 *          property="email",
 *          ref="#/components/schemas/email"
 *     ),
 *     @OA\Property(
 *          property="name",
 *          title="Имя",
 *          type="string",
 *     ),
 *     @OA\Property(
 *          property="enabled",
 *          title="Активность",
 *          description="Является ли пользователь не заблокированным.",
 *          type="boolean",
 *     ),
 *     @OA\Property(
 *          property="role",
 *          ref="#/components/schemas/role"
 *     ),
 * )
 * @OA\Schema(
 *     schema="UserShort",
 *     title="Пользователь",
 *     @OA\Property(
 *          property="id",
 *          ref="#/components/schemas/id"
 *     ),
 *     @OA\Property(
 *          property="email",
 *          ref="#/components/schemas/email"
 *     ),
 *     @OA\Property(
 *          property="name",
 *          title="Имя",
 *          type="string",
 *     ),
 * )
 */
/**
 * Модель таблицы "users".
 *
 * @property integer $id                   Идентификатор.
 * @property string  $email                E-mail.
 * @property string  $password_hash        Пароль.
 * @property string  $password_reset_token Токен восстановления пароля.
 * @property string  $name                 Имя.
 * @property boolean $enabled              Активен.
 * @property string  $role                 Роль.
 * @property array   $companies            Организации.
 * @property string  $created_at           Дата создания.
 * @property string  $updated_at           Дата обновления.
 * @property string  $password             Новый пароль.
 *
 * @package    app\models\db
 * @author     Nikolaj Rudakov <nnrudakov@gmail.com>
 * @copyright  2020 Nikolaj Rudakov
 */
class User extends ActiveRecord
{
    /**
     * @var integer Идентификатор главного администратора.
     */
    public const SUPER_ID = 1;

    /**
     * @var string главного администратора.
     */
    public const ROLE_SUPER_ADMIN = 'superAdmin';
    /**
     * @var string Роль администратора.
     */
    public const ROLE_ADMIN = 'admin';
    /**
     * @var string Роль пользователя.
     */
    public const ROLE_USER = 'user';

    /**
     * @var string Сценарий восстановления пароля.
     */
    public const SCENARIO_PASSWORD_RESET = 'password_reset';
    /**
     * @var string Сценарий обновления главным администратором.
     */
    public const SCENARIO_UPDATE_SUPER = 'update_super';
    /**
     * @var string Сценарий обновления пользователя администратором.
     */
    public const SCENARIO_UPDATE_ADMIN = 'update_admin';
    /**
     * @var string Сценарий обновления своих данных.
     */
    public const SCENARIO_UPDATE_SELF = 'update_self';

    /**
     * @var string Новый пароль.
     */
    public ?string $password = null;
    /**
     * @var string Повтор пароля.
     */
    public ?string $password_repeat = null;

    /**
     * {@inheritdoc}
     */
    public static function tableName(): string
    {
        return 'users';
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
                'class' => AssignRoleBehavior::class,
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function rules(): array
    {
        return [
            [['email', 'name', 'role', 'companies'], 'required'],
            [['email'], 'email'],
            [['email'], 'trim'],
            [['email'], 'string', 'max' => 50],
            [['email'], 'filter', 'filter' => 'strtolower'],
            [['email'], 'unique'],
            [
                ['password'],
                'match',
                'pattern' => '/^[\w!?@#$%^&*_+={}\[\]()\/\\\"\'`~,;:.<>|-]+$/i',
                'message' => 'Разрешается использовать только буквы английского алфавита, цифры и спец. символы.'
            ],
            [['password'], 'string', 'min' => 8],
            [['name'], 'trim'],
            [['name'], 'string', 'max' => 50],
            [['enabled'], 'boolean'],
            [['enabled'], 'default', 'value' => true],
            [['role'], 'in', 'range' => [static::ROLE_ADMIN, static::ROLE_USER], 'strict' => true],
            [['password_hash'], 'string', 'max' => 255],
            [['password_repeat'], 'compare', 'compareAttribute' => 'password', 'on' => [static::SCENARIO_UPDATE_SELF]],
            [
                ['password_repeat'],
                'required',
                'when' => static function ($model, /** @noinspection PhpUnusedParameterInspection */ $attribute): bool {
                    /** @var User $model */
                    return !empty($model->password);
                },
                'on' => [static::SCENARIO_UPDATE_SELF]
            ],
            [['companies'], UserCompaniesValidator::class],
            [['created_at', 'updated_at', 'password', 'password_repeat', 'password_reset_token'], 'safe'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function scenarios(): array
    {
        $scenarios = parent::scenarios();
        $scenarios[static::SCENARIO_PASSWORD_RESET] = ['password', 'password_reset_token', 'updated_at'];
        $scenarios[static::SCENARIO_UPDATE_SUPER] = ['email', 'name', 'role', 'enabled', 'companies', 'updated_at'];
        $scenarios[static::SCENARIO_UPDATE_ADMIN] = ['email', 'name', 'enabled', 'companies', 'updated_at'];
        $scenarios[static::SCENARIO_UPDATE_SELF] = ['email', 'name', 'password', 'password_repeat', 'updated_at'];

        return $scenarios;
    }

    /**
     * {@inheritdoc}
     */
    public function beforeValidate(): bool
    {
        if ($this->password) {
            $this->setPassword($this->password);
        }
        if ($this->companies) {
            $this->normalizeCompanies();
        }

        return parent::beforeValidate();
    }

    /**
     * {@inheritdoc}
     */
    public function beforeSave($insert): bool
    {
        if ($insert) {
            $password = Yii::$app->security->generateRandomString(8);
            $this->setPassword($password);
            $this->attachBehavior('send_password', ['class' => PasswordSendBehavior::class, 'password' => $password]);
        }

        return parent::beforeSave($insert);
    }

    /**
     * {@inheritdoc}
     */
    public function afterSave($insert, $changedAttributes): void
    {
        if (!$insert && (isset($changedAttributes['role']) || isset($changedAttributes['companies']))) {
            $this->attachBehavior('reset_permissions', [
                'class' => ResetPermissionsBehavior::class,
                'roleChanged' => isset($changedAttributes['role']),
                'oldCompanies' => $changedAttributes['companies'] ?? null
            ]);
        }

        parent::afterSave($insert, $changedAttributes);
    }

    /**
     * Установка пароля.
     *
     * @param string $password Пароль.
     *
     * @throws Exception
     */
    public function setPassword($password): void
    {
        $this->password_hash = Yii::$app->security->generatePasswordHash($password);
    }

    /**
     * Для роли `Пользователь` может быть указана только одна организация. Метод устанавливает первую из списка.
     */
    private function normalizeCompanies(): void
    {
        if (!($this->companies instanceof ArrayExpression)) {
            $companies = \array_unique((array) $this->companies);
            if ($this->isUser() && \count($companies) > 1) {
                $companies = [$companies[0]];
            }
            \sort($companies);
            $this->companies = $companies;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels(): array
    {
        return [
            'email'           => 'E-mail',
            'password'        => 'Пароль',
            'password_repeat' => 'Повторите пароль',
            'name'            => 'Имя',
            'role'            => 'Роль',
            'companies'       => 'Организации',
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function fields(): array
    {
        return ['id', 'email', 'name'];
    }

    /**
     * {@inheritdoc}
     */
    public function extraFields(): array
    {
        return ['enabled', 'role'];
    }

    /**
     * Выбор пользователя по E-mail.
     *
     * @param string $email
     *
     * @return User|\app\models\User|null
     */
    public static function findByEmail($email): ?User
    {
        $user = static::find()->select(['id', 'email', 'password_hash', 'role', 'companies'])
            ->where(['email' => $email, 'enabled' => true])->one();
        $company = Company::find()->select(['id'])->where(['enabled' => true]);
        if ($user && $user->isUser() && !$company->andWhere(['id' => $user->companies[0]])->exists()) {
            return null;
        }

        return $user;
    }

    /**
     * Поиск пользователя по токену восстановления пароля.
     *
     * @param string $token Токен.
     *
     * @return User|null
     */
    public static function findByPasswordResetToken($token): ?User
    {
        if (!static::isPasswordResetTokenValid($token)) {
            return null;
        }

        return static::find()->select(['id', 'email', 'password_hash', 'password_reset_token', 'updated_at'])
            ->where(['password_reset_token' => $token, 'enabled' => true])->one();
    }

    /**
     * Определение валидности токена восстановления пароля.
     *
     * @param string $token Токен.
     *
     * @return bool
     */
    public static function isPasswordResetTokenValid($token): bool
    {
        if (empty($token)) {
            return false;
        }

        $timestamp = (int) substr($token, strrpos($token, '_') + 1);
        $expire = Yii::$app->params['passwordResetTokenExpire'];

        return $timestamp + $expire >= time();
    }

    /**
     * Проверка пароля.
     *
     * @param string $password Пароль.
     *
     * @return bool
     *
     * @throws InvalidArgumentException
     */
    public function validatePassword($password): bool
    {
        return Yii::$app->security->validatePassword($password, $this->password_hash);
    }

    /**
     * Создание токена для восстановления пароля.
     */
    public function generatePasswordResetToken():void
    {
        $this->password_reset_token = Yii::$app->security->generateRandomString() . '_' . time();
        $this->update(false, ['password_reset_token', 'updated_at']);
    }

    /**
     * Является ли пользователь главным администратором.
     *
     * @return bool
     */
    public function isSuperAdmin(): bool
    {
        return $this->id === static::SUPER_ID;
    }

    /**
     * Является ли пользователь администратором.
     *
     * @return bool
     */
    public function isAdmin(): bool
    {
        return $this->role === static::ROLE_ADMIN;
    }

    /**
     * Является ли пользователь обычным пользователем.
     *
     * @return bool
     */
    public function isUser(): bool
    {
        return $this->role === static::ROLE_USER;
    }
}
