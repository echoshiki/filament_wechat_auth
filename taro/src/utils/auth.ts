import Taro from '@tarojs/taro';
import useAuthStore from '../stores/auth';

import { getCurrentPageUrl } from '@/utils/common';

/**
 * 验证用户是否已登录，未登录则跳转到登录页
 * @param targetUrl 目标页面URL，登录成功后跳转
 * @returns 是否已登录
 */
function checkLogin(targetUrl?: string): boolean {
    const isLoggedIn = useAuthStore.getState().isLoggedIn();
    if (!isLoggedIn) {
        // 跳转到登录页，并传递目标页面参数
        const redirectUrl = targetUrl || getCurrentPageUrl();
        Taro.navigateTo({ 
            url: `/pages/login/index?redirect=${redirectUrl}` 
        });
        return false;
    }
    return true;
}

export {
    checkLogin
}
