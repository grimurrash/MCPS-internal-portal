<template>
  <div>
    <template v-if="isManager">
      <file-storage-add-new
        :is-add-new-item-sidebar-active.sync="isAddNewItemSidebarActive"
        @refetch-data="refetchData"
      />
    </template>

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
            <label>файлов</label>
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
                v-if="isManager"
                variant="primary"
                @click="isAddNewItemSidebarActive = true"
              >
                <span class="text-nowrap">Добавить новый файл</span>
              </b-button>
            </div>
          </b-col>
        </b-row>

      </div>

      <b-table
        ref="tableItems"
        class="position-relative"
        :items="fetchItems"
        responsive
        :fields="tableColumns"
        primary-key="id"
        :sort-by.sync="sortBy"
        show-empty
        empty-text="No matching records found"
        :sort-desc.sync="isSortDirDesc"
      >
        <!-- Column: File -->
        <template #cell(filePath)="data">
          <a
            class="text-nowrap alert-link mr-2"
            :href="data.item.filePath"
          >
            <feather-icon
              icon="DownloadIcon"
              size="18"
              class="mr-25 text-primary"
            />
            <span class="align-text-top text-capitalize">Скачать</span>
          </a>
        </template>

        <template #cell(copy)="data">
          <a
            class="text-nowrap alert-link"
            href="#"
            @click.prevent="doCopy(data.item.filePath)"
          >
            <feather-icon
              icon="CopyIcon"
              size="18"
              class="mr-25 text-primary"
            />
            <span class="align-text-top text-capitalize">Скопировать ссылку</span>
          </a>
        </template>

        <template
          v-if="isManager"
          #cell(delete)="data"
        >
          <a
            class="text-nowrap alert-link"
            href="#"
            @click.prevent="fetchDeleteItem(data.item.id)"
          >
            <feather-icon
              icon="TrashIcon"
              size="18"
              class="mr-25 text-danger"
            />
            <span class="align-text-top text-danger text-capitalize">Удалить</span>
          </a>
        </template>
      </b-table>
      <div class="mx-2 mb-2">
        <b-row>
          <b-col
            cols="12"
            sm="6"
            class="d-flex align-items-center justify-content-center justify-content-sm-start"
          >
            <span class="text-muted">Показано с {{ dataMeta.from }} по {{ dataMeta.to }} из {{ dataMeta.of }} записей</span>
          </b-col>
          <!-- Pagination -->
          <b-col
            cols="12"
            sm="6"
            class="d-flex align-items-center justify-content-center justify-content-sm-end"
          >

            <b-pagination
              v-model="currentPage"
              :total-rows="totalRows"
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
import FileStorageAddNew from '@/views/apps/file-storage/FileStorageAddNew.vue'

export default {
  components: {
    FileStorageAddNew,
    BCard,
    BButton,
    BRow,
    BCol,
    BFormInput,
    BTable,
    BPagination,
    vSelect,
  },
  data: () => ({
    isManager: (JSON.parse(localStorage.getItem('userData'))).ability
      .find(ability => ability.action === 'manage' && ability.subject === 'all'),

  }),
  methods: {
    doCopy(text) {
      this.$copyText(text).then(() => {
        this.$toast({
          component: ToastificationContent,
          props: {
            title: 'Ссылка на файл скопирована!',
            icon: 'BellIcon',
          },
        })
      }, () => {
        this.$toast({
          component: ToastificationContent,
          props: {
            title: 'Не может скопировать ссылку на файл!',
            icon: 'BellIcon',
          },
        })
      })
    },
  },
  setup() {
    const toast = useToast()
    const tableItems = ref(null)
    const isAddNewItemSidebarActive = ref(false)
    // Table Handlers
    const tableColumns = [
      { key: 'name', sortable: true, label: 'Наименование файла' },
      { key: 'description', sortable: true, label: 'Краткое описание' },
      { key: 'extension', sortable: true, label: 'Расширение файла' },
      { key: 'filePath', sortable: false, label: 'Файл' },
      { key: 'copy', sortable: false, label: '' },
      { key: 'delete', sortable: false, label: '' },

    ]
    const perPage = ref(10)
    const totalRows = ref(0)
    const currentPage = ref(1)
    const perPageOptions = [10, 25, 50, 100]
    const searchQuery = ref('')
    const sortBy = ref('id')
    const isSortDirDesc = ref(true)

    const dataMeta = computed(() => {
      const localItemsCount = tableItems.value ? tableItems.value.localItems.length : 0
      return {
        from: perPage.value * (currentPage.value - 1) + (localItemsCount ? 1 : 0),
        to: perPage.value * (currentPage.value - 1) + localItemsCount,
        of: totalRows.value,
      }
    })

    const refetchData = () => {
      isAddNewItemSidebarActive.value = false
      tableItems.value.refresh()
    }

    watch([currentPage, perPage, searchQuery], () => {
      refetchData()
    })

    const fetchDeleteItem = id => {
      axios.delete(`/file-storage/${id}`)
        .then(response => {
          refetchData()
          toast({
            component: ToastificationContent,
            position: 'top-right',
            props: {
              title: response.data.message,
              variant: 'success',
              icon: 'CheckCircleIcon',
            },
          })
        })
        .catch(() => {
          toast({
            component: ToastificationContent,
            position: 'top-right',
            props: {
              title: 'Не удалось удалить!',
              variant: 'danger',
              icon: 'AlertCircleIcon',
            },
          })
        })
    }

    const fetchItems = (ctx, callback) => {
      axios.get('/file-storage', {
        params: {
          q: searchQuery.value,
          perPage: perPage.value,
          page: currentPage.value,
          sortBy: sortBy.value,
          sortDesc: isSortDirDesc.value,
        },
      })
        .then(response => {
          const { fileStorage, total } = response.data

          callback(fileStorage)
          totalRows.value = total
        })
        .catch(() => {
          toast({
            component: ToastificationContent,
            position: 'top-right',
            props: {
              title: 'Не удалось загрузить документы',
              variant: 'danger',
              icon: 'AlertCircleIcon',
            },
          })
        })
    }

    return {
      tableColumns,
      tableItems,
      perPage,
      totalRows,
      currentPage,
      perPageOptions,
      searchQuery,
      sortBy,
      isSortDirDesc,
      dataMeta,

      isAddNewItemSidebarActive,
      fetchItems,
      refetchData,
      fetchDeleteItem,
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
