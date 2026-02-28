<template>
  <Head :title="statusCode + ' - Error'" />

  <div class="relative min-h-screen overflow-hidden bg-[#fafafa] text-slate-900 dark:bg-[#050505] dark:text-slate-100 font-sans antialiased">
    <!-- Animated Background Blobs -->
    <div class="fixed inset-0 -z-10 overflow-hidden">
      <div class="absolute -top-[10%] -left-[10%] h-[40%] w-[40%] animate-blob rounded-full blur-[120px]" :style="{backgroundColor: blobColor1Style}"></div>
      <div class="absolute bottom-[10%] right-[10%] h-[30%] w-[30%] animate-blob rounded-full blur-[100px]" :style="{backgroundColor: blobColor2Style}" style="animation-delay: 3s"></div>
    </div>

    <!-- Main Content -->
    <div class="relative flex min-h-screen flex-col items-center justify-center px-6 py-12">
      <!-- Card -->
      <div class="w-full max-w-2xl overflow-hidden rounded-[2.5rem] border border-slate-200 bg-white/40 p-1 shadow-2xl backdrop-blur-2xl dark:border-slate-800 dark:bg-slate-900/40">
        <!-- Split Layout -->
        <div class="flex flex-col md:flex-row">
          <!-- Left: Error Code Section -->
          <div class="relative flex min-h-60 flex-1 items-center justify-center overflow-hidden rounded-4xl p-8 text-white shadow-inner md:min-h-full" :style="{backgroundImage: gradientCSS}">
            <div class="absolute inset-0 opacity-10 bg-grid-pattern"></div>
            <div class="relative flex flex-col items-center text-center">
              <!-- Icon -->
              <div class="mb-4 flex h-24 w-24 items-center justify-center rounded-3xl bg-white/20 shadow-2xl backdrop-blur-md">
                <component :is="iconComponent" :class="[`h-12 w-12 text-white`, animationClass]" />
              </div>
              <!-- Code Label -->
              <span class="text-xs font-black uppercase tracking-[0.4em] opacity-70">Error Code</span>
              <!-- Status Code -->
              <h1 class="text-7xl font-black tracking-tighter">{{ statusCode }}</h1>
            </div>
          </div>

          <!-- Right: Message Section -->
          <div class="flex-[1.2] p-8 md:p-12">
            <!-- Title -->
            <h2 class="text-3xl font-extrabold tracking-tight sm:text-4xl leading-tight">
              <span :class="titleColor">{{ title }}</span>
            </h2>

            <!-- Content -->
            <div class="mt-6 space-y-4">
              <!-- Message -->
              <p class="text-lg leading-relaxed text-slate-600 dark:text-slate-400">
                {{ message }}
              </p>

              <!-- Info Box -->
              <div :class="['flex items-center gap-3 rounded-2xl p-4', infoBoxColor]">
                <div :class="['flex h-8 w-8 shrink-0 items-center justify-center rounded-full', infoBgColor]">
                  <component :is="infoIconComponent" class="h-4 w-4" />
                </div>
                <p class="text-xs font-medium text-slate-500 dark:text-slate-400">
                  {{ infoText }}
                </p>
              </div>
            </div>

            <!-- Actions -->
            <div class="mt-10 flex flex-col gap-3 sm:flex-row">
              <!-- Back Button -->
              <button
                @click="goBack"
                class="group flex flex-1 items-center justify-center gap-2 rounded-2xl border border-slate-200 bg-white px-6 py-4 text-sm font-bold transition-all hover:bg-slate-50 active:scale-95 dark:border-slate-800 dark:bg-slate-900 dark:hover:bg-slate-800"
              >
                <ArrowLeft class="h-4 w-4 transition-transform group-hover:-translate-x-1" />
                Kembali
              </button>

              <!-- Home Button or Reload Button -->
              <button
                v-if="statusCode === 500 || statusCode === 503"
                @click="reload"
                class="group flex flex-[1.5] items-center justify-center gap-2 rounded-2xl bg-slate-900 px-6 py-4 text-sm font-bold text-white transition-all hover:bg-slate-800 active:scale-95 dark:bg-white dark:text-slate-900 dark:hover:bg-slate-100"
              >
                <RefreshCw class="h-4 w-4 transition-transform group-hover:rotate-180 duration-500" />
                Coba Lagi
              </button>
              <Link
                v-else
                href="/"
                class="group flex flex-[1.5] items-center justify-center gap-2 rounded-2xl bg-slate-900 px-6 py-4 text-sm font-bold text-white transition-all hover:bg-slate-800 active:scale-95 dark:bg-white dark:text-slate-900 dark:hover:bg-slate-100"
              >
                <Home class="h-4 w-4" />
                Beranda Utama
              </Link>
            </div>
          </div>
        </div>
      </div>

      <!-- Footer -->
      <p class="mt-12 text-center text-sm font-medium text-slate-500">
        Butuh bantuan? <a href="https://wa.me/6283845257534" class="text-blue-600 underline-offset-4 hover:underline">Hubungi Tim BangLipai</a>
      </p>
    </div>
  </div>
