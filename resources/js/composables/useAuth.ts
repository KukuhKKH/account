import { computed, ComputedRef } from 'vue'
import { usePage } from '@inertiajs/vue3'
import type { User } from '@/types/auth'
import { ROLES } from '@/constants/roles'

export interface UseAuth {
  user: ComputedRef<User | null>
  hasRole: (role: string) => boolean
  hasAnyRole: (roles: string[]) => boolean
  isAdmin: ComputedRef<boolean>
  isSuperadmin: ComputedRef<boolean>
  canManageUsers: ComputedRef<boolean>
  getRoleBadgeClass: (roles?: string[]) => string
  getRoleDisplay: (roles?: string[]) => string
}

export function useAuth(): UseAuth {
  const page = usePage()

  const user = computed<User | null>(() => {
    const auth = page.props.auth as { user?: User | null }
    return auth?.user || null
  })

  const hasRole = (role: string): boolean => {
    return user.value?.roles?.includes(role) || false
  }

  const hasAnyRole = (roles: string[]): boolean => {
    return roles.some(role => hasRole(role))
  }

  const isSuperadmin = computed(() => hasRole(ROLES.SUPERADMIN))
  const isAdmin = computed(() => hasRole(ROLES.ADMIN))
  const canManageUsers = computed(() => hasRole(ROLES.SUPERADMIN) || hasRole(ROLES.ADMIN))

  const getRoleBadgeClass = (roles?: string[]): string => {
    const userRoles = roles || user.value?.roles || []

    if (userRoles.includes(ROLES.SUPERADMIN)) {
      return 'bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-200'
    }

    if (userRoles.includes(ROLES.ADMIN)) {
      return 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200'
    }

    return 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200'
  }

  const getRoleDisplay = (roles?: string[]): string => {
    const userRoles = roles || user.value?.roles || []

    if (userRoles.length === 0) {
      return ROLES.USER
    }

    // Show the highest priority role
    if (userRoles.includes(ROLES.SUPERADMIN)) {
      return ROLES.SUPERADMIN
    }

    if (userRoles.includes(ROLES.ADMIN)) {
      return ROLES.ADMIN
    }

    // Return first role or 'User' if only default role
    return userRoles[0] || ROLES.USER
  }

  return {
    user,
    hasRole,
    hasAnyRole,
    isAdmin,
    isSuperadmin,
    canManageUsers,
    getRoleBadgeClass,
    getRoleDisplay,
  }
}
