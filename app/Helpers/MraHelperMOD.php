<?php

namespace App\Helpers;

class MraHelper
{
    // Helper function to sanitize numeric values
    private static function sanitizeNumber($number) {
        // Remove any commas and convert to float
        return floatval(str_replace(',', '', $number));
    }

    public static function generateToken($transaction, $products, $business_gov)
    {
        
        $itemList = [];
        $itemCounter = 1;
        foreach ($products as $product) {
            $unitPrice = self::sanitizeNumber($product['unit_price']);
            $unitPriceIncTax = self::sanitizeNumber($product['unit_price_inc_tax']);
            $lineDiscountAmount = self::sanitizeNumber($product['line_discount_amount']);
            $itemTax = self::sanitizeNumber($product['item_tax']);
    
        $itemList[] = [
            'itemNo' => (string) $itemCounter++,
            'taxCode' => 'TC01',
            'nature' => 'GOODS',
            'productCodeMra' => '',
            'productCodeOwn' => 'ITEMCODE' . $product['product_id'],
            'itemDesc' => 'ITEM NAME ' . $product['product_id'],
            'quantity' => $product['quantity'],
            'unitPrice' => $unitPrice,
            'discount' => $lineDiscountAmount,
            'discountedValue' => round($unitPrice - $lineDiscountAmount, 2),
            'amtWoVatCur' => round($unitPrice - $lineDiscountAmount, 2),
            'amtWoVatMur' => round($unitPrice - $lineDiscountAmount, 2),
            'vatAmt' => $itemTax,
            'totalPrice' => round($unitPriceIncTax, 2)
        ];
    }


        $ebsMraId = '171501038171896R87B7D10X';
        $ebsMraUsername = 'idamauritius';
        $ebsMraPassword = 'Dansmoris0813@@';
        $areaCode = '305';

        // Generate a random AES key
        $aesKey = openssl_random_pseudo_bytes(32); // 32 bytes for AES-256
        $aesKeyBase64 = base64_encode($aesKey);

        $payload = array(
            'encryptKey' => $aesKeyBase64,
            'username' => $ebsMraUsername,
            'password' => $ebsMraPassword,
            'refreshToken' => true
        );

        // Import the certificate
        $certPath = storage_path('app/MRAPublicKey.crt');
        $certContent = file_get_contents($certPath);
        if ($certContent === false) {
            return ['error' => 'Failed to read certificate'];
        }
        $cert = openssl_x509_read($certContent);
        if ($cert === false) {
            return ['error' => 'Failed to read certificate content'];
        }

        // Extract the public key from the certificate
        $pubKeyDetails = openssl_pkey_get_details(openssl_pkey_get_public($cert));
        $publicKey = $pubKeyDetails['key'];

        // Encrypt payload using MRA public key
        $encryptedData = '';
        openssl_public_encrypt(json_encode($payload), $encryptedData, $publicKey);
        $base64EncodedData = base64_encode($encryptedData);

        $requestId = mt_rand();
        $postData = array(
            'requestId' => $requestId,
            'payload' => $base64EncodedData
        );

        $requestHeadersAuth = [
            'Content-Type: application/json',
            'ebsMraId: ' . $ebsMraId,
            'username: ' . $ebsMraUsername,
            'areaCode: ' . $areaCode
        ];

        $chAuth = curl_init();
        curl_setopt($chAuth, CURLOPT_URL, "https://vfisc.mra.mu/einvoice-token-service/token-api/generate-token");
        curl_setopt($chAuth, CURLOPT_POST, 1);
        curl_setopt($chAuth, CURLOPT_POSTFIELDS, json_encode($postData));
        curl_setopt($chAuth, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($chAuth, CURLOPT_HTTPHEADER, $requestHeadersAuth);
        curl_setopt($chAuth, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($chAuth, CURLOPT_SSL_VERIFYPEER, 0);

        $responseDataAuth = curl_exec($chAuth);
        if ($responseDataAuth === false) {
            return ['error' => 'cURL error: ' . curl_error($chAuth)];
        }

        $responseArray = json_decode($responseDataAuth, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            return ['error' => 'JSON decode error: ' . json_last_error_msg()];
        }

        if (!isset($responseArray['token']) || !isset($responseArray['key'])) {
            return ['error' => 'Error: token or key not found in the response'];
        }

        // Add requestId to the response array
        $responseArray['requestId'] = $requestId;

        // Prepare the invoice data
        $arInvoice = [
            'invoiceCounter' => $requestId,
            'transactionType' => 'B2C',
            'personType' => 'VATR',
            'invoiceTypeDesc' => 'STD',
            'currency' => 'MUR',
            'invoiceIdentifier' => $transaction->invoice_no,
            'invoiceRefIdentifier' => '',
            'previousNoteHash' => 'prevNote',
            'reasonStated' => '',
            'totalVatAmount' => round($transaction->tax_amount, 2),
            'totalAmtWoVatCur' => round($transaction->final_total, 2),
            'totalAmtWoVatMur' => round($transaction->final_total, 2),
            'invoiceTotal' => round($transaction->final_total, 2),
            'discountTotalAmount' => round($transaction->discount_amount, 2),
            'totalAmtPaid' => round($transaction->final_total, 2),
            //'dateTimeInvoiceIssued' => date('Ymd H:i:s'),
            'dateTimeInvoiceIssued' => date('Ymd H:i:s', strtotime($transaction['transaction_date'])),
            'salesTransactions' => strtoupper($transaction->method),
            'seller' => [
                'name' => $business_gov->name,
                'tradeName' => '',
                'tan' => '27672656',
                'brn' => 'C19161438',
                'businessAddr' => $business_gov->name,
                'businessPhoneNo' => '',
                'ebsCounterNo' => 'a1'
            ],
            'buyer' => [
                'name' => '',
                'tan' => '27672656',
                'brn' => 'C19161438',
                'businessAddr' => 'Quatre Bornes',
                'buyerType' => 'VATR',
                'nic' => ''
            ],
            'itemList' => $itemList
            // 'itemList' => [
            //     [
            //         'itemNo' => '1',
            //         'taxCode' => 'TC01',
            //         'nature' => 'GOODS',
            //         'productCodeMra' => '',
            //         'productCodeOwn' => 'ITEMCODE01',
            //         'itemDesc' => 'ITEM NAME 01',
            //         'quantity' => '1',
            //         'unitPrice' => '110',
            //         'discount' => '0',
            //         'discountedValue' => '10',
            //         'amtWoVatCur' => '100',
            //         'amtWoVatMur' => '100',
            //         'vatAmt' => '15',
            //         'totalPrice' => '115'
            //     ]
            // ]
        ];

        $invoiceArray = array($arInvoice);
        $jsonencode = json_encode($invoiceArray);

        // Decrypt the MRA key using AES-256-ECB
        $decryptedKey = openssl_decrypt($responseArray['key'], 'AES-256-ECB', base64_decode($aesKeyBase64));
        if ($decryptedKey === false) {
            return ['error' => 'Failed to decrypt MRA key'];
        }

        // Encrypt the invoice using AES-256-ECB
        $encryptedInvoice = openssl_encrypt($jsonencode, 'AES-256-ECB', base64_decode($decryptedKey), OPENSSL_RAW_DATA);
        if ($encryptedInvoice === false) {
            return ['error' => 'Failed to encrypt invoice'];
        }

        $payloadInv = base64_encode($encryptedInvoice);

        $requestHeadersInv = [
            'Content-Type: application/json',
            'ebsMraId: ' . $ebsMraId,
            'username: ' . $ebsMraUsername,
            'areaCode: ' . $areaCode,
            'token: ' . $responseArray['token']
        ];

        $postDataInv = [
            'requestId' => $requestId,
            'requestDateTime' => date('Ymd H:i:s'),
            'signedHash' => '',
            'encryptedInvoice' => $payloadInv
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "https://vfisc.mra.mu/realtime/invoice/transmit");
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($postDataInv));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $requestHeadersInv);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);

        $responseData = curl_exec($ch);
        if ($responseData === false) {
            return ['error' => 'cURL error: ' . curl_error($ch)];
        }

        $invoiceResponse = json_decode($responseData, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            return ['error' => 'JSON decode error: ' . json_last_error_msg()];
        }

        return [
            'token_response' => $responseArray,
            'invoice_response' => $invoiceResponse
        ];
    }
}
