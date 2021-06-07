<template>
  <b-row>
    <b-col cols="12">
      <div>
        <form-wizard
          color="#7367F0"
          :title="null"
          :subtitle="null"
          shape="square"
          back-button-text="Назад"
          next-button-text="Далее"
          finish-button-text="Получить документ"
          class="mb-3"
          @on-complete="formSubmitted"
        >
          <!-- template tab -->
          <tab-content
            title="Выбор шаблона"
            :before-change="validationTemplateForm"
          >
            <validation-observer
              ref="templateRules"
              tag="form"
            >
              <b-row>
                <b-col
                  cols="12"
                  class="mb-2"
                >
                  <h5 class="mb-0">
                    Шаблон
                  </h5>
                  <small class="text-muted">
                    Выберите шаблон
                  </small>
                </b-col>
                <b-col md="12">
                  <validation-provider
                    #default="{ errors }"
                    name="Шаблон"
                    rules="required"
                  >
                    <b-form-group
                      label="Шаблон"
                      label-for="template"
                      :state="errors.length > 0 ? false:null"
                    >
                      <v-select
                        id="template"
                        v-model="selectedTemplate"
                        :dir="$store.state.appConfig.isRTL ? 'rtl' : 'ltr'"
                        :options="templatesOptions"
                      />
                      <b-form-invalid-feedback :state="errors.length > 0 ? false:null">
                        {{ errors[0] }}
                      </b-form-invalid-feedback>
                    </b-form-group>
                  </validation-provider>
                </b-col>
              </b-row>
            </validation-observer>
          </tab-content>

          <!-- load data tab -->
          <tab-content
            title="Загрузка данных"
            :before-change="validationLoadDataForm"
          >
            <validation-observer
              ref="loadDataRules"
              tag="form"
            >
              <b-row>
                <b-col
                  cols="12"
                  class="mb-2"
                >
                  <h5 class="mb-0">
                    Данные
                  </h5>
                  <small class="text-muted">Загрузите файл с данными для заполнения шаблона</small>
                </b-col>
                <b-col md="6">
                  <b-form-group
                    label="DataFile"
                    label-for="data-file"
                  >
                    <validation-provider
                      #default="{ errors }"
                      name="Excel Файл с данными"
                      rules="required"
                    >
                      <b-form-file
                        id="data-file"
                        ref="fileInput"
                        v-model="dataFile"
                        :state="errors.length > 0 ? false:null"
                        accept=".xls, .xlsx"
                        placeholder="Выберите файл или перетащите его сюда..."
                        drop-placeholder="Перетащите файл сюда..."
                        @change="handleFileUpload()"
                      />
                      <small class="text-danger">{{ errors[0] }}</small>
                    </validation-provider>
                  </b-form-group>
                </b-col>
              </b-row>
            </validation-observer>
          </tab-content>
        </form-wizard>
      </div>
    </b-col>
  </b-row>
</template>

<script>
import { FormWizard, TabContent } from 'vue-form-wizard'
import vSelect from 'vue-select'
import { ValidationProvider, ValidationObserver } from 'vee-validate'
import ToastificationContent from '@core/components/toastification/ToastificationContent.vue'
import 'vue-form-wizard/dist/vue-form-wizard.min.css'
import {
  BRow,
  BCol,
  BFormGroup,
  BFormFile,
  BFormInvalidFeedback,
} from 'bootstrap-vue'
import { required } from '@validations'
import axios from '@/libs/axios'
// eslint-disable-next-line import/no-extraneous-dependencies
import FileDownload from 'js-file-download'

export default {
  components: {
    ValidationProvider,
    ValidationObserver,
    FormWizard,
    TabContent,
    BRow,
    BCol,
    BFormGroup,
    BFormFile,
    vSelect,
    BFormInvalidFeedback,
    // eslint-disable-next-line vue/no-unused-components
    ToastificationContent,
  },
  data() {
    return {
      required,

      templatesOptions: [],
      selectedTemplate: null,
      dataFile: null,
    }
  },
  created() {
    axios.get('/documents/templates/optionsList')
      .then(response => {
        this.templatesOptions = response.data.templates.map(item => ({
          value: item.id,
          label: item.name,
        }))
      })
      .catch(() => {
        this.$toast({
          component: ToastificationContent,
          position: 'top-right',
          props: {
            title: 'Не удалось загрузить шаблоны',
            variant: 'danger',
            icon: 'AlertCircleIcon',
          },
        })
      })
  },
  methods: {
    handleFileUpload() {
      this.dataFile = this.$refs.fileInput.files[0]
    },
    formSubmitted() {
      const formData = new FormData()
      formData.append('template_id', this.selectedTemplate.value)
      formData.append('file', this.dataFile)
      axios.post('/documents/printExcelDataByExistentTemplate', formData, {
        headers: {
          'Content-Type': 'multipart/form-data',
        },
        responseType: 'blob', // Important
      })
        .then(response => {
          FileDownload(response.data, 'Документы по шаблону.zip')
        })
        .catch(() => {
          this.$toast({
            component: ToastificationContent,
            position: 'top-right',
            props: {
              title: 'Не удалось распечатать шаблон',
              variant: 'danger',
              icon: 'AlertCircleIcon',
            },
          })
        })
    },
    validationTemplateForm() {
      return new Promise((resolve, reject) => {
        this.$refs.templateRules.validate().then(success => {
          if (success) {
            resolve(true)
          } else {
            reject()
          }
        })
      })
    },
    validationLoadDataForm() {
      return new Promise((resolve, reject) => {
        this.$refs.loadDataRules.validate().then(success => {
          if (success) {
            resolve(true)
          } else {
            reject()
          }
        })
      })
    },
  },
}
</script>

<style lang="scss">
@import '@core/scss/vue/libs/vue-wizard.scss';
@import '@core/scss/vue/libs/vue-select.scss';
</style>
