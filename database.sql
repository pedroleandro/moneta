use sigeti;

create table categories
(
    id          int unsigned auto_increment
        primary key,
    name        varchar(100)                          not null,
    description varchar(255)                          null,
    created_at  timestamp default current_timestamp() not null,
    updated_at  timestamp default current_timestamp() not null on update current_timestamp(),
    deleted_at  datetime                              null
);

create table schools
(
    id         int unsigned auto_increment
        primary key,
    name       varchar(150)                          not null,
    code       varchar(8)                            not null,
    address    varchar(150)                          not null,
    created_at timestamp default current_timestamp() not null,
    updated_at timestamp default current_timestamp() not null on update current_timestamp(),
    deleted_at datetime                              null,
    constraint uq_schools_code
        unique (code)
);

create table users
(
    id               int unsigned auto_increment
        primary key,
    name             varchar(150)                                                        not null,
    email            varchar(150)                                                        not null,
    password         varchar(255)                                                        not null,
    document         varchar(11)                                                         null,
    role             enum ('professor', 'tecnico')           default 'professor'         not null,
    last_login_at    datetime                                                            null,
    status           enum ('registrado', 'ativo', 'inativo') default 'registrado'        not null,
    reset_token      varchar(64)                                                         null,
    reset_expires_at datetime                                                            null,
    created_at       timestamp                               default current_timestamp() not null,
    updated_at       timestamp                               default current_timestamp() not null on update current_timestamp(),
    deleted_at       datetime                                                            null,
    constraint uq_users_document
        unique (document),
    constraint uq_users_email
        unique (email)
);

create table school_users
(
    id         int unsigned auto_increment
        primary key,
    school_id  int unsigned                                                    not null,
    user_id    int unsigned                                                    not null,
    shift      enum ('manha', 'tarde', 'integral') default 'integral'          not null,
    created_at timestamp                           default current_timestamp() not null,
    updated_at timestamp                           default current_timestamp() not null,
    deleted_at timestamp                                                       null,
    constraint uq_school_user_shift
        unique (school_id, user_id, shift),
    constraint fk_school_users_school
        foreign key (school_id) references schools (id)
            on update cascade on delete cascade,
    constraint fk_school_users_user
        foreign key (user_id) references users (id)
            on update cascade on delete cascade
);

create index idx_school_users_school
    on school_users (school_id);

create index idx_school_users_user
    on school_users (user_id);

create table tickets
(
    id          int unsigned auto_increment
        primary key,
    title       varchar(150)                                                                                                      not null,
    description text                                                                                                              not null,
    school_id   int unsigned                                                                                                      not null,
    category_id int unsigned                                                                                                      not null,
    opened_by   int unsigned                                                                                                      not null,
    assigned_to int unsigned                                                                                                      null,
    status      enum ('aberto', 'em_andamento', 'aguardando', 'resolvido', 'finalizado', 'arquivado') default 'aberto'            not null,
    priority    enum ('baixa', 'media', 'alta')                                                       default 'media'             not null,
    opened_at   timestamp                                                                             default current_timestamp() not null,
    closed_at   datetime                                                                                                          null,
    created_at  timestamp                                                                             default current_timestamp() not null,
    updated_at  timestamp                                                                             default current_timestamp() not null on update current_timestamp(),
    deleted_at  datetime                                                                                                          null,
    constraint fk_tickets_assigned
        foreign key (assigned_to) references users (id)
            on update cascade on delete set null,
    constraint fk_tickets_category
        foreign key (category_id) references categories (id)
            on update cascade,
    constraint fk_tickets_opened_by
        foreign key (opened_by) references users (id)
            on update cascade,
    constraint fk_tickets_school
        foreign key (school_id) references schools (id)
            on update cascade
);

create index idx_tickets_assigned
    on tickets (assigned_to);

create index idx_tickets_category
    on tickets (category_id);

create index idx_tickets_opened_at
    on tickets (opened_at);

create index idx_tickets_opened_by
    on tickets (opened_by);

create index idx_tickets_priority
    on tickets (priority);

create index idx_tickets_school
    on tickets (school_id);

create index idx_tickets_status
    on tickets (status);

create table tickets_comments
(
    id         int unsigned auto_increment
        primary key,
    ticket_id  int unsigned                          not null,
    user_id    int unsigned                          not null,
    comment    text                                  not null,
    created_at timestamp default current_timestamp() not null,
    updated_at timestamp default current_timestamp() not null,
    deleted_at datetime                              null,
    constraint fk_comments_ticket
        foreign key (ticket_id) references tickets (id)
            on update cascade on delete cascade,
    constraint fk_comments_user
        foreign key (user_id) references users (id)
            on update cascade
);

create index idx_comments_ticket
    on tickets_comments (ticket_id);

create index idx_comments_user
    on tickets_comments (user_id);

-- ================================================================
-- SIGETI — Seed de dados de teste (Jan–Abr 2026)
-- ================================================================
USE sigeti;
SET FOREIGN_KEY_CHECKS = 0;

-- ----------------------------------------------------------------
-- Escolas
-- ----------------------------------------------------------------
INSERT INTO schools (id, name, code, address, deleted_at) VALUES (1, 'E.E. João Pessoa', 'EEJP0001', 'Av. Getúlio Vargas, 100 - Centro', NULL);
INSERT INTO schools (id, name, code, address, deleted_at) VALUES (2, 'E.E. Rui Barbosa', 'EERB0002', 'Rua Sete de Setembro, 250 - Bairro Novo', NULL);
INSERT INTO schools (id, name, code, address, deleted_at) VALUES (3, 'E.E. Santos Dumont', 'EESD0003', 'Rua das Flores, 45 - Vila Esperança', NULL);
INSERT INTO schools (id, name, code, address, deleted_at) VALUES (4, 'E.E. Tiradentes', 'EETD0004', 'Av. Brasil, 800 - Centro', NULL);
INSERT INTO schools (id, name, code, address, deleted_at) VALUES (5, 'E.E. Monteiro Lobato', 'EEML0005', 'Rua Marechal Deodoro, 33 - Jardim América', NULL);
INSERT INTO schools (id, name, code, address, deleted_at) VALUES (6, 'E.E. Presidente Vargas', 'EEPV0006', 'Av. Presidente Vargas, 1200 - Centro', NULL);
INSERT INTO schools (id, name, code, address, deleted_at) VALUES (7, 'E.E. Dom Pedro II', 'EEDP0007', 'Rua Dom Pedro II, 88 - Vila Nova', NULL);
INSERT INTO schools (id, name, code, address, deleted_at) VALUES (8, 'E.E. Machado de Assis', 'EEMA0008', 'Rua Gonçalves Dias, 320 - Cohab', NULL);
INSERT INTO schools (id, name, code, address, deleted_at) VALUES (9, 'E.E. Castro Alves', 'EECA0009', 'Av. Castro Alves, 540 - São João', NULL);
INSERT INTO schools (id, name, code, address, deleted_at) VALUES (10, 'E.E. Olavo Bilac', 'EEOB0010', 'Rua Olavo Bilac, 77 - Cohab II', NULL);
INSERT INTO schools (id, name, code, address, deleted_at) VALUES (11, 'E.E. Caxias', 'EECX0011', 'Rua Afonso Pena, 210 - Centro', NULL);
INSERT INTO schools (id, name, code, address, deleted_at) VALUES (12, 'E.E. Prof. Aprígio Lima', 'EEAP0012', 'Rua Aprígio Lima, 95 - Parque São Luís', NULL);
INSERT INTO schools (id, name, code, address, deleted_at) VALUES (13, 'E.E. José Sarney', 'EEJS0013', 'Av. José Sarney, 1500 - Cohab', NULL);
INSERT INTO schools (id, name, code, address, deleted_at) VALUES (14, 'E.E. Gonçalves Dias', 'EEGD0014', 'Trav. Gonçalves Dias, 60 - Centro', NULL);
INSERT INTO schools (id, name, code, address, deleted_at) VALUES (15, 'E.E. Benedito Leite', 'EEBL0015', 'Rua Benedito Leite, 330 - Jardim Tropical', NULL);
INSERT INTO schools (id, name, code, address, deleted_at) VALUES (16, 'E.E. Vitor Fialho', 'EEVF0016', 'Rua Vitor Fialho, 411 - Parque Anhanguera', NULL);
INSERT INTO schools (id, name, code, address, deleted_at) VALUES (17, 'E.E. Antônio Dino', 'EEAD0017', 'Av. Antônio Dino, 780 - Bequimão', NULL);
INSERT INTO schools (id, name, code, address, deleted_at) VALUES (18, 'E.E. Clodomir Cardoso', 'EECC0018', 'Rua Clodomir Cardoso, 50 - Res. Itaperi', NULL);
INSERT INTO schools (id, name, code, address, deleted_at) VALUES (19, 'E.E. Newton Bello', 'EENB0019', 'Av. Newton Bello, 640 - Cohab Anil', NULL);
INSERT INTO schools (id, name, code, address, deleted_at) VALUES (20, 'E.E. Marly Sarney', 'EEMS0020', 'Rua Marly Sarney, 200 - Alto Alegre', NULL);
INSERT INTO schools (id, name, code, address, deleted_at) VALUES (21, 'E.E. Prof. Jesuíno Brilhante', 'EEJB0021', 'Rua Jesuíno Brilhante, 123 - Juçaral', NULL);
INSERT INTO schools (id, name, code, address, deleted_at) VALUES (22, 'E.E. Francisca Lobato', 'EEFL0022', 'Trav. Francisca Lobato, 88 - Trizidela', NULL);
INSERT INTO schools (id, name, code, address, deleted_at) VALUES (23, 'E.E. Coelho Neto', 'EECN0023', 'Rua Coelho Neto, 405 - São Luís Rei', NULL);
INSERT INTO schools (id, name, code, address, deleted_at) VALUES (24, 'E.E. Álvares de Azevedo', 'EEAA0024', 'Av. Álvares de Azevedo, 290 - Olho dÁgua', NULL);
INSERT INTO schools (id, name, code, address, deleted_at) VALUES (25, 'E.E. Osvaldo Cruz', 'EEOC0025', 'Rua Osvaldo Cruz, 111 - Renascença', NULL);
INSERT INTO schools (id, name, code, address, deleted_at) VALUES (26, 'E.E. José de Alencar', 'EEJA0026', 'Rua José de Alencar, 67 - São Benedito', NULL);
INSERT INTO schools (id, name, code, address, deleted_at) VALUES (27, 'E.E. Getúlio Vargas', 'EEGV0027', 'Av. Getúlio Vargas, 900 - Codozinho', NULL);
INSERT INTO schools (id, name, code, address, deleted_at) VALUES (28, 'E.E. Augusto dos Anjos', 'EEAUA028', 'Rua Augusto dos Anjos, 34 - Res. Palmeiras', NULL);
INSERT INTO schools (id, name, code, address, deleted_at) VALUES (29, 'E.E. Afonso Costa', 'EEAFC029', 'Rua Afonso Costa, 500 - Ribamar Fiquene', NULL);
INSERT INTO schools (id, name, code, address, deleted_at) VALUES (30, 'E.E. José Lins do Rego', 'EEJLR030', 'Av. José Lins do Rego, 220 - Jardim Primavera', NULL);
INSERT INTO schools (id, name, code, address, deleted_at) VALUES (31, 'E.E. Assis Chateaubriand', 'EEACH031', 'Rua Assis Chateaubriand, 190 - São Francisco', NULL);
INSERT INTO schools (id, name, code, address, deleted_at) VALUES (32, 'E.E. Murilo Braga', 'EEMB0032', 'Trav. Murilo Braga, 78 - Bairro de Fátima', NULL);
INSERT INTO schools (id, name, code, address, deleted_at) VALUES (33, 'E.E. Frei Henrique', 'EEFH0033', 'Rua Frei Henrique, 302 - Liberdade', NULL);
INSERT INTO schools (id, name, code, address, deleted_at) VALUES (34, 'E.E. Humberto de Campos', 'EEHC0034', 'Av. Humberto de Campos, 640 - Forquilha', NULL);
INSERT INTO schools (id, name, code, address, deleted_at) VALUES (35, 'E.E. Pedro Neiva de Santana', 'EEPNS035', 'Rua Pedro Neiva de Santana, 85 - Piçarreira', NULL);
INSERT INTO schools (id, name, code, address, deleted_at) VALUES (36, 'E.E. Gov. Edison Lobão', 'EEGEL036', 'Av. Edison Lobão, 1100 - Cohab Nova', NULL);
INSERT INTO schools (id, name, code, address, deleted_at) VALUES (37, 'E.E. Luís Pinto Ferreira', 'EELPF037', 'Rua Luís Pinto, 44 - São Raimundo', NULL);
INSERT INTO schools (id, name, code, address, deleted_at) VALUES (38, 'E.E. Maria Campos', 'EEMC0038', 'Rua Maria Campos, 230 - Santa Bárbara', NULL);
INSERT INTO schools (id, name, code, address, deleted_at) VALUES (39, 'E.E. Odorico Mendes', 'EEOM0039', 'Rua Odorico Mendes, 512 - Liberdade', NULL);
INSERT INTO schools (id, name, code, address, deleted_at) VALUES (40, 'E.E. Maranhão', 'EEMR0040', 'Av. Maranhão, 370 - Trizidela do Vale', NULL);
INSERT INTO schools (id, name, code, address, deleted_at) VALUES (41, 'E.E. Vitorino Freire', 'EEVFR041', 'Rua Vitorino Freire, 88 - Pedreiras', NULL);
INSERT INTO schools (id, name, code, address, deleted_at) VALUES (42, 'E.E. Lago Verde', 'EELV0042', 'Rua das Mangueiras, 150 - Lago Verde', NULL);
INSERT INTO schools (id, name, code, address, deleted_at) VALUES (43, 'E.E. São Francisco', 'EESF0043', 'Rua São Francisco, 678 - Cohab Primavera', NULL);
INSERT INTO schools (id, name, code, address, deleted_at) VALUES (44, 'E.E. Professora Cida', 'EEPC0044', 'Rua Prof. Cida, 310 - Res. Aurora', NULL);
INSERT INTO schools (id, name, code, address, deleted_at) VALUES (45, 'E.E. João Simplício', 'EEJSI045', 'Av. João Simplício, 720 - Bairro Industrial', NULL);
INSERT INTO schools (id, name, code, address, deleted_at) VALUES (46, 'E.E. Raimundo Ribeiro', 'EERRI046', 'Rua Raimundo Ribeiro, 90 - Bom Jesus', NULL);
INSERT INTO schools (id, name, code, address, deleted_at) VALUES (47, 'E.E. Florentino Menezes', 'EEFM0047', 'Rua Florentino Menezes, 55 - Res. Vitória', NULL);
INSERT INTO schools (id, name, code, address, deleted_at) VALUES (48, 'E.E. Antônio Ferreira da Costa', 'EEAFC048', 'Av. Antônio Ferreira, 430 - São Cristóvão', '2026-02-15 00:00:00');
INSERT INTO schools (id, name, code, address, deleted_at) VALUES (49, 'E.E. Joaquim Alves', 'EEJAL049', 'Rua Joaquim Alves, 300 - Matinha', NULL);
INSERT INTO schools (id, name, code, address, deleted_at) VALUES (50, 'E.E. Prof. Mota Filho', 'EEPMF050', 'Rua Prof. Mota Filho, 102 - Poção de Pedras', NULL);
INSERT INTO schools (id, name, code, address, deleted_at) VALUES (51, 'E.E. Benedita dos Santos', 'EEBS0051', 'Rua Benedita Santos, 77 - São Pedro', NULL);
INSERT INTO schools (id, name, code, address, deleted_at) VALUES (52, 'E.E. Antônio Leal', 'EEAL0052', 'Av. Antônio Leal, 580 - Res. dos Lagos', '2026-02-15 00:00:00');
INSERT INTO schools (id, name, code, address, deleted_at) VALUES (53, 'E.E. Coronel Horácio', 'EECH0053', 'Rua Cel. Horácio, 245 - Parque União', NULL);
INSERT INTO schools (id, name, code, address, deleted_at) VALUES (54, 'E.E. Iracema', 'EEIR0054', 'Rua Iracema, 130 - Boa Vista', NULL);
INSERT INTO schools (id, name, code, address, deleted_at) VALUES (55, 'E.E. Álvaro Maia', 'EEAM0055', 'Av. Álvaro Maia, 800 - Centro Histórico', '2026-02-15 00:00:00');

-- ----------------------------------------------------------------
-- Categorias
-- ----------------------------------------------------------------
INSERT INTO categories (id, name, description) VALUES (1, 'Hardware', 'Problemas com equipamentos físicos como computadores, impressoras e periféricos');
INSERT INTO categories (id, name, description) VALUES (2, 'Software', 'Instalação, atualização ou erros em programas e sistemas operacionais');
INSERT INTO categories (id, name, description) VALUES (3, 'Rede e Conectividade', 'Problemas de acesso à internet, Wi-Fi, switches e cabeamento');
INSERT INTO categories (id, name, description) VALUES (4, 'Projetor', 'Manutenção e suporte a projetores e datashow');
INSERT INTO categories (id, name, description) VALUES (5, 'Impressora', 'Manutenção, troca de toner e papel em impressoras');
INSERT INTO categories (id, name, description) VALUES (6, 'Sistema SIGE', 'Suporte ao sistema de gestão escolar');
INSERT INTO categories (id, name, description) VALUES (7, 'E-mail Institucional', 'Problemas com conta de e-mail da rede estadual');
INSERT INTO categories (id, name, description) VALUES (8, 'Telefonia', 'Ramal, PABX e chamadas institucionais');
INSERT INTO categories (id, name, description) VALUES (9, 'Câmera de Segurança', 'Suporte e manutenção de câmeras CFTV');
INSERT INTO categories (id, name, description) VALUES (10, 'Ar-Condicionado TI', 'Climatização das salas de servidor e laboratório');

-- ----------------------------------------------------------------
-- Técnicos (IDs 1–6)
-- ----------------------------------------------------------------
INSERT INTO users (id, name, email, password, document, role, status, deleted_at) VALUES (1, 'Carlos Eduardo Silva', 'carlos.silva@sigeti.com.br', '$2b$10$NLT3WuQV4GasLTTLd66n3eYaajkdlb5I9gNVHDUE2xeUSDMq7/Q.C', '11122233344', 'tecnico', 'ativo', NULL);
INSERT INTO users (id, name, email, password, document, role, status, deleted_at) VALUES (2, 'Fernanda Oliveira Costa', 'fernanda.costa@sigeti.com.br', '$2b$10$NLT3WuQV4GasLTTLd66n3eYaajkdlb5I9gNVHDUE2xeUSDMq7/Q.C', '22233344455', 'tecnico', 'ativo', NULL);
INSERT INTO users (id, name, email, password, document, role, status, deleted_at) VALUES (3, 'Rafael Mendes Souza', 'rafael.souza@sigeti.com.br', '$2b$10$NLT3WuQV4GasLTTLd66n3eYaajkdlb5I9gNVHDUE2xeUSDMq7/Q.C', '33344455566', 'tecnico', 'ativo', NULL);
INSERT INTO users (id, name, email, password, document, role, status, deleted_at) VALUES (4, 'Lucas Andrade Pinto', 'lucas.pinto@sigeti.com.br', '$2b$10$NLT3WuQV4GasLTTLd66n3eYaajkdlb5I9gNVHDUE2xeUSDMq7/Q.C', '44455566677', 'tecnico', 'ativo', NULL);
INSERT INTO users (id, name, email, password, document, role, status, deleted_at) VALUES (5, 'Patrícia Viana Rocha', 'patricia.rocha@sigeti.com.br', '$2b$10$NLT3WuQV4GasLTTLd66n3eYaajkdlb5I9gNVHDUE2xeUSDMq7/Q.C', '55566677788', 'tecnico', 'inativo', '2026-03-01 00:00:00');
INSERT INTO users (id, name, email, password, document, role, status, deleted_at) VALUES (6, 'Marcos Túlio Fonseca', 'marcos.fonseca@sigeti.com.br', '$2b$10$NLT3WuQV4GasLTTLd66n3eYaajkdlb5I9gNVHDUE2xeUSDMq7/Q.C', '66677788899', 'tecnico', 'inativo', '2026-01-20 00:00:00');

