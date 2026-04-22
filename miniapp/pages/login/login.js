const app = getApp()
const api = require('../../utils/api')
const { showLoading, hideLoading, showToast, showModal } = require('../../utils/util')

Page({
  data: {
    phone: '',
    code: '',
    isLogin: true,
    countdown: 0,
    agreed: false,
    userInfo: null
  },

  onLoad: function (options) {
    this.checkLoginStatus()
  },

  checkLoginStatus: function () {
    const token = wx.getStorageSync('token')
    const userInfo = wx.getStorageSync('userInfo')
    if (token && userInfo) {
      this.setData({
        userInfo: userInfo
      })
    }
  },

  switchMode: function () {
    this.setData({
      isLogin: !this.data.isLogin
    })
  },

  inputPhone: function (e) {
    this.setData({
      phone: e.detail.value
    })
  },

  inputCode: function (e) {
    this.setData({
      code: e.detail.value
    })
  },

  toggleAgree: function () {
    this.setData({
      agreed: !this.data.agreed
    })
  },

  getCode: async function () {
    const phone = this.data.phone
    if (!phone) {
      showToast('请输入手机号')
      return
    }
    if (!/^1[3-9]\d{9}$/.test(phone)) {
      showToast('手机号格式不正确')
      return
    }

    showLoading('发送中...')
    try {
      const res = await api.user.register({
        phone: phone,
        action: 'sendCode'
      })
      if (res.code === 200) {
        showToast('验证码已发送')
        this.startCountdown()
      }
    } catch (error) {
      console.error('发送验证码失败:', error)
    } finally {
      hideLoading()
    }
  },

  startCountdown: function () {
    let countdown = 60
    this.setData({ countdown })
    const timer = setInterval(() => {
      countdown--
      this.setData({ countdown })
      if (countdown <= 0) {
        clearInterval(timer)
      }
    }, 1000)
  },

  login: async function () {
    const { phone, code } = this.data
    
    if (!phone) {
      showToast('请输入手机号')
      return
    }
    if (!/^1[3-9]\d{9}$/.test(phone)) {
      showToast('手机号格式不正确')
      return
    }
    if (!code) {
      showToast('请输入验证码')
      return
    }
    if (!this.data.agreed) {
      showToast('请先同意用户协议')
      return
    }

    showLoading('登录中...')
    try {
      const res = await api.user.login({
        phone: phone,
        code: code
      })
      
      if (res.code === 200) {
        const data = res.data
        app.globalData.token = data.token
        app.globalData.userInfo = data.userInfo
        wx.setStorageSync('token', data.token)
        wx.setStorageSync('userInfo', data.userInfo)
        showToast('登录成功')
        
        setTimeout(() => {
          wx.switchTab({
            url: '/pages/mine/mine'
          })
        }, 1500)
      }
    } catch (error) {
      console.error('登录失败:', error)
    } finally {
      hideLoading()
    }
  },

  register: async function () {
    const { phone, code } = this.data
    
    if (!phone) {
      showToast('请输入手机号')
      return
    }
    if (!/^1[3-9]\d{9}$/.test(phone)) {
      showToast('手机号格式不正确')
      return
    }
    if (!code) {
      showToast('请输入验证码')
      return
    }
    if (!this.data.agreed) {
      showToast('请先同意用户协议')
      return
    }

    showLoading('注册中...')
    try {
      const res = await api.user.register({
        phone: phone,
        code: code
      })
      
      if (res.code === 200) {
        showToast('注册成功，请登录')
        this.setData({
          isLogin: true
        })
      }
    } catch (error) {
      console.error('注册失败:', error)
    } finally {
      hideLoading()
    }
  },

  wxLogin: function () {
    wx.getUserProfile({
      desc: '用于完善会员资料',
      success: (res) => {
        const userInfo = res.userInfo
        wx.login({
          success: (loginRes) => {
            if (loginRes.code) {
              this.doWxLogin(loginRes.code, userInfo)
            }
          }
        })
      }
    })
  },

  doWxLogin: async function (code, userInfo) {
    showLoading('登录中...')
    try {
      const res = await api.user.login({
        code: code,
        userInfo: userInfo,
        loginType: 'wechat'
      })
      
      if (res.code === 200) {
        const data = res.data
        app.globalData.token = data.token
        app.globalData.userInfo = data.userInfo
        wx.setStorageSync('token', data.token)
        wx.setStorageSync('userInfo', data.userInfo)
        showToast('登录成功')
        
        setTimeout(() => {
          wx.switchTab({
            url: '/pages/mine/mine'
          })
        }, 1500)
      }
    } catch (error) {
      console.error('微信登录失败:', error)
    } finally {
      hideLoading()
    }
  },

  goToUserAgreement: function () {
    wx.navigateTo({
      url: '/pages/mine/agreement?type=user'
    })
  },

  goToPrivacyPolicy: function () {
    wx.navigateTo({
      url: '/pages/mine/agreement?type=privacy'
    })
  }
})
