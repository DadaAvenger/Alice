<template>
  <div class="dashboard-container">
    <fieldset class="el-fieldset ">
      <div class="legend">查询条件</div>
      <el-form :inline="true">
        <el-form-item label="起始日期">
          <el-date-picker
            v-model="filters[2].value"
            type="daterange"
            align="left"
            unlink-panels
            range-separator="至"
            start-placeholder="开始日期"
            end-placeholder="结束日期"
            :picker-options="pickerOptions"
          ></el-date-picker>
        </el-form-item>
        <el-form-item label="金额范围">
          <el-input
            v-model="filters[3].value"
            style="width:48%"
            onpropertychange="this.value=this.value.replace(/[^0-9.]/g,'')"
            oninput="this.value=this.value.replace(/[^0-9.]/g,'')"
          >
            <svg-icon slot="prefix" icon-class="money" class="el-input__icon"/>
          </el-input>-
          <el-input
            v-model="filters[4].value"
            style="width:48%"
            onpropertychange="this.value=this.value.replace(/[^0-9.]/g,'')"
            oninput="this.value=this.value.replace(/[^0-9.]/g,'')"
          >
            <svg-icon slot="prefix" icon-class="money" class="el-input__icon"/>
          </el-input>
        </el-form-item>
        <el-form-item label="消费类型">
          <el-select placeholder="选择消费类型" value v-model="filters[1].value">
            <el-option label="选择消费类型" value></el-option>
            <el-option label="待签收" value="1"></el-option>
            <el-option label="维修中" value="0"></el-option>
            <el-option label="已寄出" value="3"></el-option>
            <el-option label="待确认" value="2"></el-option>
          </el-select>
        </el-form-item>
      </el-form>
    </fieldset>

    <el-row :gutter="10" style="margin-bottom: 10px" >
        <el-col :lg="17">&nbsp;</el-col>
        <el-col :lg="5" >
            <el-input prefix-icon="el-icon-search" v-model="filters[0].value" placeholder="输入消费关键字"></el-input>
        </el-col>

        <el-col :lg="2"  align="right" >
            <el-button style = "width:100%" type="primary" @click="()=>{$router.push('/aftermarketEdit')}">新建</el-button>
        </el-col>
    </el-row>
    <data-tables-server
      :data="data"
      :table-props="pagination.tableProps"
      :total="pagination.total_number?pagination.total_number:1"
      @query-change="loadData"
      :actionCol="actionCol"
      :filters="filters"
      :current-page.sync="pagination.page"
      :pagination-props="{ background: true,pageSizes: [pagination.page_size] }"
    >
      <el-table-column
        v-for="title in titles"
        sortable="custom"
        :prop="title.prop"
        :label="title.label"
        :key="title.label"
        align="center"
      ></el-table-column>
    </data-tables-server>
  </div>
</template>

<script>
import { mapGetters } from "vuex";
import { Message, PageHeader } from "element-ui";
import Cookies from 'js-cookie'
export default {
  name: "Dashboard",
  data() {
    var localStoragedata = JSON.parse(
      localStorage.getItem("DataTables_DataTables_Table_0_" + location.pathname)
    );
    return {
      data: [],
      filters: localStoragedata
        ? localStoragedata.filters
        : [
            {
              value: "",
              prop: ["customer", "bills_uuid"]
            },
            {
              value: "",
              status_prop: "status"
            },
            {
              value: "",
              date_prop: "createAt"
            },
            {
              value: "",
              prop: "start_cost"
            },
            {
              value: "",
              prop: "end_cost"
            }
          ],
      titles: [
        {
          prop: "id",
          label: "序号"
        },
        {
          prop: "customer",
          label: "客户名称"
        },
        {
          prop: "bill_uuid",
          label: "单据编号"
        },
        {
          prop: "status",
          label: "售后状态"
        },
        {
          prop: "creater",
          label: "创建人"
        },
        {
          prop: "createAt",
          label: "创建日期"
        }
      ],
      actionCol: {
        label: "操作",
        props: {
          width: 300,
          align: "center"
        },
        buttons: [
          {
            props: {
              type: "primary",
              icon: "el-icon-edit"
            },
            handler: row => {
              this.$router.push({
                path: "/aftermarketEdit",
                query: { id: row.id }
              });
            },
            label: "编辑"
          },
          {
            handler: row => {
              this.comConfirm("是否确定删除?", "warning", true, () => {
                this.deleteData(row);
              });
            },
            label: "删除"
          },
          {
            handler: row => {
              this.behaviorsData(row);
            },
            label: "记录"
          }
        ]
      }
    };
  },
  components: {
    PageHeader
  },
  computed: {
    ...mapGetters(["pagination"]),
    startDate() {
      return this.getData(0);
    },
    endDate() {
      return this.getData(1);
    }
  },
  beforeCreate: function() {
    this.$store.dispatch("table/initDataTableData");
  },
  methods: {
    getData: function(index) {
      if (this.filters[2]) {
        if (!this.filters[2].value) {
          return "";
        } else {
          return this.moment(this.filters[2].value[index])
            .utcOffset(8)
            .format("YYYY-MM-DD");
        }
      } else {
        return "";
      }
    },
    loadData: function(queryInfo) {
      queryInfo = this.queryInfoLocalStorage(queryInfo);
      this.queryInfo = queryInfo;
      let page = queryInfo.page;
      let page_size = queryInfo.pageSize != null ? queryInfo.pageSize : "10";
      let orderDirection = queryInfo.sort.order;
      let orderField = queryInfo.sort.prop;
      let params = {
        sessionId:Cookies.get("vue_admin_template_token"),
        page,
        page_size,
        start_time: this.startDate, //开始日期
        end_time: this.endDate, //结束日期
        type:queryInfo.filters[1].value,
        mark: queryInfo.filters[0].value, //搜索
        orderDirection:((orderDirection=="descending")?"SORT_DESC":"SORT_ASC"),
        orderField
      };
      this.$store
        .dispatch("table/getDailyDetail", params)
        .then(res => {
          let { list } = res;
          this.data = list;
        })
        .catch(msg => {
          Message({
            message: msg,
            type: "error",
            customClass: "my-el-message"
          });
        });
    }
  }
};
</script>

<style lang="scss" scoped>
.dashboard {
  &-container {
    margin: 30px;
  }
  &-text {
    font-size: 30px;
    line-height: 46px;
  }
}

</style>
