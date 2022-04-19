<template>

  <div>

    <help-desk-list-filters
      :categories-options="categoriesOptions"
      :execution-addresses-options="executionAddressesOptions"
      :executors-options="executorsOptions"
      :statuses-options="statusesOptions"
      :category-filter.sync="categoryFilter"
      :execution-address-filter.sync="executionAddressFilter"
      :status-filter.sync="statusFilter"
      :executor-filter.sync="executorFilter"
      :selected-date.sync="selectedDate"
    />

    <help-desk-edit
      :edit-item="editItemData"
      :statuses-options="statusesOptions"
      :executors-options="executorsOptions"
      :is-edit-sidebar-active.sync="isEditSidebarActive"
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
            <label>заявок</label>
          </b-col>

          <b-col
            cols="12"
            md="8"
            class="d-flex justify-content-end"
          >
            <b-button
              variant="outline-primary"
              class="btn-icon"
              @click.prevent="refetchData"
            >
              <feather-icon icon="LoaderIcon" />
              <span class="align-middle">Обновить</span>
            </b-button>
          </b-col>
        </b-row>

      </div>

      <b-table
        ref="items"
        class="position-relative"
        :items="fetchItemList"
        responsive
        :fields="tableColumns"
        primary-key="id"
        :sort-by.sync="sortBy"
        show-empty
        empty-text="No matching records found"
        :sort-desc.sync="isSortDirDesc"
      >

        <!-- Column: СreateUser -->
        <template #cell(employee)="data">
          <span class="font-weight-bold d-block text-nowrap">
            {{
              (data.item.employee !== null
                ? data.item.employee.fullName + (data.item.employee_info !== null ? ` (${data.item.employee_info})` : '')
                : data.item.employee_info)
            }}
          </span>
        </template>

        <template #cell(executor)="data">
          <span
            v-if="data.item.executor"
            class="font-weight-bold d-block text-nowrap"
          >
            {{ data.item.executor.fullName }}
          </span>
          <b-button
            v-else
            variant="warning"
            class="btn-icon"
            @click.prevent="acceptHelpDeskRequest(data.item.id)"
          >
            <feather-icon icon="UserPlusIcon" />
            <span class="align-middle">Принять</span>
          </b-button>
        </template>

        <!-- Column: Actions -->
        <template #cell(actions)="data">
          <!-- Просмотреть -->
          <b-button
            v-b-tooltip.hover.v-info
            title="Подробная информация"
            variant="info"
            class="btn-icon"
            @click.prevent="selectEditSidebar(data.item.id)"
          >
            <feather-icon icon="FileTextIcon" />
          </b-button>

          <!-- Выполнено -->
          <b-button
            v-if="data.item.status_id === 0 || data.item.status_id === 1"
            v-b-tooltip.hover.v-success
            variant="success"
            class="btn-icon"
            title="Выполнено"
            @click.prevent="changeHelpDeskRequestStatus(data.item.id, 2)"
          >
            <feather-icon icon="CheckCircleIcon" />
          </b-button>
          <!-- Не выполнено -->
          <b-button
            v-if="data.item.status_id === 0 || data.item.status_id === 1"
            v-b-tooltip.hover.v-danger
            variant="danger"
            class="btn-icon"
            title="Не выполнено"
            @click.prevent="changeHelpDeskRequestStatus(data.item.id, 3)"
          >
            <feather-icon icon="XIcon" />
          </b-button>
          <!-- Ошибочная заявка -->
          <b-button
            v-if="data.item.status_id === 0 || data.item.status_id === 1"
            v-b-tooltip.hover.v-danger
            variant="danger"
            class="btn-icon"
            title="Ошибочная заявка"
            @click.prevent="changeHelpDeskRequestStatus(data.item.id, 4)"
          >
            <feather-icon icon="ArchiveIcon" />
          </b-button>
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
              :total-rows="totalCount"
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
  BButton, BCard, BCol, BPagination, BRow, BTable, VBTooltip,
} from 'bootstrap-vue'
import axios from '@/libs/axios'
import ToastificationContent from '@core/components/toastification/ToastificationContent.vue'
import vSelect from 'vue-select'
import { computed, ref, watch } from '@vue/composition-api'
import { useToast } from 'vue-toastification/composition'
import HelpDeskListFilters from '@/views/managements/helpdesk/HelpDeskListFilters.vue'
import HelpDeskEdit from '@/views/managements/helpdesk/HelpDeskEdit.vue'

