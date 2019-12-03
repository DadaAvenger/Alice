import Vue from 'vue'

import 'normalize.css/normalize.css' // A modern alternative to CSS resets

import ElementUI from 'element-ui'
import moment from 'moment'
import 'element-ui/lib/theme-chalk/index.css'
import locale from 'element-ui/lib/locale/lang/en' // lang i18n

import '@/styles/index.scss' // global css
import '@/styles/reset.scss' // global css
import '@/styles/table.scss' // global css

import App from './App'
import store from './store'
import router from './router'

import '@/icons' // icon
import '@/permission' // permission control

import { DataTablesServer } from 'vue-data-tables/dist/data-tables.server'
import {initDataTableData,queryInfoLocalStorage,pickerOptions,comMessage,comConfirm} from './utils/common'
/**
 * If you don't want to use mock-server
 * you want to use MockJs for mock api
 * you can execute: mockXHR()
 *
 * Currently MockJs will be used in the production environment,
 * please remove it before going online! ! !
 */
// import { mockXHR } from '../mock'
// if (process.env.NODE_ENV === 'production') {
//   mockXHR()
// }

// set ElementUI lang to EN
Vue.use(ElementUI, { locale })
Vue.use(DataTablesServer);
// 如果想要中文版 element-ui，按如下方式声明
// Vue.use(ElementUI)

Vue.config.productionTip = false
Vue.prototype.moment = moment;
Vue.prototype.initDataTableData = initDataTableData
Vue.prototype.pickerOptions = pickerOptions
Vue.prototype.queryInfoLocalStorage = queryInfoLocalStorage
Vue.prototype.comMessage = comMessage;
Vue.prototype.comConfirm = comConfirm;

new Vue({
  el: '#app',
  router,
  store,
  render: h => h(App)
})
