export default [
  {
    title: 'Заявки',
    icon: 'SunIcon',
    resource: 'request-to',
    action: 'read',
    children: [
      {
        title: 'Техническая поддержка',
        route: 'request-to-the-technical-support',
        resource: 'request-to',
        action: 'read',
      },
      {
        title: 'Претензионные работы',
        route: 'claim-work',
        resource: 'request-to',
        action: 'read',
      },
    ],
  },
  {
    title: 'Отдел',
    icon: 'UsersIcon',
    resource: 'department',
    action: 'write',
    children: [
      {
        title: 'Опоздания',
        route: 'department-visit-event',
        resource: 'department',
        action: 'write',
      },
      {
        title: 'Перерывы',
        route: 'department-visit-event-break',
        resource: 'department',
        action: 'write',
      },
    ],
  },
]
