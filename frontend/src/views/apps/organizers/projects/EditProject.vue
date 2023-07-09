<script>

import Ripple from 'vue-ripple-directive'
import ToastificationContent from '@core/components/toastification/ToastificationContent.vue'
import { ref } from '@vue/composition-api'
import formValidation from '@core/comp-functions/forms/form-validation'
import { useToast } from 'vue-toastification/composition'
import axios from '@/libs/axios'
import {
  BButton, BForm, BFormGroup, BFormInput, BFormInvalidFeedback, BFormTextarea, BSidebar,
} from 'bootstrap-vue'
import vSelect from 'vue-select'
import { ValidationObserver, ValidationProvider } from 'vee-validate'
import { required } from '@core/utils/validations/validations'
import flatPickr from 'vue-flatpickr-component'

export default {
  name: 'EditProject',
  components: {
    BFormTextarea,
    BSidebar,
    BForm,
    BFormGroup,
    BFormInput,
    BFormInvalidFeedback,
    BButton,
    vSelect,
    flatPickr,

    // Form Validation
    ValidationProvider,
    ValidationObserver,
  },
  directives: {
    Ripple,
  },
  model: {
    prop: 'isEditProject',
    event: 'update:is-edit-project',
  },
  props: {
    isEditProject: {
      type: Boolean,
      required: true,
    },
    selectProject: {
      type: Object,
      required: true,
    },
    curatorOptions: {
      type: Array,
      required: true,
    },
    employeeOptions: {
      type: Array,
      required: true,
    },
    statusOptions: {
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
    selectProject(selectP) {
      if (selectP.id === 0) return

      this.project = {
        id: selectP.id,
        name: selectP.name,
        number: selectP.number,
        start_date: selectP.start_date,
        end_date: selectP.end_date,
        responsible_employee_id: selectP.responsible_employee_id,
        description: selectP.description,
        metrics: selectP.metrics,
        planned_coverage: selectP.planned_coverage,
        actual_coverage: selectP.actual_coverage,
        curator_id: selectP.curator_id,
        organizer_id: selectP.organizer_id,
        status: selectP.status,
      }
    },
  },
  methods: {
    onSubmit() {
      console.log(this.$refs)
      this.$refs.refFormObserver.validate()
        .then(success => {
          if (!success) return
          console.log(this.project)
          axios.post(`/organization-projects/${this.project.id}`, {
            name: this.project.name,
            number: this.project.number,
            start_date: this.project.start_date,
            end_date: this.project.end_date,
            responsible_employee_id: this.project.responsible_employee_id,
            description: this.project.description,
            metrics: this.project.metrics,
            planned_coverage: this.project.planned_coverage,
            actual_coverage: this.project.actual_coverage,
            curator_id: this.project.curator_id,
            organizer_id: this.project.organizer_id,
            status: this.project.status,
          })
            .then(() => {
              this.$emit('refresh-data')
              this.$emit('update:is-edit-project', false)
            })
            .catch(error => {
              console.log(error)
              this.toast({
                component: ToastificationContent,
                props: {
                  title: 'Ошибка при обновлении проекта',
                  icon: 'AlertTriangleIcon',
                  variant: 'danger',
                },
              })
            })
        })
    },
  },
  setup() {
    const bankProject = ref({
      id: 0,
      name: null,
      number: null,
      start_date: null,
      end_date: null,
      responsible_employee_id: null,
      description: '',
      metrics: '',
      planned_coverage: 0,
      actual_coverage: 0,
      curator_id: null,
      organizer_id: null,
      status: 1,
    })
    const project = ref(JSON.parse(JSON.stringify(bankProject)))
    const resetProject = () => {
      project.value = JSON.parse(JSON.stringify(bankProject))
    }
    const toast = useToast()

    const {
      refFormObserver,
      getValidationState,
      resetForm,
    } = formValidation(resetProject)

    return {
      project,
      toast,

      refFormObserver,
      getValidationState,
      resetProject,
      resetForm,
    }
  },
}
</script>

<template>
  <b-sidebar
    id="add-new-user-sidebar"
    :visible="isEditProject"
    bg-variant="white"
    sidebar-class="sidebar-lg"
    shadow
    backdrop
    no-header
    right
    @hidden="resetForm"
    @change="(val) => $emit('update:is-edit-project', val)"
  >
    <template #default="{ hide }">
      <!-- Header -->
      <div class="d-flex justify-content-between align-items-center content-sidebar-header px-2 py-1">
        <h5 class="mb-0">
          Добавить новый проект
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

          <validation-provider
            #default="validationContext"
            name="Наименование"
            rules="required"
          >
            <b-form-group
              label="Наименование"
              label-for="name"
            >
              <b-form-input
                id="name"
                v-model="project.name"
                autofocus
                :state="getValidationState(validationContext)"
                trim
              />

              <b-form-invalid-feedback>
                {{ validationContext.errors[0] }}
              </b-form-invalid-feedback>
            </b-form-group>
          </validation-provider>

          <validation-provider
            #default="validationContext"
            name="№ согласно календарному плану на учебный год "
            rules="required"
          >
            <b-form-group
              label="№ согласно календарному плану на учебный год "
              label-for="number"
            >
              <b-form-input
                id="number"
                v-model="project.number"
                autofocus
                :state="getValidationState(validationContext)"
                trim
              />

              <b-form-invalid-feedback>
                {{ validationContext.errors[0] }}
              </b-form-invalid-feedback>
            </b-form-group>
          </validation-provider>

          <validation-provider
            #default="validationContext"
            name="сроки реализации, начало"
            rules="required"
          >
            <b-form-group
              label="сроки реализации, дата начало"
              label-for="start_date"
            >
              <flat-pickr
                id="start_date"
                v-model="project.start_date"
                class="form-control"
                :state="getValidationState(validationContext)"
                :config="{ dateFormat: 'Y-m-d'}"
              />

              <b-form-invalid-feedback>
                {{ validationContext.errors[0] }}
              </b-form-invalid-feedback>
            </b-form-group>
          </validation-provider>

          <validation-provider
            #default="validationContext"
            name="сроки реализации, окончание"
            rules="required"
          >
            <b-form-group
              label="сроки реализации, дата окончание"
              label-for="end_date"
            >
              <flat-pickr
                id="start_date"
                v-model="project.end_date"
                class="form-control"
                :state="getValidationState(validationContext)"
                :config="{ dateFormat: 'Y-m-d'}"
              />

              <b-form-invalid-feedback>
                {{ validationContext.errors[0] }}
              </b-form-invalid-feedback>
            </b-form-group>
          </validation-provider>

          <validation-provider
            #default="validationContext"
            name="Ответственный за проект"
            rules="required"
          >
            <b-form-group
              label="Ответственный за проект"
              label-for="responsible_employee_id"
            >
              <v-select
                id="responsible_employee_id"
                v-model="project.responsible_employee_id"
                :options="employeeOptions"
                :reduce="val => val.value"
                :state="getValidationState(validationContext)"
                :clearable="false"
              />
              <b-form-invalid-feedback>
                {{ validationContext.errors[0] }}
              </b-form-invalid-feedback>
            </b-form-group>
          </validation-provider>

          <validation-provider
            #default="validationContext"
            name="Краткое описание проект"
            rules="required"
          >
            <b-form-group
              label="Краткое описание проект"
              label-for="description"
            >
              <b-form-textarea
                id="description"
                v-model="project.description"
                :state="getValidationState(validationContext)"
                trim
              />
              <b-form-invalid-feedback>
                {{ validationContext.errors[0] }}
              </b-form-invalid-feedback>
            </b-form-group>
          </validation-provider>

          <validation-provider
            #default="validationContext"
            name="Ключевые показатели"
            rules="required"
          >
            <b-form-group
              label="Ключевые показатели"
              label-for="metrics"
            >
              <b-form-textarea
                id="metrics"
                v-model="project.metrics"
                :state="getValidationState(validationContext)"
                trim
              />
              <b-form-invalid-feedback>
                {{ validationContext.errors[0] }}
              </b-form-invalid-feedback>
            </b-form-group>
          </validation-provider>

          <validation-provider
            #default="validationContext"
            name="Планируемый охват участников"
            rules="required"
          >
            <b-form-group
              label="Планируемый охват участников"
              label-for="planned_coverage"
            >
              <b-form-input
                id="planned_coverage"
                v-model="project.planned_coverage"
                type="number"
                :state="getValidationState(validationContext)"
                trim
              />
              <b-form-invalid-feedback>
                {{ validationContext.errors[0] }}
              </b-form-invalid-feedback>
            </b-form-group>
          </validation-provider>

          <validation-provider
            #default="validationContext"
            name="Фактический охват участников"
            rules="required"
          >
            <b-form-group
              label="Фактический охват участников"
              label-for="actual_coverage"
            >
              <b-form-input
                id="actual_coverage"
                v-model="project.actual_coverage"
                type="number"
                :state="getValidationState(validationContext)"
                trim
              />
              <b-form-invalid-feedback>
                {{ validationContext.errors[0] }}
              </b-form-invalid-feedback>
            </b-form-group>
          </validation-provider>

          <validation-provider
            #default="validationContext"
            name="Курирующий заместитель директора"
            rules="required"
          >
            <b-form-group
              label="Курирующий заместитель директора"
              label-for="curator_id"
            >
              <v-select
                id="curator_id"
                v-model="project.curator_id"
                :options="curatorOptions"
                :reduce="val => val.value"
                :state="getValidationState(validationContext)"
                :clearable="false"
              />
              <b-form-invalid-feedback>
                {{ validationContext.errors[0] }}
              </b-form-invalid-feedback>
            </b-form-group>
          </validation-provider>

          <validation-provider
            #default="validationContext"
            name="Статус проекта"
            rules="required"
          >
            <b-form-group
              label="Статус проекта"
              label-for="status"
            >
              <v-select
                id="status"
                v-model="project.status"
                :options="statusOptions"
                :reduce="val => val.value"
                :state="getValidationState(validationContext)"
                :clearable="false"
              />
              <b-form-invalid-feedback>
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

<style lang="scss">
@import '~@core/scss/vue/libs/vue-select.scss';
@import '~@core/scss/vue/libs/vue-flatpicker.scss';

#add-new-user-sidebar {
  .vs__dropdown-menu {
    max-height: 400px !important;
  }
}
</style>
