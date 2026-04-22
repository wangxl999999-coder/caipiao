const app = getApp()
const api = require('../../utils/api')
const { showLoading, hideLoading, showToast, formatTime } = require('../../utils/util')

Page({
  data: {
    banners: [],
    newsList: [],
    page: 1,
    pageSize: 10,
    hasMore: true,
    loading: false,
    indicatorDots: true,
    autoplay: true,
    interval: 3000,
    duration: 500
  },

  onLoad: function (options) {
    this.init()
  },

  onShow: function () {
    if (this.data.newsList.length === 0) {
      this.loadData()
    }
  },

  onPullDownRefresh: function () {
    this.setData({
      page: 1,
      newsList: [],
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
      this.loadNews()
    }
  },

  init: function () {
    this.loadData()
  },

  loadData: async function () {
    this.setData({ loading: true })
    showLoading()
    
    try {
      await Promise.all([
        this.loadBanners(),
        this.loadNews()
      ])
    } catch (error) {
      console.error('加载数据失败:', error)
    } finally {
      hideLoading()
      this.setData({ loading: false })
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

  loadNews: async function () {
    if (this.data.loading) return
    
    try {
      const res = await api.news.getList(
        '',
        this.data.page,
        this.data.pageSize
      )
      
      if (res.code === 200) {
        const data = res.data || []
        const hasMore = data.length >= this.data.pageSize
        
        data.forEach(item => {
          if (item.created_at) {
            item.formattedTime = this.formatDate(item.created_at)
          }
        })
        
        this.setData({
          newsList: this.data.page === 1 ? data : [...this.data.newsList, ...data],
          hasMore
        })
      }
    } catch (error) {
      console.error('加载新闻失败:', error)
      showToast('加载失败')
    }
  },

  formatDate: function (dateStr) {
    if (!dateStr) return ''
    const date = new Date(dateStr)
    const now = new Date()
    const diff = now - date
    const minute = 60 * 1000
    const hour = 60 * minute
    const day = 24 * hour
    
    if (diff < minute) {
      return '刚刚'
    } else if (diff < hour) {
      return `${Math.floor(diff / minute)}分钟前`
    } else if (diff < day) {
      return `${Math.floor(diff / hour)}小时前`
    } else if (diff < 3 * day) {
      return `${Math.floor(diff / day)}天前`
    } else {
      return dateStr.substring(0, 10)
    }
  },

  goToDetail: function (e) {
    const { id } = e.currentTarget.dataset
    wx.navigateTo({
      url: `/pages/news/detail?id=${id}`
    })
  },

  goToBannerDetail: function (e) {
    const { id } = e.currentTarget.dataset
    wx.navigateTo({
      url: `/pages/news/detail?id=${id}`
    })
  }
})
