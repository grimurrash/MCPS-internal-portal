<template>

  <div>

    <employee-import
      :is-import-employees-sidebar-active.sync="isImportEmployeesSidebarActive"
      @refetch-data="refetchData"
    />

    <employee-edit
      :is-edit-employee-sidebar-active.sync="isEditEmployeeSidebarActive"
      :employee-id="editEmployee"
      @refetch-data="refetchData"
    />

    <employees-list-filters
      :department-options="departmentOptions"
      :is-have-internal-code-options="isHaveInternalCodeOptions"
      :department-filter.sync="departmentFilter"
      :is-have-internal-code-filter.sync="isHaveInternalCodeFilter"
    />

    <!-- Table Container Card -->
    <b-card
      no-body
      class="mb-0"
    >
      <div class="m-2">

        <!-- Table Top -->
        <b-row>

          <!-- Per Page -->
          <b-col
            cols="12"
            md="6"
            class="d-flex align-items-center justify-content-start mb-1 mb-md-0"
          >
            <label>Показать</label>
            <v-select
              v-model="perPage"
              :dir="$store.state.appConfig.isRTL ? 'rtl' : 'ltr'"
              :options="perPageOptions"
              :clearable="false"
              class="per-page-selector d-inline-block mx-50"
            />
            <label>сотрудников</label>
          </b-col>

          <!-- Search -->
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
              <b-button
                variant="primary"
                @click="isImportEmployeesSidebarActive = true"
              >
                <span class="text-nowrap">Импорт сотрудников</span>
              </b-button>
            </div>
          </b-col>
        </b-row>

      </div>

      <b-table
        ref="employeesItems"
        class="position-relative"
        :items="fetchEmployees"
        responsive
        :fields="tableColumns"
        primary-key="id"
        :sort-by.sync="sortBy"
        show-empty
        empty-text="No matching records found"
        :sort-desc.sync="isSortDirDesc"
      >
        <!-- Column: visitControl -->
        <template #cell(visitControl)="data">
          <div class="text-nowrap">
            <b-form-checkbox
              :checked="data.item.visitControl"
              class="custom-control-primary"
              name="check-button"
              disabled
              switch
            >
              <span class="switch-icon-left">
                <feather-icon icon="BellIcon" />
              </span>
              <span class="switch-icon-right">
                <feather-icon icon="BellOffIcon" />
              </span>
            </b-form-checkbox>
          </div>
        </template>

        <!-- Column: Actions -->
        <template #cell(actions)="data">
          <b-dropdown
            variant="link"
            no-caret
            :right="$store.state.appConfig.isRTL"
          >
            <template #button-content>
              <feather-icon
                icon="MoreVerticalIcon"
                size="16"
                class="align-middle text-body"
              />
            </template>
            <b-dropdown-item @click.prevent="selectEditEmployee(data.item)">
              <feather-icon icon="EditIcon" />
              <span class="align-middle ml-50">Редактировать</span>
            </b-dropdown-item>
          </b-dropdown>
        </template>
      </b-table>
      <div class="mx-2 mb-2">
        <b-row>

          <b-col
            cols="12"
            sm="6"
            class="d-flex align-items-center justify-content-center justify-content-sm-start"
          >
            <span class="text-muted">Показано с {{ dataMeta.from }} по {{ dataMeta.to }} из {{
              dataMeta.of
            }} записей</span>
          </b-col>
          <!-- Pagination -->
          <b-col
            cols="12"
            sm="6"
            class="d-flex align-items-center justify-content-center justify-content-sm-end"
          >

            <b-pagination
              v-model="currentPage"
              :total-rows="totalEmployees"
              :per-page="perPage"
              first-number
              last-number
              class="mb-0 mt-1 mt-sm-0"
              prev-class="prev-item"
              next-class="next-item"
            >
              <template #prev-text>
                <feather-icon
                  icon="ChevronLeftIcon"
                  size="18"
                />
              </template>
              <template #next-text>
                <feather-icon
                  icon="ChevronRightIcon"
                  size="18"
                />
              </template>
            </b-pagination>

          </b-col>

        </b-row>
      </div>
    </b-card>
  </div>
</template>

<script>
import {
  BCard, BRow, BCol, BFormInput, BTable, BDropdown, BDropdownItem, BPagination, BButton, BFormCheckbox,
} from 'bootstrap-vue'
import axios from '@/libs/axios'
import ToastificationContent from '@core/components/toastification/ToastificationContent.vue'
import vSelect from 'vue-select'
import { ref, watch, computed } from '@vue/composition-api'
import { useToast } from 'vue-toastification/composition'
import EmployeeImport from '@/views/managements/employees/EmployeeImport.vue'
import EmployeesListFilters from '@/views/managements/employees/EmployeesListFilters.vue'
import EmployeeEdit from '@/views/managements/employees/EmployeeEdit.vue'