-- ----------------------------------------------------------------
-- Professores (IDs 7–46)
-- ----------------------------------------------------------------
INSERT INTO users (id, name, email, password, document, role, status, deleted_at) VALUES (7, 'Ana Paula Rodrigues', 'ana.rodrigues@sigeti.com.br', '$2b$10$sPJKDuqamVpG1wPYxH9QV.vrrj2pzepQ4vUu5mYk7K4K0XRd2F71q', '63274680169', 'professor', 'ativo', NULL);
INSERT INTO users (id, name, email, password, document, role, status, deleted_at) VALUES (8, 'Bruno Lima Ferreira', 'bruno.ferreira@sigeti.com.br', '$2b$10$sPJKDuqamVpG1wPYxH9QV.vrrj2pzepQ4vUu5mYk7K4K0XRd2F71q', '92463696126', 'professor', 'ativo', NULL);
INSERT INTO users (id, name, email, password, document, role, status, deleted_at) VALUES (9, 'Carla Souza Martins', 'carla.martins@sigeti.com.br', '$2b$10$sPJKDuqamVpG1wPYxH9QV.vrrj2pzepQ4vUu5mYk7K4K0XRd2F71q', '40832721213', 'professor', 'ativo', NULL);
INSERT INTO users (id, name, email, password, document, role, status, deleted_at) VALUES (10, 'Diego Pereira Alves', 'diego.alves@sigeti.com.br', '$2b$10$sPJKDuqamVpG1wPYxH9QV.vrrj2pzepQ4vUu5mYk7K4K0XRd2F71q', '28246873582', 'professor', 'inativo', '2026-02-10 00:00:00');
INSERT INTO users (id, name, email, password, document, role, status, deleted_at) VALUES (11, 'Elaine Costa Barbosa', 'elaine.barbosa@sigeti.com.br', '$2b$10$sPJKDuqamVpG1wPYxH9QV.vrrj2pzepQ4vUu5mYk7K4K0XRd2F71q', '21853583352', 'professor', 'ativo', NULL);
INSERT INTO users (id, name, email, password, document, role, status, deleted_at) VALUES (12, 'Felipe Santos Gonçalves', 'felipe.gonçalves@sigeti.com.br', '$2b$10$sPJKDuqamVpG1wPYxH9QV.vrrj2pzepQ4vUu5mYk7K4K0XRd2F71q', '80364739460', 'professor', 'ativo', NULL);
INSERT INTO users (id, name, email, password, document, role, status, deleted_at) VALUES (13, 'Gabriela Nunes Ramos', 'gabriela.ramos@sigeti.com.br', '$2b$10$sPJKDuqamVpG1wPYxH9QV.vrrj2pzepQ4vUu5mYk7K4K0XRd2F71q', '20903329114', 'professor', 'ativo', NULL);
INSERT INTO users (id, name, email, password, document, role, status, deleted_at) VALUES (14, 'Henrique Lima Castro', 'henrique.castro@sigeti.com.br', '$2b$10$sPJKDuqamVpG1wPYxH9QV.vrrj2pzepQ4vUu5mYk7K4K0XRd2F71q', '95634676514', 'professor', 'ativo', NULL);
INSERT INTO users (id, name, email, password, document, role, status, deleted_at) VALUES (15, 'Isabela Ferreira Dias', 'isabela.dias@sigeti.com.br', '$2b$10$sPJKDuqamVpG1wPYxH9QV.vrrj2pzepQ4vUu5mYk7K4K0XRd2F71q', '37871223723', 'professor', 'inativo', '2026-02-10 00:00:00');
INSERT INTO users (id, name, email, password, document, role, status, deleted_at) VALUES (16, 'João Victor Moreira', 'joão.moreira@sigeti.com.br', '$2b$10$sPJKDuqamVpG1wPYxH9QV.vrrj2pzepQ4vUu5mYk7K4K0XRd2F71q', '89943154243', 'professor', 'ativo', NULL);
INSERT INTO users (id, name, email, password, document, role, status, deleted_at) VALUES (17, 'Larissa Cunha Teixeira', 'larissa.teixeira@sigeti.com.br', '$2b$10$sPJKDuqamVpG1wPYxH9QV.vrrj2pzepQ4vUu5mYk7K4K0XRd2F71q', '38700555525', 'professor', 'ativo', NULL);
INSERT INTO users (id, name, email, password, document, role, status, deleted_at) VALUES (18, 'Marcelo Alves Siqueira', 'marcelo.siqueira@sigeti.com.br', '$2b$10$sPJKDuqamVpG1wPYxH9QV.vrrj2pzepQ4vUu5mYk7K4K0XRd2F71q', '70783564879', 'professor', 'ativo', NULL);
INSERT INTO users (id, name, email, password, document, role, status, deleted_at) VALUES (19, 'Natália Borges Lima', 'natália.lima@sigeti.com.br', '$2b$10$sPJKDuqamVpG1wPYxH9QV.vrrj2pzepQ4vUu5mYk7K4K0XRd2F71q', '34311954012', 'professor', 'ativo', NULL);
INSERT INTO users (id, name, email, password, document, role, status, deleted_at) VALUES (20, 'Otávio Farias Mendes', 'otávio.mendes@sigeti.com.br', '$2b$10$sPJKDuqamVpG1wPYxH9QV.vrrj2pzepQ4vUu5mYk7K4K0XRd2F71q', '90959992632', 'professor', 'ativo', NULL);
INSERT INTO users (id, name, email, password, document, role, status, deleted_at) VALUES (21, 'Paula Rezende Costa', 'paula.costa@sigeti.com.br', '$2b$10$sPJKDuqamVpG1wPYxH9QV.vrrj2pzepQ4vUu5mYk7K4K0XRd2F71q', '20045098443', 'professor', 'ativo', NULL);
INSERT INTO users (id, name, email, password, document, role, status, deleted_at) VALUES (22, 'Quirino Matos Serra', 'quirino.serra@sigeti.com.br', '$2b$10$sPJKDuqamVpG1wPYxH9QV.vrrj2pzepQ4vUu5mYk7K4K0XRd2F71q', '21916273235', 'professor', 'inativo', '2026-02-10 00:00:00');
INSERT INTO users (id, name, email, password, document, role, status, deleted_at) VALUES (23, 'Roberta Leal Azevedo', 'roberta.azevedo@sigeti.com.br', '$2b$10$sPJKDuqamVpG1wPYxH9QV.vrrj2pzepQ4vUu5mYk7K4K0XRd2F71q', '54964180336', 'professor', 'ativo', NULL);
INSERT INTO users (id, name, email, password, document, role, status, deleted_at) VALUES (24, 'Sérgio Nunes Barbosa', 'sérgio.barbosa@sigeti.com.br', '$2b$10$sPJKDuqamVpG1wPYxH9QV.vrrj2pzepQ4vUu5mYk7K4K0XRd2F71q', '16024218768', 'professor', 'ativo', NULL);
INSERT INTO users (id, name, email, password, document, role, status, deleted_at) VALUES (25, 'Tatiane Viana Pinheiro', 'tatiane.pinheiro@sigeti.com.br', '$2b$10$sPJKDuqamVpG1wPYxH9QV.vrrj2pzepQ4vUu5mYk7K4K0XRd2F71q', '84171504845', 'professor', 'ativo', NULL);
INSERT INTO users (id, name, email, password, document, role, status, deleted_at) VALUES (26, 'Ulisses Soares Dutra', 'ulisses.dutra@sigeti.com.br', '$2b$10$sPJKDuqamVpG1wPYxH9QV.vrrj2pzepQ4vUu5mYk7K4K0XRd2F71q', '65867301630', 'professor', 'ativo', NULL);
INSERT INTO users (id, name, email, password, document, role, status, deleted_at) VALUES (27, 'Vanessa Gomes Freitas', 'vanessa.freitas@sigeti.com.br', '$2b$10$sPJKDuqamVpG1wPYxH9QV.vrrj2pzepQ4vUu5mYk7K4K0XRd2F71q', '58119801756', 'professor', 'ativo', NULL);
INSERT INTO users (id, name, email, password, document, role, status, deleted_at) VALUES (28, 'Wagner Lopes Coelho', 'wagner.coelho@sigeti.com.br', '$2b$10$sPJKDuqamVpG1wPYxH9QV.vrrj2pzepQ4vUu5mYk7K4K0XRd2F71q', '20884778705', 'professor', 'ativo', NULL);
INSERT INTO users (id, name, email, password, document, role, status, deleted_at) VALUES (29, 'Xavier Pinto Moraes', 'xavier.moraes@sigeti.com.br', '$2b$10$sPJKDuqamVpG1wPYxH9QV.vrrj2pzepQ4vUu5mYk7K4K0XRd2F71q', '80704123928', 'professor', 'inativo', '2026-02-10 00:00:00');
INSERT INTO users (id, name, email, password, document, role, status, deleted_at) VALUES (30, 'Yara Cristina Nogueira', 'yara.nogueira@sigeti.com.br', '$2b$10$sPJKDuqamVpG1wPYxH9QV.vrrj2pzepQ4vUu5mYk7K4K0XRd2F71q', '39636314865', 'professor', 'ativo', NULL);
INSERT INTO users (id, name, email, password, document, role, status, deleted_at) VALUES (31, 'Zuleica Fonseca Caldas', 'zuleica.caldas@sigeti.com.br', '$2b$10$sPJKDuqamVpG1wPYxH9QV.vrrj2pzepQ4vUu5mYk7K4K0XRd2F71q', '72730184045', 'professor', 'ativo', NULL);
INSERT INTO users (id, name, email, password, document, role, status, deleted_at) VALUES (32, 'Alexandre Rocha Lima', 'alexandre.lima@sigeti.com.br', '$2b$10$sPJKDuqamVpG1wPYxH9QV.vrrj2pzepQ4vUu5mYk7K4K0XRd2F71q', '18489912902', 'professor', 'ativo', NULL);
INSERT INTO users (id, name, email, password, document, role, status, deleted_at) VALUES (33, 'Beatriz Salles Porto', 'beatriz.porto@sigeti.com.br', '$2b$10$sPJKDuqamVpG1wPYxH9QV.vrrj2pzepQ4vUu5mYk7K4K0XRd2F71q', '28715427055', 'professor', 'ativo', NULL);
INSERT INTO users (id, name, email, password, document, role, status, deleted_at) VALUES (34, 'Cláudio Henrique Dias', 'cláudio.dias@sigeti.com.br', '$2b$10$sPJKDuqamVpG1wPYxH9QV.vrrj2pzepQ4vUu5mYk7K4K0XRd2F71q', '73530465220', 'professor', 'ativo', NULL);
INSERT INTO users (id, name, email, password, document, role, status, deleted_at) VALUES (35, 'Denise Figueiredo Melo', 'denise.melo@sigeti.com.br', '$2b$10$sPJKDuqamVpG1wPYxH9QV.vrrj2pzepQ4vUu5mYk7K4K0XRd2F71q', '35696308698', 'professor', 'ativo', NULL);
INSERT INTO users (id, name, email, password, document, role, status, deleted_at) VALUES (36, 'Eduardo Maia Cavalcante', 'eduardo.cavalcante@sigeti.com.br', '$2b$10$sPJKDuqamVpG1wPYxH9QV.vrrj2pzepQ4vUu5mYk7K4K0XRd2F71q', '82773541861', 'professor', 'ativo', NULL);
INSERT INTO users (id, name, email, password, document, role, status, deleted_at) VALUES (37, 'Flávia Ramalho Torres', 'flávia.torres@sigeti.com.br', '$2b$10$sPJKDuqamVpG1wPYxH9QV.vrrj2pzepQ4vUu5mYk7K4K0XRd2F71q', '34310801909', 'professor', 'ativo', NULL);
INSERT INTO users (id, name, email, password, document, role, status, deleted_at) VALUES (38, 'Gilberto Saraiva Vilas', 'gilberto.vilas@sigeti.com.br', '$2b$10$sPJKDuqamVpG1wPYxH9QV.vrrj2pzepQ4vUu5mYk7K4K0XRd2F71q', '23044082700', 'professor', 'ativo', NULL);
INSERT INTO users (id, name, email, password, document, role, status, deleted_at) VALUES (39, 'Helena Macedo Pinto', 'helena.pinto@sigeti.com.br', '$2b$10$sPJKDuqamVpG1wPYxH9QV.vrrj2pzepQ4vUu5mYk7K4K0XRd2F71q', '31574925754', 'professor', 'ativo', NULL);
INSERT INTO users (id, name, email, password, document, role, status, deleted_at) VALUES (40, 'Igor Dantas Oliveira', 'igor.oliveira@sigeti.com.br', '$2b$10$sPJKDuqamVpG1wPYxH9QV.vrrj2pzepQ4vUu5mYk7K4K0XRd2F71q', '69355022383', 'professor', 'ativo', NULL);
INSERT INTO users (id, name, email, password, document, role, status, deleted_at) VALUES (41, 'Juliana Barros Monteiro', 'juliana.monteiro@sigeti.com.br', '$2b$10$sPJKDuqamVpG1wPYxH9QV.vrrj2pzepQ4vUu5mYk7K4K0XRd2F71q', '97726511899', 'professor', 'ativo', NULL);
INSERT INTO users (id, name, email, password, document, role, status, deleted_at) VALUES (42, 'Kevin Amaral Simões', 'kevin.simões@sigeti.com.br', '$2b$10$sPJKDuqamVpG1wPYxH9QV.vrrj2pzepQ4vUu5mYk7K4K0XRd2F71q', '27549317507', 'professor', 'ativo', NULL);
INSERT INTO users (id, name, email, password, document, role, status, deleted_at) VALUES (43, 'Lúcia Medeiros Tavares', 'lúcia.tavares@sigeti.com.br', '$2b$10$sPJKDuqamVpG1wPYxH9QV.vrrj2pzepQ4vUu5mYk7K4K0XRd2F71q', '92934989232', 'professor', 'ativo', NULL);
INSERT INTO users (id, name, email, password, document, role, status, deleted_at) VALUES (44, 'Márcio Azevedo Cunha', 'márcio.cunha@sigeti.com.br', '$2b$10$sPJKDuqamVpG1wPYxH9QV.vrrj2pzepQ4vUu5mYk7K4K0XRd2F71q', '61563951199', 'professor', 'inativo', '2026-02-10 00:00:00');
INSERT INTO users (id, name, email, password, document, role, status, deleted_at) VALUES (45, 'Nilza Ferreira Ramos', 'nilza.ramos@sigeti.com.br', '$2b$10$sPJKDuqamVpG1wPYxH9QV.vrrj2pzepQ4vUu5mYk7K4K0XRd2F71q', '28595909979', 'professor', 'ativo', NULL);
INSERT INTO users (id, name, email, password, document, role, status, deleted_at) VALUES (46, 'Pablo Custódio Araújo', 'pablo.araújo@sigeti.com.br', '$2b$10$sPJKDuqamVpG1wPYxH9QV.vrrj2pzepQ4vUu5mYk7K4K0XRd2F71q', '63787602372', 'professor', 'ativo', NULL);

-- ----------------------------------------------------------------
-- Vínculos escola–professor (school_users)
-- ----------------------------------------------------------------
INSERT INTO school_users (id, school_id, user_id, shift) VALUES (1, 53, 7, 'integral');
INSERT INTO school_users (id, school_id, user_id, shift) VALUES (2, 41, 8, 'manha');
INSERT INTO school_users (id, school_id, user_id, shift) VALUES (3, 14, 8, 'tarde');
INSERT INTO school_users (id, school_id, user_id, shift) VALUES (4, 35, 9, 'manha');
INSERT INTO school_users (id, school_id, user_id, shift) VALUES (5, 26, 9, 'tarde');
INSERT INTO school_users (id, school_id, user_id, shift, deleted_at) VALUES (6, 9, 10, 'manha', '2026-02-10 00:00:00');
INSERT INTO school_users (id, school_id, user_id, shift, deleted_at) VALUES (7, 23, 10, 'tarde', '2026-02-10 00:00:00');
INSERT INTO school_users (id, school_id, user_id, shift) VALUES (8, 31, 11, 'manha');
INSERT INTO school_users (id, school_id, user_id, shift) VALUES (9, 4, 11, 'tarde');
INSERT INTO school_users (id, school_id, user_id, shift) VALUES (10, 46, 12, 'integral');
INSERT INTO school_users (id, school_id, user_id, shift) VALUES (11, 49, 13, 'manha');
INSERT INTO school_users (id, school_id, user_id, shift) VALUES (12, 10, 13, 'tarde');
INSERT INTO school_users (id, school_id, user_id, shift) VALUES (13, 27, 14, 'manha');
INSERT INTO school_users (id, school_id, user_id, shift) VALUES (14, 8, 14, 'tarde');
INSERT INTO school_users (id, school_id, user_id, shift, deleted_at) VALUES (15, 10, 15, 'integral', '2026-02-10 00:00:00');
INSERT INTO school_users (id, school_id, user_id, shift) VALUES (16, 12, 16, 'integral');
INSERT INTO school_users (id, school_id, user_id, shift) VALUES (17, 14, 17, 'manha');
INSERT INTO school_users (id, school_id, user_id, shift) VALUES (18, 36, 17, 'tarde');
INSERT INTO school_users (id, school_id, user_id, shift) VALUES (19, 32, 18, 'integral');
INSERT INTO school_users (id, school_id, user_id, shift) VALUES (20, 17, 19, 'manha');
INSERT INTO school_users (id, school_id, user_id, shift) VALUES (21, 8, 19, 'tarde');
INSERT INTO school_users (id, school_id, user_id, shift) VALUES (22, 24, 20, 'manha');
INSERT INTO school_users (id, school_id, user_id, shift) VALUES (23, 12, 20, 'tarde');
INSERT INTO school_users (id, school_id, user_id, shift) VALUES (24, 33, 21, 'manha');
INSERT INTO school_users (id, school_id, user_id, shift) VALUES (25, 19, 21, 'tarde');
INSERT INTO school_users (id, school_id, user_id, shift, deleted_at) VALUES (26, 4, 22, 'integral', '2026-02-10 00:00:00');
INSERT INTO school_users (id, school_id, user_id, shift) VALUES (27, 25, 23, 'integral');
INSERT INTO school_users (id, school_id, user_id, shift) VALUES (28, 54, 24, 'manha');
INSERT INTO school_users (id, school_id, user_id, shift) VALUES (29, 26, 24, 'tarde');
INSERT INTO school_users (id, school_id, user_id, shift) VALUES (30, 24, 25, 'manha');
INSERT INTO school_users (id, school_id, user_id, shift) VALUES (31, 35, 25, 'tarde');
INSERT INTO school_users (id, school_id, user_id, shift) VALUES (32, 3, 26, 'integral');
INSERT INTO school_users (id, school_id, user_id, shift) VALUES (33, 47, 27, 'manha');
INSERT INTO school_users (id, school_id, user_id, shift) VALUES (34, 26, 27, 'tarde');
INSERT INTO school_users (id, school_id, user_id, shift) VALUES (35, 15, 28, 'manha');
INSERT INTO school_users (id, school_id, user_id, shift) VALUES (36, 20, 28, 'tarde');
INSERT INTO school_users (id, school_id, user_id, shift, deleted_at) VALUES (37, 15, 29, 'manha', '2026-02-10 00:00:00');
INSERT INTO school_users (id, school_id, user_id, shift, deleted_at) VALUES (38, 42, 29, 'tarde', '2026-02-10 00:00:00');
INSERT INTO school_users (id, school_id, user_id, shift) VALUES (39, 7, 30, 'manha');
INSERT INTO school_users (id, school_id, user_id, shift) VALUES (40, 45, 30, 'tarde');
INSERT INTO school_users (id, school_id, user_id, shift) VALUES (41, 14, 31, 'manha');
INSERT INTO school_users (id, school_id, user_id, shift) VALUES (42, 50, 31, 'tarde');
INSERT INTO school_users (id, school_id, user_id, shift) VALUES (43, 44, 32, 'manha');
INSERT INTO school_users (id, school_id, user_id, shift) VALUES (44, 46, 32, 'tarde');
INSERT INTO school_users (id, school_id, user_id, shift) VALUES (45, 19, 33, 'integral');
INSERT INTO school_users (id, school_id, user_id, shift) VALUES (46, 6, 34, 'manha');
INSERT INTO school_users (id, school_id, user_id, shift) VALUES (47, 29, 34, 'tarde');
INSERT INTO school_users (id, school_id, user_id, shift) VALUES (48, 17, 35, 'manha');
INSERT INTO school_users (id, school_id, user_id, shift) VALUES (49, 5, 35, 'tarde');
INSERT INTO school_users (id, school_id, user_id, shift) VALUES (50, 44, 36, 'manha');
INSERT INTO school_users (id, school_id, user_id, shift) VALUES (51, 21, 36, 'tarde');
INSERT INTO school_users (id, school_id, user_id, shift) VALUES (52, 35, 37, 'manha');
INSERT INTO school_users (id, school_id, user_id, shift) VALUES (53, 9, 37, 'tarde');
INSERT INTO school_users (id, school_id, user_id, shift) VALUES (54, 17, 38, 'manha');
INSERT INTO school_users (id, school_id, user_id, shift) VALUES (55, 28, 38, 'tarde');
INSERT INTO school_users (id, school_id, user_id, shift) VALUES (56, 27, 39, 'manha');
INSERT INTO school_users (id, school_id, user_id, shift) VALUES (57, 43, 39, 'tarde');
INSERT INTO school_users (id, school_id, user_id, shift) VALUES (58, 11, 40, 'manha');
INSERT INTO school_users (id, school_id, user_id, shift) VALUES (59, 19, 40, 'tarde');
INSERT INTO school_users (id, school_id, user_id, shift) VALUES (60, 44, 41, 'manha');
INSERT INTO school_users (id, school_id, user_id, shift) VALUES (61, 39, 41, 'tarde');
INSERT INTO school_users (id, school_id, user_id, shift) VALUES (62, 40, 42, 'manha');
INSERT INTO school_users (id, school_id, user_id, shift) VALUES (63, 43, 42, 'tarde');
INSERT INTO school_users (id, school_id, user_id, shift) VALUES (64, 13, 43, 'manha');
INSERT INTO school_users (id, school_id, user_id, shift) VALUES (65, 25, 43, 'tarde');
INSERT INTO school_users (id, school_id, user_id, shift, deleted_at) VALUES (66, 13, 44, 'integral', '2026-02-10 00:00:00');
INSERT INTO school_users (id, school_id, user_id, shift) VALUES (67, 43, 45, 'integral');
INSERT INTO school_users (id, school_id, user_id, shift) VALUES (68, 19, 46, 'manha');
INSERT INTO school_users (id, school_id, user_id, shift) VALUES (69, 13, 46, 'tarde');

