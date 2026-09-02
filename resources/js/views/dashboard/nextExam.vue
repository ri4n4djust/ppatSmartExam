<script setup>
const router = useRouter()

const headers = [
  { title: 'No', key: 'id' },
  { title: 'Title', key: 'title' },
  { title: 'Start Time', key: 'start_time' },
  { title: 'Countdown', key: 'countdown' },
  { title: 'Actions', key: 'actions', sortable: false, align: 'end', width: '120px' },
]

const exams = ref([])
const categories = ref([])
const isLoading = ref(false)
const errorMessage = ref('')
const now = ref(new Date())
const remainingTime = ref('')

const request = async (url, options = {}) => {
  const response = await fetch(url, {
    credentials: 'same-origin',
    ...options,
    headers: {
      Accept: 'application/json',
      ...options.headers,
    },
  })

  if (response.status === 204)
    return null

  const data = await response.json()

  if (!response.ok)
    throw new Error(Object.values(data.errors ?? {})[0]?.[0] ?? data.message ?? 'The request could not be completed.')

  return data
}

// cek apakah ujian sudah bisa dimulai
const canJoin = computed(() => {
  const start = new Date(exams.value.start_time.replace(' ', 'T'))
  const end = new Date(exams.value.end_time.replace(' ', 'T'))
  return now.value >= start && now.value <= end
})

function getCountdown(startTime, endTime) {
  const start = new Date(startTime.replace(' ', 'T'))
  const end = new Date(endTime.replace(' ', 'T'))
  const now = new Date()

  const diffStart = start - now
  const diffEnd = end - now

  if (diffStart > 0) {
    // belum mulai
    const hours = String(Math.floor(diffStart / (1000 * 60 * 60))).padStart(2, '0')
    const minutes = String(Math.floor((diffStart % (1000 * 60 * 60)) / (1000 * 60))).padStart(2, '0')
    const seconds = String(Math.floor((diffStart % (1000 * 60)) / 1000)).padStart(2, '0')
    return { text: `Ujian akan dimulai dalam ${hours}:${minutes}:${seconds}`, canJoin: false }
  } else if (diffEnd > 0) {
    // sedang berlangsung
    const hours = String(Math.floor(diffEnd / (1000 * 60 * 60))).padStart(2, '0')
    const minutes = String(Math.floor((diffEnd % (1000 * 60 * 60)) / (1000 * 60))).padStart(2, '0')
    const seconds = String(Math.floor((diffEnd % (1000 * 60)) / 1000)).padStart(2, '0')
    return { text: `Ujian berakhir dalam ${hours}:${minutes}:${seconds}`, canJoin: true }
  } else {
    // sudah selesai
    return { text: 'Ujian sudah berakhir!', canJoin: false }
  }
}

const loadExams = async () => {
  isLoading.value = true
  errorMessage.value = ''

  try {
    exams.value = await request('/api/exams')
  }
  catch (error) {
    errorMessage.value = error.message
  }
  finally {
    isLoading.value = false
  }
}

const loadCategories = async () => {
  isLoading.value = true
  errorMessage.value = ''

  try {
    categories.value = await request('/api/categories')
  }
  catch (error) {
    errorMessage.value = error.message
  }
  finally {
    isLoading.value = false
  }
}

const statistics = [
  {
    title: 'Sales',
    stats: '245k',
    icon: 'ri-pie-chart-2-line',
    color: 'primary',
  },
  {
    title: 'Customers',
    stats: '12.5k',
    icon: 'ri-group-line',
    color: 'success',
  },
  {
    title: 'Product',
    stats: '1.54k',
    icon: 'ri-macbook-line',
    color: 'warning',
  },
  {
    title: 'Revenue',
    stats: '$88k',
    icon: 'ri-money-dollar-circle-line',
    color: 'info',
  },
]

const moreList = [
  {
    title: 'Share',
    value: 'Share',
  },
  {
    title: 'Refresh',
    value: 'Refresh',
  },
  {
    title: 'Update',
    value: 'Update',
  },
]

const goToExamSheet = exam => {
  router.push({
    path: '/lembar-ujian',
    query: {
      exam: encodeURIComponent(JSON.stringify(exam)),
    },
  })
}

onMounted(() => Promise.all([
  loadExams(), 
  loadCategories(),
  setInterval(() => {
    exams.value = exams.value.map(exam => {
      const countdown = getCountdown(exam.start_time, exam.end_time)
      return { ...exam, countdown: countdown.text, canJoin: countdown.canJoin }
    })
  }, 1000)
]))
</script>

<template>
  <VCard title="Next Exam" class="card-analytics">
    <template #subtitle>
      <p class="text-body-1 mb-0">
        <span class="d-inline-block font-weight-medium text-high-emphasis">Ikuti Sesi ujian berikutnya</span> <span class="text-high-emphasis">😎</span> this month
      </p>
    </template>

    <template #append>
      <MoreBtn :menu-list="moreList" />
    </template>

    <VCardText class="pt-10">
      <VRow>
        <VCol
          v-for="item in statistics"
          :key="item.title"
          cols="12"
          sm="6"
          md="3"
        >
          <div class="d-flex align-center gap-x-3">
            <VAvatar
              :color="item.color"
              rounded
              size="40"
              class="elevation-2"
            >
              <VIcon
                size="24"
                :icon="item.icon"
              />
            </VAvatar>

            <div class="d-flex flex-column">
              <div class="text-body-1">
                {{ item.title }}
              </div>
              <h5 class="text-h5">
                {{ item.stats }}
              </h5>
            </div>
          </div>
        </VCol>
      </VRow>
    </VCardText>
    <VDataTable
          :headers="headers"
          :items="exams"
          :loading="isLoading"
          item-value="id"
        >
        <template #item.countdown="{ item }">
            <p v-if="!item.canJoin">{{ item.countdown }}</p>
          </template>
          <template #item.actions="{ item }">
            <VBtn
              :disabled="!item.canJoin"
              color="primary"
              @click="goToExamSheet(item)"
            >
              Join Exam
            </VBtn>
          </template>
        </VDataTable>
  </VCard>

</template>