export default {
  components: {
    EmployeesListFilters,
    EmployeeImport,
    EmployeeEdit,

    BCard,
    BRow,
    BCol,
    BFormInput,
    BFormCheckbox,
    BTable,
    BDropdown,
    BButton,
    BDropdownItem,
    BPagination,

    vSelect,
  },
  setup() {
    const toast = useToast()

    const employeesItems = ref(null)

    // Table Handlers
    const tableColumns = [
      { key: 'fullName', sortable: true, label: 'ФИО' },
      { key: 'department', sortable: true, label: 'Отдел' },
      { key: 'workingPosition', sortable: true, label: 'Должность' },
      { key: 'roomNumber', sortable: true, label: 'Кабинет' },
      { key: 'internalCode', sortable: true, label: 'Внутренний номер' },
      { key: 'startOfTheDay', sortable: true, label: 'Начало рабочего дня' },
      { key: 'endOfTheDay', sortable: true, label: 'Конец рабочего дня' },
      { key: 'visitControl', sortable: true, label: 'Контроль посещения' },
      { key: 'actions', sortable: false, label: 'Действия' },
    ]
    const perPage = ref(10)
    const totalEmployees = ref(0)
    const currentPage = ref(1)
    const perPageOptions = [10, 25, 50, 100]
    const searchQuery = ref('')
    const sortBy = ref('fullName')
    const isSortDirDesc = ref(false)
    const isEditEmployeeSidebarActive = ref(false)
    const isImportEmployeesSidebarActive = ref(false)
    const departmentFilter = ref(null)
    const isHaveInternalCodeFilter = ref(null)

    const dataMeta = computed(() => {
      const localItemsCount = employeesItems.value ? employeesItems.value.localItems.length : 0
      return {
        from: perPage.value * (currentPage.value - 1) + (localItemsCount ? 1 : 0),
        to: perPage.value * (currentPage.value - 1) + localItemsCount,
        of: totalEmployees.value,
      }
    })

    const departmentOptions = ref([])
    axios.get('/management/departments/options')
      .then(response => {
        departmentOptions.value = response.data.departments.map(item => ({
          value: item.id,
          label: item.name,
        }))
      })
      .catch(() => {
        toast({
          component: ToastificationContent,
          position: 'top-right',
          props: {
            title: 'Не удалось загрузить отделы',
            variant: 'danger',
            icon: 'AlertCircleIcon',
          },
        })
      })

    const isHaveInternalCodeOptions = [
      {
        value: 1,
        label: 'Имеется',
      },
      {
        value: 2,
        label: 'Отсутствует',
      },
    ]

    const editEmployee = ref({
      id: '',
      fullName: '',
      internalCode: '',
      mobilePhone: '',
      roomNumber: '',
      workingPosition: '',
      startOfTheDay: '',
      endOfTheDay: '',
      visitControl: false,
    })
    const selectEditEmployee = item => {
      editEmployee.value = item
      isEditEmployeeSidebarActive.value = true
    }

    const refetchData = () => {
      employeesItems.value.refresh()
      isEditEmployeeSidebarActive.value = false
      isImportEmployeesSidebarActive.value = false
    }

    watch([currentPage, perPage, searchQuery, departmentFilter, isHaveInternalCodeFilter], () => {
      refetchData()
    })

    const fetchEmployees = (ctx, callback) => {
      axios.get('/management/employees', {
        params: {
          q: searchQuery.value,
          perPage: perPage.value,
          page: currentPage.value,
          sortBy: sortBy.value,
          sortDesc: isSortDirDesc.value,
          department_id: departmentFilter.value,
          isHaveInternalCode: isHaveInternalCodeFilter.value,
        },
      })
        .then(response => {
          const { employees, total } = response.data

          callback(employees)
          totalEmployees.value = total
        })
        .catch(() => {
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
    }

    return {
      tableColumns,
      editEmployee,
      employeesItems,
      perPage,
      totalEmployees,
      currentPage,
      perPageOptions,
      searchQuery,
      sortBy,
      isSortDirDesc,
      isEditEmployeeSidebarActive,
      isImportEmployeesSidebarActive,
      dataMeta,
      departmentFilter,
      isHaveInternalCodeFilter,
      departmentOptions,
      isHaveInternalCodeOptions,
      fetchEmployees,
      refetchData,
      selectEditEmployee,
    }
  },
}
</script>

<style lang="scss" scoped>
.per-page-selector {
  width: 90px;
}
</style>

<style lang="scss">
@import '~@core/scss/vue/libs/vue-select.scss';
</style>
