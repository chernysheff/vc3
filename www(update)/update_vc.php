<?php
/**
 * ������: ����������� ����� v3.x ��� DLE
 * ���������� �����: ������������������ ����������
 * @author SeregaL <SeregaL2009@yandex.ru>
 * 
 * ������ ������������ ���������� ���: bolix10@yandex.ru
 */
error_reporting(E_ALL ^ E_NOTICE);
@ini_set('display_errors', true);
@ini_set('html_errors', false);
@ini_set('error_reporting', E_ALL ^ E_NOTICE);
define('DATALIFEENGINE', true);
define('ROOT_DIR', dirname(__file__));
define('ENGINE_DIR', ROOT_DIR . '/engine');
include ENGINE_DIR . '/data/config.php';
require_once ENGINE_DIR . '/modules/functions.php';
require_once ENGINE_DIR . '/classes/mysql.php';
require_once ENGINE_DIR . '/data/dbconfig.php';
require_once ENGINE_DIR . '/modules/sitelogin.php';
if ($member_id['user_group'] != 1) {
    exit('��� ��������� ������ ���������� �������������� �� ����� ��� �������������.');
}

##########################################################
$this_path = "update_vc.php";

$step = isset($_GET["step"]) ? intval($_GET["step"]) : 0; // ���
$data = array();
switch ($step) {
    case 0: // ������ �����
        $data["title"] = "���������� ������������ ����� v3.x";
        $data["text"] = "<p>������������!<br>�� ����������� �������� ����������� ����� v3.x.<br>
���. �������� �������: <a href='http://ralode.com/konstruktor-video-v3.x'>http://ralode.com/konstruktor-video-v3.x</a></p>
<p>�� ������ �����, ����������� � �������� ����� ������:</p>
<ul><li>E-mail: <span class='red'>SeregaL2009@yandex.ru</span></li>
<li>Skype: <span class='red'>serg5734</span></li>
<li>ICQ: <span class='red'>323-395</span></li></ul>";
        $data["next"] = "1";
        break;
    case 1: // �������� ������
        $data["title"] = "��� 1: ���������� ������ � ����";
        $sqls = array();
        $sqls[] = "ALTER TABLE `" . PREFIX . "_vidvk_z` CHANGE `style` `style` VARCHAR( 50 ) NOT NULL"; // build 0008
        $sqls[] = "ALTER TABLE `" . PREFIX . "_vidvk_s` CHANGE `scode` `scode` TEXT NOT NULL"; // build 0009
        $sqls[] = "ALTER TABLE `" . PREFIX . "_vidvk_s` CHANGE `lssort` `lssort` INT NOT NULL"; // build 0011
        $ok = true;
        foreach ($sqls as $sql) {
            $res = $db->query($sql);
            if (!$res) {
                $ok = false;
                $data["text"] .= "<span class='red'>������: �� ������� ��������� ������</span>:<br><textarea>{$sql}</textarea>";
            }
        }
        if ($ok) {
            $data["text"] .= "<p class='green'>��������� �� ������� ��������!</p>";
            $data["next"] = "2";
        }


        break;

    case 2: // �������� ������
        $data["title"] = "��� 2: ������� ����";
        $data["text"] .= "��� ���������� ������ ���������� ����� ������ ��� ������������ �����.<br>\n";
        require_once ENGINE_DIR . '/inc/include/p_construct/classes/VideoConstructor.php';
        VideoConstructor::getInstance()->cacheClear();
        $data["text"] .= "<br><span class='green bold'>��� ������!</span><br>\n";
        $data["next"] = "3";

        break;

    case 3: // �������� ������ ������
        $data["title"] = "��� 3: �������� ������ ������";
        $folder = ENGINE_DIR . '/inc/include/p_construct';
        @unlink($folder . '/template/search-new.tpl.php');
        @unlink($folder . '/template/search-yandex.tpl.php');
        @unlink($folder . '/template/search.tpl.php');
        @rmdir($folder . '/template');
        @unlink($folder . '/classes/JSON_ApiConnection.php');
        $data["text"] .= "<br><span class='green bold'>������ ������ ������ ����� ������������, ������� �� ����� � ���� ������!</span><br>\n";
        $data["next"] = "6";

        break;

    case 6:
        $data["title"] = "���������� ���������";
        $data["text"] .= "<p class='red'>������� ������������ ���� <strong>/{$this_path}</strong>, 
            ����� ���������� ���������� �������� ���������� �� ���������.</p>";

        // ����� ���������� ������ (2.�)
        $sql = "SHOW tables LIKE 'vk%'";
        $res = $db->query($sql, true);
        $isset = false;
        while ($row = $db->get_array($res)) {
            if ($row[0] == "vkfilms" || $row[0] == "vkitems")
                $isset = true;
        }
        $href = $config["http_home_url"] . $config["admin_path"] . "?mod=parser_constructor";
        $data["text"] .= "<button onclick=\"document.location.href='$href';\">� ������� ������������</button>";

        break;

    default:
        $data["title"] = "������ �����������";
        $data["text"] = "<p>��� �� ������! ����� �� ����������!</p>";
        break;
}
header('Content-type: text/html; charset='.($config["charset"] == "utf-8" ? "utf-8" : "windows-1251") );
?>
<!DOCTYPE html>
<html>
    <head>
        <title>��������� - ���������� ����� v3.x ��� DLE</title>
        <meta charset="<?php echo $config["charset"]; ?>">
    </head>
    <style>
        body { margin:0; color: #3a3a3a; }
        .conteiner {background-color: #eaf2f4; width:700px; height:450px; margin: 40px auto 0; border:6px solid #b4d0d7; border-bottom-color: #d1dcdf; border-right-color: #d1dcdf; border-radius:4px;}
        .head_block {font-size:18px; color: #052127; font-weight: bold; border-bottom: 2px dotted #146c82; padding:3px 10px 3px; }
        .content {height:360px; padding:10px; overflow-y: auto; }
        .bottom {margin: 10px 30px 10px;}
        .left{float:left;}
        .right{float:right;}
        a, a:visited {color: #05587a; }
        .red {color:#9f0335;}
        .green {color:#0c5b0e;}
        textarea {width:100%; height:60px;}
        .bold {font-weight:bold;}
    </style>
    <body>
        <div class="conteiner">
            <div class="head_block"><?php echo $data["title"]; ?></div>
            <div class="content"><?php echo $data["text"]; ?></div>
            <div class="bottom">
                <?php if (isset($data["prev"])) echo "<button class='left' onclick=\"document.location='{$this_path}?step={$data['prev']}';\">�����</button>"; ?>
                <?php if (isset($data["next"])) echo "<button class='right' onclick=\"document.location='{$this_path}?step={$data['next']}';\">������</button>"; ?>
            </div>
        </div>

    </body>
</html>