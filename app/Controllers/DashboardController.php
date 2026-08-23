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

            $selectedMonth = $_GET["mes"] ?? date('Y-m');

            if (!preg_match('/^\d{4}-\d{2}$/', $selectedMonth)) {
                $selectedMonth = date('Y-m');
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

            $firstMonth = Transaction::getFirstMonthForUser($userId);
            $lastMonth = Transaction::getLastMonthForUser($userId);
            $monthOptions = $this->buildMonthOptions($firstMonth, $lastMonth);

            $hasSelected = false;
            foreach ($monthOptions as $option) {
                if ($option['value'] === $selectedMonth) {
                    $hasSelected = true;
                    break;
                }
            }
            if (!$hasSelected) {
                $selectedMonth = date('Y-m');
            }

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
                "monthOptions" => $monthOptions,
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
                "monthOptions" => [["value" => date('Y-m'), "label" => "Mês atual"]],
                "cardUsers" => [],
                "selectedPersonId" => null,
            ]);
        }
    }

    /**
     * Lista de meses pro seletor — do mês do primeiro lançamento até
     * o mês do lançamento mais distante no futuro (última parcela
     * de uma compra parcelada, por exemplo). Do mais recente pro
     * mais antigo. Teto de 36 meses por segurança.
     */
    private function buildMonthOptions(?string $firstMonth, ?string $lastMonth): array
    {
        $today = new \DateTimeImmutable('first day of this month');

        if (!$firstMonth) {
            return [["value" => $today->format('Y-m'), "label" => $this->formatMonthLabel($today)]];
        }

        $first = new \DateTimeImmutable($firstMonth . '-01');
        $last = $lastMonth ? new \DateTimeImmutable($lastMonth . '-01') : $today;

        if ($last < $today) {
            $last = $today;
        }

        $options = [];
        $cursor = $last;
        $count = 0;

        while ($cursor >= $first && $count < 36) {
            $options[] = ["value" => $cursor->format('Y-m'), "label" => $this->formatMonthLabel($cursor)];
            $cursor = $cursor->modify('-1 month');
            $count++;
        }

        return $options;
    }

    private function formatMonthLabel(\DateTimeImmutable $date): string
    {
        return ucfirst($this->monthNamePt((int)$date->format('n'))) . '/' . $date->format('Y');
    }

    private function monthNamePt(int $month): string
    {
        $names = [
            1 => 'janeiro', 2 => 'fevereiro', 3 => 'março', 4 => 'abril',
            5 => 'maio', 6 => 'junho', 7 => 'julho', 8 => 'agosto',
            9 => 'setembro', 10 => 'outubro', 11 => 'novembro', 12 => 'dezembro',
        ];

        return $names[$month] ?? '';
    }
}