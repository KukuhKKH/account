import { defineStore } from 'pinia'

export const useThemeStore = defineStore('theme', () => {
  const isDarkMode = ref(true)

  function initTheme() {
    if (typeof window !== 'undefined') {
      const savedTheme = localStorage.getItem('theme')
      if (savedTheme === 'light') {
        isDarkMode.value = false
        document.documentElement.classList.remove('dark')
      } else {
        isDarkMode.value = true
        document.documentElement.classList.add('dark')
      }
    }
  }

  function toggleTheme() {
    isDarkMode.value = !isDarkMode.value
    if (typeof window !== 'undefined') {
      if (isDarkMode.value) {
        document.documentElement.classList.add('dark')
        localStorage.setItem('theme', 'dark')
      } else {
        document.documentElement.classList.remove('dark')
        localStorage.setItem('theme', 'light')
      }
    }
  }

  return {
    isDarkMode,
    initTheme,
    toggleTheme
  }
})