export default {
  components: {
    HelpDeskListFilters,
    HelpDeskEdit,
    BCard,
    BRow,
    BCol,
    BTable,
    BButton,
    BPagination,
    vSelect,
  },
  directives: {
    'b-tooltip': VBTooltip,
  },
  setup() {
    const toast = useToast()

    const items = ref(null)

    // Table Handlers
    const tableColumns = [
      { key: 'id', sortable: true, label: '#' },
      { key: 'employee', sortable: true, label: 'Заявитель' },
      { key: 'execution_address', sortable: true, label: 'Адрес' },
      { key: 'description', sortable: true, label: 'Описание' },
      { key: 'executor', sortable: true, label: 'Исполнитель' },
      { key: 'status', sortable: true, label: 'Статус' },
      { key: 'actions', sortable: false, label: 'Действия' },
    ]
    const perPage = ref(10)
    const totalCount = ref(0)
    const currentPage = ref(1)
    const perPageOptions = [10, 25, 50, 100]
    const sortBy = ref('id')
    const isSortDirDesc = ref(true)
    const isEditSidebarActive = ref(false)

    const dataMeta = computed(() => {
      const localItemsCount = items.value ? items.value.localItems.length : 0
      return {
        from: perPage.value * (currentPage.value - 1) + (localItemsCount ? 1 : 0),
        to: perPage.value * (currentPage.value - 1) + localItemsCount,
        of: totalCount.value,
      }
    })

    const executionAddressFilter = ref(null)
    const categoryFilter = ref(null)
    const statusFilter = ref(null)
    const executorFilter = ref(null)
    const selectedDate = ref('')
    const executionAddressesOptions = ref([])
    const categoriesOptions = ref([])
    const statusesOptions = ref([])
    const executorsOptions = ref([])

    axios.get('/helpdesk/tableOptions').then(response => {
      executionAddressesOptions.value = response.data.addresses.map(item => ({
        value: item.id,
        label: item.name,
      }))
      categoriesOptions.value = response.data.categories.map(item => ({
        value: item.id,
        label: item.name,
      }))
      statusesOptions.value = response.data.statuses.map(item => ({
        value: item.id,
        label: item.name,
      }))
      executorsOptions.value = response.data.executors.map(item => ({
        value: item.id,
        label: item.name,
      }))
    }).catch(() => {
      toast({
        component: ToastificationContent,
        position: 'top-right',
        props: {
          title: 'Не удалось загрузить выпадающие списки',
          variant: 'danger',
          icon: 'AlertCircleIcon',
        },
      })
    })

    const editItemData = ref({
      id: 0,
    })

    const refetchData = () => {
      items.value.refresh()
      isEditSidebarActive.value = false
    }

    watch([currentPage, perPage, executionAddressFilter, categoryFilter, statusFilter, executorFilter, selectedDate],
      () => {
        refetchData()
      })

    const changeHelpDeskRequestStatus = (itemId, status) => {
      axios.post(`/helpdesk/${itemId}/status`, {
        status_id: status,
      }).then(() => {
        refetchData()
        toast({
          component: ToastificationContent,
          position: 'top-right',
          props: {
            title: 'Статус изменен',
            variant: 'success',
            icon: 'CheckCircleIcon',
          },
        })
      }).catch(() => {
        toast({
          component: ToastificationContent,
          position: 'top-right',
          props: {
            title: 'Ошибка при получении информации о заявке. Обратитесь к технической поддержке.',
            variant: 'danger',
            icon: 'AlertCircleIcon',
          },
        })
      })
    }

    const acceptHelpDeskRequest = itemId => {
      axios.post(`/helpdesk/${itemId}/executor`).then(() => {
        refetchData()
        toast({
          component: ToastificationContent,
          position: 'top-right',
          props: {
            title: 'Заявка принята',
            variant: 'success',
            icon: 'CheckCircleIcon',
          },
        })
      }).catch(() => {
        toast({
          component: ToastificationContent,
          position: 'top-right',
          props: {
            title: 'Ошибка при получении информации о заявке. Обратитесь к технической поддержке.',
            variant: 'danger',
            icon: 'AlertCircleIcon',
          },
        })
      })
    }

    const selectEditSidebar = itemId => {
      axios.get(`/helpdesk/${itemId}`).then(res => {
        editItemData.value = res.data
        isEditSidebarActive.value = true
      }).catch(() => {
        toast({
          component: ToastificationContent,
          position: 'top-right',
          props: {
            title: 'Ошибка при получении информации о заявке. Обратитесь к технической поддержке.',
            variant: 'danger',
            icon: 'AlertCircleIcon',
          },
        })
      })
    }

    const fetchItemList = (ctx, callback) => {
      axios.get('/helpdesk', {
        params: {
          perPage: perPage.value,
          page: currentPage.value,
          sortBy: sortBy.value,
          executionAddressFilter: executionAddressFilter.value,
          categoryFilter: categoryFilter.value,
          statusFilter: statusFilter.value,
          executorFilter: executorFilter.value,
          start_date: selectedDate.value.split(' to ')[0],
          end_date: selectedDate.value.split(' to ')[1],
        },
      }).then(response => {
        const { list, total } = response.data

        callback(list)
        totalCount.value = total
      }).catch(() => {
        toast({
          component: ToastificationContent,
          position: 'top-right',
          props: {
            title: 'Не удалось загрузить заявки',
            variant: 'danger',
            icon: 'AlertCircleIcon',
          },
        })
      })
    }

    return {
      tableColumns,
      editItemData,
      items,
      perPage,
      totalCount,
      currentPage,
      perPageOptions,
      sortBy,
      isSortDirDesc,
      isEditSidebarActive,
      dataMeta,

      executionAddressFilter,
      categoryFilter,
      statusFilter,
      executorFilter,
      selectedDate,

      executionAddressesOptions,
      categoriesOptions,
      statusesOptions,
      executorsOptions,

      fetchItemList,
      selectEditSidebar,
      changeHelpDeskRequestStatus,
      acceptHelpDeskRequest,
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
@import '@core/scss/vue/libs/tour.scss';
</style>
