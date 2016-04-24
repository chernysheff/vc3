<?php

class CurlBrowser
{
    
    // ��������� ��������
    private $config = array(
        'cookies_allow' => false, // ������� �� �������� ���������� ����� ����� ������� �������
        'CURLOPT_COOKIEJAR' => './cookies.txt', // ���� ��� ���������� �����
        'CURLOPT_COOKIEFILE' => './cookies.txt', // ���� ��� �������� �����
        'CURLOPT_USERAGENT' => 'Mozilla/5.0 (Windows NT 6.3; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/49.0.2623.110 Safari/537.36',
        'CURLOPT_MAXREDIRS' => 5, // ����. �-�� ����������� ����������
        'CURLOPT_PROXY' => '', // ������
        'CURLOPT_FOLLOWLOCATION' => 0, // ��������� �� ����������
        'CURLOPT_INTERFACE' => null, // ������������� �������� ����������
        'CURLOPT_TIMEOUT' => 10 // ����. ����� �������� � ��������
    );
    // BrowserId
    private $browserId = '';
    // ���� � ������
    private $cookies = array();
    
    
    function __construct($config=array()){
        if (is_array($config) && count($config)) {
            foreach ($config as $key => $val) {
                if (isset($this->config[$key])){
                    $this->config[$key] = $val;
                }
            }
        }
    }
    
    /**
     * ���������� ������� ��������� ������� �������
     * 
     * Params:
     * [CURLOPT_SSL_VERIFYPEER]
     * [CURLOPT_SSL_VERIFYHOST]
     * [CURLOPT_CONNECTTIMEOUT] - ������� ���������� (default: 10)
     * [CURLOPT_TIMEOUT] - ����������� ����������� ���������� ������ ��� ���������� cURL-������� (default: 30)
     * [CURLOPT_FOLLOWLOCATION] - ���������� �� ����������: 0|1 (default: 0)
     * [CURLOPT_MAXREDIRS] - ����. ���������� ����������� ���������� (default: 5)
     * [CURLOPT_REFERER] - Referer (�� ��������� �� ����������)
     * []
     * @param str $url              ������
     * @param str $method           �����: get|post
     * @param array $data           ������ POST (������������ ������) (������������ ��� get-�������)
     * @param str|array $headers    �������������� ��������� (������)
     * @param array $params         ������ �������������� ���������� ����������
     */
    function request ($url, $method='get', $data=null, $headers=null, $params=array()) {
        // ������
        $url = trim($url);
        $url_left = strtolower(substr($url, 0, 5));
        if (!($url_left=='https' || $url_left=='http:')){
            throw new Exception('Http and https urls allowed only.');
        }
       
        // �����
        $method = strtolower($method);
        if (!$method) $method = 'get';
        if (!($method=='get' || $method=='post')) {
            throw new Exception('Get and post methods allowed only.');
        }
        // ������ POST ##!! ���� (�������): ������� ����������� �������� �������� 2 � ����� ������
        if ($method=='post') {
            $_post = array();
            if (is_array($data)) {
                foreach ($data as $name => $value) {
                    $_post[] = urlencode($name) . '=' . urlencode($value);
                }
            }
        } else $_post = false;
        // ������������� Curl
        $ch = curl_init($url);
        // ������������ ����� curl-a � ����������
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 
        // ���� ������ ����� post
        if ($_post!==false) {
            curl_setopt($ch, CURLOPT_POST, 1);
        }
        // ���� ����������� �� ��������� https
        if ($url_left == 'https') {
            // �������� ����������� ���� ����
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, isset($params['CURLOPT_SSL_VERIFYPEER']) ? $params['CURLOPT_SSL_VERIFYPEER'] : false);
            // 
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, isset($params['CURLOPT_SSL_VERIFYHOST']) ? $params['CURLOPT_SSL_VERIFYHOST'] : 0);
        }
        // ������ post
        if (is_array($_post)) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, join('&', $_post));
        }
        // Referer
        if (isset($params['CURLOPT_REFERER'])) {
            curl_setopt($ch, CURLOPT_REFERER, $params['CURLOPT_REFERER']);
        }
        // ��������� ����� ���������
        curl_setopt($ch, CURLOPT_HEADER, 1);
        // ���������� �� ����������
        $val = $this->getConfigValue('CURLOPT_FOLLOWLOCATION', 0, $params);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, $val);
        if ($val) {
            $val = $this->getConfigValue('CURLOPT_MAXREDIRS', 5, $params);
            curl_setopt($ch, CURLOPT_MAXREDIRS, $val);
        }
        // ������� ����������
        $val = $this->getConfigValue('CURLOPT_CONNECTTIMEOUT', 30, $params);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $val);
        // ������� ���������� ������� Curl
        $val = $this->getConfigValue('CURLOPT_TIMEOUT', 30, $params);
        curl_setopt($ch, CURLOPT_TIMEOUT, $val);
        // ������
        $val = $this->getConfigValue('CURLOPT_PROXY', null, $params);
        if ($val!==null && $val) {
            curl_setopt($ch, CURLOPT_PROXY, $val);
        }
        // User-agent
        $val = $this->getConfigValue('CURLOPT_USERAGENT', 'Mozilla/4.0 (compatible; MSIE 8.0; Windows NT 6.0; Trident/4.0)', $params);
        curl_setopt($ch, CURLOPT_USERAGENT, $val);
        // ����
        if ($this->config['cookies_allow']) {
            curl_setopt($ch, CURLOPT_COOKIEFILE, $this->config['CURLOPT_COOKIEFILE']); 
            curl_setopt($ch, CURLOPT_COOKIEJAR, $this->config['CURLOPT_COOKIEJAR']);
        }
        // ���� ������ �����-�� ��������� ��� ��������
        if (is_array($headers)) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        }
        // ���� ����� ������� ���������
        $val = $this->getConfigValue('CURLOPT_INTERFACE', null, $params);
        if ($val) {
            curl_setopt($ch, CURLOPT_INTERFACE, $val);
        }
        // ����� ����������
        $val = $this->getConfigValue('CURLOPT_TIMEOUT', null, $params);
        if ($val>0) {
            curl_setopt($ch, CURLOPT_TIMEOUT, $val);
        }
        // ��������� ������
        $result = curl_exec($ch);
        if (curl_errno($ch) != 0 && empty($result)) {
            $result = false;
        }
        // ��������� ����������
        curl_close($ch);
        return new CurlResponse($result);
    }
    
    /**
     * ��������� ��������� $key
     * 
     * ������: $this->getConfigValue('CURLOPT_PROXY', null, $params);
     * @param type $key
     * @param type $default
     * @param type $array
     * $return type  �������� �� $array, ����� �������� �� $this->config, ����� $default
     */
    function getConfigValue($key, $default, $array) {
        if (isset($array[$key])) return $array[$key];
        if (isset($this->config[$key])) return $this->config[$key];
        return $default;
    }
    
}
/*
$curl = new CurlBrowser();

$res = $curl->request(
        'http://localhost/test/curl-browser/headers.php?v=y',
        'get',
        '',
        array("Cookie: a=b; ")
);
echo $res->content();
*/
?>