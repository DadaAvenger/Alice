var Login = {
    Url : Config.Url,
    SessionId: Config.SessionId,
    Uid : Config.Uid,
    Uname : Config.Uname,
    init : function(){
        this.CheckSession();

        $("#logout").click(function(){
            var url = this.Url+'/api/api.php';
            $.post(url, {action: 'index', opt : 'logout'}, function(data, status){
            }, 'json');
            sessionStorage.clear();
            location.reload();
        });
    },

    setPage : function(page){
        switch(page){
            case 'index':
                window.location.href = Login.Url+'web/phone/tab.html';
                // Personal.setPage('personal');
            break;
            default:
                break;
        }
    },

    Login : function(){
        var uname = $("input[name='username']").val();
        var pword = $("input[name='password']").val(); 
        var url = this.Url+'/api/api.php';
        $.post(url, {action: 'index', opt : 'login', userName : uname, passWord : pword}, function(data, status){
            if (data['ret'] != -1){
                console.log(data);
                sessionStorage.setItem('SessionId', data['data']['sessionid']);
                sessionStorage.setItem('Uid', data['data']['uid']);
                sessionStorage.setItem('Uname', data['data']['username']);

                Login.setPage('index');
            } else {
                alert('账户信息有误');
            }
        }, 'json');
    },

    CheckSession : function(){
        // this.SessionId = sessionStorage.getItem('SessionId');
        // this.Uid = sessionStorage.getItem('Uid');
        if (this.SessionId && this.Uid){
            console.log('logined');
            Login.setPage('index');
        } else {
            $("#login-submit").click(function(){
                Login.Login();
            });
        }
    },
}
Login.init();