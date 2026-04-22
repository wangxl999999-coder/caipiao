const app = getApp()
const { showToast, hideLoading } = require('./util')

const request = (options) => {
  return new Promise((resolve, reject) => {
    const { url, method = 'GET', data = {}, header = {} } = options
    
    const token = app.globalData.token || wx.getStorageSync('token')
    const defaultHeader = {
      'Content-Type': 'application/json'
    }
    
    if (token) {
      defaultHeader['Authorization'] = `Bearer ${token}`
    }
    
    wx.request({
      url: `${app.globalData.apiBaseUrl}${url}`,
      method,
      data,
      header: { ...defaultHeader, ...header },
      success: (res) => {
        if (res.statusCode === 200) {
          const response = res.data
          if (response.code === 200) {
            resolve(response)
          } else if (response.code === 401) {
            wx.removeStorageSync('token')
            wx.removeStorageSync('userInfo')
            app.globalData.token = null
            app.globalData.userInfo = null
            showToast('登录已过期，请重新登录')
            reject(response)
          } else {
            showToast(response.message || '请求失败')
            reject(response)
          }
        } else {
          showToast('网络错误')
          reject(res)
        }
      },
      fail: (err) => {
        console.error('Request failed:', err)
        showToast('网络请求失败')
        reject(err)
      }
    })
  })
}

const api = {
  user: {
    login: (code) => {
      return request({
        url: '/user/login',
        method: 'POST',
        data: { code }
      })
    },
    
    register: (data) => {
      return request({
        url: '/user/register',
        method: 'POST',
        data
      })
    },
    
    getInfo: () => {
      return request({
        url: '/user/info',
        method: 'GET'
      })
    },
    
    updateInfo: (data) => {
      return request({
        url: '/user/info',
        method: 'PUT',
        data
      })
    },
    
    logout: () => {
      return request({
        url: '/user/logout',
        method: 'POST'
      })
    }
  },
  
  lottery: {
    getList: (type = '', page = 1, pageSize = 10) => {
      return request({
        url: '/lottery/list',
        method: 'GET',
        data: { type, page, pageSize }
      })
    },
    
    getLatest: () => {
      return request({
        url: '/lottery/latest',
        method: 'GET'
      })
    },
    
    getDetail: (id) => {
      return request({
        url: `/lottery/detail/${id}`,
        method: 'GET'
      })
    },
    
    getByTypeAndIssue: (type, issue) => {
      return request({
        url: '/lottery/byTypeAndIssue',
        method: 'GET',
        data: { type, issue }
      })
    }
  },
  
  station: {
    getList: (latitude, longitude, page = 1, pageSize = 10) => {
      return request({
        url: '/station/list',
        method: 'GET',
        data: { latitude, longitude, page, pageSize }
      })
    },
    
    getDetail: (id) => {
      return request({
        url: `/station/detail/${id}`,
        method: 'GET'
      })
    }
  },
  
  rule: {
    getList: () => {
      return request({
        url: '/rule/list',
        method: 'GET'
      })
    },
    
    getDetail: (type) => {
      return request({
        url: `/rule/detail/${type}`,
        method: 'GET'
      })
    }
  },
  
  news: {
    getList: (type = '', page = 1, pageSize = 10) => {
      return request({
        url: '/news/list',
        method: 'GET',
        data: { type, page, pageSize }
      })
    },
    
    getBanner: () => {
      return request({
        url: '/news/banner',
        method: 'GET'
      })
    },
    
    getDetail: (id) => {
      return request({
        url: `/news/detail/${id}`,
        method: 'GET'
      })
    }
  },
  
  setting: {
    getAboutUs: () => {
      return request({
        url: '/setting/about-us',
        method: 'GET'
      })
    },
    
    getUserAgreement: () => {
      return request({
        url: '/setting/user-agreement',
        method: 'GET'
      })
    },
    
    getCustomerService: () => {
      return request({
        url: '/setting/customer-service',
        method: 'GET'
      })
    }
  },
  
  chat: {
    getHistory: (page = 1, pageSize = 20) => {
      return request({
        url: '/chat/history',
        method: 'GET',
        data: { page, pageSize }
      })
    },
    
    sendMessage: (content, type = 'text') => {
      return request({
        url: '/chat/send',
        method: 'POST',
        data: { content, type }
      })
    }
  }
}

module.exports = api
