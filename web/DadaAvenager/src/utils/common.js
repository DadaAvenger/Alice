import Vue from 'vue'
import axios from 'axios'
import ElementUI from 'element-ui';
import 'element-ui/lib/theme-chalk/index.css';

Vue.use(ElementUI);
axios.defaults.headers.post['Content-Type'] = 'application/x-www-form-urlencoded;charset=UTF-8';
axios.defaults.baseURL = process.env.API_ROOT;
axios.defaults.crossDomain = true;
axios.defaults.withCredentials = true
Vue.prototype.httpHeader = process.env.API_ROOT;
if (location.hostname == 'store.zhiyun-tech.com') {
} else if (location.hostname.indexOf("2.101") > -1) {
    Vue.prototype.env = "test";
    Vue.prototype.httpHeader = "http://172.16.2.101:3002/";
    axios.defaults.baseURL = "http://172.16.2.101:3002/";
}

var filtersFun = (defaultfilters) => {
    let dataTableData =  JSON.parse(localStorage.getItem('DataTables_DataTables_Table_0_' + location.pathname))
    return dataTableData ? dataTableData.filters : defaultfilters
};

export const comConfirm = (msg="",status="warning",showCancel=true,callback=()=>{})=>{
    ElementUI.MessageBox.confirm(msg, "提示", {
        confirmButtonText: "确定",
        cancelButtonText: "取消",
        closeOnClickModal:false,
        showCancelButton: showCancel,
        type: status
      }).then(() => {
        callback()
      })
      .catch(() => {});
}

export const comMessage = (msg="",status="error")=>{
    ElementUI.Message({
        message: msg,
        type: status,
        duration: 2000,
        showClose: true,
        offset:60,
        customClass: "my-el-message"
    });
}
export const queryInfoLocalStorage = (queryInfo)=>{
    let dataTableName = 'DataTables_DataTables_Table_0' + '_' + location.pathname
    if (queryInfo.type == "init") {
        if (localStorage.getItem(dataTableName)) {
            queryInfo = JSON.parse(localStorage.getItem(dataTableName))
        } else {
            localStorage.setItem(
                'DataTables_DataTables_Table_0' + '_' + location.pathname,
                JSON.stringify(queryInfo)
            )
        }
    }
    else {
        localStorage.setItem(
            'DataTables_DataTables_Table_0' + '_' + location.pathname,
            JSON.stringify(queryInfo)
        )
    }
    return queryInfo
}
export const pickerOptions = {
    shortcuts: [{
        text: '最近一周',
        onClick(picker) {
            const end = new Date();
            const start = new Date();
            start.setTime(start.getTime() - 3600 * 1000 * 24 * 7);
            picker.$emit('pick', [start, end]);
        }
    }, {
        text: '最近一个月',
        onClick(picker) {
            const end = new Date();
            const start = new Date();
            start.setTime(start.getTime() - 3600 * 1000 * 24 * 30);
            picker.$emit('pick', [start, end]);
        }
    }, {
        text: '最近三个月',
        onClick(picker) {
            const end = new Date();
            const start = new Date();
            start.setTime(start.getTime() - 3600 * 1000 * 24 * 90);
            picker.$emit('pick', [start, end]);
        }
    }]
}
