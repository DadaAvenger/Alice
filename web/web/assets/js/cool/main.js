var Main = {
Url : Config.Url,
    SessionId: Config.SessionId,
    Uid : Config.Uid,
    Uname : Config.Uname,
    init : function(){
        this.CheckSession();
        $("#logout").click(function(){
            sessionStorage.clear();
            location.reload();
        });
    },

    CheckSession : function(){
        if (this.SessionId && this.Uid){
            $(".username").html(this.Uname);
        } else {
            window.location.href = Main.Url+'web/web/login.html';
        }
    },
};
Main.init();