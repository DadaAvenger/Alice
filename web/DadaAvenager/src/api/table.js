import request from '@/utils/request'

export function getDailyDetail(params) {
  return request({
    url: '/getDailyDetail',
    method: 'get',
    params
  })
}