-- ----------------------------------------------------------------
-- Chamados (Jan–Abr 2026)
-- ----------------------------------------------------------------
INSERT INTO tickets (id, title, description, school_id, category_id, opened_by, assigned_to, status, priority, opened_at, closed_at) VALUES (1, 'PABX desligado', 'Há chiado constante na linha do telefone da secretaria.', 26, 8, 24, 3, 'resolvido', 'alta', '2026-01-22 13:00:07', '2026-01-24 14:00:07');
INSERT INTO tickets (id, title, description, school_id, category_id, opened_by, assigned_to, status, priority, opened_at, closed_at) VALUES (2, 'Projetor com imagem distorcida', 'O projetor desliga sozinho após alguns minutos de uso.', 3, 4, 26, NULL, 'aberto', 'media', '2026-01-15 15:33:40', NULL);
INSERT INTO tickets (id, title, description, school_id, category_id, opened_by, assigned_to, status, priority, opened_at, closed_at) VALUES (3, 'Projetor desligando durante aula', 'A lâmpada do projetor queimou durante a aula.', 8, 4, 14, 1, 'em_andamento', 'media', '2026-01-19 07:03:26', NULL);
INSERT INTO tickets (id, title, description, school_id, category_id, opened_by, assigned_to, status, priority, opened_at, closed_at) VALUES (4, 'IP duplicado na rede', 'A rede Wi-Fi do laboratório cai várias vezes durante as aulas.', 21, 3, 36, 2, 'arquivado', 'baixa', '2026-01-09 14:21:08', '2026-01-16 18:21:08');
INSERT INTO tickets (id, title, description, school_id, category_id, opened_by, assigned_to, status, priority, opened_at, closed_at) VALUES (5, 'Erro de autenticação no Outlook', 'O Outlook não autentica com as credenciais institucionais.', 12, 7, 20, NULL, 'aberto', 'alta', '2026-01-09 10:17:29', NULL);
INSERT INTO tickets (id, title, description, school_id, category_id, opened_by, assigned_to, status, priority, opened_at, closed_at) VALUES (6, 'Projetor não conecta no notebook', 'A imagem do projetor está distorcida e fora de foco.', 44, 4, 41, 1, 'resolvido', 'media', '2026-01-18 10:29:33', '2026-01-19 11:29:33');
INSERT INTO tickets (id, title, description, school_id, category_id, opened_by, assigned_to, status, priority, opened_at, closed_at) VALUES (7, 'Ponto de rede sem sinal', 'Os computadores da sala 12 estão sem acesso à internet desde ontem.', 53, 3, 7, 1, 'em_andamento', 'media', '2026-01-19 12:26:16', NULL);
INSERT INTO tickets (id, title, description, school_id, category_id, opened_by, assigned_to, status, priority, opened_at, closed_at) VALUES (8, 'Computador não liga', 'O HD emite barulho de clique ao acessar arquivos grandes.', 13, 1, 43, NULL, 'finalizado', 'baixa', '2026-01-25 11:10:53', '2026-01-29 12:10:53');
INSERT INTO tickets (id, title, description, school_id, category_id, opened_by, assigned_to, status, priority, opened_at, closed_at) VALUES (9, 'Impressora imprimindo em branco', 'O papel está atolando toda vez que tentamos imprimir.', 8, 5, 19, 3, 'finalizado', 'media', '2026-01-22 12:04:35', '2026-01-24 17:04:35');
INSERT INTO tickets (id, title, description, school_id, category_id, opened_by, assigned_to, status, priority, opened_at, closed_at) VALUES (10, 'Atualização do Windows pendente', 'Após reinstalar a impressora, o driver apresenta erro.', 12, 2, 16, 2, 'em_andamento', 'baixa', '2026-01-05 15:05:02', NULL);
INSERT INTO tickets (id, title, description, school_id, category_id, opened_by, assigned_to, status, priority, opened_at, closed_at) VALUES (11, 'SIGE lento para carregar turmas', 'O sistema SIGE está inacessível desde esta manhã.', 54, 6, 24, 3, 'resolvido', 'media', '2026-01-25 07:57:21', '2026-01-29 09:57:21');
INSERT INTO tickets (id, title, description, school_id, category_id, opened_by, assigned_to, status, priority, opened_at, closed_at) VALUES (12, 'Wi-Fi instável no laboratório', 'A rede Wi-Fi do laboratório cai várias vezes durante as aulas.', 43, 3, 45, 2, 'em_andamento', 'media', '2026-01-13 17:50:06', NULL);
INSERT INTO tickets (id, title, description, school_id, category_id, opened_by, assigned_to, status, priority, opened_at, closed_at) VALUES (13, 'Notas não salvam no sistema', 'Minha senha do SIGE expirou e não consigo redefinir.', 49, 6, 13, 2, 'em_andamento', 'media', '2026-01-25 14:23:16', NULL);
INSERT INTO tickets (id, title, description, school_id, category_id, opened_by, assigned_to, status, priority, opened_at, closed_at) VALUES (14, 'Sem acesso à internet na sala 12', 'Dois computadores estão com o mesmo IP, causando conflito na rede.', 17, 3, 19, 1, 'finalizado', 'media', '2026-01-29 11:51:35', '2026-02-01 14:51:35');
INSERT INTO tickets (id, title, description, school_id, category_id, opened_by, assigned_to, status, priority, opened_at, closed_at) VALUES (15, 'Memória RAM insuficiente no lab', 'O equipamento não responde ao ser ligado. Já verifiquei a tomada e o cabo.', 50, 1, 31, 3, 'resolvido', 'baixa', '2026-01-26 07:24:52', '2026-01-29 12:24:52');
INSERT INTO tickets (id, title, description, school_id, category_id, opened_by, assigned_to, status, priority, opened_at, closed_at) VALUES (16, 'Erro ao abrir LibreOffice', 'O sistema operacional trava frequentemente ao abrir múltiplos programas.', 35, 2, 37, 2, 'em_andamento', 'media', '2026-01-17 13:03:13', NULL);
INSERT INTO tickets (id, title, description, school_id, category_id, opened_by, assigned_to, status, priority, opened_at, closed_at) VALUES (17, 'Driver de impressora corrompido', 'O antivírus está com definições de vírus desatualizadas há semanas.', 53, 2, 7, 2, 'finalizado', 'alta', '2026-01-05 09:40:19', '2026-01-06 12:40:19');
INSERT INTO tickets (id, title, description, school_id, category_id, opened_by, assigned_to, status, priority, opened_at, closed_at) VALUES (18, 'Licença do Office expirada', 'O antivírus está com definições de vírus desatualizadas há semanas.', 26, 2, 9, NULL, 'aberto', 'baixa', '2026-01-30 14:04:10', NULL);
INSERT INTO tickets (id, title, description, school_id, category_id, opened_by, assigned_to, status, priority, opened_at, closed_at) VALUES (19, 'Lâmpada do projetor queimada', 'O projetor não detecta o sinal HDMI do notebook.', 10, 4, 13, NULL, 'resolvido', 'media', '2026-01-06 10:46:36', '2026-01-10 12:46:36');
INSERT INTO tickets (id, title, description, school_id, category_id, opened_by, assigned_to, status, priority, opened_at, closed_at) VALUES (20, 'Erro de login no SIGE', 'O sistema SIGE está inacessível desde esta manhã.', 24, 6, 20, 4, 'finalizado', 'alta', '2026-01-24 10:11:53', '2026-01-30 13:11:53');
INSERT INTO tickets (id, title, description, school_id, category_id, opened_by, assigned_to, status, priority, opened_at, closed_at) VALUES (21, 'Antivírus desatualizado no laboratório', 'O antivírus está com definições de vírus desatualizadas há semanas.', 9, 2, 37, NULL, 'resolvido', 'media', '2026-01-18 08:15:16', '2026-01-21 13:15:16');
INSERT INTO tickets (id, title, description, school_id, category_id, opened_by, assigned_to, status, priority, opened_at, closed_at) VALUES (22, 'Cabo de rede danificado', 'Os computadores da sala 12 estão sem acesso à internet desde ontem.', 26, 3, 9, 1, 'finalizado', 'alta', '2026-01-11 10:59:03', '2026-01-17 13:59:03');
INSERT INTO tickets (id, title, description, school_id, category_id, opened_by, assigned_to, status, priority, opened_at, closed_at) VALUES (23, 'Driver de impressora corrompido', 'O sistema operacional trava frequentemente ao abrir múltiplos programas.', 19, 2, 21, 1, 'resolvido', 'alta', '2026-01-06 12:24:09', '2026-01-09 17:24:09');
INSERT INTO tickets (id, title, description, school_id, category_id, opened_by, assigned_to, status, priority, opened_at, closed_at) VALUES (24, 'Erro ao instalar software educacional', 'O sistema operacional trava frequentemente ao abrir múltiplos programas.', 32, 2, 18, 4, 'resolvido', 'baixa', '2026-01-01 12:20:56', '2026-01-05 14:20:56');
INSERT INTO tickets (id, title, description, school_id, category_id, opened_by, assigned_to, status, priority, opened_at, closed_at) VALUES (25, 'Projetor sem imagem', 'O projetor não projeta imagem, apenas a luz do equipamento acende.', 26, 4, 24, NULL, 'finalizado', 'baixa', '2026-01-09 08:41:00', '2026-01-13 09:41:00');
INSERT INTO tickets (id, title, description, school_id, category_id, opened_by, assigned_to, status, priority, opened_at, closed_at) VALUES (26, 'Impressora offline', 'O toner acabou e as impressões estão saindo apagadas.', 5, 5, 35, 3, 'resolvido', 'media', '2026-01-23 12:51:07', '2026-01-26 17:51:07');
INSERT INTO tickets (id, title, description, school_id, category_id, opened_by, assigned_to, status, priority, opened_at, closed_at) VALUES (27, 'Windows travando constantemente', 'O antivírus está com definições de vírus desatualizadas há semanas.', 8, 2, 14, 3, 'finalizado', 'media', '2026-01-20 16:15:13', '2026-01-24 22:15:13');
INSERT INTO tickets (id, title, description, school_id, category_id, opened_by, assigned_to, status, priority, opened_at, closed_at) VALUES (28, 'Sem acesso à internet na sala 12', 'Dois computadores estão com o mesmo IP, causando conflito na rede.', 17, 3, 35, 1, 'finalizado', 'alta', '2026-01-10 14:48:57', '2026-01-16 18:48:57');
INSERT INTO tickets (id, title, description, school_id, category_id, opened_by, assigned_to, status, priority, opened_at, closed_at) VALUES (29, 'Lentidão na rede interna do lab', 'Dois computadores estão com o mesmo IP, causando conflito na rede.', 19, 3, 46, 3, 'resolvido', 'media', '2026-01-13 10:04:37', '2026-01-16 14:04:37');
INSERT INTO tickets (id, title, description, school_id, category_id, opened_by, assigned_to, status, priority, opened_at, closed_at) VALUES (30, 'Relatório de frequência com erro', 'Minha senha do SIGE expirou e não consigo redefinir.', 31, 6, 11, 2, 'em_andamento', 'baixa', '2026-01-28 13:12:27', NULL);
INSERT INTO tickets (id, title, description, school_id, category_id, opened_by, assigned_to, status, priority, opened_at, closed_at) VALUES (31, 'Ramal sem linha', 'O ramal da sala dos professores está sem sinal de linha.', 25, 8, 23, 1, 'aguardando', 'baixa', '2026-01-01 13:53:56', NULL);
INSERT INTO tickets (id, title, description, school_id, category_id, opened_by, assigned_to, status, priority, opened_at, closed_at) VALUES (32, 'Erro ao abrir LibreOffice', 'Ao abrir o LibreOffice aparece mensagem de erro e o programa fecha.', 8, 2, 14, 2, 'em_andamento', 'media', '2026-01-25 13:24:40', NULL);
INSERT INTO tickets (id, title, description, school_id, category_id, opened_by, assigned_to, status, priority, opened_at, closed_at) VALUES (33, 'Fonte do computador queimada', 'O computador não liga. Suspeito que seja a fonte de alimentação.', 8, 1, 19, 1, 'finalizado', 'media', '2026-01-02 18:38:53', '2026-01-03 21:38:53');
INSERT INTO tickets (id, title, description, school_id, category_id, opened_by, assigned_to, status, priority, opened_at, closed_at) VALUES (34, 'Switch da sala 05 com problema', 'O switch da sala 05 está com leds piscando de forma anormal.', 25, 3, 43, 4, 'resolvido', 'alta', '2026-01-09 15:16:40', '2026-01-11 19:16:40');
INSERT INTO tickets (id, title, description, school_id, category_id, opened_by, assigned_to, status, priority, opened_at, closed_at) VALUES (35, 'Sistema lento para inicializar', 'Ao abrir o LibreOffice aparece mensagem de erro e o programa fecha.', 13, 2, 43, NULL, 'finalizado', 'alta', '2026-01-12 16:33:04', '2026-01-14 21:33:04');
INSERT INTO tickets (id, title, description, school_id, category_id, opened_by, assigned_to, status, priority, opened_at, closed_at) VALUES (36, 'Switch da sala 05 com problema', 'Dois computadores estão com o mesmo IP, causando conflito na rede.', 44, 3, 36, 1, 'finalizado', 'baixa', '2026-02-14 08:27:29', '2026-02-15 09:27:29');
INSERT INTO tickets (id, title, description, school_id, category_id, opened_by, assigned_to, status, priority, opened_at, closed_at) VALUES (37, 'Teclado com teclas travadas', 'Algumas teclas não registram ao pressionar, dificultando as aulas práticas.', 32, 1, 18, 3, 'finalizado', 'media', '2026-02-06 15:43:41', '2026-02-08 16:43:41');
INSERT INTO tickets (id, title, description, school_id, category_id, opened_by, assigned_to, status, priority, opened_at, closed_at) VALUES (38, 'Toner acabou na impressora da secretaria', 'A impressora aparece offline no sistema mesmo estando ligada.', 44, 5, 32, 4, 'finalizado', 'media', '2026-02-21 11:30:34', '2026-02-26 14:30:34');
INSERT INTO tickets (id, title, description, school_id, category_id, opened_by, assigned_to, status, priority, opened_at, closed_at) VALUES (39, 'Erro ao abrir LibreOffice', 'O sistema operacional trava frequentemente ao abrir múltiplos programas.', 54, 2, 24, NULL, 'finalizado', 'alta', '2026-02-20 13:45:30', '2026-02-22 18:45:30');
INSERT INTO tickets (id, title, description, school_id, category_id, opened_by, assigned_to, status, priority, opened_at, closed_at) VALUES (40, 'Switch da sala 05 com problema', 'Dois computadores estão com o mesmo IP, causando conflito na rede.', 14, 3, 8, 2, 'finalizado', 'alta', '2026-02-05 13:02:04', '2026-02-10 16:02:04');
INSERT INTO tickets (id, title, description, school_id, category_id, opened_by, assigned_to, status, priority, opened_at, closed_at) VALUES (41, 'Câmera da entrada offline', 'A câmera da entrada principal está aparecendo offline no sistema.', 8, 9, 14, 1, 'resolvido', 'media', '2026-02-22 12:27:12', '2026-02-24 13:27:12');
INSERT INTO tickets (id, title, description, school_id, category_id, opened_by, assigned_to, status, priority, opened_at, closed_at) VALUES (42, 'Antivírus desatualizado no laboratório', 'O computador leva mais de 10 minutos para inicializar completamente.', 13, 2, 46, 3, 'resolvido', 'media', '2026-02-19 17:53:59', '2026-02-23 20:53:59');
INSERT INTO tickets (id, title, description, school_id, category_id, opened_by, assigned_to, status, priority, opened_at, closed_at) VALUES (43, 'HD com barulho estranho', 'O monitor fica em modo sleep mesmo com o computador ligado.', 27, 1, 14, 3, 'finalizado', 'media', '2026-02-13 12:37:15', '2026-02-18 13:37:15');
INSERT INTO tickets (id, title, description, school_id, category_id, opened_by, assigned_to, status, priority, opened_at, closed_at) VALUES (44, 'Toner acabou na impressora da secretaria', 'O toner acabou e as impressões estão saindo apagadas.', 14, 5, 17, NULL, 'aberto', 'media', '2026-02-10 18:42:50', NULL);
INSERT INTO tickets (id, title, description, school_id, category_id, opened_by, assigned_to, status, priority, opened_at, closed_at) VALUES (45, 'Erro de login no SIGE', 'Minha senha do SIGE expirou e não consigo redefinir.', 32, 6, 18, 4, 'resolvido', 'alta', '2026-02-17 15:08:16', '2026-02-20 19:08:16');
INSERT INTO tickets (id, title, description, school_id, category_id, opened_by, assigned_to, status, priority, opened_at, closed_at) VALUES (46, 'Erro de login no SIGE', 'O relatório de frequência está gerando dados incorretos.', 26, 6, 24, NULL, 'aberto', 'baixa', '2026-02-21 08:31:56', NULL);
INSERT INTO tickets (id, title, description, school_id, category_id, opened_by, assigned_to, status, priority, opened_at, closed_at) VALUES (47, 'Windows travando constantemente', 'O antivírus está com definições de vírus desatualizadas há semanas.', 46, 2, 12, NULL, 'aguardando', 'media', '2026-02-05 17:59:22', NULL);
INSERT INTO tickets (id, title, description, school_id, category_id, opened_by, assigned_to, status, priority, opened_at, closed_at) VALUES (48, 'Senha do SIGE expirada', 'Não consigo fazer login no SIGE com as minhas credenciais.', 25, 6, 23, 4, 'finalizado', 'baixa', '2026-02-07 17:33:48', '2026-02-11 21:33:48');
INSERT INTO tickets (id, title, description, school_id, category_id, opened_by, assigned_to, status, priority, opened_at, closed_at) VALUES (49, 'Impressora offline', 'O papel está atolando toda vez que tentamos imprimir.', 54, 5, 24, 4, 'finalizado', 'alta', '2026-02-06 09:55:58', '2026-02-11 13:55:58');
INSERT INTO tickets (id, title, description, school_id, category_id, opened_by, assigned_to, status, priority, opened_at, closed_at) VALUES (50, 'Windows travando constantemente', 'O sistema operacional trava frequentemente ao abrir múltiplos programas.', 35, 2, 9, NULL, 'finalizado', 'media', '2026-02-13 12:48:31', '2026-02-15 15:48:31');
INSERT INTO tickets (id, title, description, school_id, category_id, opened_by, assigned_to, status, priority, opened_at, closed_at) VALUES (51, 'Projetor com imagem distorcida', 'O projetor não projeta imagem, apenas a luz do equipamento acende.', 6, 4, 34, 4, 'resolvido', 'media', '2026-02-22 11:11:32', '2026-02-25 15:11:32');
INSERT INTO tickets (id, title, description, school_id, category_id, opened_by, assigned_to, status, priority, opened_at, closed_at) VALUES (52, 'Erro de autenticação no Outlook', 'O Outlook não autentica com as credenciais institucionais.', 45, 7, 30, 1, 'arquivado', 'alta', '2026-02-18 09:49:01', '2026-02-27 11:49:01');
INSERT INTO tickets (id, title, description, school_id, category_id, opened_by, assigned_to, status, priority, opened_at, closed_at) VALUES (53, 'Windows travando constantemente', 'O antivírus está com definições de vírus desatualizadas há semanas.', 45, 2, 30, NULL, 'aberto', 'alta', '2026-02-11 13:22:25', NULL);
INSERT INTO tickets (id, title, description, school_id, category_id, opened_by, assigned_to, status, priority, opened_at, closed_at) VALUES (54, 'Driver de impressora corrompido', 'Ao abrir o LibreOffice aparece mensagem de erro e o programa fecha.', 19, 2, 40, 3, 'finalizado', 'media', '2026-02-24 14:40:20', '2026-03-02 20:40:20');
INSERT INTO tickets (id, title, description, school_id, category_id, opened_by, assigned_to, status, priority, opened_at, closed_at) VALUES (55, 'Erro ao abrir LibreOffice', 'Após reinstalar a impressora, o driver apresenta erro.', 32, 2, 18, 2, 'resolvido', 'baixa', '2026-02-17 08:50:44', '2026-02-21 11:50:44');
INSERT INTO tickets (id, title, description, school_id, category_id, opened_by, assigned_to, status, priority, opened_at, closed_at) VALUES (56, 'Senha do SIGE expirada', 'O relatório de frequência está gerando dados incorretos.', 46, 6, 12, 2, 'arquivado', 'media', '2026-02-21 10:50:24', '2026-02-28 12:50:24');
INSERT INTO tickets (id, title, description, school_id, category_id, opened_by, assigned_to, status, priority, opened_at, closed_at) VALUES (57, 'Notas não salvam no sistema', 'O relatório de frequência está gerando dados incorretos.', 31, 6, 11, 4, 'finalizado', 'baixa', '2026-02-13 10:46:16', '2026-02-17 13:46:16');
INSERT INTO tickets (id, title, description, school_id, category_id, opened_by, assigned_to, status, priority, opened_at, closed_at) VALUES (58, 'Projetor com imagem distorcida', 'O projetor desliga sozinho após alguns minutos de uso.', 28, 4, 38, 3, 'resolvido', 'alta', '2026-02-24 09:39:59', '2026-02-26 13:39:59');
INSERT INTO tickets (id, title, description, school_id, category_id, opened_by, assigned_to, status, priority, opened_at, closed_at) VALUES (59, 'Projetor com imagem distorcida', 'O projetor não detecta o sinal HDMI do notebook.', 19, 4, 40, NULL, 'resolvido', 'media', '2026-02-20 11:34:28', '2026-02-24 12:34:28');
INSERT INTO tickets (id, title, description, school_id, category_id, opened_by, assigned_to, status, priority, opened_at, closed_at) VALUES (60, 'Projetor não conecta no notebook', 'A imagem do projetor está distorcida e fora de foco.', 53, 4, 7, 4, 'finalizado', 'baixa', '2026-02-11 07:46:12', '2026-02-13 08:46:12');
INSERT INTO tickets (id, title, description, school_id, category_id, opened_by, assigned_to, status, priority, opened_at, closed_at) VALUES (61, 'Imagem da câmera pixelada', 'A câmera do corredor principal parou de transmitir imagem.', 33, 9, 21, 2, 'finalizado', 'baixa', '2026-02-25 11:01:22', '2026-03-03 12:01:22');
INSERT INTO tickets (id, title, description, school_id, category_id, opened_by, assigned_to, status, priority, opened_at, closed_at) VALUES (62, 'DVR sem gravação', 'A câmera do corredor principal parou de transmitir imagem.', 25, 9, 23, NULL, 'finalizado', 'media', '2026-02-25 07:11:29', '2026-02-28 11:11:29');
INSERT INTO tickets (id, title, description, school_id, category_id, opened_by, assigned_to, status, priority, opened_at, closed_at) VALUES (63, 'Lentidão na rede interna do lab', 'O switch da sala 05 está com leds piscando de forma anormal.', 14, 3, 8, 1, 'finalizado', 'media', '2026-02-06 16:02:52', '2026-02-08 20:02:52');
INSERT INTO tickets (id, title, description, school_id, category_id, opened_by, assigned_to, status, priority, opened_at, closed_at) VALUES (64, 'Telefone com chiado na linha', 'Há chiado constante na linha do telefone da secretaria.', 39, 8, 41, 3, 'finalizado', 'media', '2026-02-26 17:30:41', '2026-03-02 18:30:41');
INSERT INTO tickets (id, title, description, school_id, category_id, opened_by, assigned_to, status, priority, opened_at, closed_at) VALUES (65, 'Lâmpada do projetor queimada', 'O projetor desliga sozinho após alguns minutos de uso.', 27, 4, 14, 4, 'finalizado', 'alta', '2026-02-23 12:44:58', '2026-03-01 13:44:58');
INSERT INTO tickets (id, title, description, school_id, category_id, opened_by, assigned_to, status, priority, opened_at, closed_at) VALUES (66, 'Atualização do Windows pendente', 'Ao abrir o LibreOffice aparece mensagem de erro e o programa fecha.', 26, 2, 27, 3, 'finalizado', 'alta', '2026-02-13 09:38:52', '2026-02-15 15:38:52');
INSERT INTO tickets (id, title, description, school_id, category_id, opened_by, assigned_to, status, priority, opened_at, closed_at) VALUES (67, 'Wi-Fi instável no laboratório', 'Os computadores da sala 12 estão sem acesso à internet desde ontem.', 49, 3, 13, 1, 'resolvido', 'alta', '2026-02-12 13:55:20', '2026-02-14 15:55:20');
INSERT INTO tickets (id, title, description, school_id, category_id, opened_by, assigned_to, status, priority, opened_at, closed_at) VALUES (68, 'Monitor sem sinal', 'O computador não liga. Suspeito que seja a fonte de alimentação.', 44, 1, 36, 1, 'aguardando', 'media', '2026-02-28 12:20:12', NULL);
INSERT INTO tickets (id, title, description, school_id, category_id, opened_by, assigned_to, status, priority, opened_at, closed_at) VALUES (69, 'Ponto de rede sem sinal', 'A rede Wi-Fi do laboratório cai várias vezes durante as aulas.', 41, 3, 8, NULL, 'aberto', 'media', '2026-02-05 07:19:22', NULL);
INSERT INTO tickets (id, title, description, school_id, category_id, opened_by, assigned_to, status, priority, opened_at, closed_at) VALUES (70, 'Projetor sem imagem', 'A imagem do projetor está distorcida e fora de foco.', 3, 4, 26, 3, 'em_andamento', 'media', '2026-02-08 07:15:16', NULL);
INSERT INTO tickets (id, title, description, school_id, category_id, opened_by, assigned_to, status, priority, opened_at, closed_at) VALUES (71, 'Wi-Fi instável no laboratório', 'Dois computadores estão com o mesmo IP, causando conflito na rede.', 24, 3, 20, NULL, 'aguardando', 'baixa', '2026-02-22 10:20:31', NULL);
INSERT INTO tickets (id, title, description, school_id, category_id, opened_by, assigned_to, status, priority, opened_at, closed_at) VALUES (72, 'Switch da sala 05 com problema', 'Dois computadores estão com o mesmo IP, causando conflito na rede.', 28, 3, 38, 3, 'finalizado', 'media', '2026-02-14 10:28:14', '2026-02-17 15:28:14');
INSERT INTO tickets (id, title, description, school_id, category_id, opened_by, assigned_to, status, priority, opened_at, closed_at) VALUES (73, 'HD com barulho estranho', 'O monitor fica em modo sleep mesmo com o computador ligado.', 25, 1, 23, NULL, 'aguardando', 'media', '2026-02-23 18:02:11', NULL);
INSERT INTO tickets (id, title, description, school_id, category_id, opened_by, assigned_to, status, priority, opened_at, closed_at) VALUES (74, 'HD com barulho estranho', 'O monitor fica em modo sleep mesmo com o computador ligado.', 49, 1, 13, 1, 'finalizado', 'media', '2026-02-08 08:07:04', '2026-02-09 09:07:04');
INSERT INTO tickets (id, title, description, school_id, category_id, opened_by, assigned_to, status, priority, opened_at, closed_at) VALUES (75, 'Fonte do computador queimada', 'O monitor fica em modo sleep mesmo com o computador ligado.', 17, 1, 35, 4, 'finalizado', 'media', '2026-02-06 14:12:32', '2026-02-10 17:12:32');
INSERT INTO tickets (id, title, description, school_id, category_id, opened_by, assigned_to, status, priority, opened_at, closed_at) VALUES (76, 'Câmera da entrada offline', 'O DVR não está gravando as imagens das câmeras.', 7, 9, 30, 2, 'finalizado', 'alta', '2026-02-22 10:02:32', '2026-02-27 13:02:32');
INSERT INTO tickets (id, title, description, school_id, category_id, opened_by, assigned_to, status, priority, opened_at, closed_at) VALUES (77, 'Notas não salvam no sistema', 'Minha senha do SIGE expirou e não consigo redefinir.', 35, 6, 37, 1, 'finalizado', 'media', '2026-02-02 09:30:51', '2026-02-03 11:30:51');
INSERT INTO tickets (id, title, description, school_id, category_id, opened_by, assigned_to, status, priority, opened_at, closed_at) VALUES (78, 'Erro ao abrir LibreOffice', 'O computador leva mais de 10 minutos para inicializar completamente.', 3, 2, 26, 2, 'resolvido', 'baixa', '2026-02-07 15:49:59', '2026-02-08 21:49:59');
INSERT INTO tickets (id, title, description, school_id, category_id, opened_by, assigned_to, status, priority, opened_at, closed_at) VALUES (79, 'Impressora fazendo barulho ao imprimir', 'A impressora não aparece mais nas opções de impressão da rede.', 3, 5, 26, 2, 'resolvido', 'media', '2026-02-18 18:29:34', '2026-02-21 22:29:34');
INSERT INTO tickets (id, title, description, school_id, category_id, opened_by, assigned_to, status, priority, opened_at, closed_at) VALUES (80, 'Senha do SIGE expirada', 'Minha senha do SIGE expirou e não consigo redefinir.', 46, 6, 12, NULL, 'aberto', 'baixa', '2026-02-06 16:15:56', NULL);
INSERT INTO tickets (id, title, description, school_id, category_id, opened_by, assigned_to, status, priority, opened_at, closed_at) VALUES (81, 'PABX desligado', 'Não estou conseguindo transferir ligações para outros ramais.', 14, 8, 8, 4, 'finalizado', 'alta', '2026-03-06 17:23:56', '2026-03-11 18:23:56');
INSERT INTO tickets (id, title, description, school_id, category_id, opened_by, assigned_to, status, priority, opened_at, closed_at) VALUES (82, 'HD com barulho estranho', 'O computador não liga. Suspeito que seja a fonte de alimentação.', 14, 1, 17, 4, 'finalizado', 'alta', '2026-03-12 16:54:29', '2026-03-14 19:54:29');
INSERT INTO tickets (id, title, description, school_id, category_id, opened_by, assigned_to, status, priority, opened_at, closed_at) VALUES (83, 'Cabo de rede danificado', 'A rede Wi-Fi do laboratório cai várias vezes durante as aulas.', 54, 3, 24, 4, 'finalizado', 'alta', '2026-03-23 16:08:30', '2026-03-26 22:08:30');
INSERT INTO tickets (id, title, description, school_id, category_id, opened_by, assigned_to, status, priority, opened_at, closed_at) VALUES (84, 'Switch da sala 05 com problema', 'A rede Wi-Fi do laboratório cai várias vezes durante as aulas.', 14, 3, 8, 3, 'resolvido', 'alta', '2026-03-01 12:00:49', '2026-03-05 14:00:49');
INSERT INTO tickets (id, title, description, school_id, category_id, opened_by, assigned_to, status, priority, opened_at, closed_at) VALUES (85, 'Toner acabou na impressora da secretaria', 'A impressora não aparece mais nas opções de impressão da rede.', 13, 5, 43, NULL, 'finalizado', 'baixa', '2026-03-12 10:09:52', '2026-03-13 15:09:52');
INSERT INTO tickets (id, title, description, school_id, category_id, opened_by, assigned_to, status, priority, opened_at, closed_at) VALUES (86, 'Teclado com teclas travadas', 'O computador não liga. Suspeito que seja a fonte de alimentação.', 25, 1, 43, NULL, 'resolvido', 'media', '2026-03-29 15:47:31', '2026-03-30 20:47:31');
INSERT INTO tickets (id, title, description, school_id, category_id, opened_by, assigned_to, status, priority, opened_at, closed_at) VALUES (87, 'Mouse não funciona', 'Algumas teclas não registram ao pressionar, dificultando as aulas práticas.', 41, 1, 8, 2, 'resolvido', 'media', '2026-03-24 10:45:18', '2026-03-26 12:45:18');
INSERT INTO tickets (id, title, description, school_id, category_id, opened_by, assigned_to, status, priority, opened_at, closed_at) VALUES (88, 'Impressora offline', 'O toner acabou e as impressões estão saindo apagadas.', 19, 5, 40, 1, 'resolvido', 'media', '2026-03-06 10:37:05', '2026-03-07 14:37:05');
INSERT INTO tickets (id, title, description, school_id, category_id, opened_by, assigned_to, status, priority, opened_at, closed_at) VALUES (89, 'Licença do Office expirada', 'Após reinstalar a impressora, o driver apresenta erro.', 28, 2, 38, 1, 'finalizado', 'media', '2026-03-03 08:53:21', '2026-03-07 10:53:21');
INSERT INTO tickets (id, title, description, school_id, category_id, opened_by, assigned_to, status, priority, opened_at, closed_at) VALUES (90, 'Antivírus desatualizado no laboratório', 'O sistema operacional trava frequentemente ao abrir múltiplos programas.', 20, 2, 28, 2, 'finalizado', 'media', '2026-03-28 15:50:46', '2026-03-31 20:50:46');
INSERT INTO tickets (id, title, description, school_id, category_id, opened_by, assigned_to, status, priority, opened_at, closed_at) VALUES (91, 'Projetor desligando durante aula', 'O projetor desliga sozinho após alguns minutos de uso.', 27, 4, 39, 2, 'aguardando', 'media', '2026-03-05 13:29:30', NULL);
INSERT INTO tickets (id, title, description, school_id, category_id, opened_by, assigned_to, status, priority, opened_at, closed_at) VALUES (92, 'Lentidão na rede interna do lab', 'O cabo de rede está fisicamente danificado e precisa ser substituído.', 13, 3, 43, 2, 'finalizado', 'media', '2026-03-16 15:43:34', '2026-03-17 19:43:34');
INSERT INTO tickets (id, title, description, school_id, category_id, opened_by, assigned_to, status, priority, opened_at, closed_at) VALUES (93, 'Sistema SIGE fora do ar', 'Minha senha do SIGE expirou e não consigo redefinir.', 10, 6, 13, NULL, 'em_andamento', 'baixa', '2026-03-28 17:26:30', NULL);
INSERT INTO tickets (id, title, description, school_id, category_id, opened_by, assigned_to, status, priority, opened_at, closed_at) VALUES (94, 'HD com barulho estranho', 'O equipamento não responde ao ser ligado. Já verifiquei a tomada e o cabo.', 17, 1, 35, 2, 'resolvido', 'media', '2026-03-21 10:20:24', '2026-03-24 16:20:24');
INSERT INTO tickets (id, title, description, school_id, category_id, opened_by, assigned_to, status, priority, opened_at, closed_at) VALUES (95, 'Cooler do processador parou', 'O equipamento não responde ao ser ligado. Já verifiquei a tomada e o cabo.', 44, 1, 36, 1, 'resolvido', 'media', '2026-03-29 12:34:20', '2026-03-31 15:34:20');
INSERT INTO tickets (id, title, description, school_id, category_id, opened_by, assigned_to, status, priority, opened_at, closed_at) VALUES (96, 'Cabo de rede danificado', 'O cabo de rede está fisicamente danificado e precisa ser substituído.', 44, 3, 36, 1, 'resolvido', 'media', '2026-03-03 16:32:02', '2026-03-06 21:32:02');
INSERT INTO tickets (id, title, description, school_id, category_id, opened_by, assigned_to, status, priority, opened_at, closed_at) VALUES (97, 'Erro de autenticação no Outlook', 'Não estou conseguindo acessar meu e-mail institucional pelo navegador.', 24, 7, 20, 3, 'finalizado', 'alta', '2026-03-30 17:24:21', '2026-03-31 19:24:21');
INSERT INTO tickets (id, title, description, school_id, category_id, opened_by, assigned_to, status, priority, opened_at, closed_at) VALUES (98, 'Senha do SIGE expirada', 'O sistema SIGE está inacessível desde esta manhã.', 44, 6, 36, 2, 'em_andamento', 'alta', '2026-03-20 08:31:33', NULL);
INSERT INTO tickets (id, title, description, school_id, category_id, opened_by, assigned_to, status, priority, opened_at, closed_at) VALUES (99, 'Erro ao abrir LibreOffice', 'O computador leva mais de 10 minutos para inicializar completamente.', 13, 2, 43, 4, 'finalizado', 'media', '2026-03-23 16:57:47', '2026-03-28 18:57:47');
INSERT INTO tickets (id, title, description, school_id, category_id, opened_by, assigned_to, status, priority, opened_at, closed_at) VALUES (100, 'Monitor sem sinal', 'O HD emite barulho de clique ao acessar arquivos grandes.', 3, 1, 26, 2, 'aguardando', 'media', '2026-03-01 11:17:22', NULL);
INSERT INTO tickets (id, title, description, school_id, category_id, opened_by, assigned_to, status, priority, opened_at, closed_at) VALUES (101, 'Projetor com imagem distorcida', 'O projetor não detecta o sinal HDMI do notebook.', 7, 4, 30, NULL, 'em_andamento', 'media', '2026-03-16 17:59:46', NULL);
INSERT INTO tickets (id, title, description, school_id, category_id, opened_by, assigned_to, status, priority, opened_at, closed_at) VALUES (102, 'Caixa de entrada cheia', 'Meu e-mail foi bloqueado por suspeita de envio de spam.', 14, 7, 31, 1, 'aguardando', 'alta', '2026-03-26 18:08:27', NULL);
INSERT INTO tickets (id, title, description, school_id, category_id, opened_by, assigned_to, status, priority, opened_at, closed_at) VALUES (103, 'Toner acabou na impressora da secretaria', 'A impressora não aparece mais nas opções de impressão da rede.', 5, 5, 35, 2, 'aguardando', 'media', '2026-03-25 18:04:22', NULL);
INSERT INTO tickets (id, title, description, school_id, category_id, opened_by, assigned_to, status, priority, opened_at, closed_at) VALUES (104, 'Papel enroscando na impressora', 'A impressora não aparece mais nas opções de impressão da rede.', 26, 5, 9, 4, 'aguardando', 'media', '2026-03-02 12:44:59', NULL);
INSERT INTO tickets (id, title, description, school_id, category_id, opened_by, assigned_to, status, priority, opened_at, closed_at) VALUES (105, 'IP duplicado na rede', 'A rede Wi-Fi do laboratório cai várias vezes durante as aulas.', 4, 3, 11, 1, 'finalizado', 'alta', '2026-03-18 08:39:55', '2026-03-22 13:39:55');
INSERT INTO tickets (id, title, description, school_id, category_id, opened_by, assigned_to, status, priority, opened_at, closed_at) VALUES (106, 'Teclado com teclas travadas', 'O HD emite barulho de clique ao acessar arquivos grandes.', 36, 1, 17, 1, 'finalizado', 'media', '2026-03-11 15:16:56', '2026-03-15 16:16:56');
INSERT INTO tickets (id, title, description, school_id, category_id, opened_by, assigned_to, status, priority, opened_at, closed_at) VALUES (107, 'Erro ao instalar software educacional', 'O antivírus está com definições de vírus desatualizadas há semanas.', 13, 2, 46, 4, 'em_andamento', 'media', '2026-03-28 17:07:38', NULL);
INSERT INTO tickets (id, title, description, school_id, category_id, opened_by, assigned_to, status, priority, opened_at, closed_at) VALUES (108, 'Computador não liga', 'O equipamento não responde ao ser ligado. Já verifiquei a tomada e o cabo.', 27, 1, 39, NULL, 'resolvido', 'media', '2026-03-21 18:52:24', '2026-03-25 20:52:24');
INSERT INTO tickets (id, title, description, school_id, category_id, opened_by, assigned_to, status, priority, opened_at, closed_at) VALUES (109, 'Atualização do Windows pendente', 'Ao abrir o LibreOffice aparece mensagem de erro e o programa fecha.', 53, 2, 7, NULL, 'aberto', 'media', '2026-03-11 13:16:56', NULL);
INSERT INTO tickets (id, title, description, school_id, category_id, opened_by, assigned_to, status, priority, opened_at, closed_at) VALUES (110, 'Driver de impressora corrompido', 'O sistema operacional trava frequentemente ao abrir múltiplos programas.', 46, 2, 12, NULL, 'em_andamento', 'media', '2026-03-11 16:54:12', NULL);
INSERT INTO tickets (id, title, description, school_id, category_id, opened_by, assigned_to, status, priority, opened_at, closed_at) VALUES (111, 'Licença do Office expirada', 'O antivírus está com definições de vírus desatualizadas há semanas.', 35, 2, 9, 3, 'finalizado', 'media', '2026-03-01 13:28:16', '2026-03-07 16:28:16');
INSERT INTO tickets (id, title, description, school_id, category_id, opened_by, assigned_to, status, priority, opened_at, closed_at) VALUES (112, 'Sistema lento para inicializar', 'O computador leva mais de 10 minutos para inicializar completamente.', 47, 2, 27, 1, 'aguardando', 'media', '2026-03-11 07:00:47', NULL);
INSERT INTO tickets (id, title, description, school_id, category_id, opened_by, assigned_to, status, priority, opened_at, closed_at) VALUES (113, 'Computador não liga', 'O HD emite barulho de clique ao acessar arquivos grandes.', 49, 1, 13, 3, 'finalizado', 'media', '2026-03-15 16:26:08', '2026-03-18 20:26:08');
INSERT INTO tickets (id, title, description, school_id, category_id, opened_by, assigned_to, status, priority, opened_at, closed_at) VALUES (114, 'Driver de impressora corrompido', 'O computador leva mais de 10 minutos para inicializar completamente.', 3, 2, 26, 4, 'aguardando', 'media', '2026-03-28 16:38:33', NULL);
INSERT INTO tickets (id, title, description, school_id, category_id, opened_by, assigned_to, status, priority, opened_at, closed_at) VALUES (115, 'Erro ao instalar software educacional', 'Após reinstalar a impressora, o driver apresenta erro.', 9, 2, 37, 4, 'resolvido', 'media', '2026-03-17 11:19:54', '2026-03-20 14:19:54');
INSERT INTO tickets (id, title, description, school_id, category_id, opened_by, assigned_to, status, priority, opened_at, closed_at) VALUES (116, 'Erro ao abrir LibreOffice', 'Ao abrir o LibreOffice aparece mensagem de erro e o programa fecha.', 20, 2, 28, NULL, 'finalizado', 'baixa', '2026-03-07 14:55:53', '2026-03-08 18:55:53');
INSERT INTO tickets (id, title, description, school_id, category_id, opened_by, assigned_to, status, priority, opened_at, closed_at) VALUES (117, 'Antivírus desatualizado no laboratório', 'O computador leva mais de 10 minutos para inicializar completamente.', 31, 2, 11, NULL, 'resolvido', 'alta', '2026-03-16 16:34:43', '2026-03-18 17:34:43');
INSERT INTO tickets (id, title, description, school_id, category_id, opened_by, assigned_to, status, priority, opened_at, closed_at) VALUES (118, 'Mouse não funciona', 'O HD emite barulho de clique ao acessar arquivos grandes.', 7, 1, 30, 3, 'finalizado', 'alta', '2026-03-23 12:37:52', '2026-03-27 13:37:52');
INSERT INTO tickets (id, title, description, school_id, category_id, opened_by, assigned_to, status, priority, opened_at, closed_at) VALUES (119, 'Cooler do processador parou', 'Algumas teclas não registram ao pressionar, dificultando as aulas práticas.', 53, 1, 7, NULL, 'em_andamento', 'media', '2026-03-18 15:13:45', NULL);
INSERT INTO tickets (id, title, description, school_id, category_id, opened_by, assigned_to, status, priority, opened_at, closed_at) VALUES (120, 'Ar-condicionado pingando água', 'A temperatura da sala do servidor está muito elevada.', 14, 10, 8, 3, 'aguardando', 'media', '2026-03-09 08:04:12', NULL);
INSERT INTO tickets (id, title, description, school_id, category_id, opened_by, assigned_to, status, priority, opened_at, closed_at) VALUES (121, 'Impressora fazendo barulho ao imprimir', 'O toner acabou e as impressões estão saindo apagadas.', 19, 5, 21, NULL, 'aberto', 'media', '2026-03-10 13:17:56', NULL);
INSERT INTO tickets (id, title, description, school_id, category_id, opened_by, assigned_to, status, priority, opened_at, closed_at) VALUES (122, 'Impressora não aparece na rede', 'A impressora está imprimindo páginas em branco.', 53, 5, 7, NULL, 'finalizado', 'alta', '2026-03-29 08:53:22', '2026-03-31 10:53:22');
INSERT INTO tickets (id, title, description, school_id, category_id, opened_by, assigned_to, status, priority, opened_at, closed_at) VALUES (123, 'Erro de autenticação no Outlook', 'O Outlook não autentica com as credenciais institucionais.', 12, 7, 20, 4, 'finalizado', 'media', '2026-03-13 12:28:39', '2026-03-17 14:28:39');
INSERT INTO tickets (id, title, description, school_id, category_id, opened_by, assigned_to, status, priority, opened_at, closed_at) VALUES (124, 'Toner acabou na impressora da secretaria', 'A impressora aparece offline no sistema mesmo estando ligada.', 32, 5, 18, 3, 'em_andamento', 'baixa', '2026-03-06 14:34:24', NULL);
INSERT INTO tickets (id, title, description, school_id, category_id, opened_by, assigned_to, status, priority, opened_at, closed_at) VALUES (125, 'Fonte do computador queimada', 'O equipamento não responde ao ser ligado. Já verifiquei a tomada e o cabo.', 53, 1, 7, 4, 'arquivado', 'baixa', '2026-03-13 11:38:20', '2026-03-24 12:38:20');
INSERT INTO tickets (id, title, description, school_id, category_id, opened_by, assigned_to, status, priority, opened_at, closed_at) VALUES (126, 'Papel enroscando na impressora', 'A impressora está imprimindo páginas em branco.', 25, 5, 23, 1, 'resolvido', 'baixa', '2026-03-26 14:00:56', '2026-03-29 15:00:56');
INSERT INTO tickets (id, title, description, school_id, category_id, opened_by, assigned_to, status, priority, opened_at, closed_at) VALUES (127, 'Ar-condicionado do lab parou', 'O ar-condicionado está fazendo barulho incomum ao ligar.', 46, 10, 12, 2, 'finalizado', 'media', '2026-03-23 10:41:22', '2026-03-29 15:41:22');
INSERT INTO tickets (id, title, description, school_id, category_id, opened_by, assigned_to, status, priority, opened_at, closed_at) VALUES (128, 'Senha do SIGE expirada', 'Não consigo fazer login no SIGE com as minhas credenciais.', 41, 6, 8, 3, 'finalizado', 'alta', '2026-03-22 09:22:40', '2026-03-27 15:22:40');
INSERT INTO tickets (id, title, description, school_id, category_id, opened_by, assigned_to, status, priority, opened_at, closed_at) VALUES (129, 'Ar-condicionado fazendo barulho', 'O ar-condicionado está fazendo barulho incomum ao ligar.', 12, 10, 20, 1, 'em_andamento', 'alta', '2026-03-04 07:25:17', NULL);
INSERT INTO tickets (id, title, description, school_id, category_id, opened_by, assigned_to, status, priority, opened_at, closed_at) VALUES (130, 'Não consigo acessar e-mail institucional', 'O Outlook não autentica com as credenciais institucionais.', 19, 7, 33, 1, 'finalizado', 'media', '2026-03-06 11:15:36', '2026-03-12 15:15:36');
INSERT INTO tickets (id, title, description, school_id, category_id, opened_by, assigned_to, status, priority, opened_at, closed_at) VALUES (131, 'Não consigo acessar e-mail institucional', 'Ao tentar enviar arquivos grandes por e-mail, ocorre erro.', 43, 7, 45, NULL, 'em_andamento', 'alta', '2026-03-19 07:59:02', NULL);
INSERT INTO tickets (id, title, description, school_id, category_id, opened_by, assigned_to, status, priority, opened_at, closed_at) VALUES (132, 'Driver de impressora corrompido', 'Ao abrir o LibreOffice aparece mensagem de erro e o programa fecha.', 46, 2, 12, 2, 'aguardando', 'media', '2026-03-23 17:24:45', NULL);
INSERT INTO tickets (id, title, description, school_id, category_id, opened_by, assigned_to, status, priority, opened_at, closed_at) VALUES (133, 'Cabo de rede danificado', 'Dois computadores estão com o mesmo IP, causando conflito na rede.', 40, 3, 42, NULL, 'aberto', 'media', '2026-03-05 15:09:26', NULL);
INSERT INTO tickets (id, title, description, school_id, category_id, opened_by, assigned_to, status, priority, opened_at, closed_at) VALUES (134, 'Lentidão na rede interna do lab', 'Os computadores da sala 12 estão sem acesso à internet desde ontem.', 12, 3, 20, 4, 'em_andamento', 'media', '2026-03-13 10:07:08', NULL);
INSERT INTO tickets (id, title, description, school_id, category_id, opened_by, assigned_to, status, priority, opened_at, closed_at) VALUES (135, 'Roteador reiniciando sozinho', 'Dois computadores estão com o mesmo IP, causando conflito na rede.', 35, 3, 25, 4, 'em_andamento', 'alta', '2026-03-27 07:06:18', NULL);
INSERT INTO tickets (id, title, description, school_id, category_id, opened_by, assigned_to, status, priority, opened_at, closed_at) VALUES (136, 'Sistema lento para inicializar', 'Após reinstalar a impressora, o driver apresenta erro.', 39, 2, 41, 1, 'em_andamento', 'media', '2026-03-17 15:50:19', NULL);
INSERT INTO tickets (id, title, description, school_id, category_id, opened_by, assigned_to, status, priority, opened_at, closed_at) VALUES (137, 'Windows travando constantemente', 'O sistema operacional trava frequentemente ao abrir múltiplos programas.', 25, 2, 43, 1, 'finalizado', 'alta', '2026-03-11 18:13:16', '2026-03-15 20:13:16');
INSERT INTO tickets (id, title, description, school_id, category_id, opened_by, assigned_to, status, priority, opened_at, closed_at) VALUES (138, 'Projetor sem imagem', 'A imagem do projetor está distorcida e fora de foco.', 19, 4, 21, 3, 'finalizado', 'media', '2026-03-08 17:27:23', '2026-03-13 21:27:23');
INSERT INTO tickets (id, title, description, school_id, category_id, opened_by, assigned_to, status, priority, opened_at, closed_at) VALUES (139, 'E-mail bloqueado por spam', 'Meu e-mail foi bloqueado por suspeita de envio de spam.', 20, 7, 28, 3, 'aguardando', 'media', '2026-04-04 16:15:42', NULL);
INSERT INTO tickets (id, title, description, school_id, category_id, opened_by, assigned_to, status, priority, opened_at, closed_at) VALUES (140, 'Sem acesso à internet na sala 12', 'Dois computadores estão com o mesmo IP, causando conflito na rede.', 35, 3, 25, NULL, 'aberto', 'alta', '2026-04-08 18:52:05', NULL);
INSERT INTO tickets (id, title, description, school_id, category_id, opened_by, assigned_to, status, priority, opened_at, closed_at) VALUES (141, 'Papel enroscando na impressora', 'A impressora não aparece mais nas opções de impressão da rede.', 26, 5, 24, 1, 'finalizado', 'media', '2026-04-28 13:13:36', '2026-05-01 16:13:36');
INSERT INTO tickets (id, title, description, school_id, category_id, opened_by, assigned_to, status, priority, opened_at, closed_at) VALUES (142, 'Projetor sem imagem', 'A imagem do projetor está distorcida e fora de foco.', 43, 4, 39, 3, 'resolvido', 'media', '2026-04-08 18:43:26', '2026-04-10 22:43:26');
INSERT INTO tickets (id, title, description, school_id, category_id, opened_by, assigned_to, status, priority, opened_at, closed_at) VALUES (143, 'Projetor sem imagem', 'A lâmpada do projetor queimou durante a aula.', 46, 4, 12, NULL, 'finalizado', 'alta', '2026-04-05 12:44:20', '2026-04-10 18:44:20');
INSERT INTO tickets (id, title, description, school_id, category_id, opened_by, assigned_to, status, priority, opened_at, closed_at) VALUES (144, 'Sem acesso à internet na sala 12', 'O switch da sala 05 está com leds piscando de forma anormal.', 24, 3, 25, 1, 'finalizado', 'baixa', '2026-04-02 11:30:38', '2026-04-08 15:30:38');
INSERT INTO tickets (id, title, description, school_id, category_id, opened_by, assigned_to, status, priority, opened_at, closed_at) VALUES (145, 'Erro ao instalar software educacional', 'Após reinstalar a impressora, o driver apresenta erro.', 15, 2, 28, 1, 'resolvido', 'media', '2026-04-26 08:13:49', '2026-04-30 12:13:49');
INSERT INTO tickets (id, title, description, school_id, category_id, opened_by, assigned_to, status, priority, opened_at, closed_at) VALUES (146, 'Telefone com chiado na linha', 'O ramal da sala dos professores está sem sinal de linha.', 5, 8, 35, 1, 'arquivado', 'alta', '2026-04-10 12:45:02', '2026-04-21 16:45:02');
INSERT INTO tickets (id, title, description, school_id, category_id, opened_by, assigned_to, status, priority, opened_at, closed_at) VALUES (147, 'Licença do Office expirada', 'Ao abrir o LibreOffice aparece mensagem de erro e o programa fecha.', 45, 2, 30, NULL, 'resolvido', 'baixa', '2026-04-29 13:25:08', '2026-05-01 17:25:08');
INSERT INTO tickets (id, title, description, school_id, category_id, opened_by, assigned_to, status, priority, opened_at, closed_at) VALUES (148, 'Erro de login no SIGE', 'O relatório de frequência está gerando dados incorretos.', 27, 6, 39, NULL, 'aguardando', 'media', '2026-04-12 12:30:11', NULL);
INSERT INTO tickets (id, title, description, school_id, category_id, opened_by, assigned_to, status, priority, opened_at, closed_at) VALUES (149, 'E-mail bloqueado por spam', 'Não estou conseguindo acessar meu e-mail institucional pelo navegador.', 14, 7, 31, 4, 'aguardando', 'alta', '2026-04-04 11:04:22', NULL);
INSERT INTO tickets (id, title, description, school_id, category_id, opened_by, assigned_to, status, priority, opened_at, closed_at) VALUES (150, 'Sem acesso à internet na sala 12', 'O switch da sala 05 está com leds piscando de forma anormal.', 26, 3, 27, 4, 'arquivado', 'media', '2026-04-04 14:37:39', '2026-04-13 16:37:39');
INSERT INTO tickets (id, title, description, school_id, category_id, opened_by, assigned_to, status, priority, opened_at, closed_at) VALUES (151, 'Projetor sem imagem', 'A imagem do projetor está distorcida e fora de foco.', 4, 4, 11, 4, 'aguardando', 'alta', '2026-04-06 08:15:03', NULL);
INSERT INTO tickets (id, title, description, school_id, category_id, opened_by, assigned_to, status, priority, opened_at, closed_at) VALUES (152, 'Impressora não aparece na rede', 'O papel está atolando toda vez que tentamos imprimir.', 47, 5, 27, 1, 'aguardando', 'alta', '2026-04-04 12:20:52', NULL);
INSERT INTO tickets (id, title, description, school_id, category_id, opened_by, assigned_to, status, priority, opened_at, closed_at) VALUES (153, 'Notas não salvam no sistema', 'Ao tentar salvar as notas, o sistema retorna erro de conexão.', 8, 6, 14, 4, 'aguardando', 'media', '2026-04-27 16:46:01', NULL);
INSERT INTO tickets (id, title, description, school_id, category_id, opened_by, assigned_to, status, priority, opened_at, closed_at) VALUES (154, 'Windows travando constantemente', 'Após reinstalar a impressora, o driver apresenta erro.', 35, 2, 9, 1, 'finalizado', 'media', '2026-04-14 07:34:07', '2026-04-17 11:34:07');
INSERT INTO tickets (id, title, description, school_id, category_id, opened_by, assigned_to, status, priority, opened_at, closed_at) VALUES (155, 'Teclado com teclas travadas', 'O monitor fica em modo sleep mesmo com o computador ligado.', 11, 1, 40, NULL, 'aberto', 'baixa', '2026-04-27 14:15:52', NULL);
INSERT INTO tickets (id, title, description, school_id, category_id, opened_by, assigned_to, status, priority, opened_at, closed_at) VALUES (156, 'Caixa de entrada cheia', 'Meu e-mail foi bloqueado por suspeita de envio de spam.', 53, 7, 7, NULL, 'em_andamento', 'media', '2026-04-08 09:44:55', NULL);
INSERT INTO tickets (id, title, description, school_id, category_id, opened_by, assigned_to, status, priority, opened_at, closed_at) VALUES (157, 'Senha do SIGE expirada', 'O sistema SIGE está inacessível desde esta manhã.', 41, 6, 8, NULL, 'aguardando', 'alta', '2026-04-27 12:35:13', NULL);
INSERT INTO tickets (id, title, description, school_id, category_id, opened_by, assigned_to, status, priority, opened_at, closed_at) VALUES (158, 'DVR sem gravação', 'A câmera da entrada principal está aparecendo offline no sistema.', 33, 9, 21, 1, 'em_andamento', 'media', '2026-04-17 12:05:21', NULL);
INSERT INTO tickets (id, title, description, school_id, category_id, opened_by, assigned_to, status, priority, opened_at, closed_at) VALUES (159, 'PABX desligado', 'Há chiado constante na linha do telefone da secretaria.', 7, 8, 30, 2, 'em_andamento', 'alta', '2026-04-08 07:41:12', NULL);
INSERT INTO tickets (id, title, description, school_id, category_id, opened_by, assigned_to, status, priority, opened_at, closed_at) VALUES (160, 'SIGE lento para carregar turmas', 'O relatório de frequência está gerando dados incorretos.', 27, 6, 14, 4, 'em_andamento', 'media', '2026-04-28 14:18:32', NULL);
INSERT INTO tickets (id, title, description, school_id, category_id, opened_by, assigned_to, status, priority, opened_at, closed_at) VALUES (161, 'Telefone com chiado na linha', 'Não estou conseguindo transferir ligações para outros ramais.', 43, 8, 39, 3, 'em_andamento', 'media', '2026-04-19 10:56:56', NULL);
INSERT INTO tickets (id, title, description, school_id, category_id, opened_by, assigned_to, status, priority, opened_at, closed_at) VALUES (162, 'Licença do Office expirada', 'O sistema operacional trava frequentemente ao abrir múltiplos programas.', 27, 2, 39, 2, 'resolvido', 'media', '2026-04-16 10:26:35', '2026-04-18 16:26:35');
INSERT INTO tickets (id, title, description, school_id, category_id, opened_by, assigned_to, status, priority, opened_at, closed_at) VALUES (163, 'Toner acabou na impressora da secretaria', 'O toner acabou e as impressões estão saindo apagadas.', 3, 5, 26, NULL, 'aberto', 'media', '2026-04-05 10:56:09', NULL);
INSERT INTO tickets (id, title, description, school_id, category_id, opened_by, assigned_to, status, priority, opened_at, closed_at) VALUES (164, 'Ar-condicionado fazendo barulho', 'O ar-condicionado está fazendo barulho incomum ao ligar.', 32, 10, 18, 1, 'em_andamento', 'alta', '2026-04-26 09:35:28', NULL);
INSERT INTO tickets (id, title, description, school_id, category_id, opened_by, assigned_to, status, priority, opened_at, closed_at) VALUES (165, 'Sem acesso à internet na sala 12', 'O cabo de rede está fisicamente danificado e precisa ser substituído.', 21, 3, 36, NULL, 'aberto', 'alta', '2026-04-17 16:40:31', NULL);
INSERT INTO tickets (id, title, description, school_id, category_id, opened_by, assigned_to, status, priority, opened_at, closed_at) VALUES (166, 'Monitor sem sinal', 'O monitor fica em modo sleep mesmo com o computador ligado.', 32, 1, 18, 3, 'em_andamento', 'baixa', '2026-04-30 12:15:01', NULL);
INSERT INTO tickets (id, title, description, school_id, category_id, opened_by, assigned_to, status, priority, opened_at, closed_at) VALUES (167, 'Ar-condicionado pingando água', 'O ar-condicionado está pingando água no chão do laboratório.', 19, 10, 33, 4, 'resolvido', 'media', '2026-04-19 15:11:39', '2026-04-20 16:11:39');
INSERT INTO tickets (id, title, description, school_id, category_id, opened_by, assigned_to, status, priority, opened_at, closed_at) VALUES (168, 'Projetor não conecta no notebook', 'A lâmpada do projetor queimou durante a aula.', 28, 4, 38, 2, 'finalizado', 'media', '2026-04-02 11:20:12', '2026-04-03 14:20:12');
INSERT INTO tickets (id, title, description, school_id, category_id, opened_by, assigned_to, status, priority, opened_at, closed_at) VALUES (169, 'Erro ao instalar software educacional', 'Após reinstalar a impressora, o driver apresenta erro.', 12, 2, 16, 2, 'finalizado', 'media', '2026-04-18 09:07:24', '2026-04-22 10:07:24');
INSERT INTO tickets (id, title, description, school_id, category_id, opened_by, assigned_to, status, priority, opened_at, closed_at) VALUES (170, 'Antivírus desatualizado no laboratório', 'O computador leva mais de 10 minutos para inicializar completamente.', 43, 2, 45, NULL, 'finalizado', 'baixa', '2026-04-28 11:45:55', '2026-05-03 16:45:55');

