/**
 *  ����������� ����� ��� DLE
 *  http://ralode.com
 *  @rationalObfuscation compress
 */
var RalodePlayer = function() {
    var data_z = []; // ������ ������
    var data_s = []; // ������ ������ �� ������
    var curr_z = 0; // ������� ������
    var curr_s = 0; // ������� �����
    var scount = 0; // ����� �-�� �����
    var selected_arr = null; // ������ $s ������� ������
    
    var visor_width = 0; // ������ ������ ��� ��������� � ���� � �������
    var top_lenta_w = 0; // ������ ����� ���������
    var bottom_lenta_w = 0;
    var top_margin_min = 0; // ����������� �������� ������� ����� ��� �����
    var bottom_margin_min = 0;
    var margin_step = 400; // ��� ����������� �������

    /**
     * ��������� ������ �����. pos = {"top","bottom"}
     */
    function getItemsWidth (pos) {
        if (!(pos=="top" || pos=="bottom")) return;
        var width = 0;
        var objs = $("#rl-lenta-"+pos+" .RlItem");
        var l = objs.length;
        if (l>0) {
            objs.each(function(){
                width+= $(this).outerWidth();
            });
        }
        // ��������� ������ �������� ����� ����������
        width += (l-1)*4; // 4 px ������
        return width; // 808
    }

    function getMarginMin (pos) {
        if (!(pos=="top" || pos=="bottom")) return;
        if (pos=="top") {
            var min = visor_width - top_lenta_w;
        } else {
            var min = visor_width - bottom_lenta_w;
        }
        min -= 2; // �����-�� ��������, ��� �� ��� ��������
        if (min>0) min = 0;
        return min;
    }

    function getMargin(pos) {
        var s = $("#rl-lenta-"+pos).css("margin-left");
        return parseInt(s);
    }

    function setMargin (pos, val) {
        if (pos=="top") var min = top_margin_min; else var min = bottom_margin_min;
        if (val<=0 && val>=min) 
            $("#rl-lenta-"+pos).css("margin-left",val+"px");
    }

    this.move = function (pos, side) {
        //alert(pos+", "+side);
        var margin = getMargin(pos);
        if (side=="left") {
            var newMargin = margin+margin_step;
            if (newMargin>0) newMargin = 0;
        } else {
            if (pos=="top") var min = top_margin_min; else var min = bottom_margin_min;
            var newMargin = margin-margin_step;
            if (newMargin<min) newMargin = min;
        }
        setMargin(pos, newMargin);
    }
    
    /**
     * ����� �����
     */
    this.serie = function (s, obj) {
        s = parseInt(s);
        if (data_s[curr_z][s]) {
            curr_s = s;
            selected_arr = data_s[curr_z][curr_s];
            $(".playerCode").html(data_s[curr_z][s]["code"]);
            $("#rl-lenta-bottom .serie-active").removeClass("serie-active");
            $(obj).addClass("serie-active");
        }
    }
    
    /**
     * ��������� ������ �����
     */
    function generateSeries() {
        if (scount>1) {
            var html = "";
            for (var key in data_s[curr_z]) {
                if (key!=="in_array"){ // ������� ����
                    var arr = data_s[curr_z][key];
                    html += '<div class="RlItem'+(key==curr_s?' serie-active':'')+'" onclick="return RalodePlayer.serie('+key+',this);">'+arr["name"]+'</div>';
                }
            }
            $("#rl-lenta-bottom").html(html);
            setMargin("bottom",0); // last mod

            bottom_lenta_w = getItemsWidth("bottom");
            bottom_margin_min = getMarginMin("bottom");
        } else {
            $("#rl-buttons-bottom").css("display","none").after("<div style='height:5px;'></div>");
        }
    }
    /**
     * ����� ������
     */
    this.zborka = function (z, obj) {
        z = parseInt(z);
        if (data_s[z]) {
            curr_s = 0;
            curr_z = z;
            generateSeries();
            setMargin("bottom",0);
            $("#rl-lenta-bottom .serie-active").removeClass("serie-active");
            $("#rl-lenta-top .serie-active").removeClass("serie-active");
            $(obj).addClass("serie-active");
        }
    }
    /**
     * ��������� ������ ������
     */
    function generateZborki() {
        if (data_z.length>1) {
            var html = "";
            for (var key in data_z) {
                if (key!=="in_array"){ // ������� ����
                    var zname = data_z[key];
                    html += '<div class="RlItem'+(key==curr_s?' serie-active':'')+'" onclick="return RalodePlayer.zborka('+key+',this);">'+zname+'</div>';
                }
            }
            $("#rl-lenta-top").html(html);
            setMargin("top",0);

            top_lenta_w = getItemsWidth("top");
            top_margin_min = getMarginMin("top");
        } else {
            $("#rl-buttons-top").css("display","none").after("<div style='height:5px;'></div>");
        }
    }

    /**
     * �������������
     */
    this.init = function(dataz,datas,s_count,lists) { // mod mark
        data_z = dataz;
        data_s = datas;
        selected_arr = data_s[0][0];
        scount = s_count;
		this.lists = lists;
        visor_width = $("#rl-buttons-bottom .RlVisor").width();
        margin_step = parseInt(visor_width * 0.7);
        generateZborki();
        generateSeries();
    }
	this.lists = null; // array (z =>..., s=>...) // id => num (0...n-1) // mod mark
    
    this.getZid = function () {
        return selected_arr["zid"];
    }
    this.getSid = function () {
        return selected_arr["sid"];
    }
	// Mark mod for �����������
	this.getZidSid = function() {
		return {
			zid: data_s[curr_z][curr_s]["zid"],
			sid: data_s[curr_z][curr_s]["sid"]
		};
	}
	this.selectMark = function(seazon, serie) {
		var pos_z = RalodePlayer.lists.z[seazon];
		//console.log(seazon, " --- ", serie);
		//console.log(RalodePlayer.lists);
		if (RalodePlayer.lists.s[seazon]) {
			var pos_s = RalodePlayer.lists.s[seazon][serie];
		}
		if (this.currentSeason!==0 && pos_z>=0) {
			var sc = "<"+"script>$(\".RalodePlayer #rl-lenta-top .RlItem\").eq("+pos_z+").click();</sc"+"ript>";
			$("body").append(sc);
		}
		if (this.currentSeries!==0 && pos_s>=0) {
			var sc = "<"+"script>$(\".RalodePlayer #rl-lenta-bottom .RlItem\").eq("+pos_s+").click();</sc"+"ript>";
			$("body").append(sc);
		}
	}
	// Reinit
	var once_used = false;
	this.reinit = function (once) {
		if (once) {
			if (once_used==false) {
				once_used = true;
			} else
				return false;
		}
		visor_width = $("#rl-buttons-bottom .RlVisor").width();
		margin_step = parseInt(visor_width * 0.7);
		generateZborki();
		generateSeries();
	}
    return this;
}();
$(document).ready(function(){
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
                        var zid = RalodePlayer.getZid();
                        var sid = RalodePlayer.getSid();
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