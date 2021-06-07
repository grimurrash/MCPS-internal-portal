export default [
  {
    path: '/',
    name: 'home',
    component: () => import('@/views/pages/Home.vue'),
    meta: {
      resource: 'member',
      action: 'read',
      pageTitle: 'Home',
      breadcrumb: [
        {
          text: 'Home',
          active: true,
        },
      ],
    },
  },
  {
    path: '/phoneBook',
    name: 'phone-book',
    component: () => import('@/views/pages/PhoneBook'),
    meta: {
      resource: 'member',
      action: 'read',
      pageTitle: 'Телефонный справочник',
      breadcrumb: [
        {
          text: 'Телефонный справочник',
          active: true,
        },
      ],
    },
  },
  {
    path: '/account-setting',
    name: 'account-setting',
    component: () => import('@/views/pages/account-setting/AccountSetting'),
    meta: {
      resource: 'member',
      action: 'read',
      pageTitle: 'Настройки учетной записи',
      breadcrumb: [
        {
          text: 'Настройки учетной записи',
          active: true,
        },
      ],
    },
  },
]
