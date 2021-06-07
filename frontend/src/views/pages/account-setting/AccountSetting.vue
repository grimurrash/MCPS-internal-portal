<template>
  <b-tabs
    vertical
    content-class="col-12 col-md-9 mt-1 mt-md-0"
    pills
    nav-wrapper-class="col-md-3 col-12"
    nav-class="nav-left"
  >

    <!-- general tab -->
    <b-tab active>

      <!-- title -->
      <template #title>
        <feather-icon
          icon="UserIcon"
          size="18"
          class="mr-50"
        />
        <span class="font-weight-bold">Общие</span>
      </template>

      <account-setting-general
        v-if="options.general"
        :general-data.sync="options.general"
      />
    </b-tab>
    <!--/ general tab -->

    <!-- change password tab -->
    <b-tab>

      <!-- title -->
      <template #title>
        <feather-icon
          icon="LockIcon"
          size="18"
          class="mr-50"
        />
        <span class="font-weight-bold">Поменять пароль</span>
      </template>

      <account-setting-password />
    </b-tab>
    <!--/ change password tab -->

    <!-- info -->
    <!--    <b-tab>-->

    <!--      &lt;!&ndash; title &ndash;&gt;-->
    <!--      <template #title>-->
    <!--        <feather-icon-->
    <!--          icon="InfoIcon"-->
    <!--          size="18"-->
    <!--          class="mr-50"-->
    <!--        />-->
    <!--        <span class="font-weight-bold">Информация</span>-->
    <!--      </template>-->

    <!--      <account-setting-information-->
    <!--        v-if="options.info"-->
    <!--        :information-data="options.info"-->
    <!--      />-->
    <!--    </b-tab>-->
  </b-tabs>
</template>

<script>
import { BTabs, BTab } from 'bootstrap-vue'
import axios from '@/libs/axios'
import AccountSettingGeneral from './AccountSettingGeneral.vue'
import AccountSettingPassword from './AccountSettingPassword.vue'
import AccountSettingInformation from './AccountSettingInformation.vue'

export default {
  components: {
    BTabs,
    BTab,
    AccountSettingGeneral,
    AccountSettingPassword,
    AccountSettingInformation,
  },
  data() {
    return {
      options: {},
    }
  },
  beforeCreate() {
    axios.get('/account-setting/data').then(res => { this.options = res.data })
  },
}
</script>
