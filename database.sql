create table email_logs
(
    id            bigint unsigned auto_increment
        primary key,
    user_id       bigint unsigned                    null,
    to_email      varchar(150)                       not null,
    subject       varchar(255)                       not null,
    type          varchar(50)                        not null,
    status        enum ('enviado', 'falhou')         not null,
    error_message text                               null,
    delivered_at  datetime                           null,
    created_at    datetime default CURRENT_TIMESTAMP not null
);

create index to_email
    on email_logs (to_email);

create index type
    on email_logs (type);

create index user_id
    on email_logs (user_id);

create table users
(
    id                         bigint unsigned auto_increment
        primary key,
    name                       varchar(150)                       not null,
    email                      varchar(150)                       not null,
    password                   varchar(255)                       null,
    avatar                     varchar(255)                       null,
    email_verified_at          datetime                           null,
    reset_token                varchar(64)                        null,
    reset_expires_at           datetime                           null,
    email_verification_token   varchar(64)                        null,
    email_verification_sent_at datetime                           null,
    created_at                 datetime default CURRENT_TIMESTAMP not null,
    updated_at                 datetime default CURRENT_TIMESTAMP not null on update CURRENT_TIMESTAMP,
    deleted_at                 datetime                           null,
    constraint email
        unique (email),
    constraint uq_users_email
        unique (email)
);

create table audit_logs
(
    id          bigint unsigned auto_increment
        primary key,
    user_id     bigint unsigned                    null,
    event       varchar(100)                       not null,
    description varchar(255)                       null,
    ip_address  varchar(45)                        null,
    user_agent  varchar(255)                       null,
    metadata    json                               null,
    created_at  datetime default CURRENT_TIMESTAMP not null,
    constraint fk_audit_user
        foreign key (user_id) references users (id)
            on delete set null
);

create index idx_user_event
    on audit_logs (user_id, event, created_at);

create table bank_accounts
(
    id              bigint unsigned auto_increment
        primary key,
    user_id         bigint unsigned                                                 not null,
    name            varchar(100)                                                    not null,
    type            enum ('checking', 'savings', 'wallet', 'investment', 'payment') not null,
    bank_name       varchar(100)                                                    null,
    initial_balance decimal(12, 2) default 0.00                                     not null,
    current_balance decimal(12, 2) default 0.00                                     not null,
    color           varchar(7)                                                      null,
    icon            varchar(50)                                                     null,
    is_active       tinyint(1)     default 1                                        not null,
    created_at      datetime       default CURRENT_TIMESTAMP                        not null,
    updated_at      datetime       default CURRENT_TIMESTAMP                        not null on update CURRENT_TIMESTAMP,
    deleted_at      datetime                                                        null,
    constraint fk_bank_account_user
        foreign key (user_id) references users (id)
            on delete cascade
);

create table account_transfers
(
    id              bigint unsigned auto_increment
        primary key,
    user_id         bigint unsigned                    not null,
    from_account_id bigint unsigned                    not null,
    to_account_id   bigint unsigned                    not null,
    amount          decimal(12, 2)                     not null,
    transfer_date   date                               not null,
    description     varchar(255)                       null,
    created_at      datetime default CURRENT_TIMESTAMP not null,
    updated_at      datetime default CURRENT_TIMESTAMP not null on update CURRENT_TIMESTAMP,
    deleted_at      datetime                           null,
    constraint fk_transfer_from
        foreign key (from_account_id) references bank_accounts (id)
            on delete cascade,
    constraint fk_transfer_to
        foreign key (to_account_id) references bank_accounts (id)
            on delete cascade,
    constraint fk_transfer_user
        foreign key (user_id) references users (id)
            on delete cascade,
    check (`from_account_id` <> `to_account_id`)
);

create table card_users
(
    id            bigint unsigned auto_increment
        primary key,
    owner_user_id bigint unsigned                    not null,
    name          varchar(150)                       not null,
    phone         varchar(20)                        null,
    notes         text                               null,
    created_at    datetime default CURRENT_TIMESTAMP not null,
    updated_at    datetime default CURRENT_TIMESTAMP not null on update CURRENT_TIMESTAMP,
    deleted_at    datetime                           null,
    constraint fk_carduser_owner
        foreign key (owner_user_id) references users (id)
            on delete cascade
);

