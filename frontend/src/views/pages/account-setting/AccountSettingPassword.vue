<template>
  <b-card>
    <!-- form -->
    <validation-observer ref="simpleRules">
      <b-form>
        <b-row>
          <!-- old password -->
          <b-col md="6">
            <b-form-group
              label="Старый пароль"
              label-for="account-old-password"
            >
              <validation-provider
                #default="{ errors }"
                vid="oldPassword"
                name="Старый пароль"
                rules="required"
              >
                <b-input-group class="input-group-merge">
                  <b-form-input
                    id="account-old-password"
                    v-model="passwordValueOld"
                    name="old-password"
                    :type="passwordFieldTypeOld"
                    placeholder="Old Password"
                  />
                  <b-input-group-append is-text>
                    <feather-icon
                      :icon="passwordToggleIconOld"
                      class="cursor-pointer"
                      @click="togglePasswordOld"
                    />
                  </b-input-group-append>
                </b-input-group>
                <small class="text-danger">{{ errors[0] }}</small>
              </validation-provider>
            </b-form-group>
          </b-col>
          <!--/ old password -->
        </b-row>
        <b-row>
          <!-- new password -->
          <b-col md="6">
            <b-form-group
              label-for="account-new-password"
              label="Новый пароль"
            >
              <validation-provider
                #default="{ errors }"
                name="Новый пароль"
                vid="Password"
                rules="required"
              >
                <b-input-group class="input-group-merge">
                  <b-form-input
                    id="account-new-password"
                    v-model="newPasswordValue"
                    :type="passwordFieldTypeNew"
                    name="new-password"
                    placeholder="New Password"
                  />
                  <b-input-group-append is-text>
                    <feather-icon
                      :icon="passwordToggleIconNew"
                      class="cursor-pointer"
                      @click="togglePasswordNew"
                    />
                  </b-input-group-append>
                </b-input-group>
                <small class="text-danger">{{ errors[0] }}</small>
              </validation-provider>
            </b-form-group>
          </b-col>
          <!--/ new password -->

          <!-- retype password -->
          <b-col md="6">
            <b-form-group
              label-for="account-retype-new-password"
              label="Повторно введите Новый Пароль"
            >
              <validation-provider
                #default="{ errors }"
                name="Подтвердите Пароль"
                rules="required|confirmed:Password"
              >
                <b-input-group class="input-group-merge">
                  <b-form-input
                    id="account-retype-new-password"
                    v-model="RetypePassword"
                    :type="passwordFieldTypeRetype"
                    required
                    name="retype-password"
                    placeholder="New Password"
                  />
                  <b-input-group-append is-text>
                    <feather-icon
                      :icon="passwordToggleIconRetype"
                      class="cursor-pointer"
                      @click="togglePasswordRetype"
                    />
                  </b-input-group-append>
                </b-input-group>
                <small class="text-danger">{{ errors[0] }}</small>
              </validation-provider>
            </b-form-group>
          </b-col>
          <!--/ retype password -->

          <!-- buttons -->
          <b-col cols="12">
            <b-button
              v-ripple.400="'rgba(255, 255, 255, 0.15)'"
              variant="primary"
              class="mt-1 mr-1"
              @click="onSaveChanges"
            >
              Сохранить изменения
            </b-button>
          </b-col>
          <!--/ buttons -->
        </b-row>
      </b-form>
    </validation-observer>
  </b-card>
</template>

<script>
import {
  BButton, BForm, BFormGroup, BFormInput, BRow, BCol, BCard, BInputGroup, BInputGroupAppend,
} from 'bootstrap-vue'
import Ripple from 'vue-ripple-directive'
import { ValidationProvider, ValidationObserver } from 'vee-validate'
import { required } from '@validations'
import axios from '@/libs/axios'
import ToastificationContent from '@core/components/toastification/ToastificationContent.vue'

export default {
  components: {
    BButton,
    BForm,
    BFormGroup,
    BFormInput,
    BRow,
    BCol,
    BCard,
    BInputGroup,
    BInputGroupAppend,
    ValidationProvider,
    ValidationObserver,
  },
  directives: {
    Ripple,
  },
  data() {
    return {
      required,
      passwordValueOld: '',
      newPasswordValue: '',
      RetypePassword: '',
      passwordFieldTypeOld: 'password',
      passwordFieldTypeNew: 'password',
      passwordFieldTypeRetype: 'password',
    }
  },
  computed: {
    passwordToggleIconOld() {
      return this.passwordFieldTypeOld === 'password' ? 'EyeIcon' : 'EyeOffIcon'
    },
    passwordToggleIconNew() {
      return this.passwordFieldTypeNew === 'password' ? 'EyeIcon' : 'EyeOffIcon'
    },
    passwordToggleIconRetype() {
      return this.passwordFieldTypeRetype === 'password' ? 'EyeIcon' : 'EyeOffIcon'
    },
  },
  methods: {
    togglePasswordOld() {
      this.passwordFieldTypeOld = this.passwordFieldTypeOld === 'password' ? 'text' : 'password'
    },
    togglePasswordNew() {
      this.passwordFieldTypeNew = this.passwordFieldTypeNew === 'password' ? 'text' : 'password'
    },
    togglePasswordRetype() {
      this.passwordFieldTypeRetype = this.passwordFieldTypeRetype === 'password' ? 'text' : 'password'
    },
    onSaveChanges() {
      this.$refs.simpleRules.validate()
        .then(success => {
          if (!success) return

          axios.post('/account-setting/password/save-changes', {
            passwordValueOld: this.passwordValueOld,
            password: this.newPasswordValue,
            password_confirmation: this.RetypePassword,
          })
            .then(response => {
              this.$toast({
                component: ToastificationContent,
                position: 'top-right',
                props: {
                  title: response.data.message,
                  variant: 'success',
                  icon: 'CheckIcon',
                },
              })
            })
            .catch(error => {
              this.$toast({
                component: ToastificationContent,
                position: 'top-right',
                props: {
                  title: error.response.data.message,
                  variant: 'danger',
                  icon: 'AlertCircleIcon',
                },
              })
            })
        })
    },
  },
}
</script>
