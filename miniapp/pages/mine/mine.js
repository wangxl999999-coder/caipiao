const app = getApp()
const api = require('../../utils/api')
const { showLoading, hideLoading, showToast, showModal } = require('../../utils/util')

Page({
  data: {
    userInfo: null,
    isLoggedIn: false,
    menuList: [
      {
        id: 'about',
        icon: 'ℹ️',
        title: '关于我们',
        url: '/pages/mine/about'
      },
      {
        id: 'agreement',
        icon: '📄',
        title: '用户协议',
        url: '/pages/mine/agreement?type=user'
      },
      {
        id: 'privacy',
        icon: '🔒',
        title: '隐私政策',
        url: '/pages/mine/agreement?type=privacy'
      },
      {
        id: 'service',
        icon: '💬',
        title: '在线客服',
        url: '/pages/chat/chat'
      }
    ]
  },

  onLoad: function (options) {
    this.checkLoginStatus()
  },

  onShow: function () {
    this.checkLoginStatus()
  },

  checkLoginStatus: function () {
    const token = wx.getStorageSync('token')
    const userInfo = wx.getStorageSync('userInfo')
    if (token && userInfo) {
      const formattedUserInfo = {
        ...userInfo,
        maskedPhone: this.formatPhone(userInfo.phone),
        avatarChar: this.getAvatarChar(userInfo.nickname)
      }
      this.setData({
        userInfo: formattedUserInfo,
        isLoggedIn: true
      })
    } else {
      this.setData({
        userInfo: null,
        isLoggedIn: false
      })
    }
  },

  getAvatarChar: function (nickname) {
    if (!nickname) return '用'
    return nickname.charAt(0)
  },

  formatPhone: function (phone) {
    if (!phone) return ''
    if (phone.length >= 11) {
      return phone.replace(/(\d{3})\d{4}(\d{4})/, '$1****$2')
    }
    return phone
  },

  goToLogin: function () {
    if (!this.data.isLoggedIn) {
      wx.navigateTo({
        url: '/pages/login/login'
      })
    }
  },

  goToPage: function (e) {
    const { url } = e.currentTarget.dataset
    if (!this.data.isLoggedIn && url === '/pages/chat/chat') {
      showToast('请先登录')
      setTimeout(() => {
        wx.navigateTo({
          url: '/pages/login/login'
        })
      }, 1500)
      return
    }
    
    wx.navigateTo({
      url: url
    })
  },

  logout: async function () {
    const confirmed = await showModal('提示', '确定要退出登录吗？')
    if (confirmed) {
      showLoading('退出中...')
      try {
        await api.user.logout()
      } catch (error) {
        console.error('退出登录失败:', error)
      }
      
      wx.removeStorageSync('token')
      wx.removeStorageSync('userInfo')
      app.globalData.token = null
      app.globalData.userInfo = null
      
      this.setData({
        userInfo: null,
        isLoggedIn: false
      })
      
      hideLoading()
      showToast('已退出登录')
    }
  }
})
