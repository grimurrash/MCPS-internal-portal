<template>
  <b-sidebar
    id="edit-employee-sidebar"
    :visible="isEditEmployeeSidebarActive"
    bg-variant="white"
    sidebar-class="sidebar-lg"
    shadow
    backdrop
    no-header
    right
    @hidden="resetForm"
    @change="(val) => $emit('update:is-edit-employee-sidebar-active', val)"
  >
    <template #default="{ hide }">
      <!-- Header -->
      <div class="d-flex justify-content-between align-items-center content-sidebar-header px-2 py-1">
        <h5 class="mb-0">
          Изменить информацию о сотрудника
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
              v-model="employee.fullName"
              disabled
              trim
              placeholder="ФИО"
            />
          </b-form-group>

          <!-- Department -->
          <b-form-group
            label="Отдел"
            label-for="department"
          >
            <b-form-input
              id="department"
              v-model="employee.department"
              disabled
              trim
              placeholder="Отдел"
            />
          </b-form-group>

          <!-- WorkingPosition -->
          <b-form-group
            label="Должность"
            label-for="workingPosition"
          >
            <b-form-input
              id="workingPosition"
              v-model="employee.workingPosition"
              autofocus
              trim
              placeholder="Должность"
            />
          </b-form-group>

          <!-- RoomNumber -->
          <validation-provider
            #default="{ errors }"
            name="Рабочий кабинет"
            rules="max:5"
          >
            <b-form-group
              label="Рабочий кабинет"
              label-for="roomNumber"
            >
              <b-form-input
                id="roomNumber"
                v-model="employee.roomNumber"
                autofocus
                trim
                :state="errors.length > 0 ? false:null"
                placeholder="Рабочий кабинет"
              />
              <small class="text-danger">{{ errors[0] }}</small>
            </b-form-group>
          </validation-provider>

          <!-- internalCode -->
          <validation-provider
            #default="{ errors }"
            name="Внутренний номер"
            rules="max:4"
          >
            <b-form-group
              label="Внутренний номер"
              label-for="internalCode"
            >
              <b-form-input
                id="internalCode"
                v-model="employee.internalCode"
                trim
                :state="errors.length > 0 ? false:null"
                placeholder="Внутренний номер"
              />
              <small class="text-danger">{{ errors[0] }}</small>
            </b-form-group>
          </validation-provider>

          <!-- mobilePhone -->
          <validation-provider
            #default="{ errors }"
            name="Мобильный телефон"
            rules="max:20"
          >
            <b-form-group
              label="Мобильный телефон"
              label-for="mobilePhone"
            >
              <b-form-input
                id="mobilePhone"
                v-model="employee.mobilePhone"
                trim
                :state="errors.length > 0 ? false:null"
                placeholder="Мобильный телефон"
              />
              <small class="text-danger">{{ errors[0] }}</small>
            </b-form-group>
          </validation-provider>

          <b-form-group
            label="Пол"
            label-for="gender"
          >
            <v-select
              id="gender"
              v-model="employee.gender"
              :options="genderOptions"
              :reduce="val => val.value"
              trim
            />
          </b-form-group>

          <b-form-group
            label="Образование"
            label-for="education"
          >
            <v-select
              id="gender"
              v-model="employee.education"
              :options="educationOptions"
              :reduce="val => val.value"
              trim
            />
          </b-form-group>

          <!-- dateOfBirth -->
          <b-form-group
            label="Дата рождения"
            label-for="dateOfBirth"
          >
            <b-input-group>
              <b-form-input
                id="dateOfBirth"
                v-model="employee.dateOfBirth"
                type="text"
                placeholder="YYYY-MM-DD"
              />
              <b-input-group-append>
                <b-form-datepicker
                  v-model="employee.dateOfBirth"
                  button-only
                  button-variant="outline-primary"
                  size="sm"
                  right
                  aria-controls="dateOfBirth"
                />
              </b-input-group-append>
            </b-input-group>
          </b-form-group>

          <!-- dateOfEmployment -->
          <b-form-group
            label="Дата принятия на работу"
            label-for="dateOfEmployment"
          >
            <b-input-group>
              <b-form-input
                id="dateOfEmployment"
                v-model="employee.dateOfEmployment"
                type="text"
                placeholder="YYYY-MM-DD"
              />
              <b-input-group-append>
                <b-form-datepicker
                  v-model="employee.dateOfEmployment"
                  button-only
                  button-variant="outline-primary"
                  size="sm"
                  right
                  aria-controls="dateOfEmployment"
                />
              </b-input-group-append>
            </b-input-group>
          </b-form-group>

          <b-form-group
            label="Дата прохождения курса Представителей Учредителя"
            label-for="foundersRepresentativeDate"
          >
            <b-input-group>
              <b-form-input
                id="foundersRepresentativeDate"
                v-model="employee.foundersRepresentativeDate"
                type="text"
                placeholder="YYYY-MM-DD"
              />
              <b-input-group-append>
                <b-form-datepicker
                  v-model="employee.foundersRepresentativeDate"
                  button-only
                  button-variant="outline-primary"
                  size="sm"
                  right
                  aria-controls="foundersRepresentativeDate"
                />
              </b-input-group-append>
            </b-input-group>
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
  BSidebar, BForm, BFormGroup, BFormInput, BButton, BInputGroup, BInputGroupAppend, BFormDatepicker,
} from 'bootstrap-vue'
import { ValidationObserver, ValidationProvider } from 'vee-validate'
import { ref } from '@vue/composition-api'
import { required, regex, max } from '@validations'
import formValidation from '@core/comp-functions/forms/form-validation'
import Ripple from 'vue-ripple-directive'
import axios from '@/libs/axios'
import ToastificationContent from '@core/components/toastification/ToastificationContent.vue'
import vSelect from 'vue-select'