create table categories
(
    id         bigint unsigned auto_increment
        primary key,
    user_id    bigint unsigned                    null,
    parent_id  bigint unsigned                    null,
    name       varchar(100)                       not null,
    type       enum ('receita', 'despesa')        not null,
    color      varchar(7)                         null,
    icon       varchar(50)                        null,
    created_at datetime default CURRENT_TIMESTAMP not null,
    updated_at datetime default CURRENT_TIMESTAMP not null on update CURRENT_TIMESTAMP,
    deleted_at datetime                           null,
    constraint fk_category_parent
        foreign key (parent_id) references categories (id)
            on delete set null,
    constraint fk_category_user
        foreign key (user_id) references users (id)
            on delete cascade
);

create table credit_cards
(
    id          bigint unsigned auto_increment
        primary key,
    user_id     bigint unsigned                          not null,
    name        varchar(100)                             not null,
    card_limit  decimal(12, 2) default 0.00              not null,
    closing_day tinyint unsigned                         not null,
    due_day     tinyint unsigned                         not null,
    color       varchar(7)                               null,
    icon        varchar(50)                              null,
    is_active   tinyint(1)     default 1                 not null,
    created_at  datetime       default CURRENT_TIMESTAMP not null,
    updated_at  datetime       default CURRENT_TIMESTAMP not null on update CURRENT_TIMESTAMP,
    deleted_at  datetime                                 null,
    constraint fk_card_user
        foreign key (user_id) references users (id)
            on delete cascade
);

create table card_invoices
(
    id              bigint unsigned auto_increment
        primary key,
    credit_card_id  bigint unsigned                                              not null,
    reference_month date                                                         not null,
    closing_date    date                                                         not null,
    due_date        date                                                         not null,
    total_amount    decimal(12, 2)                     default 0.00              not null,
    status          enum ('aberta', 'fechada', 'paga') default 'aberta'          not null,
    created_at      datetime                           default CURRENT_TIMESTAMP not null,
    updated_at      datetime                           default CURRENT_TIMESTAMP not null on update CURRENT_TIMESTAMP,
    deleted_at      datetime                                                     null,
    constraint uq_card_month
        unique (credit_card_id, reference_month),
    constraint fk_invoice_card
        foreign key (credit_card_id) references credit_cards (id)
            on delete cascade
);

create table card_user_credit_cards
(
    id             bigint unsigned auto_increment
        primary key,
    card_user_id   bigint unsigned                    not null,
    credit_card_id bigint unsigned                    not null,
    created_at     datetime default CURRENT_TIMESTAMP not null,
    constraint uq_person_card
        unique (card_user_id, credit_card_id),
    constraint fk_cucc_card
        foreign key (credit_card_id) references credit_cards (id)
            on delete cascade,
    constraint fk_cucc_carduser
        foreign key (card_user_id) references card_users (id)
            on delete cascade
);

create table installment_purchases
(
    id                     bigint unsigned auto_increment
        primary key,
    user_id                bigint unsigned                    not null,
    credit_card_id         bigint unsigned                    not null,
    category_id            bigint unsigned                    not null,
    card_user_id           bigint unsigned                    null,
    description            varchar(255)                       not null,
    total_amount           decimal(12, 2)                     not null,
    installments_count     tinyint unsigned                   not null,
    first_installment_date date                               not null,
    created_at             datetime default CURRENT_TIMESTAMP not null,
    updated_at             datetime default CURRENT_TIMESTAMP not null on update CURRENT_TIMESTAMP,
    deleted_at             datetime                           null,
    constraint fk_installment_card
        foreign key (credit_card_id) references credit_cards (id)
            on delete cascade,
    constraint fk_installment_carduser
        foreign key (card_user_id) references card_users (id)
            on delete set null,
    constraint fk_installment_category
        foreign key (category_id) references categories (id),
    constraint fk_installment_user
        foreign key (user_id) references users (id)
            on delete cascade
);

create table known_devices
(
    id                bigint unsigned auto_increment
        primary key,
    user_id           bigint unsigned                      not null,
    device_hash       varchar(64)                          not null,
    device_token_hash varchar(64)                          null,
    user_agent        varchar(255)                         null,
    ip_address        varchar(45)                          null,
    location          varchar(150)                         null,
    trusted           tinyint(1) default 1                 not null,
    first_seen_at     datetime   default CURRENT_TIMESTAMP not null,
    last_seen_at      datetime   default CURRENT_TIMESTAMP not null,
    constraint uq_user_device
        unique (user_id, device_hash),
    constraint fk_known_devices_user
        foreign key (user_id) references users (id)
            on delete cascade
);

