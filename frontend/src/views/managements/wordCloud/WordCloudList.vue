<template>

  <div>

    <word-cloud-add
      :is-add-sidebar-active.sync="isAddSidebarActive"
      @refetch-data="refetchData"
    />

    <word-cloud-edit
      :is-edit-sidebar-active.sync="isEditSidebarActive"
      :edit-item="editItemData"
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
            <label>облак слов</label>
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
                @click="isAddSidebarActive = true"
              >
                <span class="text-nowrap">Добавить</span>
              </b-button>
            </div>
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
        <template #cell(createUser)="data">
          <span class="font-weight-bold d-block text-nowrap">
            {{ data.item.createUser.fullName }}
          </span>
        </template>

        <!-- Column: wordCloudUrl -->
        <template #cell(wordCloudUrl)="data">
          <b-button
            v-clipboard:copy="data.item.wordCloudUrl"
            v-clipboard:success="onCopy"
            v-clipboard:error="onError"
            v-ripple.400="'rgba(186, 191, 199, 0.15)'"
            variant="primary"
          >
            <feather-icon icon="CopyIcon" />
            Скопировать
          </b-button>
        </template>

        <!-- Column: answerFormUrl -->
        <template #cell(answerFormUrl)="data">
          <b-button
            v-clipboard:copy="data.item.answerFormUrl"
            v-clipboard:success="onCopy"
            v-clipboard:error="onError"
            variant="primary"
          >
            <feather-icon icon="CopyIcon" />
            Скопировать
          </b-button>
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
            <b-dropdown-item @click.prevent="selectEditSidebar(data.item.id)">
              <feather-icon icon="EditIcon" />
              <span class="align-middle ml-50">Редактировать</span>
            </b-dropdown-item>
            <b-dropdown-item @click.prevent="fetchClearItem(data.item.id)">
              <feather-icon icon="Trash2Icon" />
              <span class="align-middle ml-50">Очистить</span>
            </b-dropdown-item>
            <b-dropdown-item @click.prevent="fetchDeleteItem(data.item.id)">
              <feather-icon icon="TrashIcon" />
              <span class="align-middle ml-50">Удалить</span>
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
  BCard, BRow, BCol, BFormInput, BTable, BDropdown, BDropdownItem, BPagination, BButton,
} from 'bootstrap-vue'
import axios from '@/libs/axios'
import ToastificationContent from '@core/components/toastification/ToastificationContent.vue'
import Ripple from 'vue-ripple-directive'
import vSelect from 'vue-select'
import { getUserData } from '@/auth/utils'
import { ref, watch, computed } from '@vue/composition-api'
import { useToast } from 'vue-toastification/composition'
import { avatarText } from '@core/utils/filter'
import WordCloudAdd from '@/views/managements/wordCloud/WordCloudAdd.vue'
import WordCloudEdit from '@/views/managements/wordCloud/WordCloudEdit.vue'