</template>

<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3'
import { computed } from 'vue'
import {
  ShieldAlert,
  FileSearch,
  Clock,
  ServerCrash,
  AlertTriangle,
  Lock,
  Info,
  AlertCircle,
  Activity,
  Wrench,
  ArrowLeft,
  Home,
  RefreshCw,
} from 'lucide-vue-next'

interface Props {
  statusCode: number
  title: string
  icon: string
  animation: string
  blobColor1: string
  blobColor2: string
  gradientCSS: string
  titleColor: string
  infoIcon: string
  infoBgColor: string
  infoBoxColor: string
  message: string
  infoText: string
  debug?: boolean
  debugMessage?: string
}

const props = withDefaults(defineProps<Props>(), {
  debug: false,
  debugMessage: '',
})

// Icon map
const iconMap: Record<string, any> = {
  ShieldAlert,
  FileSearch,
  Clock,
  ServerCrash,
  AlertTriangle,
}

const infoIconMap: Record<string, any> = {
  Lock,
  Info,
  AlertCircle,
  Activity,
  Wrench,
}

// Computed properties
const iconComponent = computed(() => iconMap[props.icon] || AlertTriangle)
const infoIconComponent = computed(() => infoIconMap[props.infoIcon] || Info)
const animationClass = computed(() => {
  if (props.animation === 'pulse-slow') return 'animate-pulse-slow'
  if (props.animation === 'bounce-slow') return 'animate-bounce-slow'
  return 'animate-pulse-slow'
})

// Blob color styles
const blobColor1Style = computed(() => {
  const colors: Record<string, string> = {
    red: 'rgb(239 68 68 / 0.1)',
    blue: 'rgb(37 99 235 / 0.1)',
    yellow: 'rgb(234 179 8 / 0.1)',
    orange: 'rgb(249 115 22 / 0.1)',
    purple: 'rgb(168 85 247 / 0.1)',
  }
  return colors[props.blobColor1] || colors.red
})

const blobColor2Style = computed(() => {
  const colors: Record<string, string> = {
    rose: 'rgb(225 29 72 / 0.1)',
    indigo: 'rgb(79 70 229 / 0.1)',
    amber: 'rgb(217 119 6 / 0.1)',
    violet: 'rgb(109 40 217 / 0.1)',
  }
  return colors[props.blobColor2] || colors.rose
})

// Methods
const goBack = () => {
  if (window.history.length > 1) {
    window.history.back()
  } else {
    router.visit('/')
  }
}

const reload = () => {
  router.reload()
}
</script>

<style scoped>
.bg-grid-pattern {
  background-image: radial-gradient(circle, currentColor 1px, transparent 1px);
  background-size: 24px 24px;
}

.animate-blob {
  animation: blob 15s infinite ease-in-out;
}

.animate-pulse-slow {
  animation: pulse 3s cubic-bezier(0.4, 0, 0.6, 1) infinite;
}

.animate-bounce-slow {
  animation: bounce 3s infinite ease-in-out;
}

@keyframes blob {
  0%, 100% { transform: translate(0, 0) scale(1); }
  33% { transform: translate(-30px, 50px) scale(1.1); }
  66% { transform: translate(20px, -20px) scale(0.9); }
}

@keyframes pulse {
  0%, 100% { opacity: 1; transform: scale(1); }
  50% { opacity: .7; transform: scale(0.95); }
}

@keyframes bounce {
  0%, 100% { transform: translateY(0); }
  50% { transform: translateY(-10px); }
}
</style>
