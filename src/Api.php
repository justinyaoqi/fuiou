<?php
namespace Yaoqi\FuYou;

use Yaoqi\FuYou\Support\Log;
use Yaoqi\FuYou\Support\Signature;

class Api
{
    //加密类型
    const SIGNATURE_TYPE_SHA256 = 'SHA256';
    const SIGNATURE_TYPE_HMAC = 'HMAC';
    const SIGNATURE_TYPE_MD5 = 'MD5';
    const SIGNATURE_TYPE_RSA = 'RSA';

    const PAYMENT_WECHAT = 'WECHAT';//微信 扫码
    const PAYMENT_H5_WECHAT = 'JSAPI';//微信公众号H5
    const PAYMENT_WXAPP_WECHAT = 'LETPAY';//微信小程序
    const PAYMENT_ALIPAY = 'ALIPAY';//支付宝 扫码
    const PAYMENT_H5_ALIPAY = 'FWC';//支付宝 服务窗H5

    const PAYMENT_QQ = 'QQ';//QQ钱包
    const PAYMENT_UNIONPAY = 'UNIONPAY';//银联支付
    const PAYMENT_BESTPAY = 'BESTPAY';//中国电信（翼支付）

    public $organizationNumber;//机构号
    public $signature;//签名方式 当次请求的签名方式，对应SIGNATURE_TYPE_XX常量
    public $signatureKey;//签名密钥
    public $signaturePublicKey;//非对称加密解密验证秘钥
    public $method;//请求方式
    public $interfaceAddress;//请求地址
    public $merchantNumber;//商户号
    public $apiVersion;//api接口版本号

    /**
     * Api constructor.
     * @param $config
     * @param string|null $interfaceAddress
     * @param string|null $method
     * @param string|null $signatureKey
     * @param string|null $signature
     * @param null $signaturePublicKey
     * @param string|null $organizationNumber
     * @param string|null $merchantNumber
     * @param string|null $apiVersion
     */
    public function __construct($config, $interfaceAddress = null, $method = null, $signatureKey = null, $signature = null, $signaturePublicKey = null, $organizationNumber = null, $merchantNumber = null, $apiVersion = null)
    {
        if (is_array($config)) {
            extract($config, EXTR_OVERWRITE);
            $this->interfaceAddress = $interfaceAddress;
            $this->method = $method;
            $this->signatureKey = $signatureKey;
            $this->signaturePublicKey = $signaturePublicKey;
            $this->signature = $signature;
            $this->organizationNumber = $organizationNumber;
            $this->merchantNumber = $merchantNumber;
            $this->apiVersion = $apiVersion;
        }
    }

    /**
     * @param $data string|array
     * @param bool $verify 是否为验证签名
     * @return bool|string
     */
    function sign($data, $verify = false)
    {

        $dataStr = '';
        if (is_array($data)) {
            ksort($data);
            foreach ($data as $key => $val) {
                if ($key === 'sign' || substr($key, 0, strlen('reserved')) === 'reserved') {
                    continue;
                }
                if (is_array($val) && empty($val))
                    $val = '';
                $dataStr .= '&' . $key . '=' . $val;
            }
        } elseif (is_string($data)) {
            $dataStr = $data;
            parse_str($dataStr, $data);
        } else {
            return false;
        }
        $dataStr = ltrim($dataStr, '&');
        if (strtoupper($this->signature) == self::SIGNATURE_TYPE_RSA) {
            if ($verify) {
                if (!isset($data['sign'])) {
                    Log::info("签名验证时未发现sign验证字段", $data);
                    return false;
                }
                return Signature::verify(iconv('UTF-8', 'GBK//IGNORE', $dataStr), $data['sign'], $this->signaturePublicKey, true);
            }
            return Signature::signature($dataStr, $this->signatureKey);
        }

        if (strtoupper($this->signature) == self::SIGNATURE_TYPE_MD5) {
            return md5($dataStr);
        }

        return false;
    }


}