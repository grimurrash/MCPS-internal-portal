export default [
  {
    header: 'Управление',
    action: 'manage',
    resource: 'manager',
  },
  {
    title: 'Отделы',
    icon: 'UsersIcon',
    action: 'manage',
    resource: 'departments',
    route: 'management-departments-list',
  },
  {
    title: 'Сотрудники',
    icon: 'UsersIcon',
    action: 'manage',
    resource: 'employees',
    route: 'management-employees-list',
  },
  {
    title: 'Посещаемость',
    icon: 'BellIcon',
    resource: 'visit-events',
    action: 'manage',
    children: [
      {
        title: 'Рабочий день',
        route: 'management-visit-events-list',
        resource: 'visit-events',
        action: 'manage',
      },
      {
        title: 'Перерыв',
        route: 'management-visit-events-break',
        resource: 'visit-events',
        action: 'manage',
      },
      {
        title: 'Прогулы',
        route: 'management-visit-events-absenteeism',
        resource: 'visit-events',
        action: 'manage',
      },
      {
        title: 'Отсутствие на работе',
        route: 'management-visit-events-absence-work',
        resource: 'visit-events',
        action: 'manage',
      },
    ],
  },
  {
    title: 'Пользователи',
    icon: 'UsersIcon',
    action: 'manage',
    resource: 'users',
    route: 'management-users-list',
  },
]
