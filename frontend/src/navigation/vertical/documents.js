export default [
  {
    title: 'Документы',
    icon: 'FileTextIcon',
    resource: 'document',
    action: 'read',
    children: [
      {
        title: 'Word по шаблону',
        resource: 'document',
        action: 'read',
        route: 'documents-templates-print',
      },
      {
        title: 'Word по сущ. шаблону',
        resource: 'document',
        action: 'read',
        route: 'documents-existent-templates-print',
      },
      {
        title: 'Шаблоны документов',
        resource: 'document',
        action: 'read',
        route: 'documents-templates-list',
      },
      {
        title: 'Шаблоны (Управление)',
        resource: 'document',
        action: 'write',
        route: 'documents-templates-list-adm',
      },
    ],
  },
  {
    title: 'Хранилище файлов',
    icon: 'InboxIcon',
    route: 'file-storage-list',
    resource: 'file-storage',
    action: 'read',
  },
]
