<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Logger;
use App\Core\Message;
use App\Models\BankAccount;
use App\Models\CardInvoice;
use App\Models\Transaction;
use App\Models\TransactionSplit;

class DashboardController extends Controller
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
            $currentMonth = date('Y-m');

            $totalBalance = BankAccount::getTotalBalanceForUser($userId);
            $monthIncome = Transaction::getMonthlyTotal($userId, Transaction::TYPE_INCOME, $currentMonth);
            $monthExpense = Transaction::getMonthlyTotal($userId, Transaction::TYPE_EXPENSE, $currentMonth);
            $owedToMe = TransactionSplit::getTotalOwedToUser($userId);

            $chartData = Transaction::getMonthlyChartData($userId, 6);
            $topCategories = Transaction::getTopCategories($userId, $currentMonth, 5);
            $recentTransactions = Transaction::findRecentForUser($userId, 6);
            $upcomingInvoices = CardInvoice::findUpcomingForUser($userId, 5);

            echo $this->view->render("dashboard/dashboard", [
                "title" => "Dashboard | " . APP_NAME,
                "active" => "dashboard",
                "totalBalance" => $totalBalance,
                "monthIncome" => $monthIncome,
                "monthExpense" => $monthExpense,
                "owedToMe" => $owedToMe,
                "chartData" => $chartData,
                "topCategories" => $topCategories,
                "recentTransactions" => $recentTransactions,
                "upcomingInvoices" => $upcomingInvoices,
            ]);
        } catch (\Throwable $exception) {
            Logger::error("Falha ao carregar dashboard", [
                "user_id" => Auth::user()->id ?? null,
                "exception" => $exception->getMessage(),
            ]);
            Message::error("Não foi possível carregar seu dashboard.");
            echo $this->view->render("dashboard/dashboard", [
                "title" => "Dashboard | " . APP_NAME,
                "active" => "dashboard",
                "totalBalance" => 0,
                "monthIncome" => 0,
                "monthExpense" => 0,
                "owedToMe" => 0,
                "chartData" => ["labels" => [], "income" => [], "expense" => []],
                "topCategories" => [],
                "recentTransactions" => [],
                "upcomingInvoices" => [],
            ]);
        }
    }
}