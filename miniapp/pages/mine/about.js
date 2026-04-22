const app = getApp()
const api = require('../../utils/api')
const { showLoading, hideLoading, showToast } = require('../../utils/util')

Page({
  data: {
    content: '',
    loading: true
  },

  onLoad: function (options) {
    wx.setNavigationBarTitle({
      title: '关于我们'
    })
    this.loadData()
  },

  loadData: async function () {
    showLoading()
    try {
      const res = await api.setting.getAboutUs()
      if (res.code === 200) {
        this.setData({
          content: res.data.content || ''
        })
      }
    } catch (error) {
      console.error('加载关于我们失败:', error)
      showToast('加载失败')
    } finally {
      hideLoading()
      this.setData({ loading: false })
    }
  }
})
