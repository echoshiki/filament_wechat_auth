import Taro from "@tarojs/taro";
import { create } from "zustand";
import { persist } from 'zustand/middleware';

import { http } from "@/utils/request";
import { mapsTo } from "@/utils/common";

import UserInfoProps from "@/types/user";

interface AuthStoreProps{
    openid: string | null;
    isBound: boolean | null;
    token: string | null;
    userInfo: UserInfoProps | null;
    loginInSilence: () => Promise<void>;
    loginOnBound: (
        e: any, 
        currentOpenid: string | null, 
        redirectUrl?: string
    ) => Promise<void>;
    login: (
        currentOpenid: string | null, 
        redirectUrl?: string
    ) => Promise<void>;
    logout: () => void;
    isLoggedIn: () => boolean;
}

const useAuthStore = create<AuthStoreProps>()(
    // 用于将状态持久化的中间件
    persist(
        (set, get) => ({
            openid: null,
            isBound: null,
            token: null,
            userInfo: null,

            loginInSilence: async () => {
                try {
                    const loginResponse = await Taro.login();

                    // 获取授权失败
                    if (loginResponse.errMsg !== 'login:ok') {
                        throw new Error('loginInSilence - 微信授权失败');
                    }

                    // 用 code 获取 openid 和 isBound
                    const response = await http.request({
                        url: '/api/v1/login-silence',
                        method: 'POST',
                        data: {
                            code: loginResponse.code
                        }
                    });

                    // 获取后存入本地
                    if (response?.openid && response.isBound !== null) {
                        set({ openid: response.openid, isBound: response.isBound });
                    } else {
                        throw new Error('静默处理错误');
                    }
                } catch (e) {
                    console.error(e);
                }
            },

            loginOnBound: async (e, currentOpenid, redirectUrl?) => {
                try {
                    if (!currentOpenid) {
                        throw new Error('loginOnBound - 未获取到 openid');
                    }

                    if (e.detail.errMsg === 'getPhoneNumber:fail user deny') {
                        console.log('用户拒绝授权');
                        return ;
                    }

                    // 用 code 和 openid 获取手机号和 token
                    const response = await http.request({
                        url: '/api/v1/login-bound',
                        method: 'POST',
                        data: {
                            code: e.detail.code,
                            openid: currentOpenid
                        }
                    });

                    // 获取后存入本地
                    if (response?.token) {
                        set({ token: response.token, userInfo: response.user });
                        Taro.showToast({ title: '登录成功', icon: 'success' });

                        setTimeout(() => {
                            // 自定义跳转
                            mapsTo(redirectUrl || '/pages/index/index');
                        }, 1000);

                    } else {
                        throw new Error('登录失败');
                    }
                } catch (e) {
                    console.error(e);
                    Taro.showToast({ title: e.message || '登录失败', icon: 'none' });
                }
            },

            login: async (currentOpenid, redirectUrl?) => {
                try {
                    // 未获取到 openid
                    if (!currentOpenid) {
                        throw new Error('login - 未获取到 openid');
                    }

                    // 用 code 获取 token
                    const response = await http.request({
                        url: '/api/v1/login',
                        method: 'POST',
                        data: {
                            openid: currentOpenid
                        }
                    })

                    console.log('正常登录响应 response', response);

                    // 获取后存入本地
                    if (response?.token) {
                        set({ token: response.token, userInfo: response.user });
                        Taro.showToast({ title: '登录成功', icon: 'success' });

                        setTimeout(() => {
                            // 自定义跳转
                            mapsTo(redirectUrl || '/pages/index/index');
                        }, 1000);
                        
                    } else {
                        throw new Error('登录失败');
                    }

                } catch (e) {
                    console.error(e);
                    Taro.showToast({ title: e.message || '登录失败', icon: 'none' });
                }
            },

            logout: async () => {
                try {
                    await http.request({
                        url: '/api/v1/logout',
                        method: 'POST'
                    });
                    set({ 
                        token: null, 
                        userInfo: null
                    });
                } catch (e) {
                    console.error(e);
                }
            },

            isLoggedIn: () => !!get().token
        }),
        {
            name: 'auth-storage',
            getStorage: () => ({
                getItem: (key) => Taro.getStorageSync(key),
                setItem: (key, value) => Taro.setStorageSync(key, value),
                removeItem: (key) => Taro.removeStorageSync(key),
            })
        }
    )
)

export default useAuthStore;