-- ----------------------------------------------------------------
-- Comentários
-- ----------------------------------------------------------------
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (1, 1, 3, 'Vou verificar o problema. Aguarde.', '2026-01-23 05:03:07', '2026-01-23 05:03:07');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (2, 1, 24, 'Realizei a verificação. limpeza interna realizada foi corrigido.', '2026-01-23 12:03:07', '2026-01-23 12:03:07');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (3, 1, 3, 'Funcionou! Podem fechar o chamado.', '2026-01-23 16:03:07', '2026-01-23 16:03:07');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (4, 2, 26, 'Bom dia! Acabei de abrir este chamado. O problema está impactando minhas aulas.', '2026-01-16 02:18:40', '2026-01-16 02:18:40');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (5, 3, 1, 'Chamado recebido. Estou analisando o problema.', '2026-01-20 00:49:26', '2026-01-20 00:49:26');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (6, 3, 14, 'Já identifiquei a causa. Estou trabalhando na solução, aguarde.', '2026-01-20 06:49:26', '2026-01-20 06:49:26');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (7, 3, 1, 'Bom dia, tem alguma previsão de quando resolve? Estou precisando para as aulas.', '2026-01-20 16:49:26', '2026-01-20 16:49:26');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (8, 3, 14, 'Estou aguardando a chegada de uma peça. Prazo estimado: 2 dias úteis.', '2026-01-21 00:49:26', '2026-01-21 00:49:26');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (9, 3, 1, 'Ok, aguardo. Obrigado pela informação.', '2026-01-21 17:49:26', '2026-01-21 17:49:26');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (10, 4, 2, 'Tentamos contato com o professor mas não obtivemos retorno. Arquivando por inatividade.', '2026-01-09 18:12:08', '2026-01-09 18:12:08');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (11, 4, 36, 'Desculpe, estava em licença. O problema se resolveu sozinho. Pode arquivar mesmo.', '2026-01-10 03:12:08', '2026-01-10 03:12:08');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (12, 5, 20, 'Bom dia! Acabei de abrir este chamado. O problema está impactando minhas aulas.', '2026-01-09 18:29:29', '2026-01-09 18:29:29');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (13, 6, 1, 'Vou verificar o problema. Aguarde.', '2026-01-19 02:51:33', '2026-01-19 02:51:33');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (14, 6, 41, 'Realizei a verificação. cabo substituído foi corrigido.', '2026-01-19 16:51:33', '2026-01-19 16:51:33');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (15, 6, 1, 'Funcionou! Podem fechar o chamado.', '2026-01-20 10:51:33', '2026-01-20 10:51:33');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (16, 7, 1, 'Tomei conhecimento do problema. Vou agendar visita técnica.', '2026-01-19 22:19:16', '2026-01-19 22:19:16');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (17, 7, 7, 'Visita agendada para amanhã às 9h. Por favor, garanta acesso ao laboratório.', '2026-01-20 14:19:16', '2026-01-20 14:19:16');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (18, 7, 1, 'Ok, estarei lá para receber o técnico.', '2026-01-20 17:19:16', '2026-01-20 17:19:16');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (19, 7, 7, 'Visita realizada. Problema mais complexo do que esperado. Continuando análise.', '2026-01-20 22:19:16', '2026-01-20 22:19:16');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (20, 8, 2, 'Chamado recebido. Vou verificar o problema ainda hoje.', '2026-01-25 18:53:53', '2026-01-25 18:53:53');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (21, 8, 43, 'Fui até a unidade e identifiquei o problema. Já iniciando o reparo.', '2026-01-26 03:53:53', '2026-01-26 03:53:53');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (22, 8, 2, 'Reparo concluído. O equipamento está funcionando normalmente. Fechando o chamado.', '2026-01-26 13:53:53', '2026-01-26 13:53:53');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (23, 9, 3, 'Chamado registrado. Vou analisar remotamente primeiro.', '2026-01-22 15:57:35', '2026-01-22 15:57:35');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (24, 9, 19, 'Não foi possível resolver remotamente. Agendei visita presencial para amanhã.', '2026-01-23 07:57:35', '2026-01-23 07:57:35');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (25, 9, 3, 'Visita realizada. fonte substituída corrigido com sucesso.', '2026-01-24 01:57:35', '2026-01-24 01:57:35');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (26, 9, 19, 'Ótimo! Já testei e está funcionando perfeitamente. Obrigado!', '2026-01-24 10:57:35', '2026-01-24 10:57:35');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (27, 9, 3, 'Fico feliz que resolveu. Qualquer novo problema, abra outro chamado.', '2026-01-24 21:57:35', '2026-01-24 21:57:35');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (28, 10, 2, 'Tomei conhecimento do problema. Vou agendar visita técnica.', '2026-01-05 22:42:02', '2026-01-05 22:42:02');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (29, 10, 16, 'Visita agendada para amanhã às 9h. Por favor, garanta acesso ao laboratório.', '2026-01-06 17:42:02', '2026-01-06 17:42:02');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (30, 10, 2, 'Ok, estarei lá para receber o técnico.', '2026-01-06 23:42:02', '2026-01-06 23:42:02');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (31, 10, 16, 'Visita realizada. Problema mais complexo do que esperado. Continuando análise.', '2026-01-07 08:42:02', '2026-01-07 08:42:02');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (32, 11, 3, 'Vou verificar o problema. Aguarde.', '2026-01-26 04:31:21', '2026-01-26 04:31:21');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (33, 11, 24, 'Realizei a verificação. cabo substituído foi corrigido.', '2026-01-26 08:31:21', '2026-01-26 08:31:21');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (34, 11, 3, 'Funcionou! Podem fechar o chamado.', '2026-01-26 13:31:21', '2026-01-26 13:31:21');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (35, 12, 2, 'Chamado recebido. Estou analisando o problema.', '2026-01-14 12:35:06', '2026-01-14 12:35:06');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (36, 12, 45, 'Já identifiquei a causa. Estou trabalhando na solução, aguarde.', '2026-01-15 06:35:06', '2026-01-15 06:35:06');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (37, 12, 2, 'Bom dia, tem alguma previsão de quando resolve? Estou precisando para as aulas.', '2026-01-15 16:35:06', '2026-01-15 16:35:06');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (38, 12, 45, 'Estou aguardando a chegada de uma peça. Prazo estimado: 2 dias úteis.', '2026-01-16 10:35:06', '2026-01-16 10:35:06');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (39, 12, 2, 'Ok, aguardo. Obrigado pela informação.', '2026-01-16 18:35:06', '2026-01-16 18:35:06');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (40, 13, 2, 'Tomei conhecimento do problema. Vou agendar visita técnica.', '2026-01-25 23:59:16', '2026-01-25 23:59:16');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (41, 13, 13, 'Visita agendada para amanhã às 9h. Por favor, garanta acesso ao laboratório.', '2026-01-26 00:59:16', '2026-01-26 00:59:16');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (42, 13, 2, 'Ok, estarei lá para receber o técnico.', '2026-01-26 18:59:16', '2026-01-26 18:59:16');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (43, 13, 13, 'Visita realizada. Problema mais complexo do que esperado. Continuando análise.', '2026-01-27 02:59:16', '2026-01-27 02:59:16');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (44, 14, 1, 'Chamado recebido. Vou verificar o problema ainda hoje.', '2026-01-29 16:26:35', '2026-01-29 16:26:35');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (45, 14, 19, 'Fui até a unidade e identifiquei o problema. Já iniciando o reparo.', '2026-01-30 07:26:35', '2026-01-30 07:26:35');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (46, 14, 1, 'Reparo concluído. O equipamento está funcionando normalmente. Fechando o chamado.', '2026-01-30 14:26:35', '2026-01-30 14:26:35');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (47, 15, 3, 'Vou verificar o problema. Aguarde.', '2026-01-26 12:48:52', '2026-01-26 12:48:52');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (48, 15, 31, 'Realizei a verificação. fonte substituída foi corrigido.', '2026-01-26 15:48:52', '2026-01-26 15:48:52');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (49, 15, 3, 'Funcionou! Podem fechar o chamado.', '2026-01-26 18:48:52', '2026-01-26 18:48:52');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (50, 16, 2, 'Chamado recebido. Estou analisando o problema.', '2026-01-18 11:26:13', '2026-01-18 11:26:13');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (51, 16, 37, 'Já identifiquei a causa. Estou trabalhando na solução, aguarde.', '2026-01-19 07:26:13', '2026-01-19 07:26:13');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (52, 16, 2, 'Bom dia, tem alguma previsão de quando resolve? Estou precisando para as aulas.', '2026-01-20 01:26:13', '2026-01-20 01:26:13');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (53, 16, 37, 'Estou aguardando a chegada de uma peça. Prazo estimado: 2 dias úteis.', '2026-01-20 10:26:13', '2026-01-20 10:26:13');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (54, 16, 2, 'Ok, aguardo. Obrigado pela informação.', '2026-01-20 19:26:13', '2026-01-20 19:26:13');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (55, 17, 2, 'Recebi o chamado. Estarei na escola amanhã cedo para verificar.', '2026-01-06 00:35:19', '2026-01-06 00:35:19');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (56, 17, 7, 'Problema identificado: sistema atualizado. Realizando correção.', '2026-01-06 18:35:19', '2026-01-06 18:35:19');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (57, 17, 2, 'Tudo resolvido. Pode confirmar se está funcionando para fechar o chamado.', '2026-01-07 03:35:19', '2026-01-07 03:35:19');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (58, 17, 7, 'Confirmado! Muito obrigado pelo atendimento rápido.', '2026-01-07 23:35:19', '2026-01-07 23:35:19');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (59, 18, 9, 'Preciso de suporte urgente. O problema já dura 2 dias.', '2026-01-30 17:53:10', '2026-01-30 17:53:10');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (60, 19, 4, 'Chamado recebido e encaminhado para análise técnica.', '2026-01-07 07:28:36', '2026-01-07 07:28:36');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (61, 19, 13, 'Problema localizado. Aplicando solução.', '2026-01-07 10:28:36', '2026-01-07 10:28:36');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (62, 19, 4, 'Solução aplicada. Por favor confirme se o problema foi resolvido.', '2026-01-08 01:28:36', '2026-01-08 01:28:36');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (63, 19, 13, 'Confirmei aqui, está funcionando normalmente. Obrigado!', '2026-01-08 20:28:36', '2026-01-08 20:28:36');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (64, 20, 4, 'Chamado registrado. Vou analisar remotamente primeiro.', '2026-01-24 18:15:53', '2026-01-24 18:15:53');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (65, 20, 20, 'Não foi possível resolver remotamente. Agendei visita presencial para amanhã.', '2026-01-24 19:15:53', '2026-01-24 19:15:53');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (66, 20, 4, 'Visita realizada. fonte substituída corrigido com sucesso.', '2026-01-25 13:15:53', '2026-01-25 13:15:53');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (67, 20, 20, 'Ótimo! Já testei e está funcionando perfeitamente. Obrigado!', '2026-01-26 09:15:53', '2026-01-26 09:15:53');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (68, 20, 4, 'Fico feliz que resolveu. Qualquer novo problema, abra outro chamado.', '2026-01-26 12:15:53', '2026-01-26 12:15:53');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (69, 21, 1, 'Chamado recebido e encaminhado para análise técnica.', '2026-01-19 06:01:16', '2026-01-19 06:01:16');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (70, 21, 37, 'Problema localizado. Aplicando solução.', '2026-01-20 01:01:16', '2026-01-20 01:01:16');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (71, 21, 1, 'Solução aplicada. Por favor confirme se o problema foi resolvido.', '2026-01-20 12:01:16', '2026-01-20 12:01:16');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (72, 21, 37, 'Confirmei aqui, está funcionando normalmente. Obrigado!', '2026-01-21 00:01:16', '2026-01-21 00:01:16');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (73, 22, 1, 'Recebi o chamado. Estarei na escola amanhã cedo para verificar.', '2026-01-11 16:58:03', '2026-01-11 16:58:03');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (74, 22, 9, 'Problema identificado: senha redefinida. Realizando correção.', '2026-01-11 17:58:03', '2026-01-11 17:58:03');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (75, 22, 1, 'Tudo resolvido. Pode confirmar se está funcionando para fechar o chamado.', '2026-01-11 22:58:03', '2026-01-11 22:58:03');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (76, 22, 9, 'Confirmado! Muito obrigado pelo atendimento rápido.', '2026-01-12 01:58:03', '2026-01-12 01:58:03');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (77, 23, 1, 'Vou verificar o problema. Aguarde.', '2026-01-07 04:23:09', '2026-01-07 04:23:09');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (78, 23, 21, 'Realizei a verificação. cabo substituído foi corrigido.', '2026-01-07 16:23:09', '2026-01-07 16:23:09');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (79, 23, 1, 'Funcionou! Podem fechar o chamado.', '2026-01-08 10:23:09', '2026-01-08 10:23:09');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (80, 24, 4, 'Vou verificar o problema. Aguarde.', '2026-01-02 11:05:56', '2026-01-02 11:05:56');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (81, 24, 18, 'Realizei a verificação. equipamento trocado por reserva foi corrigido.', '2026-01-02 18:05:56', '2026-01-02 18:05:56');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (82, 24, 4, 'Funcionou! Podem fechar o chamado.', '2026-01-03 09:05:56', '2026-01-03 09:05:56');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (83, 25, 3, 'Chamado registrado. Vou analisar remotamente primeiro.', '2026-01-09 16:53:00', '2026-01-09 16:53:00');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (84, 25, 24, 'Não foi possível resolver remotamente. Agendei visita presencial para amanhã.', '2026-01-09 19:53:00', '2026-01-09 19:53:00');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (85, 25, 3, 'Visita realizada. driver reinstalado corrigido com sucesso.', '2026-01-10 00:53:00', '2026-01-10 00:53:00');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (86, 25, 24, 'Ótimo! Já testei e está funcionando perfeitamente. Obrigado!', '2026-01-10 06:53:00', '2026-01-10 06:53:00');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (87, 25, 3, 'Fico feliz que resolveu. Qualquer novo problema, abra outro chamado.', '2026-01-10 07:53:00', '2026-01-10 07:53:00');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (88, 26, 3, 'Vou verificar o problema. Aguarde.', '2026-01-23 18:40:07', '2026-01-23 18:40:07');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (89, 26, 35, 'Realizei a verificação. driver reinstalado foi corrigido.', '2026-01-24 04:40:07', '2026-01-24 04:40:07');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (90, 26, 3, 'Funcionou! Podem fechar o chamado.', '2026-01-24 17:40:07', '2026-01-24 17:40:07');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (91, 27, 3, 'Recebi o chamado. Estarei na escola amanhã cedo para verificar.', '2026-01-20 21:44:13', '2026-01-20 21:44:13');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (92, 27, 14, 'Problema identificado: cabo substituído. Realizando correção.', '2026-01-21 00:44:13', '2026-01-21 00:44:13');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (93, 27, 3, 'Tudo resolvido. Pode confirmar se está funcionando para fechar o chamado.', '2026-01-21 17:44:13', '2026-01-21 17:44:13');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (94, 27, 14, 'Confirmado! Muito obrigado pelo atendimento rápido.', '2026-01-22 06:44:13', '2026-01-22 06:44:13');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (95, 28, 1, 'Chamado registrado. Vou analisar remotamente primeiro.', '2026-01-11 04:06:57', '2026-01-11 04:06:57');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (96, 28, 35, 'Não foi possível resolver remotamente. Agendei visita presencial para amanhã.', '2026-01-11 11:06:57', '2026-01-11 11:06:57');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (97, 28, 1, 'Visita realizada. equipamento trocado por reserva corrigido com sucesso.', '2026-01-11 15:06:57', '2026-01-11 15:06:57');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (98, 28, 35, 'Ótimo! Já testei e está funcionando perfeitamente. Obrigado!', '2026-01-12 08:06:57', '2026-01-12 08:06:57');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (99, 28, 1, 'Fico feliz que resolveu. Qualquer novo problema, abra outro chamado.', '2026-01-12 20:06:57', '2026-01-12 20:06:57');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (100, 29, 3, 'Vou verificar o problema. Aguarde.', '2026-01-14 01:18:37', '2026-01-14 01:18:37');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (101, 29, 46, 'Realizei a verificação. sistema atualizado foi corrigido.', '2026-01-14 07:18:37', '2026-01-14 07:18:37');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (102, 29, 3, 'Funcionou! Podem fechar o chamado.', '2026-01-15 02:18:37', '2026-01-15 02:18:37');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (103, 30, 2, 'Chamado recebido. Estou analisando o problema.', '2026-01-28 18:04:27', '2026-01-28 18:04:27');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (104, 30, 11, 'Já identifiquei a causa. Estou trabalhando na solução, aguarde.', '2026-01-29 01:04:27', '2026-01-29 01:04:27');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (105, 30, 2, 'Bom dia, tem alguma previsão de quando resolve? Estou precisando para as aulas.', '2026-01-29 15:04:27', '2026-01-29 15:04:27');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (106, 30, 11, 'Estou aguardando a chegada de uma peça. Prazo estimado: 2 dias úteis.', '2026-01-29 19:04:27', '2026-01-29 19:04:27');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (107, 30, 2, 'Ok, aguardo. Obrigado pela informação.', '2026-01-29 21:04:27', '2026-01-29 21:04:27');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (108, 31, 1, 'Problema identificado. Precisamos aguardar aprovação para compra de peça.', '2026-01-02 00:20:56', '2026-01-02 00:20:56');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (109, 31, 23, 'Solicitação de compra enviada para aprovação da direção.', '2026-01-02 02:20:56', '2026-01-02 02:20:56');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (110, 31, 1, 'Conseguiram aprovação? O problema continua afetando o laboratório.', '2026-01-02 10:20:56', '2026-01-02 10:20:56');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (111, 31, 23, 'Ainda aguardando aprovação. Assim que sair, atendemos imediatamente.', '2026-01-02 14:20:56', '2026-01-02 14:20:56');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (112, 32, 2, 'Tomei conhecimento do problema. Vou agendar visita técnica.', '2026-01-26 04:17:40', '2026-01-26 04:17:40');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (113, 32, 14, 'Visita agendada para amanhã às 9h. Por favor, garanta acesso ao laboratório.', '2026-01-26 10:17:40', '2026-01-26 10:17:40');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (114, 32, 2, 'Ok, estarei lá para receber o técnico.', '2026-01-26 12:17:40', '2026-01-26 12:17:40');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (115, 32, 14, 'Visita realizada. Problema mais complexo do que esperado. Continuando análise.', '2026-01-26 16:17:40', '2026-01-26 16:17:40');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (116, 33, 1, 'Chamado recebido. Vou verificar o problema ainda hoje.', '2026-01-03 15:36:53', '2026-01-03 15:36:53');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (117, 33, 19, 'Fui até a unidade e identifiquei o problema. Já iniciando o reparo.', '2026-01-03 16:36:53', '2026-01-03 16:36:53');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (118, 33, 1, 'Reparo concluído. O equipamento está funcionando normalmente. Fechando o chamado.', '2026-01-04 07:36:53', '2026-01-04 07:36:53');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (119, 34, 4, 'Chamado recebido e encaminhado para análise técnica.', '2026-01-10 04:02:40', '2026-01-10 04:02:40');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (120, 34, 43, 'Problema localizado. Aplicando solução.', '2026-01-10 23:02:40', '2026-01-10 23:02:40');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (121, 34, 4, 'Solução aplicada. Por favor confirme se o problema foi resolvido.', '2026-01-11 00:02:40', '2026-01-11 00:02:40');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (122, 34, 43, 'Confirmei aqui, está funcionando normalmente. Obrigado!', '2026-01-11 13:02:40', '2026-01-11 13:02:40');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (123, 35, 2, 'Chamado registrado. Vou analisar remotamente primeiro.', '2026-01-13 03:46:04', '2026-01-13 03:46:04');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (124, 35, 43, 'Não foi possível resolver remotamente. Agendei visita presencial para amanhã.', '2026-01-13 11:46:04', '2026-01-13 11:46:04');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (125, 35, 2, 'Visita realizada. driver reinstalado corrigido com sucesso.', '2026-01-13 12:46:04', '2026-01-13 12:46:04');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (126, 35, 43, 'Ótimo! Já testei e está funcionando perfeitamente. Obrigado!', '2026-01-13 16:46:04', '2026-01-13 16:46:04');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (127, 35, 2, 'Fico feliz que resolveu. Qualquer novo problema, abra outro chamado.', '2026-01-13 17:46:04', '2026-01-13 17:46:04');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (128, 36, 1, 'Recebi o chamado. Estarei na escola amanhã cedo para verificar.', '2026-02-14 12:31:29', '2026-02-14 12:31:29');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (129, 36, 36, 'Problema identificado: fonte substituída. Realizando correção.', '2026-02-15 03:31:29', '2026-02-15 03:31:29');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (130, 36, 1, 'Tudo resolvido. Pode confirmar se está funcionando para fechar o chamado.', '2026-02-15 19:31:29', '2026-02-15 19:31:29');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (131, 36, 36, 'Confirmado! Muito obrigado pelo atendimento rápido.', '2026-02-16 09:31:29', '2026-02-16 09:31:29');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (132, 37, 3, 'Chamado recebido. Vou verificar o problema ainda hoje.', '2026-02-07 08:04:41', '2026-02-07 08:04:41');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (133, 37, 18, 'Fui até a unidade e identifiquei o problema. Já iniciando o reparo.', '2026-02-07 14:04:41', '2026-02-07 14:04:41');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (134, 37, 3, 'Reparo concluído. O equipamento está funcionando normalmente. Fechando o chamado.', '2026-02-07 17:04:41', '2026-02-07 17:04:41');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (135, 38, 4, 'Recebi o chamado. Estarei na escola amanhã cedo para verificar.', '2026-02-21 22:10:34', '2026-02-21 22:10:34');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (136, 38, 32, 'Problema identificado: driver reinstalado. Realizando correção.', '2026-02-22 04:10:34', '2026-02-22 04:10:34');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (137, 38, 4, 'Tudo resolvido. Pode confirmar se está funcionando para fechar o chamado.', '2026-02-23 00:10:34', '2026-02-23 00:10:34');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (138, 38, 32, 'Confirmado! Muito obrigado pelo atendimento rápido.', '2026-02-23 12:10:34', '2026-02-23 12:10:34');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (139, 39, 3, 'Chamado registrado. Vou analisar remotamente primeiro.', '2026-02-21 07:32:30', '2026-02-21 07:32:30');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (140, 39, 24, 'Não foi possível resolver remotamente. Agendei visita presencial para amanhã.', '2026-02-21 20:32:30', '2026-02-21 20:32:30');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (141, 39, 3, 'Visita realizada. toner trocado corrigido com sucesso.', '2026-02-22 16:32:30', '2026-02-22 16:32:30');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (142, 39, 24, 'Ótimo! Já testei e está funcionando perfeitamente. Obrigado!', '2026-02-22 22:32:30', '2026-02-22 22:32:30');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (143, 39, 3, 'Fico feliz que resolveu. Qualquer novo problema, abra outro chamado.', '2026-02-23 10:32:30', '2026-02-23 10:32:30');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (144, 40, 2, 'Chamado registrado. Vou analisar remotamente primeiro.', '2026-02-05 18:46:04', '2026-02-05 18:46:04');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (145, 40, 8, 'Não foi possível resolver remotamente. Agendei visita presencial para amanhã.', '2026-02-05 20:46:04', '2026-02-05 20:46:04');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (146, 40, 2, 'Visita realizada. toner trocado corrigido com sucesso.', '2026-02-06 04:46:04', '2026-02-06 04:46:04');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (147, 40, 8, 'Ótimo! Já testei e está funcionando perfeitamente. Obrigado!', '2026-02-06 19:46:04', '2026-02-06 19:46:04');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (148, 40, 2, 'Fico feliz que resolveu. Qualquer novo problema, abra outro chamado.', '2026-02-07 13:46:04', '2026-02-07 13:46:04');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (149, 41, 1, 'Vou verificar o problema. Aguarde.', '2026-02-22 16:03:12', '2026-02-22 16:03:12');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (150, 41, 14, 'Realizei a verificação. driver reinstalado foi corrigido.', '2026-02-23 12:03:12', '2026-02-23 12:03:12');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (151, 41, 1, 'Funcionou! Podem fechar o chamado.', '2026-02-23 18:03:12', '2026-02-23 18:03:12');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (152, 42, 3, 'Vou verificar o problema. Aguarde.', '2026-02-19 23:50:59', '2026-02-19 23:50:59');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (153, 42, 46, 'Realizei a verificação. senha redefinida foi corrigido.', '2026-02-20 09:50:59', '2026-02-20 09:50:59');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (154, 42, 3, 'Funcionou! Podem fechar o chamado.', '2026-02-21 05:50:59', '2026-02-21 05:50:59');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (155, 43, 3, 'Recebi o chamado. Estarei na escola amanhã cedo para verificar.', '2026-02-13 19:12:15', '2026-02-13 19:12:15');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (156, 43, 14, 'Problema identificado: configuração de rede corrigida. Realizando correção.', '2026-02-14 12:12:15', '2026-02-14 12:12:15');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (157, 43, 3, 'Tudo resolvido. Pode confirmar se está funcionando para fechar o chamado.', '2026-02-15 07:12:15', '2026-02-15 07:12:15');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (158, 43, 14, 'Confirmado! Muito obrigado pelo atendimento rápido.', '2026-02-15 22:12:15', '2026-02-15 22:12:15');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (159, 44, 17, 'Abri este chamado ontem e ainda não recebi retorno. Podem verificar?', '2026-02-11 12:07:50', '2026-02-11 12:07:50');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (160, 45, 4, 'Chamado recebido e encaminhado para análise técnica.', '2026-02-18 10:48:16', '2026-02-18 10:48:16');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (161, 45, 18, 'Problema localizado. Aplicando solução.', '2026-02-18 19:48:16', '2026-02-18 19:48:16');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (162, 45, 4, 'Solução aplicada. Por favor confirme se o problema foi resolvido.', '2026-02-19 01:48:16', '2026-02-19 01:48:16');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (163, 45, 18, 'Confirmei aqui, está funcionando normalmente. Obrigado!', '2026-02-19 03:48:16', '2026-02-19 03:48:16');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (164, 46, 24, 'Preciso de suporte urgente. O problema já dura 2 dias.', '2026-02-21 17:10:56', '2026-02-21 17:10:56');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (165, 47, 2, 'Problema identificado. Precisamos aguardar aprovação para compra de peça.', '2026-02-06 12:24:22', '2026-02-06 12:24:22');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (166, 47, 12, 'Solicitação de compra enviada para aprovação da direção.', '2026-02-07 07:24:22', '2026-02-07 07:24:22');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (167, 47, 2, 'Conseguiram aprovação? O problema continua afetando o laboratório.', '2026-02-07 14:24:22', '2026-02-07 14:24:22');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (168, 47, 12, 'Ainda aguardando aprovação. Assim que sair, atendemos imediatamente.', '2026-02-07 17:24:22', '2026-02-07 17:24:22');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (169, 48, 4, 'Recebi o chamado. Estarei na escola amanhã cedo para verificar.', '2026-02-08 11:42:48', '2026-02-08 11:42:48');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (170, 48, 23, 'Problema identificado: senha redefinida. Realizando correção.', '2026-02-08 18:42:48', '2026-02-08 18:42:48');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (171, 48, 4, 'Tudo resolvido. Pode confirmar se está funcionando para fechar o chamado.', '2026-02-08 20:42:48', '2026-02-08 20:42:48');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (172, 48, 23, 'Confirmado! Muito obrigado pelo atendimento rápido.', '2026-02-09 11:42:48', '2026-02-09 11:42:48');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (173, 49, 4, 'Chamado recebido. Vou verificar o problema ainda hoje.', '2026-02-06 23:27:58', '2026-02-06 23:27:58');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (174, 49, 24, 'Fui até a unidade e identifiquei o problema. Já iniciando o reparo.', '2026-02-07 17:27:58', '2026-02-07 17:27:58');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (175, 49, 4, 'Reparo concluído. O equipamento está funcionando normalmente. Fechando o chamado.', '2026-02-08 12:27:58', '2026-02-08 12:27:58');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (176, 50, 4, 'Chamado recebido. Vou verificar o problema ainda hoje.', '2026-02-14 09:09:31', '2026-02-14 09:09:31');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (177, 50, 9, 'Fui até a unidade e identifiquei o problema. Já iniciando o reparo.', '2026-02-14 16:09:31', '2026-02-14 16:09:31');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (178, 50, 4, 'Reparo concluído. O equipamento está funcionando normalmente. Fechando o chamado.', '2026-02-14 17:09:31', '2026-02-14 17:09:31');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (179, 51, 4, 'Chamado recebido e encaminhado para análise técnica.', '2026-02-22 21:30:32', '2026-02-22 21:30:32');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (180, 51, 34, 'Problema localizado. Aplicando solução.', '2026-02-23 15:30:32', '2026-02-23 15:30:32');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (181, 51, 4, 'Solução aplicada. Por favor confirme se o problema foi resolvido.', '2026-02-23 19:30:32', '2026-02-23 19:30:32');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (182, 51, 34, 'Confirmei aqui, está funcionando normalmente. Obrigado!', '2026-02-23 22:30:32', '2026-02-23 22:30:32');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (183, 52, 1, 'Chamado recebido. Verificando.', '2026-02-19 02:28:01', '2026-02-19 02:28:01');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (184, 52, 30, 'Após análise, o problema reportado não foi reproduzido. Arquivando.', '2026-02-19 03:28:01', '2026-02-19 03:28:01');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (185, 52, 1, 'Entendido. Se o problema reaparecer, abrirei novo chamado.', '2026-02-19 21:28:01', '2026-02-19 21:28:01');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (186, 53, 30, 'Bom dia! Acabei de abrir este chamado. O problema está impactando minhas aulas.', '2026-02-12 08:17:25', '2026-02-12 08:17:25');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (187, 54, 3, 'Chamado recebido. Vou verificar o problema ainda hoje.', '2026-02-25 06:26:20', '2026-02-25 06:26:20');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (188, 54, 40, 'Fui até a unidade e identifiquei o problema. Já iniciando o reparo.', '2026-02-25 09:26:20', '2026-02-25 09:26:20');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (189, 54, 3, 'Reparo concluído. O equipamento está funcionando normalmente. Fechando o chamado.', '2026-02-26 04:26:20', '2026-02-26 04:26:20');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (190, 55, 2, 'Vou verificar o problema. Aguarde.', '2026-02-17 21:25:44', '2026-02-17 21:25:44');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (191, 55, 18, 'Realizei a verificação. IP configurado corretamente foi corrigido.', '2026-02-18 14:25:44', '2026-02-18 14:25:44');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (192, 55, 2, 'Funcionou! Podem fechar o chamado.', '2026-02-19 05:25:44', '2026-02-19 05:25:44');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (193, 56, 2, 'Tentamos contato com o professor mas não obtivemos retorno. Arquivando por inatividade.', '2026-02-21 15:32:24', '2026-02-21 15:32:24');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (194, 56, 12, 'Desculpe, estava em licença. O problema se resolveu sozinho. Pode arquivar mesmo.', '2026-02-22 04:32:24', '2026-02-22 04:32:24');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (195, 57, 4, 'Chamado recebido. Vou verificar o problema ainda hoje.', '2026-02-13 17:11:16', '2026-02-13 17:11:16');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (196, 57, 11, 'Fui até a unidade e identifiquei o problema. Já iniciando o reparo.', '2026-02-14 13:11:16', '2026-02-14 13:11:16');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (197, 57, 4, 'Reparo concluído. O equipamento está funcionando normalmente. Fechando o chamado.', '2026-02-15 08:11:16', '2026-02-15 08:11:16');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (198, 58, 3, 'Vou verificar o problema. Aguarde.', '2026-02-24 11:19:59', '2026-02-24 11:19:59');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (199, 58, 38, 'Realizei a verificação. cabo substituído foi corrigido.', '2026-02-25 05:19:59', '2026-02-25 05:19:59');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (200, 58, 3, 'Funcionou! Podem fechar o chamado.', '2026-02-25 18:19:59', '2026-02-25 18:19:59');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (201, 59, 4, 'Chamado recebido e encaminhado para análise técnica.', '2026-02-20 20:23:28', '2026-02-20 20:23:28');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (202, 59, 40, 'Problema localizado. Aplicando solução.', '2026-02-20 21:23:28', '2026-02-20 21:23:28');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (203, 59, 4, 'Solução aplicada. Por favor confirme se o problema foi resolvido.', '2026-02-21 09:23:28', '2026-02-21 09:23:28');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (204, 59, 40, 'Confirmei aqui, está funcionando normalmente. Obrigado!', '2026-02-21 19:23:28', '2026-02-21 19:23:28');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (205, 60, 4, 'Chamado registrado. Vou analisar remotamente primeiro.', '2026-02-11 11:03:12', '2026-02-11 11:03:12');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (206, 60, 7, 'Não foi possível resolver remotamente. Agendei visita presencial para amanhã.', '2026-02-11 12:03:12', '2026-02-11 12:03:12');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (207, 60, 4, 'Visita realizada. senha redefinida corrigido com sucesso.', '2026-02-12 01:03:12', '2026-02-12 01:03:12');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (208, 60, 7, 'Ótimo! Já testei e está funcionando perfeitamente. Obrigado!', '2026-02-12 11:03:12', '2026-02-12 11:03:12');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (209, 60, 4, 'Fico feliz que resolveu. Qualquer novo problema, abra outro chamado.', '2026-02-12 18:03:12', '2026-02-12 18:03:12');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (210, 61, 2, 'Chamado recebido. Vou verificar o problema ainda hoje.', '2026-02-25 23:53:22', '2026-02-25 23:53:22');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (211, 61, 21, 'Fui até a unidade e identifiquei o problema. Já iniciando o reparo.', '2026-02-26 04:53:22', '2026-02-26 04:53:22');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (212, 61, 2, 'Reparo concluído. O equipamento está funcionando normalmente. Fechando o chamado.', '2026-02-26 08:53:22', '2026-02-26 08:53:22');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (213, 62, 3, 'Chamado recebido. Vou verificar o problema ainda hoje.', '2026-02-25 17:00:29', '2026-02-25 17:00:29');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (214, 62, 23, 'Fui até a unidade e identifiquei o problema. Já iniciando o reparo.', '2026-02-26 00:00:29', '2026-02-26 00:00:29');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (215, 62, 3, 'Reparo concluído. O equipamento está funcionando normalmente. Fechando o chamado.', '2026-02-26 09:00:29', '2026-02-26 09:00:29');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (216, 63, 1, 'Chamado registrado. Vou analisar remotamente primeiro.', '2026-02-07 09:15:52', '2026-02-07 09:15:52');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (217, 63, 8, 'Não foi possível resolver remotamente. Agendei visita presencial para amanhã.', '2026-02-08 02:15:52', '2026-02-08 02:15:52');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (218, 63, 1, 'Visita realizada. limpeza interna realizada corrigido com sucesso.', '2026-02-08 08:15:52', '2026-02-08 08:15:52');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (219, 63, 8, 'Ótimo! Já testei e está funcionando perfeitamente. Obrigado!', '2026-02-08 15:15:52', '2026-02-08 15:15:52');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (220, 63, 1, 'Fico feliz que resolveu. Qualquer novo problema, abra outro chamado.', '2026-02-09 10:15:52', '2026-02-09 10:15:52');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (221, 64, 3, 'Chamado registrado. Vou analisar remotamente primeiro.', '2026-02-27 14:21:41', '2026-02-27 14:21:41');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (222, 64, 41, 'Não foi possível resolver remotamente. Agendei visita presencial para amanhã.', '2026-02-28 10:21:41', '2026-02-28 10:21:41');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (223, 64, 3, 'Visita realizada. fonte substituída corrigido com sucesso.', '2026-03-01 01:21:41', '2026-03-01 01:21:41');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (224, 64, 41, 'Ótimo! Já testei e está funcionando perfeitamente. Obrigado!', '2026-03-01 17:21:41', '2026-03-01 17:21:41');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (225, 64, 3, 'Fico feliz que resolveu. Qualquer novo problema, abra outro chamado.', '2026-03-02 04:21:41', '2026-03-02 04:21:41');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (226, 65, 4, 'Chamado registrado. Vou analisar remotamente primeiro.', '2026-02-23 22:16:58', '2026-02-23 22:16:58');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (227, 65, 14, 'Não foi possível resolver remotamente. Agendei visita presencial para amanhã.', '2026-02-24 13:16:58', '2026-02-24 13:16:58');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (228, 65, 4, 'Visita realizada. driver reinstalado corrigido com sucesso.', '2026-02-25 02:16:58', '2026-02-25 02:16:58');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (229, 65, 14, 'Ótimo! Já testei e está funcionando perfeitamente. Obrigado!', '2026-02-25 06:16:58', '2026-02-25 06:16:58');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (230, 65, 4, 'Fico feliz que resolveu. Qualquer novo problema, abra outro chamado.', '2026-02-25 08:16:58', '2026-02-25 08:16:58');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (231, 66, 3, 'Chamado registrado. Vou analisar remotamente primeiro.', '2026-02-13 12:23:52', '2026-02-13 12:23:52');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (232, 66, 27, 'Não foi possível resolver remotamente. Agendei visita presencial para amanhã.', '2026-02-14 04:23:52', '2026-02-14 04:23:52');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (233, 66, 3, 'Visita realizada. IP configurado corretamente corrigido com sucesso.', '2026-02-14 12:23:52', '2026-02-14 12:23:52');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (234, 66, 27, 'Ótimo! Já testei e está funcionando perfeitamente. Obrigado!', '2026-02-15 01:23:52', '2026-02-15 01:23:52');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (235, 66, 3, 'Fico feliz que resolveu. Qualquer novo problema, abra outro chamado.', '2026-02-15 11:23:52', '2026-02-15 11:23:52');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (236, 67, 1, 'Chamado recebido e encaminhado para análise técnica.', '2026-02-12 20:52:20', '2026-02-12 20:52:20');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (237, 67, 13, 'Problema localizado. Aplicando solução.', '2026-02-13 05:52:20', '2026-02-13 05:52:20');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (238, 67, 1, 'Solução aplicada. Por favor confirme se o problema foi resolvido.', '2026-02-13 13:52:20', '2026-02-13 13:52:20');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (239, 67, 13, 'Confirmei aqui, está funcionando normalmente. Obrigado!', '2026-02-13 19:52:20', '2026-02-13 19:52:20');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (240, 68, 1, 'Chamado recebido. Encaminhei para o setor responsável.', '2026-03-01 00:45:12', '2026-03-01 00:45:12');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (241, 68, 36, 'Aguardando resposta do fornecedor para prosseguir.', '2026-03-01 13:45:12', '2026-03-01 13:45:12');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (242, 68, 1, 'Entendido. Quanto tempo estima que demorará?', '2026-03-02 02:45:12', '2026-03-02 02:45:12');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (243, 68, 36, 'Previsão de 3 a 5 dias úteis para retorno do fornecedor.', '2026-03-02 20:45:12', '2026-03-02 20:45:12');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (244, 68, 1, 'Ok, aguardamos. Mas preciso de solução logo pois afeta as aulas.', '2026-03-03 06:45:12', '2026-03-03 06:45:12');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (245, 69, 8, 'Abri este chamado ontem e ainda não recebi retorno. Podem verificar?', '2026-02-06 01:21:22', '2026-02-06 01:21:22');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (246, 70, 3, 'Tomei conhecimento do problema. Vou agendar visita técnica.', '2026-02-08 22:50:16', '2026-02-08 22:50:16');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (247, 70, 26, 'Visita agendada para amanhã às 9h. Por favor, garanta acesso ao laboratório.', '2026-02-09 14:50:16', '2026-02-09 14:50:16');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (248, 70, 3, 'Ok, estarei lá para receber o técnico.', '2026-02-10 03:50:16', '2026-02-10 03:50:16');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (249, 70, 26, 'Visita realizada. Problema mais complexo do que esperado. Continuando análise.', '2026-02-10 16:50:16', '2026-02-10 16:50:16');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (250, 71, 4, 'Problema identificado. Precisamos aguardar aprovação para compra de peça.', '2026-02-22 20:06:31', '2026-02-22 20:06:31');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (251, 71, 20, 'Solicitação de compra enviada para aprovação da direção.', '2026-02-23 15:06:31', '2026-02-23 15:06:31');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (252, 71, 4, 'Conseguiram aprovação? O problema continua afetando o laboratório.', '2026-02-24 03:06:31', '2026-02-24 03:06:31');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (253, 71, 20, 'Ainda aguardando aprovação. Assim que sair, atendemos imediatamente.', '2026-02-24 05:06:31', '2026-02-24 05:06:31');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (254, 72, 3, 'Chamado registrado. Vou analisar remotamente primeiro.', '2026-02-14 23:43:14', '2026-02-14 23:43:14');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (255, 72, 38, 'Não foi possível resolver remotamente. Agendei visita presencial para amanhã.', '2026-02-15 10:43:14', '2026-02-15 10:43:14');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (256, 72, 3, 'Visita realizada. limpeza interna realizada corrigido com sucesso.', '2026-02-16 00:43:14', '2026-02-16 00:43:14');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (257, 72, 38, 'Ótimo! Já testei e está funcionando perfeitamente. Obrigado!', '2026-02-16 10:43:14', '2026-02-16 10:43:14');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (258, 72, 3, 'Fico feliz que resolveu. Qualquer novo problema, abra outro chamado.', '2026-02-16 20:43:14', '2026-02-16 20:43:14');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (259, 73, 3, 'Problema identificado. Precisamos aguardar aprovação para compra de peça.', '2026-02-23 23:30:11', '2026-02-23 23:30:11');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (260, 73, 23, 'Solicitação de compra enviada para aprovação da direção.', '2026-02-24 00:30:11', '2026-02-24 00:30:11');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (261, 73, 3, 'Conseguiram aprovação? O problema continua afetando o laboratório.', '2026-02-24 04:30:11', '2026-02-24 04:30:11');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (262, 73, 23, 'Ainda aguardando aprovação. Assim que sair, atendemos imediatamente.', '2026-02-24 12:30:11', '2026-02-24 12:30:11');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (263, 74, 1, 'Chamado recebido. Vou verificar o problema ainda hoje.', '2026-02-09 00:07:04', '2026-02-09 00:07:04');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (264, 74, 13, 'Fui até a unidade e identifiquei o problema. Já iniciando o reparo.', '2026-02-09 14:07:04', '2026-02-09 14:07:04');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (265, 74, 1, 'Reparo concluído. O equipamento está funcionando normalmente. Fechando o chamado.', '2026-02-10 06:07:04', '2026-02-10 06:07:04');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (266, 75, 4, 'Recebi o chamado. Estarei na escola amanhã cedo para verificar.', '2026-02-07 01:39:32', '2026-02-07 01:39:32');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (267, 75, 35, 'Problema identificado: limpeza interna realizada. Realizando correção.', '2026-02-07 13:39:32', '2026-02-07 13:39:32');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (268, 75, 4, 'Tudo resolvido. Pode confirmar se está funcionando para fechar o chamado.', '2026-02-07 18:39:32', '2026-02-07 18:39:32');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (269, 75, 35, 'Confirmado! Muito obrigado pelo atendimento rápido.', '2026-02-08 11:39:32', '2026-02-08 11:39:32');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (270, 76, 2, 'Recebi o chamado. Estarei na escola amanhã cedo para verificar.', '2026-02-22 16:32:32', '2026-02-22 16:32:32');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (271, 76, 30, 'Problema identificado: senha redefinida. Realizando correção.', '2026-02-22 17:32:32', '2026-02-22 17:32:32');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (272, 76, 2, 'Tudo resolvido. Pode confirmar se está funcionando para fechar o chamado.', '2026-02-23 13:32:32', '2026-02-23 13:32:32');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (273, 76, 30, 'Confirmado! Muito obrigado pelo atendimento rápido.', '2026-02-24 03:32:32', '2026-02-24 03:32:32');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (274, 77, 1, 'Chamado recebido. Vou verificar o problema ainda hoje.', '2026-02-02 16:10:51', '2026-02-02 16:10:51');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (275, 77, 37, 'Fui até a unidade e identifiquei o problema. Já iniciando o reparo.', '2026-02-03 12:10:51', '2026-02-03 12:10:51');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (276, 77, 1, 'Reparo concluído. O equipamento está funcionando normalmente. Fechando o chamado.', '2026-02-03 19:10:51', '2026-02-03 19:10:51');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (277, 78, 2, 'Vou verificar o problema. Aguarde.', '2026-02-08 13:07:59', '2026-02-08 13:07:59');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (278, 78, 26, 'Realizei a verificação. sistema atualizado foi corrigido.', '2026-02-08 15:07:59', '2026-02-08 15:07:59');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (279, 78, 2, 'Funcionou! Podem fechar o chamado.', '2026-02-08 16:07:59', '2026-02-08 16:07:59');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (280, 79, 2, 'Chamado recebido e encaminhado para análise técnica.', '2026-02-19 17:24:34', '2026-02-19 17:24:34');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (281, 79, 26, 'Problema localizado. Aplicando solução.', '2026-02-20 00:24:34', '2026-02-20 00:24:34');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (282, 79, 2, 'Solução aplicada. Por favor confirme se o problema foi resolvido.', '2026-02-20 15:24:34', '2026-02-20 15:24:34');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (283, 79, 26, 'Confirmei aqui, está funcionando normalmente. Obrigado!', '2026-02-21 05:24:34', '2026-02-21 05:24:34');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (284, 80, 12, 'Bom dia! Acabei de abrir este chamado. O problema está impactando minhas aulas.', '2026-02-07 07:46:56', '2026-02-07 07:46:56');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (285, 81, 4, 'Recebi o chamado. Estarei na escola amanhã cedo para verificar.', '2026-03-06 23:08:56', '2026-03-06 23:08:56');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (286, 81, 8, 'Problema identificado: cabo substituído. Realizando correção.', '2026-03-07 03:08:56', '2026-03-07 03:08:56');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (287, 81, 4, 'Tudo resolvido. Pode confirmar se está funcionando para fechar o chamado.', '2026-03-07 17:08:56', '2026-03-07 17:08:56');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (288, 81, 8, 'Confirmado! Muito obrigado pelo atendimento rápido.', '2026-03-07 22:08:56', '2026-03-07 22:08:56');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (289, 82, 4, 'Chamado registrado. Vou analisar remotamente primeiro.', '2026-03-13 03:33:29', '2026-03-13 03:33:29');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (290, 82, 17, 'Não foi possível resolver remotamente. Agendei visita presencial para amanhã.', '2026-03-13 07:33:29', '2026-03-13 07:33:29');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (291, 82, 4, 'Visita realizada. limpeza interna realizada corrigido com sucesso.', '2026-03-13 11:33:29', '2026-03-13 11:33:29');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (292, 82, 17, 'Ótimo! Já testei e está funcionando perfeitamente. Obrigado!', '2026-03-13 22:33:29', '2026-03-13 22:33:29');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (293, 82, 4, 'Fico feliz que resolveu. Qualquer novo problema, abra outro chamado.', '2026-03-14 16:33:29', '2026-03-14 16:33:29');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (294, 83, 4, 'Chamado recebido. Vou verificar o problema ainda hoje.', '2026-03-23 23:49:30', '2026-03-23 23:49:30');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (295, 83, 24, 'Fui até a unidade e identifiquei o problema. Já iniciando o reparo.', '2026-03-24 13:49:30', '2026-03-24 13:49:30');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (296, 83, 4, 'Reparo concluído. O equipamento está funcionando normalmente. Fechando o chamado.', '2026-03-25 02:49:30', '2026-03-25 02:49:30');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (297, 84, 3, 'Vou verificar o problema. Aguarde.', '2026-03-02 07:09:49', '2026-03-02 07:09:49');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (298, 84, 8, 'Realizei a verificação. fonte substituída foi corrigido.', '2026-03-02 21:09:49', '2026-03-02 21:09:49');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (299, 84, 3, 'Funcionou! Podem fechar o chamado.', '2026-03-03 17:09:49', '2026-03-03 17:09:49');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (300, 85, 4, 'Chamado registrado. Vou analisar remotamente primeiro.', '2026-03-12 19:29:52', '2026-03-12 19:29:52');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (301, 85, 43, 'Não foi possível resolver remotamente. Agendei visita presencial para amanhã.', '2026-03-13 05:29:52', '2026-03-13 05:29:52');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (302, 85, 4, 'Visita realizada. cabo substituído corrigido com sucesso.', '2026-03-14 00:29:52', '2026-03-14 00:29:52');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (303, 85, 43, 'Ótimo! Já testei e está funcionando perfeitamente. Obrigado!', '2026-03-14 01:29:52', '2026-03-14 01:29:52');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (304, 85, 4, 'Fico feliz que resolveu. Qualquer novo problema, abra outro chamado.', '2026-03-14 16:29:52', '2026-03-14 16:29:52');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (305, 86, 2, 'Vou verificar o problema. Aguarde.', '2026-03-30 04:30:31', '2026-03-30 04:30:31');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (306, 86, 43, 'Realizei a verificação. senha redefinida foi corrigido.', '2026-03-30 06:30:31', '2026-03-30 06:30:31');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (307, 86, 2, 'Funcionou! Podem fechar o chamado.', '2026-03-30 19:30:31', '2026-03-30 19:30:31');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (308, 87, 2, 'Chamado recebido e encaminhado para análise técnica.', '2026-03-24 21:44:18', '2026-03-24 21:44:18');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (309, 87, 8, 'Problema localizado. Aplicando solução.', '2026-03-25 15:44:18', '2026-03-25 15:44:18');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (310, 87, 2, 'Solução aplicada. Por favor confirme se o problema foi resolvido.', '2026-03-26 02:44:18', '2026-03-26 02:44:18');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (311, 87, 8, 'Confirmei aqui, está funcionando normalmente. Obrigado!', '2026-03-26 18:44:18', '2026-03-26 18:44:18');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (312, 88, 1, 'Vou verificar o problema. Aguarde.', '2026-03-06 15:51:05', '2026-03-06 15:51:05');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (313, 88, 40, 'Realizei a verificação. toner trocado foi corrigido.', '2026-03-06 21:51:05', '2026-03-06 21:51:05');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (314, 88, 1, 'Funcionou! Podem fechar o chamado.', '2026-03-07 01:51:05', '2026-03-07 01:51:05');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (315, 89, 1, 'Chamado recebido. Vou verificar o problema ainda hoje.', '2026-03-03 20:23:21', '2026-03-03 20:23:21');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (316, 89, 38, 'Fui até a unidade e identifiquei o problema. Já iniciando o reparo.', '2026-03-04 16:23:21', '2026-03-04 16:23:21');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (317, 89, 1, 'Reparo concluído. O equipamento está funcionando normalmente. Fechando o chamado.', '2026-03-05 05:23:21', '2026-03-05 05:23:21');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (318, 90, 2, 'Chamado registrado. Vou analisar remotamente primeiro.', '2026-03-28 21:40:46', '2026-03-28 21:40:46');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (319, 90, 28, 'Não foi possível resolver remotamente. Agendei visita presencial para amanhã.', '2026-03-29 00:40:46', '2026-03-29 00:40:46');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (320, 90, 2, 'Visita realizada. configuração de rede corrigida corrigido com sucesso.', '2026-03-29 20:40:46', '2026-03-29 20:40:46');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (321, 90, 28, 'Ótimo! Já testei e está funcionando perfeitamente. Obrigado!', '2026-03-30 13:40:46', '2026-03-30 13:40:46');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (322, 90, 2, 'Fico feliz que resolveu. Qualquer novo problema, abra outro chamado.', '2026-03-30 18:40:46', '2026-03-30 18:40:46');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (323, 91, 2, 'Problema identificado. Precisamos aguardar aprovação para compra de peça.', '2026-03-05 19:36:30', '2026-03-05 19:36:30');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (324, 91, 39, 'Solicitação de compra enviada para aprovação da direção.', '2026-03-06 12:36:30', '2026-03-06 12:36:30');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (325, 91, 2, 'Conseguiram aprovação? O problema continua afetando o laboratório.', '2026-03-06 22:36:30', '2026-03-06 22:36:30');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (326, 91, 39, 'Ainda aguardando aprovação. Assim que sair, atendemos imediatamente.', '2026-03-07 17:36:30', '2026-03-07 17:36:30');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (327, 92, 2, 'Recebi o chamado. Estarei na escola amanhã cedo para verificar.', '2026-03-17 05:37:34', '2026-03-17 05:37:34');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (328, 92, 43, 'Problema identificado: senha redefinida. Realizando correção.', '2026-03-17 14:37:34', '2026-03-17 14:37:34');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (329, 92, 2, 'Tudo resolvido. Pode confirmar se está funcionando para fechar o chamado.', '2026-03-17 15:37:34', '2026-03-17 15:37:34');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (330, 92, 43, 'Confirmado! Muito obrigado pelo atendimento rápido.', '2026-03-18 08:37:34', '2026-03-18 08:37:34');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (331, 93, 1, 'Chamado recebido. Estou analisando o problema.', '2026-03-29 05:12:30', '2026-03-29 05:12:30');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (332, 93, 13, 'Já identifiquei a causa. Estou trabalhando na solução, aguarde.', '2026-03-29 06:12:30', '2026-03-29 06:12:30');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (333, 93, 1, 'Bom dia, tem alguma previsão de quando resolve? Estou precisando para as aulas.', '2026-03-29 23:12:30', '2026-03-29 23:12:30');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (334, 93, 13, 'Estou aguardando a chegada de uma peça. Prazo estimado: 2 dias úteis.', '2026-03-30 14:12:30', '2026-03-30 14:12:30');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (335, 93, 1, 'Ok, aguardo. Obrigado pela informação.', '2026-03-30 18:12:30', '2026-03-30 18:12:30');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (336, 94, 2, 'Vou verificar o problema. Aguarde.', '2026-03-22 02:27:24', '2026-03-22 02:27:24');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (337, 94, 35, 'Realizei a verificação. sistema atualizado foi corrigido.', '2026-03-22 13:27:24', '2026-03-22 13:27:24');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (338, 94, 2, 'Funcionou! Podem fechar o chamado.', '2026-03-22 14:27:24', '2026-03-22 14:27:24');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (339, 95, 1, 'Chamado recebido e encaminhado para análise técnica.', '2026-03-29 15:47:20', '2026-03-29 15:47:20');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (340, 95, 36, 'Problema localizado. Aplicando solução.', '2026-03-30 10:47:20', '2026-03-30 10:47:20');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (341, 95, 1, 'Solução aplicada. Por favor confirme se o problema foi resolvido.', '2026-03-30 12:47:20', '2026-03-30 12:47:20');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (342, 95, 36, 'Confirmei aqui, está funcionando normalmente. Obrigado!', '2026-03-30 19:47:20', '2026-03-30 19:47:20');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (343, 96, 1, 'Vou verificar o problema. Aguarde.', '2026-03-03 20:50:02', '2026-03-03 20:50:02');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (344, 96, 36, 'Realizei a verificação. equipamento trocado por reserva foi corrigido.', '2026-03-04 06:50:02', '2026-03-04 06:50:02');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (345, 96, 1, 'Funcionou! Podem fechar o chamado.', '2026-03-04 17:50:02', '2026-03-04 17:50:02');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (346, 97, 3, 'Chamado recebido. Vou verificar o problema ainda hoje.', '2026-03-30 21:27:21', '2026-03-30 21:27:21');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (347, 97, 20, 'Fui até a unidade e identifiquei o problema. Já iniciando o reparo.', '2026-03-31 17:27:21', '2026-03-31 17:27:21');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (348, 97, 3, 'Reparo concluído. O equipamento está funcionando normalmente. Fechando o chamado.', '2026-04-01 10:27:21', '2026-04-01 10:27:21');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (349, 98, 2, 'Tomei conhecimento do problema. Vou agendar visita técnica.', '2026-03-21 05:44:33', '2026-03-21 05:44:33');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (350, 98, 36, 'Visita agendada para amanhã às 9h. Por favor, garanta acesso ao laboratório.', '2026-03-22 00:44:33', '2026-03-22 00:44:33');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (351, 98, 2, 'Ok, estarei lá para receber o técnico.', '2026-03-22 16:44:33', '2026-03-22 16:44:33');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (352, 98, 36, 'Visita realizada. Problema mais complexo do que esperado. Continuando análise.', '2026-03-23 06:44:33', '2026-03-23 06:44:33');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (353, 99, 4, 'Recebi o chamado. Estarei na escola amanhã cedo para verificar.', '2026-03-24 12:50:47', '2026-03-24 12:50:47');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (354, 99, 43, 'Problema identificado: cabo substituído. Realizando correção.', '2026-03-24 19:50:47', '2026-03-24 19:50:47');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (355, 99, 4, 'Tudo resolvido. Pode confirmar se está funcionando para fechar o chamado.', '2026-03-25 15:50:47', '2026-03-25 15:50:47');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (356, 99, 43, 'Confirmado! Muito obrigado pelo atendimento rápido.', '2026-03-26 03:50:47', '2026-03-26 03:50:47');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (357, 100, 2, 'Chamado recebido. Encaminhei para o setor responsável.', '2026-03-02 02:23:22', '2026-03-02 02:23:22');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (358, 100, 26, 'Aguardando resposta do fornecedor para prosseguir.', '2026-03-02 21:23:22', '2026-03-02 21:23:22');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (359, 100, 2, 'Entendido. Quanto tempo estima que demorará?', '2026-03-03 07:23:22', '2026-03-03 07:23:22');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (360, 100, 26, 'Previsão de 3 a 5 dias úteis para retorno do fornecedor.', '2026-03-03 18:23:22', '2026-03-03 18:23:22');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (361, 100, 2, 'Ok, aguardamos. Mas preciso de solução logo pois afeta as aulas.', '2026-03-04 07:23:22', '2026-03-04 07:23:22');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (362, 101, 3, 'Chamado recebido. Estou analisando o problema.', '2026-03-17 08:56:46', '2026-03-17 08:56:46');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (363, 101, 30, 'Já identifiquei a causa. Estou trabalhando na solução, aguarde.', '2026-03-18 03:56:46', '2026-03-18 03:56:46');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (364, 101, 3, 'Bom dia, tem alguma previsão de quando resolve? Estou precisando para as aulas.', '2026-03-18 06:56:46', '2026-03-18 06:56:46');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (365, 101, 30, 'Estou aguardando a chegada de uma peça. Prazo estimado: 2 dias úteis.', '2026-03-18 08:56:46', '2026-03-18 08:56:46');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (366, 101, 3, 'Ok, aguardo. Obrigado pela informação.', '2026-03-18 10:56:46', '2026-03-18 10:56:46');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (367, 102, 1, 'Problema identificado. Precisamos aguardar aprovação para compra de peça.', '2026-03-27 14:18:27', '2026-03-27 14:18:27');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (368, 102, 31, 'Solicitação de compra enviada para aprovação da direção.', '2026-03-27 16:18:27', '2026-03-27 16:18:27');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (369, 102, 1, 'Conseguiram aprovação? O problema continua afetando o laboratório.', '2026-03-28 03:18:27', '2026-03-28 03:18:27');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (370, 102, 31, 'Ainda aguardando aprovação. Assim que sair, atendemos imediatamente.', '2026-03-28 09:18:27', '2026-03-28 09:18:27');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (371, 103, 2, 'Chamado recebido. Encaminhei para o setor responsável.', '2026-03-26 08:42:22', '2026-03-26 08:42:22');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (372, 103, 35, 'Aguardando resposta do fornecedor para prosseguir.', '2026-03-27 01:42:22', '2026-03-27 01:42:22');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (373, 103, 2, 'Entendido. Quanto tempo estima que demorará?', '2026-03-27 16:42:22', '2026-03-27 16:42:22');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (374, 103, 35, 'Previsão de 3 a 5 dias úteis para retorno do fornecedor.', '2026-03-27 17:42:22', '2026-03-27 17:42:22');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (375, 103, 2, 'Ok, aguardamos. Mas preciso de solução logo pois afeta as aulas.', '2026-03-28 05:42:22', '2026-03-28 05:42:22');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (376, 104, 4, 'Problema identificado. Precisamos aguardar aprovação para compra de peça.', '2026-03-03 08:06:59', '2026-03-03 08:06:59');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (377, 104, 9, 'Solicitação de compra enviada para aprovação da direção.', '2026-03-03 20:06:59', '2026-03-03 20:06:59');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (378, 104, 4, 'Conseguiram aprovação? O problema continua afetando o laboratório.', '2026-03-04 07:06:59', '2026-03-04 07:06:59');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (379, 104, 9, 'Ainda aguardando aprovação. Assim que sair, atendemos imediatamente.', '2026-03-04 21:06:59', '2026-03-04 21:06:59');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (380, 105, 1, 'Chamado recebido. Vou verificar o problema ainda hoje.', '2026-03-18 12:37:55', '2026-03-18 12:37:55');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (381, 105, 11, 'Fui até a unidade e identifiquei o problema. Já iniciando o reparo.', '2026-03-19 08:37:55', '2026-03-19 08:37:55');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (382, 105, 1, 'Reparo concluído. O equipamento está funcionando normalmente. Fechando o chamado.', '2026-03-19 10:37:55', '2026-03-19 10:37:55');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (383, 106, 1, 'Chamado recebido. Vou verificar o problema ainda hoje.', '2026-03-11 22:41:56', '2026-03-11 22:41:56');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (384, 106, 17, 'Fui até a unidade e identifiquei o problema. Já iniciando o reparo.', '2026-03-12 08:41:56', '2026-03-12 08:41:56');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (385, 106, 1, 'Reparo concluído. O equipamento está funcionando normalmente. Fechando o chamado.', '2026-03-13 03:41:56', '2026-03-13 03:41:56');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (386, 107, 4, 'Tomei conhecimento do problema. Vou agendar visita técnica.', '2026-03-29 01:55:38', '2026-03-29 01:55:38');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (387, 107, 46, 'Visita agendada para amanhã às 9h. Por favor, garanta acesso ao laboratório.', '2026-03-29 09:55:38', '2026-03-29 09:55:38');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (388, 107, 4, 'Ok, estarei lá para receber o técnico.', '2026-03-29 17:55:38', '2026-03-29 17:55:38');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (389, 107, 46, 'Visita realizada. Problema mais complexo do que esperado. Continuando análise.', '2026-03-29 19:55:38', '2026-03-29 19:55:38');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (390, 108, 4, 'Vou verificar o problema. Aguarde.', '2026-03-22 03:54:24', '2026-03-22 03:54:24');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (391, 108, 39, 'Realizei a verificação. senha redefinida foi corrigido.', '2026-03-22 13:54:24', '2026-03-22 13:54:24');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (392, 108, 4, 'Funcionou! Podem fechar o chamado.', '2026-03-23 08:54:24', '2026-03-23 08:54:24');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (393, 109, 7, 'Preciso de suporte urgente. O problema já dura 2 dias.', '2026-03-11 23:14:56', '2026-03-11 23:14:56');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (394, 110, 4, 'Tomei conhecimento do problema. Vou agendar visita técnica.', '2026-03-11 23:37:12', '2026-03-11 23:37:12');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (395, 110, 12, 'Visita agendada para amanhã às 9h. Por favor, garanta acesso ao laboratório.', '2026-03-12 05:37:12', '2026-03-12 05:37:12');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (396, 110, 4, 'Ok, estarei lá para receber o técnico.', '2026-03-12 20:37:12', '2026-03-12 20:37:12');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (397, 110, 12, 'Visita realizada. Problema mais complexo do que esperado. Continuando análise.', '2026-03-13 10:37:12', '2026-03-13 10:37:12');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (398, 111, 3, 'Recebi o chamado. Estarei na escola amanhã cedo para verificar.', '2026-03-01 22:04:16', '2026-03-01 22:04:16');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (399, 111, 9, 'Problema identificado: driver reinstalado. Realizando correção.', '2026-03-02 08:04:16', '2026-03-02 08:04:16');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (400, 111, 3, 'Tudo resolvido. Pode confirmar se está funcionando para fechar o chamado.', '2026-03-03 00:04:16', '2026-03-03 00:04:16');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (401, 111, 9, 'Confirmado! Muito obrigado pelo atendimento rápido.', '2026-03-03 08:04:16', '2026-03-03 08:04:16');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (402, 112, 1, 'Problema identificado. Precisamos aguardar aprovação para compra de peça.', '2026-03-11 22:25:47', '2026-03-11 22:25:47');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (403, 112, 27, 'Solicitação de compra enviada para aprovação da direção.', '2026-03-12 00:25:47', '2026-03-12 00:25:47');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (404, 112, 1, 'Conseguiram aprovação? O problema continua afetando o laboratório.', '2026-03-12 02:25:47', '2026-03-12 02:25:47');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (405, 112, 27, 'Ainda aguardando aprovação. Assim que sair, atendemos imediatamente.', '2026-03-12 06:25:47', '2026-03-12 06:25:47');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (406, 113, 3, 'Chamado recebido. Vou verificar o problema ainda hoje.', '2026-03-16 07:46:08', '2026-03-16 07:46:08');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (407, 113, 13, 'Fui até a unidade e identifiquei o problema. Já iniciando o reparo.', '2026-03-17 00:46:08', '2026-03-17 00:46:08');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (408, 113, 3, 'Reparo concluído. O equipamento está funcionando normalmente. Fechando o chamado.', '2026-03-17 14:46:08', '2026-03-17 14:46:08');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (409, 114, 4, 'Problema identificado. Precisamos aguardar aprovação para compra de peça.', '2026-03-29 10:13:33', '2026-03-29 10:13:33');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (410, 114, 26, 'Solicitação de compra enviada para aprovação da direção.', '2026-03-29 11:13:33', '2026-03-29 11:13:33');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (411, 114, 4, 'Conseguiram aprovação? O problema continua afetando o laboratório.', '2026-03-30 00:13:33', '2026-03-30 00:13:33');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (412, 114, 26, 'Ainda aguardando aprovação. Assim que sair, atendemos imediatamente.', '2026-03-30 02:13:33', '2026-03-30 02:13:33');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (413, 115, 4, 'Vou verificar o problema. Aguarde.', '2026-03-17 16:56:54', '2026-03-17 16:56:54');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (414, 115, 37, 'Realizei a verificação. senha redefinida foi corrigido.', '2026-03-18 09:56:54', '2026-03-18 09:56:54');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (415, 115, 4, 'Funcionou! Podem fechar o chamado.', '2026-03-18 20:56:54', '2026-03-18 20:56:54');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (416, 116, 2, 'Chamado registrado. Vou analisar remotamente primeiro.', '2026-03-07 18:48:53', '2026-03-07 18:48:53');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (417, 116, 28, 'Não foi possível resolver remotamente. Agendei visita presencial para amanhã.', '2026-03-08 01:48:53', '2026-03-08 01:48:53');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (418, 116, 2, 'Visita realizada. driver reinstalado corrigido com sucesso.', '2026-03-08 14:48:53', '2026-03-08 14:48:53');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (419, 116, 28, 'Ótimo! Já testei e está funcionando perfeitamente. Obrigado!', '2026-03-09 03:48:53', '2026-03-09 03:48:53');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (420, 116, 2, 'Fico feliz que resolveu. Qualquer novo problema, abra outro chamado.', '2026-03-09 10:48:53', '2026-03-09 10:48:53');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (421, 117, 2, 'Chamado recebido e encaminhado para análise técnica.', '2026-03-16 20:10:43', '2026-03-16 20:10:43');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (422, 117, 11, 'Problema localizado. Aplicando solução.', '2026-03-17 06:10:43', '2026-03-17 06:10:43');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (423, 117, 2, 'Solução aplicada. Por favor confirme se o problema foi resolvido.', '2026-03-17 14:10:43', '2026-03-17 14:10:43');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (424, 117, 11, 'Confirmei aqui, está funcionando normalmente. Obrigado!', '2026-03-17 18:10:43', '2026-03-17 18:10:43');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (425, 118, 3, 'Chamado recebido. Vou verificar o problema ainda hoje.', '2026-03-24 03:03:52', '2026-03-24 03:03:52');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (426, 118, 30, 'Fui até a unidade e identifiquei o problema. Já iniciando o reparo.', '2026-03-24 12:03:52', '2026-03-24 12:03:52');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (427, 118, 3, 'Reparo concluído. O equipamento está funcionando normalmente. Fechando o chamado.', '2026-03-25 07:03:52', '2026-03-25 07:03:52');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (428, 119, 4, 'Chamado recebido. Estou analisando o problema.', '2026-03-19 09:04:45', '2026-03-19 09:04:45');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (429, 119, 7, 'Já identifiquei a causa. Estou trabalhando na solução, aguarde.', '2026-03-19 13:04:45', '2026-03-19 13:04:45');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (430, 119, 4, 'Bom dia, tem alguma previsão de quando resolve? Estou precisando para as aulas.', '2026-03-19 17:04:45', '2026-03-19 17:04:45');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (431, 119, 7, 'Estou aguardando a chegada de uma peça. Prazo estimado: 2 dias úteis.', '2026-03-19 20:04:45', '2026-03-19 20:04:45');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (432, 119, 4, 'Ok, aguardo. Obrigado pela informação.', '2026-03-20 04:04:45', '2026-03-20 04:04:45');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (433, 120, 3, 'Chamado recebido. Encaminhei para o setor responsável.', '2026-03-10 03:14:12', '2026-03-10 03:14:12');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (434, 120, 8, 'Aguardando resposta do fornecedor para prosseguir.', '2026-03-10 10:14:12', '2026-03-10 10:14:12');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (435, 120, 3, 'Entendido. Quanto tempo estima que demorará?', '2026-03-10 18:14:12', '2026-03-10 18:14:12');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (436, 120, 8, 'Previsão de 3 a 5 dias úteis para retorno do fornecedor.', '2026-03-10 22:14:12', '2026-03-10 22:14:12');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (437, 120, 3, 'Ok, aguardamos. Mas preciso de solução logo pois afeta as aulas.', '2026-03-11 08:14:12', '2026-03-11 08:14:12');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (438, 121, 21, 'Bom dia! Acabei de abrir este chamado. O problema está impactando minhas aulas.', '2026-03-10 19:00:56', '2026-03-10 19:00:56');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (439, 122, 3, 'Chamado recebido. Vou verificar o problema ainda hoje.', '2026-03-29 16:37:22', '2026-03-29 16:37:22');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (440, 122, 7, 'Fui até a unidade e identifiquei o problema. Já iniciando o reparo.', '2026-03-30 01:37:22', '2026-03-30 01:37:22');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (441, 122, 3, 'Reparo concluído. O equipamento está funcionando normalmente. Fechando o chamado.', '2026-03-30 12:37:22', '2026-03-30 12:37:22');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (442, 123, 4, 'Chamado registrado. Vou analisar remotamente primeiro.', '2026-03-14 10:13:39', '2026-03-14 10:13:39');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (443, 123, 20, 'Não foi possível resolver remotamente. Agendei visita presencial para amanhã.', '2026-03-15 04:13:39', '2026-03-15 04:13:39');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (444, 123, 4, 'Visita realizada. cabo substituído corrigido com sucesso.', '2026-03-16 00:13:39', '2026-03-16 00:13:39');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (445, 123, 20, 'Ótimo! Já testei e está funcionando perfeitamente. Obrigado!', '2026-03-16 01:13:39', '2026-03-16 01:13:39');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (446, 123, 4, 'Fico feliz que resolveu. Qualquer novo problema, abra outro chamado.', '2026-03-16 12:13:39', '2026-03-16 12:13:39');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (447, 124, 3, 'Tomei conhecimento do problema. Vou agendar visita técnica.', '2026-03-07 03:16:24', '2026-03-07 03:16:24');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (448, 124, 18, 'Visita agendada para amanhã às 9h. Por favor, garanta acesso ao laboratório.', '2026-03-07 05:16:24', '2026-03-07 05:16:24');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (449, 124, 3, 'Ok, estarei lá para receber o técnico.', '2026-03-07 21:16:24', '2026-03-07 21:16:24');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (450, 124, 18, 'Visita realizada. Problema mais complexo do que esperado. Continuando análise.', '2026-03-08 13:16:24', '2026-03-08 13:16:24');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (451, 125, 4, 'Tentamos contato com o professor mas não obtivemos retorno. Arquivando por inatividade.', '2026-03-14 08:21:20', '2026-03-14 08:21:20');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (452, 125, 7, 'Desculpe, estava em licença. O problema se resolveu sozinho. Pode arquivar mesmo.', '2026-03-14 17:21:20', '2026-03-14 17:21:20');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (453, 126, 1, 'Chamado recebido e encaminhado para análise técnica.', '2026-03-27 01:55:56', '2026-03-27 01:55:56');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (454, 126, 23, 'Problema localizado. Aplicando solução.', '2026-03-27 19:55:56', '2026-03-27 19:55:56');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (455, 126, 1, 'Solução aplicada. Por favor confirme se o problema foi resolvido.', '2026-03-28 08:55:56', '2026-03-28 08:55:56');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (456, 126, 23, 'Confirmei aqui, está funcionando normalmente. Obrigado!', '2026-03-28 17:55:56', '2026-03-28 17:55:56');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (457, 127, 2, 'Recebi o chamado. Estarei na escola amanhã cedo para verificar.', '2026-03-23 15:27:22', '2026-03-23 15:27:22');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (458, 127, 12, 'Problema identificado: senha redefinida. Realizando correção.', '2026-03-24 06:27:22', '2026-03-24 06:27:22');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (459, 127, 2, 'Tudo resolvido. Pode confirmar se está funcionando para fechar o chamado.', '2026-03-25 02:27:22', '2026-03-25 02:27:22');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (460, 127, 12, 'Confirmado! Muito obrigado pelo atendimento rápido.', '2026-03-25 08:27:22', '2026-03-25 08:27:22');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (461, 128, 3, 'Chamado registrado. Vou analisar remotamente primeiro.', '2026-03-22 17:46:40', '2026-03-22 17:46:40');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (462, 128, 8, 'Não foi possível resolver remotamente. Agendei visita presencial para amanhã.', '2026-03-23 11:46:40', '2026-03-23 11:46:40');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (463, 128, 3, 'Visita realizada. configuração de rede corrigida corrigido com sucesso.', '2026-03-23 23:46:40', '2026-03-23 23:46:40');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (464, 128, 8, 'Ótimo! Já testei e está funcionando perfeitamente. Obrigado!', '2026-03-24 17:46:40', '2026-03-24 17:46:40');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (465, 128, 3, 'Fico feliz que resolveu. Qualquer novo problema, abra outro chamado.', '2026-03-24 21:46:40', '2026-03-24 21:46:40');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (466, 129, 1, 'Chamado recebido. Estou analisando o problema.', '2026-03-04 12:56:17', '2026-03-04 12:56:17');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (467, 129, 20, 'Já identifiquei a causa. Estou trabalhando na solução, aguarde.', '2026-03-04 21:56:17', '2026-03-04 21:56:17');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (468, 129, 1, 'Bom dia, tem alguma previsão de quando resolve? Estou precisando para as aulas.', '2026-03-05 11:56:17', '2026-03-05 11:56:17');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (469, 129, 20, 'Estou aguardando a chegada de uma peça. Prazo estimado: 2 dias úteis.', '2026-03-06 01:56:17', '2026-03-06 01:56:17');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (470, 129, 1, 'Ok, aguardo. Obrigado pela informação.', '2026-03-06 20:56:17', '2026-03-06 20:56:17');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (471, 130, 1, 'Chamado registrado. Vou analisar remotamente primeiro.', '2026-03-07 09:19:36', '2026-03-07 09:19:36');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (472, 130, 33, 'Não foi possível resolver remotamente. Agendei visita presencial para amanhã.', '2026-03-07 11:19:36', '2026-03-07 11:19:36');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (473, 130, 1, 'Visita realizada. senha redefinida corrigido com sucesso.', '2026-03-08 04:19:36', '2026-03-08 04:19:36');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (474, 130, 33, 'Ótimo! Já testei e está funcionando perfeitamente. Obrigado!', '2026-03-08 20:19:36', '2026-03-08 20:19:36');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (475, 130, 1, 'Fico feliz que resolveu. Qualquer novo problema, abra outro chamado.', '2026-03-09 02:19:36', '2026-03-09 02:19:36');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (476, 131, 2, 'Tomei conhecimento do problema. Vou agendar visita técnica.', '2026-03-20 06:35:02', '2026-03-20 06:35:02');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (477, 131, 45, 'Visita agendada para amanhã às 9h. Por favor, garanta acesso ao laboratório.', '2026-03-20 11:35:02', '2026-03-20 11:35:02');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (478, 131, 2, 'Ok, estarei lá para receber o técnico.', '2026-03-20 16:35:02', '2026-03-20 16:35:02');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (479, 131, 45, 'Visita realizada. Problema mais complexo do que esperado. Continuando análise.', '2026-03-21 08:35:02', '2026-03-21 08:35:02');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (480, 132, 2, 'Chamado recebido. Encaminhei para o setor responsável.', '2026-03-24 02:01:45', '2026-03-24 02:01:45');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (481, 132, 12, 'Aguardando resposta do fornecedor para prosseguir.', '2026-03-24 18:01:45', '2026-03-24 18:01:45');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (482, 132, 2, 'Entendido. Quanto tempo estima que demorará?', '2026-03-25 03:01:45', '2026-03-25 03:01:45');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (483, 132, 12, 'Previsão de 3 a 5 dias úteis para retorno do fornecedor.', '2026-03-25 20:01:45', '2026-03-25 20:01:45');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (484, 132, 2, 'Ok, aguardamos. Mas preciso de solução logo pois afeta as aulas.', '2026-03-25 23:01:45', '2026-03-25 23:01:45');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (485, 133, 42, 'Bom dia! Acabei de abrir este chamado. O problema está impactando minhas aulas.', '2026-03-06 13:02:26', '2026-03-06 13:02:26');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (486, 134, 4, 'Chamado recebido. Estou analisando o problema.', '2026-03-13 17:19:08', '2026-03-13 17:19:08');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (487, 134, 20, 'Já identifiquei a causa. Estou trabalhando na solução, aguarde.', '2026-03-14 01:19:08', '2026-03-14 01:19:08');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (488, 134, 4, 'Bom dia, tem alguma previsão de quando resolve? Estou precisando para as aulas.', '2026-03-14 07:19:08', '2026-03-14 07:19:08');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (489, 134, 20, 'Estou aguardando a chegada de uma peça. Prazo estimado: 2 dias úteis.', '2026-03-14 09:19:08', '2026-03-14 09:19:08');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (490, 134, 4, 'Ok, aguardo. Obrigado pela informação.', '2026-03-14 21:19:08', '2026-03-14 21:19:08');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (491, 135, 4, 'Chamado recebido. Estou analisando o problema.', '2026-03-27 18:45:18', '2026-03-27 18:45:18');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (492, 135, 25, 'Já identifiquei a causa. Estou trabalhando na solução, aguarde.', '2026-03-28 00:45:18', '2026-03-28 00:45:18');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (493, 135, 4, 'Bom dia, tem alguma previsão de quando resolve? Estou precisando para as aulas.', '2026-03-28 20:45:18', '2026-03-28 20:45:18');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (494, 135, 25, 'Estou aguardando a chegada de uma peça. Prazo estimado: 2 dias úteis.', '2026-03-29 11:45:18', '2026-03-29 11:45:18');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (495, 135, 4, 'Ok, aguardo. Obrigado pela informação.', '2026-03-30 04:45:18', '2026-03-30 04:45:18');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (496, 136, 1, 'Tomei conhecimento do problema. Vou agendar visita técnica.', '2026-03-18 13:44:19', '2026-03-18 13:44:19');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (497, 136, 41, 'Visita agendada para amanhã às 9h. Por favor, garanta acesso ao laboratório.', '2026-03-18 20:44:19', '2026-03-18 20:44:19');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (498, 136, 1, 'Ok, estarei lá para receber o técnico.', '2026-03-19 09:44:19', '2026-03-19 09:44:19');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (499, 136, 41, 'Visita realizada. Problema mais complexo do que esperado. Continuando análise.', '2026-03-20 00:44:19', '2026-03-20 00:44:19');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (500, 137, 1, 'Recebi o chamado. Estarei na escola amanhã cedo para verificar.', '2026-03-12 01:50:16', '2026-03-12 01:50:16');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (501, 137, 43, 'Problema identificado: cabo substituído. Realizando correção.', '2026-03-12 04:50:16', '2026-03-12 04:50:16');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (502, 137, 1, 'Tudo resolvido. Pode confirmar se está funcionando para fechar o chamado.', '2026-03-12 17:50:16', '2026-03-12 17:50:16');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (503, 137, 43, 'Confirmado! Muito obrigado pelo atendimento rápido.', '2026-03-12 19:50:16', '2026-03-12 19:50:16');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (504, 138, 3, 'Recebi o chamado. Estarei na escola amanhã cedo para verificar.', '2026-03-09 12:20:23', '2026-03-09 12:20:23');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (505, 138, 21, 'Problema identificado: IP configurado corretamente. Realizando correção.', '2026-03-10 00:20:23', '2026-03-10 00:20:23');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (506, 138, 3, 'Tudo resolvido. Pode confirmar se está funcionando para fechar o chamado.', '2026-03-10 10:20:23', '2026-03-10 10:20:23');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (507, 138, 21, 'Confirmado! Muito obrigado pelo atendimento rápido.', '2026-03-10 19:20:23', '2026-03-10 19:20:23');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (508, 139, 3, 'Problema identificado. Precisamos aguardar aprovação para compra de peça.', '2026-04-04 22:14:42', '2026-04-04 22:14:42');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (509, 139, 28, 'Solicitação de compra enviada para aprovação da direção.', '2026-04-05 05:14:42', '2026-04-05 05:14:42');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (510, 139, 3, 'Conseguiram aprovação? O problema continua afetando o laboratório.', '2026-04-05 09:14:42', '2026-04-05 09:14:42');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (511, 139, 28, 'Ainda aguardando aprovação. Assim que sair, atendemos imediatamente.', '2026-04-05 15:14:42', '2026-04-05 15:14:42');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (512, 140, 25, 'Preciso de suporte urgente. O problema já dura 2 dias.', '2026-04-09 16:50:05', '2026-04-09 16:50:05');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (513, 141, 1, 'Recebi o chamado. Estarei na escola amanhã cedo para verificar.', '2026-04-29 00:48:36', '2026-04-29 00:48:36');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (514, 141, 24, 'Problema identificado: toner trocado. Realizando correção.', '2026-04-29 17:48:36', '2026-04-29 17:48:36');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (515, 141, 1, 'Tudo resolvido. Pode confirmar se está funcionando para fechar o chamado.', '2026-04-30 04:48:36', '2026-04-30 04:48:36');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (516, 141, 24, 'Confirmado! Muito obrigado pelo atendimento rápido.', '2026-04-30 16:48:36', '2026-04-30 16:48:36');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (517, 142, 3, 'Vou verificar o problema. Aguarde.', '2026-04-09 07:10:26', '2026-04-09 07:10:26');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (518, 142, 39, 'Realizei a verificação. senha redefinida foi corrigido.', '2026-04-10 02:10:26', '2026-04-10 02:10:26');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (519, 142, 3, 'Funcionou! Podem fechar o chamado.', '2026-04-10 16:10:26', '2026-04-10 16:10:26');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (520, 143, 1, 'Recebi o chamado. Estarei na escola amanhã cedo para verificar.', '2026-04-05 23:21:20', '2026-04-05 23:21:20');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (521, 143, 12, 'Problema identificado: sistema atualizado. Realizando correção.', '2026-04-06 14:21:20', '2026-04-06 14:21:20');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (522, 143, 1, 'Tudo resolvido. Pode confirmar se está funcionando para fechar o chamado.', '2026-04-06 22:21:20', '2026-04-06 22:21:20');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (523, 143, 12, 'Confirmado! Muito obrigado pelo atendimento rápido.', '2026-04-07 12:21:20', '2026-04-07 12:21:20');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (524, 144, 1, 'Chamado recebido. Vou verificar o problema ainda hoje.', '2026-04-03 08:31:38', '2026-04-03 08:31:38');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (525, 144, 25, 'Fui até a unidade e identifiquei o problema. Já iniciando o reparo.', '2026-04-03 23:31:38', '2026-04-03 23:31:38');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (526, 144, 1, 'Reparo concluído. O equipamento está funcionando normalmente. Fechando o chamado.', '2026-04-04 06:31:38', '2026-04-04 06:31:38');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (527, 145, 1, 'Vou verificar o problema. Aguarde.', '2026-04-26 15:59:49', '2026-04-26 15:59:49');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (528, 145, 28, 'Realizei a verificação. sistema atualizado foi corrigido.', '2026-04-27 07:59:49', '2026-04-27 07:59:49');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (529, 145, 1, 'Funcionou! Podem fechar o chamado.', '2026-04-27 09:59:49', '2026-04-27 09:59:49');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (530, 146, 1, 'Chamado recebido. Verificando.', '2026-04-10 16:15:02', '2026-04-10 16:15:02');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (531, 146, 35, 'Após análise, o problema reportado não foi reproduzido. Arquivando.', '2026-04-11 09:15:02', '2026-04-11 09:15:02');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (532, 146, 1, 'Entendido. Se o problema reaparecer, abrirei novo chamado.', '2026-04-11 23:15:02', '2026-04-11 23:15:02');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (533, 147, 4, 'Vou verificar o problema. Aguarde.', '2026-04-30 12:08:08', '2026-04-30 12:08:08');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (534, 147, 30, 'Realizei a verificação. driver reinstalado foi corrigido.', '2026-04-30 17:08:08', '2026-04-30 17:08:08');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (535, 147, 4, 'Funcionou! Podem fechar o chamado.', '2026-05-01 05:08:08', '2026-05-01 05:08:08');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (536, 148, 2, 'Chamado recebido. Encaminhei para o setor responsável.', '2026-04-12 19:38:11', '2026-04-12 19:38:11');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (537, 148, 39, 'Aguardando resposta do fornecedor para prosseguir.', '2026-04-13 11:38:11', '2026-04-13 11:38:11');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (538, 148, 2, 'Entendido. Quanto tempo estima que demorará?', '2026-04-13 17:38:11', '2026-04-13 17:38:11');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (539, 148, 39, 'Previsão de 3 a 5 dias úteis para retorno do fornecedor.', '2026-04-13 18:38:11', '2026-04-13 18:38:11');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (540, 148, 2, 'Ok, aguardamos. Mas preciso de solução logo pois afeta as aulas.', '2026-04-14 11:38:11', '2026-04-14 11:38:11');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (541, 149, 4, 'Chamado recebido. Encaminhei para o setor responsável.', '2026-04-04 16:03:22', '2026-04-04 16:03:22');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (542, 149, 31, 'Aguardando resposta do fornecedor para prosseguir.', '2026-04-04 18:03:22', '2026-04-04 18:03:22');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (543, 149, 4, 'Entendido. Quanto tempo estima que demorará?', '2026-04-05 06:03:22', '2026-04-05 06:03:22');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (544, 149, 31, 'Previsão de 3 a 5 dias úteis para retorno do fornecedor.', '2026-04-05 21:03:22', '2026-04-05 21:03:22');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (545, 149, 4, 'Ok, aguardamos. Mas preciso de solução logo pois afeta as aulas.', '2026-04-06 14:03:22', '2026-04-06 14:03:22');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (546, 150, 4, 'Tentamos contato com o professor mas não obtivemos retorno. Arquivando por inatividade.', '2026-04-05 07:19:39', '2026-04-05 07:19:39');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (547, 150, 27, 'Desculpe, estava em licença. O problema se resolveu sozinho. Pode arquivar mesmo.', '2026-04-05 14:19:39', '2026-04-05 14:19:39');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (548, 151, 4, 'Problema identificado. Precisamos aguardar aprovação para compra de peça.', '2026-04-06 14:41:03', '2026-04-06 14:41:03');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (549, 151, 11, 'Solicitação de compra enviada para aprovação da direção.', '2026-04-07 04:41:03', '2026-04-07 04:41:03');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (550, 151, 4, 'Conseguiram aprovação? O problema continua afetando o laboratório.', '2026-04-07 05:41:03', '2026-04-07 05:41:03');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (551, 151, 11, 'Ainda aguardando aprovação. Assim que sair, atendemos imediatamente.', '2026-04-07 09:41:03', '2026-04-07 09:41:03');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (552, 152, 1, 'Problema identificado. Precisamos aguardar aprovação para compra de peça.', '2026-04-05 10:05:52', '2026-04-05 10:05:52');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (553, 152, 27, 'Solicitação de compra enviada para aprovação da direção.', '2026-04-06 01:05:52', '2026-04-06 01:05:52');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (554, 152, 1, 'Conseguiram aprovação? O problema continua afetando o laboratório.', '2026-04-06 21:05:52', '2026-04-06 21:05:52');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (555, 152, 27, 'Ainda aguardando aprovação. Assim que sair, atendemos imediatamente.', '2026-04-07 10:05:52', '2026-04-07 10:05:52');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (556, 153, 4, 'Chamado recebido. Encaminhei para o setor responsável.', '2026-04-28 06:19:01', '2026-04-28 06:19:01');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (557, 153, 14, 'Aguardando resposta do fornecedor para prosseguir.', '2026-04-28 15:19:01', '2026-04-28 15:19:01');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (558, 153, 4, 'Entendido. Quanto tempo estima que demorará?', '2026-04-28 16:19:01', '2026-04-28 16:19:01');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (559, 153, 14, 'Previsão de 3 a 5 dias úteis para retorno do fornecedor.', '2026-04-28 23:19:01', '2026-04-28 23:19:01');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (560, 153, 4, 'Ok, aguardamos. Mas preciso de solução logo pois afeta as aulas.', '2026-04-29 00:19:01', '2026-04-29 00:19:01');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (561, 154, 1, 'Recebi o chamado. Estarei na escola amanhã cedo para verificar.', '2026-04-14 15:03:07', '2026-04-14 15:03:07');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (562, 154, 9, 'Problema identificado: equipamento trocado por reserva. Realizando correção.', '2026-04-15 05:03:07', '2026-04-15 05:03:07');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (563, 154, 1, 'Tudo resolvido. Pode confirmar se está funcionando para fechar o chamado.', '2026-04-15 13:03:07', '2026-04-15 13:03:07');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (564, 154, 9, 'Confirmado! Muito obrigado pelo atendimento rápido.', '2026-04-16 07:03:07', '2026-04-16 07:03:07');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (565, 155, 40, 'Abri este chamado ontem e ainda não recebi retorno. Podem verificar?', '2026-04-28 00:45:52', '2026-04-28 00:45:52');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (566, 156, 2, 'Chamado recebido. Estou analisando o problema.', '2026-04-09 04:04:55', '2026-04-09 04:04:55');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (567, 156, 7, 'Já identifiquei a causa. Estou trabalhando na solução, aguarde.', '2026-04-09 14:04:55', '2026-04-09 14:04:55');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (568, 156, 2, 'Bom dia, tem alguma previsão de quando resolve? Estou precisando para as aulas.', '2026-04-09 23:04:55', '2026-04-09 23:04:55');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (569, 156, 7, 'Estou aguardando a chegada de uma peça. Prazo estimado: 2 dias úteis.', '2026-04-10 05:04:55', '2026-04-10 05:04:55');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (570, 156, 2, 'Ok, aguardo. Obrigado pela informação.', '2026-04-10 14:04:55', '2026-04-10 14:04:55');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (571, 157, 1, 'Chamado recebido. Encaminhei para o setor responsável.', '2026-04-27 19:43:13', '2026-04-27 19:43:13');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (572, 157, 8, 'Aguardando resposta do fornecedor para prosseguir.', '2026-04-28 15:43:13', '2026-04-28 15:43:13');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (573, 157, 1, 'Entendido. Quanto tempo estima que demorará?', '2026-04-29 08:43:13', '2026-04-29 08:43:13');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (574, 157, 8, 'Previsão de 3 a 5 dias úteis para retorno do fornecedor.', '2026-04-30 02:43:13', '2026-04-30 02:43:13');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (575, 157, 1, 'Ok, aguardamos. Mas preciso de solução logo pois afeta as aulas.', '2026-04-30 13:43:13', '2026-04-30 13:43:13');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (576, 158, 1, 'Tomei conhecimento do problema. Vou agendar visita técnica.', '2026-04-17 15:43:21', '2026-04-17 15:43:21');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (577, 158, 21, 'Visita agendada para amanhã às 9h. Por favor, garanta acesso ao laboratório.', '2026-04-18 08:43:21', '2026-04-18 08:43:21');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (578, 158, 1, 'Ok, estarei lá para receber o técnico.', '2026-04-18 11:43:21', '2026-04-18 11:43:21');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (579, 158, 21, 'Visita realizada. Problema mais complexo do que esperado. Continuando análise.', '2026-04-19 06:43:21', '2026-04-19 06:43:21');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (580, 159, 2, 'Chamado recebido. Estou analisando o problema.', '2026-04-09 02:50:12', '2026-04-09 02:50:12');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (581, 159, 30, 'Já identifiquei a causa. Estou trabalhando na solução, aguarde.', '2026-04-09 05:50:12', '2026-04-09 05:50:12');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (582, 159, 2, 'Bom dia, tem alguma previsão de quando resolve? Estou precisando para as aulas.', '2026-04-09 07:50:12', '2026-04-09 07:50:12');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (583, 159, 30, 'Estou aguardando a chegada de uma peça. Prazo estimado: 2 dias úteis.', '2026-04-09 14:50:12', '2026-04-09 14:50:12');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (584, 159, 2, 'Ok, aguardo. Obrigado pela informação.', '2026-04-10 10:50:12', '2026-04-10 10:50:12');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (585, 160, 4, 'Chamado recebido. Estou analisando o problema.', '2026-04-28 23:24:32', '2026-04-28 23:24:32');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (586, 160, 14, 'Já identifiquei a causa. Estou trabalhando na solução, aguarde.', '2026-04-29 15:24:32', '2026-04-29 15:24:32');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (587, 160, 4, 'Bom dia, tem alguma previsão de quando resolve? Estou precisando para as aulas.', '2026-04-30 00:24:32', '2026-04-30 00:24:32');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (588, 160, 14, 'Estou aguardando a chegada de uma peça. Prazo estimado: 2 dias úteis.', '2026-04-30 01:24:32', '2026-04-30 01:24:32');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (589, 160, 4, 'Ok, aguardo. Obrigado pela informação.', '2026-04-30 18:24:32', '2026-04-30 18:24:32');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (590, 161, 3, 'Tomei conhecimento do problema. Vou agendar visita técnica.', '2026-04-19 21:47:56', '2026-04-19 21:47:56');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (591, 161, 39, 'Visita agendada para amanhã às 9h. Por favor, garanta acesso ao laboratório.', '2026-04-20 03:47:56', '2026-04-20 03:47:56');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (592, 161, 3, 'Ok, estarei lá para receber o técnico.', '2026-04-20 17:47:56', '2026-04-20 17:47:56');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (593, 161, 39, 'Visita realizada. Problema mais complexo do que esperado. Continuando análise.', '2026-04-21 06:47:56', '2026-04-21 06:47:56');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (594, 162, 2, 'Chamado recebido e encaminhado para análise técnica.', '2026-04-16 18:58:35', '2026-04-16 18:58:35');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (595, 162, 39, 'Problema localizado. Aplicando solução.', '2026-04-16 20:58:35', '2026-04-16 20:58:35');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (596, 162, 2, 'Solução aplicada. Por favor confirme se o problema foi resolvido.', '2026-04-17 06:58:35', '2026-04-17 06:58:35');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (597, 162, 39, 'Confirmei aqui, está funcionando normalmente. Obrigado!', '2026-04-17 17:58:35', '2026-04-17 17:58:35');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (598, 163, 26, 'Bom dia! Acabei de abrir este chamado. O problema está impactando minhas aulas.', '2026-04-06 04:51:09', '2026-04-06 04:51:09');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (599, 164, 1, 'Tomei conhecimento do problema. Vou agendar visita técnica.', '2026-04-26 21:24:28', '2026-04-26 21:24:28');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (600, 164, 18, 'Visita agendada para amanhã às 9h. Por favor, garanta acesso ao laboratório.', '2026-04-27 05:24:28', '2026-04-27 05:24:28');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (601, 164, 1, 'Ok, estarei lá para receber o técnico.', '2026-04-28 01:24:28', '2026-04-28 01:24:28');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (602, 164, 18, 'Visita realizada. Problema mais complexo do que esperado. Continuando análise.', '2026-04-28 13:24:28', '2026-04-28 13:24:28');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (603, 165, 36, 'Bom dia! Acabei de abrir este chamado. O problema está impactando minhas aulas.', '2026-04-18 08:51:31', '2026-04-18 08:51:31');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (604, 166, 3, 'Tomei conhecimento do problema. Vou agendar visita técnica.', '2026-05-01 04:23:01', '2026-05-01 04:23:01');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (605, 166, 18, 'Visita agendada para amanhã às 9h. Por favor, garanta acesso ao laboratório.', '2026-05-01 22:23:01', '2026-05-01 22:23:01');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (606, 166, 3, 'Ok, estarei lá para receber o técnico.', '2026-05-02 03:23:01', '2026-05-02 03:23:01');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (607, 166, 18, 'Visita realizada. Problema mais complexo do que esperado. Continuando análise.', '2026-05-02 21:23:01', '2026-05-02 21:23:01');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (608, 167, 4, 'Vou verificar o problema. Aguarde.', '2026-04-19 18:23:39', '2026-04-19 18:23:39');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (609, 167, 33, 'Realizei a verificação. limpeza interna realizada foi corrigido.', '2026-04-20 13:23:39', '2026-04-20 13:23:39');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (610, 167, 4, 'Funcionou! Podem fechar o chamado.', '2026-04-20 14:23:39', '2026-04-20 14:23:39');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (611, 168, 2, 'Chamado recebido. Vou verificar o problema ainda hoje.', '2026-04-03 03:06:12', '2026-04-03 03:06:12');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (612, 168, 38, 'Fui até a unidade e identifiquei o problema. Já iniciando o reparo.', '2026-04-03 12:06:12', '2026-04-03 12:06:12');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (613, 168, 2, 'Reparo concluído. O equipamento está funcionando normalmente. Fechando o chamado.', '2026-04-03 22:06:12', '2026-04-03 22:06:12');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (614, 169, 2, 'Chamado recebido. Vou verificar o problema ainda hoje.', '2026-04-19 00:54:24', '2026-04-19 00:54:24');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (615, 169, 16, 'Fui até a unidade e identifiquei o problema. Já iniciando o reparo.', '2026-04-19 19:54:24', '2026-04-19 19:54:24');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (616, 169, 2, 'Reparo concluído. O equipamento está funcionando normalmente. Fechando o chamado.', '2026-04-20 11:54:24', '2026-04-20 11:54:24');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (617, 170, 3, 'Chamado registrado. Vou analisar remotamente primeiro.', '2026-04-28 13:59:55', '2026-04-28 13:59:55');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (618, 170, 45, 'Não foi possível resolver remotamente. Agendei visita presencial para amanhã.', '2026-04-28 18:59:55', '2026-04-28 18:59:55');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (619, 170, 3, 'Visita realizada. senha redefinida corrigido com sucesso.', '2026-04-29 12:59:55', '2026-04-29 12:59:55');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (620, 170, 45, 'Ótimo! Já testei e está funcionando perfeitamente. Obrigado!', '2026-04-30 01:59:55', '2026-04-30 01:59:55');
INSERT INTO tickets_comments (id, ticket_id, user_id, comment, created_at, updated_at) VALUES (621, 170, 3, 'Fico feliz que resolveu. Qualquer novo problema, abra outro chamado.', '2026-04-30 17:59:55', '2026-04-30 17:59:55');

SET FOREIGN_KEY_CHECKS = 1;

-- ================================================================
-- Fim do seed
-- ================================================================