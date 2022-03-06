<template>

  <div>

    <organizer-menu-add
      :is-add-sidebar-active.sync="isOrganizerMenuAddSidebarActive"
      :organizer-id.sync="organizerId"
      @refetch-data="refetchData"
    />

    <organizer-item-add
      :is-add-sidebar-active.sync="isOrganizerItemAddSidebarActive"
      :organizer-menu-id.sync="selectOrganizerMenuId"
      @refetch-data="refetchData"
    />

    <organizer-menu-edit
      :is-edit-sidebar-active.sync="isOrganizerMenuEditSidebarActive"
      :edit-item="selectOrganizerMenu"
      @refetch-data="refetchData"
    />

    <organizer-item-edit
      :is-edit-sidebar-active.sync="isOrganizerItemEditSidebarActive"
      :edit-item="selectOrganizerItem"
      @refetch-data="refetchData"
    />

    <b-card
      no-body
      class="mb-0"
    >
      <div class="m-2">
        <b-tabs v-model="tabIndex">
          <!-- Render Tabs, supply a unique `key` to each tab -->
          <b-tab
            v-for="organizerMenu in organizerMenus"
            :key="'organizer-menu-' + organizerMenu.id"
            :title="organizerMenu.name"
            lazy
          >
            <template #title>
              <span @contextmenu.prevent="openOrganizerMenuContextMenu($event, organizerMenu)"> {{
                organizerMenu.name
              }}</span>
            </template>
            <b-tabs>
              <!-- Render Tabs, supply a unique `key` to each tab -->
              <b-tab
                v-for="organizerItem in organizerMenu.items"
                :key="'organizer-item-' + organizerItem.id"
                :title="organizerItem.name"
                lazy
                class="organizerItemTab"
              >
                <template #title>
                  <span @contextmenu.prevent="openOrganizerItemContextMenu($event, organizerItem)"> {{
                    organizerItem.name
                  }}</span>
                </template>
                <iframe
                  :src="organizerItem.link"
                  class="organizerIframe"
                  align="left"
                  height="100%"
                  width="100%"
                  frameborder="0"
                >
                  Ваш браузер не поддерживает плавающие фреймы!
                </iframe>
              </b-tab>

              <!-- New Tab Button (Using tabs-end slot) -->
              <template #tabs-end>
                <b-nav-item
                  role="presentation"
                  @click.prevent="openOrganizerItemAddSidebar"
                >
                  <b>+</b>
                </b-nav-item>
              </template>

              <!-- Render this if no tabs -->
              <template #empty>
                <div class="text-center text-muted">
                  В данном пункте меню нет таблиц<br>
                  Добавьте таблицу с помощью кнопки <b>+</b> выше..
                </div>
              </template>
            </b-tabs>
          </b-tab>

          <!-- New Tab Button (Using tabs-end slot) -->
          <template #tabs-end>
            <b-nav-item
              role="presentation"
              @click.prevent="openOrganizerMenuAddSidebar"
            >
              <b>+</b>
            </b-nav-item>
          </template>

          <!-- Render this if no tabs -->
          <template #empty>
            <div class="text-center text-muted">
              Ваш органайзер пустой<br>
              Создайте новый пункт меню с помощью кнопки <b>+</b> выше..
            </div>
          </template>
        </b-tabs>
      </div>
    </b-card>

    <vue-context ref="organizerMenuContextMenu">
      <li>
        <b-link
          class="d-flex align-items-center"
          @click="isOrganizerMenuEditSidebarActive = true"
        >
          <span class="ml-25">Изменить</span>
        </b-link>
      </li>
      <li>
        <b-link
          class="d-flex align-items-center"
          @click="deleteOrganizerMenu"
        >
          <span class="ml-25">Удалить</span>
        </b-link>
      </li>
    </vue-context>

    <vue-context ref="organizerItemContextMenu">
      <li>
        <b-link
          class="d-flex align-items-center"
          @click="openOrganizerItemNewWindow"
        >
          <span class="ml-25">Открыть в новой вкладке</span>
        </b-link>
      </li>
      <li>
        <b-link
          class="d-flex align-items-center"
          @click="copyOrganizerItemLink"
        >
          <span class="ml-25">Скопировать ссылку</span>
        </b-link>
      </li>
      <li>
        <b-link
          class="d-flex align-items-center"
          @click="isOrganizerItemEditSidebarActive = true"
        >
          <span class="ml-25">Изменить</span>
        </b-link>
      </li>
      <li>
        <b-link
          class="d-flex align-items-center"
          @click="deleteOrganizerItem"
        >
          <span class="ml-25">Удалить</span>
        </b-link>
      </li>
    </vue-context>
  </div>
</template>

<script>
import {
  BCard, BTab, BTabs, BNavItem, BLink,
} from 'bootstrap-vue'
import axios from '@/libs/axios'
import VueContext from 'vue-context'
import ToastificationContent from '@core/components/toastification/ToastificationContent.vue'
import OrganizerMenuAdd from '@/views/apps/organizers/sport/OrganizerMenuAdd.vue'
import OrganizerItemAdd from '@/views/apps/organizers/sport/OrganizerItemAdd.vue'
import Ripple from 'vue-ripple-directive'
import OrganizerMenuEdit from '@/views/apps/organizers/sport/OrganizerMenuEdit.vue'
import OrganizerItemEdit from '@/views/apps/organizers/sport/OrganizerItemEdit.vue'

