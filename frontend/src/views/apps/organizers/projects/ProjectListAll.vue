<script>
import {
  BButton,
  BCard,
  BCol,
  BFormInput,
  BRow,
  BCardHeader,
  BCardBody,
  BCardTitle,
  BCardText,
} from 'bootstrap-vue'
import { useToast } from 'vue-toastification/composition'
import axios from '@/libs/axios'
import ToastificationContent from '@core/components/toastification/ToastificationContent.vue'
import { ref, watch } from '@vue/composition-api'
import vSelect from 'vue-select'
import flatPickr from 'vue-flatpickr-component'
import AddProject from '@/views/apps/organizers/projects/AddProject.vue'
import ViewProject from '@/views/apps/organizers/projects/ViewProject.vue'
import EditProject from '@/views/apps/organizers/projects/EditProject.vue'

export default {
  components: {
    EditProject,
    ViewProject,
    BCard,
    BRow,
    BCol,
    BCardHeader,
    BCardBody,
    BCardTitle,
    BCardText,
    flatPickr,
    BFormInput,
    BButton,
    vSelect,
    AddProject,
  },

  setup() {
    const toast = useToast()
    const startDateFilter = ref(null)
    const endDateFilter = ref(null)
    const sortBy = ref('start_date')
    const sortDir = ref('desc')
    const searchQuery = ref(null)

    const sortOptions = [
      {
        value: 'start_date',
        label: 'Дата начала',
      },
      {
        value: 'end_date',
        label: 'Дата окончания',
      },
      {
        value: 'status',
        label: 'Статус',
      },
      {
        value: 'planned_coverage',
        label: 'Планируемый охват участников',
      },
      {
        value: 'actual_coverage',
        label: 'Фактический охват участников',
      },
    ]

    const sortDirsOptions = [
      {
        value: 'asc',
        label: 'По возрастанию',
      },
      {
        value: 'desc',
        label: 'По убыванию',
      },
    ]

    const statusOptions = [
      {
        value: 1,
        label: 'Создан',
      },
      {
        value: 2,
        label: 'В процессе',
      },
      {
        value: 3,
        label: 'Завершен',
      },
    ]

    const curatorOptions = ref([])
    const employeeOptions = ref([])
    axios.get('/organization-projects/curators')
      .then(res => {
        curatorOptions.value = res.data.map(item => ({
          value: item.id,
          label: item.fullName,
        }))
      })
      .catch(res => {
        console.log(res)
        toast({
          component: ToastificationContent,
          position: 'top-right',
          props: {
            title: 'Не удалось загрузить кураторов',
            variant: 'danger',
            icon: 'AlertCircleIcon',
          },
        })
      })

    axios.get('/management/users/options')
      .then(res => {
        employeeOptions.value = res.data.users.map(item => ({
          value: item.id,
          label: item.fullName,
        }))
      })
      .catch(res => {
        console.log(res)
        toast({
          component: ToastificationContent,
          position: 'top-right',
          props: {
            title: 'Не удалось загрузить сотрудников',
            variant: 'danger',
            icon: 'AlertCircleIcon',
          },
        })
      })

    const projectsList = ref([])
    const selectProject = ref({
      id: null,
      name: null,
      number: null,
      start_date: null,
      end_date: null,
      responsible_employee_id: null,
      description: null,
      metrics: null,
      planned_coverage: null,
      actual_coverage: null,
      curator_id: null,
      organizer_id: null,
      status: 1,
      created_at: null,
      updated_at: null,
    })

    const isAddProject = ref(false)
    const isEditProject = ref(false)
    const isViewProject = ref(false)

    const selectViewProject = item => {
      selectProject.value = item
      isEditProject.value = false
      isAddProject.value = false
      isViewProject.value = true
    }

    const selectEditProject = item => {
      selectProject.value = item
      isViewProject.value = false
      isAddProject.value = false
      isEditProject.value = true
    }

    const refreshData = () => {
      axios.get('/organization-projects/all', {
        params: {
          sort_by: sortBy.value,
          sort_dir: sortDir.value,
          start_date: startDateFilter.value,
          end_date: endDateFilter.value,
          search_query: searchQuery.value,
        },
      })
        .then(res => {
          projectsList.value = res.data
        }).catch(() => {
          toast({
            component: ToastificationContent,
            position: 'top-right',
            props: {
              title: 'Не удалось загрузить проекты',
              variant: 'danger',
              icon: 'AlertCircleIcon',
            },
          })
        })
      isViewProject.value = false
      isEditProject.value = false
      isAddProject.value = false
    }

    watch([startDateFilter, endDateFilter, sortBy, sortDir, searchQuery], () => {
      refreshData()
    })

    refreshData()
    return {
      startDateFilter,
      endDateFilter,
      sortBy,
      sortDir,
      searchQuery,

      sortOptions,
      sortDirsOptions,
      curatorOptions,
      employeeOptions,
      statusOptions,

      projectsList,
      selectProject,
      isViewProject,
      isEditProject,
      isAddProject,

      selectViewProject,
      selectEditProject,
      refreshData,
    }
  },
}
</script>

