const app = getApp()
const api = require('../../utils/api')
const { showLoading, hideLoading, showToast } = require('../../utils/util')

Page({
  data: {
    banners: [],
    latestLotteries: [],
    lotteryTypes: [
      { type: 'ssq', name: '双色球', color: '#e60012', icon: '🎱' },
      { type: 'qcl', name: '七乐彩', color: '#1890ff', icon: '🎲' },
      { type: '22x5', name: '22选5', color: '#52c41a', icon: '🎯' },
      { type: '3d', name: '3D', color: '#faad14', icon: '🎰' },
      { type: 'kl8', name: '快乐8', color: '#722ed1', icon: '🎡' }
    ],
    indicatorDots: true,
    autoplay: true,
    interval: 3000,
    duration: 500
  },

  onLoad: function (options) {
    this.init()
  },

  onShow: function () {
    this.loadData()
  },

  onPullDownRefresh: function () {
    this.loadData().then(() => {
      wx.stopPullDownRefresh()
    })
  },

  init: function () {
    this.loadData()
  },

  loadData: async function () {
    showLoading()
    try {
      await Promise.all([
        this.loadBanners(),
        this.loadLatestLotteries()
      ])
    } catch (error) {
      console.error('加载数据失败:', error)
    } finally {
      hideLoading()
    }
  },

  loadBanners: async function () {
    try {
      const res = await api.news.getBanner()
      if (res.code === 200) {
        this.setData({
          banners: res.data || []
        })
      }
    } catch (error) {
      console.error('加载轮播图失败:', error)
    }
  },

  loadLatestLotteries: async function () {
    try {
      const res = await api.lottery.getLatest()
      if (res.code === 200) {
        const lotteries = (res.data || []).map(item => {
          const processedItem = { ...item }
          if (item.type === 'kl8' && item.red_balls && item.red_balls.length > 10) {
            processedItem.display_balls = item.red_balls.slice(0, 10)
            processedItem.has_more_balls = item.red_balls.length > 10
          } else {
            processedItem.display_balls = item.red_balls
            processedItem.has_more_balls = false
          }
          return processedItem
        })
        this.setData({
          latestLotteries: lotteries
        })
      }
    } catch (error) {
      console.error('加载最新开奖失败:', error)
    }
  },

  goToLottery: function (e) {
    const { type, name } = e.currentTarget.dataset
    wx.navigateTo({
      url: `/pages/lottery/lottery?type=${type}&name=${name}`
    })
  },

  goToLotteryDetail: function (e) {
    const { id } = e.currentTarget.dataset
    wx.navigateTo({
      url: `/pages/lottery/detail?id=${id}`
    })
  },

  goToNewsDetail: function (e) {
    const { id } = e.currentTarget.dataset
    wx.navigateTo({
      url: `/pages/news/detail?id=${id}`
    })
  },

  goToStations: function () {
    wx.navigateTo({
      url: '/pages/stations/stations'
    })
  },

  goToRules: function () {
    wx.navigateTo({
      url: '/pages/rules/rules'
    })
  },

  goToNews: function () {
    wx.switchTab({
      url: '/pages/news/news'
    })
  },

  onShareAppMessage: function () {
    return {
      title: '福彩助手 - 快速查询开奖信息',
      path: '/pages/index/index'
    }
  }
})
