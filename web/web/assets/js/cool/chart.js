var Calculator = {
    // Url : Config.Url,
    // SessionId: Config.SessionId,
    // Uid : Config.Uid,
    // Uname : Config.Uname,
    init : function(){

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

    SetChart: function () {
        var url = Config.Url+'/api/api.php';
        $.ajax({
            url:url,
            type:'POST',
            data:{
                'action': 'dailyPay',
                'opt':'getChart',
                'uid': Config.Uid
            },
            dataType:'json',
            success: function(data) {
                var dom = document.getElementById("donut-example2");
                var myChart = echarts.init(dom);
                var app = {};

                // data['data']['legend'] = ['直接访问', '邮件营销','联盟广告','视频广告','搜索引擎'];
                var tes = data['data']['legend'];
                // var tes = ['直接访问', '邮件营销','联盟广告','视频广告','搜索引擎'];
                var series = JSON.stringify(data['data']['data']);

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
                    xAxis:  {
                        type: 'value'
                    },
                    yAxis: {
                        type: 'category',
                        data: data['data']['category']
                    }
                };
                option.push(data['data']['data']);
                console.log(option);

                if (option && typeof option === "object") {
                    myChart.setOption(option, true);
                }
            }
        });
    }

}
Calculator.init();  