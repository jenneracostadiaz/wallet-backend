<?php

namespace App\Services;

use App\Models\ScheduledPayment;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class PaymentNotificationService
{
    /**
     * Get payments that need notification today.
     */
    public function getPaymentsNeedingNotification(): Collection
    {
        return ScheduledPayment::with(['account.currency', 'category', 'paymentSchedule'])
            ->where('status', 'active')
            ->where(function($query) {
                // Payments due today
                $query->whereDate('next_payment_date', today())
                      // Or payments due in the notification window
                      ->orWhere(function($subQuery) {
                          $subQuery->whereHas('paymentSchedule', function($scheduleQuery) {
                              $scheduleQuery->whereRaw('DATE(next_payment_date) = DATE_ADD(CURDATE(), INTERVAL days_before_notification DAY)');
                          });
                      });
            })
            ->get();
    }

    /**
     * Get overdue payments.
     */
    public function getOverduePayments(): Collection
    {
        return ScheduledPayment::with(['account.currency', 'category', 'debtDetail'])
            ->where('status', 'active')
            ->whereDate('next_payment_date', '<', today())
            ->get();
    }

    /**
     * Get notification summary for a user.
     */
    public function getNotificationSummary(int $userId): array
    {
        $user = \App\Models\User::find($userId);
        
        $upcoming = $user->scheduledPayments()
            ->with(['account.currency', 'category'])
            ->where('status', 'active')
            ->whereBetween('next_payment_date', [today(), today()->addDays(7)])
            ->get();

        $overdue = $user->scheduledPayments()
            ->with(['account.currency', 'category'])
            ->where('status', 'active')
            ->whereDate('next_payment_date', '<', today())
            ->get();

        $dueToday = $user->scheduledPayments()
            ->with(['account.currency', 'category'])
            ->where('status', 'active')
            ->whereDate('next_payment_date', today())
            ->get();

        return [
            'overdue' => [
                'count' => $overdue->count(),
                'total_amount' => $overdue->sum('amount'),
                'payments' => $overdue->map(function($payment) {
                    return [
                        'id' => $payment->id,
                        'name' => $payment->name,
                        'amount' => $payment->amount,
                        'due_date' => $payment->next_payment_date,
                        'days_overdue' => today()->diffInDays($payment->next_payment_date),
                        'account' => $payment->account->name,
                        'currency' => $payment->account->currency->code,
                    ];
                })
            ],
            'due_today' => [
                'count' => $dueToday->count(),
                'total_amount' => $dueToday->sum('amount'),
                'payments' => $dueToday->map(function($payment) {
                    return [
                        'id' => $payment->id,
                        'name' => $payment->name,
                        'amount' => $payment->amount,
                        'account' => $payment->account->name,
                        'currency' => $payment->account->currency->code,
                    ];
                })
            ],
            'upcoming_week' => [
                'count' => $upcoming->count(),
                'total_amount' => $upcoming->sum('amount'),
                'by_day' => $upcoming->groupBy(function($payment) {
                    return $payment->next_payment_date->format('Y-m-d');
                })->map(function($dayPayments, $date) {
                    return [
                        'date' => $date,
                        'day_name' => Carbon::parse($date)->locale('es')->dayName,
                        'count' => $dayPayments->count(),
                        'total_amount' => $dayPayments->sum('amount'),
                        'payments' => $dayPayments->map(function($payment) {
                            return [
                                'id' => $payment->id,
                                'name' => $payment->name,
                                'amount' => $payment->amount,
                                'account' => $payment->account->name,
                                'currency' => $payment->account->currency->code,
                            ];
                        })
                    ];
                })
            ]
        ];
    }

    /**
     * Generate notification messages.
     */
    public function generateNotificationMessages(int $userId): array
    {
        $summary = $this->getNotificationSummary($userId);
        $messages = [];

        // Overdue payments
        if ($summary['overdue']['count'] > 0) {
            $messages[] = [
                'type' => 'warning',
                'title' => 'Pagos Vencidos',
                'message' => "Tienes {$summary['overdue']['count']} pago(s) vencido(s) por un total de {$summary['overdue']['total_amount']}",
                'count' => $summary['overdue']['count'],
                'action' => 'view_overdue'
            ];
        }

        // Due today
        if ($summary['due_today']['count'] > 0) {
            $messages[] = [
                'type' => 'info',
                'title' => 'Pagos de Hoy',
                'message' => "Tienes {$summary['due_today']['count']} pago(s) programado(s) para hoy por un total de {$summary['due_today']['total_amount']}",
                'count' => $summary['due_today']['count'],
                'action' => 'view_today'
            ];
        }

        // Upcoming this week
        if ($summary['upcoming_week']['count'] > 0) {
            $messages[] = [
                'type' => 'success',
                'title' => 'Próximos Pagos',
                'message' => "Tienes {$summary['upcoming_week']['count']} pago(s) programado(s) para esta semana",
                'count' => $summary['upcoming_week']['count'],
                'action' => 'view_upcoming'
            ];
        }

        return $messages;
    }
}
