<script setup>
import { useAuthStore } from '@/stores/auth'
import { computed, onMounted, ref } from 'vue'

const authStore = useAuthStore()
const router = useRouter()

const headers = [
  { title: 'No', key: 'no' },
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
const status = ref([])

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

const cekExamStatus = async () => {
  // console.log(exams.value)
  const results = []
  for (let i = 0; i < exams.value.length; i++) {
    try {
      const token = await getFreshCsrfToken()
      const ql = await request('/api/cek-ujian', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
          'X-CSRF-TOKEN': token,
        },
        body: JSON.stringify({ exam_id: exams.value[i].id, siswa_id: authStore.user?.id ?? null }),
      })
      results.push(ql.result ?? [])
    }
    catch (error) {
      errorMessage.value = error.message
    }
    finally {
      isLoading.value = false
    }

  }
  
  status.value = results
  console.log(results)
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
    cekExamStatus()
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
  }, 1000),
  
]))
</script>

<template>
  <VCard title="Next Exam" class="card-analytics">
    <template #subtitle>
      <p class="text-body-1 mb-0">
        <span class="d-inline-block font-weight-medium text-high-emphasis">Ikuti Sesi ujian berikutnya</span> <span class="text-high-emphasis">😎</span> this month
      </p>
    </template>


    
    <VDataTable
          :headers="headers"
          :items="exams"
          :loading="isLoading"
          item-value="id"
        >
        <template #item.no="{ index }">
          {{ index + 1 }}
        </template>
        <template #item.countdown="{ item }">
          <p v-if="!item.canJoin">{{ item.countdown }} </p>
          <p v-else class="text-success font-weight-medium">{{ item.countdown }} </p>
        </template>
        <template #item.actions="{ item }">

          
          <VBtn
            :disabled="!item.canJoin || item.id === status[0]?.exam_id"
            color="primary"
            @click="goToExamSheet(item)"
          >
            <div v-if="item.id === status[0]?.exam_id" class="font-weight-medium">Sudah ikut</div>
            <div v-else class="font-weight-medium">Ikuti Ujian</div>
          </VBtn>

        </template>
        </VDataTable>
  </VCard>

</template>
