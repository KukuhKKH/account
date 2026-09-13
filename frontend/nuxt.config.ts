import Aura from '@primevue/themes/aura'
import tailwindcss from '@tailwindcss/vite'

// https://nuxt.com/docs/api/configuration/nuxt-config
export default defineNuxtConfig({
  ssr: false,
  compatibilityDate: '2025-07-15',
  devtools: { enabled: true },

  runtimeConfig: {
    public: {
      apiBase: 'https://api-identity.home.test'
    }
  },

  app: {
    head: {
      title: 'BangLipai Identity - Central SSO & Access Management Engine',
      meta: [
        { charset: 'utf-8' },
        { name: 'viewport', content: 'width=device-width, initial-scale=1' },
        { name: 'description', content: 'High-Performance Coroutine Identity & Access Governance Engine for BangLipai Ecosystem, powered by Hypervel & BangLipai Secure Portal.' },
        { name: 'theme-color', content: '#0f172a' }
      ],
      link: [
        { rel: 'preconnect', href: 'https://fonts.googleapis.com' },
        { rel: 'preconnect', href: 'https://fonts.gstatic.com', crossorigin: '' },
        { rel: 'stylesheet', href: 'https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&family=JetBrains+Mono:wght@400;500;600;700&display=swap' }
      ]
    }
  },

  modules: [
    '@primevue/nuxt-module',
    '@pinia/nuxt'
  ],

  css: ['~/assets/css/main.css'],

  vite: {
    plugins: [
      tailwindcss()
    ],
    server: {
      allowedHosts: [
        'identity.home.test',
        'localhost',
        '127.0.0.1',
        '.home.test',
        '.banglipai.web.id'
      ]
    }
  },

  primevue: {
    options: {
      theme: {
        preset: Aura,
        options: {
          darkModeSelector: '.dark'
        }
      }
    }
  }
})
