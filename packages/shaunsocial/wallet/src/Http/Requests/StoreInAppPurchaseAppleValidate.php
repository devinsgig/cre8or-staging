<?php

namespace Packages\ShaunSocial\Wallet\Http\Requests;

use Packages\ShaunSocial\Core\Http\Requests\BaseFormRequest;
use Packages\ShaunSocial\Wallet\Models\WalletOrder;
use Packages\ShaunSocial\Wallet\Models\WalletPackage;
use ReceiptValidator\iTunes\Validator as iTunesValidator;

class StoreInAppPurchaseAppleValidate extends BaseFormRequest
{
    public function rules()
    {
        return [
            'data' => [
                'required',
                'string',
                function ($attribute, $data, $fail) {
                    if (!validateJson($data)) {
                        return $fail(__('The data must be json format.'));
                    }

                    $listValidate = ['productId', 'transactionId', 'transactionReceipt'];
                    foreach ($listValidate as $key) {
                        if (empty($data[$key])) {
                            return $fail(__('The data must be json format.'));
                        }
                    }

                    $validator = new iTunesValidator(iTunesValidator::ENDPOINT_PRODUCTION);
                    try {
                        $response = $validator->setSharedSecret(setting('mobile.apple_shared_secret'))->setReceiptData($data['transactionReceipt'])->validate(); // use setSharedSecret() if for recurring subscriptions
                    } catch (\Exception $e) {
                        return $fail($e->getMessage());
                    }

                    if ($response->getBundleId() != setting('mobile.apple_bundle_id')) {
                        return $fail('The apple bundle id is not correct.');
                    }

                    $order = WalletOrder::findByField('gateway_transaction_id', $data['transactionId']);
                    if ($order) {
                        return $fail('The order already exists.');
                    }

                    $package = WalletPackage::findByField('google_price_id', $data['productId']);
                    if (!$package || $package->is_delete) {
                        return $fail('The package not found.');
                    }
                },
            ]
        ];
    }

    public function messages()
    {
        return [
            'data.required' => __('The gateway id is required.'),
        ];
    }
}
