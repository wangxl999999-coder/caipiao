const app = getApp()
const api = require('../../utils/api')
const { showLoading, hideLoading, showToast } = require('../../utils/util')

Page({
  data: {
    type: 'user',
    title: '用户协议',
    content: '',
    loading: true
  },

  onLoad: function (options) {
    const { type = 'user' } = options
    const titles = {
      user: '用户协议',
      privacy: '隐私政策'
    }
    
    this.setData({
      type: type,
      title: titles[type] || '用户协议'
    })
    
    wx.setNavigationBarTitle({
      title: this.data.title
    })
    
    this.loadData()
  },

  loadData: async function () {
    showLoading()
    try {
      let res
      if (this.data.type === 'user') {
        res = await api.setting.getUserAgreement()
      } else if (this.data.type === 'privacy') {
        res = await api.setting.getPrivacyPolicy()
      }
      
      if (res && res.code === 200) {
        this.setData({
          content: res.data.content || ''
        })
      }
    } catch (error) {
      console.error('加载协议失败:', error)
      showToast('加载失败')
    } finally {
      hideLoading()
      this.setData({ loading: false })
    }
  }
})
