/**
 * Role Constants
 * These should match the constants in App\Models\UserRole
 */
export const ROLES = {
  SUPERADMIN: 'Superadmin',
  ADMIN: 'Admin Account',
  USER: 'User',
} as const

export type RoleName = typeof ROLES[keyof typeof ROLES]

export const ALL_ROLES: RoleName[] = [
  ROLES.SUPERADMIN,
  ROLES.ADMIN,
  ROLES.USER,
]
