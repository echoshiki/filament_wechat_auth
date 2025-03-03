import { CommonEvent } from "@tarojs/components";

// 微信登录获取手机号码事件的类型
interface GetPhoneNumberEventDetail {
    errMsg: string;
    code: string;
    encryptedData?: string;
    iv?: string;
}

type GetPhoneNumberEvent = CommonEvent<GetPhoneNumberEventDetail>;

export { GetPhoneNumberEvent };