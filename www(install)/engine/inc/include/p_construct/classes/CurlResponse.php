<?php
/**
 * ����� Curl
 */

class CurlResponse
{
    private $_headers = null; // ��������� ������
    private $_content = null; // ���� ������
    private $_cookies; // �������� �������� ���� (������)
    
    public $http_status; // ��� ������ HTTP
    public $charset; // ���������
    
    /**
     * �����������
     * @param type $response    ����� ������ � �����������
     */
    function __construct($response) {
        // ������� ����� HTTP/1.1 100 Continue? ���� ����
        if (substr($response,0,25)=="HTTP/1.1 100 Continue\r\n\r\n") $response = substr($response,25);
        
        $pos = strpos($response, "\r\n\r\n");
	if ($pos!==false) {
            // ���������
            $this->_headers = substr ($response, 0, $pos);
            // ����
            $this->_content = substr ($response, $pos+4);
            unset($response);
            // ����� �������
            if (preg_match("/HTTP[^ ]+ ([0-9]+)[^\r\n]+/", $this->_headers, $arr)){
                $this->http_status = $arr[1];
            }
            // ����
            preg_match_all ("/Set-Cookie:([^\r\n]+)/", $this->_headers, $arr);
            $this->_cookies = array();
            if (count($arr[1])){
                foreach ($arr[1] as $value) {
                    $array = explode(';',trim($value));
                    $cookie = array();
                    $first = true;
                    foreach($array as $val2) {
                        $val2 = trim($val2);
                        $pos = strpos($val2,'=');
                        if ($first) {
                            $cookie['key'] = urldecode(substr($val2,0,$pos));
                            $cookie['value'] = urldecode(substr($val2,$pos+1));
                        } else {
                            $cookie[substr($val2,0,$pos)] = substr($val2,$pos+1);
                        }
                        $first = false;
                    }
                    if (isset($cookie['key'])) {
                        if (isset($cookie['expires']))
                            $cookie['expires'] = strtotime($cookie['expires']);
                            $this->_cookies[] = $cookie;
                    }
                };
            }
            // ���������
            if (preg_match("/charset=([A-Za-z0-9-]+)/", $this->_headers, $arr)) {
                $this->charset = strtolower($arr[1]);
            }
	}
    }
    
    /**
     * �������� ���������� �� �����
     * @return boolean
     */
    function check() {
        if ($this->_headers!==null || $this->_content!==null)
            return true;
        else 
            return false;
    }
    
    /**
     * �������� ������ ����������
     * @return type
     */
    function headers() {
        return $this->_headers;
    }
    
    /**
     * �������� ������ ����������
     * @return type
     */
    function headersArray() {
        $h = explode("\r\n", $this->_headers);
        $headers = array();
        foreach ($h as $k => $v) {
            if (strpos($v, ':')) {
                $k = substr($v, 0, strpos($v, ':'));
                $v = trim(substr($v, strpos($v, ':') + 1));
            }
            $headers[$k] = $v;
        }
        return $headers;
    }
    
    /**
     * �������� ������� ������
     * @return type
     */
    function content() {
        return $this->_content;
    }
    
    /**
     * �������� ������ ����� ������
     * @return type
     */
    function cookies() {
        return $this->_cookies;
    }
    
    /**
     * �������� ������ �����
     * @return string   ������ ����: key1=val1; key2=val2; ...
     */
    function cookiesLine() {
        $cookies_line = '';
        if (count($this->_cookies)){
            foreach ($this->_cookies as $cookie) {
                $cookies_line.= $cookie['key'].'='.$cookie['value'].'; ';
            }
        }
        return $cookies_line;
    }
}

?>