export default {
  components: {
    WordCloudEdit,
    BCard,
    BRow,
    BCol,
    BFormInput,
    BTable,
    BDropdown,
    BButton,
    BDropdownItem,
    BPagination,
    vSelect,
    WordCloudAdd,
  },
  directives: {
    Ripple,
  },
  methods: {
    onCopy() {
      this.$toast({
        component: ToastificationContent,
        props: {
          title: 'Ссылка скопирована',
          icon: 'BellIcon',
        },
      })
    },
    onError() {
      this.$toast({
        component: ToastificationContent,
        props: {
          title: 'Ошибка при копировании ссылки! ',
          icon: 'BellIcon',
        },
      })
    },
  },
  setup() {
    const toast = useToast()

    const items = ref(null)

    // Table Handlers
    const tableColumns = [
      { key: 'eventName', sortable: true, label: 'Мероприятие' },
      { key: 'question', sortable: true, label: 'Вопрос' },
      { key: 'createUser', sortable: true, label: 'Создатель' },
      { key: 'wordCloudUrl', sortable: true, label: 'Ссылка на облако слов' },
      { key: 'answerFormUrl', sortable: true, label: 'Ссылка на форму вопроса' },
      { key: 'answersCount', sortable: true, label: 'Кол-во ответов' },
      { key: 'actions', sortable: false, label: 'Действия' },
    ]
    const perPage = ref(10)
    const totalCount = ref(0)
    const currentPage = ref(1)
    const perPageOptions = [10, 25, 50, 100]
    const searchQuery = ref('')
    const sortBy = ref('id')
    const isSortDirDesc = ref(true)
    const isEditSidebarActive = ref(false)
    const isAddSidebarActive = ref(false)

    const dataMeta = computed(() => {
      const localItemsCount = items.value ? items.value.localItems.length : 0
      return {
        from: perPage.value * (currentPage.value - 1) + (localItemsCount ? 1 : 0),
        to: perPage.value * (currentPage.value - 1) + localItemsCount,
        of: totalCount.value,
      }
    })

    const editItemData = ref({
      id: 0,
      eventName: '',
      question: '',
      backgroundImage: '',
      wordColor: 'pastel1',
      exceptionWords: '',
      pageTitle: '',
      logoPanelImg: '',
      isUnique: false,
      additionalCss: '',
    })

    const refetchData = () => {
      items.value.refresh()
      isEditSidebarActive.value = false
      isAddSidebarActive.value = false
    }

    watch([currentPage, perPage, searchQuery], () => {
      refetchData()
    })

    const selectEditSidebar = itemId => {
      axios.get(`/events/wordcloud/${itemId}`).then(res => {
        editItemData.value = res.data
        isEditSidebarActive.value = true
      }).catch(error => {
        if (error.response.status === 403) {
          toast({
            component: ToastificationContent,
            position: 'top-right',
            props: {
              title: 'Недостаточно прав. Обратитесь к создателю облака.',
              variant: 'danger',
              icon: 'AlertCircleIcon',
            },
          })
        } else {
          toast({
            component: ToastificationContent,
            position: 'top-right',
            props: {
              title: 'Ошибка при получении информации об облаке. Обратитесь к технической поддержке.',
              variant: 'danger',
              icon: 'AlertCircleIcon',
            },
          })
        }
      })
    }

    const fetchItemList = (ctx, callback) => {
      axios.get('/events/wordcloud', {
        params: {
          q: searchQuery.value,
          perPage: perPage.value,
          page: currentPage.value,
          sortBy: sortBy.value,
          sortDesc: isSortDirDesc.value,
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
            title: 'Не удалось загрузить облака слов',
            variant: 'danger',
            icon: 'AlertCircleIcon',
          },
        })
      })
    }

    const fetchClearItem = itemId => {
      axios.post(`/events/wordcloud/${itemId}/clear`).then(() => {
        toast({
          component: ToastificationContent,
          position: 'top-right',
          props: {
            title: 'Облако слов очищено',
            variant: 'success',
            icon: 'CheckIcon',
          },
        })
        refetchData()
      }).catch(error => {
        if (error.response.status === 403) {
          toast({
            component: ToastificationContent,
            position: 'top-right',
            props: {
              title: 'Недостаточно прав. Обратитесь к создателю облака.',
              variant: 'danger',
              icon: 'AlertCircleIcon',
            },
          })
        } else {
          toast({
            component: ToastificationContent,
            position: 'top-right',
            props: {
              title: 'Ошибка при очистке облака. Обратитесь к технической поддержке.',
              variant: 'danger',
              icon: 'AlertCircleIcon',
            },
          })
        }
      })
    }

    const fetchDeleteItem = itemId => {
      axios.delete(`/events/wordcloud/${itemId}`).then(() => {
        toast({
          component: ToastificationContent,
          position: 'top-right',
          props: {
            title: 'Облако слов удалено',
            variant: 'success',
            icon: 'CheckIcon',
          },
        })
        refetchData()
      }).catch(error => {
        if (error.response.status === 403) {
          toast({
            component: ToastificationContent,
            position: 'top-right',
            props: {
              title: 'Недостаточно прав. Обратитесь к создателю облака.',
              variant: 'danger',
              icon: 'AlertCircleIcon',
            },
          })
        } else {
          toast({
            component: ToastificationContent,
            position: 'top-right',
            props: {
              title: 'Ошибка при удалении облака. Обратитесь к технической поддержке.',
              variant: 'danger',
              icon: 'AlertCircleIcon',
            },
          })
        }
      })
    }

    const resolveUserRoleVariant = role => {
      // if (role === 'member' || role === 'teacher') return 'primary'
      // if (role === 'developer') return 'warning'
      if (role === 'developer') {
        return 'success'
      }
      if (role === 'manager') {
        return 'info'
      }
      if (role === 'director' || role === 'deputy-director') {
        return 'danger'
      }
      return 'primary'
    }

    const resolveUserRoleIcon = role => {
      // if (role === 'member' || role === 'teacher') return 'UserIcon'
      // if (role === 'developer') return 'SettingsIcon'
      if (role === 'developer') {
        return 'DatabaseIcon'
      }
      if (role === 'manager') {
        return 'Edit2Icon'
      }
      if (role === 'director' || role === 'deputy-director') {
        return 'ServerIcon'
      }
      return 'UserIcon'
    }

    return {
      tableColumns,
      editItemData,
      items,
      perPage,
      totalCount,
      currentPage,
      perPageOptions,
      searchQuery,
      sortBy,
      isSortDirDesc,
      isEditSidebarActive,
      isAddSidebarActive,
      dataMeta,
      avatarText,
      resolveUserRoleVariant,
      resolveUserRoleIcon,
      fetchItemList,
      refetchData,
      selectEditSidebar,
      getUserData,
      fetchClearItem,
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
