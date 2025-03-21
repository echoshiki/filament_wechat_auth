import { PropsWithChildren } from 'react'
import { useLaunch } from '@tarojs/taro'
import useAuthStore from './stores/auth';

import './app.css'

function App({ children }: PropsWithChildren<any>) {
  useLaunch(() => {
    console.log('App launched.')
    // 静默登录
    if (!useAuthStore.getState().openid) {
      console.log('执行静默处理')
      useAuthStore.getState().loginInSilence();
    }
  });

  // children 是将要会渲染的页面
  return children
}
  
export default App
