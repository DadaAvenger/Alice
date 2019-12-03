/** When your routing table is too long, you can split it into small modules**/

import Layout from '@/layout'

const chartsRouter = {
  path: '/charts',
  component: Layout,
  redirect: 'noRedirect',
  name: 'Charts',
  meta: {
    title: '记账数据分析',
    icon: 'chart'
  },
  children: [
    {
      path: 'keyboard',
      component: () => import('@/views/charts/keyboard'),
      name: 'KeyboardChart',
      meta: { title: '柱状图分析', noCache: true }
    },
    {
      path: 'line',
      component: () => import('@/views/charts/line'),
      name: 'LineChart',
      meta: { title: '饼图分析', noCache: true }
    },
    {
      path: 'mix-chart',
      component: () => import('@/views/charts/mix-chart'),
      name: 'MixChart',
      meta: { title: '预测分析', noCache: true }
    },
    {
      path: '_line',
      component: () => import('@/views/charts/line'),
      name: '_LineChart',
      meta: { title: '预算分析', noCache: true }
    },
    {
      path: '_mix-chart',
      component: () => import('@/views/charts/mix-chart'),
      name: '_MixChart',
      meta: { title: '余额分析', noCache: true }
    }
  ]
}

export default chartsRouter
