<template>

  <div>
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
            md="3"
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

          <b-col
            cols="12"
            md="9"
          >
            <b-row class="w-100 d-flex align-items-end">
              <b-col
                cols="12"
                md="6"
                class="mb-md-0 mb-2"
              >
                <v-select
                  v-model="employeeFilter"
                  :dir="$store.state.appConfig.isRTL ? 'rtl' : 'ltr'"
                  :options="employeeOptions"
                  placeholder="Выбрать сотрудника"
                  class="w-100 mr-2"
                  :reduce="val => val.value"
                  @input="(val) => $emit('update:employeeFilter', val)"
                />
              </b-col>
              <b-col
                cols="12"
                md="4"
                class="mb-md-0 mb-2"
              >
                <flat-pickr
                  v-model="selectDate"
                  class="form-control"
                  placeholder="Дата"
                  :config="{ mode: 'range', dateFormat: 'Y-m-d'}"
                />
              </b-col>
              <b-col
                cols="12"
                md="2"
                class="mb-md-0 mb-2"
              >
                <div>
                  <b-card-text class="mb-0">
                    Опоздание
                  </b-card-text>
                  <b-form-checkbox
                    v-model="isLateArrival"
                    class="custom-control-warning"
                    name="check-button"
                    switch
                  >
                    <span class="switch-icon-left">
                      <feather-icon icon="ShieldIcon" />
                    </span>
                    <span class="switch-icon-right">
                      <feather-icon icon="ShieldOffIcon" />
                    </span>
                  </b-form-checkbox>
                </div>
              </b-col>
            </b-row>
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
  BCard, BRow, BCol, BTable, BPagination, BFormCheckbox, BCardText,
} from 'bootstrap-vue'
import axios from '@/libs/axios'
import ToastificationContent from '@core/components/toastification/ToastificationContent.vue'
import vSelect from 'vue-select'
import { ref, watch, computed } from '@vue/composition-api'
import { useToast } from 'vue-toastification/composition'
import flatPickr from 'vue-flatpickr-component'

export default {
  components: {
    BCard,
    BCardText,
    BRow,
    BCol,
    BTable,
    BPagination,
    BFormCheckbox,
    flatPickr,
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
    const employeeFilter = ref(null)
    const todayDate = new Date()
    const selectDate = ref(`${todayDate.getFullYear()}-${(todayDate.getMonth() + 1)}-${todayDate.getDate() - 1}`)
    const isEditVisitEventsSidebarActive = ref(false)
    const isLateArrival = ref(false)

    const dataMeta = computed(() => {
      const localItemsCount = eventsItems.value ? eventsItems.value.localItems.length : 0
      return {
        from: perPage.value * (currentPage.value - 1) + (localItemsCount ? 1 : 0),
        to: perPage.value * (currentPage.value - 1) + localItemsCount,
        of: totalEvents.value,
      }
    })

    const userData = JSON.parse(localStorage.getItem('userData'))
    const departmentFilter = ref(null)
    const employeeOptions = ref([])

    const refetchData = () => {
      isEditVisitEventsSidebarActive.value = false
      eventsItems.value.refresh()
    }

    watch([currentPage, perPage, searchQuery, departmentFilter, employeeFilter, selectDate, isLateArrival], () => {
      refetchData()
    })

    axios.get(`/management/employees/optionsByUser/${userData.id}`)
      .then(response => {
        departmentFilter.value = response.data.departmentId
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
            title: 'Не удалось найти отдел',
            variant: 'danger',
            icon: 'AlertCircleIcon',
          },
        })
      })

    const fetchVisitEvents = (ctx, callback) => {
      if (departmentFilter.value !== null) {
        axios.get('/management/visit-events', {
          params: {
            q: searchQuery.value,
            perPage: perPage.value,
            page: currentPage.value,
            sortBy: sortBy.value,
            sortDesc: isSortDirDesc.value,
            department_id: departmentFilter.value,
            employee_id: employeeFilter.value,
            start_date: selectDate.value === null ? '' : selectDate.value.split(' to ')[0],
            end_date: selectDate.value === null ? '' : selectDate.value.split(' to ')[1],
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
      } else {
        callback([])
      }
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
      employeeOptions,
      isEditVisitEventsSidebarActive,
      editVisitEvent,
      isLateArrival,
      rowClass,
      fetchVisitEvents,
      refetchData,
      selectEditVisitEvent,
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
@import '~@core/scss/vue/libs/vue-flatpicker.scss';
</style>
