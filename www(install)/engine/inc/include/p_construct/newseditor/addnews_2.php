<?php

require_once (ENGINE_DIR . '/inc/include/p_construct/config.php');
require_once (ENGINE_DIR . '/inc/include/p_construct/constants.php');
require_once (ENGINE_DIR . '/inc/include/p_construct/classes/vkvideolib.php');
// �������� ��������� JSON ��� PHP<5.2.0
if (!is_callable("json_encode")) {
    include_once (ENGINE_DIR . '/inc/include/p_construct/classes/JSON.php');
}
require_once (ENGINE_DIR . '/inc/include/p_construct/classes/VideoConstructor.php');
require_once (ENGINE_DIR . '/inc/include/p_construct/classes/VideoTubes.php');
include_once (ENGINE_DIR . '/inc/include/p_construct/classes/VcExtensionConfig.php');
include_once (ENGINE_DIR . '/inc/include/p_construct/classes/VcExtension.php');
VcExtension::getInstance()->init();


// ����� ����������� �������
$news_id = intval($row);
$post_id = isset($_POST['id']) ? intval($_POST['id']) : 0;
$curr_news_id = $news_id>0 ? $news_id : $post_id;

$vc_z = array(); // ������ ��� ����������

$vc_players = VideoConstructor::getInstance()->getPlayers(); // ������ ��������� �������� ������
$vc_player_style = null; // ����� ������
$vc_errors = array(); // ������ ��� ����������

if ( ($news_id>0 || $post_id>0) && $_POST['xfield']['pconstruct']) {
    
    // Magic quotes
    if (get_magic_quotes_gpc()) {
        $_POST['xfield']['pconstruct'] = stripslashes($_POST['xfield']['pconstruct']);
    }
    
    // ������������� � utf-8
    if ($config["charset"]!="utf-8") {
        $_POST['xfield']['pconstruct'] = iconv("windows-1251", "utf-8", $_POST['xfield']['pconstruct']);
    }
    // ������� JSON
    $data = json_decode ($_POST['xfield']['pconstruct'], true);
    // ������������� � windows-1251
    if ($config['charset']=="windows-1251")
        $data = array_iconv("utf-8", "windows-1251", $data);
    //print_r ($data); exit;
    if (count($data)) {
        $vc_real_sort = array();
        foreach ($data as $zid => $arr) {
            // ����������� - ���������� ����� ������ ��� �������������� ������
            if ($arr['id']{0}=='N') {
                //$arr['id'] = substr($arr['id'],1);
                $isNew = true; // ���������� �����
            } else $isNew = false; // �������������� ������
            
            if (count($arr["items"])) {
                
                if ($arr["name"]=="") {
                    $arr["name"] = "����� ������";
                } else
                    $arr["name"] = str_replace('&amp;','&',strip_tags($arr["name"]));
                if ($vc_player_style===null && $arr["style"]!="" && in_array($arr["style"],$vc_players)) {
                    $vc_player_style = $arr["style"];
                }
                $vc_real_sort[$arr["sort"]] = ($zid{0}=='N' ? 'z'.$zid : $zid);
                $arr["ssort"] = intval($arr["ssort"]);
                if ($arr["ssort"]<0 || $arr["ssort"]>2) $arr["ssort"] = 2;
                // ������ �� �������
                foreach ($arr['items'] as $num => &$zarray) {
                    if ($zarray['id']{0}=='N') {
                        $zarray['id'] = substr($zarray['id'],1);
                        $zarray['isNew'] = 'Y';
                    } else $zarray['isNew'] = 'N';
                    $zarray['sname'] = htmlspecialchars_decode($zarray['sname']);
                    $zarray['scode'] = htmlspecialchars_decode($zarray['scode']);
                    $zarray['parent_zid'] = $arr['id'];
                }
                
                if ($isNew) {
                    // ���������� ����� ������ � �������
                    $arr['isNew'] = 'Y';
                } else { 
                    // �������������� ����� ������ � �������
                    $arr['isNew'] = 'N';
                }
                $vc_z["z".$arr["id"]] = $arr;
            }
        }
        // ����������
        ksort($vc_real_sort);
        $vc_real_sort_assoc = array_flip ($vc_real_sort);
        if (count($vc_real_sort_assoc)) {
            $i = 0;
            foreach($vc_real_sort_assoc as $key => &$val) 
                $val = ++$i;
        }
        unset($vc_real_sort);
        
    }
    unset($data);
    
    //print_r($vc_z); exit;
    // �������������� ������ � ����
    $prev_series = array(); // ������ (� ������ - sid) ���������� �����
    if ($post_id>0) {
        $prev_data = VideoConstructor::getInstance()->getDataForEditor($post_id);
        if (count($prev_data)) {
            foreach ($prev_data as $z_idm => $z) {
                if (count($z['items'])) {
                    foreach ($z['items'] as $num => $s) {
                        if ($s["id"]) {
                            $prev_series[$s["id"]] = 1;
                        }
                        // ��������� ���� [parent_zid]
                        $prev_data[$z_idm]['items'][$num]['parent_zid'] = $z['id'];
                    }
                }
            }
        }
    } else {
        $prev_data = array();
    }
    //print_r ($prev_data); exit;
	// ���������� ������� �� ��������� ������������� 
	uasort($vc_z, "z_compare_sortf");
    //print_r ($vc_z); exit;
    //print_r ($vc_real_sort_assoc); exit;
	
	// ���������� ��������
	$sql_info = array("z_insert"=>0, "s_insert"=>0, "s_delete"=>0, "z_update"=>0, "z_update"=>0, "z_delete"=>0);

    // ���������� ������� � ����
    if (count($vc_z)) {
		$z_num = 1;
        foreach ($vc_z as $z_idm => $z) { // $z_idm = "z".{zid};
            $zid = intval($z['id']);
            if ($z['isNew']=='Y') {
                // ���������� ������
                //print_r ($vc_real_sort_assoc); echo "[$z_idm]"; exit;
                $sql = 'INSERT INTO ' .PREFIX. "_vidvk_z (name, post_id, sort, style, ssort, data) VALUES ('".$db->safesql($z['name'])."','{$curr_news_id}','".$z_num."','".$db->safesql($z['style'])."','".$db->safesql($z['ssort'])."','')";
                //echo "$sql\n\n";
				$sql_info["z_insert"]++;
                $db->query($sql);
                $zid = $db->insert_id();
            } else { 
                // �������������� ������
                if ($prev_data[$z_idm]['name']!=$z['name'] ||
                $prev_data[$z_idm]['sort']!=$z_num ||
                $prev_data[$z_idm]['style']!=$z['style'] ||
                $prev_data[$z_idm]['real_sort']!=$vc_real_sort_assoc[$z_idm] ||
                $prev_data[$z_idm]['ssort']!=$z['ssort']) 
                {
                    // ����������
                    $sql = 'UPDATE ' .PREFIX. "_vidvk_z SET name='".$db->safesql($z['name'])."',sort='".$z_num."',style='".$db->safesql($z['style'])."',ssort='".$db->safesql($z['ssort'])."' WHERE id='{$zid}' AND post_id='{$curr_news_id}'";
                    //echo "$sql\n";
					$sql_info["z_update"]++;
                    $db->query($sql);
                }
            }
            // ����������/�������������� ����� � �������
            if (count($z['items'])) {
                $s_num = 1;
                foreach ($z['items'] as $num => $s) {
                    if ($num) {
                        //print_r ($s); exit;
                        $codetype = VideoTubes::getInstance()->getTube($s['scode']);
                        $s['scode'] = VideoTubes::getInstance()->code; // ������������ ���
                        //print_r ($s); //exit('Q');
                        if ($s['isNew']=='Y') {
                            // ����������
                            $sql = 'INSERT INTO ' .PREFIX. "_vidvk_s (parent, sname, scode, lssort, err, codetype, sdata) VALUES ('".intval($zid)."', '".$db->safesql($s['sname'])."', '".$db->safesql($s['scode'])."', '".intval($s_num)."', '0', '".intval($codetype)."', '')";
                            //echo "$sql\n\n";
							$sql_info["s_insert"]++;
                            $db->query($sql);
                        } else {
                            // ��������������
                            $row_prev = $prev_data[$z_idm]['items'][$num];
                            //echo '< PREV:'.$zid.': '; print_r($row_prev);  echo '> ';
                            //echo '< CURR:'.$zid.': '; print_r($s);  echo '> ';
                            if ((isset($s["leave_empty"]) && $s["leave_empty"]) || $row_prev['sname']!=$s['sname'] || $row_prev['scode']!=htmlspecialchars ($s['scode'],ENT_QUOTES) || $row_prev['lssort']!=$s_num || $row_prev['parent_zid']!=$s['parent_zid']){
                                $sql = 'UPDATE ' .PREFIX. "_vidvk_s SET sname='".$db->safesql($s['sname'])."', scode='".$db->safesql($s['scode'])."', lssort='".intval($s_num)."', codetype='".intval($codetype)."', parent='".intval($s['parent_zid'])."' WHERE id = '".intval($s['id'])."'";
                                //echo "$sql\n\n";
								$sql_info["s_update"]++;
                                $res = $db->query($sql);
                            }
                            if (isset($prev_series[$s["id"]])) 
                                unset($prev_series[$s["id"]]);
                        }
                        $s_num++;
                    }
                }
            }
			$z_num++;
        } 
        // �������� ��������� ������
        if (count($prev_data)) {
            foreach ($prev_data as $z_idm => $z) {
                if (!isset($vc_z[$z_idm])) {
                    $zid = intval($z['id']);
                    $sql = 'DELETE FROM `' .PREFIX. "_vidvk_z` WHERE id='{$zid}' AND post_id='{$curr_news_id}'";
                    //echo "$sql\n\n";
					$sql_info["z_delete"]++;
                    $db->query($sql);
                }
            }
        }
        // �������� ��������� �����
        if (count($prev_series)) { // ���������� �����
            $prev_series = array_keys($prev_series);
            $prev_series = array_map ("intval", $prev_series);
            $list = implode("','", $prev_series);
            $sql = 'DELETE FROM `' .PREFIX. "_vidvk_s` WHERE id IN ('".$list."')";
            //echo "$sql\n\n";
			$sql_info["s_delete"]++;
            $db->query($sql);
        }

		if ($vk_config["is_debug"]) echo "���������� �������� (����� �������): ".print_r($sql_info, true) . ".";
        
    } else {
        // ������� ���� ���������� ����� � ������, ���� ���� ������� ���
        $del_z = array();
        $del_s = array();
        if (is_array($prev_data)) {
            foreach ($prev_data as $z) {
                $zid = intval($z["id"]);
                if ($zid>0) {
                    $del_z[] = $zid;
                }
                if (is_array($z["items"])) {
                    foreach ($z["items"] as $s) {
                        $sid = intval($s["id"]);
                        if ($sid>0) 
                            $del_s[] = $sid;
                    }
                }
            }
        }
        if (count($del_z)>0) {
            $sql = 'DELETE FROM `' .PREFIX. "_vidvk_z` WHERE `id` IN ('".implode("','",$del_z)."')";
            //echo "$sql\n\n";
            $db->query($sql);
        }
        if (count($del_s)>0) {
            $sql = 'DELETE FROM `' .PREFIX. "_vidvk_s` WHERE `id` IN ('".implode("','",$del_s)."')";
            //echo "$sql\n\n";
            $db->query($sql);
        }
    }
    
    // �������� ����
    VideoConstructor::getInstance()->cacheDestroy($curr_news_id);
    
} else 
    if (!isset($_GET['ifdelete'])) echo ('������ � ���. ���� pconstruct.');

// ���������� �������� - ���������� ����������� ��������
VcExtension::getInstance()->inc("form-2.php","Catalog");

?>