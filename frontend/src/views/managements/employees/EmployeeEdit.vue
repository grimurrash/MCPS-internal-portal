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

          <!-- startOfTheDay -->
          <validation-provider
            #default="{ errors }"
            name="Время начала рабочего дня"
            rules="required|regex:^[0-2][0-9]:[0-5][0-9]$"
          >
            <b-form-group
              label="Время начала рабочего дня"
              label-for="startOfTheDay"
            >
              <b-input-group>
                <b-form-input
                  id="startOfTheDay"
                  v-model="employee.startOfTheDay"
                  type="text"
                  :state="errors.length > 0 ? false:null"
                  placeholder="HH:mm"
                />
                <b-input-group-append>
                  <b-form-timepicker
                    v-model="employee.startOfTheDay"
                    button-only
                    button-variant="outline-primary"
                    size="sm"
                    right
                    aria-controls="startOfTheDay"
                  />
                </b-input-group-append>
              </b-input-group>
              <small class="text-danger">{{ errors[0] }}</small>
            </b-form-group>
          </validation-provider>

          <!-- endOfTheDay -->
          <validation-provider
            #default="{ errors }"
            name="Время окончания рабочего дня"
            rules="required|regex:^[0-2][0-9]:[0-5][0-9]$"
          >
            <b-form-group
              label="Время окончания рабочего дня"
              label-for="endOfTheDay"
            >
              <b-input-group>
                <b-form-input
                  id="endOfTheDay"
                  v-model="employee.endOfTheDay"
                  type="text"
                  :state="errors.length > 0 ? false:null"
                  placeholder="HH:mm"
                />
                <b-input-group-append>
                  <b-form-timepicker
                    v-model="employee.endOfTheDay"
                    button-only
                    button-variant="outline-primary"
                    size="sm"
                    right
                    aria-controls="endOfTheDay"
                  />
                </b-input-group-append>
              </b-input-group>
              <small class="text-danger">{{ errors[0] }}</small>
            </b-form-group>
          </validation-provider>

          <!-- visitControl -->
          <b-form-group
            label="Контроль посещения"
            label-for="visitControl"
          >
            <b-form-checkbox
              id="visitControl"
              v-model="employee.visitControl"
              class="custom-control-primary"
              name="visitControl"
              switch
            >
              <span class="switch-icon-left">
                <feather-icon icon="BellIcon" />
              </span>
              <span class="switch-icon-right">
                <feather-icon icon="BellOffIcon" />
              </span>
            </b-form-checkbox>
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
  BSidebar, BForm, BFormGroup, BFormInput, BButton, BFormTimepicker, BInputGroup, BInputGroupAppend, BFormCheckbox,
} from 'bootstrap-vue'
import { ValidationObserver, ValidationProvider } from 'vee-validate'
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
    BFormCheckbox,
    BFormTimepicker,
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
        startOfTheDay: employeeId.startOfTheDay,
        endOfTheDay: employeeId.endOfTheDay,
        visitControl: employeeId.visitControl,
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
            startOfTheDay: this.employee.startOfTheDay,
            endOfTheDay: this.employee.endOfTheDay,
            visitControl: this.employee.visitControl,
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
