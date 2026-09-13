/**
 * Composable for managing user avatars with fallback to https://avatar.banglipai.web.id/
 */
export function useAvatar() {
  /**
   * Generates a valid avatar URL for a user.
   * If a custom avatar is provided, it will be used.
   * Otherwise, it falls back to the BangLipai Avatar microservice at https://avatar.banglipai.web.id/
   */
  function getAvatarUrl(
    userOrName?: string | { name?: string; email?: string; avatar?: string | null; avatarUrl?: string | null } | null,
    optionalEmail?: string
  ): string {
    if (!userOrName) {
      return 'https://avatar.banglipai.web.id/?name=User'
    }

    // If string is passed
    if (typeof userOrName === 'string') {
      const seed = userOrName.trim() || optionalEmail?.trim() || 'User'
      return `https://avatar.banglipai.web.id/?name=${encodeURIComponent(seed)}`
    }

    // If user object is passed
    if (userOrName.avatar && userOrName.avatar.trim().length > 0) {
      return userOrName.avatar
    }

    if (userOrName.avatarUrl && userOrName.avatarUrl.trim().length > 0) {
      return userOrName.avatarUrl
    }

    const seed = userOrName.name?.trim() || userOrName.email?.trim() || 'User'
    return `https://avatar.banglipai.web.id/?name=${encodeURIComponent(seed)}`
  }

  return {
    getAvatarUrl
  }
}
