const app = getApp()
const api = require('../../utils/api')
const { showLoading, hideLoading, showToast } = require('../../utils/util')

Page({
  data: {
    messages: [],
    inputContent: '',
    scrollTop: 0,
    sending: false,
    page: 1,
    pageSize: 20
  },

  onLoad: function (options) {
    wx.setNavigationBarTitle({
      title: '在线客服'
    })
    this.init()
  },

  onShow: function () {
    if (this.data.messages.length === 0) {
      this.loadHistory()
    }
  },

  init: function () {
    this.addWelcomeMessage()
    this.loadHistory()
  },

  addWelcomeMessage: function () {
    const welcomeMsg = {
      id: 'welcome',
      type: 'text',
      content: '您好！欢迎使用福彩助手在线客服，请问有什么可以帮助您的？',
      from: 'service',
      created_at: new Date().toISOString()
    }
    this.setData({
      messages: [welcomeMsg]
    })
  },

  loadHistory: async function () {
    try {
      const res = await api.chat.getHistory(this.data.page, this.data.pageSize)
      if (res.code === 200) {
        const history = res.data || []
        if (history.length > 0) {
          this.setData({
            messages: [...history.reverse(), ...this.data.messages]
          })
          this.scrollToBottom()
        }
      }
    } catch (error) {
      console.error('加载聊天记录失败:', error)
    }
  },

  inputChange: function (e) {
    this.setData({
      inputContent: e.detail.value
    })
  },

  inputFocus: function () {
    setTimeout(() => {
      this.scrollToBottom()
    }, 200)
  },

  sendMessage: async function () {
    const content = this.data.inputContent.trim()
    if (!content) {
      showToast('请输入消息内容')
      return
    }
    if (this.data.sending) {
      return
    }

    const userMsg = {
      id: Date.now().toString(),
      type: 'text',
      content: content,
      from: 'user',
      created_at: new Date().toISOString()
    }

    this.setData({
      messages: [...this.data.messages, userMsg],
      inputContent: '',
      sending: true
    })
    
    this.scrollToBottom()

    try {
      const res = await api.chat.sendMessage(content, 'text')
      if (res.code === 200) {
        const replyMsg = {
          id: Date.now().toString() + '_reply',
          type: 'text',
          content: res.data.reply || '感谢您的咨询，客服人员会尽快回复您。',
          from: 'service',
          created_at: new Date().toISOString()
        }
        
        this.setData({
          messages: [...this.data.messages, replyMsg]
        })
        
        this.scrollToBottom()
      }
    } catch (error) {
      console.error('发送消息失败:', error)
      showToast('发送失败，请稍后重试')
      
      this.setData({
        messages: this.data.messages.map(msg => {
          if (msg.id === userMsg.id) {
            return { ...msg, sendFailed: true }
          }
          return msg
        })
      })
    } finally {
      this.setData({
        sending: false
      })
    }
  },

  scrollToBottom: function () {
    wx.createSelectorQuery()
      .select('.message-list')
      .boundingClientRect((rect) => {
        if (rect) {
          this.setData({
            scrollTop: rect.height + 1000
          })
        }
      })
      .exec()
  },

  formatTime: function (dateStr) {
    if (!dateStr) return ''
    const date = new Date(dateStr)
    const now = new Date()
    const dayDiff = Math.floor((now.getTime() - date.getTime()) / (1000 * 60 * 60 * 24))
    
    const hour = date.getHours().toString().padStart(2, '0')
    const minute = date.getMinutes().toString().padStart(2, '0')
    
    if (dayDiff === 0) {
      return `${hour}:${minute}`
    } else if (dayDiff === 1) {
      return `昨天 ${hour}:${minute}`
    } else if (dayDiff < 7) {
      const weekDays = ['周日', '周一', '周二', '周三', '周四', '周五', '周六']
      return `${weekDays[date.getDay()]} ${hour}:${minute}`
    } else {
      const month = (date.getMonth() + 1).toString().padStart(2, '0')
      const day = date.getDate().toString().padStart(2, '0')
      return `${month}-${day} ${hour}:${minute}`
    }
  },

  retrySend: function (e) {
    const { id } = e.currentTarget.dataset
    const msg = this.data.messages.find(m => m.id === id)
    if (msg) {
      this.setData({
        inputContent: msg.content,
        messages: this.data.messages.filter(m => m.id !== id)
      })
    }
  }
})
