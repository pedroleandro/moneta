-- =========================================================
-- MONETA — SCHEMA COMPLETO
-- =========================================================

-- =========================================
-- USERS
-- =========================================
CREATE TABLE users
(
    id                BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name              VARCHAR(150) NOT NULL,
    email             VARCHAR(150) NOT NULL UNIQUE,
    password          VARCHAR(255) NULL, -- nulo se o usuário só usa login social
    avatar            VARCHAR(255) NULL,
    email_verified_at DATETIME NULL,
    created_at        DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at        DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at        DATETIME NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =========================================
-- SOCIAL ACCOUNTS (login social: Google/Facebook)
-- =========================================
CREATE TABLE social_accounts
(
    id            BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id       BIGINT UNSIGNED NOT NULL,
    provider      ENUM('google', 'facebook') NOT NULL,
    provider_id   VARCHAR(255) NOT NULL,
    access_token  TEXT NULL,
    refresh_token TEXT NULL,
    created_at    DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at    DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at    DATETIME NULL,
    UNIQUE KEY uq_provider_account (provider, provider_id),
    CONSTRAINT fk_social_user FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =========================================
-- BANK ACCOUNTS (contas bancárias)
-- =========================================
CREATE TABLE bank_accounts
(
    id              BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id         BIGINT UNSIGNED NOT NULL,
    name            VARCHAR(100)   NOT NULL,
    type            ENUM('corrente', 'poupanca', 'carteira', 'investimento') NOT NULL,
    bank_name       VARCHAR(100) NULL,
    initial_balance DECIMAL(12, 2) NOT NULL DEFAULT 0,
    current_balance DECIMAL(12, 2) NOT NULL DEFAULT 0,
    color           VARCHAR(7) NULL,
    icon            VARCHAR(50) NULL,
    is_active       BOOLEAN        NOT NULL DEFAULT TRUE,
    created_at      DATETIME       NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at      DATETIME       NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at      DATETIME NULL,
    CONSTRAINT fk_bank_account_user FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =========================================
-- CREDIT CARDS
-- =========================================
CREATE TABLE credit_cards
(
    id          BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id     BIGINT UNSIGNED NOT NULL,
    name        VARCHAR(100)   NOT NULL,
    card_limit  DECIMAL(12, 2) NOT NULL DEFAULT 0,
    closing_day TINYINT UNSIGNED NOT NULL,
    due_day     TINYINT UNSIGNED NOT NULL,
    color       VARCHAR(7) NULL,
    icon        VARCHAR(50) NULL,
    is_active   BOOLEAN        NOT NULL DEFAULT TRUE,
    created_at  DATETIME       NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at  DATETIME       NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at  DATETIME NULL,
    CONSTRAINT fk_card_user FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =========================================
-- CARD USERS (pessoas que usam o cartão de outra pessoa)
-- =========================================
CREATE TABLE card_users
(
    id             BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    credit_card_id BIGINT UNSIGNED NOT NULL,
    owner_user_id  BIGINT UNSIGNED NOT NULL,
    name           VARCHAR(150) NOT NULL,
    phone          VARCHAR(20) NULL,
    notes          TEXT NULL,
    created_at     DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at     DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at     DATETIME NULL,
    CONSTRAINT fk_carduser_card FOREIGN KEY (credit_card_id) REFERENCES credit_cards (id) ON DELETE CASCADE,
    CONSTRAINT fk_carduser_owner FOREIGN KEY (owner_user_id) REFERENCES users (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =========================================
-- CATEGORIES
-- =========================================
CREATE TABLE categories
(
    id         BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id    BIGINT UNSIGNED NULL,
    parent_id  BIGINT UNSIGNED NULL,
    name       VARCHAR(100) NOT NULL,
    type       ENUM('receita', 'despesa') NOT NULL,
    color      VARCHAR(7) NULL,
    icon       VARCHAR(50) NULL,
    created_at DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at DATETIME NULL,
    CONSTRAINT fk_category_user FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE,
    CONSTRAINT fk_category_parent FOREIGN KEY (parent_id) REFERENCES categories (id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =========================================
-- CARD INVOICES (faturas do cartão)
-- =========================================
CREATE TABLE card_invoices
(
    id              BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    credit_card_id  BIGINT UNSIGNED NOT NULL,
    reference_month DATE           NOT NULL,
    closing_date    DATE           NOT NULL,
    due_date        DATE           NOT NULL,
    total_amount    DECIMAL(12, 2) NOT NULL DEFAULT 0,
    status          ENUM('aberta', 'fechada', 'paga') NOT NULL DEFAULT 'aberta',
    created_at      DATETIME       NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at      DATETIME       NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at      DATETIME NULL,
    UNIQUE KEY uq_card_month (credit_card_id, reference_month),
    CONSTRAINT fk_invoice_card FOREIGN KEY (credit_card_id) REFERENCES credit_cards (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =========================================
-- RECURRENCES (lançamentos recorrentes automáticos)
-- =========================================
CREATE TABLE recurrences
(
    id                   BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id              BIGINT UNSIGNED NOT NULL,
    category_id          BIGINT UNSIGNED NOT NULL,
    bank_account_id      BIGINT UNSIGNED NULL,
    credit_card_id       BIGINT UNSIGNED NULL,
    type                 ENUM('receita', 'despesa') NOT NULL,
    description          VARCHAR(255)   NOT NULL,
    amount               DECIMAL(12, 2) NOT NULL,
    frequency            ENUM('diaria', 'semanal', 'mensal', 'anual') NOT NULL DEFAULT 'mensal',
    day_of_month         TINYINT UNSIGNED NULL,   -- usado quando frequency = mensal/anual
    start_date           DATE           NOT NULL,
    end_date             DATE NULL,               -- nulo = sem data pra terminar
    next_occurrence_date DATE           NOT NULL, -- próxima data que o job deve gerar
    is_active            BOOLEAN        NOT NULL DEFAULT TRUE,
    created_at           DATETIME       NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at           DATETIME       NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at           DATETIME NULL,
    CONSTRAINT fk_recurrence_user FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE,
    CONSTRAINT fk_recurrence_category FOREIGN KEY (category_id) REFERENCES categories (id),
    CONSTRAINT fk_recurrence_account FOREIGN KEY (bank_account_id) REFERENCES bank_accounts (id) ON DELETE CASCADE,
    CONSTRAINT fk_recurrence_card FOREIGN KEY (credit_card_id) REFERENCES credit_cards (id) ON DELETE CASCADE,
    INDEX                idx_recurrence_next (next_occurrence_date, is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =========================================
-- INSTALLMENT PURCHASES (compras parceladas no cartão)
-- =========================================
CREATE TABLE installment_purchases
(
    id                     BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id                BIGINT UNSIGNED NOT NULL,
    credit_card_id         BIGINT UNSIGNED NOT NULL,
    category_id            BIGINT UNSIGNED NOT NULL,
    card_user_id           BIGINT UNSIGNED NULL,      -- se a compra parcelada é de outra pessoa
    description            VARCHAR(255)   NOT NULL,
    total_amount           DECIMAL(12, 2) NOT NULL,
    installments_count     TINYINT UNSIGNED NOT NULL, -- ex: 10 (10x)
    first_installment_date DATE           NOT NULL,   -- data da 1ª parcela
    created_at             DATETIME       NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at             DATETIME       NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at             DATETIME NULL,
    CONSTRAINT fk_installment_user FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE,
    CONSTRAINT fk_installment_card FOREIGN KEY (credit_card_id) REFERENCES credit_cards (id) ON DELETE CASCADE,
    CONSTRAINT fk_installment_category FOREIGN KEY (category_id) REFERENCES categories (id),
    CONSTRAINT fk_installment_carduser FOREIGN KEY (card_user_id) REFERENCES card_users (id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =========================================
-- ACCOUNT TRANSFERS (transferência entre contas)
-- =========================================
CREATE TABLE account_transfers
(
    id              BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id         BIGINT UNSIGNED NOT NULL,
    from_account_id BIGINT UNSIGNED NOT NULL,
    to_account_id   BIGINT UNSIGNED NOT NULL,
    amount          DECIMAL(12, 2) NOT NULL,
    transfer_date   DATE           NOT NULL,
    description     VARCHAR(255) NULL,
    created_at      DATETIME       NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at      DATETIME       NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at      DATETIME NULL,
    CONSTRAINT fk_transfer_user FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE,
    CONSTRAINT fk_transfer_from FOREIGN KEY (from_account_id) REFERENCES bank_accounts (id) ON DELETE CASCADE,
    CONSTRAINT fk_transfer_to FOREIGN KEY (to_account_id) REFERENCES bank_accounts (id) ON DELETE CASCADE,
    CHECK (from_account_id <> to_account_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =========================================
-- TRANSACTIONS (lançamentos)
-- =========================================
CREATE TABLE transactions
(
    id                      BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id                 BIGINT UNSIGNED NOT NULL,
    category_id             BIGINT UNSIGNED NOT NULL,
    bank_account_id         BIGINT UNSIGNED NULL,
    credit_card_id          BIGINT UNSIGNED NULL,
    card_invoice_id         BIGINT UNSIGNED NULL,
    card_user_id            BIGINT UNSIGNED NULL,  -- pessoa que usou o cartão (se não for o dono)
    recurrence_id           BIGINT UNSIGNED NULL,  -- veio de uma recorrência
    installment_purchase_id BIGINT UNSIGNED NULL,  -- veio de uma compra parcelada
    installment_number      TINYINT UNSIGNED NULL, -- número da parcela (ex: 3 de 10)
    transfer_id             BIGINT UNSIGNED NULL,  -- veio de uma transferência entre contas
    type                    ENUM('receita', 'despesa', 'transferencia') NOT NULL,
    description             VARCHAR(255)   NOT NULL,
    amount                  DECIMAL(12, 2) NOT NULL,
    transaction_date        DATE           NOT NULL,
    status                  ENUM('pendente', 'confirmado') NOT NULL DEFAULT 'pendente',
    created_at              DATETIME       NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at              DATETIME       NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at              DATETIME NULL,
    CONSTRAINT fk_transaction_user FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE,
    CONSTRAINT fk_transaction_category FOREIGN KEY (category_id) REFERENCES categories (id),
    CONSTRAINT fk_transaction_account FOREIGN KEY (bank_account_id) REFERENCES bank_accounts (id) ON DELETE CASCADE,
    CONSTRAINT fk_transaction_card FOREIGN KEY (credit_card_id) REFERENCES credit_cards (id) ON DELETE CASCADE,
    CONSTRAINT fk_transaction_invoice FOREIGN KEY (card_invoice_id) REFERENCES card_invoices (id) ON DELETE SET NULL,
    CONSTRAINT fk_transaction_carduser FOREIGN KEY (card_user_id) REFERENCES card_users (id) ON DELETE SET NULL,
    CONSTRAINT fk_transaction_recurrence FOREIGN KEY (recurrence_id) REFERENCES recurrences (id) ON DELETE SET NULL,
    CONSTRAINT fk_transaction_installment FOREIGN KEY (installment_purchase_id) REFERENCES installment_purchases (id) ON DELETE SET NULL,
    CONSTRAINT fk_transaction_transfer FOREIGN KEY (transfer_id) REFERENCES account_transfers (id) ON DELETE SET NULL,
    INDEX                   idx_transaction_date (transaction_date),
    INDEX                   idx_transaction_user_type (user_id, type)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =========================================
-- USER PROFILES (dados pessoais e preferências)
-- =========================================
CREATE TABLE user_profiles
(
    id                     BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id                BIGINT UNSIGNED NOT NULL UNIQUE,
    cpf                    VARCHAR(14) NULL UNIQUE, -- formato: 000.000.000-00
    phone                  VARCHAR(20) NULL,
    birth_date             DATE NULL,
    gender                 ENUM('masculino', 'feminino', 'outro', 'prefiro_nao_informar') NULL,
    zip_code               VARCHAR(9) NULL,
    address                VARCHAR(255) NULL,
    address_number         VARCHAR(20) NULL,
    neighborhood           VARCHAR(100) NULL,
    city                   VARCHAR(100) NULL,
    state                  CHAR(2) NULL,
    currency               VARCHAR(3)  NOT NULL DEFAULT 'BRL',
    timezone               VARCHAR(50) NOT NULL DEFAULT 'America/Sao_Paulo',
    theme                  ENUM('claro', 'escuro') NOT NULL DEFAULT 'claro',
    notify_invoice_due     BOOLEAN     NOT NULL DEFAULT TRUE,
    notify_budget_exceeded BOOLEAN     NOT NULL DEFAULT TRUE,
    bio                    VARCHAR(255) NULL,
    created_at             DATETIME    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at             DATETIME    NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at             DATETIME NULL,
    CONSTRAINT fk_profile_user FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;