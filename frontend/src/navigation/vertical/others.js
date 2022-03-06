export default [
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
]
