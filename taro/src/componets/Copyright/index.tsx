import { View, Image, Text } from "@tarojs/components";

const BottomCopyright = ({ content, bottomImg }: {
    content: string,
    bottomImg?: string,
}) => {
    return (
        <View className="w-full flex flex-col px-12 pb-10 mt-3 text-center">
            {bottomImg && (
                <Image
                    className="w-32 h-28 opacity-75 mx-auto"
                    src={bottomImg}
                />
            )}
            <Text className="text-gray-600 font-light text-xs mt-2">
                {content}
            </Text>
        </View>
    )
}

export default BottomCopyright;