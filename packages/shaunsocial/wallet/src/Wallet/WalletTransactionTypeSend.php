<?php


namespace Packages\ShaunSocial\Wallet\Wallet;

use Packages\ShaunSocial\Core\Http\Resources\User\UserResource;

class WalletTransactionTypeSend extends WalletTransactionTypeBase
{
    public function getDescription($transaction)
    {
        $user = $transaction->getSubject();
        if (! $user) {
            $user = getDeleteUser();
        }
        if ($transaction->amount < 0) {
            return __('Send funds to'). ' '. $user->getName();
        } else {
            return __('Receive funds from'). ' '. $user->getName();
        }
    }

    public function getName()
    {
        return __('Send and receive funds');
    }

    public function getExtra($transaction)
    {
        $user = $transaction->getSubject();
        if (! $user) {
            $user = getDeleteUser();
        }
        return ['user' => new UserResource($user)];
    }
}