create index idx_device_token_hash
    on known_devices (device_token_hash);

create table login_verification_tokens
(
    id                     bigint unsigned auto_increment
        primary key,
    user_id                bigint unsigned                    not null,
    token                  varchar(64)                        not null,
    ip_address             varchar(45)                        null,
    user_agent             varchar(255)                       null,
    location               varchar(150)                       null,
    used_at                datetime                           null,
    reported_suspicious_at datetime                           null,
    created_at             datetime default CURRENT_TIMESTAMP not null,
    expires_at             datetime                           not null,
    constraint token
        unique (token),
    constraint fk_login_verification_user
        foreign key (user_id) references users (id)
            on delete cascade
);

create table recurrences
(
    id                   bigint unsigned auto_increment
        primary key,
    user_id              bigint unsigned                                                         not null,
    category_id          bigint unsigned                                                         not null,
    bank_account_id      bigint unsigned                                                         null,
    credit_card_id       bigint unsigned                                                         null,
    type                 enum ('receita', 'despesa')                                             not null,
    description          varchar(255)                                                            not null,
    amount               decimal(12, 2)                                                          not null,
    frequency            enum ('diaria', 'semanal', 'mensal', 'anual') default 'mensal'          not null,
    day_of_month         tinyint unsigned                                                        null,
    start_date           date                                                                    not null,
    end_date             date                                                                    null,
    next_occurrence_date date                                                                    not null,
    is_active            tinyint(1)                                    default 1                 not null,
    created_at           datetime                                      default CURRENT_TIMESTAMP not null,
    updated_at           datetime                                      default CURRENT_TIMESTAMP not null on update CURRENT_TIMESTAMP,
    deleted_at           datetime                                                                null,
    constraint fk_recurrence_account
        foreign key (bank_account_id) references bank_accounts (id)
            on delete cascade,
    constraint fk_recurrence_card
        foreign key (credit_card_id) references credit_cards (id)
            on delete cascade,
    constraint fk_recurrence_category
        foreign key (category_id) references categories (id),
    constraint fk_recurrence_user
        foreign key (user_id) references users (id)
            on delete cascade
);

create index idx_recurrence_next
    on recurrences (next_occurrence_date, is_active);

create table sessions
(
    id            varchar(128)                       not null
        primary key,
    user_id       bigint unsigned                    null,
    ip_address    varchar(45)                        null,
    user_agent    varchar(255)                       null,
    payload       longtext                           not null,
    last_activity int unsigned                       not null,
    created_at    datetime default CURRENT_TIMESTAMP not null,
    constraint fk_sessions_user
        foreign key (user_id) references users (id)
            on delete cascade
);

create index idx_sessions_last_activity
    on sessions (last_activity);

create index idx_sessions_user
    on sessions (user_id);

create table social_accounts
(
    id            bigint unsigned auto_increment
        primary key,
    user_id       bigint unsigned                    not null,
    provider      enum ('google', 'facebook')        not null,
    provider_id   varchar(255)                       not null,
    access_token  text                               null,
    refresh_token text                               null,
    created_at    datetime default CURRENT_TIMESTAMP not null,
    updated_at    datetime default CURRENT_TIMESTAMP not null on update CURRENT_TIMESTAMP,
    deleted_at    datetime                           null,
    constraint uq_provider_account
        unique (provider, provider_id),
    constraint fk_social_user
        foreign key (user_id) references users (id)
            on delete cascade
);

