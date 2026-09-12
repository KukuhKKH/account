<script setup lang="ts">
import HeroSection from '~/components/home/HeroSection.vue'
import ServicesCatalog from '~/components/home/ServicesCatalog.vue'
import ClusterTopology from '~/components/home/ClusterTopology.vue'
import IamPlayground from '~/components/home/IamPlayground.vue'

useHead({
  title: 'BangLipai Identity - Central SSO & Access Management Engine'
})
</script>

<template>
  <div class="space-y-16 sm:space-y-24 pb-20">
    <!-- Hero Banner -->
    <HeroSection />

    <!-- Main Container Sections -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-20 sm:space-y-28">
      <!-- Ecosystem Services -->
      <ServicesCatalog />

      <!-- Cluster Topology & Node Radar -->
      <ClusterTopology />

      <!-- IAM Password Strength & Benchmarks -->
      <IamPlayground />
    </div>
  </div>
</template>
