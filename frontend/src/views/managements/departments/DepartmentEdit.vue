<template>
  <b-sidebar
    id="edit-department-sidebar"
    :visible="isEditDepartmentSidebarActive"
    bg-variant="white"
    sidebar-class="sidebar-lg"
    shadow
    backdrop
    no-header
    right
    @hidden="resetForm"
    @change="(val) => $emit('update:is-edit-department-sidebar-active', val)"
  >
    <template #default="{ hide }">
      <!-- Header -->
      <div class="d-flex justify-content-between align-items-center content-sidebar-header px-2 py-1">
        <h5 class="mb-0">
          Измененить отдел
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
            label="Наименование отдела"
            label-for="name"
          >
            <b-form-input
              id="name"
              v-model="department.name"
              autofocus
              disabled=""
              trim
              placeholder="Наименование отдела"
            />
          </b-form-group>

          <!-- DocumentType_id -->
          <validation-provider
            #default="validationContext"
            name="Руководитель отдела"
            rules="required"
          >
            <b-form-group
              label="Руководитель отдела"
              label-for="head_id"
              :state="getValidationState(validationContext)"
            >
              <v-select
                v-model="department.head_id"
                :dir="$store.state.appConfig.isRTL ? 'rtl' : 'ltr'"
                :options="usersOptions"
                :reduce="val => val.value"
                :clearable="false"
                input-id="head_id"
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
  BSidebar, BForm, BFormGroup, BFormInput, BFormInvalidFeedback, BButton,
} from 'bootstrap-vue'
import { ValidationProvider, ValidationObserver } from 'vee-validate'
import { ref } from '@vue/composition-api'
import { required, alphaNum } from '@validations'
import formValidation from '@core/comp-functions/forms/form-validation'
import Ripple from 'vue-ripple-directive'
import vSelect from 'vue-select'
import axios from '@/libs/axios'
import ToastificationContent from '@core/components/toastification/ToastificationContent.vue'
import { useToast } from 'vue-toastification/composition'

export default {
  components: {
    BSidebar,
    BForm,
    BFormGroup,
    BFormInput,
    BFormInvalidFeedback,
    BButton,
    vSelect,

    // Form Validation
    ValidationProvider,
    ValidationObserver,
  },
  directives: {
    Ripple,
  },
  model: {
    prop: 'isEditDepartmentSidebarActive',
    event: 'update:is-edit-department-sidebar-active',
  },
  props: {
    isEditDepartmentSidebarActive: {
      type: Boolean,
      required: true,
    },
    departmentId: {
      type: Object,
      required: true,
    },
  },
  data() {
    return {
      required,
      alphaNum,
    }
  },
  watch: {
    departmentId(editDepartment) {
      if (editDepartment.id === 0) return
      this.department = {
        id: editDepartment.id,
        name: editDepartment.name,
        parent: editDepartment.parent_id,
        head_id: editDepartment.head_id,
      }
    },
  },
  methods: {
    onSubmit() {
      this.$refs.refFormObserver.validate()
        .then(success => {
          if (!success) return
          axios.post(`/management/departments/${this.department.id}`, {
            head_id: this.department.head_id,
          })
            .then(() => {
              this.$emit('refetch-data')
            })
            .catch(() => {
              this.$toast({
                component: ToastificationContent,
                position: 'top-right',
                props: {
                  title: 'Не удалось обновить информацию о руководителе отдела',
                  variant: 'danger',
                  icon: 'AlertCircleIcon',
                },
              })
            })
        })
    },
  },
  setup() {
    const toast = useToast()
    const blankUserData = {
      id: 0,
      name: '',
      parent: '',
      head_id: null,
    }
    const usersOptions = ref([])

    axios.get('management/users/options')
      .then(response => {
        usersOptions.value = response.data.users.map(item => ({
          value: item.id,
          label: item.fullName,
        }))
      })
      .catch(() => {
        toast({
          component: ToastificationContent,
          position: 'top-right',
          props: {
            title: 'Не удалось загрузить пользователей',
            variant: 'danger',
            icon: 'AlertCircleIcon',
          },
        })
      })
    const department = ref(JSON.parse(JSON.stringify(blankUserData)))
    const resetuserData = () => {
      department.value = JSON.parse(JSON.stringify(blankUserData))
    }
    const {
      refFormObserver,
      getValidationState,
      resetForm,
    } = formValidation(resetuserData)

    return {
      department,
      usersOptions,
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
