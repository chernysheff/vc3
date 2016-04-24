<?php
/**
 * ������ ��������� ��� ������ ��������� ������.
 * �� ������� ������� ���������� ���� ���� ����-�� �� ���� - ��� ����� ���� �����:
 * VideoConstructor::getInstance()->runEditor($data);
 * 
 * @uses        $data           ������ ������ ������ ������� ��� ��������������
 * @uses        $vc_conf        ������ �������� ��� JS
 * @uses        $config         Dle configuration
 * @uses        $vk_config      ������������ �������
 */

$data = json_encode($data);
$vc_conf = json_encode($vc_conf);
?>
<div id="VcEditorAll" style="display:none;">
    <div class="VcHPanel">
        <!-- ����� �������� ��������. -->
        <!--<span class="rcbold">��������:</span><span class="vc_Quality" id="vc_Quality_240">240</span><span class="vc_Quality" id="vc_Quality_360">360</span><span class="vc_Quality" id="vc_Quality_480">480</span><span class="vc_Quality" id="vc_Quality_720">720</span>-->
        <span class="rcbold">�����������&nbsp;������&nbsp;�����&nbsp;(�����):</span><input type="text" id="vc_MinLen" value="30" />
        <span class="rcbold">������&nbsp;������:</span><select id="vc_PlayerStyle">
            <option value="">�� ���������</option>
            <option value="default">default</option>
            <option value="x-scrolling">x-scrolling</option>
        </select>
        <a id="VcNewVersion" href="http://ralode.com/konstruktor-video-v3-changes.html" target="_blank">���������� ;)</a>
        <div></div>
        <span class="rcbold">��������:</span><input type="text" id="vc_Tpl" value="{title}" />
        <input type="button" class="vc_player_st_button" title="�������� ������ ��������" style="background: url(/engine/inc/include/p_construct/editor/images/arrow-circle-down.png) 1px 1px no-repeat #cae6ef;" />
        <span class="rcbold">�������:</span><input type="text" id="vc_Counter" value="1" />
        <input type="button" title="������ ������" id="vc_Import" class="vc-btn16">
        <input type="button" title="������� ������" id="vc_Export" class="vc-btn16">
    </div>
    <div id="VcConteiner"></div>
    <div class="vc_AddPanelFooter"><span><a href="#" id="vc_addZborka">�������� ������</a></span></div>
    <br />
    <!-- ��������� �� ������ -->
    <div id="VcErrorsBlock"></div>
    <?php if ($vk_config['is_debug']) { ?> 
    �������: 
    <a href="#" onclick="console.log( $('#VcConteiner').html() ); return false;">VcConteiner HTML</a> - 
    <a href="#" onclick="console.log( VcEditor.getAllItems() ); return false;">Get all items</a>
    <?php } ?>
    <!-- ������ ��������� ���� -->
    <div id="VcPreviewDialog" title="�������� ����" style="display:none;"></div>
    <!-- ������ �������������� ���� -->
    <div id="VcEditDialog" title="�������������� ����" style="display:none;">
        <div>
            <textarea id="vc_editor_text" rows="4"></textarea>
            <input type="hidden" id="vc_editor_zid" value="" />
            <input type="hidden" id="vc_editor_num" value="" />
        </div>
    </div>
    <!-- ������ ��������� ����� �� ����� -->
    <div id="VcCplDialog" title="������ ����� �� �����" style="display:none;">
        <ul class="cpl_list">
            <li></li>
        </ul>
        <!-- ������ ������ ����� -->
        <ul class="cpl_list" style="display:none">
            <li style="margin-bottom:5px;"><strong>{text}</strong><br>������������: <a href="/user/{user_name}/" style="color: #0c607e;">{user_name}</a>. �����: <span style="color: #0d6d23;">{time}</span></li>
        </ul>
    </div>
    <!-- ������ ��������� ������ � ������ -->
    <div id="VcErrDialog" title="������ ������" style="display:none;">
        <p><strong>������</strong>:</p>
        <p id="vc_err_text"></p>
    </div>
    <!-- ������ ������ ��������� -->
    <div id="VcFindVkDialog" style="display:none;">
        <!-- ������ ���� �������� � ������� JS -->
        <div class="VcFindPreload">
            <img src="<?php echo $config["http_home_url"]."engine/inc/include/p_construct/editor/images"; ?>/ajax-loader.gif" /><br />
            ���������, ���� �������� ������...
        </div>
    </div>
    <!-- ������ ��������� ����� �� ����� -->
    <div id="VcCplDialog" title="������ �����" style="display:none;">
        <ul class="cpl_list">
            <li></li>
        </ul>
        <!-- ������ ������ ����� -->
        <ul class="cpl_list" style="display:none">
            <li>{text} by {user_name} at {time}</li>
        </ul>
    </div>
    <!-- ������ ������ ������� �������� -->
    <div id="VcFindVkNameTpl" title="����� �������" style="display:none;">
        ����� HTML-��� ������������ � ������� JS
    </div>
    <!-- ������ �������������� ����� � ������ -->
    <div id="VcRenamingDialog" title="�������������� �����" style="display:none;">
        ����� ������: <input type="text" id="vc_rn_template" value="����� ��� {num}"><br>
        ������� (��������� ��������): <input type="text" id="vc_rn_counter" value="1"> 
        �������: <input type="text" id="vc_rn_ddd" value="1"><br><br>
        <div id="vc_rm_list">
            <span class="tdNum tdTHead">&nbsp;</span><span class="tdLeft tdTHead">������ ���</span><span class="tdRight tdTHead">����� ���</span>
            <div id="vc_rm_listItems">
                <span class="tdNum">1</span><span class="tdLeft">�����</span><span class="tdRight">����� ����</span>
                <span class="tdNum">2</span><span class="tdLeft">����� �������</span><span class="tdRight">������.</span>
            </div>
            <div style="clear: both;"></div>
        </div>
    </div>
