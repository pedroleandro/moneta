<?php

namespace App\Controllers;

use App\Core\AuditLog;
use App\Core\Auth;
use App\Core\Controller;
use App\Core\LogEvent;
use App\Core\Logger;
use App\Core\Message;
use App\Models\Category;

class CategoryController extends Controller
{
    public function __construct()
    {
        parent::__construct("App");
        Auth::requireLogin();
    }

    public function index(): void
    {
        try {
            $userId = Auth::user()->id;
            $categories = Category::findAllForUser($userId);

            echo $this->view->render("categories/index", [
                "title" => "Categorias | " . APP_NAME,
                "active" => "categorias",
                "categories" => $categories,
            ]);
        } catch (\Throwable $exception) {
            Logger::error("Falha ao listar categorias", [
                "user_id" => Auth::user()->id ?? null,
                "exception" => $exception->getMessage(),
            ]);
            Message::error("Não foi possível carregar suas categorias.");
            redirect("/dashboard");
        }
    }

    public function create(): void
    {
        echo $this->view->render("categories/create", [
            "title" => "Nova Categoria | " . APP_NAME,
            "active" => "categorias",
        ]);

        clear_old();
    }

    public function store(?array $data): void
    {
        $this->validateCsrfToken($data, "/categorias/nova");

        $userId = Auth::user()->id;

        try {
            $category = new Category();
            $category->fill([
                "user_id" => $userId,
                "parent_id" => !empty($data["parent_id"]) ? (int)$data["parent_id"] : null,
                "name" => trim($data["name"] ?? ""),
                "type" => $data["type"] ?? "",
                "color" => $data["color"] ?? null,
                "icon" => $data["icon"] ?? null,
            ]);

            $errors = $category->validate($data);

            if ($errors) {
                flash_old($data);
                Message::error(implode(" ", $errors));
                redirect("/categorias/nova");
                return;
            }

            $category->save();

            AuditLog::record(LogEvent::CATEGORY_CREATED, $userId, [
                "category_id" => $category->getId(),
                "name" => $category->getName(),
            ]);

            clear_old();

            Message::success("Categoria criada com sucesso.");
            redirect("/categorias");
        } catch (\InvalidArgumentException $exception) {
            flash_old($data);
            Message::error($exception->getMessage());
            redirect("/categorias/nova");
        } catch (\Throwable $exception) {
            Logger::error("Falha ao criar categoria", [
                "user_id" => $userId,
                "exception" => $exception->getMessage(),
            ]);
            flash_old($data);
            Message::error("Não foi possível criar a categoria. Tente novamente.");
            redirect("/categorias/nova");
        }
    }

    public function edit(?array $data): void
    {
        $userId = Auth::user()->id;
        $id = (int)($data["id"] ?? 0);

        try {
            $category = Category::findByIdForUser($id, $userId);

            if (!$category) {
                Message::error("Categoria não encontrada.");
                redirect("/categorias");
                return;
            }

            if ($category->isSystemDefault()) {
                Message::error("Categorias padrão do sistema não podem ser editadas.");
                redirect("/categorias");
                return;
            }

            echo $this->view->render("categories/edit", [
                "title" => "Editar Categoria | " . APP_NAME,
                "active" => "categorias",
                "category" => $category,
            ]);

            clear_old();
        } catch (\Throwable $exception) {
            Logger::error("Falha ao carregar categoria para edição", [
                "user_id" => $userId,
                "category_id" => $id,
                "exception" => $exception->getMessage(),
            ]);
            Message::error("Não foi possível carregar a categoria.");
            redirect("/categorias");
        }
    }

