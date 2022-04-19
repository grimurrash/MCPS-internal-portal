<template>
  <div class="px-2 helpdesk-wrapper">
    <div class="helpdesk-inner">
      <template>
        <b-card
          v-if="!isTaskGenerated"
          class="mb-0 "
        >
          <b-link class="brand-logo helpdesk-logo mb-2">
            <mpsc-logo />

            <h2 class="brand-text text-primary ml-1">
              Патриот.Спорт
            </h2>
          </b-link>

          <h2 class="card-title mb-1 text-center">
            Заявка в службу технической поддержки
          </h2>

          <!-- form -->
          <validation-observer
            ref="helpDeskForm"
            #default="{invalid, handleSubmit}"
          >
            <b-form
              class="mt-2"
              @submit.prevent="handleSubmit(onSubmit)"
            >
              <b-form-group
                label-for="contactPerson"
                label="Заявитель или контактное лицо"
              >
                <v-select
                  id="employeeId"
                  v-model="employeeId"
                  name="employeeId"
                  :options="employees"
                  placeholder="Контактное лицо"
                  class="w-100"
                  :reduce="val => val.value"
                  autofocus
                />
              </b-form-group>

              <b-form-group
                label="Дополнительная информация о сотруднике"
                label-for="employeeNote"
              >
                <b-form-input
                  id="employeeInfo"
                  v-model="employeeInfo"
                  placeholder="Если вас нет в списки сотрудников, укажите ваш ФИО, телефон, кабинет"
                />
              </b-form-group>

              <b-form-group
                label-for="executionAddress"
                label="Адрес выполнения заявки"
              >
                <validation-provider
                  #default="{ errors }"
                  name="Адрес выполнения заявки"
                  rules="required"
                >
                  <b-form-select
                    id="executionAddress"
                    v-model="executionAddress"
                    name="executionAddress"
                    :options="executionAddresses"
                    :state="executionAddress!==null"
                    placeholder="Адрес выполнения заявки"
                  />
                  <small class="text-danger">{{ errors[0] }}</small>
                </validation-provider>
              </b-form-group>

              <b-form-group
                label-for="category"
                label="Категория заявки"
              >
                <validation-provider
                  #default="{ errors }"
                  name="Категория заявки"
                  rules="required"
                >
                  <b-form-select
                    id="category"
                    v-model="category"
                    name="category"
                    :options="categories"
                    :state="category!==null"
                    placeholder="Категория заявки"
                  />
                  <small class="text-danger">{{ errors[0] }}</small>
                </validation-provider>
              </b-form-group>

              <b-form-group
                label-for="description"
                label="Описание проблемы"
              >
                <validation-provider
                  #default="{ errors }"
                  name="Описание проблемы"
                  rules="required"
                >
                  <b-form-textarea
                    id="description"
                    v-model="description"
                    name="description"
                    :state="description.length > 1"
                    placeholder="Описание проблемы"
                    rows="3"
                  />
                  <small class="text-danger">{{ errors[0] }}</small>
                </validation-provider>
              </b-form-group>

              <!-- submit button -->
              <b-button
                variant="primary"
                type="submit"
                block
                :disabled="invalid"
              >
                Отправить
              </b-button>
            </b-form>
          </validation-observer>
        </b-card>
        <b-card v-else>
          <b-link class="brand-logo">
            <mpsc-logo />

            <h2 class="brand-text text-primary ml-1">
              Патриот Спорт
            </h2>
          </b-link>

          <h2 class="card-title mb-1 text-center">
            Заявка в службу технической поддержки
          </h2>

          <h4 class="mb-2 text-center text-success">
            Заявка создана!
          </h4>
        </b-card>
      </template>

    </div>
  </div>
</template>

<script>
import { ValidationObserver, ValidationProvider } from 'vee-validate'
import {
  BButton,
  BCard,
  BForm,
  BFormGroup,
  BFormSelect,
  BFormInput,
  BFormTextarea,
  BLink,
} from 'bootstrap-vue'
import { email, required } from '@validations'
import MpscLogo from '@/layouts/components/Logo.vue'
import axios from '@/libs/axios'
import ToastificationContent from '@core/components/toastification/ToastificationContent.vue'
import vSelect from 'vue-select'

export default {
  components: {
    // BSV
    BButton,
    BForm,
    BFormGroup,
    BCard,
    BLink,
    BFormInput,
    BFormSelect,
    BFormTextarea,
    vSelect,
    ValidationProvider,
    ValidationObserver,
    MpscLogo,
  },
  data() {
    return {
      required,
      email,
      isTaskGenerated: false,

      executionAddresses: [],
      categories: [],
      employees: [],

      employeeId: null,
      employeeInfo: '',
      executionAddress: null,
      cabinetNumber: '',
      category: null,
      description: '',
    }
  },
  computed: {},
  created() {
    axios.get('/helpdesk/options').then(response => {
      this.employees = Object.keys(response.data.employees).map(key => ({
        value: key,
        label: response.data.employees[key],
      }))
      this.executionAddresses = response.data.addresses.map((text, value) => ({
        value,
        text,
      }))
      this.categories = response.data.categories.map((text, value) => ({
        value,
        text,
      }))
    }).catch(() => {
      this.$toast({
        component: ToastificationContent,
        position: 'top-right',
        props: {
          title: 'Не удалось загрузить выпадающие списки',
          variant: 'danger',
          icon: 'AlertCircleIcon',
        },
      })
    })
  },
  methods: {
    onSubmit() {
      this.$refs.helpDeskForm.validate().then(success => {
        if (!success) {
          return
        }
        axios.post('/helpdesk/create', {
          employee_id: this.employeeId,
          employee_info: this.employeeInfo,
          execution_address: this.executionAddress,
          cabinet_number: this.cabinetNumber,
          category: this.category,
          description: this.description,
        }).then(() => {
          this.isTaskGenerated = true
        }).catch(() => {
          this.$toast({
            component: ToastificationContent,
            position: 'top-right',
            props: {
              title: 'Не удалось создать заявку',
              variant: 'danger',
              icon: 'AlertCircleIcon',
            },
          })
        })
      })
    },
  },
}
</script>

<style lang="scss">
@import '~@core/scss/vue/libs/vue-select.scss';
</style>
