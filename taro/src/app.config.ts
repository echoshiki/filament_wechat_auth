export default defineAppConfig({
  pages: [
    'pages/index/index',
    'pages/user/index'
  ],
  window: {
    backgroundTextStyle: 'light',
    navigationBarBackgroundColor: '#fff',
    navigationBarTitleText: 'WeChat',
    navigationBarTextStyle: 'black'
  },
  tabBar: {
    list: [
      {
        iconPath: 'assets/icons/home.png',
        selectedIconPath: 'assets/icons/home_fill.png',
        pagePath: 'pages/index/index',
        text: '首页'
      },
      {
        iconPath: 'assets/icons/user.png',
        selectedIconPath: 'assets/icons/user_fill.png',
        pagePath: 'pages/user/index',
        text: '我的'
      },
    ],
    color: '#000',
    selectedColor: '#000',
    backgroundColor: '#fff',
    borderStyle: 'white',
  },
})
