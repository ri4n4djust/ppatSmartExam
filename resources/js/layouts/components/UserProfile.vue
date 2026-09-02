<script setup>
import { useAuthStore } from '@/stores/auth'
import avatar1 from '@images/avatars/avatar-1.png'

const authStore = useAuthStore()
const isLoggingOut = ref(false)
const logoutError = ref('')

const getCsrfHeaders = () => {
  const metaToken = document.querySelector('meta[name="csrf-token"]')?.content ?? ''
  const xsrfCookie = document.cookie
    .split('; ')
    .find(row => row.startsWith('XSRF-TOKEN='))
    ?.split('=')[1]

  const xsrfToken = xsrfCookie ? decodeURIComponent(xsrfCookie) : ''


  return {
    Accept: 'application/json',
    ...(metaToken ? { 'X-CSRF-TOKEN': metaToken } : {}),
    ...(xsrfToken ? { 'X-XSRF-TOKEN': xsrfToken } : {}),
  }
}

const getFreshCsrfToken = async () => {
  const response = await fetch('/csrf-token', {
    credentials: 'same-origin',
    headers: { Accept: 'application/json' },
  })

  if (!response.ok)
    throw new Error('Unable to refresh the security token.')

  const { token } = await response.json()

  return token
}

const logout = async () => {
  if (isLoggingOut.value)
    return

  logoutError.value = ''
  isLoggingOut.value = true

  try {
    const token = await getFreshCsrfToken()

    const response = await fetch('/auth/logout', {
      method: 'POST',
      headers: {
        ...getCsrfHeaders(),
        'X-CSRF-TOKEN': token,
      },
      credentials: 'same-origin',
    })

    if (!response.ok && response.status !== 401)
      throw new Error('Unable to log out.')

    authStore.clearUser()
    window.location.assign('/login')
  }
  catch {
    logoutError.value = 'Unable to log out. Please try again.'
  }
  finally {
    isLoggingOut.value = false
  }
}
</script>

<template>
  <VBadge
    dot
    location="bottom right"
    offset-x="3"
    offset-y="3"
    color="success"
    bordered
  >
    <VAvatar
      class="cursor-pointer"
      color="primary"
      variant="tonal"
    >
      <VImg :src="avatar1" />

      <!-- SECTION Menu -->
      <VMenu
        activator="parent"
        width="230"
        location="bottom end"
        offset="14px"
      >
        <VList>
          <!-- 👉 User Avatar & Name -->
          <VListItem>
            <template #prepend>
              <VListItemAction start>
                <VBadge
                  dot
                  location="bottom right"
                  offset-x="3"
                  offset-y="3"
                  color="success"
                >
                  <VAvatar
                    color="primary"
                    variant="tonal"
                  >
                    <VImg :src="avatar1" />
                  </VAvatar>
                </VBadge>
              </VListItemAction>
            </template>

            <VListItemTitle class="font-weight-semibold">
              {{ authStore.user?.name || authStore.user?.username }}
            </VListItemTitle>
            <VListItemSubtitle>{{ authStore.user?.email }}</VListItemSubtitle>
          </VListItem>
          <VDivider class="my-2" />

          <!-- 👉 Profile -->
          <VListItem link>
            <template #prepend>
              <VIcon
                class="me-2"
                icon="ri-user-line"
                size="22"
              />
            </template>

            <VListItemTitle>Profile</VListItemTitle>
          </VListItem>

          <!-- 👉 Settings -->
          <VListItem link>
            <template #prepend>
              <VIcon
                class="me-2"
                icon="ri-settings-4-line"
                size="22"
              />
            </template>

            <VListItemTitle>Settings</VListItemTitle>
          </VListItem>

          <!-- 👉 Pricing -->
          <VListItem link>
            <template #prepend>
              <VIcon
                class="me-2"
                icon="ri-money-dollar-circle-line"
                size="22"
              />
            </template>

            <VListItemTitle>Pricing</VListItemTitle>
          </VListItem>

          <!-- 👉 FAQ -->
          <VListItem link>
            <template #prepend>
              <VIcon
                class="me-2"
                icon="ri-question-line"
                size="22"
              />
            </template>

            <VListItemTitle>FAQ</VListItemTitle>
          </VListItem>

          <!-- Divider -->
          <VDivider class="my-2" />

          <!-- 👉 Logout -->
          <VListItem
            :disabled="isLoggingOut"
            @click="logout"
          >
            <template #prepend>
              <VIcon
                class="me-2"
                icon="ri-logout-box-r-line"
                size="22"
              />
            </template>

            <VListItemTitle>{{ isLoggingOut ? 'Logging out...' : 'Logout' }}</VListItemTitle>
          </VListItem>
        </VList>
      </VMenu>
      <!-- !SECTION -->
    </VAvatar>
  </VBadge>
</template>