export default {
  components: {
    OrganizerItemEdit,
    OrganizerMenuEdit,
    BCard,
    BTabs,
    BTab,
    BNavItem,
    BLink,
    VueContext,
    OrganizerMenuAdd,
    OrganizerItemAdd,
  },
  directives: {
    Ripple,
  },
  data() {
    return {
      organizerId: 1,
      organizerMenus: [],
      tabIndex: 0,
      selectOrganizerMenuId: 1,
      selectOrganizerMenu: {
        id: 0,
      },
      selectOrganizerItem: {
        id: 0,
      },
      isOrganizerMenuAddSidebarActive: false,
      isOrganizerItemAddSidebarActive: false,
      isOrganizerMenuEditSidebarActive: false,
      isOrganizerItemEditSidebarActive: false,
      iframeHeight: 720,
    }
  },
  watch: {
    isOrganizerItemAddSidebarActive(newStatus) {
      if (!newStatus) {
        this.selectOrganizerMenuId = 0
      }
    },
    isOrganizerMenuEditSidebarActive(newStatus) {
      if (!newStatus) {
        this.selectOrganizerMenu = {
          id: 0,
        }
      }
    },
    isOrganizerItemEditSidebarActive(newStatus) {
      if (!newStatus) {
        this.selectOrganizerItem = {
          id: 0,
        }
      }
    },
  },
  created() {
    this.refetchData()
  },
  methods: {
    refetchData() {
      axios.get(`/organizer/${this.organizerId}`).then(res => {
        this.organizerMenus = res.data
        this.isOrganizerMenuAddSidebarActive = false
        this.isOrganizerItemAddSidebarActive = false
        this.isOrganizerMenuEditSidebarActive = false
        this.isOrganizerItemEditSidebarActive = false
      }).catch(() => {
        this.$toast({
          component: ToastificationContent,
          position: 'top-right',
          props: {
            title: 'Ошибка при получении информации. Обратитесь к технической поддержке.',
            variant: 'danger',
            icon: 'AlertCircleIcon',
          },
        })
      })
    },
    openOrganizerMenuAddSidebar() {
      this.isOrganizerMenuAddSidebarActive = true
    },
    openOrganizerMenuContextMenu(event, organizerMenu) {
      this.selectOrganizerMenu = organizerMenu
      this.$refs.organizerMenuContextMenu.open(event)
    },
    deleteOrganizerMenu() {
      axios.delete(`/organizer/menu/${this.selectOrganizerMenu.id}`).then(() => {
        this.refetchData()
        this.$toast({
          component: ToastificationContent,
          props: {
            title: 'Органайзер обновлен',
            icon: 'CheckIcon',
            variant: 'success',
          },
        })
      }).catch(() => {
        this.$toast({
          component: ToastificationContent,
          props: {
            title: 'Ошибка при удалении таблицы из органайзера: ',
            icon: 'AlertTriangleIcon',
            variant: 'danger',
          },
        })
      })
    },
    openOrganizerItemContextMenu(event, organizerItem) {
      this.selectOrganizerItem = organizerItem
      this.$refs.organizerItemContextMenu.open(event)
    },
    openOrganizerItemAddSidebar() {
      this.selectOrganizerMenuId = this.organizerMenus[this.tabIndex].id
      this.isOrganizerItemAddSidebarActive = true
    },
    openOrganizerItemNewWindow() {
      window.open(this.selectOrganizerItem.link)
    },
    copyOrganizerItemLink() {
      this.$copyText(this.selectOrganizerItem.link).then(() => {
        this.$toast({
          component: ToastificationContent,
          props: {
            title: 'Ссылка скопирована',
            icon: 'BellIcon',
          },
        })
      }, () => {
        this.$toast({
          component: ToastificationContent,
          props: {
            title: 'Ошибка при копировании ссылки! ',
            icon: 'BellIcon',
          },
        })
      })
    },
    deleteOrganizerItem() {
      axios.delete(`/organizer/item/${this.selectOrganizerItem.id}`).then(() => {
        this.refetchData()
        this.$toast({
          component: ToastificationContent,
          props: {
            title: 'Органайзер обновлен',
            icon: 'CheckIcon',
            variant: 'success',
          },
        })
      }).catch(() => {
        this.$toast({
          component: ToastificationContent,
          props: {
            title: 'Ошибка при удалении таблицы из органайзера',
            icon: 'AlertTriangleIcon',
            variant: 'danger',
          },
        })
      })
    },

  },
}
</script>

<style lang="scss" scoped>
@import '~@core/scss/vue/libs/vue-context.scss';

.per-page-selector {
  width: 90px;
}

.organizerItemTab {
  margin: 0;
  padding: 0;
  overflow: hidden;
}

.organizerIframe {
  overflow: hidden;
  width: 100%;
  height: calc(100vh - 150px);
}
</style>
