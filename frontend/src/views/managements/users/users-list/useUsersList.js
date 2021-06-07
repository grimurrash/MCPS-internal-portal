import { ref, watch, computed } from '@vue/composition-api'
import store from '@/store'

// Notification
import { useToast } from 'vue-toastification/composition'
import ToastificationContent from '@core/components/toastification/ToastificationContent.vue'

export default function useUsersList() {
  // Use toast
  const toast = useToast()

  const refUserListTable = ref(null)

  // Table Handlers
  const tableColumns = [
    { key: 'fullName', sortable: true, label: 'ФИО' },
    { key: 'email', sortable: true, label: 'Электронная почта' },
    { key: 'role_id', sortable: true, label: 'Роль' },
    { key: 'created_at', sortable: true, label: 'Дата добавления' },
    { key: 'actions', sortable: false, label: 'Действия' },
  ]
  const perPage = ref(10)
  const totalUsers = ref(0)
  const currentPage = ref(1)
  const perPageOptions = [10, 25, 50, 100]
  const searchQuery = ref('')
  const sortBy = ref('id')
  const isSortDirDesc = ref(true)
  const roleFilter = ref(null)
  const permissionFilter = ref(null)

  const dataMeta = computed(() => {
    const localItemsCount = refUserListTable.value ? refUserListTable.value.localItems.length : 0
    return {
      from: perPage.value * (currentPage.value - 1) + (localItemsCount ? 1 : 0),
      to: perPage.value * (currentPage.value - 1) + localItemsCount,
      of: totalUsers.value,
    }
  })

  const refetchData = () => {
    refUserListTable.value.refresh()
  }

  watch([currentPage, perPage, roleFilter, permissionFilter], () => {
    refetchData()
  })
  watch([searchQuery], () => {
    if (searchQuery.value.length >= 3) refetchData()
  })

  const fetchDeleteUser = id => {
    store.dispatch('app-user/fetchDeleteUser', {
      id,
    }).then(response => {
      if (response.data.status) {
        toast({
          component: ToastificationContent,
          props: {
            title: 'Пользователь удален',
            icon: 'CheckIcon',
            variant: 'success',
          },
        })
        refetchData()
      }
    }).catch(() => {
      toast({
        component: ToastificationContent,
        props: {
          title: 'Ошибка при удалении пользователя',
          icon: 'AlertTriangleIcon',
          variant: 'danger',
        },
      })
    })
  }

  const fetchUsers = (ctx, callback) => {
    store
      .dispatch('app-user/fetchUsers', {
        q: searchQuery.value,
        perPage: perPage.value,
        page: currentPage.value,
        sortBy: sortBy.value,
        sortDesc: isSortDirDesc.value,
        role_id: roleFilter.value,
        permission_id: permissionFilter.value,
      })
      .then(response => {
        const { users, total } = response.data

        callback(users)
        totalUsers.value = total
      })
      .catch(() => {
        toast({
          component: ToastificationContent,
          props: {
            title: 'Ошибка при получении списка пользователей',
            icon: 'AlertTriangleIcon',
            variant: 'danger',
          },
        })
      })
  }

  // *===============================================---*
  // *--------- UI ---------------------------------------*
  // *===============================================---*

  const resolveUserRoleVariant = role => {
    // if (role === 'member' || role === 'teacher') return 'primary'
    // if (role === 'developer') return 'warning'
    if (role === 'developer') return 'success'
    if (role === 'manager') return 'info'
    if (role === 'director' || role === 'deputy-director') return 'danger'
    return 'primary'
  }

  const resolveUserRoleIcon = role => {
    // if (role === 'member' || role === 'teacher') return 'UserIcon'
    // if (role === 'developer') return 'SettingsIcon'
    if (role === 'developer') return 'DatabaseIcon'
    if (role === 'manager') return 'Edit2Icon'
    if (role === 'director' || role === 'deputy-director') return 'ServerIcon'
    return 'UserIcon'
  }

  return {
    fetchUsers,
    fetchDeleteUser,
    tableColumns,
    perPage,
    currentPage,
    totalUsers,
    dataMeta,
    perPageOptions,
    searchQuery,
    sortBy,
    isSortDirDesc,
    refUserListTable,

    resolveUserRoleVariant,
    resolveUserRoleIcon,
    refetchData,

    // Extra Filters
    roleFilter,
    permissionFilter,
  }
}
