export default [
  {
    path: '/management/departments/list',
    name: 'management-departments-list',
    component: () => import('@/views/managements/departments/DepartmentsList'),
    meta: {
      resource: 'departments',
      action: 'manage',
      pageTitle: 'Отделы',
      breadcrumb: [
        {
          text: 'Управление',
        },
        {
          text: 'Отделы',
          active: true,
        },
      ],
    },
  },
  {
    path: '/management/employees/list',
    name: 'management-employees-list',
    component: () => import('@/views/managements/employees/EmployeesList'),
    meta: {
      resource: 'employees',
      action: 'manage',
      pageTitle: 'Сотрудники',
      breadcrumb: [
        {
          text: 'Управление',
        },
        {
          text: 'Сотрудники',
          active: true,
        },
      ],
    },
  },
  {
    path: '/management/visit-events/list',
    name: 'management-visit-events-list',
    component: () => import('@/views/managements/visitEvents/visitEventsList'),
    meta: {
      resource: 'visit-events',
      action: 'manage',
      pageTitle: 'Рабочий день',
      breadcrumb: [
        {
          text: 'Управление',
        },
        {
          text: 'Посещаемость',
        },
        {
          text: 'Рабочий день',
          active: true,
        },
      ],
    },
  },
  {
    path: '/management/visit-events/break',
    name: 'management-visit-events-break',
    component: () => import('@/views/managements/visitEvents/visitEventsBreak'),
    meta: {
      resource: 'visit-events',
      action: 'manage',
      pageTitle: 'Перерыв',
      breadcrumb: [
        {
          text: 'Управление',
        },
        {
          text: 'Посещаемость',
        },
        {
          text: 'Перерыв',
          active: true,
        },
      ],
    },
  },
  {
    path: '/management/users/list',
    name: 'management-users-list',
    component: () => import('@/views/managements/users/users-list/UsersList'),
    meta: {
      resource: 'users',
      action: 'manage',
      pageTitle: 'Пользователи',
      breadcrumb: [
        {
          text: 'Управление',
        },
        {
          text: 'Пользователи',
          active: true,
        },
      ],
    },
  },
  {
    path: '/management/users/view/:id',
    name: 'management-users-view',
    component: () => import('@/views/managements/users/users-view/UsersView'),
    meta: {
      resource: 'users',
      action: 'manage',
      pageTitle: 'Пользователи',
      breadcrumb: [
        {
          text: 'Управление',
        },
        {
          text: 'Пользователи',
          active: true,
        },
        {
          text: 'Подробно',
          active: true,
        },
      ],
    },
  },
  {
    path: '/management/users/edit/:id',
    name: 'management-users-edit',
    component: () => import('@/views/managements/users/users-edit/UsersEdit'),
    meta: {
      resource: 'users',
      action: 'manage',
      pageTitle: 'Пользователи',
      breadcrumb: [
        {
          text: 'Управление',
        },
        {
          text: 'Пользователи',
          active: true,
        },
        {
          text: 'Редактирование',
          active: true,
        },
      ],
    },
  },
]
