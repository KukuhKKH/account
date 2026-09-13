export type RolePermission = 'all' | 'canManageUsers' | 'isSuperadmin' | 'isAdminAccount'

export type BadgeVariant = 'default' | 'root' | 'rbac' | 'warning' | 'info'

export interface SidebarBadge {
  text: string
  variant?: BadgeVariant
}

export interface SidebarMenuItem {
  id: string
  title: string
  path: string
  icon: string
  permission?: RolePermission
  badge?: SidebarBadge
  iconClass?: string
}
