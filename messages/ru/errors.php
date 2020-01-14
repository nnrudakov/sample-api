<?php

declare(strict_types=1);

/**
 * Текстовые сообщения ошибок.
 */
return [
    'user_not_found'               => 'Пользователь не найден либо заблокирован',
    'password_reset_invalid_token' => 'Неверный ключ восстановления пароля или время его действия истекло',
    'password_reset_request_fail'  => 'К сожалению, не удалось отправить письмо на указанный адрес',
    'password_reset_no_token'      => 'Не указан ключ восстановления пароля',
    'password_reset_no_password'   => 'Не указан новый пароль',
    'login_no_email'               => 'E-mail не указан или неверный',
    'login_no_password'            => 'Не указан пароль',
    'login_invalid_password'       => 'Неверный пароль',
    'login_fail'                   => 'Войти не удалось',
    'logout_fail'                  => 'Выйти не удалось',
    'users_invalid_company'        => 'Организации с ID={id} не существует, либо вам запрещено добавлять пользователей в эту организацию',
    'role_empty'                   => 'Не указана роль',
    'company_empty'                => 'Не указана организация',
    'access_empty'                 => 'Не указаны разрешения',
    'user_company_mismatch'        => 'В данной организации такого пользователя нет',
];
