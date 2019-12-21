var Calculator = {
    // Url : Config.Url,
    // SessionId: Config.SessionId,
    // Uid : Config.Uid,
    // Uname : Config.Uname,
    init : function(){
        this.getAccountType();

        this.SetChart(Config.Uid);
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
    },
    SetChart: function () {
        var url = Config.Url+'/api/api.php';
        $.ajax({
            url:url,
            type:'POST',
            data:{
                'action': 'dailyPay',
                'opt':'getColumnChart',
                'uid': Config.Uid
            },
            dataType:'json',
            success: function(data) {
                // console.log(data);
                var dom = document.getElementById("donut-example2");
                var myChart = echarts.init(dom);
                var app = {};

                // data['data']['legend'] = ['直接访问', '邮件营销','联盟广告','视频广告','搜索引擎'];
                var tes = data['data']['legend'];
                // var tes = ['直接访问', '邮件营销','联盟广告','视频广告','搜索引擎'];
                var tt = JSON.stringify(data['data']['data']);

                // option = {
                //     tooltip : {
                //         trigger: 'axis',
                //         axisPointer : {            // 坐标轴指示器，坐标轴触发有效
                //             type : 'shadow'        // 默认为直线，可选为：'line' | 'shadow'
                //         }
                //     },
                //     legend: {
                //         data: tes
                //     },
                //     grid: {
                //         left: '3%',
                //         right: '4%',
                //         bottom: '3%',
                //         containLabel: true
                //     },
                //     xAxis:  {
                //         type: 'value'
                //     },
                //     yAxis: {
                //         type: 'category',
                //         data: data['data']['category']
                //     }
                // };
                option = {
                    tooltip : {
                        trigger: 'axis',
                        axisPointer : {            // 坐标轴指示器，坐标轴触发有效
                            type : 'shadow'        // 默认为直线，可选为：'line' | 'shadow'
                        }
                    },
                    legend: {
                        data: tes
                    },
                    grid: {
                        left: '3%',
                        right: '4%',
                        bottom: '3%',
                        containLabel: true
                    },
                    xAxis : [
                        {
                            type : 'category',
                            data: data['data']['category']
                        }
                    ],
                    yAxis : [
                        {
                            type : 'value'
                        }
                    ],
                    series: data['data']['data']
                };
                // console.log(data['data']['data']);
                // option.series.push(data['data']['data']);

                var series = option["series"];
                var fun = function (params) {
                    var data3 =0;
                    for(var i=0,l=series.length;i<l;i++){
                        data3 += series[i].data[params.dataIndex][1]
                    }
                    return data3
                };
                //加载页面时候替换最后一个series的formatter
                series[series.length-1]["label"]["normal"]["formatter"] = fun;
                // 使用刚指定的配置项和数据显示图表。
                myChart.setOption(option);

                //legend点击事件，根据传过来的obj.selected得到状态是true的legend对应的series的下标，再去计算总和
                myChart.on("legendselectchanged", function(obj) {
                    var b = obj.selected
                        , d = [];
                    //alert(JSON.stringify(b))
                    for(var key in b){
                        if(b[key]){
                            //alert(key)
                            for(var i=0,l=series.length;i<l;i++){
                                var changename = series[i]["name"];
                                if(changename == key){
                                    d.push(i);//得到状态是true的legend对应的series的下标
                                }
                            }
                        }
                    }
                    var fun1 = function (params) {
                        var data3 =0;
                        for(var i=0,l=d.length;i<l;i++){
                            for(var j=0,h=series.length;j<h;j++){
                                if(d[i] == j){
                                    data3 += series[j].data[params.dataIndex][1] //重新计算总和
                                }
                            }
                        }
                        return data3
                    };
                    series[series.length-1]["label"]["normal"]["formatter"] = fun1;
                    myChart.setOption(option);

                });
                console.log(option);

                // if (option && typeof option === "object") {
                //     myChart.setOption(option, true);
                // }
            }
        });
    }

};
Calculator.init();  