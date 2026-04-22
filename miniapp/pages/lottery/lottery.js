const app = getApp()
const api = require('../../utils/api')
const { showLoading, hideLoading, showToast } = require('../../utils/util')

Page({
  data: {
    type: '',
    typeName: '',
    lotteries: [],
    page: 1,
    pageSize: 10,
    hasMore: true,
    loading: false
  },

  onLoad: function (options) {
    const { type = 'ssq', name = '双色球' } = options
    this.setData({
      type,
      typeName: name
    })
    wx.setNavigationBarTitle({
      title: name
    })
    this.loadData()
  },

  onShow: function () {
    if (this.data.lotteries.length === 0) {
      this.loadData()
    }
  },

  onPullDownRefresh: function () {
    this.setData({
      page: 1,
      lotteries: [],
      hasMore: true
    })
    this.loadData().then(() => {
      wx.stopPullDownRefresh()
    })
  },

  onReachBottom: function () {
    if (this.data.hasMore && !this.data.loading) {
      this.setData({
        page: this.data.page + 1
      })
      this.loadData()
    }
  },

  loadData: async function () {
    if (this.data.loading) return
    this.setData({ loading: true })
    showLoading()
    
    try {
      const res = await api.lottery.getList(
        this.data.type,
        this.data.page,
        this.data.pageSize
      )
      
      if (res.code === 200) {
        const data = res.data || []
        const hasMore = data.length >= this.data.pageSize
        
        this.setData({
          lotteries: this.data.page === 1 ? data : [...this.data.lotteries, ...data],
          hasMore
        })
      }
    } catch (error) {
      console.error('加载开奖数据失败:', error)
      showToast('加载失败')
    } finally {
      hideLoading()
      this.setData({ loading: false })
    }
  },

  goToDetail: function (e) {
    const { id } = e.currentTarget.dataset
    wx.navigateTo({
      url: `/pages/lottery/detail?id=${id}`
    })
  },

  formatBalls: function (balls) {
    if (!balls || balls.length === 0) return ''
    return balls.join(', ')
  }
})
