import { getDailyDetail } from '@/api/table'
import moment from 'moment'

const state = {
    page:1,
    page_size:10,
    total_number: 0,
    filtersFun:[],
    sort:{"order":"descending","prop":"createAt"},
    tableProps:{
        border: true,
        stripe: true,
        defaultSort: {"order":"descending","prop":"createAt"}
    }
}

const mutations = {
    SET_TOTAL_NUMBER: (state, total_number) => {
        state.total_number = total_number
    },
    SET_PAGE:(state,page)=>{
        state.page = page
    },
    SET_PAGE_SIZE:(state,page_size)=>{
        state.page_size = page_size
    },
    SET_FILTERFUN:(state,filtersFun)=>{
        state.filtersFun = filtersFun
    },
    SET_SORT:(state,sort)=>{
        state.sort = sort
    }
}

const actions = {
  getDailyDetail({ commit }, params) {
    return new Promise((resolve, reject) => {
        getDailyDetail(params).then(response => {
            const { data,ret } = response
            if (ret!==1) {
                reject(data.msg)
            }
            const { total_number } = data;
            const list = data.list.map((item, index) => {
                item.createAt = moment(item.createAt)
                .utcOffset(8)
                .format("YYYY-MM-DD HH:mm");
                return item;
            });
            
            commit('SET_TOTAL_NUMBER', total_number)
            resolve({list})
        }).catch(error => {
            reject(error)
        })
    })
  },
  initDataTableData({commit}){
        return new Promise(resolve => {
            let dataTableData =  JSON.parse(localStorage.getItem('DataTables_DataTables_Table_0_' + location.pathname))
            let page = dataTableData ? dataTableData.page : 1
            let page_size = dataTableData?dataTableData.pageSize ? dataTableData.pageSize : 10:10
            let sort = dataTableData? dataTableData.sort : {"order":"descending","prop":"createAt"}
            var filtersFun = (defaultfilters) => {
                let dataTableData =  JSON.parse(localStorage.getItem('DataTables_DataTables_Table_0_' + location.pathname))
                return dataTableData ? dataTableData.filters : defaultfilters
            };
            commit('SET_PAGE', page)
            commit('SET_SORT', sort)
            commit('SET_PAGE_SIZE', page_size)
            commit('SET_FILTERFUN', filtersFun)
            resolve()
        })
  }
}

export default {
  namespaced: true,
  state,
  mutations,
  actions
}

