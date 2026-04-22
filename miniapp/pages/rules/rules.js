const app = getApp()
const api = require('../../utils/api')
const { showLoading, hideLoading, showToast } = require('../../utils/util')

Page({
  data: {
    rules: [],
    activeType: '',
    currentRule: null,
    loading: false
  },

  onLoad: function (options) {
    this.init()
  },

  onShow: function () {
    if (this.data.rules.length === 0) {
      this.loadRules()
    }
  },

  init: function () {
    this.loadRules()
  },

  loadRules: async function () {
    this.setData({ loading: true })
    showLoading()
    
    try {
      const res = await api.rule.getList()
      if (res.code === 200) {
        const rules = res.data || []
        if (rules.length > 0) {
          this.setData({
            rules,
            activeType: rules[0].type,
            currentRule: rules[0]
          })
        }
      }
    } catch (error) {
      console.error('加载规则失败:', error)
      showToast('加载失败')
    } finally {
      hideLoading()
      this.setData({ loading: false })
    }
  },

  switchRule: function (e) {
    const { type } = e.currentTarget.dataset
    const rule = this.data.rules.find(r => r.type === type)
    if (rule) {
      this.setData({
        activeType: type,
        currentRule: rule
      })
    }
  }
})
