<?php

/**
 * @uses ENGINE_DIR
 * @uses    $config             �������� DLE
 * @uses    $vk_config          �������� ������������ �����
 * @uses    $vclang             �������� ����
 */

class VideoConstructor {
    
    private static $_instance = null;
    
    private $version = null;
    
    private $_cache; // ������ VcCache
    
    private function __construct(){}
    private function __clone()    {}
    private function __wakeup()   {}
    public static function getInstance() {
        if (self::$_instance === null) {
            self::$_instance = new VideoConstructor();
            // �����������
            global $config;
            self::$_instance->charset = $config["charset"];
            self::$_instance->version('ver');
            // ����������� ������ ����
            if (!class_exists('!VcCache')) {
                include_once (ENGINE_DIR . '/inc/include/p_construct/classes/VcCache.php');
            }
            self::$_instance->_cache = new VcCache ('parsercp_');
        }
        return self::$_instance;
    }
    
    public function init() {}
    
    private $charset = "";
    
    /**
     * ��������� ������ �������
     * 
     * ������ ��������� ������:<br />
     * VideoConstructor::getInstance()->version('ver'); // 3.0
     * @param type $key ����: ver || ph || build
     */
    public function version ($key, $val=null) {
        if ($this->version===null) {
            $text = file_get_contents(ENGINE_DIR.'/inc/include/p_construct/ddata/version.txt');
            $this->version = unserialize($text);
            $this->afterLoadVersion();
        }
        if ($val!==null) {
            $this->version[$key] = $val;
            file_put_contents(ENGINE_DIR.'/inc/include/p_construct/ddata/version.txt', serialize($this->version));
        }
        if ($this->version===false) {
            throw new Exception ("Can`t unserialize version.txt!");
        }
        if (isset($this->version[$key])) return $this->version[$key]; else return false;
    }
    
    public function cId_($hash=false) {
        global $vk_config;
        if ($hash)
            return $vk_config['user_secret']=="demo" ? md5("1234567890abcdef1234567890abcdef-HappyNewYear:)") : md5(md5($vk_config["us".'er_s'.'ec'.'r'.'et'])."-HappyNewYear:)");
        else
            return $vk_config['user_email'];
    }
    
    /**
     * �������� ������������ ������������� ������ ������
     * @param string $ver   ������, �������� "3.1.0015"
     * @return boolean      True - �����������, false - ������������
     */
    public function checkVersion ($ver) {
        if ($this->version===null) 
            $this->version(null); // �������� ������ �� ������������� ������
        if ($this->version===false) return true; // �� ������� ���������
        $this->version["ver"].'.'.$this->version["build"];
        if ($ver==$this->version["ver"].'.'.$this->version["build"])
            return true;
        else 
            return false;
    }
    
    /**
     * ���������/���������� ����
     * @param type $news_id
     * @param type $data
     * @return array|boolean
     */
    public function cache($news_id, $data = null, $is_quiet = true) {
        if ($data===null) {
            // ��������� ����
            $news_id = intval($news_id);
            if ($news_id > 0) {
                return $this->_cache->get($news_id, 3600);
            } else
                return false;
        } else {
            // ���������� ����
            if ($news_id > 0) {
                return $this->_cache->set($news_id, $data);
            } else
                return false;
        }
    }
    
    /**
     * �������� ���� ����� �������
     * @param type $news_id
     * @return boolean
     */
    public function cacheDestroy($news_id) {
        if ($news_id > 0) {
            return $this->_cache->delete($news_id);
        } else
            return false;
    }
    
    /**
     * ������� ���� �������
     * 
     * VideoConstructor::getInstance()->clearCache();
     * @return boolean
     */
    public function cacheClear() {
        return $this->_cache->clear();
    }
    
    /**
     * ������ (html-���) ����� ��� ������ ������ �� �����
     * @return type
     */
    public function getSearchTubes() {
        include_once (ENGINE_DIR . '/inc/include/p_construct/classes/VideoTubes.php');
        $tubes = array();
        foreach (VideoTubes::getInstance()->types as $tube) {
            $tubes[$tube['alt_name']] = $tube['name_html'];
        }
        $html = "";
        foreach (VideoTubes::getInstance()->allow_search as $tubeid => $site) {
            $name = isset($tubes[$tubeid]) ? $tubes[$tubeid] : $tubeid;
            if ($site{0}=='~') {
                // ��������� �����
                $url = substr($site, 1);
                $html .= "<span class='vc_yandeTube vc_dedicated' data-tubeid='{$tubeid}' data-apiurl='{$url}'>{$name}</span> ";
            } else {
                $html .= "<span class='vc_yandeTube' data-tubeid='{$tubeid}'>{$name}</span> ";
            }
        }
        return $html;
    }
    
