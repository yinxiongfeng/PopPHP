<?php
/**
 * Pop PHP Framework
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.TXT.
 * It is also available through the world-wide-web at this URL:
 * http://www.popphp.org/LICENSE.TXT
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to info@popphp.org so we can send you a copy immediately.
 *
 * @category   Pop
 * @package    Pop_Payment
 * @author     Nick Sagona, III <nick@popphp.org>
 * @copyright  Copyright (c) 2009-2012 Moc 10 Media, LLC. (http://www.moc10media.com)
 * @license    http://www.popphp.org/LICENSE.TXT     New BSD License
 */

/**
 * @namespace
 */
namespace Pop\Payment\Adapter;

use Pop\Curl\Curl;

/**
 * @category   Pop
 * @package    Pop_Payment
 * @author     Nick Sagona, III <nick@popphp.org>
 * @copyright  Copyright (c) 2009-2012 Moc 10 Media, LLC. (http://www.moc10media.com)
 * @license    http://www.popphp.org/LICENSE.TXT     New BSD License
 * @version    0.9
 */
class PayLeap extends AbstractAdapter
{

    /**
     * API Login ID
     * @var string
     */
    protected $apiLoginId = null;

    /**
     * Transaction Key
     * @var string
     */
    protected $transKey = null;

    /**
     * Test URL
     * @var string
     */
    protected $testUrl = 'https://uat.payleap.com/TransactServices.svc/ProcessCreditCard';

    /**
     * Live URL
     * @var string
     */
    protected $liveUrl = 'https://secure1.payleap.com/TransactServices.svc/ProcessCreditCard';

    /**
     * Transaction data
     * @var array
     */
    protected $transaction = array(
        'UserName'    => null,
        'Password'    => null,
        'TransType'   => 'Sale',
        'CardNum'     => null,
        'ExpDate'     => null,
        'CVNum'       => null,
        'Amount'      => null,
        'FNameOnCard' => null,
        'LNameOnCard' => null,
        'InvNum'      => null,
        'Street'      => null,
        'City'        => null,
        'State'       => null,
        'Zip'         => null,
        'Country'     => null,
        'Email'       => null,
        'Phone'       => null,
        'Fax'         => null,
        'TaxAmt'      => null,
        'CustomerID'  => null,
        'PONum'       => null
    );

    /**
     * Transaction fields for normalization purposes
     * @var array
     */
    protected $fields = array(
        'amount'          => 'Amount',
        'cardNum'         => 'CardNum',
        'expDate'         => 'ExpDate',
        'ccv'             => 'CVNum',
        'firstName'       => 'FNameOnCard',
        'lastName'        => 'LNameOnCard',
        'address'         => 'Street',
        'city'            => 'City',
        'state'           => 'State',
        'zip'             => 'Zip',
        'country'         => 'Country',
        'phone'           => 'Phone',
        'fax'             => 'Fax',
        'email'           => 'Email',
    );

    /**
     * Required fields
     * @var array
     */
    protected $requiredFields = array(
        'UserName',
        'Password',
        'TransType',
        'CardNum',
        'ExpDate',
        'Amount'
    );

    /**
     * Constructor
     *
     * Method to instantiate an Payleap payment adapter object
     *
     * @param  string  $apiLoginId
     * @param  string  $transKey
     * @param  boolean $test
     * @return void
     */
    public function __construct($apiLoginId, $transKey, $test = false)
    {
        $this->apiLoginId = $apiLoginId;
        $this->transKey = $transKey;
        $this->transaction['UserName'] = $apiLoginId;
        $this->transaction['Password'] = $transKey;
        $this->test = $test;
    }

    /**
     * Send transaction
     *
     * @param  boolean $verifyPeer
     * @throws Exception
     * @return Pop\Payment\Adapter\Authorize
     */
    public function send($verifyPeer = true)
    {
        if (!$this->validate()) {
            throw new Exception('The required transaction data has not been set.');
        }

        $url = ($this->test) ? $this->testUrl : $this->liveUrl;
        $url .= '?' . $this->buildQueryString();

        $options = array(
            CURLOPT_URL            => $url,
            CURLOPT_RETURNTRANSFER => true
        );

        if (!$verifyPeer) {
            $options[CURLOPT_SSL_VERIFYPEER] = false;
        }

        $curl = new Curl($options);
        $this->response = $curl->execute();
        $this->responseCodes = $this->parseResponseCodes();
        $this->responseCode = $this->responseCodes['Result'];
        $this->message = $this->responseCodes['RespMSG'];

        switch ($this->message) {
            case 'Approved':
                $this->approved = true;
                break;
            case 'Declined':
                $this->declined = true;
                break;
        }

        if ($this->responseCode > 0) {
            $this->error = true;
        }
    }

