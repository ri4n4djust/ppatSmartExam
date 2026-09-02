<script setup>
import { useAuthStore } from '@/stores/auth'
import { useExamStore } from '@/stores/examStore'
import axios from 'axios'
const route = useRoute()
const examStore = useExamStore()
const authStore = useAuthStore()

const selectedExam = computed(() => {
  const rawExam = route.query.exam

  if (!rawExam)
    return null

  try {
    return JSON.parse(decodeURIComponent(String(rawExam)))
  }
  catch {
    return null
  }
})

const exams = ref([])
const questions = ref([]);
const timer = null
const sudahMulai = ref(false)

async function assignQuestions(examId, questionIds) {
  try {
    const payload = {
      exam_id: examId,
      question_id: questionIds
    }
    const response = await axios.post('/api/questions/assign', payload, {
      headers: {
        'Accept': 'application/json',
        'Content-Type': 'application/json'
      },
      withCredentials: true // kalau pakai auth berbasis cookie/session
    })

    // console.log('Response:', response.data)
    return questions.value =  response.data
  } catch (error) {
    console.error('Error:', error)
    return []
  }
}


const mulaiUjian = async () => {
  if (!selectedExam.value)
    return

  // Redirect ke halaman ujian dengan membawa data ujian
  exams.value = await examStore.fetchQuestions(selectedExam.value.id)
  sudahMulai.value = true
  timer = setInterval(() => {
    examStore.tick()
  }, 1000)
}

const submitUjian = async () => {
  try {
    const payload = {
      siswa_id: authStore.user?.id ?? authStore.idUser,
      exam_id: selectedExam.value.id,
      answers: examStore.questions.map(q => ({
        question_id: q.id,
        answer: q.answer,
        is_correct: q.is_correct
      }))
    }
    const response = await axios.post('/api/exams/submit', payload, {
      headers: {
        'Accept': 'application/json',
        'Content-Type': 'application/json'
      },
      withCredentials: true // kalau pakai auth berbasis cookie/session
    })

    console.log('Response:', response.data)
    alert('Ujian berhasil disubmit!')
    // Redirect ke halaman hasil ujian atau halaman lain sesuai kebutuhan
    window.location.href = '/dashboard' // Contoh redirect ke dashboard
  } catch (error) {
    console.error('Error:', error)
    alert('Terjadi kesalahan saat submit ujian.')
  }
}

onMounted(async () => {
  // console.log(selectedExam.value)
  // if (selectedExam.value) {
  //   exams.value = await assignQuestions(selectedExam.value.id)
    
  // }
})
onUnmounted(() => {
  clearInterval(timer)
})
</script>

<template>
  <VRow>
    <VCol cols="12">
      <VCard>
        <VCardTitle class="text-h4">
          {{ selectedExam?.title || 'Lembar Ujian' }}
        </VCardTitle>

        <VCardText>
          <div v-if="selectedExam">
            <p class="mb-2">
              <strong>Waktu mulai:</strong>
              {{ selectedExam.start_time }}
            </p>
            <p class="mb-2">
              <strong>Waktu selesai:</strong>
              {{ selectedExam.end_time }}
            </p>
            <p class="mb-2">
              <strong>Durasi:</strong>
              {{ selectedExam.duration }} menit
            </p>
            <p class="mb-0">
              <strong>Deskripsi:</strong>
              {{ selectedExam.description || 'Tidak ada deskripsi' }}
            </p>
          </div>

          <VAlert
            v-else
            type="warning"
            variant="tonal"
          >
            Data ujian tidak ditemukan.
          </VAlert>
        </VCardText>
      </VCard>
    </VCol>

    <VCol cols="12">
      <VCard title="Texts">
        
        <VCardText>
          <VBtn
            v-if="!sudahMulai"
            color="primary"
            @click="mulaiUjian()"
          >
            Mulai Ujian
          </VBtn>
          <div>
            <div v-if="examStore.questions.length">
              <h2>Soal {{ examStore.currentQuestionIndex + 1 }}</h2>
              <p class="question-text">
                {{ examStore.questions[examStore.currentQuestionIndex].text }}
              </p>

              <div class="options">
                <button
                  v-for="opt in examStore.questions[examStore.currentQuestionIndex].options"
                  :key="opt.key"
                  class="option-btn"
                  :class="{ selected: examStore.questions[examStore.currentQuestionIndex].answer === opt.value }"
                  @click="examStore.setAnswer(opt.value)"
                >
                  <strong>{{ opt.value }}.</strong> 
                </button>
              </div>

              <p class="timer">⏳ Waktu tersisa: {{ examStore.timeLeft }} detik</p>
              <div class="button-group">
                <button class="prev-btn" @click="examStore.prevQuestion()" :disabled="examStore.currentQuestionIndex === 0">
                  Previous
                </button>
                <button class="next-btn" @click="examStore.nextQuestion()" :disabled="examStore.currentQuestionIndex === examStore.questions.length - 1">
                  Next
                </button>
                <button class="next-btn" @click="submitUjian()" :disabled="examStore.currentQuestionIndex !== examStore.questions.length - 1">
                  Submit
                </button>
              </div>
            </div>
          </div>
          
        </VCardText>
      </VCard>
    </VCol>
    

  </VRow>
  
</template>
<style scoped>
.question-text {
  font-size: 1.2rem;
  margin-bottom: 1rem;
}
.options {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}
.option-btn {
  padding: 0.6rem 1rem;
  border: 1px solid #ccc;
  border-radius: 6px;
  text-align: left;
  background: #f9f9f9;
  cursor: pointer;
}
.option-btn:hover {
  background: #e0f7fa;
}
.option-btn.selected {
  background: #4caf50;
  color: white;
  border-color: #4caf50;
}
.timer {
  margin-top: 1rem;
  font-weight: bold;
}
.next-btn {
  margin-top: 1rem;
  padding: 0.6rem 1rem;
  background: #2196f3;
  color: white;
  border: none;
  border-radius: 6px;
  cursor: pointer;
}
.next-btn:hover {
  background: #1976d2;
}
.prev-btn {
  margin-top: 1rem;
  padding: 0.6rem 1rem;
  background: #9e9e9e;
  color: white;
  border: none;
  border-radius: 6px;
  cursor: pointer;
}
.prev-btn:hover {
  background: #616161;
}
.button-group {
  display: flex;
  gap: 1rem;
}
</style>
