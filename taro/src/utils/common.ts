import Taro from '@tarojs/taro';

/**
 * 判断当前页面是否在 tabBar 中
 * @param path 当前页面路径
 * @returns 是否在 tabBar 中
 */
const isTabBarPage = (path: string): boolean => {

    const appConfig = Taro.getApp().config;

    if (appConfig.tabBar && appConfig.tabBar.list) {
        // 检查当前 path 是否以斜杠开头，如果是去掉斜杠，如果不是直接返回
        const normalizedPath = path.startsWith('/') ? path.substring(1) : path;
        // 检查当前 path 是否在 tabBar 中
        return appConfig.tabBar.list.some(item => item.pagePath === normalizedPath);
    }
    return false;
}

/**
 * 获取当前页面URL（包含参数）
 * @returns 当前页面URL（包含参数）
 */
const getCurrentPageUrl = (): string => {
    // 获取所有打开的页面实例数组
    const pages = Taro.getCurrentPages();

    // 获取数组中最后一个页面实例
    const currentPage = pages[pages.length - 1];

    // 获取当前页面路由路径
    const route = `/${currentPage.route}`;

    // 获取当前页面路由参数
    const params = currentPage.options;

    let queryString = '';

    // 如果当前页面路由参数不为空，则拼接参数
    // entire 将 params 对象转换成包含键值对的数组
    // 最后遍历数组，将键值对组合成字符串
    if (Object.keys(params).length > 0) {
        queryString = '?' + Object.entries(params)
          .map(([key, value]) => `${key}=${value}`)
          .join('&');
    }

    // 返回当前页面路由路径和参数
    return route + queryString;
}

/**
 * 自定义跳转函数
 */
const mapsTo = (url: string) => {
    // 获取 url 中的路径部分，不包含参数
    const pathWithoutParams = url.split('?')[0];

    // 判断当前页面是否在 tabBar 中
    if (isTabBarPage(pathWithoutParams)) {
        Taro.switchTab({ url });
    } else {
        Taro.navigateTo({ url });
    }
}

export {
    isTabBarPage,
    getCurrentPageUrl,
    mapsTo
};
