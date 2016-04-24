<?php
/*
 * ���������� �����: ������ ������ ������ "Modern"
 */

// $CvPlayerStyle - �������� �������� �����
//print_r($CvStruct); exit; // - ��������� ������
//print_r($CvInfo); exit; // - ������ ���������� ��� ��������� ������
// Array ( [scount] => 2 [zcount] => 1 [first_name] => ������ 1 [first_code] => oid=-245070.. [first_zid] => 77 [first_sid] => 137 )

/**
 * �������������� ������ ���������� ������� �� div-�� .vcsel_wrap ��� ���������
 */
if (!isset($modern_wrap_classes)) $modern_wrap_classes = "";

// ���� ���� �� ������ ����
if ($CvInfo["first_code"]!==false) {

?>
<style>
    #VideoConstructor_v3_x_Player { width:<?php echo $vk_config['player_width']; ?>px; margin:8px auto 8px; }
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
<link rel='stylesheet'  href='<?php echo $config["http_home_url"];?>engine/inc/include/p_construct/players_style/modern/style.css' type='text/css' media='all' />
<div id="VideoConstructor_v3_x_Player" class="vc-player-default">
    <div id="VcCode">
        <?php echo $CvInfo["first_code"]; ?>
    </div>
    <?php 
    if ($CvInfo["scount"]>1) {
    ?>
    <div id="vc-player-selectbox">
        <div class="vcsel_wrap <?php echo $modern_wrap_classes;?>">
            <div class="vcsel_imul">             
                <div class="vcsel_selected">
                    <div class="vc_visor">
                        <div class="vc_selected-text"><?php echo $CvInfo["first_name"];?></div><div class="vcsel_arrow"></div>
                    </div>
                </div> 
                <div class="vcsel_options <?php if($CvInfo["scount"]>5) echo "vc_scrolling"; ?>" data-value="0">
                <?php
                $vc_codes = array();
                $i = 0;
                foreach ($CvStruct as $zid => $arr) {
                    $options = '';
                    $z_count_series = 0;
                    foreach ($arr['items'] as $film) {
                        if ($film['scode']<>'') {
                            $options .= '<div class="vcsel_option" id="vcoption_'.$i.'" data-value="'.$i.'" data-zid="'.intval($film['parent']).'" data-sid="'.intval($film['id']).'">'.htmlspecialchars($film['sname'],ENT_QUOTES, $config['charset']).'</div>'."\n";
                            $vc_codes[$i] = $film['scode'];
                            $i++;
                            $z_count_series++;
                        }
                    }
                    if ($z_count_series>0) { // ���� � ������ ���� �������� �����
                        if ($CvInfo["zcount"]>1) { // ���� ��������� ������
                        ?>
                            <div class="vcsel_optgroup"><?php echo htmlspecialchars($arr['name'],ENT_QUOTES, $config['charset']);?></div>
                            <?php echo $options; ?>
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
                </div>
            </div>
        </div>
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
        
        /**
        *  ������� ��� ����� ��� ����� �� ��� ����������� select, ���������� ��� ���������� ������ option���
        */
        $('.vcsel_imul').bind('click', function() {
            $('.vcsel_imul').removeClass('act');
            $(this).addClass('act');
            if ($(this).children('.vcsel_options').is(':visible')) {
                $('.vcsel_options').hide();
            } else {
                $('.vcsel_options').hide();
                $(this).children('.vcsel_options').show();
            }
        });

        /**
         * ��������� � �������
         */
        $('.vcsel_options').on('click', '.vcsel_option', function() {
            var obj = $(this);
            //������ �������� �� ���������
            var tektext = obj.html();
            obj.parent('.vcsel_options').parent('.vcsel_imul').children('.vcsel_selected').children('.vc_visor').children('.vc_selected-text').html(tektext);
            //���������� �������
            obj.parent('.vcsel_options').children('.vcsel_option').removeClass('vcsel_ed');
            obj.addClass('vcsel_ed');
            // ��������� �����
            var obj = $(this);
            var i =obj.attr("data-value");
            if ($.type(vc_codes[i])!="undefined") {
                $.data(document, "vcZid", obj.attr("data-zid") ); // ������� ������ � �����
                $.data(document, "vcSid", obj.attr("data-sid") );
                $("#VcCode").html(vc_codes[i]);
            }
        });

        /**
         * �� ��� ������� ��������� ��� ������� ����� ��������� ������ ��� �������� �����, blur ��� ������
         */
        var vc_selenter = false;
        $('.vcsel_imul').bind('mouseenter', function() {
            vc_selenter = true;
        });
        $('.vcsel_imul').bind('mouseleave', function() {
            vc_selenter = false;
        });
        $(document).click(function() {
            if (!vc_selenter) {
                $('.vcsel_options').hide();
                $('.vcsel_imul').removeClass('act');
            }
        });
        /**
         * �������� ��������� ���� ��� ��������
         */
        $(".vcsel_options").each(function(){
            var val = $(this).attr("data-value");
            if ($.type(val)!="undefined") {
                var first_opt = false;
                var is_marked = false;
                $(this).find(".vcsel_option").each(function(){
                    if($(this).attr("data-value")==val) {
                        $(this).addClass("vcsel_ed");
                        is_marked = true;
                        return;
                    } else if(first_opt===false) {
                        first_opt = $(this);
                    }
                });
                if (is_marked===false && first_opt!==false) {
                    first_opt.addClass("vcsel_ed");
                }
            }
            // ������ �������, ���� ���� optgroup
            if ($(this).find(".vcsel_optgroup").length>0) {
                $(this).find(".vcsel_option").addClass("vc_space");
            }
        });
        
        // ������������ (����� �� ����� Default)
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
        // ����� �������� (����� �� ����� Default)
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