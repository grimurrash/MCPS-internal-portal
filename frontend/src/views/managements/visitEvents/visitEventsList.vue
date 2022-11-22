<template>

  <div>

    <visit-events-list-filters
      :department-filter.sync="departmentFilter"
      :employee-filter.sync="employeeFilter"
      :select-data.sync="selectDate"
      :is-late-arrival.sync="isLateArrival"
      :department-options="departmentOptions"
      :employee-options="employeeOptions"
    />

    <visit-event-import
      :is-import-visit-events-sidebar-active.sync="isImportVisitEventsSidebarActive"
      @refetch-data="refetchData"
    />

    <visit-event-edit
      :is-edit-visit-events-sidebar-active.sync="isEditVisitEventsSidebarActive"
      :edit-event="editVisitEvent"
      @refetch-data="refetchData"
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
            md="4"
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
            <label>строк</label>
          </b-col>
          <!-- Search -->
          <b-col
            cols="12"
            md="8"
          >
            <div class="d-flex align-items-center justify-content-end">
              <b-form-input
                v-model="searchQuery"
                class="d-inline-block mr-1"
                placeholder="Поиск..."
              />
              <b-button
                variant="primary"
                class="mr-1"
                @click="isImportVisitEventsSidebarActive = true"
              >
                <span class="text-nowrap">Импорт события</span>
              </b-button>
              <b-button
                variant="primary"
                @click="exportVisitEvents"
              >
                <span class="text-nowrap">Экспорт</span>
              </b-button>
            </div>
          </b-col>
        </b-row>

      </div>

      <b-table
        ref="eventsItems"
        class="position-relative"
        :items="fetchVisitEvents"
        responsive
        :fields="tableColumns"
        primary-key="id"
        :sort-by.sync="sortBy"
        show-empty
        empty-text="No matching records found"
        :sort-desc.sync="isSortDirDesc"
        :tbody-tr-class="rowClass"
      />

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
              :total-rows="totalEvents"
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
  BCard, BRow, BCol, BFormInput, BTable, BPagination, BButton,
} from 'bootstrap-vue'
import axios from '@/libs/axios'
import ToastificationContent from '@core/components/toastification/ToastificationContent.vue'
import vSelect from 'vue-select'
import { ref, watch, computed } from '@vue/composition-api'
import { useToast } from 'vue-toastification/composition'
import VisitEventsListFilters from '@/views/managements/visitEvents/visitEventsListFilters.vue'
import VisitEventImport from '@/views/managements/visitEvents/visitEventImport.vue'
import VisitEventEdit from '@/views/managements/visitEvents/visitEventEdit.vue'
// eslint-disable-next-line import/no-extraneous-dependencies
import FileDownload from 'js-file-download'

