<?php

namespace App\Core;

/**
 * Catálogo central dos eventos de auditoria do sistema.
 * Usar sempre essas constantes ao chamar AuditLog::record(),
 * nunca strings soltas — evita erro de digitação e mantém
 * um único lugar pra consultar "quais eventos existem".
 */
final class LogEvent
{
    // Autenticação
    public const LOGIN_SUCCESS = "login_success";
    public const LOGIN_FAILED = "login_failed";
    public const LOGOUT = "logout";
    public const ACCOUNT_LOCKED = "account_locked";

    // Cadastro / conta
    public const USER_REGISTERED = "user_registered";
    public const EMAIL_VERIFIED = "email_verified";
    public const EMAIL_CHANGED = "email_changed";
    public const PASSWORD_CHANGED = "password_changed";
    public const PASSWORD_RESET_REQUESTED = "password_reset_requested";
    public const ACCOUNT_DELETED = "account_deleted";

    // Login Social
    public const SOCIAL_ACCOUNT_LINKED = "social_account_linked";
    public const SOCIAL_ACCOUNT_UNLINKED = "social_account_unlinked";

    // Dados Financeiros Sensíveis
    public const BANK_ACCOUNT_CREATED = "bank_account_created";
    public const BANK_ACCOUNT_DELETED = "bank_account_deleted";
    public const CREDIT_CARD_CREATED = "credit_card_created";
    public const CREDIT_CARD_DELETED = "credit_card_deleted";

    public const CATEGORY_CREATED = "category_created";
    public const CATEGORY_UPDATED = "category_updated";
    public const CATEGORY_DELETED = "category_deleted";

    // Perfil
    public const PROFILE_UPDATED = "profile_updated";

    public static function label(string $event): string
    {
        return match ($event) {
            self::LOGIN_SUCCESS => "Login realizado",
            self::LOGIN_FAILED => "Tentativa de login falhou",
            self::LOGOUT => "Logout realizado",
            self::ACCOUNT_LOCKED => "Conta bloqueada por tentativas excessivas",
            self::USER_REGISTERED => "Usuário cadastrado",
            self::EMAIL_VERIFIED => "E-mail verificado",
            self::EMAIL_CHANGED => "E-mail alterado",
            self::PASSWORD_CHANGED => "Senha alterada",
            self::PASSWORD_RESET_REQUESTED => "Redefinição de senha solicitada",
            self::ACCOUNT_DELETED => "Conta excluída",
            self::SOCIAL_ACCOUNT_LINKED => "Conta social vinculada",
            self::SOCIAL_ACCOUNT_UNLINKED => "Conta social desvinculada",
            self::BANK_ACCOUNT_CREATED => "Conta bancária criada",
            self::BANK_ACCOUNT_DELETED => "Conta bancária excluída",
            self::CREDIT_CARD_CREATED => "Cartão de crédito criado",
            self::CREDIT_CARD_DELETED => "Cartão de crédito excluído",
            self::PROFILE_UPDATED => "Perfil atualizado",
            self::CATEGORY_CREATED => "Categoria criada",
            self::CATEGORY_UPDATED => "Categoria atualizada",
            self::CATEGORY_DELETED => "Categoria excluída",
            default => $event,
        };
    }
}