create table transactions
(
    id                      bigint unsigned auto_increment
        primary key,
    user_id                 bigint unsigned                                           not null,
    category_id             bigint unsigned                                           null,
    bank_account_id         bigint unsigned                                           null,
    credit_card_id          bigint unsigned                                           null,
    card_invoice_id         bigint unsigned                                           null,
    card_user_id            bigint unsigned                                           null,
    recurrence_id           bigint unsigned                                           null,
    installment_purchase_id bigint unsigned                                           null,
    installment_number      tinyint unsigned                                          null,
    transfer_id             bigint unsigned                                           null,
    type                    enum ('receita', 'despesa', 'transferencia')              not null,
    description             varchar(255)                                              not null,
    amount                  decimal(12, 2)                                            not null,
    transaction_date        date                                                      not null,
    status                  enum ('pendente', 'confirmado') default 'pendente'        not null,
    created_at              datetime                        default CURRENT_TIMESTAMP not null,
    updated_at              datetime                        default CURRENT_TIMESTAMP not null on update CURRENT_TIMESTAMP,
    deleted_at              datetime                                                  null,
    constraint fk_transaction_account
        foreign key (bank_account_id) references bank_accounts (id)
            on delete cascade,
    constraint fk_transaction_card
        foreign key (credit_card_id) references credit_cards (id)
            on delete cascade,
    constraint fk_transaction_carduser
        foreign key (card_user_id) references card_users (id)
            on delete set null,
    constraint fk_transaction_category
        foreign key (category_id) references categories (id),
    constraint fk_transaction_installment
        foreign key (installment_purchase_id) references installment_purchases (id)
            on delete set null,
    constraint fk_transaction_invoice
        foreign key (card_invoice_id) references card_invoices (id)
            on delete set null,
    constraint fk_transaction_recurrence
        foreign key (recurrence_id) references recurrences (id)
            on delete set null,
    constraint fk_transaction_transfer
        foreign key (transfer_id) references account_transfers (id)
            on delete set null,
    constraint fk_transaction_user
        foreign key (user_id) references users (id)
            on delete cascade
);

create table card_invoice_payments
(
    id                  bigint unsigned auto_increment
        primary key,
    card_invoice_id     bigint unsigned                    not null,
    bank_account_id     bigint unsigned                    null,
    transaction_id      bigint unsigned                    null,
    paying_card_user_id bigint unsigned                    null,
    amount              decimal(12, 2)                     not null,
    payment_date        date                               not null,
    notes               varchar(255)                       null,
    created_at          datetime default CURRENT_TIMESTAMP not null,
    constraint fk_invoice_payment_account
        foreign key (bank_account_id) references bank_accounts (id)
            on delete set null,
    constraint fk_invoice_payment_invoice
        foreign key (card_invoice_id) references card_invoices (id)
            on delete cascade,
    constraint fk_invoice_payment_paying_person
        foreign key (paying_card_user_id) references card_users (id)
            on delete set null,
    constraint fk_invoice_payment_transaction
        foreign key (transaction_id) references transactions (id)
            on delete set null
);

create table transaction_splits
(
    id             bigint unsigned auto_increment
        primary key,
    transaction_id bigint unsigned                    not null,
    card_user_id   bigint unsigned                    not null,
    amount         decimal(12, 2)                     not null,
    created_at     datetime default CURRENT_TIMESTAMP not null,
    updated_at     datetime default CURRENT_TIMESTAMP not null on update CURRENT_TIMESTAMP,
    constraint uq_split_transaction_person
        unique (transaction_id, card_user_id),
    constraint fk_split_carduser
        foreign key (card_user_id) references card_users (id)
            on delete cascade,
    constraint fk_split_transaction
        foreign key (transaction_id) references transactions (id)
            on delete cascade
);

create index idx_transaction_date
    on transactions (transaction_date);

create index idx_transaction_user_type
    on transactions (user_id, type);

create table user_profiles
(
    id                     bigint unsigned auto_increment
        primary key,
    user_id                bigint unsigned                                                 not null,
    cpf                    varchar(14)                                                     null,
    phone                  varchar(20)                                                     null,
    birth_date             date                                                            null,
    gender                 enum ('masculino', 'feminino', 'outro', 'prefiro_nao_informar') null,
    zip_code               varchar(9)                                                      null,
    address                varchar(255)                                                    null,
    address_number         varchar(20)                                                     null,
    neighborhood           varchar(100)                                                    null,
    city                   varchar(100)                                                    null,
    state                  char(2)                                                         null,
    currency               varchar(3)               default 'BRL'                          not null,
    timezone               varchar(50)              default 'America/Sao_Paulo'            not null,
    theme                  enum ('claro', 'escuro') default 'claro'                        not null,
    notify_invoice_due     tinyint(1)               default 1                              not null,
    notify_budget_exceeded tinyint(1)               default 1                              not null,
    bio                    varchar(255)                                                    null,
    created_at             datetime                 default CURRENT_TIMESTAMP              not null,
    updated_at             datetime                 default CURRENT_TIMESTAMP              not null on update CURRENT_TIMESTAMP,
    deleted_at             datetime                                                        null,
    constraint cpf
        unique (cpf),
    constraint user_id
        unique (user_id),
    constraint fk_profile_user
        foreign key (user_id) references users (id)
            on delete cascade
);

