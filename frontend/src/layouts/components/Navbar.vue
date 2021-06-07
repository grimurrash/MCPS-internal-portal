<template>
  <div class="navbar-container d-flex content align-items-center">

    <!-- Nav Menu Toggler -->
    <ul class="nav navbar-nav d-xl-none">
      <li class="nav-item">
        <b-link
          class="nav-link"
          @click="toggleVerticalMenuActive"
        >
          <feather-icon
            icon="MenuIcon"
            size="21"
          />
        </b-link>
      </li>
    </ul>

    <!--     Left Col-->
    <!--            <div class="bookmark-wrapper align-items-center flex-grow-1 d-none d-lg-flex"></div>-->

    <b-navbar-nav class="nav align-items-center ml-auto">
      <dark-toggler class="d-none d-lg-block" />
      <user-dropdown />
    </b-navbar-nav>
  </div>
</template>

<script>
import {
  BLink, BNavbarNav,
} from 'bootstrap-vue'
import DarkToggler from '@core/layouts/components/app-navbar/components/DarkToggler.vue'
// eslint-disable-next-line import/extensions
import UserDropdown from '@core/layouts/components/app-navbar/components/UserDropdown'

export default {
  components: {
    BLink,
    BNavbarNav,

    // Navbar Components
    DarkToggler,
    UserDropdown,
  },
  props: {
    toggleVerticalMenuActive: {
      type: Function,
      default: () => {},
    },
  },
}
</script>
