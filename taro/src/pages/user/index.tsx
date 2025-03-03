import { View, Text, Image, Button } from "@tarojs/components";
import defaultAvatar from '@/assets/images/default-avatar.svg';
import avatarRightImg from '@/assets/images/avatar-right-img.svg';
import bottomImg from '@/assets/images/center-bottom-img.svg';

// 横向大图标
import orderBigIcon from '@/assets/icons/shop-icon.svg';
import formBigIcon from '@/assets/icons/point-icon.svg';
import favoriteBigIcon from '@/assets/icons/gift-icon.svg';
import serviceBigIcon from '@/assets/icons/service-icon.svg';

// 列表图标
import formIcon from '@/assets/icons/form-icon.svg';
import phoneIcon from '@/assets/icons/phone-icon.svg';
import favoriteIcon from '@/assets/icons/favorite-icon.svg';
import orderIcon from '@/assets/icons/order-icon.svg';
import editorIcon from '@/assets/icons/editor-icon.svg';

import MenuRow from "@/componets/Menu/MenuRow";
import MenuColumn from "@/componets/Menu/MenuColumn";
import BottomCopyright from "@/componets/Copyright";

import useAuthStore from "@/stores/auth";
import UserInfoProps from "@/types/user";

import { useState } from "react";
import { GetPhoneNumberEvent } from "./props";

import Taro from "@tarojs/taro";
import LoginActionSheet from "@/componets/LoginLayout";
import { http } from "@/utils/request";

const menu_row = [
    {
        title: '我的报名',
        icon: formBigIcon,
        url: '/pages/user/order/index'
    },
    {
        title: '我的订单',
        icon: orderBigIcon,
        url: '/pages/user/order/index'
    },
    {
        title: '收藏夹',
        icon: favoriteBigIcon,
        url: '/pages/user/order/index'
    },
    {
        title: '在线客服',
        icon: serviceBigIcon,
        url: '/pages/user/order/index'
    }
]

const menu_col_01 = [
    {
        title: '我的报名',
        icon: formIcon,
        url: '/pages/user/order/index'
    },
    {
        title: '我的订单',
        icon: orderIcon,
        url: '/pages/user/order/index'
    },
    {
        title: '收藏夹',
        icon: favoriteIcon,
        url: '/pages/user/order/index'
    }
];

const menu_col_02 = [
    {
        title: '个人资料',
        icon: editorIcon,
        url: '/pages/user/order/index'
    },
    {
        title: '联系我们',
        icon: phoneIcon,
        url: '/pages/user/order/index'
    },
];

/**
 * 包含昵称、头像以及右边图片的用户信息块
 * @param userInfo 用户信息
 */
const UserInfo = ({ userInfo }: {
    userInfo: UserInfoProps | null
    onGetPhoneNumber: (e: GetPhoneNumberEvent, isAgreement: boolean) => void
}) => {
    // 登录框状态
    const [isVisible, setIsVisible] = useState(false);

    return (
        <View className="max-w-screen-md mx-auto rounded-xl shadow-sm flex flex-nowrap w-full p-4 justify-between items-center bg-white">
            <View className="flex flex-nowrap space-x-3">
                <View className="w-12 h-12 border border-gray-300 rounded-full">
                    <Image
                        className="w-full h-full"
                        src={userInfo?.avatar || defaultAvatar}
                    />
                </View>
                {userInfo ? (
                    <View className="flex flex-col justify-center">
                        <Text className="text-lg font-semibold">
                            {userInfo.nickname}
                        </Text>
                        <Text className="text-xs font-light text-gray-600">
                            ID:{userInfo.id}
                        </Text>
                    </View> 
                ) : (
                    <View>
                        <View className="flex flex-col justify-center" onClick={() => setIsVisible(true)}>
                            <Text className="text-lg font-semibold">
                                未登录
                            </Text>
                            <Text className="text-xs font-light text-gray-600">
                                点击立即授权登录
                            </Text>
                        </View>
                        {/* 登录框 */}
                        <LoginActionSheet 
                            isVisible={isVisible} 
                            setIsVisible={setIsVisible}
                        />
                    </View>
                )}
            </View>
            <View>
                <Image
                    className="w-20 h-20"
                    src={avatarRightImg}
                />
            </View>
        </View>
    )
}

/**
 * 用户中心
 */
const UserCenter = () => {

    const { userInfo, loginTest, logout, token } = useAuthStore();

    console.log('当前store中的token:', token);

    const handleGetPhoneNumber = async (e: GetPhoneNumberEvent) => { 
        try {
            const loginRes = await Taro.login();
            console.log(loginRes);

            if (e.detail.errMsg === 'getPhoneNumber:ok') {
                console.log(e.detail);
            }
        } catch (e) {
            console.log(e);
        }
    }

    const testApi = async () => {
        const response = await http.request({
            url: '/addons/wxauth/index/login',
            method: 'POST',
            data: {
                login_code: '1',
                phone_code: '2'
            }
        })
        console.log(response);

        if (response.data.token) {
            loginTest(response.data.token, response.data.userInfo);
            Taro.showToast({ title: '登录成功', icon: 'success' });
        } else {
            throw new Error('登录失败');
        }
    }

    const testGetUser = async () => {
        // 没携带 token
        const response = await http.request({
            url: '/addons/wxauth/index/info',
            method: 'POST',
            data: {}
        })
        console.log(response);
    }

    return (
        <View className="bg-gray-100 min-h-screen pb-10">
            {/* 用户信息块 */}
            <View className="before:(relative block w-full h-32 bg-gray-950 mb-12)">
                <View className="absolute w-full bottom-[-2rem] px-5">
                    <UserInfo 
                        userInfo={userInfo} 
                        onGetPhoneNumber={handleGetPhoneNumber}
                    />
                </View>
            </View>


            <Button onClick={testApi}>Test Login</Button>
            <Button onClick={testGetUser}>getUserInfo</Button>
            <Button onClick={logout}>Test Logout</Button>
            

            {/* 横向菜单块 */}  
            <MenuRow menuList={menu_row} />

            {/* 条目菜单块 */}
            <MenuColumn menuList={menu_col_01} />
            <MenuColumn menuList={menu_col_02} />

            {/* 底部版权信息 */}
            <BottomCopyright
                content="版权所有 © 2025 云铺网络" 
                bottomImg={bottomImg}
            />
        </View>
    );
}

export default UserCenter;