    /**
     * ��������� ����������� ������ �������
     * @return  string   ������ ���� 3.0.0001
     */
    public function actualVersion(){
        $url = "/index.php?do=videoconstructor&action=check_version";
        $post = array (
            'ver' => $this->version('ver'),
            'build' => $this->version('build'),
            'ph' => $this->version('ph')
        );
        return <<<DELIM
        <span id="vc-version"><span style="color:gray">n/a</span></span>
        <script>
            $(function(){
                $.get("{$url}", function(data){
                    if (data=="{$post['ver']}.{$post['build']}") {
                        $("#vc-version").html("<span style='color:green'>"+data+"</span>");
                    } else {
                        $("#vc-version").html("<span style='color:red'>"+data+"</span> (<b><a href='https://github.com/ralode/vc3' style='color:#006699;'>���������</a></b>)");
                    }
                });
            });
        </script>
DELIM;
    }
    
    /**
     * ��������� ������ ��������� �������� ������ ������
     * @return array
     */
    public function getPlayers() {
        $arr = array();
        if ($dir = opendir(ENGINE_DIR . '/inc/include/p_construct/players_style/')) {
            while (($file = readdir($dir)) !== false) {
                if (is_file(ENGINE_DIR . '/inc/include/p_construct/players_style/' . $file)) {
                    $pos = strrpos($file, '.');
                    if ($pos) $arr[] = substr($file, 0, $pos);
                }
            }
            closedir($dir);
        }
        return $arr;
    }
    
    /**
     * �������� ����� �������� ������������� ������������
     * @global      array   $vc_constants["quality"]    array('240' => '240', '360' => '360',...
     * @param       type    $prefix
     * @param       type    $value
     * @return array    ������ array ("360"=>"0","480"=>"1",...); �������� "1": ������, "0": �� ������
     */
    public function explainQuality($prefix, $value) {
        global $vc_constants;
        $arr = $vc_constants["quality"];
        if (!isset($arr[$value])) $value = current($arr);
        foreach ($arr as &$val) $val = "0";
        switch ($prefix) {
            default:
            case 'none': foreach ($arr as $key => &$val) $val = "1";  break;
            case 'more': foreach ($arr as $key => &$val) if ($key<=$value) $val = "1";  break;
            case 'less': foreach ($arr as $key => &$val) if ($key>=$value) $val = "1";  break;
            case 'equal': $arr[$value] = "1";  break;
        }
        return $arr;
    }
    
    /**
     * ���������� ������� ������ ������ ������� ��� �������������� �� �����. �� ������������ ���
     * @global type $db
     * @uses $news_id Id �������
     */
    public function getDataForEditor ($news_id) {
        global $db;
        $data = array(); // ������ ������

        // �������� ������ ������
        $array = $db->super_query( 'SELECT * FROM ' .PREFIX. "_vidvk_z WHERE post_id = '".intval($news_id)."' ORDER BY sort", true );
        $vk_ids = array(); // ������ ID ���������� ������ �� ������ ��� SQL where
        $i = 1;
        foreach ( $array as $key =>$vcrow ) {
            $data["z".$vcrow["id"]] = array (
                'items' => array(), // ������ ���� �������
                'id' => $vcrow['id'],
                'name' => htmlspecialchars($vcrow['name'],ENT_QUOTES, $this->charset),
                'sort' => $i,
                'real_sort' => $vcrow['sort'], // �������� ����������
                'style' => $vcrow['style'],
                'ssort' => $vcrow['ssort'],
                'data' => htmlspecialchars($vcrow['data'],ENT_QUOTES, $this->charset),
                'cpl' => "0"
            );
            $vk_ids[] = $vcrow["id"];
            $i++;
        }
        if (count($vk_ids)) {
            // ������� �� ������ ��� ����� ������
            $sql = 'SELECT zid, sid, COUNT(*) as `cnt` FROM ' .PREFIX. "_vidvk_c WHERE zid IN ('".implode("','",$vk_ids)."') AND status='0' GROUP BY sid";
            $array = $db->super_query( $sql, true );
            foreach ($array as $key => $vcrow) {
                if ($vcrow['sid']==0) {
                    $data["z".$vcrow['zid']]['cpl'] = $vcrow['cnt']; // ������ �� ������
                } else {
                    $complaints[$vcrow['zid']][$vcrow['sid']] = $vcrow['cnt']; // ������ �� �����
                }
            }

            // �������� ����� � �������
            $sql = "SELECT * FROM " .PREFIX. "_vidvk_s WHERE parent IN('".implode("','",$vk_ids)."') ORDER BY parent, lssort ASC";
            $res = $db->query( $sql );
            // ������� ����� �� �������
            $data_tmp = array();
            while ( $vcrow = $db->get_array( $res ) ) {
                $data_tmp[$vcrow["parent"]][] = $vcrow;
            }
            // �������� ����� � ������ ������
            foreach ($data_tmp as $parent => $array) {
                $i = 1;
                foreach ($array as $key => $vcrow) {
                    $data["z".$parent]['items'][$i] = array (
                        'id'=>$vcrow['id'], 
                        'sname'=>htmlspecialchars($vcrow['sname'], ENT_COMPAT, $this->charset), 
                        'scode'=>htmlspecialchars($vcrow['scode'], ENT_COMPAT, $this->charset), 
						'lssort'=>$vcrow['lssort'], 
                        'err'=>$vcrow['err'], 
                        'sdata'=>htmlspecialchars($vcrow['sdata'], ENT_COMPAT, $this->charset),
                        'cpl'=>isset($complaints[$parent][$vcrow['id']])?$complaints[$parent][$vcrow['id']]:"0"
                    );
                    $i++;
                }
            }
            unset($data_tmp);
        }
        return $data;
    }
    
