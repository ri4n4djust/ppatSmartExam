import axios from 'axios'
import { defineStore } from 'pinia'

export const useExamStore = defineStore('exam', {
  state: () => ({
    
    idExam: '',
    currentQuestionIndex: 0,
    questions: [],
    duration: 0, // detik per soal
    timeLeft: 0,
    loading: false,
    error: null
  }),
  getters: {
    currentQuestion: (state) => state.questions[state.currentQuestionIndex]
  },
  actions: {
    async fetchQuestions(examId) {
      this.loading = true
      this.error = null
      try {
        const { data } = await axios.post(`/api/questions/assign`, {
          exam_id: examId
        })
        // console.log(data.question_ids)
        this.questions = data.question_ids.map(q => {
            // const opts = q.options
            const opts = typeof q.options === 'string' ? JSON.parse(q.options) : q.options
            // console.log(opts)
            return {
                ...q,
                options: [
                { key: 'A', value: opts.option_a },
                { key: 'B', value: opts.option_b },
                { key: 'C', value: opts.option_c },
                { key: 'D', value: opts.option_d }
                ]
            }
            
        })
        // console.log(data.question_ids.map(q => q.options))
        this.duration = data.exam_details.duration * 60
        this.timeLeft = data.exam_details.duration * 60
        this.idExam = data.exam_details.id
      } catch (err) {
        this.error = err.message
      } finally {
        this.loading = false
      }
    },
    nextQuestion() {
      if (this.currentQuestionIndex < this.questions.length - 1) {
        this.currentQuestionIndex++
      }
    },
    prevQuestion() {
      if (this.currentQuestionIndex > 0) {
        this.currentQuestionIndex--
      }
    },
    setAnswer(option) {
      this.questions[this.currentQuestionIndex].answer = option
      // console.log(`Jawaban untuk soal ${this.currentQuestionIndex + 1}: ${option}`)
      this.nextQuestion()
    },
    tick() {
      if (this.timeLeft > 0) {
        this.timeLeft--
      } else {
        // waktu habis → ujian selesai
        alert('Waktu ujian habis!')
      }
    }
  }
})
