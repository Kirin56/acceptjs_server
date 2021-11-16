<?php

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: *');
header('Access-Control-Allow-Headers: *');
header('Access-Control-Max-Age: 86400');

$data = file_get_contents('php://input');

if (! empty($data)) {
    $json = json_decode($data, true);
}

require_once './vendor/autoload.php';

const END_POINT = \net\authorize\api\constants\ANetEnvironment::SANDBOX;
const TRANSACTION_TYPE = 'authCaptureTransaction';

$merchantAuthentication = new \net\authorize\api\contract\v1\MerchantAuthenticationType;

$merchantAuthentication->setName('6h5w25tM7JV');
$merchantAuthentication->setTransactionKey('66nZqg33Ae7mY7Ts');

$customer = new \net\authorize\api\contract\v1\CustomerDataType;

$customer->setEmail('customer@example.com');
$customer->setType('individual');

$refId = 'ref' . time();

$opaqueData = new \net\authorize\api\contract\v1\OpaqueDataType;

$opaqueData->setDataDescriptor($json['data_descriptor']);
$opaqueData->setDataValue($json['data_value']);

$payment = new \net\authorize\api\contract\v1\PaymentType;

$payment->setOpaqueData($opaqueData);

$transactionRequestType = new \net\authorize\api\contract\v1\TransactionRequestType;

$transactionRequestType->setTransactionType(TRANSACTION_TYPE);
$transactionRequestType->setAmount(0.5);
$transactionRequestType->setPayment($payment);
$transactionRequestType->setCustomer($customer);

$request = new \net\authorize\api\contract\v1\CreateTransactionRequest;

$request->setMerchantAuthentication($merchantAuthentication);
$request->setRefId($refId);
$request->setTransactionRequest($transactionRequestType);

$controller = new \net\authorize\api\controller\CreateTransactionController($request);

$response = $controller->executeWithApiResponse(END_POINT);

if ($response === null) {
    throw new \Exception('AuthorizeNet service unavailable');
}

$transactionResponse = $response->getTransactionResponse();

if ($response->getMessages()->getResultCode() !== "Ok") {
    if ($transactionResponse === null) {
        throw new \Exception(
            "{$response->getMessages()->getMessage()[0]->getText()} {$response->getMessages()->getMessage()[0]->getCode()}"
        );
    } elseif ($transactionResponse->getErrors() !== null) {
        throw new \Exception(
            "{$transactionResponse->getErrors()[0]->getErrorText()} {$transactionResponse->getErrors()[0]->getErrorCode()}"
        );
    }
}

$answer = [];

foreach ($response->getMessages()->getMessage() as $message) {
    $answer[] = [
        'text' => $message->getText()
    ];
}

echo json_encode($answer);
