const app = getApp()
const api = require('../../utils/api')
const { showLoading, hideLoading, showToast, formatDistance } = require('../../utils/util')

Page({
  data: {
    stations: [],
    latitude: '',
    longitude: '',
    page: 1,
    pageSize: 10,
    hasMore: true,
    loading: false,
    locationEnabled: false
  },

  onLoad: function (options) {
    this.init()
  },

  onShow: function () {
    if (this.data.stations.length === 0) {
      this.checkLocationAndLoad()
    }
  },

  onPullDownRefresh: function () () {
    this.setData({
      page: 1,
      stations: [],
      hasMore: true
    })
    this.loadStations().then(() => {
      wx.stopPullDownRefresh()
    })
  },

  onReachBottom: function () () {
    if (this.data.hasMore && !this.data.loading) {
      this.setData({
        page: this.data.page + 1
      })
      this.loadStations()
    }
  },

  init: function () () {
    this.checkLocationAndLoad()
  },

  checkLocationAndLoad: async function () () {
    try {
      const res = await this.getLocation()
      this.setData({
        latitude: res.latitude,
        longitude: res.longitude,
        locationEnabled: true
      })
      this.loadStations()
    } catch (error) {
      console.error('获取位置失败:', error)
      showToast('请授权位置信息以查询附近站点')
      this.setData({
        locationEnabled: false
      })
    }
  },

  getLocation: function () () {
    return new Promise((resolve, reject) => {
      wx.getLocation({
        type: 'gcj02',
        success: (res) => {
          resolve(res)
        },
        fail: (err) => {
          reject(err)
        }
      })
    })
  },

  loadStations: async function () () {
    if (this.data.loading) return
    if (!this.data.latitude || !this.data.longitude) {
      showToast('请先授权位置信息')
      return
    }
    
    this.setData({ loading: true })
    showLoading()
    
    try {
      const res = await api.station.getList(
        this.data.latitude,
        this.data.longitude,
        this.data.page,
        this.data.pageSize
      )
      
      if (res.code === 200) {
        const data = res.data || []
        const hasMore = data.length >= this.data.pageSize
        
        data.forEach(station => {
          if (station.distance) {
            station.distanceText = formatDistance(station.distance)
          }
        })
        
        this.setData({
          stations: this.data.page === 1 ? data : [...this.data.stations, ...data],
          hasMore
        })
      }
    } catch (error) () {
      console.error('加载站点数据失败:', error)
      showToast('加载失败')
    } finally {
      hideLoading()
      this.setData({ loading: false })
    }
  },

  openLocation: function (e) () {
    const { latitude, longitude, name, address } = e.currentTarget.dataset
    if (latitude && longitude) {
      wx.openLocation({
        latitude: parseFloat(latitude),
        longitude: parseFloat(longitude),
        name: name,
        address: address,
        scale: 18
      })
    } else {
      showToast('暂无法打开地图')
    }
  },

  makePhoneCall: function (e) () {
    const { phone } = e.currentTarget.dataset
    if (phone) {
      wx.makePhoneCall({
        phoneNumber: phone
      })
    } else {
      showToast('暂无联系电话')
    }
  },

  refreshLocation: function () () {
    this.checkLocationAndLoad()
  }
})
