<template>
  <b-sidebar
    id="edit-visit-events-sidebar"
    :visible="isEditVisitEventsSidebarActive"
    bg-variant="white"
    sidebar-class="sidebar-lg"
    shadow
    backdrop
    no-header
    right
    @hidden="resetForm"
    @change="(val) => $emit('update:is-edit-visit-events-sidebar-active', val)"
  >
    <template #default="{ hide }">

      <!-- Header -->
      <div class="d-flex justify-content-between align-items-center content-sidebar-header px-2 py-1">
        <h5 class="mb-0">
          Изменить примечание события
        </h5>

        <feather-icon
          class="ml-1 cursor-pointer"
          icon="XIcon"
          size="16"
          @click="hide"
        />

      </div>

      <!-- BODY -->
      <validation-observer
        #default="{ handleSubmit }"
        ref="refFormObserver"
      >
        <!-- Form -->
        <b-form
          class="p-2"
          @submit.prevent="handleSubmit(onSubmit)"
          @reset.prevent="resetForm"
        >
          <!-- Name -->
          <b-form-group
            label="ФИО"
            label-for="fullName"
          >
            <b-form-input
              id="fullName"
              v-model="visitEvent.fullName"
              disabled
              trim
              placeholder="ФИО"
            />
          </b-form-group>

          <!-- Date -->
          <b-form-group
            label="Дата"
            label-for="date"
          >
            <b-form-input
              id="date"
              v-model="visitEvent.date"
              disabled
              trim
              placeholder="Дата"
            />
          </b-form-group>

          <hr>

          <!-- Date -->
          <b-form-group
            label="Примечание"
            label-for="note"
          >
            <b-form-textarea
              id="note"
              v-model="visitEvent.note"
              trim
              placeholder="Примечание"
            />
          </b-form-group>

          <!-- Form Actions -->
          <div class="d-flex mt-2">
            <b-button
              v-ripple.400="'rgba(255, 255, 255, 0.15)'"
              variant="primary"
              class="mr-2"
              type="submit"
            >
              Изменить
            </b-button>
            <b-button
              v-ripple.400="'rgba(186, 191, 199, 0.15)'"
              type="button"
              variant="outline-secondary"
              @click="hide"
            >
              Отмена
            </b-button>
          </div>
        </b-form>
      </validation-observer>
    </template>
  </b-sidebar>
</template>

<script>
import {
  BSidebar, BForm, BFormGroup, BFormInput, BButton, BFormTextarea,
} from 'bootstrap-vue'
import { ValidationObserver } from 'vee-validate'
import { ref } from '@vue/composition-api'
import { required, regex, max } from '@validations'
import formValidation from '@core/comp-functions/forms/form-validation'
import Ripple from 'vue-ripple-directive'
import axios from '@/libs/axios'
import ToastificationContent from '@core/components/toastification/ToastificationContent.vue'

export default {
  components: {
    BSidebar,
    BForm,
    BFormGroup,
    BFormInput,
    BButton,
    BFormTextarea,

    // Form Validation
    ValidationObserver,
  },
  directives: {
    Ripple,
  },
  model: {
    prop: 'isEditVisitEventsSidebarActive',
    event: 'update:is-edit-visit-events-sidebar-active',
  },
  props: {
    isEditVisitEventsSidebarActive: {
      type: Boolean,
      required: true,
    },
    editEvent: {
      type: Object,
      required: true,
    },
  },
  data() {
    return {
      required,
      max,
      regex,
    }
  },
  watch: {
    editEvent(event) {
      if (event.id === 0) return
      this.visitEvent = {
        id: event.id,
        fullName: event.employee,
        date: event.date,
        note: event.note,
      }
    },
  },
  methods: {
    onSubmit() {
      this.$refs.refFormObserver.validate()
        .then(success => {
          if (!success) return
          axios.post(`/management/visit-events/${this.visitEvent.id}`, {
            note: this.visitEvent.note,
          })
            .then(() => {
              this.$emit('refetch-data')
            })
            .catch(() => {
              this.$toast({
                component: ToastificationContent,
                position: 'top-right',
                props: {
                  title: 'Не удалось обновить примечание',
                  variant: 'danger',
                  icon: 'AlertCircleIcon',
                },
              })
            })
        })
    },
  },
  setup() {
    const blankUserData = {
      id: '',
      startOfTheDay: null,
      endOfTheDay: null,
      visitControl: false,
      date: '',
      note: '',
    }
    const visitEvent = ref(JSON.parse(JSON.stringify(blankUserData)))
    const resetuserData = () => {
      visitEvent.value = JSON.parse(JSON.stringify(blankUserData))
    }
    const {
      refFormObserver,
      getValidationState,
      resetForm,
    } = formValidation(resetuserData)

    return {
      visitEvent,
      refFormObserver,
      getValidationState,
      resetForm,
    }
  },
}
</script>

<style lang="scss">

</style>
