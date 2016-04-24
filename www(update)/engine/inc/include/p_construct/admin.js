/**
 * ������� ������� ������������ �����
 * ������ ������������ ���������� ���: {%loader(EMAIL)%}
 * @rationalObfuscation compress
 */
 
 

/**
 * ����������� ����� ��� �������� ����
 * @params obj_name     ��� ����������� ������� ��� ������������� �������
 * @params items        ������ (������) ����� ��� ��������
 */
function vidosCheckerCreator(obj_name, items) {
    
    var $this = this;
    
    // ��������
    if (typeof items !== 'object') {
        alert ('Error - "items" is not object!');
        return false;
    }
    // ������� �����
    this.objGlobalName = obj_name; // ��� ����������� ����� ����� ����� ��� setTimeout
    this.items = items; // ������ ������ ��� �������
    this.pos = 0; // ������� ������� ����
    this.count = items.length; // ������� ���� ��� ��������
    this.stat = [0,0,0,0,0,0]; // ʳ������ ���� �� ����� �������
    this.delay_ms = 1000; // �������� ����� ��������� ��� ������
    this.is_first_check = 1; // 1 - �������� �������� �������, ���� �������� ������ ��
    this.session = 0;
    
    // ���������� �������� ��� �������� �� ����
    // ������ ��������: s_id, err . ����� ��������� �������������� � JSON
    this.results = {};
    this.results_count = 0; // ������� ��������� ����������
    this.results_count_tosend = 20; // ������� ��������� ���������� ����������
    
    // ����� ����������
    this.chkStatObj = {
        "0":$('#chk_error_0'), // ��� ������
        "1":$('#chk_error_1'), // �������
        "2":$('#chk_error_2'), // ����������� tube
        "3":$('#chk_error_3'), // ��������� ������
        "4":$('#chk_error_4'), // �� �������������� �������� ��� ����� tube
        "5":$('#chk_error_5')  // ������ ��� �������� - �������� ��������� � PHP ��� JS
    };
    
    this.chk_positionObj = $('#chk_position');
    this.chk_countObj = $('#chk_count');
    this.chk_percentObj = $('#chk_percent');
    this.chk_logObj = $('#chk_log');
    this.progress_barObj = $('#progress-3');
    
    /**
     *  ��������� ������ �������� (����������� ����� ��� ������������� �������� ��������)
     */
    this.getSession = function () {
        if (this.session==0){
            this.session = Math.floor( Math.random() * (999999 - 100000 + 1) ) + 100000; // �� m �� n
        }
        return this.session;
    }
    
    /**
    * �������� ���������� (�������, � ��������, ...)
    * @returns {undefined}
    */
    this.refreshStatistics = function () {
        var i = 0;
        while (i<=6) {
            if (this.chkStatObj[i]) this.chkStatObj[i].text(this.stat[i]);
            i++;
        }
        this.chk_positionObj.text(this.pos);
        this.chk_countObj.text(this.count);
        this.chk_percentObj.text(parseInt(this.pos/this.count*100));
    }
    
    /**
     * ����������� � ���� ����
     */
    this.displayMessage = function (html) {
        if (this.chk_logObj) {
            this.chk_logObj.prepend("<p>"+html+"</p>");
        } else {
            alert('Error - "chk_logObj" is not Object!');
        }
    }
    
    /**
     * ³��������� ������� �������� �� ����
     */
    this.setProgress = function (part) { // part = {0..1)
        if (this.progress_barObj) {
            var new_width = parseInt(500*part); // 500 - ������ ������������ �����
            if (isNaN(new_width)) new_width = 0;
            this.progress_barObj.attr('width',new_width);
        } else {
            alert('Error - "progress_barObj" is not Object!');
        }
    }
    
    /**
     * ��������� ���������� ��� ��������
     */
    this.resultsAdd = function (sid, err, codetype) {
        sid = parseInt(sid); if (isNaN(sid)) sid = 0;
        err = parseInt(err); if (isNaN(err)) err = 5;
        codetype = parseInt(codetype); if (isNaN(codetype)) err = 0;
        this.results[this.results_count] = {"sid":sid,"err":err,"codetype":codetype};
        this.results_count++;
    }
    
    /**
     * ���������� ���������� �������� �� ������
     */
    this.resultsSave = function () {
        var path = '/index.php?do=videoconstructor&action=check-save';
        //alert('save: '+print_r(this.results)); return;
        $.post(path, {"results":this.results,"check_session":this.getSession()}, function(data_text) {
            // ����� ������
            //alert('resultsSave DATA:'+data_text); return '{%loader(ID_TEXT)%}';
            if (data_text=='OK') {
               vChecker.displayMessage ('<b style="color:green">���������� ����������� ������ - ������� ('+vChecker.results_count+' ��.).</b>');
               vChecker.results_count = 0;
               vChecker.results = {};
               setTimeout(vChecker.objGlobalName+'.checkNext();', vChecker.delay_ms);
            } else {
               vChecker.displayMessage ('<b style="color:red">���������� ����������� ������ - ��� ������ �� �������, �����������.</b>');
               setTimeout(vChecker.objGlobalName+'.resultsSave();', vChecker.delay_ms*5);
            }
        });
    }
    
    /**
     * �������� ���������� ����
     */
    this.checkNext = function () {
        if (this.pos<this.count) {
            var path = '/index.php?do=videoconstructor&action=check';
            var resetdb = 0;
            if (this.is_first_check==1) {
                this.is_first_check = 0;
                if ($('#vChecker_resetdb').prop("checked")) {
                    resetdb = 1;
                }
            }
            $.post(path, {"what":this.items[this.pos],"resetdb":resetdb,"check_session":this.getSession()}, function(data) {
                // �������� ������ �����������
                vChecker.checkNextHandler(data, $this.items[$this.pos]);
            });
            
        } else {
            if (this.results_count>0) {
                this.resultsSave();
            } else {
                this.displayMessage ('<b style="color:darkgreen">�������� ���������!</b>');
            }
        }
    }
    
    /**
     * ���������� ������� checkNext
     */
    this.checkNextHandler = function (data_text, what) {
        var data = $.parseJSON(data_text);
        var suf = '[<b style="color:darkgray"><a href="/admin.php?mod=editnews&action=editnews&id='+what.post_id+'#Vcs'+what.id+'" target="_blank">'+what.sname+'</a></b>]';
        //alert('checkNext DATA_text:'+data_text);
        // ����� ������
        if ($.type(data)!=='object') {
             // ������� ���������, �����������
             this.displayMessage ('<span style="color:red"><b>'+(this.pos+1)+'</b> ������������ �����: </span><br /><code>'+data_text+'</code>');
             setTimeout(this.objGlobalName+'.checkNext();', this.delay_ms*5);
        } else {
            
            var answer_int = parseInt(data.err);
            if (!isNaN(answer_int)) {
                // ��������� �������� ������
                switch (answer_int) {
                    case 0: var answer_text = '<span style="color:green">��� ������</span>'; break;
                    case 1: var answer_text = '<span style="color:red">�������</span>'; break;
                    case 2: var answer_text = '<span style="color:red">����������� tube</span>'; break;
                    case 3: var answer_text = '<span style="color:red">��������� ������</span>'; break;
                    case 4: var answer_text = '<span style="color:red">�������� ��� tube �� ��������������</span>'; break;
                    case 5: var answer_text = '<span style="color:red">����������� ������ ��� ��������</span>'; break;
                    case 6: var answer_text = '<span style="color:red">��� ���� �� ����������</span>'; break;
                    case 51: var answer_text = '<span style="color:red">������ �������� / ���������������� tube</span>'; break;
                    case 52: var answer_text = '<span style="color:red">������ �������� �� ������� PHP</span>'; break;
                    case 53: var answer_text = '<span style="color:red">��� ������ �� �������/�������</span>'; break;
                    case 11: var answer_text = '<span style="color:red">��������� �����������</span>'; break;
                    default: var answer_text = '<span style="color:red">unknown #0</span>'; break;
                }
                this.displayMessage ('<b>'+(this.pos+1)+'</b> ����� OK:'+answer_int+' ('+answer_text+') // [���: '+data.codetype_name+'] '+suf);
                // ��������� ��������� � �����
                if (answer_int>0) {
                    var answer_int_simple = parseInt(answer_int.toString()); // .substr(0,1)
                    this.resultsAdd (this.items[this.pos]['id'], answer_int_simple, data.codetype);
                }
            } else {
                this.displayMessage ('<b>'+(this.pos+1)+'</b> <span style="color:red">����������� ������ ��� �������� (isNaN())</span> // '+suf);
                answer_int = 5;
                this.resultsAdd (this.items[this.pos]['id'], answer_int, 0);
            }
            
            this.pos++;
            this.stat[answer_int_simple]++;
            this.setProgress (this.pos/this.count);
            this.refreshStatistics();
            if (this.results_count>=this.results_count_tosend) {
                this.resultsSave();
            } else {
                setTimeout(this.objGlobalName+'.checkNext();', this.delay_ms);
            }
        }
    }
    return this;
}

