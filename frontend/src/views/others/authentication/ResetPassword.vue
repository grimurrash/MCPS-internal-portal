<template>
  <div class="auth-wrapper auth-v2">
    <b-row class="auth-inner m-0">

      <!-- Brand logo-->
      <b-link class="brand-logo">
        <mpsc-logo />
        <h2 class="brand-text text-primary ml-1">
          Патриот.Спорт
        </h2>
      </b-link>
      <!-- /Brand logo-->

      <!-- Left Text-->
      <b-col
        lg="8"
        class="d-none d-lg-flex align-items-center p-5"
      >
        <div class="w-100 d-lg-flex align-items-center justify-content-center px-5">
          <b-img
            :src="imgUrl"
            fluid
            alt="Register V2"
          />
        </div>
      </b-col>
      <!-- /Left Text-->

      <!-- Reset password-->
      <b-col
        lg="4"
        class="d-flex align-items-center auth-bg px-2 p-lg-5"
      >
        <b-col
          sm="8"
          md="6"
          lg="12"
          class="px-xl-2 mx-auto"
        >
          <b-card-title
            title-tag="h2"
            class="font-weight-bold mb-1"
          >
            Сбросить пароль 🔒
          </b-card-title>
          <b-card-text class="mb-2">
            Ваш новый пароль должен отличаться от ранее использовавшихся паролей.
          </b-card-text>

          <!-- form -->
          <validation-observer ref="simpleRules">
            <b-form
              class="auth-reset-password-form mt-2"
              method="POST"
              @submit.prevent="resetPassword"
            >

              <!-- password -->
              <b-form-group
                label="Новый пароль"
                label-for="reset-password-new"
              >
                <validation-provider
                  #default="{ errors }"
                  name="Новый пароль"
                  vid="Password"
                  rules="required"
                >
                  <b-input-group
                    class="input-group-merge"
                    :class="errors.length > 0 ? 'is-invalid':null"
                  >
                    <b-form-input
                      id="reset-password-new"
                      v-model="password"
                      :type="password1FieldType"
                      :state="errors.length > 0 ? false:null"
                      class="form-control-merge"
                      name="reset-password-new"
                      placeholder="············"
                    />
                    <b-input-group-append is-text>
                      <feather-icon
                        class="cursor-pointer"
                        :icon="password1ToggleIcon"
                        @click="togglePassword1Visibility"
                      />
                    </b-input-group-append>
                  </b-input-group>
                  <small class="text-danger">{{ errors[0] }}</small>
                </validation-provider>
              </b-form-group>

              <!-- confirm password -->
              <b-form-group
                label-for="reset-password-confirm"
                label="Подтвердите Пароль"
              >
                <validation-provider
                  #default="{ errors }"
                  name="Подтвердите Пароль"
                  rules="required|confirmed:Password"
                >
                  <b-input-group
                    class="input-group-merge"
                    :class="errors.length > 0 ? 'is-invalid':null"
                  >
                    <b-form-input
                      id="reset-password-confirm"
                      v-model="cPassword"
                      :type="password2FieldType"
                      class="form-control-merge"
                      :state="errors.length > 0 ? false:null"
                      name="reset-password-confirm"
                      placeholder="············"
                    />
                    <b-input-group-append is-text>
                      <feather-icon
                        class="cursor-pointer"
                        :icon="password2ToggleIcon"
                        @click="togglePassword2Visibility"
                      />
                    </b-input-group-append>
                  </b-input-group>
                  <small class="text-danger">{{ errors[0] }}</small>
                </validation-provider>
              </b-form-group>

              <!-- submit button -->
              <b-button
                block
                type="submit"
                variant="primary"
              >
                Установить новый пароль
              </b-button>
            </b-form>
          </validation-observer>

          <p class="text-center mt-2">
            <b-link :to="{name:'auth-login'}">
              <feather-icon icon="ChevronLeftIcon" />
              Вернуться на страницу входа
            </b-link>
          </p>
        </b-col>
      </b-col>
      <!-- /Reset password-->
    </b-row>
  </div>
