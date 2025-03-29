import { View, Text, Image } from "@tarojs/components";
import { menuListProps } from '../props';

/**
 * 横向带图标的菜单
 * @param menuList 菜单数据
 */
const MenuRow = ({ menuList }: menuListProps) => {
    return (
        <View className="px-5 mt-4">
            <View className="w-full py-5 rounded-xl bg-white grid grid-cols-4 gap-y-2">
                {menuList.map((item, index) => (
                    <View key={index} className="flex flex-col items-center justify-center">
                        <Image src={item.icon} className="w-6 h-6" />
                        <Text className="text-xs text-gray-800 mt-2">{item.title}</Text>
                    </View>                    
                ))}
            </View>
        </View>
    )
}

export default MenuRow;