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
            <label>шаблонов</label>
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
        ref="templatesItems"
        class="position-relative"
        :items="fetchTemplates"
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
            class="text-nowrap alert-link"
            :href="data.item.filePath"
          >
            <feather-icon
              icon="FileTextIcon"
              size="18"
              class="mr-50 text-primary"
            />
            <span class="align-text-top text-capitalize">Шаблон</span>
          </a>
        </template>

        <!-- Column: DocumentType -->
        <template #cell(type_id)="data">
          <div class="text-nowrap">
            <span class="align-text-top text-capitalize">{{ data.item.type }}</span>
          </div>
        </template>

        <!-- Column: isDouble -->
        <template #cell(isDouble)="data">
          <div class="text-nowrap">
            <b-form-checkbox
              :checked="data.item.isDouble"
              disabled
            />
          </div>
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
              :total-rows="totalTemplates"
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
  BCard, BRow, BCol, BFormInput, BTable, BPagination, BFormCheckbox,
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
    BFormCheckbox,

    vSelect,
  },
  setup() {
    const toast = useToast()
    const templatesItems = ref(null)

    // Table Handlers
    const tableColumns = [
      { key: 'name', sortable: true, label: 'Наименование' },
      { key: 'description', sortable: true, label: 'Краткое описание' },
      { key: 'filePath', sortable: true, label: 'Шаблон' },
      { key: 'type_id', sortable: true, label: 'Тип документа' },
      { key: 'isDouble', sortable: true, label: '2 в 1' },
    ]
    const perPage = ref(10)
    const totalTemplates = ref(0)
    const currentPage = ref(1)
    const perPageOptions = [10, 25, 50, 100]
    const searchQuery = ref('')
    const sortBy = ref('id')
    const isSortDirDesc = ref(true)
    const documentTypeFilter = ref(null)
    const isDoubleFilter = ref(null)
    const isAddNewTemplateSidebarActive = ref(false)
    const isEditTemplateSidebarActive = ref(false)

    const dataMeta = computed(() => {
      const localItemsCount = templatesItems.value ? templatesItems.value.localItems.length : 0
      return {
        from: perPage.value * (currentPage.value - 1) + (localItemsCount ? 1 : 0),
        to: perPage.value * (currentPage.value - 1) + localItemsCount,
        of: totalTemplates.value,
      }
    })

    const documentTypeOptions = ref([])
    const isDoubleOptions = [
      {
        value: 1,
        label: '2 копии в документе',
      },
      {
        value: 0,
        label: '1 копия в документе',
      },
    ]
    axios.get('/documents/documentTypes')
      .then(response => {
        documentTypeOptions.value = response.data.documentTypes.map(item => ({
          value: item.id,
          label: item.name,
        }))
      })
      .catch(() => {
        toast({
          component: ToastificationContent,
          position: 'top-right',
          props: {
            title: 'Не удалось загрузить типы документов',
            variant: 'danger',
            icon: 'AlertCircleIcon',
          },
        })
      })

    const editTemplate = ref({
      id: 0,
      name: '',
      description: '',
      file: null,
      documentType_id: null,
      isDouble: 0,
    })
    const selectEditTemplate = item => {
      editTemplate.value = item
    }

    const refetchData = () => {
      templatesItems.value.refresh()
    }

    watch([currentPage, perPage, documentTypeFilter, isDoubleFilter, searchQuery], () => {
      refetchData()
    })

    const fetchTemplates = (ctx, callback) => {
      axios.get('/documents/templates', {
        params: {
          q: searchQuery.value,
          perPage: perPage.value,
          page: currentPage.value,
          sortBy: sortBy.value,
          sortDesc: isSortDirDesc.value,
          type_id: documentTypeFilter.value,
          isDouble: isDoubleFilter.value,
        },
      })
        .then(response => {
          const { templates, total } = response.data

          callback(templates)
          totalTemplates.value = total
        })
        .catch(() => {
          this.$toast({
            component: ToastificationContent,
            position: 'top-right',
            props: {
              title: 'Не удалось загрузить шаблоны',
              variant: 'danger',
              icon: 'AlertCircleIcon',
            },
          })
        })
    }

    const fetchDeleteTemplate = id => {
      axios.delete(`/documents/templates/${id}`)
        .then(() => {
          refetchData()
        })
        .catch(() => {
          this.$toast({
            component: ToastificationContent,
            position: 'top-right',
            props: {
              title: 'Не удалось удалить шаблон',
              variant: 'danger',
              icon: 'AlertCircleIcon',
            },
          })
        })
    }

    return {
      tableColumns,
      editTemplate,
      templatesItems,
      perPage,
      totalTemplates,
      currentPage,
      perPageOptions,
      searchQuery,
      sortBy,
      isSortDirDesc,
      documentTypeFilter,
      isDoubleFilter,
      isAddNewTemplateSidebarActive,
      isEditTemplateSidebarActive,
      dataMeta,

      documentTypeOptions,
      isDoubleOptions,

      fetchTemplates,
      fetchDeleteTemplate,
      refetchData,
      selectEditTemplate,
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