export default {
  components: {
    vSelect,
    BSidebar,
    BForm,
    BFormGroup,
    BFormInput,
    BButton,
    BFormDatepicker,
    BInputGroup,
    BInputGroupAppend,

    // Form Validation
    ValidationObserver,
    ValidationProvider,
  },
  directives: {
    Ripple,
  },
  model: {
    prop: 'isEditEmployeeSidebarActive',
    event: 'update:is-edit-employee-sidebar-active',
  },
  props: {
    isEditEmployeeSidebarActive: {
      type: Boolean,
      required: true,
    },
    employeeId: {
      type: Object,
      required: true,
    },
    genderOptions: {
      type: Array,
      required: true,
    },
    educationOptions: {
      type: Array,
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
    employeeId(employeeId) {
      if (employeeId.id === 0) return
      this.employee = {
        id: employeeId.id,
        fullName: employeeId.fullName,
        department: employeeId.department,
        internalCode: employeeId.internalCode,
        mobilePhone: employeeId.mobilePhone,
        roomNumber: employeeId.roomNumber,
        workingPosition: employeeId.workingPosition,
        gender: employeeId.gender,
        dateOfBirth: employeeId.date_of_birth,
        education: employeeId.education,
        foundersRepresentativeDate: employeeId.founders_representative_date,
        dateOfEmployment: employeeId.date_of_employment,
      }
    },
  },
  methods: {
    onSubmit() {
      this.$refs.refFormObserver.validate()
        .then(success => {
          if (!success) return
          axios.post(`/management/employees/${this.employee.id}`, {
            internalCode: this.employee.internalCode,
            mobilePhone: this.employee.mobilePhone,
            roomNumber: this.employee.roomNumber,
            workingPosition: this.employee.workingPosition,
            gender: this.employee.gender,
            dateOfBirth: this.employee.dateOfBirth,
            education: this.employee.education,
            foundersRepresentativeDate: this.employee.foundersRepresentativeDate,
            dateOfEmployment: this.employee.dateOfEmployment,
          })
            .then(() => {
              this.$emit('refetch-data')
            })
            .catch(() => {
              this.$toast({
                component: ToastificationContent,
                position: 'top-right',
                props: {
                  title: 'Не удалось обновить информацию о сотруднике',
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
      fullName: '',
      internalCode: '',
      mobilePhone: '',
      roomNumber: '',
      workingPosition: '',
      startOfTheDay: null,
      endOfTheDay: null,
      visitControl: false,
      gender: null,
      dateOfBirth: null,
      education: null,
      foundersRepresentativeDate: null,
      dateOfEmployment: null,
    }
    const employee = ref(JSON.parse(JSON.stringify(blankUserData)))
    const resetuserData = () => {
      employee.value = JSON.parse(JSON.stringify(blankUserData))
    }
    const {
      refFormObserver,
      getValidationState,
      resetForm,
    } = formValidation(resetuserData)

    return {
      employee,
      refFormObserver,
      getValidationState,
      resetForm,
    }
  },
}
</script>

<style lang="scss">

</style>
