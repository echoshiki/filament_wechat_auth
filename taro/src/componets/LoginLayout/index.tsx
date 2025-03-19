import { View, Text, Button, Switch } from "@tarojs/components";
import { ActionSheet } from '@nutui/nutui-react-taro';
import { useState } from "react";
import Taro from "@tarojs/taro";
import useAuthStore from "@/stores/auth";

/**
 * 登录弹窗
 * 通过 isBound 判断渲染按钮触发方式
 * @param isVisible 是否显示
 * @param setIsVisible 设置是否显示
 */

interface LoginActionSheetProps {
    isVisible: boolean;
    setIsVisible: (isVisible: boolean) => void;
}

const LoginActionSheet = ({ isVisible, setIsVisible }: LoginActionSheetProps) => {
    // 用户协议状态
    const [isAgreement, setIsAgreement] = useState(false);

    // 从 store 里解构出登录方法、openid、是否绑定手机号
    const { loginOnBound, login, openid, isBound } = useAuthStore();

    return (
        <ActionSheet
            visible={isVisible}
            cancelText="取消"
            onSelect={() => {
                setIsVisible(false)
            }}
            onCancel={() => setIsVisible(false)}
        >
            <View className="w-4/5 mx-auto pt-12 pb-5">
                <Button
                    className="w-full"
                    type="primary"
                    // 只有同意协议时才启用手机号授权
                    openType={isAgreement && !isBound ? 'getPhoneNumber' : undefined} 
                    onClick={(_e) => {
                        if (!isAgreement) {
                            Taro.showToast({
                                title: '请先同意用户协议',
                                icon: 'none'
                            });
                            return;
                        }
                        if (isBound) {
                            login(openid); 
                        }
                        setIsVisible(false);
                    }}
                    onGetPhoneNumber={(e) => loginOnBound(e, openid)}
                >
                    手机号码一键登录 
                </Button>
                
                <View className="flex items-center space-x-2 mt-3">
                    <Switch type="checkbox" checked={isAgreement} onChange={() => setIsAgreement(!isAgreement)} />
                    <Text className="text-xs"> 我已阅读并同意
                        <Text className="text-blue-600">《用户服务协议》</Text> 及
                        <Text className="text-blue-600">《隐私协议》</Text>
                    </Text>
                </View>
            </View>
        </ActionSheet>
    )
}

export default LoginActionSheet;