</template>

<script>
/* eslint-disable global-require */
import { ValidationProvider, ValidationObserver } from 'vee-validate'
import MpscLogo from '@/layouts/components/Logo.vue'
import {
  BRow,
  BCol,
  BCardTitle,
  BCardText,
  BForm,
  BFormGroup,
  BInputGroup,
  BInputGroupAppend,
  BLink,
  BFormInput,
  BButton,
  BImg,
} from 'bootstrap-vue'
import { required } from '@validations'
import store from '@/store'
import ToastificationContent from '@core/components/toastification/ToastificationContent.vue'
import router from '@/router'
import axios from '@/libs/axios'

export default {
  components: {
    MpscLogo,
    BRow,
    BCol,
    BButton,
    BCardTitle,
    BCardText,
    BForm,
    BFormGroup,
    BImg,
    BInputGroup,
    BLink,
    BFormInput,
    BInputGroupAppend,
    ValidationProvider,
    ValidationObserver,
  },
  data() {
    return {
      userEmail: '',
      resetToken: '',
      cPassword: '',
      password: '',
      sideImg: require('@/assets/images/pages/reset-password-v2.svg'),
      // validation
      required,

      // Toggle Password
      password1FieldType: 'password',
      password2FieldType: 'password',
    }
  },
  computed: {
    passwordToggleIcon() {
      return this.passwordFieldType === 'password' ? 'EyeIcon' : 'EyeOffIcon'
    },
    imgUrl() {
      if (store.state.appConfig.layout.skin === 'dark') {
        // eslint-disable-next-line vue/no-side-effects-in-computed-properties
        this.sideImg = require('@/assets/images/pages/reset-password-v2-dark.svg')
        return this.sideImg
      }
      return this.sideImg
    },
    password1ToggleIcon() {
      return this.password1FieldType === 'password' ? 'EyeIcon' : 'EyeOffIcon'
    },
    password2ToggleIcon() {
      return this.password2FieldType === 'password' ? 'EyeIcon' : 'EyeOffIcon'
    },
  },
  created() {
    this.resetToken = router.currentRoute.params.token
    axios
      .post('/auth/password/find', {
        token: this.resetToken,
      })
      .then(response => {
        this.userEmail = response.data.email
      })
      .catch(error => {
        this.$toast({
          component: ToastificationContent,
          position: 'top-right',
          props: {
            icon: 'AlertCircleIcon',
            title: error.response.data.message,
            variant: 'danger',
          },
        })
        this.$router.replace({ name: 'auth-login' })
      })
  },
  methods: {
    togglePassword1Visibility() {
      this.password1FieldType = this.password1FieldType === 'password' ? 'text' : 'password'
    },
    togglePassword2Visibility() {
      this.password2FieldType = this.password2FieldType === 'password' ? 'text' : 'password'
    },
    resetPassword() {
      this.$refs.simpleRules.validate().then(success => {
        if (success) {
          console.log(this.userEmail)
          axios
            .post('/auth/password/reset', {
              token: this.resetToken,
              email: this.userEmail,
              password: this.password,
              password_confirmation: this.cPassword,
            })
            .then(response => {
              this.$toast({
                component: ToastificationContent,
                position: 'top-right',
                props: {
                  icon: 'CheckCircleIcon',
                  title: response.data.message,
                  variant: 'success',
                },
              })
              this.$router.push({ name: 'auth-login' })
            })
            .catch(error => {
              this.$toast({
                component: ToastificationContent,
                position: 'top-right',
                props: {
                  icon: 'AlertCircleIcon',
                  title: error.response.data.message,
                  variant: 'danger',
                },
              })
            })
        }
      })
    },
  },
}
</script>

<style lang="scss">
@import '@core/scss/vue/pages/page-auth.scss';
</style>