export default {
  components: {
    VisitEventEdit,
    VisitEventImport,
    VisitEventsListFilters,
    BCard,
    BRow,
    BCol,
    BFormInput,
    BTable,
    BButton,
    BPagination,

    vSelect,
  },
  setup() {
    const toast = useToast()

    const eventsItems = ref(null)

    // Table Handlers
    const tableColumns = [
      { key: 'employee', sortable: true, label: 'ФИО' },
      { key: 'department', sortable: true, label: 'Отдел' },
      { key: 'date', sortable: true, label: 'Дата' },
      { key: 'startOfTheDay', sortable: true, label: 'Начало рабочего дня' },
      { key: 'endOfTheDay', sortable: true, label: 'Конец рабочего дня' },
      { key: 'entrance_time', sortable: true, label: 'Приход' },
      { key: 'exit_time', sortable: true, label: 'Уход' },
    ]
    const perPage = ref(10)
    const totalEvents = ref(0)
    const currentPage = ref(1)
    const perPageOptions = [10, 25, 50, 100]
    const searchQuery = ref('')
    const sortBy = ref('employee')
    const isSortDirDesc = ref(false)
    const departmentFilter = ref(null)
    const employeeFilter = ref(null)
    const todayDate = new Date()
    const selectDate = ref(`${todayDate.getFullYear()}-${(todayDate.getMonth() + 1)}-${todayDate.getDate() - 1}`)
    const isLateArrival = ref(false)

    const isImportVisitEventsSidebarActive = ref(false)
    const isEditVisitEventsSidebarActive = ref(false)

    const dataMeta = computed(() => {
      const localItemsCount = eventsItems.value ? eventsItems.value.localItems.length : 0
      return {
        from: perPage.value * (currentPage.value - 1) + (localItemsCount ? 1 : 0),
        to: perPage.value * (currentPage.value - 1) + localItemsCount,
        of: totalEvents.value,
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

    const employeeOptions = ref([])
    axios.get('/management/employees/options')
      .then(response => {
        employeeOptions.value = response.data.employees.map(item => ({
          value: item.id,
          label: item.fullName,
        }))
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

    const refetchData = () => {
      isEditVisitEventsSidebarActive.value = false
      isImportVisitEventsSidebarActive.value = false
      eventsItems.value.refresh()
    }

    watch([currentPage, perPage, searchQuery, departmentFilter, employeeFilter, selectDate, isLateArrival], () => {
      refetchData()
    })

    const fetchVisitEvents = (ctx, callback) => {
      axios.get('/management/visit-events/attendance', {
        params: {
          q: searchQuery.value,
          perPage: perPage.value,
          page: currentPage.value,
          sortBy: sortBy.value,
          sortDesc: isSortDirDesc.value,
          department_id: departmentFilter.value,
          employee_id: employeeFilter.value,
          start_date: selectDate.value.split(' to ')[0],
          end_date: selectDate.value.split(' to ')[1],
          isLateArrival: isLateArrival.value,
        },
      })
        .then(response => {
          const { events, total } = response.data

          callback(events)
          totalEvents.value = total
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

    const exportVisitEvents = () => {
      axios.post('/management/visit-events/attendance/export', {
        q: searchQuery.value,
        perPage: perPage.value,
        page: currentPage.value,
        sortBy: sortBy.value,
        sortDesc: isSortDirDesc.value,
        department_id: departmentFilter.value,
        employee_id: employeeFilter.value,
        start_date: selectDate.value.split(' to ')[0],
        end_date: selectDate.value.split(' to ')[1],
        isLateArrival: isLateArrival.value,
      }, {
        responseType: 'blob', // Important
      })
        .then(response => {
          FileDownload(response.data, 'Экспорт событий (приход-уход).xlsx')
        })
        .catch(() => {
          toast({
            component: ToastificationContent,
            position: 'top-right',
            props: {
              title: 'Не удалось экспортировать события',
              variant: 'danger',
              icon: 'AlertCircleIcon',
            },
          })
        })
    }

    const editVisitEvent = ref({
      id: '',
      note: '',
      date: '',
    })

    const selectEditVisitEvent = item => {
      editVisitEvent.value = item
      isEditVisitEventsSidebarActive.value = true
    }

    const rowClass = (item, type) => {
      const colorClass = 'table-warning'
      if (!item || type !== 'row') {
        return
      }
      // eslint-disable-next-line consistent-return
      if (item.isLateArrival) return colorClass
    }

    return {
      tableColumns,
      eventsItems,
      perPage,
      totalEvents,
      currentPage,
      perPageOptions,
      searchQuery,
      sortBy,
      isSortDirDesc,
      dataMeta,
      departmentFilter,
      employeeFilter,
      selectDate,
      departmentOptions,
      employeeOptions,
      isImportVisitEventsSidebarActive,
      isEditVisitEventsSidebarActive,
      editVisitEvent,
      isLateArrival,
      rowClass,
      fetchVisitEvents,
      refetchData,
      selectEditVisitEvent,
      exportVisitEvents,
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
