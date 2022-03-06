export default [
  {
    path: '/documents/templates/print',
    name: 'documents-templates-print',
    component: () => import('@/views/apps/documents/print/TemplatePrint'),
    meta: {
      pageTitle: 'Печать по шаблону',
      resource: 'document',
      action: 'read',
      breadcrumb: [
        {
          text: 'Документы',
        },
        {
          text: 'Печать по шаблону',
          active: true,
        },
      ],
    },
  },
  {
    path: '/documents/templates/list',
    name: 'documents-templates-list',
    component: () => import('@/views/apps/documents/templates/TemplatesList'),
    meta: {
      resource: 'document',
      action: 'read',
      pageTitle: 'Шаблоны документов',
      breadcrumb: [
        {
          text: 'Документы',
        },
        {
          text: 'Шаблоны',
          active: true,
        },
      ],
    },
  },
  {
    path: '/documents/existent-templates/print',
    name: 'documents-existent-templates-print',
    component: () => import('@/views/apps/documents/print/ExistentTemplatePrint'),
    meta: {
      resource: 'document',
      action: 'read',
      pageTitle: 'Печать по сущ. шаблону',
      breadcrumb: [
        {
          text: 'Документы',
        },
        {
          text: 'Печать по сущ. шаблону',
          active: true,
        },
      ],
    },
  },
  {
    path: '/documents/templates/management-list',
    name: 'documents-templates-list-adm',
    component: () => import('@/views/apps/documents/templates/TemplatesManagerList'),
    meta: {
      resource: 'document',
      action: 'write',
      pageTitle: 'Шаблоны документов (Управление)',
      breadcrumb: [
        {
          text: 'Документы',
        },
        {
          text: 'Шаблоны',
          active: true,
        },
      ],
    },
  },
  {
    path: '/file-storage/list',
    name: 'file-storage-list',
    component: () => import('@/views/apps/file-storage/FileStorageList'),
    meta: {
      resource: 'file-storage',
      action: 'read',
      pageTitle: 'Хранилище файлов',
      breadcrumb: [
        {
          text: 'Документы',
        },
        {
          text: 'Хранилище файлов',
          active: true,
        },
      ],
    },
  },
]
