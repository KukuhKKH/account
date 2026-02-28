<script setup lang="ts">
import { Head, Link, usePage } from '@inertiajs/vue3'
import { route } from 'ziggy-js'
import { computed } from 'vue'
import { Activity, ArrowRight, Shield, Users, Zap } from 'lucide-vue-next'
import type { Auth } from '../types/auth'
import ToastContainer from '@/components/ToastContainer.vue'

const page = usePage<{ auth: Auth }>()
const auth = computed(() => page.props.auth)
</script>

<template>
  <Head title="Welcome" />
  <ToastContainer />

  <div class="relative min-h-screen overflow-hidden bg-white text-slate-900 dark:bg-slate-950 dark:text-slate-100">


    <!-- Content -->
    <div class="relative flex min-h-screen flex-col items-center justify-center px-6 py-12">
      <!-- Logo Section -->
      <div class="group relative mb-10">
        <div class="absolute -inset-1 rounded-2xl bg-gradient-to-r from-blue-600 to-indigo-600 opacity-25 blur transition duration-1000 group-hover:opacity-50 group-hover:duration-200" />
        <div class="relative flex h-20 w-20 items-center justify-center rounded-2xl bg-white shadow-xl dark:bg-slate-900">
          <Zap class="h-10 w-10 text-blue-600" />
        </div>
      </div>

      <!-- Hero Section -->
      <div class="max-w-4xl text-center">
        <h1 class="text-5xl font-extrabold tracking-tight sm:text-7xl text-balance">
          Elevate Your
          <span class="bg-gradient-to-r from-blue-600 to-indigo-500 bg-clip-text text-transparent">
            Identity
          </span>
          Control
        </h1>

        <p class="mx-auto mt-8 max-w-2xl text-lg leading-relaxed text-slate-600 dark:text-slate-400">
          Seamlessly manage your professional profile with the power of
          <span class="font-semibold text-nowrap text-slate-900 underline decoration-blue-500/30 underline-offset-4 dark:text-white">
            BangLipai Secure Portal
          </span>
          . The next generation of Identity Access Management.
        </p>

        <!-- CTA Buttons -->
        <div class="mt-12 flex flex-col items-center justify-center gap-4 sm:flex-row">
          <template v-if="auth.user">
            <Link
              href="/dashboard"
              class="group inline-flex items-center gap-2 rounded-full bg-slate-900 px-8 py-4 text-sm font-bold text-white transition-all hover:scale-105 hover:bg-slate-800 dark:bg-white dark:text-slate-900 dark:hover:bg-slate-100"
            >
              Go to Dashboard
              <ArrowRight class="h-4 w-4 transition-transform group-hover:translate-x-1" />
            </Link>
          </template>
          <template v-else>
            <a
              :href="route('auth.login')"
              class="group inline-flex items-center gap-2 rounded-full bg-blue-600 px-8 py-4 text-sm font-bold text-white transition-all hover:scale-105 hover:bg-blue-700 hover:shadow-[0_0_30px_rgba(37,99,235,0.4)]"
            >
              Enter Secure Portal
              <ArrowRight class="h-4 w-4 transition-transform group-hover:translate-x-1" />
            </a>
          </template>

          <button
            type="button"
            class="inline-flex items-center gap-2 rounded-full border border-slate-200 bg-white/50 px-8 py-4 text-sm font-bold backdrop-blur-md transition-all hover:bg-white dark:border-slate-800 dark:bg-slate-900/50 dark:hover:bg-slate-900"
          >
            <a href="https://instagram.com/kukuhkkh" target="_blank" class="text-sm font-bold text-slate-900 underline decoration-blue-500/30 underline-offset-4 dark:text-white">
                Learn More
            </a>
          </button>
        </div>
      </div>

      <!-- Features Section -->
      <div class="mt-28 grid w-full max-w-6xl gap-6 sm:grid-cols-3">
        <!-- Feature Card 1 -->
        <div class="group relative overflow-hidden rounded-3xl border border-slate-200 bg-white/50 p-8 backdrop-blur-sm transition-all hover:-translate-y-2 hover:border-blue-300 dark:border-slate-800 dark:bg-slate-900/40">
          <div class="mb-5 inline-flex rounded-xl bg-blue-50 p-3 text-blue-600 dark:bg-blue-900/30 dark:text-blue-400">
            <Users class="h-6 w-6" />
          </div>
          <h3 class="text-xl font-bold">User Management</h3>
          <p class="mt-3 text-sm leading-relaxed text-slate-500 dark:text-slate-400">
            Granular role-based access control with real-time sync across your entire ecosystem.
          </p>
        </div>

        <!-- Feature Card 2 -->
        <div class="group relative overflow-hidden rounded-3xl border border-blue-200 bg-blue-600/5 p-8 backdrop-blur-sm transition-all hover:-translate-y-2 hover:shadow-2xl dark:border-blue-900/50 dark:bg-blue-900/20">
          <div class="mb-5 inline-flex rounded-xl bg-blue-600 p-3 text-white">
            <Shield class="h-6 w-6" />
          </div>
          <h3 class="text-xl font-bold">BFF Architecture</h3>
          <p class="mt-3 text-sm leading-relaxed text-slate-500 dark:text-slate-400">
            Backend-for-Frontend pattern ensures your tokens are never exposed to the client side.
          </p>
        </div>

        <!-- Feature Card 3 -->
        <div class="group relative overflow-hidden rounded-3xl border border-slate-200 bg-white/50 p-8 backdrop-blur-sm transition-all hover:-translate-y-2 hover:border-green-300 dark:border-slate-800 dark:bg-slate-900/40">
          <div class="mb-5 inline-flex rounded-xl bg-green-50 p-3 text-green-600 dark:bg-green-900/30 dark:text-green-400">
            <Activity class="h-6 w-6" />
          </div>
          <h3 class="text-xl font-bold">Live Analytics</h3>
          <p class="mt-3 text-sm leading-relaxed text-slate-500 dark:text-slate-400">
            Monitor sign-in logs and device information with high-fidelity real-time tracking.
          </p>
        </div>
      </div>

      <!-- Footer Text -->
      <p class="mt-16 text-xs font-medium tracking-widest text-slate-400 uppercase">
        Trusted by BangLipai Ecosystem
      </p>
    </div>
  </div>
</template>
