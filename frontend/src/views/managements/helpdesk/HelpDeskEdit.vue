<template>
  <b-sidebar
    id="add-sidebar"
    :visible="isEditSidebarActive"
    bg-variant="white"
    sidebar-class="sidebar-lg"
    shadow
    backdrop
    no-header
    right
    @hidden="resetForm"
    @change="(val) => $emit('update:is-edit-sidebar-active', val)"
  >
    <template #default="{ hide }">
      <!-- Header -->
      <div class="d-flex justify-content-between align-items-center content-sidebar-header px-2 py-1">
        <h5 class="mb-0">
          Информация о заявке
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

          <b-form-group
            v-if="itemData.employee && itemData.employee.fullName"
            label="Заявитель"
            label-for="employeeFullName"
          >
            <b-form-input
              id="employeeFullName"
              v-model="itemData.employee.fullName"
              disabled
            />
          </b-form-group>

          <b-form-group
            v-if="itemData.employee_info"
            label="Информация о заявителе"
            label-for="employeeFullName"
          >
            <b-form-input
              id="employeeInfo"
              v-model="itemData.employee_info"
              disabled
            />
          </b-form-group>

          <b-form-group
            v-if="itemData.employee && itemData.employee.department"
            label="Отдел"
            label-for="employeeDepartment"
          >
            <b-form-input
              id="employeeDepartment"
              v-model="itemData.employee.department"
              disabled
            />
          </b-form-group>

          <b-form-group
            v-if="itemData.employee && itemData.employee.mobilePhone"
            label="Номер телефона"
            label-for="employeeMobilePhone"
          >
            <b-form-input
              id="employeeMobilePhone"
              v-model="itemData.employee.mobilePhone"
              disabled
            />
          </b-form-group>

          <b-form-group
            v-if="itemData.employee && itemData.employee.email"
            label="Почта"
            label-for="employeeEmail"
          >
            <b-form-input
              id="employeeEmail"
              v-model="itemData.employee.email"
              disabled
            />
          </b-form-group>

          <b-form-group
            v-if="itemData.execution_address"
            label="Адрес"
            label-for="executionAddress"
          >
            <b-form-input
              id="executionAddress"
              v-model="itemData.execution_address"
              disabled
            />
          </b-form-group>

          <b-form-group
            v-if="itemData.employee && itemData.employee.roomNumber"
            label="Кабинет"
            label-for="employeeRoomNumber"
          >
            <b-form-input
              id="employeeRoomNumber"
              v-model="itemData.employee.roomNumber"
              disabled
            />
          </b-form-group>

          <b-form-group
            label="Категория заявки"
            label-for="category"
          >
            <b-form-input
              id="category"
              v-model="itemData.category"
              disabled
            />
          </b-form-group>

          <b-form-group
            label="Описание"
            label-for="description"
          >
            <b-form-textarea
              id="description"
              v-model="itemData.description"
              disabled
              rows="3"
            />
          </b-form-group>

          <b-form-group
            label="Исполнитель"
            label-for="description"
          >
            <v-select
              v-model="itemData.executor_id"
              :options="executorsOptions"
              class="w-100"
              :reduce="val => val.value"
              :state="itemData.executor_id!==null"
            />
          </b-form-group>

          <b-form-group
            label="Статус заявки"
            label-for="description"
          >
            <validation-provider
              #default="{ errors }"
              name="Статус заявки"
              rules="required"
            >
              <v-select
                v-model="itemData.status_id"
                :options="statusesOptions"
                class="w-100"
                :reduce="val => val.value"
                :state="itemData.status_id!==null"
              />
              <small class="text-danger">{{ errors[0] }}</small>
            </validation-provider>
          </b-form-group>

          <b-form-group
            label="Комментарий исполнителя"
            label-for="executorNote"
          >
            <b-form-textarea
              id="executorNote"
              v-model="itemData.executor_note"
              rows="3"
            />
          </b-form-group>

          <b-form-group
            v-if="itemData.date_of_execution"
            label="Дата завершения"
            label-for="dateOfExecution"
          >
            <b-form-input
              id="dateOfExecution"
              v-model="itemData.date_of_execution"
              disabled
            />
          </b-form-group>

          <b-form-group
            v-if="itemData.execution_time"
            label="Время исполнения"
            label-for="executionTime"
          >
            <b-form-input
              id="executionTime"
              v-model="itemData.execution_time"
              disabled
            />
          </b-form-group>

          <b-form-group
            v-if="itemData.estimation"
            label="Оценка"
            label-for="executorNote"
          >
            <b-form-rating
              id="rating-md-no-border"
              v-model="itemData.estimation"
              variant="warning"
              stars="10"
              readonly
              show-value
            />
          </b-form-group>

          <b-form-group
            v-if="itemData.employee_note"
            label="Комментарий сотрудника"
            label-for="employeeNote"
          >
            <b-form-textarea
              id="employeeNote"
              v-model="itemData.employee_note"
              disabled
              rows="3"
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
              Сохранить
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
  BButton, BForm, BFormGroup, BFormInput, BFormRating, BFormTextarea, BSidebar,
} from 'bootstrap-vue'
import { ValidationObserver, ValidationProvider } from 'vee-validate'
import formValidation from '@core/comp-functions/forms/form-validation'
import { required } from '@validations'
import Ripple from 'vue-ripple-directive'
import { ref } from '@vue/composition-api'
import vSelect from 'vue-select'
import axios from '@/libs/axios'
import ToastificationContent from '@core/components/toastification/ToastificationContent.vue'

