<?php namespace BB\Presenters;

use BB\Entities\Payment;
use Laracasts\Presenter\Presenter;

class PaymentPresenter extends Presenter
{

    public function reason()
    {
        switch ($this->entity->reason) {
            case 'subscription':
                return 'Subscription';
            case 'unknown':
                return 'Unknown';
            case 'induction':
                return 'Equipment Access Fee';
            case 'door-key':
                return 'Key Deposit';
            case 'storage-box':
                return 'Storage Box Deposit';
            case 'balance':
                return 'Balance Top Up';
            case 'equipment-fee':
                return 'Equipment Costs';
            case 'withdrawal':
                return 'Withdrawal';
            case 'consumables':
                return 'Consumables (' . $this->entity->reference . ')';
            case 'transfer':
                return 'Transfer (to user:' . $this->entity->reference . ')';
            case 'donation':
                return 'Donation';
            default:
                return $this->entity->reason;
        }
    }

    public function status()
    {
        switch ($this->entity->status) {
            case Payment::STATUS_PENDING:
                return 'Pending confirmation';
            case Payment::STATUS_PENDING_SUBMISSION:
                return 'Pending submission to members bank';
            case PaymenT::STATUS_CANCELLED:
                return 'Cancelled';
            case Payment::STATUS_PAID:
            case Payment::STATUS_WITHDRAWN:
                return 'Paid';
            default:
                return $this->entity->status;
        }
    }

    public function date()
    {
        return $this->entity->created_at->toFormattedDateString();
    }

    public function method()
    {
        switch ($this->entity->source) {
            case 'gocardless':
            case 'gocardless-variable':
                return 'Direct Debit';
            case 'standing-order':
                return 'Standing Order';
            case 'manual':
                return 'Manual';
            case 'cash':
                return 'Cash' . ($this->entity->source_id? ' (' . $this->entity->source_id . ')':'');
            case 'other':
                return 'Other';
            case 'balance':
                return 'BB Balance';
            case 'reimbursement':
                return 'Reimbursement';
            case 'transfer':
                return 'Transfer (from user:' . $this->entity->reference . ')';
            default:
                return $this->entity->source;
        }
    }

    public function amount()
    {
        return '£' . number_format($this->entity->amount, 2);
    }

    public function balanceAmount()
    {
        if ($this->entity->source == 'balance') {
            return '-£' . $this->entity->amount;
        }

        if ($this->entity->reason == 'balance') {
            return '£' . $this->entity->amount;
        }
    }

    public function balanceRowClass()
    {
        if ($this->entity->source == 'balance') {
            return 'danger';
        }

        if ($this->entity->reason == 'balance') {
            return 'success';
        }
    }
} 
