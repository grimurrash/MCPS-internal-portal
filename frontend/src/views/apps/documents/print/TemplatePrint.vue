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
            title="Загрузка шаблона"
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
                    Данные
                  </h5>
                  <small class="text-muted">Загрузите файл с данными для заполнения шаблона</small>
                </b-col>
                <b-col md="6">
                  <b-form-group
                    label="Шаблон"
                    label-for="template-file"
                  >
                    <validation-provider
                      #default="{ errors }"
                      name="Excel Файл с данными"
                      rules="required"
                    >
                      <b-form-file
                        id="template-file"
                        ref="templateFileInput"
                        v-model="templateFile"
                        :state="errors.length > 0 ? false:null"
                        accept=".doc, .docx"
                        placeholder="Выберите файл или перетащите его сюда..."
                        drop-placeholder="Перетащите файл сюда..."
                        @change="handleTemplateFileUpload()"
                      />
                      <small class="text-danger">{{ errors[0] }}</small>
                    </validation-provider>
                  </b-form-group>
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
                    label="Excel Файл с данными"
                    label-for="data-file"
                  >
                    <validation-provider
                      #default="{ errors }"
                      name="Excel Файл с данными"
                      rules="required"
                    >
                      <b-form-file
                        id="data-file"
                        ref="dataFileInput"
                        v-model="dataFile"
                        :state="errors.length > 0 ? false:null"
                        accept=".xls, .xlsx"
                        placeholder="Выберите файл или перетащите его сюда..."
                        drop-placeholder="Перетащите файл сюда..."
                        @change="handleDataFileUpload()"
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
import { ValidationProvider, ValidationObserver } from 'vee-validate'
import ToastificationContent from '@core/components/toastification/ToastificationContent.vue'
import 'vue-form-wizard/dist/vue-form-wizard.min.css'
import {
  BRow,
  BCol,
  BFormGroup,
  BFormFile,
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
    // eslint-disable-next-line vue/no-unused-components
    ToastificationContent,
  },
  data() {
    return {
      required,

      templateFile: null,
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
    handleTemplateFileUpload() {
      this.templateFile = this.$refs.templateFileInput.files[0]
    },
    handleDataFileUpload() {
      this.dataFile = this.$refs.dataFileInput.files[0]
    },
    formSubmitted() {
      const formData = new FormData()
      formData.append('templateFile', this.templateFile)
      formData.append('dataFile', this.dataFile)
      axios.post('/documents/printExcelDataByTemplate', formData, {
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
