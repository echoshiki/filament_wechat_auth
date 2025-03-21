interface menuItemProps {
    title: string,
    icon: string,
    url?: string,
    onClick?: () => void,
}

interface menuListProps {
    menuList: menuItemProps[]
}

export { menuListProps, menuItemProps };