<template>
  <div>

    <edit-project
      :status-options="statusOptions"
      :employee-options="employeeOptions"
      :curator-options="curatorOptions"
      :select-project="selectProject"
      :is-edit-project.sync="isEditProject"
      @refresh-data="refreshData"
    />

    <add-project
      :status-options="statusOptions"
      :employee-options="employeeOptions"
      :curator-options="curatorOptions"
      :is-add-project.sync="isAddProject"
      @refresh-data="refreshData"
    />

    <view-project
      :status-options="statusOptions"
      :employee-options="employeeOptions"
      :curator-options="curatorOptions"
      :select-project="selectProject"
      :is-view-project.sync="isViewProject"
      @refresh-data="refreshData"
    />

    <b-card no-body>
      <b-card-header class="pb-50">
        <h5>
          Фильтры
        </h5>
      </b-card-header>
      <b-card-body>
        <b-row>
          <b-col
            cols="12"
            md="3"
            class="mb-md-0 mb-2"
          >
            <label>Дата начала от</label>
            <flat-pickr
              v-model="startDateFilter"
              class="form-control"
              placeholder="Дата"
              :config="{ dateFormat: 'Y-m-d'}"
            />
          </b-col>
          <b-col
            cols="12"
            md="3"
            class="mb-md-0 mb-2"
          >
            <label>Дата окончания до</label>
            <flat-pickr
              v-model="endDateFilter"
              class="form-control"
              placeholder="Дата"
              :config="{ dateFormat: 'Y-m-d'}"
            />
          </b-col>
        </b-row>
      </b-card-body>
    </b-card>

    <b-card no-body>
      <b-card-body>
        <b-row>
          <b-col
            cols="12"
            md="3"
            class="mb-md-0 mb-2"
          >
            <v-select
              v-model="sortBy"
              :options="sortOptions"
              class="w-100"
              :reduce="val => val.value"
            />
          </b-col>

          <b-col
            cols="12"
            md="2"
            class="mb-md-0 mb-2"
          >
            <v-select
              v-model="sortDir"
              :options="sortDirsOptions"
              class="w-100"
              :reduce="val => val.value"
            />
          </b-col>
          <b-col
            cols="12"
            md="1"
            class="mb-md-0 mb-2"
          />
          <b-col
            cols="12"
            md="6"
          >
            <div class="d-flex align-items-center justify-content-end">
              <b-form-input
                v-model="searchQuery"
                class="d-inline-block mr-1"
                placeholder="Поиск..."
              />
              <!--              <b-button-->
              <!--                variant="primary"-->
              <!--                @click="isAddProject = true"-->
              <!--              >-->
              <!--                <span class="text-nowrap">Добавить проект</span>-->
              <!--              </b-button>-->
            </div>
          </b-col>
        </b-row>
      </b-card-body>
    </b-card>

    <b-row>
      <b-col
        v-for="project in projectsList"
        :key="project.id"
        cols="12"
        md="3"
      >
        <b-card no-body>
          <b-card-header>
            <b-card-title><h4>{{ project.name }}</h4></b-card-title>
          </b-card-header>
          <b-card-text class="ml-2 mr-2">
            {{ 'Статус: ' + statusOptions.find((item) => item.value === project.status).label }}
          </b-card-text>
          <b-card-text class="ml-2 mr-2">
            {{ 'Куратор: ' + project.curator_name }}
          </b-card-text>
          <b-card-text class="ml-2 mr-2">
            {{ 'Организатор: ' + project.organizer_name }}
          </b-card-text>

          <b-card-text class="d-flex justify-content-center m-2">
            <b-button
              variant="success"
              class="mr-2"
              type="submit"
              @click="selectViewProject(project)"
            >
              Подробнее
            </b-button>
            <!--            <b-button-->
            <!--              variant="info"-->
            <!--              class="mr-2"-->
            <!--              type="submit"-->
            <!--              @click="selectEditProject(project)"-->
            <!--            >-->
            <!--              Редактировать-->
            <!--            </b-button>-->
          </b-card-text>
        </b-card>
      </b-col>

    </b-row>

  </div>
</template>

<style lang="scss">
@import '~@core/scss/vue/libs/vue-select.scss';
@import '~@core/scss/vue/libs/vue-flatpicker.scss';
</style>
