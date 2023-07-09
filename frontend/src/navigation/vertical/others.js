export default [
  {
    header: 'Органайзер проектов',
    action: 'write',
    resource: 'organization-project',
  },
  {
    title: 'Проекты',
    icon: 'ClipboardIcon',
    action: 'write',
    resource: 'organization-project',
    route: 'organization-project',
  },
  {
    title: 'Все проекты',
    icon: 'ClipboardIcon',
    action: 'manage',
    resource: 'organization-project',
    route: 'organization-all-project',
  },
  {
    header: 'Органайзер',
    action: 'write',
    resource: 'sport-department',
  },
  {
    title: 'Управление Спорта',
    icon: 'ClipboardIcon',
    action: 'write',
    resource: 'sport-department',
    route: 'organizer-sport',
  },
  {
    header: 'Мероприятия',
    action: 'read',
    resource: 'mcps-events',
  },
  {
    title: 'Облако слов',
    icon: 'AwardIcon',
    action: 'read',
    resource: 'mcps-events',
    route: 'management-word-cloud-list',
  },
  {
    header: 'Техподдержка',
    action: 'write',
    resource: 'it-support',
  },
  {
    title: 'Список заявок',
    icon: 'CheckSquareIcon',
    action: 'write',
    resource: 'it-support',
    route: 'management-helpdesk-list',
  },
]
