export default [
  {
    path: '/request-to-the-technical-support',
    name: 'request-to-the-technical-support',
    component: () => import('@/views/pages/RequestTo/RequestToTheTechnicalSupport.vue'),
    meta: {
      action: 'read',
      resource: 'request-to',
      pageTitle: 'Заявка в техническую поддержку',
      breadcrumb: [
        {
          text: 'Заявки',
        },
        {
          text: 'Заявка в техническую поддержку',
          active: true,
        },
      ],
    },
  },
  {
    path: '/claim-work',
    name: 'claim-work',
    component: () => import('@/views/pages/RequestTo/ClaimWork.vue'),
    meta: {
      resource: 'request-to',
      action: 'read',
      pageTitle: 'Претензионные работы',
      breadcrumb: [
        {
          text: 'Заявки',
        },
        {
          text: 'Претензионные работы',
          active: true,
        },
      ],
    },
  },
  {
    path: '/department/visit-event',
    name: 'department-visit-event',
    component: () => import('@/views/apps/department/visitEvent/visitEventsList'),
    meta: {
      resource: 'department',
      action: 'write',
      pageTitle: 'Посещаемость отдела',
      breadcrumb: [
        {
          text: 'Отдел',
        },
        {
          text: 'Опоздания',
          active: true,
        },
      ],
    },
  },
  {
    path: '/department/visit-event/break',
    name: 'department-visit-event-break',
    component: () => import('@/views/apps/department/visitEvent/visitEventsBreak'),
    meta: {
      resource: 'department',
      action: 'write',
      pageTitle: 'Посещаемость отдела',
      breadcrumb: [
        {
          text: 'Отдел',
        },
        {
          text: 'Перерывы',
          active: true,
        },
      ],
    },
  },
]
