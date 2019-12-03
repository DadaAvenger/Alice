<template>
  <div class="wscn-http404-container">
    <div class="wscn-http404">
      <div class="pic-404">
        <img class="pic-404__parent" src="@/assets/404_images/404.jpg" alt="404">
        <!-- <img class="pic-404__child left" src="@/assets/404_images/404_cloud.png" alt="404">
        <img class="pic-404__child mid" src="@/assets/404_images/404_cloud.png" alt="404">
        <img class="pic-404__child right" src="@/assets/404_images/404_cloud.png" alt="404"> -->
        <div class="pic-bottom">
          <a href="/" style="margin-right:65px"><svg-icon icon-class="return" style="vertical-align: -0.1em;" />返回首页</a>
          <a href="javascript:void(0)" @click="()=>{$router.go(0)}"><svg-icon icon-class="refresh" />刷新一下</a>
        </div>
      </div>
    </div>
  </div>
</template>

<script>

export default {
  name: 'Page404',
  computed: {
    message() {
      return '网站管理员说你不能进入这个页面…'
    }
  }
}
</script>

<style lang="scss" scoped>
.pic-bottom{
  font-size: 14px;
  color: green;
}
.pic-bottom>a{
}
.pic-bottom .svg-icon{
  margin-right: 5px;
}
.wscn-http404-container{
  transform: translate(-50%,-50%);
  position: absolute;
  width: 100%;
  top: 40%;
  left: 50%;
}
.pic-bottom{
  width: 100%;
  text-align: center;
  position: absolute;
  bottom:120px;
  left: 0px;
}
.pic-bottom a{
  cursor: pointer;
}
.wscn-http404 {
  position: relative;
  width: 100%;
  overflow: hidden;
  .pic-404 {
    width: 100%;
    margin: 0 auto;
    overflow: hidden;
    &__parent {
      width: 100%;
    }
    &__child {
      position: absolute;
    }
  }
  .bullshit {
    position: relative;
    float: left;
    width: 300px;
    padding: 30px 0;
    overflow: hidden;
    &__oops {
      font-size: 32px;
      font-weight: bold;
      line-height: 40px;
      color: #1482f0;
      opacity: 0;
      margin-bottom: 20px;
      animation-name: slideUp;
      animation-duration: 0.5s;
      animation-fill-mode: forwards;
    }
    &__headline {
      font-size: 20px;
      line-height: 24px;
      color: #222;
      font-weight: bold;
      opacity: 0;
      margin-bottom: 10px;
      animation-name: slideUp;
      animation-duration: 0.5s;
      animation-delay: 0.1s;
      animation-fill-mode: forwards;
    }
    &__info {
      font-size: 13px;
      line-height: 21px;
      color: grey;
      opacity: 0;
      margin-bottom: 30px;
      animation-name: slideUp;
      animation-duration: 0.5s;
      animation-delay: 0.2s;
      animation-fill-mode: forwards;
    }
    &__return-home {
      display: block;
      float: left;
      width: 110px;
      height: 36px;
      background: #1482f0;
      border-radius: 100px;
      text-align: center;
      color: #ffffff;
      opacity: 0;
      font-size: 14px;
      line-height: 36px;
      cursor: pointer;
      animation-name: slideUp;
      animation-duration: 0.5s;
      animation-delay: 0.3s;
      animation-fill-mode: forwards;
    }
    @keyframes slideUp {
      0% {
        transform: translateY(60px);
        opacity: 0;
      }
      100% {
        transform: translateY(0);
        opacity: 1;
      }
    }
  }
}
</style>
