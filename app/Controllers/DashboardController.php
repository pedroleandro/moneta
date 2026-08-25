<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Logger;
use App\Core\Message;
use App\Models\BankAccount;
use App\Models\CardInvoice;
use App\Models\CardUser;
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
            $firstMonth = Transaction::getFirstMonthForUser($userId);
            $lastMonth = Transaction::getLastMonthForUser($userId);

            $minMonth = ($firstMonth && $firstMonth < $currentMonth) ? $firstMonth : $currentMonth;
            $maxMonth = ($lastMonth && $lastMonth > $currentMonth) ? $lastMonth : $currentMonth;

            $selectedMonth = $_GET["mes"] ?? $currentMonth;

            if (!preg_match('/^\d{4}-\d{2}$/', $selectedMonth)) {
                $selectedMonth = $currentMonth;
            }

            if ($selectedMonth < $minMonth) {
                $selectedMonth = $minMonth;
            }
            if ($selectedMonth > $maxMonth) {
                $selectedMonth = $maxMonth;
            }

            $selectedPersonId = !empty($_GET["pessoa"]) ? (int)$_GET["pessoa"] : null;
            $cardUsers = CardUser::findAllForUser($userId);

            if ($selectedPersonId) {
                $belongsToUser = false;
                foreach ($cardUsers as $cardUser) {
                    if ($cardUser->getId() === $selectedPersonId) {
                        $belongsToUser = true;
                        break;
                    }
                }
                if (!$belongsToUser) {
                    $selectedPersonId = null;
                }
            }

            $totalBalance = BankAccount::getTotalBalanceForUser($userId);
            $monthIncome = Transaction::getMonthlyTotal($userId, Transaction::TYPE_INCOME, $selectedMonth);
            $monthExpense = Transaction::getMonthlyTotal($userId, Transaction::TYPE_EXPENSE, $selectedMonth);
            $owedToMe = TransactionSplit::getOwedToUserForMonth($userId, $selectedMonth, $selectedPersonId);

            $chartData = Transaction::getMonthlyChartData($userId, 12);
            $topCategories = Transaction::getTopCategories($userId, $selectedMonth, 5);
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
                "selectedMonth" => $selectedMonth,
                "minMonth" => $minMonth,
                "maxMonth" => $maxMonth,
                "cardUsers" => $cardUsers,
                "selectedPersonId" => $selectedPersonId,
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
                "selectedMonth" => date('Y-m'),
                "minMonth" => date('Y-m'),
                "maxMonth" => date('Y-m'),
                "cardUsers" => [],
                "selectedPersonId" => null,
            ]);
        }
    }
}