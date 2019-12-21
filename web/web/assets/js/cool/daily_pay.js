var Daily = {
    Url : Config.Url,
    SessionId: Config.SessionId,
    Uid : Config.Uid,
    Uname : Config.Uname,
    init : function(){
        Daily.GetDailyPay();
        //添加记录
        $("#quick-access .btn-add").click(function(){
            Daily.setPage('add');
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
                Daily.GetDailyPay(Daily.Uid);
            break;
            case 'add':
                Daily.AddDailyPay(Daily.Uid);
            break;
            // case 'change':
            //     Daily.ChangeDailyPay(Daily.Uid);
            // break;
            default:
                break;
        }
    },

    GetDailyPay : function(searchDate){
        if (!this.Uid) return false;
        var url = this.Url+'/api/api.php';
        $.ajaxSetup({ 
            async : false 
        });    
        $.post(url, {
            action: 'dailyPay',
            opt : 'getDailyPay',
            uid : this.Uid,
            date : searchDate,
            sessionid:this.SessionId
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

    AddDailyPay : function(userId){
        if (!userId) return false;
        var cost = $("input[name='cost']").val();
        var mark = $("input[name='mark']").val();
        var use = $("input[ name='use' ]").val();
        var type = $("#type option:selected") .val();
        if (!use || !cost) {
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
            cost : cost,
            use   : use,
            type  : type, 
            sessionid:this.SessionId
        }, function(data, status){
            if (data['ret'] != -1){
                $("#quick-access").css("bottom","-200px");
                var tr = $("#dailyPay-table>tr").html();
                var sign = '<td valign="top" colspan="3" class="dataTables_empty">No data available in table</td>';
                if (tr==sign) {
                    $("#dailyPay-table").html("");
                }
                $("#dailyPay-table").prepend(tableRow);
                Daily.setPage('personal');
            }
        }, 'json');
    }

    // //修改状态
    // ChangeDailyPay : function(userId){
    //     if (!userId) return false;
    //     var cost = $(this).attr("v");
    //     // var mark = $(this).html();
    //     console.log(this);
    //     alert(cost);
    //     return;
    //     if (!use || !cost) {
    //         alert('请填写信息');
    //         return;
    //     }
    //     var url = Daily.Url+'/cool/api/api.php';
    //     $.ajaxSetup({ 
    //         async : false 
    //     });    

    //     $.post(url, {
    //         action: 'dailyPay',
    //         opt   : 'addDailyPay',
    //         uid   : userId,
    //         cost : cost,
    //         use   : use,
    //         type  : type, 
    //         sessionid:this.SessionId
    //     }, function(data, status){
    //         if (data['ret'] != -1){
    //             $("#quick-access").css("bottom","-200px");
    //             var tr = $("#dailyPay-table>tr").html();
    //             var sign = '<td valign="top" colspan="3" class="dataTables_empty">No data available in table</td>';
    //             if (tr==sign) {
    //                 $("#dailyPay-table").html("");
    //             }
    //             $("#dailyPay-table").prepend(tableRow);
    //             Daily.setPage('personal');
    //         }
    //     }, 'json');
    // }
}
Daily.init();
