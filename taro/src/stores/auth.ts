import { create } from "zustand";
import { persist } from 'zustand/middleware';

import UserInfoProps from "@/types/user";
import Taro from "@tarojs/taro";
import { http } from "@/utils/request";

interface AuthStoreProps{
    openid: string | null;
    isBound: boolean | null;
    token: string | null;
    userInfo: UserInfoProps | null;
    loginInSilence: () => Promise<void>;
    loginOnBound: (e, currentOpenid) => Promise<void>;
    login: (currentOpenid) => Promise<void>;
    logout: () => void;
}

const useAuthStore = create<AuthStoreProps>()(
    // 用于将状态持久化的中间件
    persist(
        (set) => ({
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

            loginOnBound: async (e, currentOpenid: string) => {
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

                    console.log('绑定手机号的登录响应 response', response);

                    // 获取后存入本地
                    if (response?.token) {
                        set({ token: response.token, userInfo: response.user });
                        Taro.showToast({ title: '登录成功', icon: 'success' });
                    } else {
                        throw new Error('登录失败');
                    }
                } catch (e) {
                    console.error(e);
                    Taro.showToast({ title: e.message || '登录失败', icon: 'none' });
                }
            },

            login: async (currentOpenid: string) => {
                try {

                    console.log('正常登录 currentOpenid', currentOpenid);

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
                    } else {
                        throw new Error('登录失败');
                    }

                } catch (e) {
                    console.error(e);
                    Taro.showToast({ title: e.message || '登录失败', icon: 'none' });
                }
            },

            logout: () => set({ 
                token: null, 
                userInfo: null,
                // 测试用
                openid: null,
                isBound: null
            })
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