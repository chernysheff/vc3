<?php

/**
 * ������: ����������� ����� v3.x ��� DLE
 * ���������� �����: ������� ������ � ������ �������
 * @author SeregaL <SeregaL2009@yandex.ru>
 * 
 * ������ ������������ ���������� ���: {%loader(EMAIL)%}
 */

include_once (ENGINE_DIR . '/inc/include/p_construct/config.php');
include_once (ENGINE_DIR . '/inc/include/p_construct/classes/vkvideolib.php');
include_once (ENGINE_DIR . '/inc/include/p_construct/classes/VcExtensionConfig.php');
include_once (ENGINE_DIR . '/inc/include/p_construct/classes/VcExtension.php');
VcExtension::getInstance()->init();
// ���������� �������� - ����������� ��������
if ($_GET["mod"] === "rational-catalog") VcExtension::getInstance()->inc("Catalog-modconf.php","Catalog");

include_once (ENGINE_DIR . '/inc/include/p_construct/classes/VideoTubes.php');
include_once (ENGINE_DIR . '/inc/include/p_construct/classes/VideoConstructor.php');


$vk_newsid = intval ($_GET['newsid']);
if ($vk_newsid>0) {
    
    // ������ ��������� � ����
    $CvStruct = VideoConstructor::getInstance()->cache($vk_newsid);
    // ���� ��� �� ��������
    if ($CvStruct===false) {
        $CvStruct = array(); // ������������ ����� ���������� �� ��� ��������� � �������. ��������� ��� ���������� ����
        // �������� ������ ������
        $vkfilm_res = $db->query( 'SELECT * FROM ' .PREFIX. "_vidvk_z WHERE post_id = '".$vk_newsid."' ORDER BY sort" );
        $vk_ids = ''; // ������ ID ���������� ������ �� ������ ��� SQL where
        while ( $vcrow = $db->get_row( $vkfilm_res ) ) {
            $CvStruct[$vcrow['id']] = array (
                'items' => array(), // ������ ���� �������
                'id' => $vcrow['id'],
                'name' => $vcrow['name'],
                'sort' => $vcrow['sort'],
                'style' => $vcrow['style'],
                'ssort' => $vcrow['ssort'],
                'data' => $vcrow['data']
            );
            if ($vk_ids=='') $vk_ids = "'$vcrow[id]'"; else $vk_ids .= ',\''.$vcrow['id'].'\'';
        }
        // �������� ������ ����� � ������
        if ($vk_ids!=="") {
            $vkfilm_res = $db->query( 'SELECT * FROM ' .PREFIX. "_vidvk_s WHERE `parent` IN (".$vk_ids.") ORDER BY lssort" );
            $s_num = 1;
            while ( $vcrow = $db->get_row( $vkfilm_res ) ) {
                //print_r($vcrow);
                $CvStruct[$vcrow["parent"]]["items"][$s_num."."] = $vcrow;
                $s_num++;
            }
        }
        // ���������� ����
        VideoConstructor::getInstance()->cache($vk_newsid, $CvStruct);
        
    }
    //print_r($CvStruct); exit;
    
    if (count($CvStruct)>0) {
        // ��������������� ��������� ���������
        $CvInfo = array( // ���������� �� �������
            "scount" => 0, // ��������� ����� �����
            "zcount" => 0, // ��������� ������ �����
            "first_name" => "", // ������ �����
            "first_code" => "",
            "first_zid" => 0,
            "first_sid" => 0,
            "style" => false,
            "first_z_name" => "",
        );
        
        $isset_first_code = false;
        foreach ($CvStruct as $zid => $arr) {
            if (count($arr['items'])>0) {
                if ($CvInfo['first_z_name']=="") $CvInfo['first_z_name'] = $arr['name'];
                $count_items = 0;
                // ���������� ����� � ������
                if ($arr['ssort']==2) $ssort = $vk_config['serie_sort']; else $ssort = $arr['ssort'];
                // ����� ������
                if ($arr['style'] && $CvInfo["style"]===false && preg_match("/^[A-Za-z0-9-_]+$/",$arr["style"]) && is_file(ENGINE_DIR . "/inc/include/p_construct/players_style/{$arr['style']}.php")) 
                    $CvInfo["style"] = $arr['style'];
                // ��������� �����
                foreach ($arr['items'] as $num => $film) {
                    if ($film["scode"]!="" && substr($film["scode"],0,5)!="prep(") {
                        $CvStruct[$zid]['items'][$num]['scode_begin'] = $film['scode'];
                        $CvStruct[$zid]['items'][$num]['scode'] = VideoTubes::getInstance()->getPlayer($film['scode'], $film['sname']);
                        if ($CvStruct[$zid]['items'][$num]['scode']) {
                            $count_items++;
                            if ($isset_first_code===false) {
                                // ����� ������ �����
                                if ($ssort==0) { // ���� ���������������  �� �����������
                                    if ($CvInfo["first_code"]=="") {
                                        $CvInfo["first_name"] = $CvStruct[$zid]['items'][$num]['sname'];
                                        $CvInfo["first_code"] = $CvStruct[$zid]['items'][$num]['scode'];
                                        $CvInfo["first_code_begin"] = $CvStruct[$zid]['items'][$num]['scode_begin'];
                                        $CvInfo["first_zid"] = $film['parent'];
                                        $CvInfo["first_sid"] = $film['id'];
                                    }
                                } else { // ���� �� ��������
                                    $CvInfo["first_name"] = $CvStruct[$zid]['items'][$num]['sname'];
                                    $CvInfo["first_code"] = $CvStruct[$zid]['items'][$num]['scode'];
                                    $CvInfo["first_code_begin"] = $CvStruct[$zid]['items'][$num]['scode_begin'];
                                    $CvInfo["first_zid"] = $film['parent'];
                                    $CvInfo["first_sid"] = $film['id'];
                                }
                            }
                        }
                    } else 
                        unset($CvStruct[$zid]['items'][$num]);
                }
                
                if ($count_items>0) { 
                    $CvInfo["scount"] += $count_items;
                    $CvInfo["zcount"]++;
                } else
                    unset($CvStruct[$zid]);
                // ���� ����� ������ ��� � 1 ������, ������ �� ����
                if ($CvInfo["first_code"]!="") {
                    $isset_first_code = true;
                }
                // ��������� ����� � ������
                if ($ssort==1) {
                    $CvStruct[$zid]["items"] = array_reverse ($CvStruct[$zid]["items"], true);
                }
            }
        }
        // ���������� ����������� ������� ������
        VcExtension::getInstance()->inc("PlayerResize.php","PlayerResize");
        
        //print_r($CvStruct); exit;
        //print_r($CvInfo); //exit;
        // ���������� �������� - ����������� $CvStruct, $CvInfo
        if ($_GET["mod"] === "rational-catalog") VcExtension::getInstance()->inc("struct.php","Catalog");
        if (VcExtension::getInstance()->enabled("Catalog") && isset($rl_doReturnStruct)) return $CvStruct;
        
        // �������� ������ ������� ������
        if ($CvInfo["style"])
            $CvPlayerStyle = $CvInfo["style"];
        else {
            $CvPlayerStyle = $vk_config['player_style'];
            $CvInfo["style"]="";
        }
        
        if (!preg_match("/^[A-Za-z0-9_-]+$/",$CvPlayerStyle)) exit("Hack attempt! �������� ����� ������ '".htmlspecialchars($CvPlayerStyle, ENT_COMPAT, $config['charset'])."' �������� ������������ �������!");
        
        // ���������� ������ �� ���������
        $stopWork = false; 
        $CvBuffer = "";
        
        VcExtension::getInstance()->event('afterStruct');
        VcExtension::getInstance()->event('afterStruct2');
        if (!$stopWork) {
            if (isset($CvInfo) && $CvInfo["scount"]>0) {
                ob_start();
                include (ENGINE_DIR . "/inc/include/p_construct/players_style/{$CvPlayerStyle}.php");
                $CvBuffer = ob_get_clean();
            }
        }
        // ���������� �������� - ������� ���� ������
        if (VcExtension::getInstance()->enabled("Catalog") && $_GET["mod"] === "rational-catalog") return $CvBuffer;
        
        if ($CvBuffer!==''){
            // MOD TEST
            $file = ENGINE_DIR . '/inc/include/p_construct/mods/viewlater/afterStyle.php';
            if (file_exists($file))
                include_once ($file);
            
            VcExtension::getInstance()->event('beforeTemplateCompuillation');
            
            // ����������� ������ UPPOD
            if (VideoTubes::getInstance()->uppodUsed) {
                $CvBuffer = VideoTubes::getInstance()->uppodInitialization(). $CvBuffer;
            }
            
            $tpl->set_block( "'\\[video-constructor\\](.*?)\\[/video-constructor\\]'si", "$1" );
            $tpl->set( '{video-constructor}', $CvBuffer );
            $tpl->set_block( "'\\[is-video-constructor\\](.*?)\\[/is-video-constructor\\]'si", "$1" );
            $tpl->set_block( "'\\[no-video-constructor\\](.*?)\\[/no-video-constructor\\]'si", "" );
            
            VcExtension::getInstance()->event('afterTemplateCompuillation');
            
        } else {
            $tpl->set_block( "'\\[video-constructor\\](.*?)\\[/video-constructor\\]'si", "" );
            $tpl->set_block( "'\\[is-video-constructor\\](.*?)\\[/is-video-constructor\\]'si", "" );
            $tpl->set_block( "'\\[no-video-constructor\\](.*?)\\[/no-video-constructor\\]'si", "$1" );
        }
    } else {
        $tpl->set_block( "'\\[video-constructor\\](.*?)\\[/video-constructor\\]'si", "" );
        $tpl->set_block( "'\\[is-video-constructor\\](.*?)\\[/is-video-constructor\\]'si", "" );
        $tpl->set_block( "'\\[no-video-constructor\\](.*?)\\[/no-video-constructor\\]'si", "$1" );
    }
    
    // MOD TEST
    $file = ENGINE_DIR . '/inc/include/p_construct/mods/viewlater/always.php';
    if (file_exists($file))
        include_once ($file);
    
} else {
    $tpl->set_block( "'\\[video-constructor\\](.*?)\\[/video-constructor\\]'si", "" );
    $tpl->set_block( "'\\[is-video-constructor\\](.*?)\\[/is-video-constructor\\]'si", "" );
    $tpl->set_block( "'\\[no-video-constructor\\](.*?)\\[/no-video-constructor\\]'si", "$1" );
}

?>