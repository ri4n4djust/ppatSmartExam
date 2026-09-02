export const routes = [
  {
    path: '/',
    component: () => import('@/layouts/blank.vue'),
    children: [
      {
        path: '',
        component: () => import('@/pages/home.vue'),
      },
    ],
  },
  {
    path: '/',
    component: () => import('@/layouts/default.vue'),
    meta: {
      requiresAuth: true,
    },
    children: [
      {
        path: 'dashboard',
        component: () => import('@/pages/dashboard.vue'),
      },
      {
        path: 'account-settings',
        component: () => import('@/pages/account-settings.vue'),
      },
      {
        path: 'bank-soal',
        component: () => import('@/pages/bank-soal.vue'),
      },
      {
        path: 'jadwal-ujian',
        component: () => import('@/pages/jadwal-ujian.vue'),
      },
      {
        path: 'laporan-ujian',
        component: () => import('@/pages/laporan-ujian.vue'),
      },
      {
        path: 'laporan-ujian-admin',
        component: () => import('@/pages/laporan-ujian-admin.vue'),
      },
      {
        path: 'lembar-ujian',
        component: () => import('@/pages/lembar-ujian.vue'),
      },
      {
        path: 'daftar-siswa',
        component: () => import('@/pages/daftar-siswa.vue'),
      },
      
      {
        path: 'typography',
        component: () => import('@/pages/typography.vue'),
      },
      {
        path: 'icons',
        component: () => import('@/pages/icons.vue'),
      },
      {
        path: 'cards',
        component: () => import('@/pages/cards.vue'),
      },
      {
        path: 'tables',
        component: () => import('@/pages/tables.vue'),
      },
      {
        path: 'form-layouts',
        component: () => import('@/pages/form-layouts.vue'),
      },
    ],
  },
  {
    path: '/',
    component: () => import('@/layouts/blank.vue'),
    children: [
      {
        path: 'login',
        component: () => import('@/pages/login.vue'),
      },
      {
        path: 'register',
        component: () => import('@/pages/register.vue'),
      },
      {
        path: '/:pathMatch(.*)*',
        component: () => import('@/pages/[...error].vue'),
      },
    ],
  },
]
