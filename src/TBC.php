<?php

namespace Gabievi\TBC;

class TBC
{
    /**
     * @var string
     */
    private $submit_url = 'https://securepay.ufc.ge:18443/ecomm2/MerchantHandler';

    /**
     * @var
     */
    private $cert_path;

    /**
     * @var
     */
    private $cert_pass;

    /**
     * @var
     */
    private $client_ip;

    /**
     * TBC constructor.
     */
    public function __construct()
    {
        $this->cert_path = config('tbc.cert_path');
        $this->cert_pass = config('tbc.cert_pass');

        $this->client_ip = request()->getClientIp();
    }

    /**
     * @param string $query
     *
     * @return mixed
     */
    private function cURL($query)
    {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_POSTFIELDS, $query);
        curl_setopt($curl, CURLOPT_VERBOSE, '1');
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, '0');
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, '0');
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_SSLCERT, $this->cert_path);
        curl_setopt($curl, CURLOPT_SSLKEY, $this->cert_path);
        curl_setopt($curl, CURLOPT_SSLKEYPASSWD, $this->cert_pass);
        curl_setopt($curl, CURLOPT_URL, $this->submit_url);
        $result = curl_exec($curl);
        curl_close($curl);

        return $result;
    }

    /**
     * @param array $data
     *
     * @return string
     */
    private function queryString(array $data = [])
    {
        return http_build_query($data);
    }

    /**
     * @param $string
     *
     * @return array
     */
    private function parse($string)
    {
        $array1 = explode(PHP_EOL, trim($string));
        $result = [];

        foreach ($array1 as $key => $value) {
            $array2 = explode(':', $value);
            $result[$array2[0]] = trim($array2[1]);
        }

        return $result;
    }

    /**
     * @param string $command
     * @param array  $data
     *
     * @return array
     */
    private function process($command, array $data = [])
    {
        return $this->parse(
            $this->cURL(
                $this->queryString(
                    array_merge([
                        'command' => $command,
                        $data,
                    ])
                )
            )
        );
    }

    /**
     * @param $amount
     * @param int    $currency
     * @param string $description
     * @param string $language
     *
     * @return array
     */
    public function SMSTransaction($amount, $currency = 981, $description = '', $language = 'GE')
    {
        return $this->process('v', [
            'amount'         => $amount,
            'currency'       => $currency,
            'client_ip_addr' => $this->client_ip,
            'description'    => $description,
            'language'       => $language,
            'msg_type'       => 'SMS',
        ]);
    }

    /**
     * @param $amount
     * @param int    $currency
     * @param string $description
     * @param string $language
     *
     * @return array
     */
    public function DMSAuthorization($amount, $currency = 981, $description = '', $language = 'GE')
    {
        return $this->process('a', [
            'amount'         => $amount,
            'currency'       => $currency,
            'client_ip_addr' => $this->client_ip,
            'description'    => $description,
            'language'       => $language,
            'msg_type'       => 'DMS',
        ]);
    }

    /**
     * @param $txn_id
     * @param $amount
     * @param int    $currency
     * @param string $description
     * @param string $language
     *
     * @return array
     */
    public function DMSTransaction($txn_id, $amount, $currency = 981, $description = '', $language = 'GE')
    {
        return $this->process('t', [
            'trans_id'       => $txn_id,
            'amount'         => $amount,
            'currency'       => $currency,
            'client_ip_addr' => $this->client_ip,
            'description'    => $description,
            'language'       => $language,
            'msg_type'       => 'DMS',
        ]);
    }

    /**
     * @param $txn_id
     *
     * @return array
     */
    public function getTransactionResult($txn_id)
    {
        return $this->process('c', [
            'trans_id'       => $txn_id,
            'client_ip_addr' => $this->client_ip,
        ]);
    }

    /**
     * @param $txn_id
     * @param $amount
     * @param string $suspected_fraud
     *
     * @return array
     */
    public function reverseTransaction($txn_id, $amount = '', $suspected_fraud = '')
    {
        return $this->process('r', [
            'trans_id'        => $txn_id,
            'amount'          => $amount,
            'suspected_fraud' => $suspected_fraud,
        ]);
    }

    /**
     * @param $txn_id
     *
     * @return array
     */
    public function refundTransaction($txn_id)
    {
        return $this->process('k', [
            'trans_id' => $txn_id,
        ]);
    }

    /**
     * @param $txn_id
     * @param $amount
     *
     * @return array
     */
    public function creditTransaction($txn_id, $amount = '')
    {
        return $this->process('g', [
            'trans_id' => $txn_id,
            'amount'   => $amount,
        ]);
    }

    /**
     * @return array
     */
    public function closeDay()
    {
        return $this->process('b');
    }
}