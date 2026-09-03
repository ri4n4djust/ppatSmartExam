<script setup>
import { useAuthStore } from '@/stores/auth';
import trophy from '@images/misc/trophy.png';

const authStore = useAuthStore()
</script>

<template>
  <VCard class="position-relative">
    <VCardText>
      <div class="mb-2">
        <h5 class="text-h5">
          Selamat Datang, {{ authStore.user.name }}! <span class="text-high-emphasis">🎉</span>
        </h5>
        <div class="text-body-1">
          Nilai Ujian terakhir <span class="text-high-emphasis">😎</span>
        </div>
      </div>
      <h4 class="text-h4 text-primary">
        100
      </h4>
      <!-- <div class="text-body-1 mb-2">
        78% of target <span class="text-high-emphasis">🚀</span>
      </div> -->
      <RouterLink to="/laporan-ujian">
        <VBtn size="small">
          Lihat Hasil Ujian
        </VBtn>
      </RouterLink>
    </VCardText>

    <!-- Trophy -->
    <VImg
      :src="trophy"
      class="trophy"
    />
  </VCard>
</template>

<style lang="scss">
.v-card .trophy {
  position: absolute;
  inline-size: 5.188rem;
  inset-block-end: 1.25rem;
  inset-inline-end: 1.25rem;
}
</style>
