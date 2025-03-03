import { create } from "zustand";
import { persist } from 'zustand/middleware';

import UserInfoProps from "@/types/user";
import Taro from "@tarojs/taro";
import { http } from "@/utils/request";

interface AuthStoreProps{
    token: string | null;
    userInfo: UserInfoProps | null;
    loginWithPhone: (e) => Promise<void>;
    logout: () => void;
    loginTest: (token, userInfo) => void;
}

const useAuthStore = create<AuthStoreProps>()(
    // 用于将状态持久化的中间件
    persist(
        (set) => ({
            token: null,
            userInfo: null,

            loginWithPhone: async (e) => {
                try {
                    const loginResponse = await Taro.login();

                    // 获取授权失败
                    if (loginResponse.errMsg !== 'login:ok' || e.detail.errMsg !== 'getPhoneNumber:ok') {
                        throw new Error('loginWithPhone - 微信授权失败');
                    }

                    console.log(loginResponse.code);
                    console.log(e.detail.code);

                    // 用两个 code 获取 openid 和手机号
                    const response = await http.request({
                        url: '/addons/wxauth/index/login',
                        method: 'POST',
                        data: {
                            login_code: loginResponse.code,
                            phone_code: e.detail.code
                        }
                    })

                    // 获取后存入本地
                    if (response?.token) {
                        set({ token: response.token, userInfo: response.user_info });
                        Taro.showToast({ title: '登录成功', icon: 'success' });
                    } else {
                        throw new Error('登录失败');
                    }

                } catch (e) {
                    console.error(e);
                    Taro.showToast({ title: e.message || '登录失败', icon: 'none' });
                }
            },
            logout: () => set({ token: null, userInfo: null }),
            loginTest: (token, userInfo) => set({ token, userInfo }), 
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