<div align="center">

<img src="public/assets/images/moneta_logo_vertical.png" alt="Moneta" width="160"/>

### Sistema Web de Controle Financeiro Pessoal

*Porque saúde financeira também é autocuidado.*

![PHP](https://img.shields.io/badge/PHP-8.2-777BB4?style=flat-square&logo=php&logoColor=white)
![MySQL](https://img.shields.io/badge/MySQL-8.0-4479A1?style=flat-square&logo=mysql&logoColor=white)
![Redis](https://img.shields.io/badge/Redis-7-DC382D?style=flat-square&logo=redis&logoColor=white)
![Docker](https://img.shields.io/badge/Docker-ready-2496ED?style=flat-square&logo=docker&logoColor=white)
![License](https://img.shields.io/badge/license-privado-lightgrey?style=flat-square)

</div>

---

## O que é o Moneta?

O **Moneta** é uma plataforma web para organizar suas finanças pessoais — contas
bancárias, cartões de crédito, faturas, lançamentos, compras parceladas e até
aquela divisão de conta com os amigos que ninguém nunca lembra de pagar.

> ⚠️ O Moneta **não é uma instituição financeira** e **não movimenta dinheiro
> real** — é uma ferramenta de organização e controle, não um banco.

---

## Stack Tecnológica

| Camada | Tecnologia |
|---|---|
| **Backend** | PHP 8.2 (MVC próprio, sem framework pesado) |
| **Roteamento** | [CoffeeCode Router](https://github.com/GabrielAnhaia/CoffeeCode-Router) |
| **Views** | [League Plates](https://platesphp.com/) |
| **Banco de dados** | MySQL 8.0 |
| **Cache / Filas leves** | Redis 7 |
| **Front-end** | Bootstrap 5 + tema [Sneat](https://themeselection.com/) |
| **E-mail** | PHPMailer — [Mailpit](https://mailpit.axllent.org/) em dev, [Brevo](https://www.brevo.com/) em produção |
| **Autenticação** | Sessão em banco, login social (Google/Facebook), [Cloudflare Turnstile](https://developers.cloudflare.com/turnstile/) anti-bot |
| **Ambiente** | Docker + Docker Compose |

---

## Rodando em localhost — passo a passo

### 1. Pré-requisitos

Você só precisa de duas coisas instaladas:

- [Docker](https://www.docker.com/products/docker-desktop/)
- [Git](https://git-scm.com/)

Nada de instalar PHP, MySQL ou Redis na sua máquina — tudo isso vive dentro
dos containers. ✨

### 2. Clone o repositório

```bash
git clone https://github.com/pedroleandro/moneta.git
cd moneta
```

### 3. Configure o `.env`

```bash
cp .env.example .env
```

Abre o `.env` e confere se está assim (config padrão pra rodar local sem
enviar e-mail de verdade):

```env
APP_ENV=local
MAIL_DRIVER=mailpit
MAIL_HOST=mailpit
MAIL_PORT=1025
```

> 💡 Se quiser testar login social (Google) ou o captcha (Turnstile), você
> vai precisar preencher `GOOGLE_CLIENT_ID`, `GOOGLE_CLIENT_SECRET`,
> `TURNSTILE_SITE_KEY` e `TURNSTILE_SECRET_KEY` com suas próprias credenciais.

### 4. Suba os containers

```bash
docker compose build
docker compose up -d
```

Na primeira vez, isso vai:
- Construir a imagem PHP + Apache
- Instalar as dependências via Composer
- Subir o MySQL já com o banco `moneta` criado
- Subir o Redis
- Subir o Mailpit (seu "Gmail fake" local)

### 5. Importe o schema do banco

```bash
docker exec -i moneta_db mysql -uroot -proot moneta < schema.sql
```

*(troca `schema.sql` pelo nome real do arquivo de schema do projeto, se for diferente)*

### 6. Pronto! Acesse:

| Serviço                                | Endereço |
|----------------------------------------|---|
| **Moneta**                             | http://localhost:8009 |
| **Mailpit** (caixa de entrada fake)    | http://localhost:8025 |
| **RedisInsight** (inspecionar o Redis) | http://localhost:5540 |
| **MySQL** (via DBeaver/DataGrip)       | `localhost:3309` |

---

## 📁 Estrutura do Projeto

```
moneta/
├── app/
│   ├── Controllers/      # Um Controller por recurso
│   ├── Models/            # Regras de negócio + validação
│   ├── Views/App/          # Views organizadas por recurso
│   └── Core/               # Infraestrutura (Auth, Session, Email...)
├── public/
│   └── assets/             # CSS, JS e imagens
├── routes/                 # Um arquivo de rotas por domínio
├── docker-compose.yml       # Orquestração dos containers
├── docker-compose.override.yml  # Ferramentas extras SÓ de dev
└── Dockerfile               # Imagem PHP + Apache
```

---

## Comandos úteis do dia a dia

```bash
# Ver o que está rodando
docker compose ps

# Acompanhar o log da aplicação em tempo real
docker compose logs -f app

# Entrar no container da aplicação (pra rodar Composer, artesanato PHP, etc.)
docker exec -it moneta_app bash

# Entrar direto no MySQL
docker exec -it moneta_db mysql -uroot -proot moneta

# Parar tudo
docker compose down

# Reconstruir do zero (depois de mudar o Dockerfile)
docker compose build --no-cache
```

---

## Debugando com Xdebug

O ambiente local já vem com **Xdebug** habilitado e configurado pra
`host.docker.internal:9003` — só configurar seu PhpStorm/VS Code pra escutar
nessa porta e colocar um breakpoint. Sem cerimônia. 🎯

---

## Boas práticas ao contribuir

- Branch nova por funcionalidade: `feature/nome-da-coisa` ou `fix/nome-do-bug`
- Commit direto ao ponto: `fix: `, `feat: `, `style: `, `refactor: ` como prefixo
- Nunca commitar o `.env` (ele já está no `.gitignore`, mas fica o aviso)
- Sempre use `findByIdForUser($id, $userId)` em vez de `find($id)` puro ao
  buscar dado sensível — é assim que evitamos um usuário acessar dado de outro

---

## Problemas comuns

<details>
<summary><strong>CSS/JS não carregam ao acessar por IP ou túnel</strong></summary>

<br>

Isso não deveria mais acontecer — o Moneta detecta o domínio real da
requisição automaticamente (`appBaseUrl()`), então funciona igual em
`localhost`, IP da rede local, túnel do Cloudflare ou produção, sem precisar
trocar `APP_URL` toda vez.

</details>

<details>
<summary><strong>Cloudflare Turnstile mostrando "Falha na verificação"</strong></summary>

<br>

Confere se o domínio que você está usando está cadastrado no painel do
Turnstile, na lista de "Nomes de host" daquela chave. E não teste em modo de
emulação de dispositivo do DevTools — o Turnstile detecta isso de propósito e
sempre vai falhar ali.

</details>

<details>
<summary><strong>Não recebo e-mail nenhum</strong></summary>

<br>

Se `MAIL_DRIVER=mailpit`, os e-mails **não saem pra internet de verdade** —
eles ficam capturados na interface do Mailpit: http://localhost:8025

</details>

---

<div align="center">

**Desenvolvido com ❤️ por Pedro Leandro**

</div>