    public function update(?array $data): void
    {
        $userId = Auth::user()->id;
        $id = (int)($data["id"] ?? 0);

        $this->validateCsrfToken($data, "/categorias/{$id}/editar");

        try {
            $category = Category::findByIdForUser($id, $userId);

            if (!$category) {
                Message::error("Categoria não encontrada.");
                redirect("/categorias");
                return;
            }

            if ($category->isSystemDefault()) {
                Message::error("Categorias padrão do sistema não podem ser editadas.");
                redirect("/categorias");
                return;
            }

            $category->fill([
                "name" => trim($data["name"] ?? ""),
                "type" => $data["type"] ?? "",
                "parent_id" => !empty($data["parent_id"]) ? (int)$data["parent_id"] : null,
                "color" => $data["color"] ?? null,
                "icon" => $data["icon"] ?? null,
            ]);

            $errors = $category->validate($data);

            if ($errors) {
                flash_old($data);
                Message::error(implode(" ", $errors));
                redirect("/categorias/{$id}/editar");
                return;
            }

            $category->save();

            AuditLog::record(LogEvent::CATEGORY_UPDATED, $userId, [
                "category_id" => $category->getId(),
                "name" => $category->getName(),
            ]);

            clear_old();

            Message::success("Categoria atualizada com sucesso.");
            redirect("/categorias");
        } catch (\InvalidArgumentException $exception) {
            flash_old($data);
            Message::error($exception->getMessage());
            redirect("/categorias/{$id}/editar");
        } catch (\Throwable $exception) {
            Logger::error("Falha ao atualizar categoria", [
                "user_id" => $userId,
                "category_id" => $id,
                "exception" => $exception->getMessage(),
            ]);
            Message::error("Não foi possível atualizar a categoria. Tente novamente.");
            redirect("/categorias/{$id}/editar");
        }
    }

    public function destroy(?array $data): void
    {
        $userId = Auth::user()->id;
        $id = (int)($data["id"] ?? 0);

        $this->validateCsrfToken($data, "/categorias");

        try {
            $category = Category::findByIdForUser($id, $userId);

            if (!$category) {
                Message::error("Categoria não encontrada.");
                redirect("/categorias");
                return;
            }

            if ($category->isSystemDefault()) {
                Message::error("Categorias padrão do sistema não podem ser excluídas.");
                redirect("/categorias");
                return;
            }

            if (!$category->belongsToUser($userId)) {
                Message::error("Você não tem permissão para excluir essa categoria.");
                redirect("/categorias");
                return;
            }

            if ($category->isInUse()) {
                Message::error(
                    "Essa categoria não pode ser excluída porque já está vinculada a lançamentos, " .
                    "recorrências ou compras parceladas."
                );
                redirect("/categorias");
                return;
            }

            $category->delete();

            AuditLog::record(LogEvent::CATEGORY_DELETED, $userId, [
                "category_id" => $id,
                "name" => $category->getName(),
            ]);

            Message::success("Categoria excluída com sucesso.");
            redirect("/categorias");
        } catch (\Throwable $exception) {
            Logger::error("Falha ao excluir categoria", [
                "user_id" => $userId,
                "category_id" => $id,
                "exception" => $exception->getMessage(),
            ]);
            Message::error("Não foi possível excluir a categoria. Tente novamente.");
            redirect("/categorias");
        }
    }

    public function duplicate(?array $data): void
    {
        $userId = Auth::user()->id;
        $id = (int)($data["id"] ?? 0);

        $this->validateCsrfToken($data, "/categorias");

        try {
            $category = Category::findByIdForUser($id, $userId);

            if (!$category) {
                Message::error("Categoria não encontrada.");
                redirect("/categorias");
                return;
            }

            if (!$category->isSystemDefault()) {
                Message::error("Essa categoria já é sua — não precisa duplicar.");
                redirect("/categorias");
                return;
            }

            $copy = $category->duplicateForUser($userId);

            AuditLog::record(LogEvent::CATEGORY_CREATED, $userId, [
                "category_id" => $copy->getId(),
                "name" => $copy->getName(),
                "duplicated_from" => $id,
            ]);

            Message::success("Categoria duplicada com sucesso. Agora você pode personalizá-la.");
            redirect("/categorias");
        } catch (\Throwable $exception) {
            Logger::error("Falha ao duplicar categoria", [
                "user_id" => $userId,
                "category_id" => $id,
                "exception" => $exception->getMessage(),
            ]);
            Message::error("Não foi possível duplicar a categoria. Tente novamente.");
            redirect("/categorias");
        }
    }
}