/** ������ ����������� �������� */
$(document).ready(function(){
    /** ������ :: �������� �������� */
    $("#rl_action_mass").click(function(){
        if ($(".action_mass_com").val()=="delete") {
            var this_obj = $(this).prop("disabled",true);
            var sids = "";
            var obj = $(".checkbox_mass:checked");
            obj.each(function(){
                sids += (sids=="") ? $(this).val() : ","+$(this).val();
            });
            if (sids) {
                $.post("?mod=parser_constructor&sec=massdelete", {idlist:sids}, function(data){
                    this_obj.prop("disabled",false);
                    if (data=="OK") {
                        obj.each(function(){
                            $(this).parent().parent().next().remove().end().remove();
                        });
                    } else {
                        alert(data);
                    }
                });
            } else
               this_obj.prop("disabled",false);
        }
        return false;
    });
    
    /** ��������� :: ����������� �������� */
    $('#vk_config\\[quality_prefix\\]').change(function(){
        if ($(this).val() == 'none') {
            $('#vk_config\\[quality\\]').prop("disabled", true);
        } else {
            $('#vk_config\\[quality\\]').prop("disabled", false);
        }
        return false;
    });
    
    // ���������� �������� :: �������� ��������
    $("#checkbox_mass").click(function(){
        if ($(this).prop("checked"))
            $(".checkbox_mass").prop("checked", true);
        else
            $(".checkbox_mass").prop("checked", false);
    });
    
    // ���������� �������� :: ��������
    $(".vc_do_delete").click(function(){
        var this_obj = $(this);
        var sid = this_obj.prop("disabled",true).attr('data-sid');
        var shvar = "{%loader(ID)%}";
        if (sid) {
            $.post("?mod=parser_constructor&sec=massdelete&what=errors", {idlist:sid}, function(data){
                this_obj.prop("disabled",false);
                if (data=="OK") {
                    $("#vc_error_"+sid).next().remove().end().remove();
                } else {
                    alert(data);
                }
            });
        }
        return false;
    });
    // ���������� �������� : �������� ��������
    $(".do_action_mass").click(function(){
        if ($(".action_mass_com").val()=="delete") {
            var this_obj = $(this).prop("disabled",true);
            var sids = "";
            var obj = $(".checkbox_mass:checked");
            obj.each(function(){
                sids += (sids=="") ? $(this).val() : ","+$(this).val();
            });
            if (sids) {
                $.post("?mod=parser_constructor&sec=massdelete&what=errors", {idlist:sids}, function(data){
                    this_obj.prop("disabled",false);
                    if (data=="OK") {
                        obj.each(function(){
                            $(this).parent().parent().next().remove().end().remove();
                        });
                    } else {
                        alert(data);
                    }
                });
            } else
               this_obj.prop("disabled",false);
        }
        return false;
    });
    
});

function print_r(arr, level) {
    var print_red_text = "";
    if(!level) level = 0;
    var level_padding = "";
    for(var j=0; j<level+1; j++) level_padding += "    ";
    if(typeof(arr) == 'object') {
        for(var item in arr) {
            var value = arr[item];
            if(typeof(value) == 'object') {
                print_red_text += level_padding + "'" + item + "' :\n";
                print_red_text += print_r(value,level+1);
		} 
            else 
                print_red_text += level_padding + "'" + item + "' => \"" + value + "\"\n";
        }
    } 

    else  print_red_text = "===>"+arr+"<===("+typeof(arr)+")";
    return print_red_text;
}