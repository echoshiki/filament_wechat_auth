
import { View, Text, Image, Button } from "@tarojs/components"
import Taro, { useRouter } from "@tarojs/taro"
import { useEffect, useState } from "react";
import useAuthStore from "@/stores/auth";

import loginBg from '@/assets/images/login-bg.jpeg';
import logo from '@/assets/images/logo.jpg'

const Login = () => {
    const router = useRouter();
    const [redirectUrl, setRedirectUrl] = useState('');

    // 从 store 里解构出登录方法、openid、是否绑定手机号
    const { loginOnBound, login, openid, isBound } = useAuthStore();

    useEffect(() => {
        if (router.params.redirect) {
            // 解码 URL 参数中的 redirect 参数
            setRedirectUrl(decodeURIComponent(router.params.redirect as string));
        } else {
            setRedirectUrl('/pages/index/index');
        }
    }, [router.params]);

    return (

        <View className="relative w-screen h-screen overflow-hidden">
            <Image src={loginBg} className="w-full h-full absolute top-0 left-0 z-[-1]" />
            <View className="flex flex-col items-center justify-center h-full p-10">  
                {/* Logo */}
                <Image src={logo} mode={`widthFix`} className="w-2/3 h-auto" />
                {/* 隐私条款 */}
                <View className="border border-gray-400 rounded-lg w-full mt-20 p-5">
                    <View>
                        <Text>尊敬的用户，我们深知个人信息对您的重要性，在您使用卫航小程序前请仔细阅读《卫航房车小程序隐私政策》，了解相关协议的各项规则，包括收集、处理、使用、储存正常运行所必须的个人信息。</Text>
                    </View>
                    <View className="mt-3">
                        <Text>我们承诺将今全力保障您的个人信息和合法权益，再次感谢您的信任。</Text>
                    </View>
                    <View className="mt-3">
                        <a href="">《卫航房车小程序隐私政策》</a>
                        <a href="">《小程序用户服务协议》</a>
                    </View>
                </View>
                <View className="flex flex-col items-center justify-center w-full mt-10">
                    <Button 
                        className="w-full bg-gray-950 rounded-full text-white text-sm py-3"
                        openType={!isBound ? 'getPhoneNumber' : undefined}
                        onClick={isBound ? () => login(openid, redirectUrl) : undefined}
                        onGetPhoneNumber={(e) => loginOnBound(e, openid, redirectUrl)}
                    >
                        同意并继续
                    </Button>
                    <a href="" onClick={() => Taro.navigateBack()} className="mt-5 text-sm">不同意，仅浏览</a>
                </View>
            </View>
        </View>
    )
}

export default Login;