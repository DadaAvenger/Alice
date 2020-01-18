var Main = {
    Url : Config.Url,
    SessionId: Config.SessionId,
    Uid : Config.Uid,
    Uname : Config.Uname,
    init : function(){
        this.CheckSession();
        $("#logout").click(function(){
            var url = Config.Url+'/api/api.php';
            $.post(url, {action: 'index', opt : 'logout'}, function(data, status){
                if (data['ret'] != -1){
                    console.log(data);
                }
                }, 'json');
            sessionStorage.clear();
            location.reload();
        });
    },

    CheckSession : function(){
        if (this.SessionId && this.Uid){
            $("#username").html(this.Uname);
        } else {
            window.location.href = Main.Url+'web/phone/login.html';
        }
    },
};
Main.init();