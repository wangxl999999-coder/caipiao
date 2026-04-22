App({
  globalData: {
    userInfo: null,
    token: null,
    apiBaseUrl: 'http://caipiao.com'
  },

  onLaunch: function () {
    this.checkLoginStatus();
  },

  checkLoginStatus: function () {
    const token = wx.getStorageSync('token');
    const userInfo = wx.getStorageSync('userInfo');
    if (token && userInfo) {
      this.globalData.token = token;
      this.globalData.userInfo = userInfo;
    }
  },

  login: function () {
    return new Promise((resolve, reject) => {
      wx.login({
        success: (res) => {
          if (res.code) {
            wx.request({
              url: `${this.globalData.apiBaseUrl}/user/login`,
              method: 'POST',
              data: {
                code: res.code
              },
              success: (response) => {
                if (response.data.code === 200) {
                  const data = response.data.data;
                  this.globalData.token = data.token;
                  this.globalData.userInfo = data.userInfo;
                  wx.setStorageSync('token', data.token);
                  wx.setStorageSync('userInfo', data.userInfo);
                  resolve(data);
                } else {
                  reject(response.data);
                }
              },
              fail: (err) => {
                reject(err);
              }
            });
          } else {
            reject(res);
          }
        },
        fail: (err) => {
          reject(err);
        }
      });
    });
  }
});
