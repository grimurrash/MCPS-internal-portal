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
  BCard, BRow, BCol, BFormInput, BTable, BPagination,
} from 'bootstrap-vue'
import axios from '@/libs/axios'
import ToastificationContent from '@core/components/toastification/ToastificationContent.vue'
import vSelect from 'vue-select'
import { ref, watch, computed } from '@vue/composition-api'
import { useToast } from 'vue-toastification/composition'

export default {
  components: {
    BCard,
    BRow,
    BCol,
    BFormInput,
    BTable,
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
      { key: 'mobilePhone', sortable: true, label: 'Мобильный телефон' },
      { key: 'email', sortable: true, label: 'Email' },
    ]
    const perPage = ref(25)
    const totalEmployees = ref(0)
    const currentPage = ref(1)
    const perPageOptions = [10, 25, 50, 100]
    const searchQuery = ref('')
    const sortBy = ref('fullName')
    const isSortDirDesc = ref(false)

    const dataMeta = computed(() => {
      const localItemsCount = employeesItems.value ? employeesItems.value.localItems.length : 0
      return {
        from: perPage.value * (currentPage.value - 1) + (localItemsCount ? 1 : 0),
        to: perPage.value * (currentPage.value - 1) + localItemsCount,
        of: totalEmployees.value,
      }
    })

    const refetchData = () => {
      employeesItems.value.refresh()
    }

    watch([currentPage, perPage, searchQuery], () => {
      refetchData()
    })

    const fetchEmployees = (ctx, callback) => {
      axios.get('/management/employees/phoneBook', {
        params: {
          q: searchQuery.value,
          perPage: perPage.value,
          page: currentPage.value,
          sortBy: sortBy.value,
          sortDesc: isSortDirDesc.value,
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
      employeesItems,
      perPage,
      totalEmployees,
      currentPage,
      perPageOptions,
      searchQuery,
      sortBy,
      isSortDirDesc,
      dataMeta,
      fetchEmployees,
      refetchData,
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
