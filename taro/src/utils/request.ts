import Taro from '@tarojs/taro'
import useAuthStore from '@/stores/auth'
import { getCurrentPageUrl } from './common'

// 继承 Taro 原生请求类型，额外增加拦截器类型
// 拦截器分别传入请求或者响应到各自的方法内进行修改
type RequestConfig = Taro.request.Option & {
    interceptors?: {
        request?: (config: RequestConfig) => RequestConfig
        response?: (response: Taro.request.SuccessCallbackResult<any>) => any
    }
}

// 创建一个名为 HttpRequest 的类
class HttpRequest {

    private baseUrl: string = process.env.TARO_APP_API || '';

    // 私有内部基础请求类
    // 用来封装 Taro.request 方法，返回一个 Promise 类型
    private async baseOptions(config: RequestConfig): Promise<any> {
        return new Promise((resolve, reject) => {
            Taro.request({
                ...config,
                success: (res) => {
                    if (res.statusCode >= 200 && res.statusCode < 300) {
                        resolve(res.data)
                    } else {
                        reject(this.handleError(res))
                    }
                },
                fail: (error) => reject(this.handleError(error))
            })
        })
    }

    // 封装了错误信息
    private handleError(error: any) {
        const err = new Error() as any
        err.name = 'RequestError'
        err.message = error.errMsg || '网络连接异常'
        err.code = error.statusCode || 'NETWORK_ERROR'
        err.data = error.data
        return err
    }

    public async request<T = any>(config: RequestConfig): Promise<T> {
        // 请求拦截
        const authStore = useAuthStore.getState()
        // 报错：defaultConfig 缺少必选属性 url（ts）
        // 解决：Partial 将 RequestConfig 的类型项全变成可选
        const defaultConfig: Partial<RequestConfig> = {
            header: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                Authorization: authStore.token ? `Bearer ${authStore.token}` : ''
            },
            timeout: 15000
        }

        // 合并配置
        const mergedConfig = { ...defaultConfig, ...config }

        // 创建一个新的配置量代替常量 mergedConfig 配置
        let interceptorConfig = mergedConfig

        // 拼接 baseUrl 到 url 上，如果 url 已经是完整路径则不处理
        if (interceptorConfig.url && !interceptorConfig.url.startsWith('http') &&  !interceptorConfig.url.startsWith('https')) {
            interceptorConfig = {
                ...interceptorConfig,
                url: this.baseUrl + mergedConfig.url
            }
        }
   
        // 自定义请求拦截
        if (mergedConfig.interceptors?.request) {
            interceptorConfig = mergedConfig.interceptors.request(mergedConfig)
        }

        try {
            const response = await this.baseOptions(interceptorConfig)

            // 自定义响应拦截
            if (interceptorConfig.interceptors?.response) {
                return interceptorConfig.interceptors.response(response)
            }

            return response
        } catch (error) {
            this.handleAuthError(error)
            throw error
        }
    }

    // 过期或者授权失败重新登录
    private handleAuthError(error: any) {
        if (error.code === 401) {
            const redirectUrl = getCurrentPageUrl();
            console.log('redirectUrl', redirectUrl);
            // useAuthStore.getState().logout();
            Taro.navigateTo({ 
                url: `/pages/login/index?redirect=${redirectUrl}` 
            });
        }
    }

    // 再次将上面的方法进行快捷方法封装
    public get<T = any>(url: string, config?: RequestConfig) {
        return this.request<T>({ ...config, url, method: 'GET' })
    }

    public post<T = any>(url: string, data?: any, config?: RequestConfig) {
        return this.request<T>({ ...config, url, data, method: 'POST' })
    }

    // 支持取消请求（需要 Taro v3.4+）
    public cancelableRequest(config: RequestConfig) {
        const controller = new AbortController()
        const task = Taro.request({
            ...config,
            signal: controller.signal
        })
        return {
            task,
            cancel: () => controller.abort()
        }
    }
}

export const http = new HttpRequest()