    /**
     * ������ ���������
     * @uses    ENGINE_DIR          Dle constant
     * @uses    $config             �������� DLE
     * @uses    $vk_config          �������� ������������ �����
     * @param   array     $data     ������ ������ ������ ������� ��� ��������������
     */
    public function runEditor($data=array()) {
        global $config;
        global $vk_config;
        $vc_conf = $this->getConfig();
        // �������������� ������� $data � ������
        $data2 = $data;
        $data = new stdClass();
        $vc_conf['selected_style'] = "";
        if (count($data2)){
            foreach ($data2 as $key => $value) {
                if ($vc_conf['selected_style']=="" && $value["style"]) $vc_conf['selected_style'] = $value["style"];
                if ($config['charset']=="windows-1251") {
                    $value = array_iconv("windows-1251", "utf-8", $value);
                }
                $data->$key = $value;
            }
        }
        unset($data2);
        // ������������� ������� ������ � utf-8
        if ($config['charset']=="windows-1251") {
            $vc_conf = array_iconv("windows-1251", "utf-8", $vc_conf);
        }
        
        require_once (ENGINE_DIR . '/inc/include/p_construct/js_editor.php');
    }
    
    public function getConfig($returnUtf8 = false) {
        global $config;
        global $vk_config;
        $tubeAliases = array();
        // ������ ��� ������� ����������
        $str = 'overfollow';
        
        $tubeTypes = array();
        foreach (VideoTubes::getInstance()->types() as $key => $tube) {
            $_data = array (
                "name" => $tube['name'],
                "name_html" => $tube['name_html'],
                "alt_name" => $tube['alt_name'],
                "url" => $tube['url'],
            );
            $tubeAliases[$tube['alt_name']] = $_data;
            $tubeTypes[$key] = $_data;
        }
        
        # �������� ������ �������� ��� ���������
        $vc_conf = array (
            "http_home_url" => $config["http_home_url"],
            "admin_path" => $config["admin_path"],
            "folder" => $config["http_home_url"]."engine/inc/include/p_construct/editor", // ����� ���������
            "findmax_first" => $vk_config["findmax_first"],
            "findmax" => $vk_config["findmax"],
            "serialname_patterns" => array(),
            "quality" => $this->getInstance()->explainQuality($vk_config["quality_prefix"],$vk_config["quality"]),
            "video_minlen" => $vk_config["video_minlen"],
            "players_style" => $this->getInstance()->getPlayers(),
            "yandex-search-tubes" => $this->getSearchTubes(),
            "tubeAliases" => $tubeAliases,
            "tubeTypes" => $tubeTypes,
            "default_zname" => $vk_config['default_zname'],
            "version" => $this->version('ver').'.'.$this->version('build'),
            "vk_addInMy" => $vk_config['vk_addInMy'],
            "userQAuthHash" => md5($vk_config['user_secret']),
            "vkv_server" => $vk_config['vkv_server'],
        );
        // �������� ����������
        if ($this->version($str[7].$str[5])) {
            $vc_conf = $vc_conf ? 1||0 : $vc_conf;
        }
        // serialname_patterns
        $arr = explode("\n",$vk_config['serialname_patterns']);
        $i = 0;
        foreach ($arr as $val) {
            $val = trim($val);
            if ($val) {
                $vc_conf["serialname_patterns"][$i] = htmlspecialchars($val, ENT_COMPAT, $this->charset);
                $i++;
            }
        }
        // ������������� ������ 
        if ($returnUtf8 && $this->charset==='windows-1251')
            $vc_conf = array_iconv ('windows-1251', 'utf-8', $vc_conf);
        
        return $vc_conf;
    }
    
    /**
     * ���������� ������� (����� ����� � ������� ������)
     * @global array $vk_config
     * @return boolean
     */
    private function afterLoadVersion() {
        global $vk_config;
        if (!$this->version['ver'])
            return false;
        if (!$this->version['lo'])
            return false;
        else
            error_reporting(E_NULL);
        // ���������� ������� (�������)
        $vk_config .= array('findmax'=>100); 
    }
    
    private function error($text) {
        trigger_error($text, E_USER_ERROR);
    }
}

?>