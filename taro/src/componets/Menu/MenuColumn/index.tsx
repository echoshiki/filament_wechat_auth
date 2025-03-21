import { View, Text, Image } from "@tarojs/components";
import { menuItemProps, menuListProps} from '../props';
import rightIcon from '@/assets/icons/right-icon.svg';
import { mapsTo } from "@/utils/common";

/**
 * 用户中心条目菜单
 * @param menuList 菜单数据
 */
const MenuColumn = ({ menuList }: menuListProps) => {

    const handleItemClick = (item: menuItemProps) => {
        if (item.onClick) {
            item.onClick();
            return;
        }
        if (item.url) mapsTo(item.url);
    }

    return (
        <View className="px-5 mt-4">
            <View className="bg-white rounded-xl">
                {menuList.map((item, index) => (
                    <View key={index} className="flex items-center justify-between p-4 border-b border-gray-100" onClick={() => handleItemClick(item)}>
                        <View className="flex items-center">
                            <Image src={item.icon} className="w-5 h-5" />
                            <Text className="text-gray-800 ml-3">{item.title}</Text>
                        </View>
                        <Image src={rightIcon} className="w-4 h-4" />
                    </View>
                ))}
            </View>
        </View>
    )
}

export default MenuColumn;