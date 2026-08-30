<script setup>

/* eslint-disable camelcase */

const emptyQuestion = () => ({
  category: '',
  question: '',
  difficulty: '',
  type: '',
  options: 
    {
      option_a: '',
      option_b: '',
      option_c: '',
      option_d: '',
    }
  ,
  correct_answer: 'A',
  score: 1,
})

const headers = [
  { title: 'No', key: 'id' },
  { title: 'Category', key: 'category_id' },
  { title: 'Question', key: 'text' },
  { title: 'Correct answer', key: 'correct_answer', width: '160px' },
  { title: 'Score', key: 'score_value', width: '100px' },
  { title: 'Actions', key: 'actions', sortable: false, align: 'end', width: '120px' },
]

const questions = ref([])
const question = ref(emptyQuestion())
const editingId = ref(null)
const dialog = ref(false)
const isLoading = ref(false)
const isSaving = ref(false)
const errorMessage = ref('')

const categories = ref([])


const isEditing = computed(() => editingId.value !== null)
const dialogTitle = computed(() => isEditing.value ? 'Edit question' : 'Add question')

const csrfToken = () => document.querySelector('meta[name="csrf-token"]')?.content ?? ''

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

const loadQuestions = async () => {
  isLoading.value = true
  errorMessage.value = ''

  try {
    questions.value = await request('/api/questions')
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

const openCreateDialog = () => {
  editingId.value = null
  question.value = emptyQuestion()
  errorMessage.value = ''
  dialog.value = true
}

const openEditDialog = item => {

  editingId.value = item.id
  // console.log(item.id)
  question.value = {
    question: item.text,
    difficulty: item.difficulty,
    type: item.type,
    options: {
      option_a: item.option_a,
      option_b: item.option_b,
      option_c: item.option_c,
      option_d: item.option_d,
    },
    correct_answer: item.correct_answer,
    category: item.category_id,
    score: item.score_value,
  }
  errorMessage.value = ''
  dialog.value = true
}

const saveQuestion = async () => {
  isSaving.value = true
  errorMessage.value = ''

  try {
    const url = isEditing.value ? `/api/questions/${editingId.value}` : '/api/questions'
    const method = isEditing.value ? 'PUT' : 'POST'

    const savedQuestion = await request(url, {
      method,
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': csrfToken(),
      },
      body: JSON.stringify(question.value),
    })

    if (isEditing.value) {
      const index = questions.value.findIndex(item => item.id === savedQuestion.id)

      questions.value.splice(index, 1, savedQuestion)
    }
    else {
      questions.value.unshift(savedQuestion)
    }

    dialog.value = false
  }
  catch (error) {
    errorMessage.value = error.message
  }
  finally {
    isSaving.value = false
  }
}

const deleteQuestion = async item => {
  if (!window.confirm('Delete this question?'))
    return

  errorMessage.value = ''

  try {
    await request(`/api/questions/${item.id}`, {
      method: 'DELETE',
      headers: {
        'X-CSRF-TOKEN': csrfToken(),
      },
    })
    questions.value = questions.value.filter(question => question.id !== item.id)
  }
  catch (error) {
    errorMessage.value = error.message
  }
}

const getCategoryName = categoryId => {
  const category = categories.value.find(item => Number(item.id) === Number(categoryId))
  return category?.name ?? `#${categoryId ?? 'unknown'}`
}

onMounted(() => Promise.all([loadQuestions(), loadCategories()]))
</script>

<template>
  <VRow>
    <VCol cols="12">
      <VCard title="Bank Soal">
        <template #append>
          <VBtn
            prepend-icon="ri-add-line"
            @click="openCreateDialog"
          >
            Add question
          </VBtn>
        </template>

        <VCardText v-if="errorMessage">
          <VAlert type="error">
            {{ errorMessage }}
          </VAlert>
        </VCardText>

        <VDataTable
          :headers="headers"
          :items="questions"
          :loading="isLoading"
          item-value="id"
        >
          
        <template #item.category_id="{ item }">
          {{ getCategoryName(item.category_id) }}
        </template>

          <template #item.actions="{ item }">
            <VBtn
              icon="ri-pencil-line"
              size="small"
              variant="text"
              @click="openEditDialog(item)"
            />
            <VBtn
              color="error"
              icon="ri-delete-bin-line"
              size="small"
              variant="text"
              @click="deleteQuestion(item)"
            />
          </template>
        </VDataTable>
      </VCard>

      <VDialog
        v-model="dialog"
        max-width="720"
      >
        <VCard :title="dialogTitle">
          <VCardText>
            <VForm @submit.prevent="saveQuestion">
              <VSelect
                v-model="question.category"
                class="mt-4"
                label="Category"
                :items="categories"
                item-title="name"
                item-value="id"
                :rules="[value => !!value || 'Category is required.']"
              />
              <VTextarea
                v-model="question.question"
                class="mt-4"
                label="Question"
                :rules="[value => !!value || 'Question is required.']"
              />
              <VSelect
                v-model="question.difficulty"
                class="mt-4"
                label="Difficulty"
                :items="['Easy', 'Medium', 'Hard']"
                :rules="[value => !!value || 'Difficulty is required.']"
              />
              <v-select
                v-model="question.type"
                class="mt-4"
                label="Type"
                :items="['mcq', 'true_false', 'Essay']"
                :rules="[value => !!value || 'Type is required.']"
              />
              <VTextField
                v-model="question.options.option_a"
                class="mt-4"
                label="Option A"
                :rules="[value => !!value || 'Option A is required.']"
              />
              <VTextField
                v-model="question.options.option_b"
                class="mt-4"
                label="Option B"
                :rules="[value => !!value || 'Option B is required.']"
              />
              <VTextField
                v-model="question.options.option_c"
                class="mt-4"
                label="Option C"
                :rules="[value => !!value || 'Option C is required.']"
              />
              <VTextField
                v-model="question.options.option_d"
                class="mt-4"
                label="Option D"
                :rules="[value => !!value || 'Option D is required.']"
              />
              <VTextField
                v-model="question.score"
                class="mt-4"
                label="Score"
                min="1"
                type="number"
                :rules="[value => !!value || 'Score is required.']"
              />
              <VSelect
                v-model="question.correct_answer"
                class="mt-4"
                :items="['A', 'B', 'C', 'D']"
                label="Correct answer"
              />
              <div class="d-flex justify-end gap-3 mt-6">
                <VBtn
                  variant="tonal"
                  @click="dialog = false"
                >
                  Cancel
                </VBtn>
                <VBtn
                  :loading="isSaving"
                  type="submit"
                >
                  Save
                </VBtn>
              </div>
            </VForm>
          </VCardText>
        </VCard>
      </VDialog>
    </VCol>
  </VRow>
</template>
