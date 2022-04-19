export default [
  {
    path: '/organizer/sport',
    name: 'organizer-sport',
    component: () => import('@/views/apps/organizers/sport/OrganizerList.vue'),
    meta: {
      resource: 'sport-department',
      action: 'write',
      pageTitle: 'Органайзер отдела',
      breadcrumb: [
        {
          text: 'Органайзер',
        },
        {
          text: 'Отдел спорта',
          active: true,
        },
      ],
    },
  },
  {
    path: '/management/word-cloud/list',
    name: 'management-word-cloud-list',
    component: () => import('@/views/managements/wordCloud/WordCloudList'),
    meta: {
      resource: 'mcps-events',
      action: 'read',
      pageTitle: 'Облако слов',
      breadcrumb: [
        {
          text: 'Мероприятия',
        },
        {
          text: 'Облако слов',
          active: true,
        },
      ],
    },
  },
  {
    path: '/management/helpdesk/list',
    name: 'management-helpdesk-list',
    component: () => import('@/views/managements/helpdesk/HelpDeskList.vue'),
    meta: {
      resource: 'it-support',
      action: 'write',
      pageTitle: 'Техподдержка',
      breadcrumb: [
        {
          text: 'Техподдержка',
        },
        {
          text: 'Заявки',
          active: true,
        },
      ],
    },
  },
  {
    path: '/helpdesk',
    name: 'helpdesk',
    component: () => import('@/views/pages/RequestTo/RequestToTheTechnicalSupport.vue'),
    meta: {
      action: 'read',
      layout: 'full',
      resource: 'Auth',
      pageTitle: 'Заявка в техническую поддержку',
      breadcrumb: [
        {
          text: 'Техподдержка',
        },
        {
          text: 'Заявка в техническую поддержку',
          active: true,
        },
      ],
    },
  },
  {
    path: '/login',
    name: 'auth-login',
    component: () => import('@/views/others/authentication/Login.vue'),
    meta: {
      layout: 'full',
      resource: 'Auth',
      redirectIfLoggedIn: true,
    },
  },
  {
    path: '/auth/forgot-password',
    name: 'auth-forgot-password',
    component: () => import('@/views/others/authentication/ForgotPassword.vue'),
    meta: {
      layout: 'full',
      resource: 'Auth',
      redirectIfLoggedIn: false,
    },
  },
  {
    path: '/auth/reset-password/:token',
    name: 'auth-reset-password',
    component: () => import('@/views/others/authentication/ResetPassword.vue'),
    meta: {
      layout: 'full',
      resource: 'Auth',
      redirectIfLoggedIn: false,
    },
  },

  {
    path: '/error-not-authorized',
    name: 'misc-not-authorized',
    component: () => import('@/views/error/NotAuthorized.vue'),
    meta: {
      layout: 'full',
      resource: 'Auth',
    },
  },
  {
    path: '/error-404',
    name: 'error-404',
    component: () => import('@/views/error/Error404.vue'),
    meta: {
      layout: 'full',
    },
  },
]
