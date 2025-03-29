import { View, Text, Image } from "@tarojs/components";
import avatarRightImg from '@/assets/images/avatar-right-img.svg';
import bottomImg from '@/assets/images/center-bottom-img.svg';

// 横向大图标
import orderBigIcon from '@/assets/icons/shop-icon.svg';
import formBigIcon from '@/assets/icons/point-icon.svg';
import favoriteBigIcon from '@/assets/icons/gift-icon.svg';
import serviceBigIcon from '@/assets/icons/service-icon.svg';

// 列表图标
import phoneIcon from '@/assets/icons/phone-icon.svg';
import agreementIcon from '@/assets/icons/agreement-icon.svg';
import billIcon from '@/assets/icons/bill-icon.svg';
import settingIcon from '@/assets/icons/setting-icon.svg';
import logoutIcon from '@/assets/icons/logout-icon.svg';

import MenuRow from "@/componets/Menu/MenuRow";
import MenuColumn from "@/componets/Menu/MenuColumn";
import BottomCopyright from "@/componets/Copyright";

import useAuthStore from "@/stores/auth";
import UserInfoProps from "@/types/user";
import { checkLogin } from "@/utils/auth";

const menu_row = [
    {
        title: '我的报名',
        icon: formBigIcon,
        url: '/pages/index/index'
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
];

/**
 * 包含昵称、头像以及右边图片的用户信息块
 * @param userInfo 用户信息
 */
const UserInfo = ({ userInfo }: {
    userInfo: UserInfoProps | null
}) => {

    return (
        <View className="max-w-screen-md mx-auto rounded-xl shadow-sm flex flex-nowrap w-full p-4 justify-between items-center bg-white">
            <View className="flex flex-nowrap space-x-3">
                {userInfo ? (
                <View className="flex flex-col justify-center">
                    <Text className="text-lg font-semibold">
                        {userInfo.name}
                    </Text>
                    <Text className="text-sm font-light text-gray-600">
                        {userInfo.phone}
                    </Text>
                </View> 
                ) : (
                <View>
                    <View className="flex flex-col justify-center" onClick={() => checkLogin()}>
                        <Text className="text-lg font-semibold">
                            未登录
                        </Text>
                        <Text className="text-xs font-light text-gray-600">
                            点击立即授权登录
                        </Text>
                    </View>
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

    const { openid, isBound, userInfo, logout, token } = useAuthStore();

    const menu_col_02 = [
        {
            title: '个人资料',
            icon: settingIcon,
            url: '/pages/user/order/index'
        },
        {
            title: '热线电话',
            icon: phoneIcon,
            url: '/pages/user/order/index'
        },
        {
            title: '隐私政策',
            icon: billIcon,
            url: '/pages/user/order/index'
        },
        {
            title: '用户协议',
            icon: agreementIcon,
            url: '/pages/user/order/index'
        },
        {
            title: '退出登录',
            icon: logoutIcon,
            onClick: () => {
                logout();
            }
        },
    ];
    
    console.log('当前 store 中的 openid:', openid);
    console.log('当前 store 中的 isBound:', isBound);
    console.log('当前 store 中的 token:', token);
    console.log('当前 store 中的 userInfo:', userInfo);
    
    // 测试接口
    // const testApi = async () => {
    //     const response = await http.request({
    //         url: '/api/v1/user',
    //         method: 'POST'
    //     });
    //     console.log(response);
    // }

    return (
        <View className="bg-gray-100 min-h-screen pb-10">
            {/* 用户信息块 */}
            <View className="before:(relative block w-full h-32 bg-gray-950 mb-12)">
                <View className="absolute w-full bottom-[-2rem] px-5">
                    <UserInfo userInfo={userInfo} />
                </View>
            </View>

            {/* 横向菜单块 */}  
            <MenuRow menuList={menu_row} />

            {/* 条目菜单块 */}
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