import request from '@/utils/request'

export function login(data) {
  return request({
    url: '/api.php?action=index&opt=login',
    method: 'post',
    data
  })
}

export function getInfo(token) {
  return request({
    url: '/user/info',
    method: 'get',
    params: { token }
  })
}

export function logout() {
  return request({
    url: '/api.php?action=index&opt=logout',
    method: 'post'
  })
}
