(window["webpackJsonp"]=window["webpackJsonp"]||[]).push([["chunk-0f82530e"],{"6e95":function(e,t,a){},9406:function(e,t,a){"use strict";a.r(t);var l=function(){var e=this,t=e.$createElement,a=e._self._c||t;return a("div",{staticClass:"dashboard-container"},[a("fieldset",{staticClass:"el-fieldset "},[a("div",{staticClass:"legend"},[e._v("查询条件")]),e._v(" "),a("el-form",{attrs:{inline:!0}},[a("el-form-item",{attrs:{label:"起始日期"}},[a("el-date-picker",{attrs:{type:"daterange",align:"left","unlink-panels":"","range-separator":"至","start-placeholder":"开始日期","end-placeholder":"结束日期","picker-options":e.pickerOptions},model:{value:e.filters[2].value,callback:function(t){e.$set(e.filters[2],"value",t)},expression:"filters[2].value"}})],1),e._v(" "),a("el-form-item",{attrs:{label:"金额范围"}},[a("el-input",{staticStyle:{width:"48%"},attrs:{onpropertychange:"this.value=this.value.replace(/[^0-9.]/g,'')",oninput:"this.value=this.value.replace(/[^0-9.]/g,'')"},model:{value:e.filters[3].value,callback:function(t){e.$set(e.filters[3],"value",t)},expression:"filters[3].value"}},[a("svg-icon",{staticClass:"el-input__icon",attrs:{slot:"prefix","icon-class":"money"},slot:"prefix"})],1),e._v("-\n        "),a("el-input",{staticStyle:{width:"48%"},attrs:{onpropertychange:"this.value=this.value.replace(/[^0-9.]/g,'')",oninput:"this.value=this.value.replace(/[^0-9.]/g,'')"},model:{value:e.filters[4].value,callback:function(t){e.$set(e.filters[4],"value",t)},expression:"filters[4].value"}},[a("svg-icon",{staticClass:"el-input__icon",attrs:{slot:"prefix","icon-class":"money"},slot:"prefix"})],1)],1),e._v(" "),a("el-form-item",{attrs:{label:"消费类型"}},[a("el-select",{attrs:{placeholder:"选择消费类型",value:""},model:{value:e.filters[1].value,callback:function(t){e.$set(e.filters[1],"value",t)},expression:"filters[1].value"}},[a("el-option",{attrs:{label:"选择消费类型",value:""}}),e._v(" "),a("el-option",{attrs:{label:"待签收",value:"1"}}),e._v(" "),a("el-option",{attrs:{label:"维修中",value:"0"}}),e._v(" "),a("el-option",{attrs:{label:"已寄出",value:"3"}}),e._v(" "),a("el-option",{attrs:{label:"待确认",value:"2"}})],1)],1)],1)],1),e._v(" "),a("el-row",{staticStyle:{"margin-bottom":"10px"},attrs:{gutter:10}},[a("el-col",{attrs:{lg:17}},[e._v(" ")]),e._v(" "),a("el-col",{attrs:{lg:5}},[a("el-input",{attrs:{"prefix-icon":"el-icon-search",placeholder:"输入消费关键字"},model:{value:e.filters[0].value,callback:function(t){e.$set(e.filters[0],"value",t)},expression:"filters[0].value"}})],1),e._v(" "),a("el-col",{attrs:{lg:2,align:"right"}},[a("el-button",{staticStyle:{width:"100%"},attrs:{type:"primary"},on:{click:function(){e.$router.push("/aftermarketEdit")}}},[e._v("新建")])],1)],1),e._v(" "),a("data-tables-server",{attrs:{data:e.data,"table-props":e.pagination.tableProps,total:e.pagination.total_number,actionCol:e.actionCol,filters:e.filters,"current-page":e.pagination.page,"pagination-props":{background:!0,pageSizes:[e.pagination.page_size]}},on:{"query-change":e.loadData,"update:currentPage":function(t){return e.$set(e.pagination,"page",t)},"update:current-page":function(t){return e.$set(e.pagination,"page",t)}}},e._l(e.titles,(function(e){return a("el-table-column",{key:e.label,attrs:{sortable:"custom",prop:e.prop,label:e.label,align:"center"}})})),1)],1)},r=[],i=(a("efce"),a("4634"),a("ed8b"),a("de90"),a("3b46")),n=a("52c1"),s=a("64f3");function o(e,t){var a=Object.keys(e);if(Object.getOwnPropertySymbols){var l=Object.getOwnPropertySymbols(e);t&&(l=l.filter((function(t){return Object.getOwnPropertyDescriptor(e,t).enumerable}))),a.push.apply(a,l)}return a}function c(e){for(var t=1;t<arguments.length;t++){var a=null!=arguments[t]?arguments[t]:{};t%2?o(Object(a),!0).forEach((function(t){Object(i["a"])(e,t,a[t])})):Object.getOwnPropertyDescriptors?Object.defineProperties(e,Object.getOwnPropertyDescriptors(a)):o(Object(a)).forEach((function(t){Object.defineProperty(e,t,Object.getOwnPropertyDescriptor(a,t))}))}return e}var p={name:"Dashboard",data:function(){var e=this,t=JSON.parse(localStorage.getItem("DataTables_DataTables_Table_0_"+location.pathname));return{data:[],filters:t?t.filters:[{value:"",prop:["customer","bills_uuid"]},{value:"",status_prop:"status"},{value:"",date_prop:"createAt"},{value:"",prop:"start_cost"},{value:"",prop:"end_cost"}],titles:[{prop:"id",label:"序号"},{prop:"customer",label:"客户名称"},{prop:"bill_uuid",label:"单据编号"},{prop:"status",label:"售后状态"},{prop:"creater",label:"创建人"},{prop:"createAt",label:"创建日期"}],actionCol:{label:"操作",props:{width:300,align:"center"},buttons:[{props:{type:"primary",icon:"el-icon-edit"},handler:function(t){e.$router.push({path:"/aftermarketEdit",query:{id:t.id}})},label:"编辑"},{handler:function(t){e.comConfirm("是否确定删除?","warning",!0,(function(){e.deleteData(t)}))},label:"删除"},{handler:function(t){e.behaviorsData(t)},label:"记录"}]}}},components:{PageHeader:s["PageHeader"]},computed:c({},Object(n["b"])(["pagination"]),{startDate:function(){return this.getData(0)},endDate:function(){return this.getData(1)}}),beforeCreate:function(){this.$store.dispatch("table/initDataTableData")},methods:{getData:function(e){return this.filters[2]&&this.filters[2].value?this.moment(this.filters[2].value[e]).utcOffset(8).format("YYYY-MM-DD"):""},loadData:function(e){var t=this;e=this.queryInfoLocalStorage(e),this.queryInfo=e;var a=e.page,l=null!=e.pageSize?e.pageSize:"10",r=e.sort.order,i=e.sort.prop,n={page:a,page_size:l,q:e.filters[0].value,orderDirection:r,orderField:i};this.$store.dispatch("table/getDailyDetail",n).then((function(e){var a=e.list;t.data=a})).catch((function(e){Object(s["Message"])({message:e,type:"error",customClass:"my-el-message"})}))}}},u=p,f=(a("fa10"),a("4e82")),v=Object(f["a"])(u,l,r,!1,null,"2ad5387f",null);t["default"]=v.exports},fa10:function(e,t,a){"use strict";var l=a("6e95"),r=a.n(l);r.a}}]);