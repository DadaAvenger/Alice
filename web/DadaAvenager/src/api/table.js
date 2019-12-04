import request from '@/utils/request'

export function getDailyDetail(params) {
  return request({
    url: '/api.php?action=dailyPay&opt=getDailyPay',
    method: 'post',
    params
  })
}
