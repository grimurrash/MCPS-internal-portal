<template>
  <b-sidebar
    id="add-new-template-sidebar"
    :visible="isAddNewTemplateSidebarActive"
    bg-variant="white"
    sidebar-class="sidebar-lg"
    shadow
    backdrop
    no-header
    right
    @hidden="resetForm"
    @change="(val) => $emit('update:is-add-new-template-sidebar-active', val)"
  >
    <template #default="{ hide }">
      <!-- Header -->
      <div class="d-flex justify-content-between align-items-center content-sidebar-header px-2 py-1">
        <h5 class="mb-0">
          Добавить новый шаблон документа
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
          <validation-provider
            #default="validationContext"
            name="Наименование шаблона"
            rules="required"
          >
            <b-form-group
              label="Наименование шаблона"
              label-for="name"
            >
              <b-form-input
                id="name"
                v-model="template.name"
                autofocus
                :state="getValidationState(validationContext)"
                trim
                placeholder="Наименование шаблона"
              />

              <b-form-invalid-feedback>
                {{ validationContext.errors[0] }}
              </b-form-invalid-feedback>
            </b-form-group>
          </validation-provider>

          <!-- Description -->
          <validation-provider
            #default="validationContext"
            name="Кратное описание"
            rules="required"
          >
            <b-form-group
              label="Кратное описание"
              label-for="description"
            >
              <b-form-textarea
                id="description"
                v-model="template.description"
                placeholder="Краткое описание"
                rows="3"
                trim
                :state="getValidationState(validationContext)"
              />
              <b-form-invalid-feedback>
                {{ validationContext.errors[0] }}
              </b-form-invalid-feedback>
            </b-form-group>
          </validation-provider>

          <!-- File -->
          <validation-provider
            #default="validationContext"
            name="Шаблон"
            rules="required"
          >
            <b-form-group
              label="Шаблон"
              label-for="file"
            >
              <b-form-file
                id="file"
                ref="fileInput"
                v-model="template.file"
                :state="getValidationState(validationContext)"
                accept=".docx, .doc."
                placeholder="Выберите файл или перетащите его сюда..."
                drop-placeholder="Перетащите файл сюда..."
                @change="handleFileUpload()"
              />
              <b-form-invalid-feedback :state="getValidationState(validationContext)">
                {{ validationContext.errors[0] }}
              </b-form-invalid-feedback>
            </b-form-group>
          </validation-provider>

          <!-- DocumentType_id -->
          <validation-provider
            #default="validationContext"
            name="Тип документа"
            rules="required"
          >
            <b-form-group
              label="Тип документа"
              label-for="documentType_id"
              :state="getValidationState(validationContext)"
            >
              <v-select
                v-model="template.documentType_id"
                :dir="$store.state.appConfig.isRTL ? 'rtl' : 'ltr'"
                :options="documentTypeOptions"
                :reduce="val => val.value"
                :clearable="false"
                input-id="documentType_id"
              />
              <b-form-invalid-feedback :state="getValidationState(validationContext)">
                {{ validationContext.errors[0] }}
              </b-form-invalid-feedback>
            </b-form-group>
          </validation-provider>

          <!-- IsDouble -->
          <validation-provider
            #default="validationContext"
            name="Кол-во копий в документе"
            rules="required"
          >
            <b-form-group
              label="Кол-во копий в документе"
              label-for="isDouble"
              :state="getValidationState(validationContext)"
            >
              <v-select
                v-model="template.isDouble"
                :dir="$store.state.appConfig.isRTL ? 'rtl' : 'ltr'"
                :options="isDoubleOptions"
                :reduce="val => val.value"
                :clearable="false"
                input-id="isDouble"
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
              Добавить
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
  BSidebar, BForm, BFormGroup, BFormInput, BFormInvalidFeedback, BButton, BFormFile, BFormTextarea,
} from 'bootstrap-vue'
import { ValidationProvider, ValidationObserver } from 'vee-validate'
import { ref } from '@vue/composition-api'
import { required, alphaNum } from '@validations'
import formValidation from '@core/comp-functions/forms/form-validation'
import Ripple from 'vue-ripple-directive'
import vSelect from 'vue-select'
import axios from '@/libs/axios'
import ToastificationContent from '@core/components/toastification/ToastificationContent.vue'

export default {
  components: {
    BSidebar,
    BForm,
    BFormGroup,
    BFormInput,
    BFormInvalidFeedback,
    BButton,
    BFormFile,
    BFormTextarea,
    vSelect,

    // Form Validation
    ValidationProvider,
    ValidationObserver,
  },
  directives: {
    Ripple,
  },
  model: {
    prop: 'isAddNewTemplateSidebarActive',
    event: 'update:is-add-new-template-sidebar-active',
  },
  props: {
    isAddNewTemplateSidebarActive: {
      type: Boolean,
      required: true,
    },
    documentTypeOptions: {
      type: Array,
      required: true,
    },
    isDoubleOptions: {
      type: Array,
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
      this.template.file = this.$refs.fileInput.files[0]
    },
    onSubmit() {
      this.$refs.refFormObserver.validate()
        .then(success => {
          if (!success) return
          const formData = new FormData()
          formData.append('name', this.template.name)
          formData.append('description', this.template.description)
          formData.append('file', this.template.file)
          formData.append('documentType_id', this.template.documentType_id)
          formData.append('isDouble', this.template.isDouble)

          axios.post('/documents/templates', formData, {
            headers: {
              'Content-Type': 'multipart/form-data',
            },
          })
            .then(() => {
              this.$emit('refetch-data')
            })
            .catch(() => {
              this.$toast({
                component: ToastificationContent,
                position: 'top-right',
                props: {
                  title: 'Не удалось Добавить шаблон',
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
      name: '',
      description: '',
      file: null,
      documentType_id: null,
      isDouble: 0,
    }
    const template = ref(JSON.parse(JSON.stringify(blankUserData)))
    const resetuserData = () => {
      template.value = JSON.parse(JSON.stringify(blankUserData))
    }
    const file = ref(null)
    const {
      refFormObserver,
      getValidationState,
      resetForm,
    } = formValidation(resetuserData)

    return {
      template,
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
