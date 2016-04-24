/**
 *  ����������� ����� ��� DLE
 *  http://ralode.com
 *  @rationalObfuscation compress
 */
var RalodePlayer = Object.create(CRalodePlayer);
RalodePlayer.init = function(data, cvStruct) {
    this.__proto__.init(data, cvStruct);
    // �������
    var _this = this;
    $(".rl-seazon").click(function(){
        var callback = function() {
            if ($(".rl-seazon").hasClass("rl-active")) {
                $(".rl-seazon").removeClass("rl-active");
                _this.zborkilistShow(false);
            } else {
                $(".rl-seazon").addClass("rl-active");
                _this.zborkilistShow(true);
            }
            $(".rl-serie").removeClass("rl-active");
        }
        if ($(".rl-serie").hasClass("rl-active")) {
            $(".rl-serie").hasClass("rl-active");
            $(".rl-list").slideUp(100, function(){
                callback();
            });
        } else
            callback();
        
    });
    $(".rl-serie").click(function(){
        var callback = function() {
            if ($(".rl-serie").hasClass("rl-active")) {
                $(".rl-serie").removeClass("rl-active");
                _this.serielistShow(false);
            } else {
                $(".rl-serie").addClass("rl-active");
                _this.serielistShow(true);
            }
            $(".rl-seazon").removeClass("rl-active");
        }
        if ($(".rl-seazon").hasClass("rl-active")) {
            $(".rl-seazon").hasClass("rl-active");
            $(".rl-list").slideUp(100, function(){
                callback();
            });
        } else
            callback();
    });
    // ������
    RalodePlayer.initComplaints();
    // ���������
    //RalodePlayer.initScrolling();
};
RalodePlayer.initScrolling = function() {
    $(".vc-player .rl-list").mCustomScrollbar({
        horizontalScroll:true,
        theme: $(".RalodePlayer").hasClass("rl-light") ? "dark" : "light"
    });
};
RalodePlayer.config = {
    inRow: 5 // ���������� ����� � �������
};
RalodePlayer.zborkilistShow = function (show) {
    if (show) {
        // ��������� ������ c,jhjr
        var data = RalodePlayer.data;
        var len = RalodePlayer.length(data);
        var inRow = 0; // ����� � �������
        var val;
        var html = "<ul>";
        for (var zid in data) {
            val = data[zid];
            inRow++;
            if (inRow==RalodePlayer.config.inRow) {
                inRow = 0;
                html += "</ul><ul>";
            }
            html += '<li'+(zid==RalodePlayer.curr_zid ? ' class="rl-active"' : '')+' onclick="RalodePlayer.selectZborka('+zid+');">'+val.name+'</li>';
        }
        html += "</ul>";
        $(".rl-list").html(html).slideDown(100, function(){
            RalodePlayer.initScrolling();
        });
    } else
        $(".rl-list").slideUp(100);
};

RalodePlayer.serielistShow = function (show) {
    if (show) {
        // ��������� ������ �����
        var z = RalodePlayer.getCurrentZ();
        var len = RalodePlayer.length(z.items);
        var inRow = 0; // ����� � �������
        var val;
        var html = "<ul>";
        for (var num in z.items) {
            val = z.items[num];
            if (inRow==RalodePlayer.config.inRow) {
                inRow = 0;
                html += "</ul><ul>";
            }
            html += '<li'+(num==RalodePlayer.curr_num ? ' class="rl-active"' : '')+' onclick="RalodePlayer.selectSerie('+val.parent+','+num+');">'+val.sname+'</li>';
            inRow++;
        }
        html += "</ul>";
        $(".rl-list").html(html).slideDown(100, function(){
            RalodePlayer.initScrolling();
        });
        
    } else
        $(".rl-list").slideUp(100);
};

// ����� ������ � ������
RalodePlayer.selectZborka = function (zid) {
    var cz = RalodePlayer.getZ(zid);
    if (cz) {
        var obj2 = RalodePlayer.firstElement(cz.items);
        if (obj2 && obj2.id!==RalodePlayer.curr_sid) {
            var obj = RalodePlayer.selectBySid(obj2.id);
            $(".rl-serie").text(obj.sname);
            RalodePlayer.player(obj.scode);
            $(".rl-seazon").text(cz.name);
            RalodePlayer.normalizeWidth();
        }
    }
    $(".rl-list").slideUp(100);
    $(".rl-lBtn").removeClass("rl-active");
}

RalodePlayer.selectSerie = function (zid, num) {
    var curr_sid = RalodePlayer.curr_sid;
    var obj = RalodePlayer.select(zid, num);
    if (obj && obj.id!==curr_sid) {
        $(".rl-serie").text(obj.sname);
        RalodePlayer.normalizeWidth();
        RalodePlayer.player(obj.scode);
    }
    $(".rl-list").slideUp(100);
    $(".rl-lBtn").removeClass("rl-active");
}

// ������� ������� �������� �����
RalodePlayer.normalizeWidth = function() {
    var player_width = 570;
    if (player_width<300) player_width = 500; // ������ ������ �� ����� 500 ����.
    player_width -= 130; // ��. �����
    // �������� ������ ���� ������
    var w1 = $(".rl-seazon").outerWidth();
    var w2 = $(".rl-serie").outerWidth();
    if (w1+w2>player_width) {
        player_width /= 2;
        if (w1>player_width) {
            var text = $(".rl-seazon").text();
            var len = text.length;
            var new_len = parseInt(player_width / w1 * len);
            $(".rl-seazon").text(text.substr(0, new_len)+"...");
        }
        if (w2>player_width) {
            var text = $(".rl-serie").text();
            var len = text.length;
            var new_len = parseInt(player_width / w2 * len);
            $(".rl-serie").text(text.substr(0, new_len)+"...");
        }
    }
};