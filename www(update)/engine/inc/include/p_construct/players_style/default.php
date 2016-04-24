<?php
/*
 * ���������� �����: ������ ������ ������
 */

// $CvPlayerStyle - �������� �������� �����
//print_r($CvStruct); exit; // - ��������� ������
//print_r($CvInfo); exit; // - ������ ���������� ��� ��������� ������
// Array ( [scount] => 2 [zcount] => 1 [first_code] => oid=-245070.. [first_zid] => 77 [first_sid] => 137 )

// ���� ���� �� ������ ����
if ($CvInfo["first_code"]!==false) {

?>
<style>
    #VideoConstructor_v3_x_Player { width: <?php echo VideoTubes::getInstance()->formatSize($vk_config["player_width"], true); ?>; margin:8px auto 8px; }
    #VcCode {height: <?php echo VideoTubes::getInstance()->formatSize($vk_config["player_height"], true); ?>;}
    /* ����� ����� */
    #vc-player-select { display:block; margin:10px auto 5px; min-width:250px; }
    /* ���� ����� */
    #vc-complait-box { float:right; }
    /* ������ "������������ �� �����" */
    #CvComplaintShowModal {} 
    /* ����� �������� ������*/
    #vc-complait-dialog div:last-child {padding-top:8px;}
    #cv_complaint_text {width:330px; height:70px; opacity:0.25; resize:none; outline: none; overflow: auto;}
</style>
<div id="VideoConstructor_v3_x_Player" class="vc-player-default">
    <div id="VcCode">
        <?php echo $CvInfo["first_code"]; ?>
    </div>
    <?php 
    if ($CvInfo["scount"]>1) {
    ?>
    <div id="vc-player-selectbox">
        <select id="vc-player-select">
            <?php
            $vc_codes = array();
            $i = 0;
            foreach ($CvStruct as $zid => $arr) {
                $options = '';
                $z_count_series = 0;
                foreach ($arr['items'] as $film) {
                    if ($film['scode']<>'') {
                        $options .= '<option value="'.$i.'" data-zid="'.intval($film['parent']).'" data-sid="'.intval($film['id']).'">'.htmlspecialchars($film['sname'],ENT_QUOTES, $config['charset']).'</option>'."\n";
                        $vc_codes[$i] = $film['scode'];
                        $i++;
                        $z_count_series++;
                    }
                }
                if ($z_count_series>0) { // ���� � ������ ���� �������� �����
                    if ($CvInfo["zcount"]>1) { // ���� ��������� ������
                    ?>
                        <optgroup label="<?php echo htmlspecialchars($arr['name'],ENT_QUOTES, $config['charset']);?>" data-zid="<?php echo intval($arr['id']);?>">
                            <?php echo $options; ?>
                        </optgroup>
                    <?php
                    } else {
                        echo $options;
                    }
                }
            }
            
            if ($config["charset"]!="utf-8" && is_array($vc_codes)) {
                foreach ($vc_codes as $key => $val) 
                    $vc_codes[$key] = iconv ("WINDOWS-1251", "UTF-8", $val);
            }
            $vc_codes = json_encode($vc_codes);
            if ($vc_codes===false) $vc_codes = "[]";
            ?>
        </select>
    </div>
    <?php
    } // endif ($CvInfo["scount"]>1)
    ?>
    <div id="vc-complait-box">
        <span class="vc-complait-span"><a href="#" class="CvComplaintShowModal">������������ �� �����</a></span>
    </div>
    <div id="vc-complait-dialog" title="�������� ������� ��� ������� �� �������" style="display:none;">
        <div><label><input type="radio" name="cv_complaint" value="����� �� ��������" checked> ����� �� ��������</label></div>
        <div><label><input type="radio" name="cv_complaint" value="����� �� ������������� ��������"> ����� �� ������������� ��������</label></div>
        <div><label><input type="radio" name="cv_complaint" value="" > ������:</label></div>
        <div><textarea id="cv_complaint_text" disabled></textarea></div>
    </div>
    <div style="clear:both;"></div>
</div>
<script type="text/javascript" language="javascript">
    /**
     * ����������� ����� ��� DLE v3.x
     * @autor SeregaL (www.ralode.com)
     */
    var vc_codes = <?php echo isset($vc_codes)&& $vc_codes ? $vc_codes : "[]"; ?>;
    $.data(document, "vcZid", <?php echo $CvInfo["first_zid"]; ?> ); // ������ �����
    $.data(document, "vcSid", <?php echo $CvInfo["first_sid"]; ?> );
    $(document).ready(function(){
        // ��������� �����
        $("#vc-player-select").change(function(){
            var obj = $(this);
            var i =obj.val();
            if ($.type(vc_codes[i])!="undefined") {
                var oobj = $("option[value="+i+"]",obj);
                $.data(document, "vcZid",oobj.attr("data-zid") ); // ������� ������ � �����
                $.data(document, "vcSid",oobj.attr("data-sid") );
                $("#VcCode").html(vc_codes[i]);
            }
        });
        // ������������
        $(".CvComplaintShowModal").click(function(){
            // ���������� ����������
            $("#vc-complait-dialog input[type=radio]:first").prop("checked",true);
            $("#cv_complaint_text").val("").css("opacity","0.25").prop("disabled",true);
            // �������� �������
            $("#vc-complait-dialog").dialog({
                closeText: "�",
				width: "auto",
                buttons: [ 
                    {
                        text: "���������", click: function() {
                            var zid = $.data(document,"vcZid");
                            var sid = $.data(document,"vcSid");
                            var text = "";
                            $("#vc-complait-dialog input[type=radio]").each(function(){
                                if ($(this).prop("checked")) text = $(this).val();
                            });
                            if (text=="") {
                                text = $("#cv_complaint_text").val();
                            }
                            if (text) {
                                var this_ = this;
                                $.post("/index.php?do=videoconstructor&action=add_cmpl",{zid:zid, sid:sid, text:text}, function(data_text){
                                    //alert(data_text);
                                    switch (data_text) {
                                        case "OK": $( this_ ).dialog( "close" ); break;
                                        case "AUTH": alert("��� �������� ��������� ��� ���� ��������������!"); break;
                                        case "ANTIFLOOD": alert("�� ������������ ��������� ������� �����! ��������� ����� 30 ������!"); break;
                                        default: alert("������ ��� �������� ���������. ����������, �������� ��������������!"); break;
                                    } 
                                });
                            } else {
                                alert("������: ��������� ����� ������...");
                            }
                        }
                    },{
                        text: "������", click: function() {
                            $( this ).dialog( "close" );
                        }
                    }
                ],
                resizable: false
            });
            return false;
        });
        // ����� ��������
        $("#vc-complait-dialog input[type=radio]").change(function(){
            var v = $(this).val();
            if (v=="") {
                $("#cv_complaint_text").css("opacity","1").prop("disabled",false);
            } else {
                $("#cv_complaint_text").css("opacity","0.25").prop("disabled",true);
            }
        });
    });
</script>
<?php
} // // ���� ���� �� ������ ����
?>