export default {
  components: {
    BSidebar,
    BForm,
    BButton,
    BFormGroup,
    BFormInput,
    BFormRating,
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
    prop: 'isEditSidebarActive',
    event: 'update:is-edit-sidebar-active',
  },
  props: {
    isEditSidebarActive: {
      type: Boolean,
      required: true,
    },
    editItem: {
      type: Object,
      required: true,
    },
    statusesOptions: {
      type: Array,
      required: true,
    },
    executorsOptions: {
      type: Array,
      required: true,
    },
  },
  data() {
    return {
      required,
    }
  },
  watch: {
    editItem(newItem) {
      if (newItem.id === 0) {
        return
      }
      this.itemData = {
        id: newItem.id,
        creation_time: newItem.creation_time,
        employee_info: newItem.employee_info,
        employee_id: newItem.employee_id,
        employee: newItem.employee,
        execution_address: newItem.execution_address,
        category_id: newItem.category_id,
        category: newItem.category,
        description: newItem.description,
        executor_id: newItem.executor_id,
        status_id: newItem.status_id,
        date_of_execution: newItem.date_of_execution,
        execution_time: newItem.execution_time,
        is_send_feedback_email: newItem.is_send_feedback_email,
        estimation: newItem.estimation,
        employee_note: newItem.employee_note,
        executor_note: newItem.executor_note,
      }
    },
  },
  methods: {
    onSubmit() {
      this.$refs.refFormObserver.validate().then(success => {
        if (!success) {
          return
        }
        axios.post(`/helpdesk/${this.itemData.id}`, {
          executor_id: this.itemData.executor_id,
          status_id: this.itemData.status_id,
          executor_note: this.itemData.executor_note,
        }).then(() => {
          this.$emit('refetch-data')
          this.$toast({
            component: ToastificationContent,
            props: {
              title: 'Изменения сохранены!',
              icon: 'CheckIcon',
              variant: 'success',
            },
          })
        }).catch(() => {
          this.$toast({
            component: ToastificationContent,
            props: {
              title: 'Ошибка при редактировании заявки',
              icon: 'AlertTriangleIcon',
              variant: 'danger',
            },
          })
        })
      })
    },
  },
  setup() {
    const blankItemData = {
      id: 1,
      creation_time: '',
      employee_id: 0,
      employee_info: '',
      employee: {
        id: 0,
        fullName: '',
        department: null,
        roomNumber: null,
        mobilePhone: null,
        email: null,
      },
      execution_address_id: 0,
      execution_address: '',
      category_id: 0,
      category: '',
      description: '',
      executor_id: null,
      executor: null,
      status_id: 0,
      status: '',
      date_of_execution: null,
      execution_time: null,
      is_send_feedback_email: false,
      estimation: 10,
      employee_note: 'null',
      executor_note: '',
    }

    const itemData = ref(JSON.parse(JSON.stringify(blankItemData)))
    const resetData = () => {
      itemData.value = JSON.parse(JSON.stringify(blankItemData))
    }

    const {
      refFormObserver,
      getValidationState,
      resetForm,
    } = formValidation(resetData)

    return {
      itemData,
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