</div>

<script>
    (function(){
        $("head").append('<link href="<?php echo $config['http_home_url'] ?>engine/inc/include/p_construct/editor/style.css" rel="stylesheet" type="text/css"/>');
        var VcEditorAll = $("#VcEditorAll");
        $('#xfield\\[pconstruct\\],#xf_pconstruct').parent().html('<?php
if ($config["version_id"]>=10.2)
    echo '<input type="hidden" name="xfield[pconstruct]" id="xf_pconstruct" value="">';
else
    echo '<input type="hidden" name="xfield[pconstruct]" id="xfield[pconstruct]" value="" />';
?>'+VcEditorAll.html());
        VcEditorAll.remove();
    })();
</script>
<script src="<?php echo $config['http_home_url']; ?>engine/classes/js/jquery.json.js"></script>
<script src="<?php echo $config['http_home_url']; ?>engine/inc/include/p_construct/editor/sortable.js"></script>
<script src="<?php echo $config['http_home_url']; ?>engine/inc/include/p_construct/editor/KonstructorApi.js"></script>
<script src="<?php echo $config['http_home_url']; ?>engine/inc/include/p_construct/editor/ejs.js"></script>
<script src="<?php echo $config['http_home_url']; ?>engine/inc/include/p_construct/editor/sortable_editor.js"></script>
<script src="<?php echo $config['http_home_url']; ?>engine/inc/include/p_construct/editor/jquery.cookie.js"></script>
<script type="text/javascript" language="javascript">
    /*
    $.fn.on
     $().jquery; // �������� ������ Jquery - ##!! �������� �� ������� ��� �����������
    */
    var VcEditor;
    $(document).ready(function(){
        // ������ .sortable
        if ($.type($.fn.sortable)!=="function") 
            $("#VcErrorsBlock").text("(!) JQueryUI.sortable �� ��������. ���������� ����� ������ �� ��������.");
        
        VcEditor = new VcEditorConstructor($("#VcConteiner"), <?php echo $data; ?>, <?php echo $vc_conf; ?>);
        
        $("p#first").after(VcEditor.init);
        VcInitSortable();
    });

</script>

<!-- ����������, ������� afterEditor -->
<script type="text/javascript" language="javascript">
<?php VcExtension::getInstance()->event('afterEditor', 'js'); ?>
</script>