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
            fluid
            :src="imgUrl"
            alt="Forgot password V2"
          />
        </div>
      </b-col>
      <!-- /Left Text-->

      <!-- Forgot password-->
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
          <b-card-title class="mb-1">
            Забыли пароль? 🔒
          </b-card-title>
          <b-card-text class="mb-2">
            Введите свой адрес электронной почты, и мы отправим вам инструкции по сбросу пароля.
          </b-card-text>

          <!-- form -->
          <validation-observer ref="simpleRules">
            <b-form
              class="auth-forgot-password-form mt-2"
              @submit.prevent="validationForm"
            >
              <b-form-group
                label="Электронная почта"
                label-for="forgot-password-email"
              >
                <validation-provider
                  #default="{ errors }"
                  name="Email"
                  rules="required|email"
                >
                  <b-form-input
                    id="forgot-password-email"
                    v-model="userEmail"
                    :state="errors.length > 0 ? false:null"
                    name="forgot-password-email"
                    placeholder="john@example.com"
                  />
                  <small class="text-danger">{{ errors[0] }}</small>
                </validation-provider>
              </b-form-group>

              <b-button
                type="submit"
                variant="primary"
                block
              >
                Отправить ссылку для сброса
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
      <!-- /Forgot password-->
    </b-row>
  </div>
</template>

<script>
/* eslint-disable global-require */
import { ValidationProvider, ValidationObserver } from 'vee-validate'
import MpscLogo from '@/layouts/components/Logo.vue'
import {
  BRow, BCol, BLink, BCardTitle, BCardText, BImg, BForm, BFormGroup, BFormInput, BButton,
} from 'bootstrap-vue'
import { required, email } from '@validations'
import store from '@/store'
import ToastificationContent from '@core/components/toastification/ToastificationContent.vue'
import axios from '@axios'

export default {
  components: {
    MpscLogo,
    BRow,
    BCol,
    BLink,
    BImg,
    BForm,
    BButton,
    BFormGroup,
    BFormInput,
    BCardTitle,
    BCardText,
    ValidationProvider,
    ValidationObserver,
  },
  data() {
    return {
      userEmail: '',
      sideImg: require('@/assets/images/pages/forgot-password-v2.svg'),
      // validation
      required,
      email,
    }
  },
  computed: {
    imgUrl() {
      if (store.state.appConfig.layout.skin === 'dark') {
        // eslint-disable-next-line vue/no-side-effects-in-computed-properties
        this.sideImg = require('@/assets/images/pages/forgot-password-v2-dark.svg')
        return this.sideImg
      }
      return this.sideImg
    },
  },
  methods: {
    validationForm() {
      this.$refs.simpleRules.validate().then(success => {
        if (success) {
          axios
            .post('/auth/password/forgot', {
              email: this.userEmail,
            })
            .then(response => {
              this.$toast({
                component: ToastificationContent,
                position: 'top-right',
                icon: 'CheckCircleIcon',
                props: {
                  title: response.data.message,
                  variant: 'success',
                },
              })
            })
            .catch(error => {
              this.$toast({
                component: ToastificationContent,
                position: 'top-right',
                icon: 'AlertCircleIcon',
                props: {
                  title: error.data.message,
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
@import '~@core/scss/vue/pages/page-auth.scss';
</style>
