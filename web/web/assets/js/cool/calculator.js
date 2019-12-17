var Calculator = {
    // Url : Config.Url,
    // SessionId: Config.SessionId,
    // Uid : Config.Uid,
    // Uname : Config.Uname,
    init : function(){
        $(".reload").click(function(){
            Calculator.GetBalance();
        });

        $("#quick-access .btn-add").click(function(){
            Calculator.AddDailyPay();
        });

        var clickNum = 0;
        $("#month_budget").on('click', function(){
            clickNum++;
            setTimeout(function () {
                clickNum = 0;
            }, 500);
            if (clickNum > 1) {
                Calculator.SetInput(this);
                clickNum = 0;
            }
        });
        
        this.GetBalance(Config.Uid);
        this.GetDailyPay();

        setTimeout("Calculator.TimeLimit()",1000);
    },

    setPage : function(page){
        switch(page){
            // case 'personal':
            //     $('#get-cost-click').click();
            //     Personal.GetDailyPay(Config.Uid);
            // break;
            // case 'add':
            //     Personal.AddDailyPay(Config.Uid);
            // break;
            // default:
            //     break;
        }
    },

    SetInput :function(obj){
        var id = $(obj).attr('id');
        var text = $('#'+id).html();
        var input = $("<input type='text' style='width:80px;'>").val(text);
        $('#'+id).html(input);
        input.blur(function(){
           var newVal = $(this).val();
           Calculator.EditBalance(this, newVal, id);
        });
    },

    GetBalance : function(){
        if (!Config.Uid) return false;
        var url = Config.Url+'/api/api.php';
        $.ajaxSetup({ 
            async : false 
        });    
        $.post(url, {action: 'balance', opt : 'getBalance', uid : Config.Uid}, function(data, status){
            if (status == "success"){
                if (data){
                    // console.log(data);
                    var arr = data['data'];
                    $("#balance").html(arr['balance']);
                    $("#month_budget").html(arr['month_budget']);
                    $("#month_balance").html(arr['month_balance']);
                    $("#rest_day").html(arr['rest_day']);
                }
            }
        }, 'json');
    },


    EditBalance : function(obj, newVal, id){
       var url = Config.Url+'/api/api.php';
       $.ajax({
            url:url,
            type:'POST',
            data:{
                'action': 'balance',
                'opt':'editBalance',
                'month_budget':newVal
            },
            dataType:'json',
            success:function(res){
                if(res['data']){
                    $(this).remove();
                    Calculator.GetBalance();
                    // $('#'+id).html(newVal);
                }
            }
        });
    },

    GetDailyPay : function(){
        if (!Config.Uid) return false;
        var url = Config.Url+'/api/api.php';
        var startDate = $("input[name='startDate']").val();
        var endDate = $("input[name='endDate']").val();

        $.ajaxSetup({ 
            async : false 
        });    
        $.post(url, {
            action: 'dailyPay',
            opt : 'getDailyPay',
            uid : Config.Uid,
            startDate : startDate,
            endDate : endDate,
            sessionid:Config.SessionId
        }, function(data, status){
            if (status == "success"){
                if (data){
                    var arr = data['data'];
                    $("#dailyPay-table").html('');
                    for (var i in arr){
                        var tableRow = `
                            <tr class="odd gradeX">
                                <td>${arr[i]['addtime']}</td>
                                <td class="description" n="description" v="${arr[i]['description']}" id="${arr[i]['id']}">${arr[i]['description']}</td>
                                <td class="money" n="money" v="${arr[i]['money']}" did="${arr[i]['id']}">${arr[i]['money']}</td>
                            </tr>
                        `;
                        $("#dailyPay-table").append(tableRow);
                    }
                }
            }
        }, 'json');
    },

    AddDailyPay : function(){
        if (!Config.Uid) return false;
        var cost = $("input[name='cost']").val();
        var mark = $("input[name='mark']").val();
        var use = $("input[ name='use' ]").val();
        var budget = $("#budget option:selected") .val();
        var type = $("#type option:selected") .val();
        if (!use || !cost) {
            alert('请填写信息');
            return;
        }
        var url = Config.Url+'/api/api.php';
        $.ajaxSetup({ 
            async : false 
        });    

        $.post(url, {
            action: 'dailyPay',
            opt   : 'addDailyPay',
            uid   : Config.Uid,
            cost : cost,
            use   : use,
            type  : type, 
            budget  : budget, 
            sessionid:Config.SessionId
        }, function(data, status){
            if (data['ret'] != -1){
                $("#quick-access").css("bottom","-200px");
                var tr = $("#dailyPay-table>tr").html();
                var sign = '<td valign="top" colspan="3" class="dataTables_empty">No data available in table</td>';
                if (tr==sign) {
                    $("#dailyPay-table").html("");
                }
                Calculator.GetDailyPay();
                Calculator.GetBalance();
            }
        }, 'json');
    },

    TimeLimit : function() {
            var time_now_server,time_now_client,time_end,time_server_client;
            today = new Date(); //系统当前时间
            intYears = today.getFullYear(); //得到年份,getFullYear()比getYear()更普适
            intMonths = today.getMonth() + 1; //得到月份，要加1
            intDays = today.getDate(); //得到日期

            time_end=new Date(intYears+"/"+intMonths+"/"+intDays+" 18:00:0");//结束的时间
            time_end=time_end.getTime();//获取的是毫秒
     
            time_now_server=new Date();//开始的时间
            time_now_server=time_now_server.getTime();
            var timer = document.getElementById("rest_time");
            if(!timer){
                return ;
            }
            timer.innerHTML =time_now_server;
 
            var time_now,time_distance,str_time;
            var int_day,int_hour,int_minute,int_second;
            var time_now=new Date();
            time_now=time_now.getTime();
            time_distance=time_end-time_now;
            if(time_distance>0)
            {
                int_day=Math.floor(time_distance/86400000)
                time_distance-=int_day*86400000;
                int_hour=Math.floor(time_distance/3600000)
                time_distance-=int_hour*3600000;
                int_minute=Math.floor(time_distance/60000)
                time_distance-=int_minute*60000;
                int_second=Math.floor(time_distance/1000)
 
                if(int_hour < 10)
                    int_hour="0"+int_hour;
                if(int_minute<10)
                    int_minute="0"+int_minute;
                if(int_second<10)
                    int_second="0"+int_second;
                str_time = int_hour+"hour  "+int_minute+"min  "+int_second+"s";
                timer.innerHTML=str_time;
                setTimeout("Calculator.TimeLimit()",1000);
            }
            else
            {
                timer.innerHTML =0;
            }

        }

}
Calculator.init();  