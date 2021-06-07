<template>
  <b-sidebar
    id="import-employees-sidebar"
    :visible="isImportEmployeesSidebarActive"
    bg-variant="white"
    sidebar-class="sidebar-lg"
    shadow
    backdrop
    no-header
    right
    @hidden="resetForm"
    @change="(val) => $emit('update:is-import-employees-sidebar-active', val)"
  >
    <template #default="{ hide }">
      <!-- Header -->
      <div class="d-flex justify-content-between align-items-center content-sidebar-header px-2 py-1">
        <h5 class="mb-0">
          Импорт сотрудников
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
          <!-- File -->
          <validation-provider
            #default="validationContext"
            name="Таблица"
            rules="required"
          >
            <b-form-group
              label="Таблица с данными сотрудников"
              label-for="file"
            >
              <b-form-file
                id="file"
                ref="fileInput"
                v-model="file"
                :state="getValidationState(validationContext)"
                accept=".xls, .xlsx."
                placeholder="Выберите файл или перетащите его сюда..."
                drop-placeholder="Перетащите файл сюда..."
                @change="handleFileUpload()"
              />
              <b-form-invalid-feedback :state="getValidationState(validationContext)">
                {{ validationContext.errors[0] }}
              </b-form-invalid-feedback>
            </b-form-group>
          </validation-provider>

          <!-- Form Actions -->
          <div class="d-flex mt-2">
            <b-button
              v-ripple.400="'rgba(255, 255, 255, 0.15)'"
              variant="primary"
              class="mr-2"
              type="submit"
            >
              Импортировать
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
  BSidebar, BForm, BFormGroup, BFormInvalidFeedback, BButton, BFormFile,
} from 'bootstrap-vue'
import { ValidationProvider, ValidationObserver } from 'vee-validate'
import { ref } from '@vue/composition-api'
import { required, alphaNum } from '@validations'
import formValidation from '@core/comp-functions/forms/form-validation'
import Ripple from 'vue-ripple-directive'
import axios from '@/libs/axios'
import ToastificationContent from '@core/components/toastification/ToastificationContent.vue'

export default {
  components: {
    BSidebar,
    BForm,
    BFormGroup,
    BFormInvalidFeedback,
    BButton,
    BFormFile,

    // Form Validation
    ValidationProvider,
    ValidationObserver,
  },
  directives: {
    Ripple,
  },
  model: {
    prop: 'isImportEmployeesSidebarActive',
    event: 'update:is-import-employees-sidebar-active',
  },
  props: {
    isImportEmployeesSidebarActive: {
      type: Boolean,
      required: true,
    },
  },
  data() {
    return {
      required,
      alphaNum,
    }
  },
  methods: {
    handleFileUpload() {
      this.file = this.$refs.fileInput.files[0]
    },
    onSubmit() {
      this.$refs.refFormObserver.validate()
        .then(success => {
          if (!success) return
          const formData = new FormData()
          formData.append('file', this.file)

          axios.post('/management/employees/import', formData, {
            headers: {
              'Content-Type': 'multipart/form-data',
            },
          })
            .then(response => {
              this.$toast({
                component: ToastificationContent,
                position: 'top-right',
                props: {
                  title: `Сотрудники успешно импортирваны! Добавлено: ${response.data.createCount}, изменено: ${response.data.updateCount}, удалено ${response.data.deleteCount} сотрудников.`,
                  variant: 'success',
                  icon: 'CheckIcon',
                },
              })
              this.$emit('refetch-data')
            })
            .catch(() => {
              this.$toast({
                component: ToastificationContent,
                position: 'top-right',
                props: {
                  title: 'Не удалось импортировать отделы',
                  variant: 'danger',
                  icon: 'AlertCircleIcon',
                },
              })
            })
        })
    },
  },
  setup() {
    const file = ref(null)
    const resetuserData = () => {
      file.value = null
    }

    const {
      refFormObserver,
      getValidationState,
      resetForm,
    } = formValidation(resetuserData)

    return {
      file,
      refFormObserver,
      getValidationState,
      resetForm,
    }
  },
}
</script>

<style lang="scss">
@import '~@core/scss/vue/libs/vue-select.scss';

</style>