    /**
     * Build the query string
     *
     * @return string
     */
    protected function buildQueryString()
    {
        $query = $this->transaction;
        $query['CardNum'] = $this->filterCardNum($query['CardNum']);
        $query['ExpDate'] = $this->filterExpDate($query['ExpDate']);

        if ((null !== $query['FNameOnCard']) || (null !== $query['LNameOnCard'])) {
            $query['NameOnCard'] = $query['FNameOnCard'] . ' ' . $query['LNameOnCard'];
        } else {
            $query['NameOnCard'] = null;
        }

        $query['MagData'] = null;
        $query['ExtData'] = $this->buildExtData();
        $query['PNRef'] = null;

        unset($query['FNameOnCard']);
        unset($query['LNameOnCard']);
        unset($query['City']);
        unset($query['State']);
        unset($query['Country']);
        unset($query['Email']);
        unset($query['Phone']);
        unset($query['Fax']);
        unset($query['TaxAmt']);
        unset($query['CustomerID']);
        unset($query['PONum']);

        $queryString = null;
        foreach ($query as $key => $value) {
            $queryString .= '&' . $key . '=' . urlencode($value);
        }

        return substr($queryString, 1);
    }

    /**
     * Build the ExtData XML string
     *
     * @return string
     */
    protected function buildExtData()
    {
        $ext = null;

        if (null !== $this->transaction['TaxAmt']) {
            $ext .= '<TaxAmt>' . $this->transaction['TaxAmt'] . '</TaxAmt>';
        }
        if (null !== $this->transaction['CustomerID']) {
            $ext .= '<CustomerID>' . $this->transaction['CustomerID'] . '</CustomerID>';
        }
        if (null !== $this->transaction['PONum']) {
            $ext .= '<PONum>' . $this->transaction['PONum'] . '</PONum>';
        }
        if ((null !== $this->transaction['FNameOnCard']) ||
            (null !== $this->transaction['LNameOnCard']) ||
            (null !== $this->transaction['Street']) ||
            (null !== $this->transaction['City']) ||
            (null !== $this->transaction['State']) ||
            (null !== $this->transaction['Zip']) ||
            (null !== $this->transaction['Country']) ||
            (null !== $this->transaction['Email']) ||
            (null !== $this->transaction['Phone']) ||
            (null !== $this->transaction['Fax'])) {
            $ext .= '<Invoice><BillTo>';
            if (null !== $this->transaction['CustomerID']) {
                $ext .= '<CustomerID>' . $this->transaction['CustomerID'] . '</CustomerID>';
            }
            if ((null !== $this->transaction['FNameOnCard']) || (null !== $this->transaction['LNameOnCard'])) {
                $ext .= '<Name>' . $this->transaction['FNameOnCard'] . ' ' . $this->transaction['LNameOnCard'] . '</Name>';
            }
            $ext .= '<Address>';
            $ext .= '<Street>' . $this->transaction['Street'] . '</Street>';
            $ext .= '<City>' . $this->transaction['City'] . '</City>';
            $ext .= '<State>' . $this->transaction['State'] . '</State>';
            $ext .= '<Zip>' . $this->transaction['Zip'] . '</Zip>';
            $ext .= '<Country>' . $this->transaction['Country'] . '</Country>';
            $ext .= '</Address>';
            if (null !== $this->transaction['Email']) {
                $ext .= '<Email>' . $this->transaction['Email'] . '</Email>';
            }
            if (null !== $this->transaction['Phone']) {
                $ext .= '<Phone>' . $this->transaction['Phone'] . '</Phone>';
            }
            if (null !== $this->transaction['Fax']) {
                $ext .= '<Fax>' . $this->transaction['Fax'] . '</Fax>';
            }
            if (null !== $this->transaction['PONum']) {
                $ext .= '<PONum>' . $this->transaction['PONum'] . '</PONum>';
            }
            $ext .= '</BillTo></Invoice>';
        }
        return $ext;
    }

    /**
     * Parse the response codes
     *
     * @return void
     */
    protected function parseResponseCodes()
    {
        $responseCodes = new \SimpleXMLElement($this->response);
        return (array)$responseCodes;
    }

}





