var Daily = {
    Url : Config.Url,
    SessionId: Config.SessionId,
    Uid : Config.Uid,
    Uname : Config.Uname,
    init : function(){
        Daily.GetDailyPay();
        Daily.getAccountType();
        //添加记录
        $("#submitBtn").click(function(){
            Daily.setPage('add');
        });
        $('#tmonth').bind('change',function(){
            Daily.GetDailyPay();
        });

        //修改状态
        $(".gradeX>.description,.money").click(function(){
            var did = $(this).attr("did");
            var name = $(this).attr("n");
            var value = $(this).attr("v");
            $(this).html(`
                <input type="text" name="`+name+`" value="`+value+`" style="width:50px;height:20px;" />
            `);
            // Daily.setPage('change');
        });
    },

    setPage : function(page){
        switch(page){
            case 'personal':
                // $('#get-cost-click').click();
                Daily.GetDailyPay();
            break;
            case 'add':
                Daily.AddDailyPay();
            break;
            // case 'change':
            //     Daily.ChangeDailyPay(Daily.Uid);
            // break;
            default:
                break;
        }
    },

    GetDailyPay : function(){
        if (!this.Uid) return false;
        var searchDate = $('#tmonth').val();
        var url = this.Url+'/api/api.php';
        $.ajaxSetup({ 
            async : false 
        });    
        $.post(url, {
            action: 'dailyPay',
            opt : 'getPhoneDailyPay',
            date : searchDate,
            sessionid:this.SessionId
        }, function(data, status){
            if (status == "success"){
                if (data['data']){
                    var allData = data['data'];
                    var arr = allData['data'];
                    var str = `<!--<p style="color:red" class="big-money"> ${arr['total_pay']}</p>-->`;
                    $("#total-pay").html(allData['total_pay']);
                    $("#daily-data").html('');
                    for (var i in arr){
                        var tableRow = `
                            <div class="hui-list-text">
                                <div class="hui-date money-total-one">
                                    ${arr[i]['date']}
                                </div>
                                <div class="hui-date money-total-two" style="">
                                    支出：${arr[i]['pay']}&nbsp;&nbsp;收入：${arr[i]['income']}
                                </div>
                            </div> `;
                        var dayData = arr[i]['data'];
                        for (var d in dayData) {
                            if (d == 'shuffle') break;
                            tableRow += `
                                <a onclick="">
                                    <div class="hui-list-text item-detal">
                                        <div class="money-item-one">
                                            ${dayData[d]['mark']}
                                        </div>`;
                                if (dayData[d]['type'] == 30) {
                                    tableRow += `
                                    <div class = "money-item-two" style = "color: green;" >+${dayData[d]['money']}
                                    `;
                                } else {
                                    tableRow += `
                                    <div class = "money-item-two" style = "color: red;" >-${dayData[d]['money']}
                                    `;
                                }
                            tableRow += `</div>
                                    </div>
                                </a>
                            `;
                        }
                        $("#daily-data").append(tableRow);
                    }
                }
            }
        }, 'json');
    },

    AddDailyPay : function(userId){
        var cost = $("#cost").val();
        var mark = $("#mark").val();
        var date = $("#nowDate").val();
        var type = $("#source").val();
        if (!cost) {
            alert('请填写信息');
            return;
        }
        var url = Daily.Url+'/api/api.php';
        $.ajaxSetup({ 
            async : false 
        });    

        $.post(url, {
            action: 'dailyPay',
            opt   : 'addDailyPay',
            uid   : userId,
            money : cost,
            type  : type,
            mark : mark,
            date : date,
            sessionid:this.SessionId
        }, function(data, status){
            if (data['ret'] != -1){
                // $("#quick-access").css("bottom","-200px");
                // var tr = $("#dailyPay-table>tr").html();
                // var sign = '<td valign="top" colspan="3" class="dataTables_empty">No data available in table</td>';
                // if (tr==sign) {
                //     $("#dailyPay-table").html("");
                // }
                // $("#dailyPay-table").prepend(tableRow);
                Daily.setPage('personal');
            }
        }, 'json');
    },

    getAccountType : function(){
        var url = Config.Url+'/api/api.php';

        $.ajaxSetup({
            async : false
        });
        $.post(url, {
            action: 'dailyPay',
            opt : 'getAccountType',
            sessionid:Config.SessionId
        }, function(data, status){
            if (status == "success"){
                if (data){
                    var arr = data['data'];
                    $("#source").html('');
                    for (var i in arr){
                        $("#source").append(`<option value="${arr[i]['id']}" >${arr[i]['name']}</option>`);
                    }
                }
            }
        }, 'json');
    }

}
Daily.init();
