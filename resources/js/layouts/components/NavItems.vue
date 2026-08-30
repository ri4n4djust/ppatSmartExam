<script setup>
import VerticalNavSectionTitle from '@/@layouts/components/VerticalNavSectionTitle.vue';
import { useAuthStore } from '@/stores/auth';
import VerticalNavGroup from '@layouts/components/VerticalNavGroup.vue';
import VerticalNavLink from '@layouts/components/VerticalNavLink.vue';
import { computed } from 'vue';

const authStore = useAuthStore();

const currentRole = computed(() => {
  const role = authStore.user?.role ?? authStore.role ?? authStore.userRole ?? 'user';

  return String(role).toLowerCase();
});

const isAdmin = computed(() => ['admin'].includes(currentRole.value));
const isManager = computed(() => ['manager', 'admin', 'dosen'].includes(currentRole.value));
const isUser = computed(() => !isAdmin.value && !isManager.value);

</script>

<template>
  <div v-if="isUser">
    <!-- 👉 Apps & Pages -->
    <VerticalNavSectionTitle
      :item="{
        heading: 'User',
      }"
    />
    <VerticalNavLink
      :item="{
        title: 'User Dashboard',
        icon: 'ri-dashboard-line',
        to: '/user-dashboard',
      }"
    />
    <VerticalNavLink
      :item="{
        title: 'Account Settings',
        icon: 'ri-user-settings-line',
        to: '/account-settings',
      }"
    />
  </div>
  <div v-else>
    <!-- 👉 Apps & Pages -->
    <VerticalNavSectionTitle
      :item="{
        heading: 'admin',
      }"
    />
    <VerticalNavLink
      :item="{
        title: 'Manager Dashboard',
        icon: 'ri-dashboard-line',
        to: '/dashboard',
      }"
    />
  
    <!-- 👉 Dashboards -->
    <VerticalNavGroup
      :item="{
        title: 'Dashboards',
        // badgeContent: '5',
        badgeClass: 'bg-error',
        icon: 'ri-home-smile-line',
      }"
    >
      <VerticalNavLink
        :item="{
          title: 'Analytics',
          to: '/dashboard',
        }"
      />
    </VerticalNavGroup>

    <!-- 👉 Apps & Pages -->
    <VerticalNavSectionTitle
      :item="{
        heading: 'Apps & Pages',
      }"
    />

    <VerticalNavLink
      :item="{
        title: 'Account Settings',
        icon: 'ri-user-settings-line',
        to: '/account-settings',
      }"
    />

    <VerticalNavLink
      :item="{
        title: 'Bank Soal',
        icon: 'ri-user-line',
        to: '/bank-soal',
      }"
    />

    <VerticalNavLink
      v-if="isUser || isManager || isAdmin"
      :item="{
        title: 'Profile',
        icon: 'ri-user-line',
        to: '/profile',
      }"
    />

    <VerticalNavLink
      v-if="!authStore.isAuthenticated"
      :item="{
        title: 'Login',
        icon: 'ri-login-box-line',
        to: '/login',
      }"
    />

    <VerticalNavLink
      v-if="!authStore.isAuthenticated"
      :item="{
        title: 'Register',
        icon: 'ri-user-add-line',
        to: '/register',
      }"
    />

    <VerticalNavLink
      v-if="isAdmin || isManager"
      :item="{
        title: 'Reports',
        icon: 'ri-bar-chart-2-line',
        to: '/reports',
      }"
    />

    <VerticalNavLink
      v-if="isAdmin"
      :item="{
        title: 'Users Management',
        icon: 'ri-user-star-line',
        to: '/users',
      }"
    />

    <VerticalNavLink
      v-if="isManager || isAdmin"
      :item="{
        title: 'Team',
        icon: 'ri-team-line',
        to: '/team',
      }"
    />

    <VerticalNavLink
      :item="{
        title: 'Error',
        icon: 'ri-information-line',
        to: '/no-existence',
      }"
    />

    <!-- 👉 User Interface -->
    <VerticalNavSectionTitle
      :item="{
        heading: 'User Interface',
      }"
    />
    <VerticalNavLink
      :item="{
        title: 'Typography',
        icon: 'ri-text',
        to: '/typography',
      }"
    />
    <VerticalNavLink
      :item="{
        title: 'Icons',
        icon: 'ri-remixicon-line',
        to: '/icons',
      }"
    />
    <VerticalNavLink
      :item="{
        title: 'Cards',
        icon: 'ri-bar-chart-box-line',
        to: '/cards',
      }"
    />

    <!-- 👉 Forms & Tables -->
    <VerticalNavSectionTitle
      :item="{
        heading: 'Forms & Tables',
      }"
    />
    <VerticalNavLink
      :item="{
        title: 'Form Layouts',
        icon: 'ri-layout-4-line',
        to: '/form-layouts',
      }"
    />

    <VerticalNavLink
      :item="{
        title: 'Tables',
        icon: 'ri-table-alt-line',
        to: '/tables',
      }"
    />
  </div>
</template>
