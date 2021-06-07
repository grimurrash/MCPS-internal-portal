<template>

  <div>

    <department-edit
      :is-edit-department-sidebar-active.sync="isEditDepartmentSidebarActive"
      :department-id="editDepartment"
      @refetch-data="refetchData"
    />

    <department-import
      :is-import-departments-sidebar-active="isImportDepartmentsSidebarActive"
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
            <label>отделов</label>
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
                @click="isImportDepartmentsSidebarActive = true"
              >
                <span class="text-nowrap">Импорт отделов</span>
              </b-button>
            </div>
          </b-col>
        </b-row>

      </div>

      <b-table
        ref="departmentsItems"
        class="position-relative"
        :items="fetchDepartments"
        responsive
        :fields="tableColumns"
        primary-key="id"
        :sort-by.sync="sortBy"
        show-empty
        empty-text="No matching records found"
        :sort-desc.sync="isSortDirDesc"
      >
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
            <b-dropdown-item @click.prevent="selectEditDepartment(data.item)">
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
              :total-rows="totalDepartments"
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

<script>import {
  BCard, BRow, BCol, BFormInput, BTable, BDropdown, BDropdownItem, BPagination, BButton,
} from 'bootstrap-vue'
import axios from '@/libs/axios'
import ToastificationContent from '@core/components/toastification/ToastificationContent.vue'
import vSelect from 'vue-select'
import { ref, watch, computed } from '@vue/composition-api'
import { useToast } from 'vue-toastification/composition'
import DepartmentEdit from '@/views/managements/departments/DepartmentEdit.vue'
import DepartmentImport from '@/views/managements/departments/DepartmentImport'

export default {
  components: {
    BCard,
    BRow,
    BCol,
    BFormInput,
    BTable,
    BDropdown,
    BButton,
    BDropdownItem,
    BPagination,
    DepartmentImport,
    DepartmentEdit,
    vSelect,
  },
  setup() {
    const toast = useToast()

    const departmentsItems = ref(null)

    // Table Handlers
    const tableColumns = [
      { key: 'name', sortable: true, label: 'Наименование' },
      { key: 'parent', sortable: true, label: 'Родительское отдел' },
      { key: 'head', sortable: true, label: 'Руководитель' },
      { key: 'employee_count', sortable: true, label: 'Кол-во сотрудников' },
      { key: 'actions', sortable: false, label: 'Действия' },
    ]
    const perPage = ref(10)
    const totalDepartments = ref(0)
    const currentPage = ref(1)
    const perPageOptions = [10, 25, 50, 100]
    const searchQuery = ref('')
    const sortBy = ref('id')
    const isSortDirDesc = ref(true)
    const isEditDepartmentSidebarActive = ref(false)
    const isImportDepartmentsSidebarActive = ref(false)

    const dataMeta = computed(() => {
      const localItemsCount = departmentsItems.value ? departmentsItems.value.localItems.length : 0
      return {
        from: perPage.value * (currentPage.value - 1) + (localItemsCount ? 1 : 0),
        to: perPage.value * (currentPage.value - 1) + localItemsCount,
        of: totalDepartments.value,
      }
    })

    const editDepartment = ref({
      id: '',
      name: '',
      parent_id: '',
    })
    const selectEditDepartment = item => {
      editDepartment.value = item
      isEditDepartmentSidebarActive.value = true
    }

    const refetchData = () => {
      departmentsItems.value.refresh()
      isEditDepartmentSidebarActive.value = false
      isImportDepartmentsSidebarActive.value = false
    }

    watch([currentPage, perPage, searchQuery], () => {
      refetchData()
    })

    const fetchDepartments = (ctx, callback) => {
      axios.get('/management/departments', {
        params: {
          q: searchQuery.value,
          perPage: perPage.value,
          page: currentPage.value,
          sortBy: sortBy.value,
          sortDesc: isSortDirDesc.value,
        },
      })
        .then(response => {
          const { departments, total } = response.data

          callback(departments)
          totalDepartments.value = total
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
    }

    return {
      tableColumns,
      editDepartment,
      departmentsItems,
      perPage,
      totalDepartments,
      currentPage,
      perPageOptions,
      searchQuery,
      sortBy,
      isSortDirDesc,
      isEditDepartmentSidebarActive,
      isImportDepartmentsSidebarActive,
      dataMeta,

      fetchDepartments,
      refetchData,
      selectEditDepartment,
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
