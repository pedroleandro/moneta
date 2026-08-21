<?php

namespace App\Core;

final class LogEvent
{
    // Autenticação
    public const LOGIN_SUCCESS = "login_success";
    public const LOGIN_FAILED = "login_failed";
    public const LOGOUT = "logout";
    public const ACCOUNT_LOCKED = "account_locked";
    public const SESSION_REVOKED = "session_revoked";

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

    // Perfil
    public const PROFILE_UPDATED = "profile_updated";

    // Categorias
    public const CATEGORY_CREATED = "category_created";
    public const CATEGORY_UPDATED = "category_updated";
    public const CATEGORY_DELETED = "category_deleted";

    // Contas Bancárias
    public const BANK_ACCOUNT_CREATED = "bank_account_created";
    public const BANK_ACCOUNT_UPDATED = "bank_account_updated";
    public const BANK_ACCOUNT_DELETED = "bank_account_deleted";

    // Cartões de Crédito
    public const CREDIT_CARD_CREATED = "credit_card_created";
    public const CREDIT_CARD_UPDATED = "credit_card_updated";
    public const CREDIT_CARD_DELETED = "credit_card_deleted";

    // Quem Usa Meu Cartão
    public const CARD_USER_CREATED = "card_user_created";
    public const CARD_USER_UPDATED = "card_user_updated";
    public const CARD_USER_DELETED = "card_user_deleted";

    // Lançamentos
    public const TRANSACTION_CREATED = "transaction_created";
    public const TRANSACTION_UPDATED = "transaction_updated";
    public const TRANSACTION_DELETED = "transaction_deleted";

    // Transferências entre Contas
    public const TRANSFER_CREATED = "transfer_created";
    public const TRANSFER_DELETED = "transfer_deleted";

    public static function label(string $event): string
    {
        return match ($event) {
            // Autenticação
            self::LOGIN_SUCCESS => "Login realizado",
            self::LOGIN_FAILED => "Tentativa de login falhou",
            self::LOGOUT => "Logout realizado",
            self::ACCOUNT_LOCKED => "Conta bloqueada por tentativas excessivas",
            self::SESSION_REVOKED => "Sessão encerrada manualmente",

            // Cadastro / conta
            self::USER_REGISTERED => "Usuário cadastrado",
            self::EMAIL_VERIFIED => "E-mail verificado",
            self::EMAIL_CHANGED => "E-mail alterado",
            self::PASSWORD_CHANGED => "Senha alterada",
            self::PASSWORD_RESET_REQUESTED => "Redefinição de senha solicitada",
            self::ACCOUNT_DELETED => "Conta excluída",

            // Login social
            self::SOCIAL_ACCOUNT_LINKED => "Conta social vinculada",
            self::SOCIAL_ACCOUNT_UNLINKED => "Conta social desvinculada",

            // Perfil
            self::PROFILE_UPDATED => "Perfil atualizado",

            // Categorias
            self::CATEGORY_CREATED => "Categoria criada",
            self::CATEGORY_UPDATED => "Categoria atualizada",
            self::CATEGORY_DELETED => "Categoria excluída",

            // Contas bancárias
            self::BANK_ACCOUNT_CREATED => "Conta bancária criada",
            self::BANK_ACCOUNT_UPDATED => "Conta bancária atualizada",
            self::BANK_ACCOUNT_DELETED => "Conta bancária excluída",

            // Cartões de crédito
            self::CREDIT_CARD_CREATED => "Cartão de crédito criado",
            self::CREDIT_CARD_UPDATED => "Cartão de crédito atualizado",
            self::CREDIT_CARD_DELETED => "Cartão de crédito excluído",

            // Quem usa meu cartão
            self::CARD_USER_CREATED => "Pessoa adicionada ao cartão",
            self::CARD_USER_UPDATED => "Pessoa do cartão atualizada",
            self::CARD_USER_DELETED => "Pessoa removida do cartão",

            // Lançamentos
            self::TRANSACTION_CREATED => "Lançamento criado",
            self::TRANSACTION_UPDATED => "Lançamento atualizado",
            self::TRANSACTION_DELETED => "Lançamento excluído",

            // Transferências
            self::TRANSFER_CREATED => "Transferência realizada",
            self::TRANSFER_DELETED => "Transferência excluída",

            default => $event,
        };
    }
}