<script setup>
import { useAuthStore } from '@/stores/auth'
import AuthProvider from '@/views/pages/authentication/AuthProvider.vue'
import logo from '@images/logoippat.png'
import authV1MaskDark from '@images/pages/auth-v1-mask-dark.png'
import authV1MaskLight from '@images/pages/auth-v1-mask-light.png'
import authV1Tree2 from '@images/pages/auth-v1-tree-2.png'
import authV1Tree from '@images/pages/auth-v1-tree.png'
import { useTheme } from 'vuetify'

const form = ref({
  email: '',
  password: '',
  remember: false,
})

const router = useRouter()
const authStore = useAuthStore()
const errorMessage = ref('')
const isLoading = ref(false)

const vuetifyTheme = useTheme()

const authThemeMask = computed(() => {
  return vuetifyTheme.global.name.value === 'light' ? authV1MaskLight : authV1MaskDark
})

const isPasswordVisible = ref(false)


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

const login = async () => {
  errorMessage.value = ''
  isLoading.value = true

  try {
    const response = await fetch('/auth/login', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        ...(await getFreshCsrfToken()) ? { 'X-CSRF-TOKEN': await getFreshCsrfToken() } : {},
      },
      credentials: 'same-origin',
      body: JSON.stringify(form.value),
    })
    const data = await response.json()

    if (!response.ok)
      throw new Error(data.errors?.email?.[0] ?? data.message ?? 'Unable to sign in.')

    authStore.setUser(data.user)

    await router.push('/dashboard')
  }
  catch (error) {
    errorMessage.value = error.message
  }
  finally {
    isLoading.value = false
  }
}
</script>

<template>
  <!-- eslint-disable vue/no-v-html -->

  <div class="auth-wrapper d-flex align-center justify-center pa-4">
    <VCard
      class="auth-card pa-4 pt-7"
      max-width="448"
    >
      <VCardItem class="justify-center">
        <RouterLink
          to="/"
          class="d-flex align-center gap-3"
        >
          <!-- eslint-disable vue/no-v-html -->
          <img
            :src="logo"
            alt="PPAT SMART EXAM logo"
            class="app-logo-image login-logo"
          >
          <!-- <h2 class="font-weight-medium text-2xl text-uppercase">
            PPAT SMART EXAM
          </h2> -->
        </RouterLink>
      </VCardItem>

      <VCardText class="pt-2">
        <h4 class="text-h4 mb-1">
          Welcome to PPAT SMART EXAM! 👋🏻
        </h4>
        <p class="mb-0">
          Please sign-in to your account and start the adventure
        </p>
      </VCardText>

      <VCardText>
        <VForm @submit.prevent="login">
          <VRow>
            <VCol
              v-if="errorMessage"
              cols="12"
            >
              <VAlert type="error" density="compact">
                {{ errorMessage }}
              </VAlert>
            </VCol>
            <!-- email -->
            <VCol cols="12">
              <VTextField
                v-model="form.email"
                label="Email"
                type="email"
              />
            </VCol>

            <!-- password -->
            <VCol cols="12">
              <VTextField
                v-model="form.password"
                label="Password"
                placeholder="············"
                :type="isPasswordVisible ? 'text' : 'password'"
                autocomplete="password"
                :append-inner-icon="isPasswordVisible ? 'ri-eye-off-line' : 'ri-eye-line'"
                @click:append-inner="isPasswordVisible = !isPasswordVisible"
              />

              <!-- remember me checkbox -->
              <div class="d-flex align-center justify-space-between flex-wrap my-6">
                <VCheckbox
                  v-model="form.remember"
                  label="Remember me"
                />

                <a
                  class="text-primary"
                  href="javascript:void(0)"
                >
                  Forgot Password?
                </a>
              </div>

              <!-- login button -->
              <VBtn
                block
                type="submit"
                :loading="isLoading"
              >
                Login
              </VBtn>
            </VCol>

            <!-- create account -->
            <VCol
              cols="12"
              class="text-center text-base"
            >
              <span>New on our platform?</span>
              <RouterLink
                class="text-primary ms-2"
                to="/register"
              >
                Create an account
              </RouterLink>
            </VCol>

            <VCol
              cols="12"
              class="d-flex align-center"
            >
              <VDivider />
              <span class="mx-4">or</span>
              <VDivider />
            </VCol>

            <!-- auth providers -->
            <VCol
              cols="12"
              class="text-center"
            >
              <AuthProvider />
            </VCol>
          </VRow>
        </VForm>
      </VCardText>
    </VCard>

    <VImg
      class="auth-footer-start-tree d-none d-md-block"
      :src="authV1Tree"
      :width="250"
    />

    <VImg
      :src="authV1Tree2"
      class="auth-footer-end-tree d-none d-md-block"
      :width="350"
    />

    <!-- bg img -->
    <VImg
      class="auth-footer-mask d-none d-md-block"
      :src="authThemeMask"
    />
  </div>
</template>

<style lang="scss">
@use "@core-scss/template/pages/page-auth";

.login-logo {
  block-size: 4rem;
  inline-size: 4rem;
  object-fit: contain;
}
</style>
